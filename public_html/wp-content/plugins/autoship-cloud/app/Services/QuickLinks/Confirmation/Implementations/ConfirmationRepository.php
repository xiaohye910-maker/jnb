<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Confirmation Repository.
 *
 * Database implementation for QuickLink confirmation persistence.
 *
 * @package Autoship\Services\QuickLinks\Confirmation\Implementations
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation\Implementations;

use Autoship\Domain\QuickLinks\QuickLinkConfirmation;
use Autoship\Services\QuickLinks\Confirmation\ConfirmationTableMigration;
use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationFunctionInterface;
use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationRepositoryInterface;

/**
 * Confirmation Repository.
 *
 * Implements database operations for QuickLink confirmations
 * with pessimistic locking support for race condition prevention.
 */
class ConfirmationRepository implements ConfirmationRepositoryInterface {

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
	 * Whether the table has been verified.
	 *
	 * @var bool
	 */
	private $table_verified = false;

	/**
	 * Constructor.
	 *
	 * @param ConfirmationFunctionInterface $wp_functions WordPress functions implementation.
	 */
	public function __construct( ConfirmationFunctionInterface $wp_functions ) {
		$this->wp_functions = $wp_functions;
		$wpdb               = $this->wp_functions->get_wpdb();
		$this->table_name   = $wpdb->prefix . ConfirmationTableMigration::TABLE_NAME;
	}

	/**
	 * Save a new confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation Confirmation entity to save.
	 *
	 * @return bool True if saved successfully.
	 */
	public function save( QuickLinkConfirmation $confirmation ): bool {
		if ( ! $this->ensure_table_exists() ) {
			return false;
		}

		$wpdb = $this->wp_functions->get_wpdb();
		$data = $confirmation->to_array();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$this->table_name,
			$data,
			$this->get_format_array( $data )
		);

		if ( false !== $result ) {
			$confirmation->set_id( (int) $wpdb->insert_id );
			return true;
		}

