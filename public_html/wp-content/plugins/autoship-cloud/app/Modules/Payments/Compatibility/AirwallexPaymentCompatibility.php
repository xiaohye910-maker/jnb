<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Airwallex Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.12.1
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\PaymentIntegrations\AirwallexPaymentIntegration;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Backward compatibility layer for Airwallex payment gateway.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.12.1
 */
class AirwallexPaymentCompatibility extends AbstractPaymentCompatibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->gateway_id   = 'airwallex_card';
		$this->gateway_name = 'Airwallex';
		$this->enabled      = true;
	}

	/**
	 * Initialize backward compatibility for Airwallex.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_filter_hooks();
		$this->register_action_hooks();
	}

	/**
	 * Get the Airwallex payment integration instance.
	 *
	 * @return ?AirwallexPaymentIntegration
	 */
	private function get_integration(): ?AirwallexPaymentIntegration {
		try {
			$container = Plugin::get_service_container();
			$registry  = $container->get( PaymentGatewayRegistry::class );

			return $registry->get_gateway( 'airwallex_card' );
		} catch ( \Exception $e ) {
			Logger::error( 'Airwallex Payment Compatibility', 'Error getting Airwallex payment integration: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Register filter hooks for Airwallex.
	 *
	 * @return void
	 */
	protected function register_filter_hooks(): void {
		$integration = $this->get_integration();
		if ( ! ( $integration instanceof AirwallexPaymentIntegration ) ) {
			Logger::trace( 'Airwallex Payment Compatibility', 'Unable to register Airwallex compatibility filter hooks. The integration is not available' );

			return;
		}

		// Remove legacy hooks to prevent duplicate processing.
		remove_filter( 'autoship_add_Airwallex_payment_method', 'autoship_add_airwallex_payment_method', 10 );
		remove_filter( 'autoship_delete_Airwallex_payment_method_qpilot_match', 'autoship_delete_airwallex_payment_method', 10 );

		// Legacy hooks for adding payment methods to QPilot.
		add_filter( 'autoship_add_Airwallex_payment_method', array( $integration, 'add_payment_method' ), 10, 3 );

		// Legacy hooks for validating payment method deletion in QPilot.
		add_filter( 'autoship_delete_Airwallex_payment_method_qpilot_match', array( $integration, 'delete_payment_method' ), 10, 4 );
	}

	/**
	 * Register action hooks for Airwallex.
	 *
	 * @return void
	 */
	protected function register_action_hooks(): void {
		// Airwallex does not have specific action hooks for metadata.
	}
}
