<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the PayPal payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the PayPal payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PayPalPaymentIntegration extends PaymentIntegration {
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

		$environment = 'sandbox' === $settings['environment'] ? 'test' : 'live';

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

		// NMI should contain the username, password and signature.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) || empty( $this->get_api_key_2() ) ) {
			return false;
		}

		return true;
	}
}
