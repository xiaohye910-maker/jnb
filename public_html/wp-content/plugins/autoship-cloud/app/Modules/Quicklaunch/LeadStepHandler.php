<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class implements the lead registration step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\EnvironmentInterface;
use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\InstallerInterface;

/**
 * The lead registration step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class LeadStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * The feature manager instance.
	 *
	 * @var FeatureManagerInterface
	 */
	private FeatureManagerInterface $feature_manager;

	/**
	 * The environment instance.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * The installer instance.
	 *
	 * @var InstallerInterface
	 */
	private InstallerInterface $installer;

	/**
	 * Initializes a new instance of the class.
	 *
	 * @param FeatureManagerInterface $feature_manager The feature manager.
	 * @param EnvironmentInterface    $environment The environment.
	 * @param InstallerInterface      $installer The installer.
	 * @return void
	 */
	public function __construct( FeatureManagerInterface $feature_manager, EnvironmentInterface $environment, InstallerInterface $installer ) {
		$this->feature_manager = $feature_manager;
		$this->environment     = $environment;
		$this->installer       = $installer;
		add_action( 'wp_ajax_autoship_quicklaunch_lead_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles the request.
	 *
	 * @return void
	 */
	public function handle(): void {
		// Check if the quick launch is enabled.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch' ) ) {
			wp_send_json_error( array( 'message' => __( 'The Autoship Quick Launch is not enabled.', 'autoship' ) ) );
		}

		// If the lead registration is not enabled, bypass it and continue using mock data.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch_lead_registration' ) ) {
			wp_send_json_success(
				array(
					'lead_id' => wp_generate_uuid4(),
					'message' => 'ok',
				)
			);
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to create a lead.', 'autoship' ) ) );
		}

		// Check if the plugin is installed and activated on the registration service.
		if ( ! $this->installer->is_installed() ) {
			$installed = $this->installer->install();

			if ( ! $installed ) {
				wp_send_json_error( array( 'message' => __( 'The current autoship installation could not be completed.', 'autoship' ) ) );
			}
		}

		// Get the lead information from the request.
		$user_email = $this->get_post_string_value( 'email' );
		$role       = $this->get_post_string_value( 'role' );
		$revenue    = $this->get_post_string_value( 'revenue' );
		$category   = $this->get_post_string_value( 'category' );
		$reason     = $this->get_post_string_value( 'reason' );

		$lead_data = array(
			'Id'                 => $this->installer->get_installation_id(),
			'InstallationKey'    => $this->installer->get_installation_key(),
			'StoreName'          => $this->environment->get_site_name(),
			'StoreUrl'           => $this->environment->get_site_url(),
			'UserName'           => $this->environment->get_current_user_name(),
			'UserEmail'          => $user_email,
			'UserRole'           => $role,
			'Revenue'            => $revenue,
			'Category'           => $category,
			'Reason'             => $reason,
			'Creation'           => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'AutoshipVersion'    => $this->environment->get_autoship_version(),
			'PhpVersion'         => $this->environment->get_php_version(),
			'WordPressVersion'   => $this->environment->get_wordpress_version(),
			'WordPressLanguage'  => $this->environment->get_wordpress_language(),
			'WooCommerceVersion' => $this->environment->get_woocommerce_version(),
			'WooCommerceCountry' => $this->environment->get_woocommerce_country(),
		);

		$lead_id = $this->register_lead( $lead_data );
		if ( empty( $lead_id ) ) {
			wp_send_json_error( array( 'message' => __( 'There is no lead id.', 'autoship' ) ) );
		}

		$updated = update_option( 'autoship_plugin_registration_lead_id', $lead_id );
		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => __( 'The lead id was not registered.', 'autoship' ) ) );
		}

		wp_send_json_success(
			array(
				'lead_id' => $lead_id,
				'message' => 'ok',
			)
		);
	}

	/**
	 * Performs the registration of the current installation.
	 *
	 * @param array $lead_data The lead data to register.
	 * @return string The installation key or null if the key could not be retrieved from the remote service.
	 */
	private function register_lead( array $lead_data ): string {
		$endpoint = $this->environment->get_api_url() . '/Quicklaunch/RegisterLead';

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $lead_data ),
				'method'  => 'POST',
			)
		);

		$lead_id = '';
		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return $lead_id;
		}

		$body = json_decode( $response['body'], true );
		return sanitize_text_field( $body['leadId'] ?? '' );
	}
}
