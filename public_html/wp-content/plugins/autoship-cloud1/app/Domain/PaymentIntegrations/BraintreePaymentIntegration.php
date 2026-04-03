<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Braintree payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the Braintree payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class BraintreePaymentIntegration extends PaymentIntegration {

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

		$environment = 'sandbox' === $settings['environment'] ? 'test' : 'live';

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
}
