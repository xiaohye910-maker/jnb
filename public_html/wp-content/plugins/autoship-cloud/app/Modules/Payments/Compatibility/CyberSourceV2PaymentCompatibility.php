<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * CyberSourceV2 Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\CyberSourceV2PaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for CyberSourceV2 payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class CyberSourceV2PaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'cybersource_credit_card';
		$this->gateway_name = 'CyberSourceV2';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for CyberSourceV2.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the CyberSourceV2 payment integration instance.
	 *
	 * @return ?CyberSourceV2PaymentIntegration
	 */
	private function get_integration(): ?CyberSourceV2PaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'cybersource_credit_card' );
		} catch ( \Exception $e ) {
			Logger::error( 'CyberSourceV2 Payment Compatibility', 'Error getting CyberSourceV2 payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for CyberSourceV2.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof CyberSourceV2PaymentIntegration ) ) {
			Logger::trace( 'CyberSourceV2 Payment Compatibility', 'Unable to register CyberSourceV2 compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_CyberSource_payment_method', 'autoship_add_CyberSource_payment_method', 10 );
		remove_filter( 'autoship_add_CyberSource_payment_method', 'autoship_add_cybersource_cc_payment_method_data', 10 );
		remove_filter( 'autoship_add_CyberSource_payment_method', 'autoship_add_cybersource_payment_method', 10 );
		remove_filter( 'autoship_add_CyberSourceV2_payment_method', 'autoship_add_cybersource_credit_card_payment_method', 10 );
		remove_filter( 'autoship_delete_CyberSource_payment_method_qpilot_match', 'autoship_delete_cybersource_payment_method', 10 );
		remove_filter( 'wc_cybersource_credit_card_my_payment_methods_table_method_actions', 'autoship_display_apply_payment_method_to_all_scheduled_orders_cybersource_cc_btn', 10 );

		// Standard CyberSource (V1) — doesn't use gatewayCustomerId.
		add_filter( 'autoship_add_CyberSource_payment_method', array( $integration, 'add_cybersource_v1_payment_method' ), 10, 3 );

		// CyberSource V2 — uses gatewayCustomerId from user meta.
		add_filter( 'autoship_add_CyberSourceV2_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Standard CyberSource (V1) deletion — matches on gatewayPaymentId only.
		add_filter( 'autoship_delete_CyberSource_payment_method_qpilot_match', array( $integration, 'delete_cybersource_v1_payment_method' ), 10, 4 );

		// Hook for displaying the apply to all orders button.
		add_filter( 'wc_cybersource_credit_card_my_payment_methods_table_method_actions', array( $integration, 'display_apply_to_all_orders_button' ), 10, 3 );
	}

	/**
	 * Register action hooks for CyberSourceV2.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof CyberSourceV2PaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'wc_payment_gateway_cybersource_credit_card_payment_processed', 'autoship_add_skyverge_payment_method', 10 );
		remove_action( 'wc_payment_gateway_cybersource_credit_card_payment_method_added', 'autoship_add_my_account_skyverge_payment_method', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_cybersource_credit_card_gateway', 'autoship_add_adjusted_gateway_metadata_cybersource_cc', 10 );

		// Hook for processing payments during checkout.
		add_action( 'wc_payment_gateway_cybersource_credit_card_payment_processed', array( $integration, 'add_skyverge_payment_method' ), 10, 2 );

		// Hook for adding payment methods from My Account.
		add_action( 'wc_payment_gateway_cybersource_credit_card_payment_method_added', array( $integration, 'add_my_account_skyverge_payment_method' ), 10, 3 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_cybersource_credit_card_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
