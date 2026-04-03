<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Braintree Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\BraintreePaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Braintree payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.0
 */
class BraintreePaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'braintree_credit_card';
		$this->gateway_name = 'Braintree';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Braintree.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Braintree payment integration instance.
	 *
	 * @return ?BraintreePaymentIntegration
	 */
	private function get_integration(): ?BraintreePaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'braintree_credit_card' );
		} catch ( \Exception $e ) {
			Logger::error( 'Braintree Payment Compatibility', 'Error getting Braintree payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Braintree.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof BraintreePaymentIntegration ) ) {
			Logger::trace( 'Braintree Payment Compatibility', 'Unable to register Braintree compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'wc_payment_gateway_braintree_credit_card_add_payment_method_transaction_result', 'autoship_add_braintree_credit_card_payment_method', 10 );
		remove_filter( 'autoship_add_Braintree_payment_method', 'autoship_add_braintree_payment_method_data', 10 );
		remove_filter( 'wc_payment_gateway_braintree_paypal_add_payment_method_transaction_result', 'autoship_add_braintree_paypal_payment_method', 10 );
		remove_filter( 'wc_braintree_my_payment_methods_table_method_actions', 'autoship_display_apply_payment_method_to_all_scheduled_orders_braintree_btn', 10 );

		// Hook for adding credit card payment method transaction result.
		add_filter( 'wc_payment_gateway_braintree_credit_card_add_payment_method_transaction_result', array( $integration, 'add_credit_card_payment_method_data' ), 10, 4 );

		// Hook for adding PayPal payment method transaction result.
		add_filter( 'wc_payment_gateway_braintree_paypal_add_payment_method_transaction_result', array( $integration, 'add_paypal_payment_method_data' ), 10, 4 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Braintree_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_Braintree_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );

		// Hook for displaying the apply to all orders button.
		add_filter( 'wc_braintree_my_payment_methods_table_method_actions', array( $integration, 'display_apply_to_all_orders_button' ), 10, 3 );
	}

	/**
	 * Register action hooks for Braintree.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof BraintreePaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'wc_payment_gateway_braintree_credit_card_payment_processed', 'autoship_add_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_braintree_credit_card_payment_method_added', 'autoship_add_my_account_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_braintree_paypal_payment_processed', 'autoship_add_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_braintree_paypal_payment_method_added', 'autoship_add_my_account_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_braintree_credit_card_payment_method_added', 'autoship_after_save_braintree_credit_card_payment_method_notice', 10 );
		remove_action( 'wc_payment_gateway_braintree_credit_card_payment_method_deleted', 'autoship_delete_braintree_credit_card_payment_method', 10 );
		remove_action( 'wc_payment_gateway_braintree_paypal_payment_method_deleted', 'autoship_delete_braintree_paypal_payment_method', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_braintree_gateway', 'autoship_add_adjusted_gateway_metadata_braintree', 10 );

		// Hook for processing payments during checkout (credit card).
		add_action( 'wc_payment_gateway_braintree_credit_card_payment_processed', array( $integration, 'add_skyverge_payment_method' ), 10, 2 );

		// Hook for adding payment methods from My Account (credit card).
		add_action( 'wc_payment_gateway_braintree_credit_card_payment_method_added', array( $integration, 'add_my_account_skyverge_payment_method' ), 10, 3 );

		// Hook for processing payments during checkout (PayPal).
		add_action( 'wc_payment_gateway_braintree_paypal_payment_processed', array( $integration, 'add_skyverge_payment_method' ), 10, 2 );

		// Hook for adding payment methods from My Account (PayPal).
		add_action( 'wc_payment_gateway_braintree_paypal_payment_method_added', array( $integration, 'add_my_account_skyverge_payment_method' ), 10, 3 );

		// Hook for displaying notice after saving payment method.
		add_action( 'wc_payment_gateway_braintree_credit_card_payment_method_added', array( $integration, 'after_save_braintree_credit_card_payment_method_notice' ), 10, 3 );

		// Hook for deleting credit card payment methods (Skyverge action: $token_id, $user_id).
		add_action( 'wc_payment_gateway_braintree_credit_card_payment_method_deleted', array( $integration, 'handle_skyverge_payment_method_deleted' ), 10, 2 );

		// Hook for deleting PayPal payment methods (Skyverge action: $token_id, $user_id).
		add_action( 'wc_payment_gateway_braintree_paypal_payment_method_deleted', array( $integration, 'handle_skyverge_payment_method_deleted' ), 10, 2 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_braintree_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
