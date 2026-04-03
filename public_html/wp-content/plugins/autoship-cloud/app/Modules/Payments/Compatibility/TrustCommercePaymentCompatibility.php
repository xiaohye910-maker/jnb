<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * TrustCommerce Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\TrustCommercePaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for TrustCommerce payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class TrustCommercePaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'trustcommerce';
		$this->gateway_name = 'TrustCommerce';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for TrustCommerce.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the TrustCommerce payment integration instance.
	 *
	 * @return ?TrustCommercePaymentIntegration
	 */
	private function get_integration(): ?TrustCommercePaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'trustcommerce' );
		} catch ( \Exception $e ) {
			Logger::error( 'TrustCommerce Payment Compatibility', 'Error getting TrustCommerce payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for TrustCommerce.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof TrustCommercePaymentIntegration ) ) {
			Logger::trace( 'TrustCommerce Payment Compatibility', 'Unable to register TrustCommerce compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_TrustCommerce_payment_method', 'autoship_add_trustcommerce_payment_method', 10 );
		remove_filter( 'autoship_delete_TrustCommerce_payment_method_qpilot_match', 'autoship_delete_trustcommerce_payment_method', 10 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_TrustCommerce_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_TrustCommerce_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for TrustCommerce.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof TrustCommercePaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'autoship_update_scheduled_orders_on_processing_trustcommerce_gateway', 'autoship_add_adjusted_gateway_metadata_trustcommerce', 10 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_trustcommerce_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
