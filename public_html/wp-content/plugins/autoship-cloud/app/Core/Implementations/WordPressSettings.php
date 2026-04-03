<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the settings interface.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\SettingsInterface;

/**
 * WordPress implementation of the settings interface.
 *
 * @package Autoship
 * @since 2.11.0
 */
class WordPressSettings implements SettingsInterface {

	/**
	 * Gets an option value.
	 *
	 * @param string $name    The option name.
	 * @param mixed  $default The default value.
	 *
	 * @return mixed
	 */
	public function get_option( string $name, $default = false ) {
		return \get_option( $name, $default );
	}

	/**
	 * Updates an option value.
	 *
	 * @param string $name  The option name.
	 * @param mixed  $value The option value.
	 *
	 * @return bool
	 */
	public function update_option( string $name, $value ): bool {
		return \update_option( $name, $value );
	}

	/**
	 * Deletes an option.
	 *
	 * @param string $name The option name.
	 *
	 * @return bool
	 */
	public function delete_option( string $name ): bool {
		return \delete_option( $name );
	}
}
