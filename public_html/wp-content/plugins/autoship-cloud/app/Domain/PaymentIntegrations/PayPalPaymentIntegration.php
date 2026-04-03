<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the PayPal payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\AbstractPaymentGateway;
use Autoship\Services\Logging\Logger;
use Autoship\Domain\PaymentMethodType;
use Exception;
use WC_Payment_Tokens;
use QPilotPaymentData;
use WC_Order;
use RuntimeException;
use WC_Payment_Token;

/**
 * Represents the PayPal payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PayPalPaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'paypal',
		'ppec_paypal',
	);

	/**
	 * Initializes the payment integration.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'PayPal Payment Integration', 'Initializing PayPal payment integration.' );
		}
	}

	/**
	 * Builds the PayPal payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return PayPalPaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): PayPalPaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::PAYPAL );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'sandbox' === ( $settings['environment'] ?? '' ) ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['sandbox_api_username'] ?? '' );
			$integration->set_api_key_1( $settings['sandbox_api_password'] ?? '' );
			$integration->set_api_key_2( $settings['sandbox_api_signature'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['api_username'] ?? '' );
			$integration->set_api_key_1( $settings['api_password'] ?? '' );
			$integration->set_api_key_2( $settings['api_signature'] ?? '' );
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
		if ( PaymentMethodType::PAYPAL !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// PayPal requires the username, password and signature.
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
		// PayPal Express Checkout stores the billing agreement ID.
		$payment_agreement_id = $order->get_meta( '_ppec_billing_agreement_id' );

		if ( ! empty( $payment_agreement_id ) ) {
			$payment_data                     = new QPilotPaymentData();
			$card_type                        = 'PayPal';
			$last_four                        = substr( $payment_agreement_id, -4 );
			$payment_data->description        = sprintf( '%s ending in %s', ucfirst( $card_type ), $last_four );
			$payment_data->type               = 'PayPal';
			$payment_data->gateway_payment_id = $payment_agreement_id;

			return $payment_data;
		}

		$gateway_payment_token = array();
		$gateway_customer_id   = null;
		$gateway_payment_token = null;
		$user_id               = $order->get_user_id();
		$wc_tokens             = WC_Payment_Tokens::get_customer_tokens( $user_id, 'ppcp-gateway' );

		if ( $wc_tokens ) {
			$gateway_customer_id = get_user_meta( $user_id, '_ppcp_target_customer_id', true );
			if ( ! $gateway_customer_id ) {
				$gateway_customer_id = get_user_meta( $user_id, 'ppcp_customer_id', true );
			}

			$customer_tokens = autoship_get_paypal_payments_tokens_for_customer( $gateway_customer_id );

			$customer_token_ids = array();
			foreach ( $customer_tokens as $customer_token ) {
				$customer_token_ids[] = $customer_token['id'];
			}

			foreach ( $wc_tokens as $token ) {
				if ( ! in_array( $token->get_token(), $customer_token_ids, true ) ) {
					continue;
				}
				$gateway_payment_token = $token->get_token();
				break;
			}
		} else {
			// Get payment data from PayPal if not found in WP.
			$order_id_value = $order->get_meta( \WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway::ORDER_ID_META_KEY );
			$order_id       = $order_id_value ? $order_id_value : wc_clean( wp_unslash( $_POST['paypal_order_id'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$order_endpoint = \WooCommerce\PayPalCommerce\PPCP::container()->get( 'api.endpoint.order' );

			if ( is_string( $order_id ) && $order_id ) {
				try {
					$ordder = $order_endpoint->order( $order_id );
				} catch ( RuntimeException ) {
					throw new Exception( esc_html( __( 'Could not retrieve PayPal order:' ) ), 'woocommerce-paypal-payments' );
				}

				$payment_source = $ordder->payment_source();
				assert( $payment_source instanceof \WooCommerce\PayPalCommerce\ApiClient\Entity\PaymentSource );
				$payment_vault_attributes = $payment_source->properties()->attributes->vault ?? null;
				if ( $payment_vault_attributes ) {
					$gateway_customer_id   = $payment_vault_attributes->customer->id ?? '';
					$gateway_payment_token = $payment_vault_attributes->id ?? '';
				}
			}
		}

		if ( ! empty( $gateway_payment_token ) ) {
			$payment_data                       = new QPilotPaymentData();
			$payment_data->description          = 'PayPal Payments';
			$payment_data->type                 = 'PayPalV3';
			$payment_data->gateway_payment_id   = $gateway_payment_token;
			$payment_data->gateway_customer_id  = $gateway_customer_id;
			$payment_data->gateway_payment_type = 25;

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
		// PayPal Express Checkout doesn't use a customer ID in the same way as credit card gateways.
		// The billing agreement ID serves as both the payment token and identifier.
		// PayPal Payments adjustments.
		if ( 'PayPalV3' === $type ) {
			$gateway        = $token->get_gateway_id();
			$wc_customer_id = $token->get_user_id();

			if ( 'ppcp-credit-card-gateway' === $gateway ) {
				$payment_method_data['gatewayPaymentType'] = 26;
			}

			if ( 'ppcp-gateway' === $gateway ) {
				$payment_method_data['gatewayPaymentType'] = 25;
				$payment_method_data['description']        = 'PayPal Payments';
			}

			$gateway_customer_id = get_user_meta( $wc_customer_id, '_ppcp_target_customer_id', true );
			if ( ! $gateway_customer_id ) {
				$gateway_customer_id = get_user_meta( $wc_customer_id, 'ppcp_customer_id', true );
			}

			if ( $gateway_customer_id ) {
				$payment_method_data['gatewayCustomerId'] = $gateway_customer_id;
			}
		}

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
		if ( 'PayPal' === $type ) {
			// PayPal uses the billing agreement ID as the gateway payment ID.
			return ( $method->gatewayPaymentId === $token->get_token() ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		if ( 'PayPalV3' === $type ) {
			$gateway_customer_id = get_user_meta( $token->get_user_id(), '_ppcp_target_customer_id', true );
			if ( ! $gateway_customer_id ) {
				$gateway_customer_id = get_user_meta( $token->get_user_id(), 'ppcp_customer_id', true );
			}

			return ( $method->gatewayCustomerId === $gateway_customer_id && $method->gatewayPaymentId === $token->get_token() ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $valid;
	}

	/**
	 * Adds Support for the PayPal Payments (PayPal V3)
	 *
	 * @param array $types The current types array.
	 *
	 * @return array The filtered types
	 */
	public function add_paypal_v3_token_support( $types ) {

		if ( class_exists( '\WooCommerce\PayPalCommerce\PPCP' ) ) {
			$ppcp_settings = \WooCommerce\PayPalCommerce\PPCP::container()->get( 'wcgateway.settings' );
			if ( $ppcp_settings->has( 'vault_enabled' ) && $ppcp_settings->get( 'vault_enabled' ) ) {
				$types['ppcp-gateway'] = 'PayPalV3';
			}

			if ( $ppcp_settings->has( 'vault_enabled_dcc' ) && $ppcp_settings->get( 'vault_enabled_dcc' ) ) {
				$types['ppcp-credit-card-gateway'] = 'PayPalV3';
			}
		}

		return $types;
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
		// Set the status for the transaction.
		$status = 'Completed' === ( $payment_meta['PAYMENTSTATUS'] ?? '' ) ? $payment_meta['PAYMENTSTATUS'] : ( $payment_meta['PAYMENTSTATUS'] ?? '' ) . '_' . ( $payment_meta['PENDINGREASON'] ?? '' );

		$metadata = array(
			'_woo_pp_txnData' => array(
				'refundable_txns' => array(
					array(
						'txnID'  => $payment_meta['TRANSACTIONID'] ?? '',
						'amount' => $order->get_total(),
						'status' => $status,
					),
				),
			),
		);

		$order->set_transaction_id( $payment_meta['TRANSACTIONID'] ?? '' );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();

		return $payment_meta;
	}

	/**
	 * Adds the refund metadata for PayPal v2
	 *
	 * @param array    $payment_meta The gateway response data.
	 * @param WC_Order $order The wc order.
	 * @param array    $payment_method The associated payment method data.
	 */
	public function add_adjusted_gateway_metadata_ppep_paypal( $payment_meta, $order, $payment_method ) {

		$order->update_meta_data( 'payment_token_id', $payment_method['gatewayPaymentId'] );
		$order->update_meta_data( '_ppcp_paypal_order_id', $payment_meta['id'] );
		$order->update_meta_data( '_ppcp_paypal_intent', 'CAPTURE' );
		$order->save();

		/**
		 * Check if Test Mode or Live
		 *
		 * @HACK Need better way to test if this is a live or test payment process.
		 */
		if ( isset( $payment_meta['links'] ) && ! empty( $payment_meta['links'] ) && ( strpos( $payment_meta['links'][0]['href'], 'https://api.sandbox.paypal.com' ) !== false ) ) {
			$order->update_meta_data( '_ppcp_paypal_payment_mode', 'sandbox' );
			$order->save();
		}
}
}
