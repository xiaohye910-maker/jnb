<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Database Audit Logger Strategy.
 *
 * Primary audit logging strategy using a custom database table.
 * Creates the table lazily on first use.
 *
 * @package Autoship\Services\QuickLinks\AuditLog\Strategies
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog\Strategies;

use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditLoggerInterface;
use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;
use Autoship\Services\QuickLinks\AuditLog\DTOs\AuditEntry;
use Autoship\Services\Logging\LoggerInterface;

/**
 * Database Audit Logger Strategy.
 *
 * Stores audit log entries in a custom WordPress database table.
 * The table is created lazily on first use if it doesn't exist.
 */
class DatabaseAuditLogger implements AuditLoggerInterface {

	/**
	 * Full table name including prefix.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Whether the table has been checked/created.
	 *
	 * @var bool
	 */
	private $table_checked = false;

	/**
	 * WordPress functions interface.
	 *
	 * @var AuditFunctionInterface
	 */
	private $wp_functions;

	/**
	 * Logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param AuditFunctionInterface $wp_functions WordPress functions interface.
	 * @param LoggerInterface        $logger       The logger instance.
	 * @param array                  $settings     Settings array with optional 'table_name' key.
	 */
	public function __construct( AuditFunctionInterface $wp_functions, LoggerInterface $logger, array $settings = array() ) {
		$this->wp_functions = $wp_functions;
		$this->logger       = $logger;
		$wpdb               = $this->wp_functions->get_wpdb();
		$table_suffix       = isset( $settings['table_name'] ) ? $settings['table_name'] : 'autoship_quicklink_audit_log';
		$this->table_name   = $wpdb->prefix . $table_suffix;
	}

	/**
	 * Log an audit entry.
	 *
	 * @param AuditEntry $entry The audit entry to log.
	 *
	 * @return bool True if logging succeeded.
	 */
	public function log( AuditEntry $entry ): bool {
		if ( ! $this->ensure_table_exists() ) {
			$this->logger->error(
				'QuickLink Audit DB',
				sprintf(
					'Cannot log audit entry - table %s does not exist or could not be created',
					$this->table_name
				)
			);
			return false;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		$data    = $entry->to_array();
		$formats = $this->get_column_formats();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$this->table_name,
			$data,
			$formats
		);

		if ( false === $result ) {
			$this->logger->error(
				'QuickLink Audit DB',
				sprintf(
					'Failed to insert audit entry into %s. DB Error: %s',
					$this->table_name,
					$wpdb->last_error
				)
			);
			return false;
		}

		return true;
	}

	/**
	 * Check if database logging is available.
	 *
	 * @return bool True if available.
	 */
	public function is_available(): bool {
		return $this->ensure_table_exists();
	}

	/**
	 * Clean up old entries.
	 *
	 * @param int $retention_days Days to retain.
	 *
	 * @return int Number deleted.
	 */
	public function cleanup( int $retention_days ): int {
		if ( ! $this->ensure_table_exists() ) {
			return 0;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property.
				"DELETE FROM {$this->table_name} WHERE created_at < %s",
				$cutoff_date
			)
		);

		return is_int( $deleted ) ? $deleted : 0;
	}

	/**
	 * Get strategy name.
	 *
	 * @return string
	 */
	public function get_strategy_name(): string {
		return 'database';
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

		$wpdb = $this->wp_functions->get_wpdb();

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
	 * Create the audit log table.
	 *
	 * @return void
	 */
	private function create_table(): void {
		$wpdb = $this->wp_functions->get_wpdb();

		$charset_collate = $wpdb->get_charset_collate();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "CREATE TABLE {$this->table_name} (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			quicklink_id BIGINT UNSIGNED NULL,
			slug VARCHAR(100) NULL,
			action_type TINYINT NOT NULL,
			scheduled_order_id BIGINT UNSIGNED NOT NULL,
			customer_id BIGINT UNSIGNED NULL,
			ip_address VARCHAR(45) NOT NULL,
			user_agent VARCHAR(500) NULL,
			wp_user_id BIGINT UNSIGNED NULL,
			token_hash VARCHAR(64) NULL,
			success TINYINT(1) NOT NULL DEFAULT 0,
			error_code VARCHAR(50) NULL,
			error_message VARCHAR(500) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			execution_time_ms INT UNSIGNED NULL,
			flagged TINYINT(1) NOT NULL DEFAULT 0,
			flag_reason VARCHAR(100) NULL,
			INDEX idx_quicklink_time (quicklink_id, created_at),
			INDEX idx_customer_time (customer_id, created_at),
			INDEX idx_ip_time (ip_address, created_at),
			INDEX idx_action_time (action_type, created_at),
			INDEX idx_slug_time (slug, created_at),
			INDEX idx_flagged (flagged, created_at),
			INDEX idx_success (success, created_at)
		) {$charset_collate};";

		$this->wp_functions->db_delta( $sql );
	}

	/**
	 * Get column format specifiers for wpdb.
	 *
	 * @return array
	 */
	private function get_column_formats(): array {
		return array(
			'%d', // quicklink_id.
			'%s', // slug.
			'%d', // action_type.
			'%d', // scheduled_order_id.
			'%d', // customer_id.
			'%s', // ip_address.
			'%s', // user_agent.
			'%d', // wp_user_id.
			'%s', // token_hash.
			'%d', // success.
			'%s', // error_code.
			'%s', // error_message.
			'%s', // created_at.
			'%d', // execution_time_ms.
			'%d', // flagged.
			'%s', // flag_reason.
		);
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
