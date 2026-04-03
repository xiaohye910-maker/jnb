<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the NMI payment integration.
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
 * Represents the NMI payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class NmiPaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'nmi',
		'nmi_gateway_woocommerce_credit_card',
	);

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'NMI Payment Integration', 'Initializing NMI payment integration.' );
		}
	}

	/**
	 * Builds an NMI payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return NmiPaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): NmiPaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::NMI );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'sandbox' === $settings['environment'] ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['sandbox_username'] ?? '' );
			$integration->set_api_key_1( $settings['sandbox_password'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['live_username'] ?? '' );
			$integration->set_api_key_1( $settings['live_password'] ?? '' );
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
		if ( PaymentMethodType::NMI !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// NMI should contain the username and the password.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get order payment data for QPilot.
	 * Since NMI uses Customer Vault ID, use tokenized data in the
	 * gatewayCustomerId (since that's customer vault ID), and nothing in the gatewayPaymentId.
	 *
	 * @param int      $order_id The order ID.
	 * @param WC_Order $order The order object.
	 *
	 * @return ?QPilotPaymentData The payment data.
	 */
	public function get_order_payment_data( int $order_id, WC_Order $order ): ?QPilotPaymentData {
		$payment_method = $order->get_payment_method();

		if ( 'nmi' === $payment_method ) {
			return $this->get_nmi_gateway_order_payment_data( $order );
		}

		return $this->get_nmi_woocommerce_order_payment_data( $order );
	}

	/**
	 * Get NMI WooCommerce gateway order payment data.
	 *
	 * @param WC_Order $order The order object.
	 * @return ?QPilotPaymentData The payment data.
	 */
	private function get_nmi_woocommerce_order_payment_data( WC_Order $order ): ?QPilotPaymentData {
		$token_id    = $order->get_meta( '_wc_nmi_gateway_woocommerce_credit_card_payment_token' );
		$customer_id = $order->get_meta( '_customer_user' );

		if ( ! empty( $token_id ) && ! empty( $customer_id ) ) {
			$token = autoship_get_related_tokenized_id( $token_id );

			if ( ! empty( $token ) ) {
				$payment_data                      = new QPilotPaymentData();
				$payment_data->description         = $token->get_display_name();
				$payment_data->type                = 'Nmi';
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
	 * Get NMI Enterprise (Pledged Plugins) gateway order payment data.
	 *
	 * @param WC_Order $order The order object.
	 * @return ?QPilotPaymentData The payment data.
	 */
	private function get_nmi_gateway_order_payment_data( WC_Order $order ): ?QPilotPaymentData {
		$customer_id = $order->get_customer_id();
		$token_id    = $order->get_meta( '_nmi_card_id' );

		if ( ! empty( $token_id ) && ! empty( $customer_id ) ) {
			$token = autoship_get_related_tokenized_id( $token_id );

			if ( ! empty( $token ) ) {
				$payment_data                      = new QPilotPaymentData();
				$payment_data->description         = $token->get_display_name();
				$payment_data->type                = 'Nmi';
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
		// NMI uses the token in place of the customer_id
		// And doesn't include the token.
		$payment_method_data['gatewayCustomerId'] = $payment_method_data['gatewayPaymentId'];
		unset( $payment_method_data['gatewayPaymentId'] );

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
		return ( 'Nmi' === $type ) ? ( $method->gatewayCustomerId === $token->get_token() ) : $valid; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
			'_wc_nmi_gateway_woocommerce_credit_card_trans_id' => $payment_meta['transactionid'],
			'_wc_nmi_gateway_woocommerce_credit_card_transaction_id' => $payment_meta['transactionid'],
		);

		$order->set_payment_method( 'nmi_gateway_woocommerce_credit_card' );

		foreach ( $metadata as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();

		return $payment_meta;
	}

	/**
	 * Static helper method for NMI force tokenization.
	 *
	 * @param bool $force The current force tokenization flag.
	 * @return bool True.
	 */
	public static function force_tokenization( bool $force ): bool {
		return true;
	}
}
