<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for WordPress settings/options abstraction.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core;

/**
 * Interface for WordPress settings/options abstraction.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface SettingsInterface {

	/**
	 * Gets an option value.
	 *
	 * @param string $name    The option name.
	 * @param mixed  $default The default value.
	 *
	 * @return mixed
	 */
	public function get_option( string $name, $default = false );

	/**
	 * Updates an option value.
	 *
	 * @param string $name  The option name.
	 * @param mixed  $value The option value.
	 *
	 * @return bool
	 */
	public function update_option( string $name, $value ): bool;

	/**
	 * Deletes an option.
	 *
	 * @param string $name The option name.
	 *
	 * @return bool
	 */
	public function delete_option( string $name ): bool;
}
