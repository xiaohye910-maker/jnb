<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Confirmation Table Migration.
 *
 * Handles database table creation for QuickLink confirmations.
 *
 * @package Autoship\Services\QuickLinks\Confirmation
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation;

use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationFunctionInterface;

/**
 * Confirmation Table Migration.
 *
 * Creates and manages the database table for QuickLink confirmations.
 * Uses dbDelta for safe table creation/modification.
 */
class ConfirmationTableMigration {

	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'autoship_quicklink_confirmations';

	/**
	 * Database version for migrations.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Option name for storing database version.
	 *
	 * @var string
	 */
	const VERSION_OPTION = 'autoship_quicklink_confirmations_db_version';

	/**
	 * WordPress functions interface.
	 *
	 * @var ConfirmationFunctionInterface
	 */
	private $wp_functions;

	/**
	 * Full table name including prefix.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor.
	 *
	 * @param ConfirmationFunctionInterface $wp_functions WordPress functions implementation.
	 */
	public function __construct( ConfirmationFunctionInterface $wp_functions ) {
		$this->wp_functions = $wp_functions;
		$wpdb               = $this->wp_functions->get_wpdb();
		$this->table_name   = $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Run the migration.
	 *
	 * Creates or updates the confirmations table.
	 *
	 * @return bool True if migration was successful.
	 */
	public function run(): bool {
		$current_version = $this->wp_functions->get_option( self::VERSION_OPTION, '0.0.0' );

		if ( version_compare( $current_version, self::DB_VERSION, '>=' ) ) {
			return true; // Already up to date.
		}

		$result = $this->create_table();

		if ( $result ) {
			$this->wp_functions->update_option( self::VERSION_OPTION, self::DB_VERSION );
		}

		return $result;
	}

	/**
	 * Create the confirmations table.
	 *
	 * @return bool True if table was created successfully.
	 */
	private function create_table(): bool {
		$wpdb            = $this->wp_functions->get_wpdb();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
			id BIGINT UNSIGNED AUTO_INCREMENT,
			uuid VARCHAR(36) NOT NULL,
			slug VARCHAR(255) NOT NULL,
			scheduled_order_id BIGINT NOT NULL,
			site_id BIGINT NOT NULL,
			action_type TINYINT NOT NULL,
			action_name VARCHAR(50) NOT NULL,
			status VARCHAR(20) DEFAULT 'pending',
			ip_address VARCHAR(45) NULL,
			submission_ip_address VARCHAR(45) NULL,
			ip_changed TINYINT(1) DEFAULT 0,
			user_agent TEXT NULL,
			referer TEXT NULL,
			customer_id BIGINT NULL,
			wp_user_id BIGINT NULL,
			shown_at DATETIME NOT NULL,
			submitted_at DATETIME NULL,
			executed_at DATETIME NULL,
			expired_at DATETIME NULL,
			time_to_submit_seconds INT NULL,
			verification_metadata LONGTEXT NULL,
			execution_result LONGTEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY uuid (uuid),
			KEY idx_status (status),
			KEY idx_slug_order (slug, scheduled_order_id),
			KEY idx_shown_at (shown_at)
		) {$charset_collate};";

		$this->wp_functions->db_delta( $sql );

		// Verify table was created.
		return $this->table_exists();
	}

	/**
	 * Check if table exists.
	 *
	 * @return bool True if table exists.
	 */
	public function table_exists(): bool {
		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $this->table_name )
		) === $this->table_name;

		return $exists;
	}

	/**
	 * Drop the table.
	 *
	 * Use with caution - this permanently deletes all data.
	 *
	 * @return bool True if table was dropped.
	 */
	public function drop_table(): bool {
		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a safe class property.
		$result = $wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );

		if ( false !== $result ) {
			$this->wp_functions->delete_option( self::VERSION_OPTION );
			return true;
		}

		return false;
	}

	/**
	 * Get the full table name.
	 *
	 * @return string The full table name with prefix.
	 */
	public function get_table_name(): string {
		return $this->table_name;
	}

	/**
	 * Get the current database version.
	 *
	 * @return string The current database version.
	 */
	public function get_current_version(): string {
		return $this->wp_functions->get_option( self::VERSION_OPTION, '0.0.0' );
	}
}
