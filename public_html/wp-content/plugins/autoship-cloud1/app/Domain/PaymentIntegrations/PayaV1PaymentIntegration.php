<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the PAYA_V1 payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;
use Exception;

/**
 * Represents the PAYA_V1 payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PayaV1PaymentIntegration extends PaymentIntegration {
	/**
	 * The allowed payment gateways for this integration.
	 *
	 * @var array
	 */
	private static array $allowed = array(
		'sagepaymentsusaapi',
	);

	/**
	 * Builds an PAYA_V1 payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return PayaV1PaymentIntegration
	 * @throws Exception When the gateway ID is not supported.
	 */
	public static function build( string $gateway_id, array $settings ): PayaV1PaymentIntegration {

		if ( ! in_array( $gateway_id, self::$allowed, true ) ) {
			throw new Exception( 'The current payment integration is not supported by this gateway.' );
		}

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::PAYA_V1 );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );

		$environment = 'testing' === $settings['status'] ? 'test' : 'live';

		if ( 'test' === $environment ) {
			$integration->set_api_account( $settings['M_id_testing'] ?? '' );
			$integration->set_api_key_1( $settings['M_key_testing'] ?? '' );
			$integration->set_test_mode( true );
		} else {
			$integration->set_api_account( $settings['M_id'] ?? '' );
			$integration->set_api_key_1( $settings['M_key'] ?? '' );
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
		if ( PaymentMethodType::PAYA_V1 !== $this->get_method_type() ) {
			return false;
		}

		if ( ! in_array( $this->get_method_id(), self::$allowed, true ) ) {
			return false;
		}

		// NMI should contain the id and the key.
		if ( empty( $this->get_api_account() ) || empty( $this->get_api_key_1() ) ) {
			return false;
		}

		return true;
	}
}
