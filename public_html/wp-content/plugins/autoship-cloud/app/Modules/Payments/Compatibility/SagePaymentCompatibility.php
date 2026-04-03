<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Sage Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\SagePaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Sage payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class SagePaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'sagepaydirect';
		$this->gateway_name = 'Sage';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Sage.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Sage payment integration instance.
	 *
	 * @return ?SagePaymentIntegration
	 */
	private function get_integration(): ?SagePaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'sagepaydirect' );
		} catch ( \Exception $e ) {
			Logger::error( 'Sage Payment Compatibility', 'Error getting Sage payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Sage.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof SagePaymentIntegration ) ) {
			Logger::trace( 'Sage Payment Compatibility', 'Unable to register Sage compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_Sage_payment_method', 'autoship_add_sagepaydirect_payment_method', 10 );
		remove_filter( 'autoship_delete_Sage_payment_method_qpilot_match', 'autoship_delete_sagepaydirect_payment_method', 10 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Sage_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_Sage_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for Sage.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof SagePaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'woocommerce_checkout_order_processed', 'autoship_sagepaydirect_order_payment_data_saved_payment_patch', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_sagepaydirect_gateway', 'autoship_add_adjusted_gateway_metadata_sage', 10 );

		// Hook for checkout order patch to store token in order meta.
		add_action( 'woocommerce_checkout_order_processed', array( SagePaymentIntegration::class, 'checkout_order_patch' ), 10, 3 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_sagepaydirect_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
