<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Cyber Source V1 payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;

/**
 * Represents the Cyber Source V1 payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class CyberSourcePaymentIntegration extends PaymentIntegration {

	/**
	 * Builds a CyberSource V1 payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return CyberSourcePaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): CyberSourcePaymentIntegration {
		autoship_log_entry(
			__( 'CyberSource V1 Payment Gateway Detected', 'autoship' ),
			__( 'The following gateway was created as Cyber Source V1 integration. This integration is disabled.' )
		);

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::CYBER_SOURCE );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );
		$integration->set_test_mode( true );

		return $integration;
	}

	/**
	 * Gets the value indicating if the payment integration is valid or not.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		// This enforces the payment integration to be disabled.
		return false;
	}
}
