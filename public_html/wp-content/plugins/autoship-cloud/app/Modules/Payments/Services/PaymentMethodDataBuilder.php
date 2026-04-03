<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Method Data Builder
 *
 * Transforms WooCommerce payment tokens into QPilot payment method data arrays.
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Services;

use Autoship\Services\Logging\Logger;
use WC_Payment_Token;

/**
 * Builds QPilot payment method data from WooCommerce payment tokens.
 *
 * Extracts the data-gathering logic from the legacy autoship_add_general_payment_method()
 * function in src/payments.php into a dedicated, testable class.
 *
 * @package Autoship\Modules\Payments\Services
 * @since 2.11.0
 */
class PaymentMethodDataBuilder {

	/**
	 * Build QPilot payment method data from a WooCommerce payment token.
	 *
	 * Extracts token metadata (type, expiration, last4, gateway IDs) and merges
	 * with customer billing data to produce the array expected by QPilot's
	 * upsert_payment_method API.
	 *
	 * @param WC_Payment_Token $token The WooCommerce payment token.
	 *
	 * @return array The payment method data array ready for QPilot, or empty array on failure.
	 */
	public function build( WC_Payment_Token $token ): array {
		$wc_customer_id = $token->get_user_id();

		// Check if the customer exists in QPilot (will upsert if not).
		$customer = autoship_check_autoship_customer( $wc_customer_id, 'autoship_add_general_payment' );

		if ( ! $customer ) {
			return array();
		}

		$payment_method_id = $token->get_token();
		$gateway           = $token->get_gateway_id();
		$description       = apply_filters( 'autoship_add_general_payment_method_description', $token->get_display_name(), $token );
		$type              = autoship_get_valid_payment_method_type( $gateway );
		$expiration        = $this->get_expiration( $token );
		$last_four_digits  = $this->get_last_four( $token, $gateway );

		$data = array(
			'type'              => $type,
			'expiration'        => $expiration,
			'lastFourDigits'    => $last_four_digits,
			'gatewayCustomerId' => $wc_customer_id,
			'gatewayPaymentId'  => $payment_method_id,
			'description'       => $description,
		);

		// PayPal Payments adjustments.
		$data = $this->apply_paypal_adjustments( $data, $type, $gateway, $wc_customer_id );

		// Stripe Link adjustments.
		$data = $this->apply_stripe_link_adjustments( $data, $token, $type, $gateway );

		// Merge with customer billing data.
		$payment_method_data = autoship_get_general_payment_method_customer_data( $wc_customer_id, $data );

		// Apply filters for gateway-specific and general overrides.
		$payment_method_data = apply_filters( 'autoship_add_general_payment_method', $payment_method_data, $type, $token );
		$payment_method_data = apply_filters( "autoship_add_{$type}_payment_method", $payment_method_data, $type, $token );
		$payment_method_data = apply_filters( 'autoship_api_create_payment_method_data', $payment_method_data );

		if ( Logger::is_tracing_enabled() ) {
			$export_payment_data = var_export( $payment_method_data, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export

			Logger::trace( 'Autoship Payment Methods', "Sending Payment Method Data to QPilot. Type: '{$type}' Gateway: '{$gateway}' Data: {$export_payment_data}." );
		}

		return $payment_method_data;
	}

	/**
	 * Extract expiration from a token if available.
	 *
	 * Some token types (e.g. SEPA) don't have expiration methods.
	 *
	 * @param WC_Payment_Token $token The payment token.
	 *
	 * @return string|null The expiration in MMYY format, or null.
	 */
	private function get_expiration( WC_Payment_Token $token ): ?string {
		if ( is_callable( array( $token, 'get_expiry_month' ) ) && is_callable( array( $token, 'get_expiry_year' ) ) ) {
			return $token->get_expiry_month() . substr( $token->get_expiry_year(), -2 );
		}

		return null;
	}

	/**
	 * Extract last four digits from a token if available.
	 *
	 * PayPal gateway tokens (ppcp-gateway) are excluded from last4 extraction.
	 *
	 * @param WC_Payment_Token $token The payment token.
	 * @param string           $gateway The gateway ID.
	 *
	 * @return string|null The last four digits, or null.
	 */
	private function get_last_four( WC_Payment_Token $token, string $gateway ): ?string {
		if ( method_exists( $token, 'get_last4' ) && is_callable( array( $token, 'get_last4' ) ) && 'ppcp-gateway' !== $gateway ) {
			return $token->get_last4();
		}

		return null;
	}

	/**
	 * Apply PayPal V3 specific adjustments to payment method data.
	 *
	 * @param array  $data The payment method data.
	 * @param string $type The QPilot payment method type.
	 * @param string $gateway The WC gateway ID.
	 * @param int    $wc_customer_id The WC customer ID.
	 *
	 * @return array The adjusted data.
	 */
	private function apply_paypal_adjustments( array $data, string $type, string $gateway, int $wc_customer_id ): array {
		if ( 'PayPalV3' !== $type ) {
			return $data;
		}

		if ( 'ppcp-credit-card-gateway' === $gateway ) {
			$data['gatewayPaymentType'] = 26;
		}

		if ( 'ppcp-gateway' === $gateway ) {
			$data['gatewayPaymentType'] = 25;
			$data['description']        = 'PayPal Payments';
		}

		$gateway_customer_id = get_user_meta( $wc_customer_id, '_ppcp_target_customer_id', true );
		if ( ! $gateway_customer_id ) {
			$gateway_customer_id = get_user_meta( $wc_customer_id, 'ppcp_customer_id', true );
		}

		if ( $gateway_customer_id ) {
			$data['gatewayCustomerId'] = $gateway_customer_id;
		}

		return $data;
	}

	/**
	 * Apply Stripe Link specific adjustments to payment method data.
	 *
	 * @param array            $data The payment method data.
	 * @param WC_Payment_Token $token The payment token.
	 * @param string           $type The QPilot payment method type.
	 * @param string           $gateway The WC gateway ID.
	 *
	 * @return array The adjusted data.
	 */
	private function apply_stripe_link_adjustments( array $data, WC_Payment_Token $token, string $type, string $gateway ): array {
		if ( is_a( $token, 'WC_Payment_Token_Link' ) && 'Stripe' === $type && 'stripe' === $gateway ) {
			$data['gatewayPaymentType'] = 32;
		}

		return $data;
	}
}
