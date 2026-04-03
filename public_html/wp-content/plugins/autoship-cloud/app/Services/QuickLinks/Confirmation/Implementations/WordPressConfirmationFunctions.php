<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress Confirmation Functions Implementation.
 *
 * Concrete implementation of ConfirmationFunctionInterface using WordPress core functions.
 *
 * @package Autoship\Services\QuickLinks\Confirmation\Implementations
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation\Implementations;

use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationFunctionInterface;

/**
 * WordPress Confirmation Functions Implementation.
 *
 * Provides WordPress core function implementations for
 * the confirmation subsystem.
 */
class WordPressConfirmationFunctions implements ConfirmationFunctionInterface {

	/**
	 * Get current time.
	 *
	 * @param string $type Type of time to retrieve ('mysql', 'timestamp', etc.).
	 * @param bool   $gmt  Whether to use GMT timezone.
	 *
	 * @return string|int The current time.
	 */
	public function current_time( string $type, bool $gmt = false ) {
		return current_time( $type, $gmt );
	}

	/**
	 * Get option value.
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value if option doesn't exist.
	 *
	 * @return mixed Option value.
	 */
	public function get_option( string $option, $default = false ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Matches WordPress get_option() signature.
		return get_option( $option, $default );
	}

	/**
	 * Update option value.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 *
	 * @return bool True if updated, false otherwise.
	 */
	public function update_option( string $option, $value ): bool {
		return update_option( $option, $value );
	}

	/**
	 * Delete option.
	 *
	 * @param string $option Option name.
	 *
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete_option( string $option ): bool {
		return delete_option( $option );
	}

	/**
	 * Get the global wpdb instance.
	 *
	 * @return object The WordPress database object.
	 */
	public function get_wpdb(): object {
		global $wpdb;
		return $wpdb;
	}

	/**
	 * Run dbDelta for table creation/modification.
	 *
	 * @param string $sql SQL statement.
	 *
	 * @return array Results from dbDelta.
	 */
	public function db_delta( string $sql ): array {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		return dbDelta( $sql );
	}

	/**
	 * Check if current user is logged in.
	 *
	 * @return bool True if user is logged in.
	 */
	public function is_user_logged_in(): bool {
		return is_user_logged_in();
	}

	/**
	 * Get current user ID.
	 *
	 * @return int User ID or 0 if not logged in.
	 */
	public function get_current_user_id(): int {
		return get_current_user_id();
	}

	/**
	 * Encode data to JSON.
	 *
	 * @param mixed $data  Data to encode.
	 * @param int   $flags JSON encoding flags.
	 *
	 * @return string|false JSON string or false on failure.
	 */
	public function wp_json_encode( $data, int $flags = 0 ) {
		return wp_json_encode( $data, $flags );
	}

	/**
	 * Generate a UUID v4.
	 *
	 * @return string UUID v4 string.
	 */
	public function generate_uuid(): string {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return wp_generate_uuid4();
		}

		// Fallback for older WordPress versions.
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0x0fff ) | 0x4000,
			wp_rand( 0, 0x3fff ) | 0x8000,
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff )
		);
	}

	/**
	 * Create a WordPress nonce.
	 *
	 * @param string|int $action Nonce action name.
	 *
	 * @return string The nonce token.
	 */
	public function wp_create_nonce( $action ): string {
		return wp_create_nonce( $action );
	}

	/**
	 * Verify a WordPress nonce.
	 *
	 * @param string     $nonce  Nonce value to verify.
	 * @param string|int $action Nonce action name.
	 *
	 * @return int|false 1 if valid and less than 12 hours old, 2 if valid but older, false if invalid.
	 */
	public function wp_verify_nonce( string $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Get home URL.
	 *
	 * @param string $path Optional path to append.
	 *
	 * @return string Home URL.
	 */
	public function home_url( string $path = '' ): string {
		return home_url( $path );
	}

	/**
	 * Get site ID for multisite or 1 for single site.
	 *
	 * @return int Site ID.
	 */
	public function get_current_blog_id(): int {
		return get_current_blog_id();
	}

	/**
	 * Execute a WordPress action hook.
	 *
	 * @param string $tag  The name of the action.
	 * @param mixed  ...$args Additional arguments passed to callbacks.
	 *
	 * @return void
	 */
	public function do_action( string $tag, ...$args ): void {
		do_action( $tag, ...$args );
	}

	/**
	 * Apply WordPress filters.
	 *
	 * @param string $tag   The name of the filter.
	 * @param mixed  $value The value to filter.
	 * @param mixed  ...$args Additional arguments passed to callbacks.
	 *
	 * @return mixed The filtered value.
	 */
	public function apply_filters( string $tag, $value, ...$args ) {
		return apply_filters( $tag, $value, ...$args );
	}

	/**
	 * Sanitize text field.
	 *
	 * @param string $str String to sanitize.
	 *
	 * @return string Sanitized string.
	 */
	public function sanitize_text_field( string $str ): string {
		return sanitize_text_field( $str );
	}

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP address.
	 */
	public function get_client_ip(): string {
		// Check for forwarded IP addresses (proxy/load balancer).
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_CLIENT_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );

				// Handle comma-separated IPs (X-Forwarded-For can contain multiple).
				if ( strpos( $ip, ',' ) !== false ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}

				// Validate IP address.
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Get user agent.
	 *
	 * @return string User agent string.
	 */
	public function get_user_agent(): string {
		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		}
		return '';
	}

	/**
	 * Get HTTP referer.
	 *
	 * @return string|null HTTP referer or null.
	 */
	public function get_referer() {
		return wp_get_referer();
	}
}
