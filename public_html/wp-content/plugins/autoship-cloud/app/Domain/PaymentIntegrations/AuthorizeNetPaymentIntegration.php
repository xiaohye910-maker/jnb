<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents an Authorize.Net payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\AbstractPaymentGateway;
use Autoship\Services\Logging\Logger;
use Autoship\Domain\PaymentMethodType;
use Exception;
use QPilotPaymentData;
use WC_Order;
use WC_Payment_Token;

/**
 * Represents an Authorize.Net payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class AuthorizeNetPaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'authorize_net_cim_credit_card',
	);

	/**
	 * Initializes the payment integration.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Authorize.Net Payment Integration', 'Initializing Authorize.Net payment integration.' );
		}
	}

	/**
	 * Builds an Authorize.Net payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return AuthorizeNetPaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): AuthorizeNetPaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::AUTHORIZE_NET );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = $settings['environment'] ?? 'test';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['test_api_login_id'] ?? '' );
			$integration->set_api_key_1( $settings['test_api_transaction_key'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['api_login_id'] ?? '' );
			$integration->set_api_key_1( $settings['api_transaction_key'] ?? '' );
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
		if ( PaymentMethodType::AUTHORIZE_NET !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// Authorize.Net requires a login id and a transaction key.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Handles the SkyVerge add payment method transaction result filter.
	 * Adds the payment method to QPilot when a transaction is approved.
	 *
	 * @param array  $result The transaction result.
	 * @param object $response The gateway API response.
	 * @param object $order The order object.
	 * @param object $client The gateway client instance.
	 * @return array The transaction result.
	 */
	public function add_transaction_payment_method( $result, $response, $order, $client ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$types = autoship_standard_gateway_id_types();

		if ( $response->transaction_approved() && ! isset( $types['authorize_net_cim_credit_card'] ) ) {
			autoship_add_non_wc_token_payment_method( $response, $order, 'authorize_net_cim_credit_card' );
		}

		return $result;
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
		$token_string = $order->get_meta( '_wc_authorize_net_cim_credit_card_payment_token' );
		$customer_id  = $order->get_meta( '_wc_authorize_net_cim_credit_card_customer_id' );

		if ( ! empty( $token_string ) && ! empty( $customer_id ) ) {
			$payment_data = new QPilotPaymentData();
			$card_type    = $order->get_meta( '_wc_authorize_net_cim_credit_card_card_type' );
			$last_four    = $order->get_meta( '_wc_authorize_net_cim_credit_card_account_four' );

			// Authnet Stores Expiration in YY-MM format so we need to adjust for Autoship
			// Expiration in format 23-02 should be 0223.
			$expiry_date = $order->get_meta( '_wc_authorize_net_cim_credit_card_card_expiry_date' );
			$expiration  = explode( '-', $expiry_date );

			$payment_data->description         = isset( $expiration[1] ) ? sprintf( '%s ending in %s (expires %s)', ucfirst( $card_type ), $last_four, $expiration[1] . '/' . $expiration[0] ) : sprintf( '%s ending in %s', ucfirst( $card_type ), $last_four );
			$payment_data->type                = 'AuthorizeNet';
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
		$test_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_test_ext', '_test', 'authorize_net_cim_credit_card' );

		// ex. _wc_authorize_net_cim_credit_card_payment_tokens_test or _wc_authorize_net_cim_credit_card_payment_tokens.
		$meta_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_ext', '', $test_ext, 'authorize_net_cim_credit_card' );

		// Authnet uses the customer id from user meta as Gateway Customer ID.
		$user_id                                  = $token->get_user_id();
		$payment_method_data['gatewayCustomerId'] = get_user_meta( $user_id, 'wc_authorize_net_cim_customer_profile_id' . $meta_ext, true );

		return $payment_method_data;
	}

	/* i. authorize.net Payment Method - Functions moved to src/payments-authorize-net.php */

	/**
	 * Adds the Skyverge Payment Method to QPilot After Checkout
	 * Fired when a payment is processed for an order.
	 *
	 * @param WC_Order $order The WC Order object.
	 * @param object   $gateway The payment gateway instance.
	 */
	public function add_skyverge_payment_method( WC_Order $order, object $gateway ) {

		// Get the types to see if this is legacy or new.
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
	 * Authorize.Net Gateway: Fires additional autoship actions after a payment method is saved.
	 *
	 * @param string $token_id new token ID.
	 */
	public function after_save_authorize_net_payment_method_notice( string $token_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Get the types to see if this is legacy or new Authnet.
		$types = autoship_standard_gateway_id_types();

		// Only Display Notice if this is the legacy Authnet Gateway.
		if ( ! isset( $types['authorize_net_cim_credit_card'] ) ) {
			autoship_after_save_payment_method_autoship_action_notice( $token_id, 'authorize_net_cim_credit_card', '' );
		}
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
		if ( 'AuthorizeNet' === $type ) {
			// Apply the test filters.
			$test_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_test_ext', '_test', 'authorize_net_cim_credit_card' );
			$meta_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_ext', '', $test_ext, 'authorize_net_cim_credit_card' );

			$user_id     = $token->get_user_id();
			$customer_id = get_user_meta( $user_id, 'wc_authorize_net_cim_customer_profile_id' . $meta_ext, true );

			return ( $method->gatewayCustomerId === $customer_id && $method->gatewayPaymentId === $token->get_token() ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $valid;
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
		$expiration = $payment_method['expiration'] ?? '';
		$exp_year   = ! empty( $expiration ) && ( strlen( $expiration ) > 2 ) ? substr( $expiration, - 2 ) : $expiration;
		$exp_month  = ! empty( $expiration ) && ( strlen( $expiration ) > 2 ) ? substr( $expiration, 0, 2 ) : $expiration;

		$metadata = array(
			'_wc_authorize_net_cim_credit_card_trans_id' => $payment_meta['transId'],
			'_wc_authorize_net_cim_credit_card_authorization_code' => $payment_meta['authCode'],
			'_wc_authorize_net_cim_credit_card_customer_id' => $payment_method['gatewayCustomerId'],
			'_wc_authorize_net_cim_credit_card_account_four' => $payment_method['lastFourDigits'],
			'_wc_authorize_net_cim_credit_card_card_expiry_date' => $exp_year . '-' . $exp_month,
			'_wc_authorize_net_cim_credit_card_charge_captured' => 'yes',
		);

		$order->set_transaction_id( $payment_meta['transId'] );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();

		return $payment_meta;
	}

	/**
	 * Display apply to all orders button.
	 *
	 * @param array $list_item The list item data.
	 * @param mixed $payment_token The payment token (WC_Payment_Token or SkyVerge payment profile).
	 * @return string The button HTML.
	 */
	public function display_apply_to_all_orders_button( array $list_item, $payment_token ): array {
		return autoship_display_apply_payment_method_to_all_scheduled_orders_skyverge_btn( $list_item, $payment_token, null, 'authorize_net_cim_credit_card' );
	}
}
