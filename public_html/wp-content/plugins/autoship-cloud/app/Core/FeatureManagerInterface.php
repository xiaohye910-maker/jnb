<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for feature management.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core;

/**
 * Interface for feature management.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface FeatureManagerInterface {

	/**
	 * Gets the value indicating if the feature is enabled or not.
	 *
	 * @param string $feature The name of the feature.
	 *
	 * @return bool
	 */
	public function is_enabled( string $feature ): bool;

	/**
	 * Gets the value indicating if a payment gateway feature is enabled.
	 *
	 * This method checks both the main 'payments' feature flag and the
	 * individual gateway feature flag.
	 *
	 * @param string $gateway_id The payment gateway ID (e.g., 'stripe', 'braintree_credit_card').
	 *
	 * @return bool
	 */
	public function is_payment_gateway_enabled( string $gateway_id ): bool;
}
