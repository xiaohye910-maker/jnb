<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the Sage payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain\PaymentIntegrations;

use Autoship\Domain\PaymentIntegration;
use Autoship\Domain\PaymentMethodType;

/**
 * Represents the Sage payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class SagePaymentIntegration extends PaymentIntegration {

	/**
	 * Builds a Sage payment integration.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param array  $settings The gateway settings.
	 * @return SagePaymentIntegration
	 */
	public static function build( string $gateway_id, array $settings ): SagePaymentIntegration {
		$integration = new self();
		$integration->set_method_id( $gateway_id );
		$integration->set_method_type( PaymentMethodType::SAGE );
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
