<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Airwallex payment integration.
 *
 * @package Autoship
 * @since 2.9.2
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the Airwallex payment integration.
 *
 * @package Autoship
 * @since 2.9.2
 */
class AirwallexPaymentIntegration extends PaymentIntegration {

	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'airwallex_card',
	);

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
}
