<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Square Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\SquarePaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Square payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.0
 */
class SquarePaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'square_credit_card';
		$this->gateway_name = 'Square';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Square.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Square payment integration instance.
	 *
	 * @return ?SquarePaymentIntegration
	 */
	private function get_integration(): ?SquarePaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'square_credit_card' );
		} catch ( \Exception $e ) {
			Logger::error( 'Square Payment Compatibility', 'Error getting Square payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Square.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof SquarePaymentIntegration ) ) {
			Logger::trace( 'Square Payment Compatibility', 'Unable to register Square compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'wc_payment_gateway_square_credit_card_add_payment_method_transaction_result', 'autoship_add_square_payment_method', 10 );
		remove_filter( 'autoship_add_Square_payment_method', 'autoship_add_square_payment_method_data', 10 );
		remove_filter( 'wc_square_my_payment_methods_table_method_actions', 'autoship_display_apply_payment_method_to_all_scheduled_orders_square_btn', 10 );

		// Hook for adding credit card payment method transaction result.
		add_filter( 'wc_payment_gateway_square_credit_card_add_payment_method_transaction_result', array( $integration, 'add_payment_method_data' ), 10, 4 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Square_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for deleting payment methods from QPilot.
		add_filter( 'autoship_delete_Square_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );

		// Hook for displaying apply payment method to all scheduled orders button.
		add_filter( 'wc_square_my_payment_methods_table_method_actions', array( $integration, 'autoship_display_apply_payment_method_to_all_scheduled_orders_square_btn' ), 10, 3 );

		// Square force save payment method filter.
		add_filter( 'wc_square_credit_card_force_save_source', array( SquarePaymentIntegration::class, 'force_save_source' ) );

		// Square create customer request filter.
		add_filter( 'wc_square_create_customer_request', array( SquarePaymentIntegration::class, 'filter_customer_request' ), 30, 2 );
	}

	/**
	 * Register action hooks for Square.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof SquarePaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'wc_payment_gateway_square_credit_card_payment_method_deleted', 'autoship_delete_square_payment_method', 10 );
		remove_action( 'wc_payment_gateway_square_payment_method_added', 'autoship_after_save_square_credit_card_payment_method_notice', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_square_credit_card_gateway', 'autoship_add_adjusted_gateway_metadata_square', 10 );

		// Hook for adding the payment method notice.
		add_action( 'wc_payment_gateway_square_payment_method_added', array( $integration, 'autoship_after_save_square_credit_card_payment_method_notice' ), 10, 3 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_square_gateway', array( $integration, 'add_adjusted_gateway_metadata' ), 10, 3 );

		// Hook for deleting the payment method.
		add_action( 'wc_payment_gateway_square_credit_card_payment_method_deleted', array( $integration, 'delete_payment_method' ), 10, 2 );
	}
}
