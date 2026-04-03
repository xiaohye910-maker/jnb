<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Braintree payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\AbstractPaymentGateway;
use Autoship\Domain\PaymentMethodType;
use Autoship\Services\Logging\Logger;
use Exception;
use QPilotPaymentData;
use WC_Order;
use WC_Payment_Token;

/**
 * Represents the Braintree payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class BraintreePaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'braintree_credit_card',
		'braintree_paypal',
	);

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Braintree Payment Integration', 'Initializing Braintree payment integration.' );
		}
	}

	/**
	 * Builds a Braintree payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return BraintreePaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): BraintreePaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::BRAINTREE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'sandbox' === ( $settings['environment'] ?? '' ) ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['sandbox_merchant_id'] ?? '' );
			$integration->set_api_key_1( $settings['sandbox_public_key'] ?? '' );
			$integration->set_api_key_2( $settings['sandbox_private_key'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['merchant_id'] ?? '' );
			$integration->set_api_key_1( $settings['public_key'] ?? '' );
			$integration->set_api_key_2( $settings['private_key'] ?? '' );
			$integration->set_test_mode( false );
		}

		return $integration;
	}

	/**
	 * Gets the value indicating if the payment integration is valid or not.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		if ( PaymentMethodType::BRAINTREE !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// Braintree should contain the merchant id, the public key and the private key.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) || empty( $this->get_api_key_2() ) ) {
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
		$payment_method = $order->get_payment_method();

		if ( 'braintree_paypal' === $payment_method ) {
			return $this->get_braintree_paypal_order_payment_data( $order );
		}

		return $this->get_braintree_credit_card_order_payment_data( $order );
	}

	/**
	 * Get Braintree Credit Card order payment data.
	 *
	 * @param WC_Order $order The order object.
	 * @return ?QPilotPaymentData The payment data.
	 */
	private function get_braintree_credit_card_order_payment_data( WC_Order $order ): ?QPilotPaymentData {
		$token_string = $order->get_meta( '_wc_braintree_credit_card_payment_token' );
		$customer_id  = $order->get_meta( '_wc_braintree_credit_card_customer_id' );

		if ( ! empty( $token_string ) && ! empty( $customer_id ) ) {
			$payment_data = new QPilotPaymentData();
			$card_type    = $order->get_meta( '_wc_braintree_credit_card_card_type' );
			$last_four    = $order->get_meta( '_wc_braintree_credit_card_account_four' );

			// Expiration in 2025-02 format.
			$expiry_date   = $order->get_meta( '_wc_braintree_credit_card_card_expiry_date' );
			$expiration    = explode( '-', $expiry_date );
			$expiration[0] = strlen( $expiration[0] ) > 2 ? substr( $expiration[0], -2 ) : $expiration[0];

			$payment_data->description         = isset( $expiration[1] ) ? sprintf( '%s ending in %s (expires %s)', ucfirst( $card_type ), $last_four, $expiration[1] . '/' . $expiration[0] ) : sprintf( '%s ending in %s', ucfirst( $card_type ), $last_four );
			$payment_data->type                = 'Braintree';
			$payment_data->gateway_payment_id  = $token_string;
			$payment_data->gateway_customer_id = $customer_id;
			$payment_data->last_four           = $last_four;
			$payment_data->expiration          = isset( $expiration[1] ) ? $expiration[1] . $expiration[0] : null;

			return $payment_data;
		}

		return null;
	}

	/**
	 * Get Braintree PayPal order payment data.
	 *
	 * @param WC_Order $order The order object.
	 * @return ?QPilotPaymentData The payment data.
	 */
	private function get_braintree_paypal_order_payment_data( WC_Order $order ): ?QPilotPaymentData {
		$token_string       = $order->get_meta( '_wc_braintree_paypal_payment_token' );
		$paypal_customer_id = $order->get_meta( '_wc_braintree_paypal_customer_id' );

		if ( ! empty( $token_string ) ) {
			$payment_data                      = new QPilotPaymentData();
			$card_type                         = 'PayPal';
			$last_four                         = substr( $token_string, -4 );
			$payment_data->description         = sprintf( '%s ending in %s', ucfirst( $card_type ), $last_four );
			$payment_data->type                = 'Braintree';
			$payment_data->gateway_payment_id  = $token_string;
			$payment_data->gateway_customer_id = $paypal_customer_id;
			$payment_data->last_four           = $last_four;
			$payment_data->expiration          = null;

			return $payment_data;
		}

		return null;
	}

	/**
	 * Adds the Braintree credit card Payment Method to QPilot.
	 * Does not use the woocommerce_payment_tokens tables.
	 *
	 * @param array  $result The result array.
	 * @param object $response The API response object.
	 * @param object $order The WC Order object.
	 * @param object $client The Direct Gateway instance.
	 *
	 * @return mixed
	 */
	public function add_credit_card_payment_method_data( $result, $response, $order, $client ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$types = autoship_standard_gateway_id_types();

		if ( $response->transaction_approved() && ! isset( $types['braintree_credit_card'] ) ) {
			autoship_add_non_wc_token_payment_method( $response, $order, 'braintree_credit_card' );
		}

		return $result;
	}

	/**
	 * Adds the Braintree PayPal Payment Method to QPilot.
	 * Does not use the woocommerce_payment_tokens tables.
	 *
	 * @param array  $result The result array.
	 * @param object $response The API response object.
	 * @param object $order The WC Order object.
	 * @param object $client The Direct Gateway instance.
	 *
	 * @return mixed
	 */
	public function add_paypal_payment_method_data( $result, $response, $order, $client ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$types = autoship_standard_gateway_id_types();

		if ( $response->transaction_approved() && ! isset( $types['braintree_paypal'] ) ) {
			autoship_add_non_wc_token_payment_method( $response, $order, 'braintree_paypal' );
		}

		return $result;
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
		$card_type = method_exists( $token, 'get_card_type' ) ? $token->get_card_type() : '';
		$ext_type  = 'Paypal' == $card_type ? 'braintree_paypal' : 'braintree_credit_card'; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual

		// Apply the test filters.
		$test_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_test_ext', '_test', $ext_type );
		$meta_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_ext', '', $test_ext, $ext_type );

		// braintree_credit_card uses the customer id from user meta as Gateway Customer ID.
		$user_id                                  = $token->get_user_id();
		$payment_method_data['gatewayCustomerId'] = get_user_meta( $user_id, 'wc_braintree_customer_id' . $meta_ext, true );

		// Get the types to see if this is legacy or new Braintree PayPal.
		$types = autoship_standard_gateway_id_types();

		// Handle PayPal description.
		if ( ! isset( $types[ $ext_type ] ) && 'Paypal' == $card_type ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			// Update the Description for PayPal (legacy path).
			$payment_tokens                     = get_user_meta( $user_id, '_wc_braintree_paypal_payment_tokens' . $meta_ext, true );
			$email                              = $payment_tokens[ $token->get_token() ]['payer_email'];
			$payment_method_data['description'] = apply_filters( 'autoship_braintree_paypal_payment_method_description', sprintf( 'Paypal for %s', $email ) );
		} elseif ( 'Paypal' == $card_type ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			$email                              = $token->get_meta( 'payer_email' );
			$payment_method_data['description'] = apply_filters( 'autoship_braintree_paypal_payment_method_description', sprintf( 'Paypal for %s', $email ) );
		}

		return $payment_method_data;
	}

	/**
	 * Delete payment method validation for the QPilot match filter.
	 *
	 * Called by the `autoship_delete_Braintree_payment_method_qpilot_match` filter
	 * which passes 4 parameters: ($valid, $type, $token, $method).
	 *
	 * @param bool             $valid Current validation status.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @param object           $method The QPilot payment method.
	 * @return bool Whether the deletion is valid.
	 */
	public function delete_payment_method( bool $valid, string $type, WC_Payment_Token $token, object $method ): bool {
		$token_id = $token->get_token();
		$user_id  = $token->get_user_id();

		return autoship_delete_non_wc_token_payment_method( $token_id, null, 'Braintree', $user_id );
	}

	/**
	 * Handle Skyverge payment method deletion action.
	 *
	 * Called by the `wc_payment_gateway_braintree_*_payment_method_deleted` actions
	 * which pass 2 parameters: ($token_id, $user_id).
	 *
	 * @param string $token_id The token ID being deleted.
	 * @param int    $user_id The user ID.
	 * @return bool Whether the deletion succeeded.
	 */
	public function handle_skyverge_payment_method_deleted( $token_id, $user_id ) {
		return autoship_delete_non_wc_token_payment_method( $token_id, null, 'Braintree', $user_id );
	}

	/**
	 * Handles the SkyVerge add payment method transaction result.
	 * Adds the payment method to QPilot when a transaction is approved.
	 *
	 * @param WC_Order $order The WC Order object.
	 * @param object   $gateway The payment gateway instance.
	 */
	public function add_skyverge_payment_method( WC_Order $order, object $gateway ) {
		$types = autoship_standard_gateway_id_types();

		if ( ! isset( $types[ $gateway->id ] ) ) {
			return;
		}

		// Retrieve the Token based off the token id & run it through the partial token filter.
		$token = autoship_get_related_tokenized_id( $order->payment->token, true );

		// Check for failed token retrieval.
		if ( is_null( $token ) || empty( $token ) || ! $token ) {
			return;
		}

		$token = autoship_tokenize_non_fully_implemented_token_classes( $token );

		// Upsert the Token to the API.
		autoship_add_general_payment_method( $token );
	}

	/**
	 * Fires after a new Skyverge payment method is added by a customer.
	 *
	 * @param string $token new token.
	 */
	public function add_my_account_skyverge_payment_method( string $token ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( is_checkout() ) {
			return;
		}

		// Retrieve the Token based off the token id & run it through the partial token filter.
		$token = autoship_get_related_tokenized_id( $token, true );

		// Check for failed token retrieval.
		if ( is_null( $token ) || empty( $token ) || ! $token ) {
			return;
		}

		$token = autoship_tokenize_non_fully_implemented_token_classes( $token );

		autoship_add_general_payment_method( $token );
	}

	/**
	 * Braintree_credit_card Gateway: Fires additional autoship actions after a payment method is saved.
	 *
	 * @param string $token_id new token ID.
	 * @param int    $user_id user ID.
	 * @param object $response API response object.
	 */
	public function after_save_braintree_credit_card_payment_method_notice( $token_id, $user_id, $response ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$types = autoship_standard_gateway_id_types();

		if ( ! isset( $types['braintree_credit_card'] ) ) {
			autoship_after_save_payment_method_autoship_action_notice( $token_id, 'braintree_credit_card', '' );
		}
	}

	/**
	 * Braintree: Outputs the apply action button after each payment method.
	 *
	 * @param array  $list_item The list item array.
	 * @param object $payment_token The payment token object.
	 * @return array The modified list item.
	 */
	public function display_apply_to_all_orders_button( array $list_item, $payment_token ): array {
		$gateway = $payment_token->is_paypal_account() ? 'braintree_paypal' : 'braintree_credit_card';

		return autoship_display_apply_payment_method_to_all_scheduled_orders_skyverge_btn( $list_item, $payment_token, null, $gateway );
	}

	/**
	 * Add metadata for Braintree.
	 * Routes to credit card or PayPal based on the payment method description.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @param array    $payment_method The payment method.
	 * @return array The updated payment metadata.
	 */
	public function add_metadata( array $payment_meta, WC_Order $order, array $payment_method ): array {
		$paypal_flag = apply_filters( 'autoship_braintree_paypal_descriptor_flag', 'PayPal', $payment_method );
		$is_paypal   = apply_filters( 'autoship_is_paypal_payment_method_scheduled_order', false !== strpos( $payment_method['description'], $paypal_flag ), $payment_meta, $order, $payment_method );

		if ( $is_paypal ) {
			$this->add_adjusted_gateway_metadata_paypal( $payment_meta, $order );
		} else {
			$this->add_adjusted_gateway_metadata_credit_card( $payment_meta, $order );
		}

		return $payment_meta;
	}

	/**
	 * Add refund metadata for Braintree Credit Card.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	private function add_adjusted_gateway_metadata_credit_card( array $payment_meta, WC_Order $order ): void {
		$metadata = array();

		if ( isset( $payment_meta['Target'] ) && isset( $payment_meta['Target']['Id'] ) ) {
			$metadata['_wc_braintree_credit_card_trans_id'] = $payment_meta['Target']['Id'];
			$order->set_transaction_id( $payment_meta['Target']['Id'] );
		}

		$order->set_payment_method( 'braintree_credit_card' );
		$order->set_payment_method_title( 'Credit Card' );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();
	}

	/**
	 * Add refund metadata for Braintree PayPal.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	private function add_adjusted_gateway_metadata_paypal( array $payment_meta, WC_Order $order ): void {
		$metadata = array(
			'_wc_braintree_paypal_trans_id' => $payment_meta['Target']['Id'],
		);

		$order->set_payment_method( 'braintree_paypal' );
		$order->set_payment_method_title( 'PayPal' );
		$order->set_transaction_id( $payment_meta['Target']['Id'] );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();
	}
}
