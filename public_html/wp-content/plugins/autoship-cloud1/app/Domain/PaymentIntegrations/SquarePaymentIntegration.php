<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Square payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the Square payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class SquarePaymentIntegration extends PaymentIntegration {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'square_credit_card',
	);

	/**
	 * Builds a Square payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return SquarePaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): SquarePaymentIntegration {
		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		// Square is not saving the authentication options in the normal settings, we retrieve the option information.
		$options = get_option( 'wc_square_settings', array() );

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::SQUARE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'yes' === ( $options['enable_sandbox'] ?? '' ) ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_key_1( $options['sandbox_token'] ?? '' );
			$integration->set_api_key_2( $options['sandbox_location_id'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			// The option table said sandbox_token instead of production_token. Need to validate this.
			$integration->set_api_key_1( $options['sandbox_token'] ?? '' );
			$integration->set_api_key_2( $options['production_location_id'] ?? '' );
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
		if ( PaymentMethodType::SQUARE !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// SQUARE should contain the token and location id.
		if ( empty( $this->get_api_key_1() ) || empty( $this->get_api_key_2() ) ) {
			return false;
		}

		return true;
	}
}
