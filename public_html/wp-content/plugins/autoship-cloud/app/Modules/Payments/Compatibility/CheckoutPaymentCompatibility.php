<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Checkout.com Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\CheckoutPaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Checkout.com payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class CheckoutPaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'wc_checkout_com_cards';
		$this->gateway_name = 'Checkout';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Checkout.com.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Checkout.com payment integration instance.
	 *
	 * @return ?CheckoutPaymentIntegration
	 */
	private function get_integration(): ?CheckoutPaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'wc_checkout_com_cards' );
		} catch ( \Exception $e ) {
			Logger::error( 'Checkout Payment Compatibility', 'Error getting Checkout.com payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Checkout.com.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof CheckoutPaymentIntegration ) ) {
			Logger::trace( 'Checkout Payment Compatibility', 'Unable to register Checkout.com compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_Checkout_payment_method', 'autoship_add_checkout_payment_method', 10 );
		remove_filter( 'autoship_delete_Checkout_payment_method_qpilot_match', 'autoship_delete_checkout_payment_method', 10 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Checkout_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_Checkout_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for Checkout.com.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof CheckoutPaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'autoship_update_scheduled_orders_on_processing_wc_checkout_com_cards_gateway', 'autoship_add_adjusted_gateway_metadata_checkout', 10 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_wc_checkout_com_cards_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
