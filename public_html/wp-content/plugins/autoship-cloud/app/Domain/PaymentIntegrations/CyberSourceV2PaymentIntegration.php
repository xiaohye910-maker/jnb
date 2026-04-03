<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Cyber Source V2 payment integration.
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
 * Represents the Cyber Source V2 payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class CyberSourceV2PaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'cybersource_credit_card',
	);

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'CyberSourceV2 Payment Integration', 'Initializing CyberSourceV2 payment integration.' );
		}
	}

	/**
	 * Builds the Cyber Source V2 payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return CyberSourceV2PaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): CyberSourceV2PaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::CYBER_SOURCE_V2 );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'test' === ( $settings['environment'] ?? '' ) ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['test_merchant_id'] ?? '' );
			$integration->set_api_key_1( $settings['test_api_key'] ?? '' );
			$integration->set_api_key_2( $settings['sandbox_private_key'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['merchant_id'] ?? '' );
			$integration->set_api_key_1( $settings['api_key'] ?? '' );
			$integration->set_api_key_2( $settings['api_shared_secret'] ?? '' );
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
		if ( PaymentMethodType::CYBER_SOURCE_V2 !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// CyberSourceV2 should contain the merchant id, the api key and the shared secret.
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
		$token_string = $order->get_meta( '_wc_cybersource_credit_card_payment_token' );
		$customer_id  = $order->get_meta( '_wc_cybersource_credit_card_customer_id' );

		if ( ! empty( $token_string ) && ! empty( $customer_id ) ) {
			$payment_data = new QPilotPaymentData();
			$card_type    = $order->get_meta( '_wc_cybersource_credit_card_card_type' );
			$last_four    = $order->get_meta( '_wc_cybersource_credit_card_account_four' );

			// Cybersource Stores Expiration in YY-MM format so we need to adjust for Autoship
			// Expiration in format 23-02 should be 0223.
			$expiry_date = $order->get_meta( '_wc_cybersource_credit_card_card_expiry_date' );
			$expiration  = explode( '-', $expiry_date );

			$payment_data->description = isset( $expiration[1] ) ? sprintf( '%s ending in %s (expires %s)', ucfirst( $card_type ), $last_four, $expiration[1] . '/' . $expiration[0] ) : sprintf( '%s ending in %s', ucfirst( $card_type ), $last_four );

			$payment_data->type                = 'CybersourceV2';
			$payment_data->gateway_payment_id  = $token_string;
			$payment_data->gateway_customer_id = $customer_id;
			$payment_data->last_four           = $last_four;
			$payment_data->expiration          = isset( $expiration[1] ) ? $expiration[1] . $expiration[0] : null;

			return $payment_data;
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
		// Apply the test filters.
		$test_ext = apply_filters( 'cybersource_cc_payment_method_sandbox_metadata_field_test_ext', '_test', 'cybersource_credit_card' );
		$meta_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_ext', '', $test_ext, 'cybersource_credit_card' );

		// Cybersource uses the customer id from user meta as Gateway Customer ID.
		$user_id                                  = $token->get_user_id();
		$payment_method_data['gatewayCustomerId'] = get_user_meta( $user_id, 'wc_cybersource_customer_id' . $meta_ext, true );

		return $payment_method_data;
	}

	/**
	 * Delete payment method validation for CyberSource V2.
	 *
	 * @param bool             $valid Current validation status.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @param object           $method The QPilot payment method.
	 * @return bool Whether the deletion is valid.
	 */
	public function delete_payment_method( bool $valid, string $type, WC_Payment_Token $token, object $method ): bool {
		// CyberSourceV2 matches on both customer ID and payment ID.
		if ( 'CybersourceV2' === $type ) {
			$user_id  = $token->get_user_id();
			$test_ext = apply_filters( 'cybersource_cc_payment_method_sandbox_metadata_field_test_ext', '_test', 'cybersource_credit_card' );
			$meta_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_ext', '', $test_ext, 'cybersource_credit_card' );

			$customer_id = get_user_meta( $user_id, 'wc_cybersource_customer_id' . $meta_ext, true );

			return $method->gatewayCustomerId === $customer_id && $method->gatewayPaymentId === $token->get_token(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $valid;
	}

	/**
	 * Delete payment method validation for standard CyberSource (V1).
	 *
	 * Standard CyberSource doesn't use the Gateway Customer ID for matching;
	 * it matches only on gatewayPaymentId.
	 *
	 * @param bool             $valid Current validation status.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @param object           $method The QPilot payment method.
	 * @return bool Whether the deletion is valid.
	 */
	public function delete_cybersource_v1_payment_method( bool $valid, string $type, WC_Payment_Token $token, object $method ): bool {
		// Standard CyberSource doesn't use the Gateway Customer ID for matching.
		return ( 'CyberSource' === $type ) ? ( $method->gatewayPaymentId === $token->get_token() ) : $valid; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Add payment method data for standard CyberSource (V1).
	 *
	 * Standard CyberSource doesn't use the gatewayCustomerId.
	 *
	 * @param array            $payment_method_data The payment method data.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @return array The modified payment method data.
	 */
	public function add_cybersource_v1_payment_method( array $payment_method_data, string $type, WC_Payment_Token $token ): array {
		// Standard CyberSource doesn't use the gatewayCustomerId.
		$payment_method_data['gatewayCustomerId'] = null;

		return $payment_method_data;
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
		$transaction_data = json_decode( $payment_meta['authorization'] );

		$exp_year  = ! empty( $payment_method['expiration'] ) && ( strlen( $payment_method['expiration'] ) > 2 ) ? substr( $payment_method['expiration'], - 2 ) : $payment_method['expiration'];
		$exp_month = ! empty( $payment_method['expiration'] ) && ( strlen( $payment_method['expiration'] ) > 2 ) ? substr( $payment_method['expiration'], 0, 2 ) : $payment_method['expiration'];
		$metadata  = array(
			'_wc_cybersource_credit_card_trans_id'           => $transaction_data->id,
			'_wc_cybersource_credit_card_processor_transaction_id' => $transaction_data->processorInformation->transactionId, // phpcs:ignore
			'_wc_cybersource_credit_card_authorization_code' => $transaction_data->processorInformation->approvalCode, // phpcs:ignore
			'_wc_cybersource_credit_card_reconciliation_id'  => $transaction_data->reconciliationId, // phpcs:ignore
			'_wc_cybersource_credit_card_customer_id'        => $payment_method['gatewayCustomerId'],
			'_wc_cybersource_credit_card_account_four'       => $payment_method['lastFourDigits'],
			'_wc_cybersource_credit_card_card_expiry_date'   => $exp_year . '-' . $exp_month,
			'_wc_cybersource_credit_card_charge_captured'    => 'yes',
		);

		$order->set_transaction_id( $transaction_data->id );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();

		return $payment_meta;
	}

	/**
	 * Display apply to all orders button for CyberSource.
	 *
	 * @param array $list_item The list item data.
	 * @param mixed $payment_token The payment token (SkyVerge payment profile).
	 * @return array The modified list item.
	 */
	public function display_apply_to_all_orders_button( array $list_item, $payment_token ): array {
		return autoship_display_apply_payment_method_to_all_scheduled_orders_skyverge_btn( $list_item, $payment_token, null, 'cybersource_credit_card' );
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
}
