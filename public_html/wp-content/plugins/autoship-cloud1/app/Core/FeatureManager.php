<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class handles the features enabled.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core;

/**
 * This class handles the features enabled.
 *
 * @package Autoship
 * @since 2.8.7
 */
class FeatureManager {

	/**
	 * The features enabled.
	 *
	 * @var array|string[]
	 */
	protected static array $features = array(
		'development_mode'                     => false,
		'quicklaunch'                          => true,
		'quicklaunch_lead_registration'        => true,
		'quicklaunch_account_login'            => true,
		'quicklaunch_account_logout'           => true,
		'quicklaunch_account_register'         => true,
		'quicklaunch_product_setup'            => true,
		'quicklaunch_payment_method_installer' => true,
		'quicklaunch_reset'                    => false,
		'quicklaunch_display_beacon'           => true,
		'product_sync'                         => true,
		'nextime'                              => true,
		'quicklaunch_phone_required'           => true,
	);

	/**
	 * Gets the value indicating if the feature is enabled or not.
	 *
	 * @param string $feature The name of the feature.
	 *
	 * @return bool
	 */
	public static function is_enabled( string $feature ): bool {
		return ! empty( self::$features[ $feature ] );
	}
}
