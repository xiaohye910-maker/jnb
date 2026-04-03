<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Funnel Kit payment integration.
 *
 * @package Autoship
 * @since 2.9.2
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the FunnelKit payment integration.
 *
 * @package Autoship
 * @since 2.9.2
 */
class FunnelKitPaymentIntegration extends PaymentIntegration {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'fkwcs_stripe',
		'fkwcs_stripe_ach',
	);

	/**
	 * Builds a FunnelKit payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return FunnelKitPaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): FunnelKitPaymentIntegration {
		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		// Square is not saving the authentication options in the normal settings, we retrieve the option information.
		$funnelkit_mode = get_option( 'fkwcs_mode', 'test' );

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::STRIPE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'test' === $funnelkit_mode ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$funnelkit_public_key = get_option( 'fkwcs_test_pub_key', '' );
			$funnelkit_secret_key = get_option( 'fkwcs_test_secret_key', '' );

			$integration->set_api_key_1( $funnelkit_secret_key ?? '' );
			$integration->set_api_key_2( $funnelkit_public_key ?? '' );
			$integration->set_test_mode( true );
		} else {
			$funnelkit_public_key = get_option( 'fkwcs_pub_key', '' );
			$funnelkit_secret_key = get_option( 'fkwcs_secret_key', '' );

			$integration->set_api_key_1( $funnelkit_secret_key ?? '' );
			$integration->set_api_key_2( $funnelkit_public_key ?? '' );
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

		// SQUARE should contain the token and location id.
		if ( empty( $this->get_api_key_1() ) || empty( $this->get_api_key_2() ) ) {
			return false;
		}

		return true;
	}
}
