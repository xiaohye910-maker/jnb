<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Autoship Test Gateway payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;

/**
 * Represents the Autoship Test Gateway payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class TestPaymentIntegration extends PaymentIntegration {

	/**
	 * Builds an Autoship Test Gateway payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return TestPaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): TestPaymentIntegration {
		autoship_log_entry(
			__( 'Autoship Test Payment Gateway Detected', 'autoship' ),
			__( 'The following gateway was created as Test Payment integration.' )
		);

		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::TEST );
		$integration->set_method_name( $settings['title'] ?? '' );
		$integration->set_authorize_only( false );
		$integration->set_test_mode( true );

		return $integration;
	}
}
