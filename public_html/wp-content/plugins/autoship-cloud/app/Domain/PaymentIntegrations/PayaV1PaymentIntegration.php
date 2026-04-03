<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the PAYA_V1 payment integration.
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
use WC_Payment_Tokens;

/**
 * Represents the PAYA_V1 payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PayaV1PaymentIntegration extends AbstractPaymentGateway {
	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'sagepaymentsusaapi',
	);

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'PayaV1 Payment Integration', 'Initializing PayaV1 payment integration.' );
		}
	}

	/**
	 * Builds an PAYA_V1 payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return PayaV1PaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): PayaV1PaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::PAYA_V1 );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'testing' === $settings['status'] ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['M_id_testing'] ?? '' );
			$integration->set_api_key_1( $settings['M_key_testing'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['M_id'] ?? '' );
			$integration->set_api_key_1( $settings['M_key'] ?? '' );
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
		if ( PaymentMethodType::PAYA_V1 !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// PayaV1 should contain the id and the key.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) ) {
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
		$token = null;

		// Get the Sage / Paya Token & Customer ID.
		$order_token_id = $order->get_meta( '_SageToken' );
		$customer_id    = $order->get_meta( '_customer_user' );
		$tokens         = WC_Payment_Tokens::get_customer_tokens( $customer_id, 'sagepaymentsusaapi' );

		// If the token is not empty get the other token payment info.
		if ( ! empty( $order_token_id ) ) {

			// Now loop through and grab the token object for this order.
			foreach ( $tokens as $wctoken ) {

				if ( $order_token_id === $wctoken->get_token() ) {
					$token = $wctoken;
					break;
				}
			}

			if ( ! empty( $token ) ) {
				$payment_data                      = new QPilotPaymentData();
				$payment_data->description         = $token->get_display_name();
				$payment_data->type                = 'PayaV1';
				$payment_data->gateway_customer_id = $token->get_token();
				$payment_data->last_four           = $token->get_last4();

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
		// PayaV1 uses the token in place of the customer_id
		// And doesn't include the token.
		$payment_method_data['gatewayCustomerId'] = $payment_method_data['gatewayPaymentId'];
		$payment_method_data['gatewayPaymentId']  = null;

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
		return ( 'PayaV1' === $type ) ? ( $method->gatewayCustomerId === $token->get_token() ) : $valid; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
		$order->set_payment_method( 'sagepaymentsusaapi' );
		$order->set_payment_method_title( 'Credit Card via Paya' );

		$order->save();

		return $payment_meta;
	}
}
