<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Stripe payment integration.
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
use WC_Stripe_API;
use WC_Payment_Token_CC;
use WC_Stripe_Helper;

/**
 * Represents the Stripe payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class StripePaymentIntegration extends AbstractPaymentGateway {

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Stripe Payment Integration', 'Initializing Stripe payment integration.' );
		}
	}

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'stripe',
		'stripe_sepa',
		'fkwcs_stripe',
		'link',
	);

	/**
	 * Builds a Stripe payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return StripePaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): StripePaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::STRIPE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		if ( 'fkwcs_stripe' === $gateway_id ) {
			return self::build_funnelkit_integration( $integration );
		}

		$environment = 'yes' === $settings['testmode'] ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_key_1( $settings['test_publishable_key'] ?? '' );
			$integration->set_api_key_2( $settings['test_secret_key'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_key_1( $settings['publishable_key'] ?? '' );
			$integration->set_api_key_2( $settings['secret_key'] ?? '' );
			$integration->set_test_mode( false );
		}

		return $integration;
	}

	/**
	 * Build FunnelKit Stripe integration with specific configuration.
	 *
	 * @param StripePaymentIntegration $integration The integration instance.
	 * @return StripePaymentIntegration
	 */
	private static function build_funnelkit_integration( StripePaymentIntegration $integration ): StripePaymentIntegration {
		$funnelkit_mode = get_option( 'fkwcs_mode', 'test' );
		$environment    = 'test' === $funnelkit_mode ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$funnelkit_public_key = get_option( 'fkwcs_test_pub_key', '' );
			$funnelkit_secret_key = get_option( 'fkwcs_test_secret_key', '' );

			$integration->set_api_key_1( $funnelkit_public_key ?? '' );
			$integration->set_api_key_2( $funnelkit_secret_key ?? '' );
			$integration->set_test_mode( true );
		} else {
			$funnelkit_public_key = get_option( 'fkwcs_pub_key', '' );
			$funnelkit_secret_key = get_option( 'fkwcs_secret_key', '' );

			$integration->set_api_key_1( $funnelkit_public_key ?? '' );
			$integration->set_api_key_2( $funnelkit_secret_key ?? '' );
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
		if ( PaymentMethodType::STRIPE !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

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

		if ( 'fkwcs_stripe' === $payment_method ) {
			return $this->get_fkwcs_order_payment_data( $order );
		}

		// Stripe version >= 4.0.0.
		$token_id = $order->get_meta( '_stripe_source_id' );

		// Stripe version < 4.0.0.
		if ( empty( $token_id ) ) {
			$token_id = $order->get_meta( '_stripe_card_id' );
		}

		$customer_id = $order->get_meta( '_stripe_customer_id' );

		if ( ! empty( $token_id ) && ! empty( $customer_id ) ) {

			$token = autoship_get_related_tokenized_id( $token_id );

			if ( ! empty( $token ) ) {

				$payment_data                      = new QPilotPaymentData();
				$payment_data->description         = $token->get_display_name();
				$payment_data->type                = 'Stripe';
				$payment_data->gateway_payment_id  = $token->get_token();
				$payment_data->gateway_customer_id = $customer_id;

				if ( method_exists( $token, 'get_last4' ) && method_exists( $token, 'get_expiry_month' ) && method_exists( $token, 'get_expiry_year' ) ) {
					$payment_data->last_four  = $token->get_last4();
					$payment_data->expiration = $token->get_expiry_month() . substr( $token->get_expiry_year(), -2 );
				}

				// Stripe Link.
				if ( 'link' === $token->get_type() ) {
					$payment_data->gateway_payment_type = 32;
				} else {
					$payment_data->gateway_payment_type = 7;
				}

				return $payment_data;
			} else {
				// Try to fetch payment data from Stripe API.
				return $this->get_payment_method_data_by_id( $token_id, $customer_id );
			}
		}

		return null;
	}

	/**
	 * Get FunnelKit Stripe order payment data.
	 *
	 * @param WC_Order $order The order object.
	 * @return ?QPilotPaymentData The payment data.
	 */
	private function get_fkwcs_order_payment_data( WC_Order $order ): ?QPilotPaymentData {
		$token_id    = $order->get_meta( '_fkwcs_source_id' );
		$customer_id = $order->get_meta( '_fkwcs_customer_id' );

		if ( ! empty( $token_id ) && ! empty( $customer_id ) ) {
			$token = autoship_get_related_tokenized_id( $token_id );

			if ( ! empty( $token ) ) {
				$expiration   = $token->get_expiry_month() . substr( $token->get_expiry_year(), -2 );
				$payment_data = new QPilotPaymentData();

				$payment_data->description          = $token->get_display_name();
				$payment_data->type                 = 'Stripe';
				$payment_data->gateway_payment_id   = $token->get_token();
				$payment_data->gateway_customer_id  = $customer_id;
				$payment_data->last_four            = $token->get_last4();
				$payment_data->expiration           = $expiration;
				$payment_data->gateway_payment_type = 7;

				return $payment_data;
			}
		}

		return null;
	}

	/**
	 * Get payment method data by ID from Stripe API.
	 *
	 * @param string $payment_method_id The payment method ID.
	 * @param string $customer_id The Stripe customer ID.
	 * @return ?QPilotPaymentData The payment data.
	 */
	public function get_payment_method_data_by_id( string $payment_method_id, string $customer_id ): ?QPilotPaymentData {
		if ( class_exists( 'WC_Stripe_API' ) ) {
			$response = WC_Stripe_API::get_payment_method( $payment_method_id );
			if ( ! empty( $response->error ) || is_wp_error( $response ) ) {
				return null;
			}
			if ( empty( $response->type ) || 'card' !== $response->type ) {
				return null;
			}
			$exp_year                          = substr( $response->card->exp_year, - 2 );
			$expiration                        = $response->card->exp_month . $exp_year;
			$description                       = sprintf( /* translators: 1: credit card type 2: last 4 digits 3: expiry month 4: expiry year */ __( '%1$s ending in %2$s (expires %3$s/%4$s)', 'woocommerce' ), wc_get_credit_card_type_label( $response->card->brand ), $response->card->last4, $response->card->exp_month, $exp_year );
			$payment_data                      = new QPilotPaymentData();
			$payment_data->description         = $description;
			$payment_data->type                = 'Stripe';
			$payment_data->gateway_payment_id  = $response->id;
			$payment_data->gateway_customer_id = $customer_id;
			$payment_data->last_four           = $response->card->last4;
			$payment_data->expiration          = $expiration;

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
		$user_id = $token->get_user_id();

		// FunnelKit Stripe payment gateway.
		if ( 'fkwcs_stripe' === $token->get_gateway_id() ) {
			$payment_method_data['gatewayCustomerId'] = get_user_option( '_fkwcs_customer_id', $user_id );
		} else {
			// Stripe uses the customer id from user meta as Gateway Customer ID.
			$payment_method_data['gatewayCustomerId'] = get_user_option( '_stripe_customer_id', $user_id );

			if ( 'stripe_sepa' === $token->get_gateway_id() ) {
				$payment_method_data['gatewayPaymentType'] = 21;
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
		if ( 'Stripe' === $type ) {
			$customer_id = get_user_option( '_stripe_customer_id', $token->get_user_id() );

			return ( $method->gatewayCustomerId === $customer_id && $method->gatewayPaymentId === $token->get_token() ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $valid;
	}

	/**
	 * Add refund metadata for Stripe.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	public function add_adjusted_gateway_metadata( array $payment_meta, WC_Order $order ): void {
		$metadata = array();

		if ( isset( $payment_meta['CustomerId'] ) ) {
			$metadata = array(
				'_stripe_customer_id'     => $payment_meta['CustomerId'],
				'_stripe_source_id'       => $payment_meta['Source']['Id'],
				'_stripe_charge_captured' => 1 === $payment_meta['Captured'] ? 'yes' : 'no',
				'_stripe_currency'        => strtoupper( $payment_meta['Currency'] ),
			);
			$order->set_transaction_id( $payment_meta['Id'] );
		} elseif ( isset( $payment_meta['charges'] ) && isset( $payment_meta['charges']['data'] ) ) {
			$data     = $payment_meta['charges']['data'][0];
			$metadata = array(
				'_stripe_customer_id'     => $data['customer'],
				'_stripe_source_id'       => $data['balance_transaction']['source'],
				'_stripe_charge_captured' => 1 === $data['captured'] ? 'yes' : 'no',
				'_stripe_currency'        => strtoupper( $data['balance_transaction']['currency'] ),
			);
			$order->set_transaction_id( $data['id'] );
		}

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();
	}

	/**
	 * Add fee metadata for Stripe.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	public function add_adjusted_gateway_metadata_fees( array $payment_meta, WC_Order $order ): void {
		if ( ! class_exists( 'WC_Stripe_Helper' ) ) {
			return;
		}

		// Stripe plugin 8.8.0+ handles fee/net metadata natively; skip to avoid doubling values.
		if ( defined( 'WC_STRIPE_VERSION' ) && version_compare( WC_STRIPE_VERSION, '8.8.0', '>=' ) ) {
			return;
		}

		$fee_refund = null;
		$net_refund = null;

		if ( ! isset( $payment_meta['BalanceTransaction'] ) && isset( $payment_meta['charges'] ) ) {
			if ( isset( $payment_meta['charges']['data'] ) && ! empty( $payment_meta['charges']['data'] ) ) {
				$data = $payment_meta['charges']['data'][0];

				$fee_refund = isset( $data['balance_transaction']['fee'] ) ? $payment_meta['charges']['data'][0]['balance_transaction']['fee'] : 0;
				$net_refund = isset( $data['balance_transaction']['net'] ) ? $payment_meta['charges']['data'][0]['balance_transaction']['net'] : 0;

				// Use Stripes Helper Function to format currency.
				if ( ! in_array( strtolower( $data['balance_transaction']['currency'] ), WC_Stripe_Helper::no_decimal_currencies(), true ) ) {
					$fee_refund = number_format( $fee_refund / 100, 2, '.', '' );
					$net_refund = number_format( $net_refund / 100, 2, '.', '' );
				}
			}
		} elseif ( isset( $payment_meta['BalanceTransaction'] ) && ! empty( $payment_meta['BalanceTransaction'] ) ) {
			// Fees and Net needs to both come from Stripe to be accurate as the returned
			// values are in the local currency of the Stripe account, not from WC.
			$fee_refund = isset( $payment_meta['BalanceTransaction']['Fee'] ) ? $payment_meta['BalanceTransaction']['Fee'] : 0;
			$net_refund = isset( $payment_meta['BalanceTransaction']['Net'] ) ? $payment_meta['BalanceTransaction']['Net'] : 0;

			// Use Stripes Helper Function to format currency.
			if ( ! in_array( strtolower( $payment_meta['BalanceTransaction']['Currency'] ), WC_Stripe_Helper::no_decimal_currencies(), true ) ) {
				$fee_refund = number_format( $fee_refund / 100, 2, '.', '' );
				$net_refund = number_format( $net_refund / 100, 2, '.', '' );
			}
		}

		if ( isset( $fee_refund ) && isset( $net_refund ) ) {
			// Current data fee & net.
			$fee_current = WC_Stripe_Helper::get_stripe_fee( $order );
			$net_current = WC_Stripe_Helper::get_stripe_net( $order );

			// Calculation.
			$fee = (float) $fee_current + (float) $fee_refund;
			$net = (float) $net_current + (float) $net_refund;

			// Retrieve the current fees.
			$current_fee = WC_Stripe_Helper::get_stripe_fee( $order );
			$current_net = WC_Stripe_Helper::get_stripe_net( $order );

			// if the fee or net doesn't exist update it.
			if ( empty( $current_fee ) || empty( $current_net ) ) {
				WC_Stripe_Helper::update_stripe_fee( $order, $fee );
				WC_Stripe_Helper::update_stripe_net( $order, $net );
			}
		}
	}

	/**
	 * Static helper method for Stripe force save compatibility.
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
	 * Static helper method for Stripe intent request filtering.
	 *
	 * @param array    $request The Stripe intent request.
	 * @param WC_Order $order The WC Order object.
	 * @return array
	 */
	public static function filter_intent_request( array $request, WC_Order $order ): array {
		if ( autoship_order_total_scheduled_items( $order ) > 0 ) {
			// Setup card for both on and off-session payments.
			$request['setup_future_usage'] = 'off_session';
			// Flag as a recurring payment.
			$request['metadata']['payment_type'] = 'recurring';
		}
		return $request;
	}

	/**
	 * Static helper method for Stripe Link mandate data.
	 *
	 * @param array $order_data The order data.
	 * @param int   $order_id The order ID.
	 * @return array
	 */
	public static function add_link_mandate_data( array $order_data, int $order_id ): array {
		if ( isset( $order_data['paymentMethod']['type'] ) && isset( $order_data['paymentMethod']['gatewayPaymentType'] ) ) {
			if ( 'Stripe' === $order_data['paymentMethod']['type'] && 32 === $order_data['paymentMethod']['gatewayPaymentType'] ) {
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$ip_address = $order->get_customer_ip_address();
					$user_agent = $order->get_customer_user_agent();

					$order_data['metadata']['stripe_mandate_data'] = array(
						'customer_acceptance' => array(
							'type'   => 'online',
							'online' => array(
								'ip_address' => $ip_address,
								'user_agent' => $user_agent,
							),
						),
					);
				}
			}
		}
		return $order_data;
	}

	/**
	 * Modifies the Payment Gateway ID for gateways treated like other gateways
	 *
	 * @param string              $gateway_id The current Gateway ID.
	 * @param WC_Payment_Token_CC $token The payment Token being deleted.
	 *
	 * @return array The modified Payment Method Data to Send to QPilot
	 */
	public static function autoship_filter_deleted_payment_method_gateway_ids( $gateway_id, $token ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		return 'stripe_sepa' === $gateway_id ? 'stripe' : $gateway_id;
	}
}
