<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Compatibility hooks and admin integration for the health check system.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Modules\Healthcheck\Compatibility;

use Autoship\Core\ClockInterface;
use Autoship\Core\CredentialsInterface;
use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;
use Autoship\Modules\Healthcheck\HealthcheckService;
use Autoship\Services\Healthcheck\HealthcheckSettingsInterface;
use Autoship\Services\Labels\LabelServiceInterface;
use Autoship\Services\Logging\LoggerInterface;

/**
 * Admin hooks, settings watchers, and QPilot response filters.
 *
 * Registers WordPress hooks for admin menu badges, settings change
 * detection, QPilot response filtering, and background check handling.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class HealthcheckCompatibility {

	/**
	 * The health check service.
	 *
	 * @var HealthcheckService
	 */
	private HealthcheckService $service;

	/**
	 * The health check settings.
	 *
	 * @var HealthcheckSettingsInterface
	 */
	private HealthcheckSettingsInterface $settings;

	/**
	 * The clock service.
	 *
	 * @var ClockInterface
	 */
	private ClockInterface $clock;

	/**
	 * The logger service.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * The label service.
	 *
	 * @var LabelServiceInterface
	 */
	private LabelServiceInterface $labels;

	/**
	 * The credentials service.
	 *
	 * @var CredentialsInterface
	 */
	private CredentialsInterface $credentials;

	/**
	 * Constructor.
	 *
	 * @param HealthcheckService           $service     The health check service.
	 * @param HealthcheckSettingsInterface $settings    The health check settings.
	 * @param ClockInterface               $clock       The clock service.
	 * @param LoggerInterface              $logger      The logger service.
	 * @param LabelServiceInterface        $labels      The label service.
	 * @param CredentialsInterface         $credentials The credentials service.
	 */
	public function __construct(
		HealthcheckService $service,
		HealthcheckSettingsInterface $settings,
		ClockInterface $clock,
		LoggerInterface $logger,
		LabelServiceInterface $labels,
		CredentialsInterface $credentials
	) {
		$this->service     = $service;
		$this->settings    = $settings;
		$this->clock       = $clock;
		$this->logger      = $logger;
		$this->labels      = $labels;
		$this->credentials = $credentials;
	}

	/**
	 * Register all WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Settings change watchers.
		$effected_settings = array(
			'autoship_client_id',
			'autoship_client_secret',
			'autoship_site_id',
			'autoship_user_id',
			'autoship_token_auth',
			'autoship_refresh_token',
		);

		foreach ( $effected_settings as $option ) {
			add_action( "update_option_{$option}", array( $this, 'on_setting_changed' ), 10, 2 );
		}

		// Admin menu bubble.
		add_action( 'admin_menu', array( $this, 'add_health_status_menu_bubble' ) );

		// Submenu health bubble.
		add_filter( 'autoship_admin_settings_submenu_pages', array( $this, 'add_submenu_health_bubble' ), 10, 1 );

		// QPilot response filters.
		add_filter( 'qpilot_remote_request_response_messages', array( $this, 'filter_user_messages' ), 10, 2 );
		add_action( 'qpilot_remote_request_response_errors', array( $this, 'log_response_errors' ), 10, 1 );

		// Background check handler.
		add_action( 'autoship_run_integration_check', array( $this, 'handle_background_check' ), 10, 1 );
	}

	/**
	 * Handle changes to settings that affect integration health.
	 *
	 * Clears integration point statuses when any of the monitored
	 * settings change, triggering a re-test on the next check.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $new_value The new option value.
	 *
	 * @return void
	 */
	public function on_setting_changed( $old_value, $new_value ): void {
		if ( $old_value !== $new_value ) {
			$this->settings->clear_point_statuses();
		}
	}

	/**
	 * Add a health check notification bubble to the Autoship admin menu.
	 *
	 * Checks freshness and queues a background check if stale or unhealthy.
	 *
	 * @return void
	 */
	public function add_health_status_menu_bubble(): void {
		global $menu;

		if ( ! $this->credentials->is_connected() || apply_filters( 'autoship_disable_admin_health_checks', false ) ) {
			return;
		}

		$healthy = $this->settings->get_health_status();

		if ( ! $this->service->is_fresh() || ( 'healthy' !== $healthy ) ) {
			//$features = Plugin::get_service_container()->get( FeatureManagerInterface::class );
			//if ( ! $features->is_enabled( 'development_mode' ) ) {
				$this->service->queue_check();
			//}
		}

		if ( ! apply_filters( 'autoship_show_admin_health_status_menu_bubble', 'healthy' !== $healthy ) ) {
			return;
		}

		foreach ( $menu as $key => $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			if ( 'autoship' === $value[2] ) {
				$menu[ $key ][0] .= '<span class="autoship-health"><span class="health-error">!</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				return;
			}
		}
	}

	/**
	 * Add a health status bubble to the Autoship settings submenu.
	 *
	 * @param array $menu_options The current Autoship menu options.
	 *
	 * @return array The modified menu options.
	 */
	public function add_submenu_health_bubble( array $menu_options ): array {
		$healthy = $this->settings->get_health_status();

		if ( 'healthy' !== $healthy ) {
			$menu_options['autoship']['menu_title'] .= '<span class="autoship-health"><span class="health-error">!</span></span>';
		}

		return $menu_options;
	}

	/**
	 * Filter user messages returned by QPilot client responses.
	 *
	 * Applies label customizations to each message for white-label support.
	 *
	 * @param array $messages The messages to filter.
	 * @param array $args     Additional call details.
	 *
	 * @return array The filtered messages.
	 */
	public function filter_user_messages( array $messages, $args ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$filtered_notices = array();

		foreach ( $messages as $message ) {
			$filtered_notices[] = __( $this->labels->apply_labels( $message ), 'autoship' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		}

		return $filtered_notices;
	}

	/**
	 * Log technical errors returned by QPilot client responses.
	 *
	 * @param array $args The error arguments to log.
	 *
	 * @return void
	 */
	public function log_response_errors( array $args ): void {
		$context = sprintf( '%d %s Request Error: %s', $args['code'], $args['callArgs']['method'], $args['response_error'] );
		$message = sprintf( 'Error Details: %s // Endpoint Details: %s', implode( ' ', $args['errors'] ), $args['callArgs']['endpoint'] );
		$this->logger->error( $context, $message );
	}

	/**
	 * Handle a background integration check from Action Scheduler.
	 *
	 * @param mixed $args The action arguments.
	 *
	 * @return void
	 */
	public function handle_background_check( $args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( ! $this->credentials->is_connected() ) {
			return;
		}

		$this->service->run_check();

		$this->settings->set_last_check_timestamp( $this->clock->timestamp() );
	}
}
