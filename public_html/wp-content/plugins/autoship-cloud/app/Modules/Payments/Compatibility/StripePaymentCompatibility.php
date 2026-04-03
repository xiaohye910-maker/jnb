<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Stripe Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\StripePaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Stripe payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.11.0
 */
class StripePaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'stripe';
		$this->gateway_name = 'Stripe';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Stripe.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Stripe payment integration instance.
	 *
	 * @return StripePaymentIntegration
	 */
	private function get_integration(): ?StripePaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'stripe' );
		} catch ( \Exception $e ) {
			Logger::error( 'Stripe Payment Compatibility', 'Error getting Stripe payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Stripe.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof StripePaymentIntegration ) ) {
			Logger::trace( 'Stripe Payment Compatibility', 'Unable to register stripe compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_Stripe_payment_method', 'autoship_add_stripe_payment_method', 10 );
		remove_filter( 'autoship_delete_Stripe_payment_method_qpilot_match', 'autoship_delete_stripe_payment_method', 10 );
		remove_filter( 'wc_stripe_force_save_source', 'autoship_stripe_force_save' );
		remove_filter( 'wc_stripe_generate_create_intent_request', 'autoship_stripe_intent_request_filter', 30 );
		remove_filter( 'autoship_create_scheduled_order_data', 'autoship_stripe_link_order_mandate_data', 10 );
		remove_filter( 'autoship_delete_tokenized_payment_method_gateway_id', 'autoship_filter_deleted_payment_method_gateway_ids', 10 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Stripe_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for deleting payment methods from QPilot.
		add_filter( 'autoship_delete_Stripe_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );

		// Stripe force save source filter.
		add_filter( 'wc_stripe_force_save_source', array( StripePaymentIntegration::class, 'force_save_source' ) );

		// Stripe intent request filter.
		add_filter( 'wc_stripe_generate_create_intent_request', array( StripePaymentIntegration::class, 'filter_intent_request' ), 30, 2 );

		// Stripe Link order mandate data filter.
		add_filter( 'autoship_create_scheduled_order_data', array( StripePaymentIntegration::class, 'add_link_mandate_data' ), 10, 2 );

		// Modifies the Payment Gateway ID for gateways treated like other gateways.
		add_filter( 'autoship_delete_tokenized_payment_method_gateway_id', array( StripePaymentIntegration::class, 'autoship_filter_deleted_payment_method_gateway_ids' ), 10, 2 );
	}

	/**
	 * Register action hooks for Stripe.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof StripePaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'autoship_update_scheduled_orders_on_processing_stripe_gateway', 'autoship_add_adjusted_gateway_metadata_stripe', 10 );
		remove_action( 'autoship_update_scheduled_orders_on_processing_stripe_gateway', 'autoship_add_adjusted_gateway_metadata_fees_stripe', 11 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_stripe_gateway', array( $integration, 'add_adjusted_gateway_metadata' ), 10, 3 );

		// Hook for adding fees metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_stripe_gateway', array( $integration, 'add_adjusted_gateway_metadata_fees' ), 11, 3 );
	}
}
