<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Core orchestration service for the Health check system.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Modules\Healthcheck;

use Autoship\Core\ClockInterface;
use Autoship\Domain\Healthcheck\HttpErrorCodes;
use Autoship\Services\Healthcheck\HealthcheckSettingsInterface;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Services\QPilot\Integrations\IntegrationCheckResponse;
use Autoship\Services\QPilot\QPilotServiceInterface;
use Exception;

/**
 * Core orchestration service for the Health check system.
 *
 * Manages integration health checks by coordinating between QPilot,
 * the settings layer, and the WordPress notification system.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class HealthcheckService {

	/**
	 * The health check settings.
	 *
	 * @var HealthcheckSettingsInterface
	 */
	private HealthcheckSettingsInterface $settings;

	/**
	 * The QPilot service client.
	 *
	 * @var QPilotServiceInterface|null
	 */
	private ?QPilotServiceInterface $qpilot;

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
	 * Constructor.
	 *
	 * @param HealthcheckSettingsInterface $settings The health check settings.
	 * @param QPilotServiceInterface|null  $qpilot   The QPilot service client (null if no credentials).
	 * @param ClockInterface               $clock    The clock service.
	 * @param LoggerInterface              $logger   The logger service.
	 */
	public function __construct(
		HealthcheckSettingsInterface $settings,
		?QPilotServiceInterface $qpilot,
		ClockInterface $clock,
		LoggerInterface $logger
	) {
		$this->settings = $settings;
		$this->qpilot   = $qpilot;
		$this->clock    = $clock;
		$this->logger   = $logger;
	}

	/**
	 * Check if all integration point statuses are fresh.
	 *
	 * Stale duration can be modified via the
	 * {@see autoship_qpilot_integration_check_duration} filter.
	 *
	 * @return bool True if all integration statuses are fresh, false otherwise.
	 */
	public function is_fresh(): bool {
		$limit  = apply_filters( 'autoship_qpilot_integration_check_duration', 90 );
		$fields = $this->settings->get_status_fields();

		foreach ( $fields as $field => $action ) {
			$val = $this->settings->get_point_status( $action );

			if ( empty( $val ) ) {
				return false;
			}

			$now      = $this->clock->timestamp();
			$dte_diff = abs( $now - $val ) / 60;

			if ( $dte_diff > $limit ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Run the integration health check.
	 *
	 * Clears point statuses, calls QPilot's integration check endpoint,
	 * and processes the results to determine overall health.
	 *
	 * @param bool $force Whether to force the check. Currently unused by the modern API.
	 *
	 * @return string|false The health status ('healthy'/'unhealthy'), or false if no credentials.
	 */
	public function run_check( bool $force = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$this->settings->clear_point_statuses();

		if ( null === $this->qpilot ) {
			return false;
		}

		try {
			$result = $this->qpilot->check_integration();
			$health = $this->process_results( $result );
		} catch ( Exception $e ) {
			$health = $this->process_results( null, true, $e->getCode(), $e->getMessage() );
		}

		do_action( 'autoship_init_integration_test_complete', $health );

		return $health;
	}

	/**
	 * Process the integration, checks the result, and update health status.
	 *
	 * Checks each integration endpoint status, builds notices, and
	 * delegates to the legacy notice handler for display.
	 *
	 * @param IntegrationCheckResponse|null $result    The integration check response.
	 * @param bool                          $log       Whether to log the notices. Default true.
	 * @param string                        $e_code    The error code for exception cases. Default '520'.
	 * @param string                        $e_message The error message for exception cases.
	 *
	 * @return string The health status ('healthy' or 'unhealthy').
	 */
	public function process_results( $result = null, $log = true, $e_code = '520', $e_message = 'An Unknown Error was encountered.' ): string {

		$checks = apply_filters(
			'autoship_integration_status_check_messages',
			array(
				'post'   => array(
					'updated' => __( 'POST: Orders can be created', 'autoship' ),
					'error'   => __( 'FAILED POST: Orders cannot be created', 'autoship' ),
					'code'    => 1100,
				),
				'wc_get' => array(
					'updated' => __( 'GET: Product Data can be synchronized', 'autoship' ),
					'error'   => __( 'FAILED GET: Product Data cannot be synchronized', 'autoship' ),
					'code'    => 1210,
				),
				'put'    => array(
					'updated' => __( 'PUT: Data can be updated', 'autoship' ),
					'error'   => __( 'FAILED PUT: Data cannot be updated', 'autoship' ),
					'code'    => 1300,
				),
			)
		);

		// Extract QPilot errors from the result.
		$qpilot_errors = $this->extract_errors( $result );

		// If no WC Get error occurred, set the timestamp manually.
		if ( ! isset( $qpilot_errors[1210] ) ) {
			$timestamp = $this->clock->timestamp();
			$this->settings->set_point_status( 'wc_get', $timestamp );
		}

		// Iterate through the statuses and build notices.
		$healthy        = true;
		$notices        = array();
		$notice_details = '';

		foreach ( $checks as $action => $values ) {

			$timestamp = $this->settings->get_point_status( $action );
			if ( empty( $timestamp ) || isset( $qpilot_errors[ $values['code'] ] ) ) {

				$msg = '<span class="autoship-health-msg">' . $values['error'] . '</span>';

				if ( isset( $qpilot_errors[ $values['code'] ] ) ) {
					$e_code         = $qpilot_errors[ $values['code'] ]['code'];
					$e_message      = empty( $qpilot_errors[ $values['code'] ]['message'] ) ? $qpilot_errors[ $values['code'] ]['code_notice'] : $qpilot_errors[ $values['code'] ]['message'];
					$notice_details = $qpilot_errors[ $values['code'] ]['details'];
				}

				$msg      .= $healthy ? '<br/><span class="autoship-health-error"> Details: ' . $e_code . ' // ' . $e_message . '</span>' : '';
				$notices[] = array(
					'type' => 'exception',
					'msg'  => $msg,
					'api'  => $notice_details,
					'log'  => true,
				);
				$healthy   = false;

			} else {

				$notices[] = array(
					'type' => 'notice',
					'msg'  => '<span class="autoship-health-msg">' . $values['updated'] . '</span>',
					'api'  => $notice_details,
					'log'  => false,
				);
			}
		}

		// Update the overall health status.
		$val = $healthy ? 'healthy' : 'unhealthy';
		$this->settings->set_health_status( $val );
		$this->settings->set_health_details( $notice_details );

		if ( $log ) {
			foreach ( $notices as $values ) {
				// Legacy notice handler — not yet migrated.
				autoship_notice_handler( $values['type'], $values['msg'], false, 'autoship_health_checks' );

				if ( apply_filters( 'autoship_log_api_health_error_details', true, $values ) ) {
					// translators: %1$s is the message and %2$s is the api message.
					$this->logger->log( __( 'Autoship Health Check', 'autoship' ), sprintf( __( '%1$s Additional API Error Details: %2$s', 'autoship' ), $values['msg'], $values['api'] ) );
				}
			}
		}

		return $val;
	}

	/**
	 * Queue a background integration check using Action Scheduler.
	 *
	 * Falls back to immediate run_check() if Action Scheduler is unavailable.
	 *
	 * @param bool $force If true, enqueue even if a pending check exists.
	 *
	 * @return void
	 */
	public function queue_check( bool $force = false ): void {

		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			$this->run_check();
			return;
		}

		$hook = 'autoship_run_integration_check';

		$pending = as_next_scheduled_action( $hook );
		if ( $pending && ! $force ) {
			return;
		}

		as_enqueue_async_action( $hook, array( 'forced' => $force ), 'autoship' );
	}

	/**
	 * Get the settings instance.
	 *
	 * @return HealthcheckSettingsInterface
	 */
	public function get_settings(): HealthcheckSettingsInterface {
		return $this->settings;
	}

	/**
	 * Extract errors from an IntegrationCheckResponse into a keyed array.
	 *
	 * Adapts the modern typed response into the error map format
	 * expected by the processing logic. Falls back to raw_data
	 * for error detail structures.
	 *
	 * @param IntegrationCheckResponse|null $result The integration check response.
	 *
	 * @return array Errors keyed by QPilot error type code.
	 */
	private function extract_errors( ?IntegrationCheckResponse $result ): array {
		$qpilot_errors = array();

		if ( null === $result ) {
			return $qpilot_errors;
		}

		$raw = $result->get_raw_data();

		// Check the typed accessor first, then fall back to raw data.
		$is_success = $result->is_success();
		if ( null === $is_success && isset( $raw->isSuccess ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$is_success = (bool) $raw->isSuccess; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		$errors = array();
		if ( isset( $raw->errors ) && is_array( $raw->errors ) ) {
			$errors = $raw->errors;
		}

		if ( ! $is_success && ! empty( $errors ) ) {
			foreach ( $errors as $error ) {
				$type        = $error->type ?? 0;
				$status_code = $error->statusCode ?? 0; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$message     = $error->message ?? '';
				$detail      = $error->detail ?? '';

				$qpilot_errors[ $type ] = array(
					'code'        => $status_code,
					'code_notice' => HttpErrorCodes::expand( (int) $status_code, 'desc' ),
					'message'     => $message,
					'details'     => $detail,
				);
			}
		}

		return $qpilot_errors;
	}
}
