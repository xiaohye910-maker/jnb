<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Sage payment integration.
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
use WC_Payment_Token_CC;
use WC_Payment_Tokens;

/**
 * Represents the Sage payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class SagePaymentIntegration extends AbstractPaymentGateway {

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Sage Payment Integration', 'Initializing Sage payment integration.' );
		}
	}

	/**
	 * Builds a Sage payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return SagePaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): SagePaymentIntegration {
		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::SAGE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );
		$integration->set_test_mode( true );

		return $integration;
	}

	/**
	 * Gets the value indicating if the payment integration is valid or not.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		// This enforces the payment integration to be disabled.
		return false;
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
		// Grab the Customer ID and Token from the order.
		$token_id = $order->get_meta( '_SagePayDirectToken' );

		if ( ! empty( $token_id ) ) {

			$token = autoship_get_related_tokenized_id( $token_id );

			if ( ! empty( $token ) ) {

				$payment_data                     = new QPilotPaymentData();
				$payment_data->description        = $token->get_display_name();
				$payment_data->type               = 'Sage';
				$payment_data->gateway_payment_id = $token_id;
				$payment_data->last_four          = $token->get_last4();

				// Get Expiration in MMYY format for Qpilot.
				$expiration               = $token->get_expiry_month() . substr( $token->get_expiry_year(), - 2 );
				$payment_data->expiration = $expiration;

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
		// SagePay Doesn't require a Gateway Customer ID.
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
		return ( 'Sage' === $type ) ? ( $method->gatewayPaymentId === $token->get_token() ) : $valid; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
			'_VendorTxCode' => $payment_meta['VendorTxCode'],
		);

		$order->set_payment_method( 'sagepaydirect' );
		$order->set_payment_method_title( 'Credit Card via Sage' );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();

		return $payment_meta;
	}

	/**
	 * Checkout order patch to store token in order meta for saved payment methods.
	 *
	 * @param int      $order_id The WC Order number.
	 * @param array    $posted_data The posted checkout data.
	 * @param WC_Order $order The WC Order object.
	 *
	 * @return void
	 */
	public static function checkout_order_patch( $order_id, $posted_data, $order ) {
		// Only apply patch to Autoship Orders that used the SagePay direct gateway.
		$payment_method_id = isset( $posted_data['payment_method'] ) ? wc_clean( wp_unslash( $posted_data['payment_method'] ) ) : false;

		if ( 'sagepaydirect' !== $payment_method_id ) {
			return;
		}

		// Process order items and remove non-autoship items.
		// If empty this order does not have scheduled items.
		$order_items = $order->get_items();
		if ( empty( autoship_group_order_items( $order_items ) ) ) {
			return;
		}

		// Check for the SagePay Posted info and see if this is a saved or new payment method.
		$sage_card_token = isset( $_POST['wc-sagepaydirect-payment-token'] ) && ! empty( $_POST['wc-sagepaydirect-payment-token'] ) ? wc_clean( $_POST['wc-sagepaydirect-payment-token'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( 'new' !== $sage_card_token && $sage_card_token ) {
			$token = WC_Payment_Tokens::get( $sage_card_token );
			$order->update_meta_data( '_SagePayDirectToken', $token->get_token() );
			$order->save();
		}
	}
}
