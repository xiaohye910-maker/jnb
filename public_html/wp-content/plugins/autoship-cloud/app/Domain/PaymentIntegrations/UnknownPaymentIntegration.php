<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Unknown or Undefined payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;

/**
 * Represents an unknown or undefined payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class UnknownPaymentIntegration extends PaymentIntegration {

	/**
	 * Builds an unknown or undefined payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return UnknownPaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): UnknownPaymentIntegration {
		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::UNDEFINED );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );
		$integration->set_test_mode( true );

		return $integration;
	}
}
