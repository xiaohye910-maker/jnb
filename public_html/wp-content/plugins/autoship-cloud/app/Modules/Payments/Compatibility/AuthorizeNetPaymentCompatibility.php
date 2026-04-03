<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Authorize.NET Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\AuthorizeNetPaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Authorize.NET payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.11.0
 */
class AuthorizeNetPaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'authorize_net_cim_credit_card';
		$this->gateway_name = 'Authorize.NET CIM';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Authorize.NET.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Authorize.NET payment integration instance.
	 *
	 * @return ?AuthorizeNetPaymentIntegration
	 */
	private function get_integration(): ?AuthorizeNetPaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'authorize_net_cim_credit_card' );
		} catch ( \Exception $e ) {
			Logger::error( 'Authorize.NET Payment Compatibility', 'Error getting Authorize.NET payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Authorize.NET.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof AuthorizeNetPaymentIntegration ) ) {
			Logger::trace( 'Authorize.NET Payment Compatibility', 'Unable to register Authorize.NET compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'wc_payment_gateway_authorize_net_cim_credit_card_add_payment_method_transaction_result', 'autoship_add_authorize_net_payment_method', 10 );
		remove_filter( 'autoship_add_AuthorizeNet_payment_method', 'autoship_add_authorize_net_payment_method_data', 10 );
		remove_filter( 'autoship_delete_AuthorizeNet_payment_method_qpilot_match', 'autoship_delete_authorizenet_payment_method', 10 );
		remove_filter( 'wc_authorize_net_cim_my_payment_methods_table_method_actions', 'autoship_display_apply_payment_method_to_all_scheduled_orders_authorize_btn', 10 );

		// Hook for adding payment method transaction result.
		add_filter( 'wc_payment_gateway_authorize_net_cim_credit_card_add_payment_method_transaction_result', array( $integration, 'add_transaction_payment_method' ), 10, 4 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_AuthorizeNet_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_AuthorizeNet_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );

		// Hook for displaying the apply to all orders button.
		add_filter( 'wc_authorize_net_cim_my_payment_methods_table_method_actions', array( $integration, 'display_apply_to_all_orders_button' ), 10, 3 );
	}

	/**
	 * Register action hooks for Authorize.NET.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof AuthorizeNetPaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'wc_payment_gateway_authorize_net_cim_credit_card_payment_processed', 'autoship_add_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_authorize_net_cim_credit_card_payment_method_added', 'autoship_add_my_account_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_authorize_net_cim_credit_card_payment_method_added', 'autoship_after_save_authorize_net_payment_method_notice', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_authorize_net_cim_credit_card_gateway', 'autoship_add_adjusted_gateway_metadata_authnet', 10 );

		// Hook for processing payments during checkout.
		add_action( 'wc_payment_gateway_authorize_net_cim_credit_card_payment_processed', array( $integration, 'add_skyverge_payment_method' ), 10, 2 );

		// Hook for adding payment methods from My Account.
		add_action( 'wc_payment_gateway_authorize_net_cim_credit_card_payment_method_added', array( $integration, 'add_my_account_skyverge_payment_method' ), 10, 3 );

		// Hook for displaying notice after saving payment method.
		add_action( 'wc_payment_gateway_authorize_net_cim_credit_card_payment_method_added', array( $integration, 'after_save_authorize_net_payment_method_notice' ), 10, 3 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_authorize_net_cim_credit_card_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
