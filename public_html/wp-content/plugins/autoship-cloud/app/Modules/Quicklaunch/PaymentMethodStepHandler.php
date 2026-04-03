<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The product step handler class.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\FeatureManagerInterface;
use Autoship\Domain\PaymentIntegrationFactory;
use QPilotClient;

/**
 * Implements the product step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PaymentMethodStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * The feature manager instance.
	 *
	 * @var FeatureManagerInterface
	 */
	private FeatureManagerInterface $feature_manager;

	/**
	 * Initializes a new instance of the class.
	 *
	 * @param FeatureManagerInterface $feature_manager The feature manager.
	 * @return void
	 */
	public function __construct( FeatureManagerInterface $feature_manager ) {
		$this->feature_manager = $feature_manager;
		add_action( 'wp_ajax_autoship_quicklaunch_payment_method_handler', array( $this, 'handle' ) );
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

		// If the register is not enabled, bypass it and continue using mock data.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch_payment_method_installer' ) ) {
			wp_send_json_success( array( 'message' => 'ok' ) );
		}

		$method_id = $this->get_post_string_value( 'gateway_id' );
		$settings  = array();

		$installed_payment_methods = WC()->payment_gateways()->payment_gateways();
		foreach ( $installed_payment_methods as $method ) {
			if ( $method->id === $method_id ) {
				$settings = $method->settings;
			}
		}

		$integration = PaymentIntegrationFactory::create( $method_id, $settings );
		if ( ! $integration->is_valid() ) {
			wp_send_json_error( array( 'message' => __( 'The payment method is not valid.', 'autoship' ) ) );
		}

		$qpilot = new QPilotClient();

		// We can make sure we have existing payment methods. $existing = $qpilot->get_payment_integrations().

		$integration_data = $integration->get_data();

		$created = $qpilot->create_payment_integration( $integration_data );
		if ( is_wp_error( $created ) ) {
			wp_send_json_error( array( 'message' => __( 'The payment method could not be created in QPilot.', 'autoship' ) ) );
		}

		$after = $qpilot->get_payment_integrations();

		$can_proceed = $after->totalCount > 0; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		wp_send_json_success(
			array(
				'message'     => 'Installed',
				'gateway_id'  => $method_id,
				'can_proceed' => $can_proceed,
				'status_icon' => Autoship_Plugin_Url . '/images/activity-icons/autoship_check.svg',
			)
		);
	}
}
