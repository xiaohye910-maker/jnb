<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Square payment integration.
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
 * Represents the Square payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class SquarePaymentIntegration extends AbstractPaymentGateway {

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Square Payment Integration', 'Initializing Square payment integration.' );
		}
	}

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'square_credit_card',
		'square_cash_app_pay',
	);

	/**
	 * Builds a Square payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return SquarePaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): SquarePaymentIntegration {
		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		// Square is not saving the authentication options in the normal settings, we retrieve the option information.
		$options = get_option( 'wc_square_settings', array() );

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::SQUARE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'yes' === ( $options['enable_sandbox'] ?? '' ) ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_key_1( $options['sandbox_token'] ?? '' );
			$integration->set_api_key_2( $options['sandbox_location_id'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_key_1( $options['production_token'] ?? '' );
			$integration->set_api_key_2( $options['production_location_id'] ?? '' );
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
		if ( PaymentMethodType::SQUARE !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// SQUARE should contain the token and location id.
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
		$payment_method = $order->get_payment_method();

		if ( ! in_array( $payment_method, self::$allowed, true ) ) {
			return null;
		}

		$token_id    = $order->get_meta( '_square_credit_card_payment_token' );
		$customer_id = $order->get_meta( '_square_customer_id' );

		if ( ! empty( $token_id ) ) {
			$payment_data = new QPilotPaymentData();
			$card_type    = $order->get_meta( '_wc_square_credit_card_card_type' );
			$last_four    = $order->get_meta( '_wc_square_credit_card_account_four' );
			$expiry_date  = $order->get_meta( '_wc_square_credit_card_card_expiry_date' );

			$expiry_date                       = explode( '-', $expiry_date );
			$expiry_date[0]                    = strlen( $expiry_date[0] ) > 2 ? substr( $expiry_date[0], - 2 ) : $expiry_date[0];
			$payment_data->description         = sprintf( '%s ending in %s (expires %s)', ucfirst( $card_type ), $last_four, $expiry_date[1] . '/' . $expiry_date[0] );
			$payment_data->type                = 'Square';
			$payment_data->gateway_payment_id  = $token_id;
			$payment_data->gateway_customer_id = $customer_id;
			$payment_data->last_four           = $last_four;

			// Get Expiration in MMYY format for Qpilot.
			$expiration               = $expiry_date[1] . $expiry_date[0];
			$payment_data->expiration = $expiration;

			return $payment_data;
		}

		return null;
	}

	/**
	 * Adds the Square credit card Payment Method to QPilot
	 * Does not use the woocommerce_payment_tokens tables
	 *
	 * @param array                                                    $result The result array.
	 * @param \SV_WC_Payment_Gateway_API_Create_Payment_Token_Response $response The API response object.
	 * @param \WC_Order                                                $order The WC Order object.
	 * @param \SV_WC_Payment_Gateway_Direct                            $client The Direct Gateway instance.
	 *
	 * @return mixed
	 * @see wc_payment_gateway_' . $this->get_id() . '_add_payment_method_transaction_result hook.
	 *
	 * Result: {
	 * @type string $message notice message to render
	 * @type bool $success true to redirect to my account, false to stay on page
	 * }
	 */
	public function add_payment_method_data( $result, $response, $order, $client ) {
		// Check if the transaction has been approved.
		if ( $response->transaction_approved() ) {
			autoship_add_non_wc_token_payment_method( $response, $order, 'square_credit_card' );
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
		$ext_type = 'square_credit_card';

		// Apply the test filters.
		$test_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_test_ext', '_test', $ext_type );
		// ex. _wc_authorize_net_cim_credit_card_payment_tokens_test or _wc_authorize_net_cim_credit_card_payment_tokens.
		$meta_ext = apply_filters( 'autoship_payment_method_sandbox_metadata_field_ext', '', $test_ext, $ext_type );

		$user_id                                  = $token->get_user_id();
		$payment_method_data['gatewayCustomerId'] = get_user_meta( $user_id, 'wc_square_customer_id' . $meta_ext, true );

		return $payment_method_data;
	}

	/**
	 * Square_credit_card Gateway: Fires additional autoship actions after a payment method is saved.
	 *
	 * @param string                              $token_id new token ID.
	 * @param int                                 $user_id user ID.
	 * @param \SV_WC_Payment_Gateway_API_Response $response API response object.
	 */
	public function autoship_after_save_square_credit_card_payment_method_notice( $token_id, $user_id, $response ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		autoship_after_save_payment_method_autoship_action_notice( $token_id, 'square_credit_card', '' );
	}

	/**
	 * Square
	 * Non-Standard skyverge framework Gateways actions
	 * Outputs the apply action button after each payment method
	 *
	 * @param array                                     $list_item The list item array.
	 * @param \SV_WC_Payment_Gateway_Payment_Token      $payment_token The payment token object.
	 * @param \SV_WC_Payment_Gateway_My_Payment_Methods $instance The instance of the My Payment Methods class.
	 */
	public function autoship_display_apply_payment_method_to_all_scheduled_orders_square_btn( $list_item, $payment_token, $instance ) {
		return autoship_display_apply_payment_method_to_all_scheduled_orders_skyverge_btn( $list_item, $payment_token, $instance, 'square_credit_card' );
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
		$token_id = $token->get_token();
		$user_id  = $token->get_user_id();

		return autoship_delete_non_wc_token_payment_method( $token_id, null, 'Square', $user_id );
	}

	/**
	 * Add metadata for Square.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	public function add_adjusted_gateway_metadata( array $payment_meta, WC_Order $order ): void {
		// Set the status for the transaction.
		$payment = $payment_meta['payment'];

		// Adjust Card Expiration.
		$exp  = substr( $payment['card_details']['card']['exp_year'], - 2 );
		$exp .= '-' . $payment['card_details']['card']['exp_month'];

		// Format the date.
		$date = autoship_get_datetime( $payment['created_at'] );

		$metadata = array(
			'_wc_square_credit_card_square_order_id'      => $payment['order_id'],
			'_wc_square_credit_card_square_location_id'   => $payment['location_id'],
			'_wc_square_credit_card_card_type'            => strtolower( $payment['card_details']['card']['card_brand'] ),
			'_wc_square_credit_card_card_expiry_date'     => $exp,
			'_wc_square_credit_card_charge_captured'      => 'yes',
			'_wc_square_credit_card_authorization_amount' => $payment['approved_money']['amount'],
			'_wc_square_credit_card_account_four'         => $payment['card_details']['card']['last_4'],
			'_wc_square_credit_card_customer_id'          => $payment['customer_id'],
			'_wc_square_credit_card_trans_date'           => $date->format( 'Y-m-d H:i:s' ),
			'_wc_square_credit_card_trans_id'             => $payment['id'],
			'_wc_square_credit_card_authorization_code'   => $payment['id'],
			'_wc_square_credit_card_square_version'       => WC_SQUARE_PLUGIN_VERSION,
		);

		$order->set_transaction_id( $payment['id'] );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();
	}

	/**
	 * Static helper method for Square force save compatibility.
	 *
	 * @param bool $val The current value.
	 * @return bool
	 */
	public static function force_save_source( bool $val ): bool {
		if ( autoship_cart_has_valid_autoship_items() ) {
			return true;
		}
		return $val;
	}

	/**
	 * Static helper method for Square customer request filtering.
	 *
	 * @param array    $request The Square customer request.
	 * @param WC_Order $order The WC Order object.
	 * @return array
	 */
	public static function filter_customer_request( array $request, WC_Order $order ): array {
		if ( autoship_order_total_scheduled_items( $order ) > 0 ) {
			// Flag as a recurring payment.
			$request['note'] = 'Autoship recurring payment customer';
		}
		return $request;
	}
}
