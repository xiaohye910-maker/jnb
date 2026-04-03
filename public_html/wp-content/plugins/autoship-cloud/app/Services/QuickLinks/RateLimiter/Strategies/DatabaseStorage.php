<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Database Storage Strategy for Rate Limiter.
 *
 * Uses a custom database table for rate limit storage.
 * Creates the table lazily on first use.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter\Strategies
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter\Strategies;

use Autoship\Services\QuickLinks\RateLimiter\Interfaces\RateLimiterStorageInterface;

/**
 * Database Storage Strategy.
 *
 * Stores rate limit data in a custom WordPress database table.
 * The table is created lazily on first use if it doesn't exist.
 */
class DatabaseStorage implements RateLimiterStorageInterface {

	/**
	 * Full table name including prefix.
	 *
	 * @var string
	 */
	private string $table_name;

	/**
	 * Whether the table has been checked/created.
	 *
	 * @var bool
	 */
	private bool $table_checked = false;

	/**
	 * Constructor.
	 *
	 * @param array $settings Settings array with optional 'table_name' key.
	 */
	public function __construct( array $settings = array() ) {
		global $wpdb;
		$table_suffix     = isset( $settings['table_name'] ) ? $settings['table_name'] : 'autoship_rate_limits';
		$this->table_name = $wpdb->prefix . $table_suffix;
	}

	/**
	 * Get the current number of attempts for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return int The number of attempts (0 if none or expired).
	 */
	public function get_attempts( string $key ): int {
		if ( ! $this->ensure_table_exists() ) {
			return 0;
		}

		global $wpdb;

		$hashed_key = $this->hash_key( $key );
		$now        = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT attempts FROM {$this->table_name} WHERE rate_key = %s AND expires_at > %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$hashed_key,
				$now
			)
		);

		return null !== $result ? (int) $result : 0;
	}

	/**
	 * Increment the attempt count for a key.
	 *
	 * @param string $key         The rate limit key.
	 * @param int    $ttl_seconds Time-to-live in seconds.
	 *
	 * @return int The new attempt count after incrementing.
	 */
	public function increment( string $key, int $ttl_seconds ): int {
		if ( ! $this->ensure_table_exists() ) {
			return 0;
		}

		global $wpdb;

		$hashed_key = $this->hash_key( $key );
		$now        = current_time( 'mysql', true );
		$expires_at = gmdate( 'Y-m-d H:i:s', time() + $ttl_seconds );

		// Try to update existing non-expired record.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->table_name} SET attempts = attempts + 1 WHERE rate_key = %s AND expires_at > %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$hashed_key,
				$now
			)
		);

		if ( $updated > 0 ) {
			return $this->get_attempts( $key );
		}

		// Delete any expired record for this key.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->table_name,
			array( 'rate_key' => $hashed_key ),
			array( '%s' )
		);

		// Insert new record.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$this->table_name,
			array(
				'rate_key'   => $hashed_key,
				'attempts'   => 1,
				'expires_at' => $expires_at,
				'created_at' => $now,
			),
			array( '%s', '%d', '%s', '%s' )
		);

		return 1;
	}

	/**
	 * Reset (delete) the attempt count for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return void
	 */
	public function reset( string $key ): void {
		if ( ! $this->ensure_table_exists() ) {
			return;
		}

		global $wpdb;

		$hashed_key = $this->hash_key( $key );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->table_name,
			array( 'rate_key' => $hashed_key ),
			array( '%s' )
		);
	}

	/**
	 * Check if this storage strategy is available.
	 *
	 * Tries to create the table if it doesn't exist.
	 *
	 * @return bool True if the table exists or was created.
	 */
	public function is_available(): bool {
		return $this->ensure_table_exists();
	}

	/**
	 * Clean up expired entries.
	 *
	 * @return void
	 */
	public function cleanup(): void {
		if ( ! $this->ensure_table_exists() ) {
			return;
		}

		global $wpdb;

		$now = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table_name} WHERE expires_at <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$now
			)
		);
	}

	/**
	 * Ensure the table exists, creating it if necessary.
	 *
	 * @return bool True if the table exists.
	 */
	private function ensure_table_exists(): bool {
		if ( $this->table_checked ) {
			return true;
		}

		global $wpdb;

		// Check if table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $this->table_name )
		) === $this->table_name;

		if ( ! $exists ) {
			$this->create_table();

			// Check again.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exists = $wpdb->get_var(
				$wpdb->prepare( 'SHOW TABLES LIKE %s', $this->table_name )
			) === $this->table_name;
		}

		$this->table_checked = $exists;
		return $exists;
	}

	/**
	 * Create the rate limits table.
	 *
	 * @return void
	 */
	private function create_table(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
			id BIGINT UNSIGNED AUTO_INCREMENT,
			rate_key VARCHAR(64) NOT NULL,
			attempts INT UNSIGNED NOT NULL DEFAULT 1,
			expires_at DATETIME NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY rate_key (rate_key),
			KEY expires_at (expires_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Hash the key for storage.
	 *
	 * Uses MD5 to create a fixed-length key that fits in VARCHAR(64).
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return string The hashed key.
	 */
	private function hash_key( string $key ): string {
		return md5( $key );
	}

	/**
	 * Get the table name.
	 *
	 * @return string The full table name.
	 */
	public function get_table_name(): string {
		return $this->table_name;
	}
}
