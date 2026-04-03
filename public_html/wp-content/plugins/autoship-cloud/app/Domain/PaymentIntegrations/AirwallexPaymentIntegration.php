<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Airwallex payment integration.
 *
 * @package Autoship
 * @since 2.9.2
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
 * Represents the Airwallex payment integration.
 *
 * @package Autoship
 * @since 2.9.2
 */
class AirwallexPaymentIntegration extends AbstractPaymentGateway {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'airwallex_card',
	);

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Airwallex Payment Integration', 'Initializing Airwallex payment integration.' );
		}
	}

	/**
	 * Builds an Airwallex payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return AirwallexPaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): AirwallexPaymentIntegration {
		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		// Airwallex is not saving the authentication options in the normal settings, we retrieve the option information.
		$airwallex = get_option( 'airwallex-online-payments-gatewayairwallex_general_settings', array() );

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::AIRWALLEX );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'yes' === $airwallex['enable_sandbox'] ? 'test' : 'live';

		$airwallex_client = get_option( 'airwallex_client_id', '' );
		$airwallex_key_1  = get_option( 'airwallex_api_key', '' );

		$integration->set_api_account( $airwallex_client ?? '' );
		$integration->set_api_key_1( $airwallex_key_1 ?? '' );

		if ( 'test' === $environment ) {
			$integration->set_test_mode( true );
		} else {
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
		if ( PaymentMethodType::AIRWALLEX !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// Airwallex should contain the token and location id.
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
		// Grab the Customer ID and Token from the order.
		$token_id    = $order->get_meta( 'airwallex_consent_id' );
		$customer_id = $order->get_meta( 'airwallex_customer_id' );

		if ( ! empty( $token_id ) ) {

			$token = autoship_get_related_tokenized_id( $token_id );

			if ( ! empty( $token ) ) {

				$payment_data                      = new QPilotPaymentData();
				$payment_data->description         = $token->get_display_name();
				$payment_data->type                = 'Airwallex';
				$payment_data->gateway_payment_id  = $token_id;
				$payment_data->gateway_customer_id = $customer_id;
				$payment_data->last_four           = $token->get_last4();

				// Get Expiration in MMYY format for Qpilot.
				$expiration               = $token->get_expiry_month() . substr( $token->get_expiry_year(), - 2 );
				$payment_data->expiration = $expiration;

				return $payment_data;
			}

			// Workaround for Airwallex as the Scheduled Orders Upsert gets called before the Payment creation.
			// Calls priority to be reviewed during the payment refactor.
			$payment_data                      = new QPilotPaymentData();
			$payment_data->description         = 'Default Airwallex Payment Method';
			$payment_data->type                = 'Airwallex';
			$payment_data->gateway_payment_id  = $token_id;
			$payment_data->gateway_customer_id = $customer_id;
			$payment_data->last_four           = '0000';
			$payment_data->expiration          = '01/30';

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
		$customer_id = autoship_get_airwallex_customer_id( $token->get_user_id() );

		$payment_method_data['gatewayCustomerId'] = $customer_id ? $customer_id : null;

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
		if ( 'Airwallex' === $type ) {
			$customer_id = autoship_get_airwallex_customer_id( $token->get_user_id() );
			$payment_id  = $token->get_token();

			return ( $method->gatewayCustomerId === $customer_id && $method->gatewayPaymentId === $payment_id ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $valid;
	}
}
