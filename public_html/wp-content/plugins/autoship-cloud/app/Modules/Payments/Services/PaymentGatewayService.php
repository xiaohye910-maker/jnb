<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Gateway Service
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Services;

use Autoship\Domain\AbstractPaymentGateway;
use WC_Payment_Token;
use WC_Payment_Tokens;

/**
 * Main service for handling payment gateway operations.
 *
 * @package Autoship\Modules\PaymentGateways\Services
 * @since 3.0.0
 */
class PaymentGatewayService {

	/**
	 * Payment gateway registry.
	 *
	 * @var PaymentGatewayRegistry
	 */
	private PaymentGatewayRegistry $registry;

	/**
	 * Constructor.
	 *
	 * @param PaymentGatewayRegistry $registry The payment gateway registry.
	 */
	public function __construct( PaymentGatewayRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Handle new payment token creation. This method is injected via action registration on the Module.
	 *
	 * @param int $token_id The payment token ID.
	 * @return void
	 */
	public function handle_new_payment_token( int $token_id ): void {
		$token = WC_Payment_Tokens::get( $token_id );
		if ( ! $token ) {
			return;
		}

		$gateway = $this->registry->get_gateway( $token->get_gateway_id() );

		if ( $gateway instanceof AbstractPaymentGateway ) {
			// Process the payment method through the gateway.
			$this->process_payment_method( $gateway, $token );
		}
	}

	/**
	 * Get valid payment methods.
	 *
	 * @return array
	 */
	public function get_valid_payment_methods(): array {
		$valid_methods = array();
		$gateways      = $this->registry->get_all_gateways();

		foreach ( $gateways as $gateway ) {
			if ( $gateway->is_valid() ) {
				$valid_methods[] = array(
					'id'   => $gateway->get_method_id(),
					'name' => $gateway->get_method_name(),
					'type' => $gateway->get_method_type(),
				);
			}
		}

		return $valid_methods;
	}

	/**
	 * Process payment method through gateway.
	 *
	 * @param AbstractPaymentGateway $gateway The payment gateway.
	 * @param WC_Payment_Token       $token The payment token.
	 * @return void
	 */
	private function process_payment_method( AbstractPaymentGateway $gateway, WC_Payment_Token $token ): void {
		// Implementation for processing payment method.
	}

	/**
	 * Main catch for the update url. Old Code.
	 * Hooks into the Autoship Update Scheduled Orders Payment Method endpoint
	 * and updates the payment method on all orders info.
	 */
	public function update_payment_method_on_orders() {
		// Pending method.
	}
}
