<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Audit Function Interface.
 *
 * Abstracts WordPress core functions for testability.
 *
 * @package Autoship\Services\QuickLinks\AuditLog\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog\Interfaces;

/**
 * Audit Function Interface.
 *
 * Provides abstraction layer for WordPress core functions
 * used by the audit logging subsystem, enabling unit testing
 * without WordPress environment.
 */
interface AuditFunctionInterface {

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
	 * Get upload directory information.
	 *
	 * @return array Upload directory info with 'basedir', 'baseurl', etc.
	 */
	public function wp_upload_dir(): array;

	/**
	 * Create directory recursively.
	 *
	 * @param string $target Directory path to create.
	 *
	 * @return bool True if created or exists, false on failure.
	 */
	public function wp_mkdir_p( string $target ): bool;

	/**
	 * Generate hash.
	 *
	 * @param string $data   Data to hash.
	 * @param string $scheme Authentication scheme.
	 *
	 * @return string Hashed data.
	 */
	public function wp_hash( string $data, string $scheme = 'auth' ): string;

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
	 * Add trailing slash to path.
	 *
	 * @param string $value Path.
	 *
	 * @return string Path with trailing slash.
	 */
	public function trailingslashit( string $value ): string;

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
}
