<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the NMI payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the NMI payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class NmiPaymentIntegration extends PaymentIntegration {

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
}
