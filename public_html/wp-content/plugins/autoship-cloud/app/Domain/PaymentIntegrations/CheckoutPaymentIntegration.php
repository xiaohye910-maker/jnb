<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Checkout.com payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\AbstractPaymentGateway;
use Autoship\Domain\PaymentMethodType;
use Autoship\Services\Logging\Logger;
use QPilotPaymentData;
use WC_Order;
use WC_Payment_Token;

/**
 * Represents the Checkout.com payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class CheckoutPaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'wc_checkout_com_cards',
	);

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Checkout Payment Integration', 'Initializing Checkout.com payment integration.' );
		}
	}

	/**
	 * Builds a Checkout.com payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return CheckoutPaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): CheckoutPaymentIntegration {
		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::CHECKOUT );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		// Checkout.com uses the same API keys for both environments;
		// the environment setting on their plugin controls the routing.
		$integration->set_api_key_1( $settings['ckocom_pk'] ?? '' );
		$integration->set_api_key_2( $settings['ckocom_sk'] ?? '' );
		$integration->set_test_mode( 'sandbox' === ( $settings['ckocom_environment'] ?? '' ) );

		return $integration;
	}

	/**
	 * Gets the value indicating if the payment integration is valid or not.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		if ( PaymentMethodType::CHECKOUT !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// Checkout should contain the pk and the sk.
		if ( empty( $this->get_api_key_1() ) || empty( $this->get_api_key_2() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get order payment data for QPilot.
	 *
	 * @param int      $order_id The order ID.
	 * @param WC_Order $order The order object.
	 *
	 * @return ?QPilotPaymentData The payment data.
	 */
	public function get_order_payment_data( int $order_id, WC_Order $order ): ?QPilotPaymentData {
		// Retrieve the Payment ID ( i.e. transaction id ).
		$transaction_id = $order->get_meta( '_cko_payment_id' );

		// Retrieve the Token Info from the API.
		$data = autoship_get_checkout_com_charge_by_transaction_id( $transaction_id );

		if ( ! empty( $data ) && isset( $data['token'] ) ) {

			// Get the token from Woo Token Tables.
			$token = autoship_get_related_tokenized_id( $data['token'] );

			if ( ! empty( $token ) ) {
				$expiration                       = $token->get_expiry_month() . substr( $token->get_expiry_year(), - 2 );
				$payment_data                     = new QPilotPaymentData();
				$payment_data->description        = $token->get_display_name();
				$payment_data->type               = 'Checkout';
				$payment_data->gateway_payment_id = $token->get_token();
				$payment_data->last_four          = $token->get_last4();
				$payment_data->expiration         = $expiration;

				return $payment_data;
			}
		}

		return null;
	}

	/**
	 * Add payment method data for QPilot.
	 *
	 * @param array            $payment_method_data The payment method data.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @return array The modified payment method data.
	 */
	public function add_payment_method( array $payment_method_data, string $type, WC_Payment_Token $token ): array {
		// Checkout.com doesn't store the customer id.
		$payment_method_data['gatewayCustomerId'] = null;

		return $payment_method_data;
	}

	/**
	 * Delete payment method validation.
	 *
	 * @param bool             $valid Current validation status.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @param object           $method The QPilot payment method.
	 * @return bool Whether the deletion is valid.
	 */
	public function delete_payment_method( bool $valid, string $type, WC_Payment_Token $token, object $method ): bool {
		return 'Checkout' === $type ? $method->gatewayPaymentId === $token->get_token() : $valid; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Add metadata to payment.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @param array    $payment_method The payment method.
	 * @return array The updated payment metadata.
	 */
	public function add_metadata( array $payment_meta, WC_Order $order, array $payment_method ): array {
		$metadata = array(
			'_cko_payment_id'        => $payment_meta['Id'],
			'cko_payment_authorized' => true,
			'cko_payment_captured'   => true,
		);

		$order->set_transaction_id( $payment_meta['action_id'] );
		$order->set_payment_method( 'wc_checkout_com_cards' );
		$order->set_payment_method_title( 'Pay by Card with Checkout.com' );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();

		return $payment_meta;
	}
}
