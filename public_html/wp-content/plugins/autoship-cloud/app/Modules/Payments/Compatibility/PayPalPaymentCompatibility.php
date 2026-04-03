<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * PayPal Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\PayPalPaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for PayPal payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.11.0
 */
class PayPalPaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'ppec_paypal';
		$this->gateway_name = 'PayPal';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for PayPal.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the PayPal payment integration instance.
	 *
	 * @return ?PayPalPaymentIntegration
	 */
	private function get_integration(): ?PayPalPaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'ppec_paypal' );
		} catch ( \Exception $e ) {
			Logger::error( 'PayPal Payment Compatibility', 'Error getting PayPal payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for PayPal.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof PayPalPaymentIntegration ) ) {
			Logger::trace( 'PayPal Payment Compatibility', 'Unable to register PayPal compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_extend_gateway_id_types', 'autoship_add_paypal_v3_token_support', 12 );
		remove_filter( 'autoship_delete_PayPalV3_payment_method_qpilot_match', 'autoship_delete_paypal_v3_payment_method', 10 );

		// PayPal V3 token support filter.
		add_filter( 'autoship_extend_gateway_id_types', array( $integration, 'add_paypal_v3_token_support' ), 12, 1 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_PayPal_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );
		add_filter( 'autoship_add_PayPalV3_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for deleting payment methods from QPilot.
		add_filter( 'autoship_delete_PayPal_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
		add_filter( 'autoship_delete_PayPalV3_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for PayPal.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof PayPalPaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'autoship_update_scheduled_orders_on_processing_ppec_paypal_gateway', 'autoship_add_adjusted_gateway_metadata_ppec_paypal', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_ppcp-gateway_gateway', 'autoship_add_adjusted_gateway_metadata_ppep_paypal', 10 );

		// Hook for adding gateway metadata during scheduled order updates (PPEC).
		add_action( 'autoship_update_scheduled_orders_on_processing_ppec_paypal_gateway', array( $integration, 'add_metadata' ), 10, 3 );

		// Hook for adding gateway metadata during scheduled order updates (PPCP).
		add_action( 'autoship_update_scheduled_orders_on_processing_ppcp-gateway_gateway', array( $integration, 'add_adjusted_gateway_metadata_ppep_paypal' ), 10, 3 );
	}
}
