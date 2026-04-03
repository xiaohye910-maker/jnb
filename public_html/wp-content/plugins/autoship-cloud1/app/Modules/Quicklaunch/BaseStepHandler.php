<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Represents the base class for all step handlers.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

/**
 * The base class for all step handlers.
 *
 * @package Autoship
 * @since 2.8.7
 */
abstract class BaseStepHandler {
	/**
	 * Gets the integer value of the given key from the POST array.
	 *
	 * @param string $key The key to get from the POST array.
	 * @return int
	 */
	protected function get_post_int_value( string $key ): int {
		return intval( $this->get_post_string_value( $key ) );
	}

	/**
	 * Gets the bool value of the given key from the POST array.
	 *
	 * @param string $key The key to get from the POST array.
	 * @return bool
	 */
	protected function get_post_bool_value( string $key ): bool {
		$original = $this->get_post_string_value( $key );
		return filter_var( $original, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Gets the string value of the given key from the POST array.
	 *
	 * @param string $key The key to get from the POST array.
	 * @return string
	 */
	protected function get_post_string_value( string $key ): string {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
