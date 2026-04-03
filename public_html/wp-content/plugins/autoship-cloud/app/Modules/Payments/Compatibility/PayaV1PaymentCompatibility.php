<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * PayaV1 Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\PayaV1PaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for PayaV1 payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class PayaV1PaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'sagepaymentsusaapi';
		$this->gateway_name = 'PayaV1';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for PayaV1.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the PayaV1 payment integration instance.
	 *
	 * @return ?PayaV1PaymentIntegration
	 */
	private function get_integration(): ?PayaV1PaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'sagepaymentsusaapi' );
		} catch ( \Exception $e ) {
			Logger::error( 'PayaV1 Payment Compatibility', 'Error getting PayaV1 payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for PayaV1.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof PayaV1PaymentIntegration ) ) {
			Logger::trace( 'PayaV1 Payment Compatibility', 'Unable to register PayaV1 compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_PayaV1_payment_method', 'autoship_add_payav1_payment_method', 10 );
		remove_filter( 'autoship_delete_PayaV1_payment_method_qpilot_match', 'autoship_delete_payav1_payment_method', 10 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_PayaV1_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_PayaV1_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for PayaV1.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof PayaV1PaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'autoship_update_scheduled_orders_on_processing_sagepaymentsusaapi_gateway', 'autoship_add_adjusted_gateway_metadata_sagepaymentsusaapi', 10 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_sagepaymentsusaapi_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
