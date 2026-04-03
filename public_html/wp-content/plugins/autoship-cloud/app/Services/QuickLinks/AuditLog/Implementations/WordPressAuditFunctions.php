<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress Audit Functions Implementation.
 *
 * Concrete implementation of AuditFunctionInterface using WordPress core functions.
 *
 * @package Autoship\Services\QuickLinks\AuditLog\Implementations
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog\Implementations;

use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;

/**
 * WordPress Audit Functions Implementation.
 *
 * Provides WordPress core function implementations for
 * the audit logging subsystem.
 */
class WordPressAuditFunctions implements AuditFunctionInterface {

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
	 * Get upload directory information.
	 *
	 * @return array Upload directory info with 'basedir', 'baseurl', etc.
	 */
	public function wp_upload_dir(): array {
		return wp_upload_dir();
	}

	/**
	 * Create directory recursively.
	 *
	 * @param string $target Directory path to create.
	 *
	 * @return bool True if created or exists, false on failure.
	 */
	public function wp_mkdir_p( string $target ): bool {
		return wp_mkdir_p( $target );
	}

	/**
	 * Generate hash.
	 *
	 * @param string $data   Data to hash.
	 * @param string $scheme Authentication scheme.
	 *
	 * @return string Hashed data.
	 */
	public function wp_hash( string $data, string $scheme = 'auth' ): string {
		return wp_hash( $data, $scheme );
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
	 * Add trailing slash to path.
	 *
	 * @param string $value Path.
	 *
	 * @return string Path with trailing slash.
	 */
	public function trailingslashit( string $value ): string {
		return trailingslashit( $value );
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
}
