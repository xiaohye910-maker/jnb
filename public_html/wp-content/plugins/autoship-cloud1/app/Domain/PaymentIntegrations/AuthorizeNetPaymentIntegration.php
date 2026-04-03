<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents an Authorize.Net payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents an Authorize.Net payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class AuthorizeNetPaymentIntegration extends PaymentIntegration {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'authorize_net_cim_credit_card',
	);

	/**
	 * Builds an Authorize.Net payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return AuthorizeNetPaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): AuthorizeNetPaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::AUTHORIZE_NET );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = $settings['environment'] ?? 'test';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['test_api_login_id'] ?? '' );
			$integration->set_api_key_1( $settings['test_api_transaction_key'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['api_login_id'] ?? '' );
			$integration->set_api_key_1( $settings['api_transaction_key'] ?? '' );
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
		if ( PaymentMethodType::AUTHORIZE_NET !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// Authorize.Net requires a login id and a transaction key.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) ) {
			return false;
		}

		return true;
	}
}