		return false;
	}

	/**
	 * Update an existing confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation Confirmation entity to update.
	 *
	 * @return bool True if updated successfully.
	 */
	public function update( QuickLinkConfirmation $confirmation ): bool {
		if ( ! $this->ensure_table_exists() ) {
			return false;
		}

		if ( null === $confirmation->get_id() ) {
			return false;
		}

		$wpdb = $this->wp_functions->get_wpdb();
		$data = $confirmation->to_array();

		// Remove fields that shouldn't be updated.
		unset( $data['uuid'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$this->table_name,
			$data,
			array( 'id' => $confirmation->get_id() ),
			$this->get_format_array( $data ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Find a confirmation by UUID.
	 *
	 * @param string $uuid UUID to search for.
	 *
	 * @return QuickLinkConfirmation|null The confirmation or null if not found.
	 */
	public function find_by_uuid( string $uuid ) {
		if ( ! $this->ensure_table_exists() ) {
			return null;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE uuid = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$uuid
			)
		);

		if ( null === $row ) {
			return null;
		}

		return QuickLinkConfirmation::from_db_row( $row );
	}

	/**
	 * Find a confirmation by UUID with pessimistic locking.
	 *
	 * This method uses SELECT ... FOR UPDATE to lock the row and prevent
	 * race conditions when multiple requests try to confirm the same link.
	 *
	 * IMPORTANT: Must be called within a transaction (begin_transaction).
	 *
	 * @param string $uuid UUID to search for.
	 *
	 * @return QuickLinkConfirmation|null The confirmation or null if not found.
	 */
	public function find_by_uuid_for_update( string $uuid ) {
		if ( ! $this->ensure_table_exists() ) {
			return null;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE uuid = %s FOR UPDATE", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$uuid
			)
		);

		if ( null === $row ) {
			return null;
		}

		return QuickLinkConfirmation::from_db_row( $row );
	}

	/**
	 * Find confirmations by scheduled order ID.
	 *
	 * @param int $scheduled_order_id Scheduled order ID.
	 *
	 * @return QuickLinkConfirmation[] Array of confirmations.
	 */
	public function find_by_scheduled_order( int $scheduled_order_id ): array {
		if ( ! $this->ensure_table_exists() ) {
			return array();
		}

		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE scheduled_order_id = %d ORDER BY created_at DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$scheduled_order_id
			)
		);

		if ( empty( $rows ) ) {
			return array();
		}

		return array_map(
			function ( $row ) {
				return QuickLinkConfirmation::from_db_row( $row );
			},
			$rows
		);
	}

	/**
	 * Find pending confirmations for a slug and order.
	 *
	 * Used to check if there's already a pending confirmation.
	 *
	 * @param string $slug               QuickLink slug.
	 * @param int    $scheduled_order_id Scheduled order ID.
	 *
	 * @return QuickLinkConfirmation|null Pending confirmation or null.
	 */
	public function find_pending_for_slug_and_order( string $slug, int $scheduled_order_id ) {
		if ( ! $this->ensure_table_exists() ) {
			return null;
		}

		$wpdb           = $this->wp_functions->get_wpdb();
		$pending_status = QuickLinkConfirmation::STATUS_PENDING;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE slug = %s AND scheduled_order_id = %d AND status = %s ORDER BY created_at DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$slug,
				$scheduled_order_id,
				$pending_status
			)
		);

		if ( null === $row ) {
			return null;
		}

		return QuickLinkConfirmation::from_db_row( $row );
	}

	/**
	 * Delete confirmations older than specified days.
	 *
	 * @param int $days Number of days to retain.
	 *
	 * @return int Number of records deleted.
	 */
	public function delete_older_than( int $days ): int {
		if ( ! $this->ensure_table_exists() ) {
			return 0;
		}

		$wpdb        = $this->wp_functions->get_wpdb();
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table_name} WHERE created_at < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$cutoff_date
			)
		);

		return false === $deleted ? 0 : (int) $deleted;
	}

	/**
	 * Delete a confirmation by ID.
	 *
	 * @param int $id Confirmation ID.
	 *
	 * @return bool True if deleted successfully.
	 */
	public function delete( int $id ): bool {
		if ( ! $this->ensure_table_exists() ) {
			return false;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Count confirmations by status.
	 *
	 * @param string $status Status to count.
	 *
	 * @return int Number of confirmations with the given status.
	 */
	public function count_by_status( string $status ): int {
		if ( ! $this->ensure_table_exists() ) {
			return 0;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$status
			)
		);

		return null === $count ? 0 : (int) $count;
	}

	/**
	 * Begin a database transaction.
	 *
	 * @return bool True if transaction started.
	 */
	public function begin_transaction(): bool {
		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $wpdb->query( 'START TRANSACTION' );
	}

	/**
	 * Commit the current transaction.
	 *
	 * @return bool True if committed successfully.
	 */
	public function commit(): bool {
		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $wpdb->query( 'COMMIT' );
	}

	/**
	 * Rollback the current transaction.
	 *
	 * @return bool True if rolled back successfully.
	 */
	public function rollback(): bool {
		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $wpdb->query( 'ROLLBACK' );
	}

	/**
	 * Check if the repository is available.
	 *
	 * @return bool True if the table exists and is ready.
	 */
	public function is_available(): bool {
		return $this->ensure_table_exists();
	}

	/**
	 * Get the table name.
	 *
	 * @return string The full table name.
	 */
	public function get_table_name(): string {
		return $this->table_name;
	}

	/**
	 * Ensure the table exists.
	 *
	 * @return bool True if table exists.
	 */
	private function ensure_table_exists(): bool {
		if ( $this->table_verified ) {
			return true;
		}

		$wpdb = $this->wp_functions->get_wpdb();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $this->table_name )
		) === $this->table_name;

		$this->table_verified = $exists;
		return $exists;
	}

	/**
	 * Get format array for wpdb prepare.
	 *
	 * @param array $data Data array.
	 *
	 * @return array Format specifications.
	 */
	private function get_format_array( array $data ): array {
		$formats = array();

		foreach ( $data as $key => $value ) {
			switch ( $key ) {
				case 'scheduled_order_id':
				case 'site_id':
				case 'action_type':
				case 'ip_changed':
				case 'customer_id':
				case 'wp_user_id':
				case 'time_to_submit_seconds':
					$formats[] = '%d';
					break;
				default:
					$formats[] = '%s';
					break;
			}
		}

		return $formats;
	}
}
