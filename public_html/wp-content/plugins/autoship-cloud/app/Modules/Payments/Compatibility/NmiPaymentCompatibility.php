<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * NMI Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\NmiPaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for NMI payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class NmiPaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'nmi_gateway_woocommerce_credit_card';
		$this->gateway_name = 'NMI';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for NMI.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the NMI payment integration instance.
	 *
	 * @return ?NmiPaymentIntegration
	 */
	private function get_integration(): ?NmiPaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			// Try the primary gateway ID first, fallback to alternate.
			$integration = $registry->get_gateway( 'nmi_gateway_woocommerce_credit_card' );
			if ( null === $integration ) {
				$integration = $registry->get_gateway( 'nmi' );
			}

			return $integration;
		} catch ( \Exception $e ) {
			Logger::error( 'NMI Payment Compatibility', 'Error getting NMI payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for NMI.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof NmiPaymentIntegration ) ) {
			Logger::trace( 'NMI Payment Compatibility', 'Unable to register NMI compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'nmi_gateway_woocommerce_credit_card_should_force_tokenize', 'autoship_force_nmi_payment_tokenization', 10 );
		remove_filter( 'autoship_add_Nmi_payment_method', 'autoship_add_nmi_payment_method', 10 );
		remove_filter( 'autoship_delete_Nmi_payment_method_qpilot_match', 'autoship_delete_nmi_payment_method', 10 );

		// Force tokenization for NMI.
		add_filter( 'nmi_gateway_woocommerce_credit_card_should_force_tokenize', array( NmiPaymentIntegration::class, 'force_tokenization' ) );
		add_filter( 'wc_nmi_force_saved_card', array( NmiPaymentIntegration::class, 'force_tokenization' ) );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Nmi_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_Nmi_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for NMI.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof NmiPaymentIntegration ) ) {
			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_action( 'autoship_update_scheduled_orders_on_processing_nmi_gateway_woocommerce_credit_card_gateway', 'autoship_add_adjusted_gateway_metadata_nmi_gateway_woocommerce_credit_card', 10 );

		// Hook for adding gateway metadata during scheduled order updates.
		add_action( 'autoship_update_scheduled_orders_on_processing_nmi_gateway_woocommerce_credit_card_gateway', array( $integration, 'add_metadata' ), 10, 3 );
	}
}
