<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Checkout.com payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;

/**
 * Represents the Checkout.com payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class CheckoutPaymentIntegration extends PaymentIntegration {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'wc_checkout_com_cards',
	);

	/**
	 * Builds a Checkout.com payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return CheckoutPaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): CheckoutPaymentIntegration {
		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::CHECKOUT );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );
		$integration->set_test_mode( true );

		$environment = 'sandbox' === $settings['ckocom_environment'] ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_key_1( $settings['ckocom_pk'] ?? '' );
			$integration->set_api_key_2( $settings['ckocom_sk'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_key_1( $settings['ckocom_pk'] ?? '' );
			$integration->set_api_key_2( $settings['ckocom_sk'] ?? '' );
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
		if ( PaymentMethodType::CHECKOUT !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// Checkout should contain the pk and the sk.
		if ( empty( $this->get_api_key_1() ) || empty( $this->get_api_key_2() ) ) {
			return false;
		}

		return true;
	}
}
