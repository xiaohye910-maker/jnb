<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Confirmation Function Interface.
 *
 * Abstracts WordPress core functions for testability.
 *
 * @package Autoship\Services\QuickLinks\Confirmation\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation\Interfaces;

/**
 * Confirmation Function Interface.
 *
 * Provides abstraction layer for WordPress core functions
 * used by the confirmation subsystem, enabling unit testing
 * without WordPress environment.
 */
interface ConfirmationFunctionInterface {

	/**
	 * Get current time.
	 *
	 * @param string $type Type of time to retrieve ('mysql', 'timestamp', etc.).
	 * @param bool   $gmt  Whether to use GMT timezone.
	 *
	 * @return string|int The current time.
	 */
	public function current_time( string $type, bool $gmt = false );

	/**
	 * Get option value.
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value if option doesn't exist.
	 *
	 * @return mixed Option value.
	 */
	public function get_option( string $option, $default = false ); // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Matches WordPress get_option() signature.

	/**
	 * Update option value.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 *
	 * @return bool True if updated, false otherwise.
	 */
	public function update_option( string $option, $value ): bool;

	/**
	 * Delete option.
	 *
	 * @param string $option Option name.
	 *
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete_option( string $option ): bool;

	/**
	 * Get the global wpdb instance.
	 *
	 * @return object The WordPress database object.
	 */
	public function get_wpdb(): object;

	/**
	 * Run dbDelta for table creation/modification.
	 *
	 * @param string $sql SQL statement.
	 *
	 * @return array Results from dbDelta.
	 */
	public function db_delta( string $sql ): array;

	/**
	 * Check if current user is logged in.
	 *
	 * @return bool True if user is logged in.
	 */
	public function is_user_logged_in(): bool;

	/**
	 * Get current user ID.
	 *
	 * @return int User ID or 0 if not logged in.
	 */
	public function get_current_user_id(): int;

	/**
	 * Encode data to JSON.
	 *
	 * @param mixed $data  Data to encode.
	 * @param int   $flags JSON encoding flags.
	 *
	 * @return string|false JSON string or false on failure.
	 */
	public function wp_json_encode( $data, int $flags = 0 );

	/**
	 * Generate a UUID v4.
	 *
	 * @return string UUID v4 string.
	 */
	public function generate_uuid(): string;

	/**
	 * Create a WordPress nonce.
	 *
	 * @param string|int $action Nonce action name.
	 *
	 * @return string The nonce token.
	 */
	public function wp_create_nonce( $action ): string;

	/**
	 * Verify a WordPress nonce.
	 *
	 * @param string     $nonce  Nonce value to verify.
	 * @param string|int $action Nonce action name.
	 *
	 * @return int|false 1 if valid and less than 12 hours old, 2 if valid but older, false if invalid.
	 */
	public function wp_verify_nonce( string $nonce, $action );

	/**
	 * Get home URL.
	 *
	 * @param string $path Optional path to append.
	 *
	 * @return string Home URL.
	 */
	public function home_url( string $path = '' ): string;

	/**
	 * Get site ID for multisite or 1 for single site.
	 *
	 * @return int Site ID.
	 */
	public function get_current_blog_id(): int;

	/**
	 * Execute a WordPress action hook.
	 *
	 * @param string $tag  The name of the action.
	 * @param mixed  ...$args Additional arguments passed to callbacks.
	 *
	 * @return void
	 */
	public function do_action( string $tag, ...$args ): void;

	/**
	 * Apply WordPress filters.
	 *
	 * @param string $tag   The name of the filter.
	 * @param mixed  $value The value to filter.
	 * @param mixed  ...$args Additional arguments passed to callbacks.
	 *
	 * @return mixed The filtered value.
	 */
	public function apply_filters( string $tag, $value, ...$args );

	/**
	 * Sanitize text field.
	 *
	 * @param string $str String to sanitize.
	 *
	 * @return string Sanitized string.
	 */
	public function sanitize_text_field( string $str ): string;

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP address.
	 */
	public function get_client_ip(): string;

	/**
	 * Get user agent.
	 *
	 * @return string User agent string.
	 */
	public function get_user_agent(): string;

	/**
	 * Get HTTP referer.
	 *
	 * @return string|null HTTP referer or null.
	 */
	public function get_referer();
}
