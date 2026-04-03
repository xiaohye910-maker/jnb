<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Confirmation Repository Interface.
 *
 * Contract for QuickLink confirmation persistence.
 *
 * @package Autoship\Services\QuickLinks\Confirmation\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation\Interfaces;

use Autoship\Domain\QuickLinks\QuickLinkConfirmation;

/**
 * Confirmation Repository Interface.
 *
 * Defines the contract for storing and retrieving QuickLink confirmations.
 */
interface ConfirmationRepositoryInterface {

	/**
	 * Save a new confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation Confirmation entity to save.
	 *
	 * @return bool True if saved successfully.
	 */
	public function save( QuickLinkConfirmation $confirmation ): bool;

	/**
	 * Update an existing confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation Confirmation entity to update.
	 *
	 * @return bool True if updated successfully.
	 */
	public function update( QuickLinkConfirmation $confirmation ): bool;

	/**
	 * Find a confirmation by UUID.
	 *
	 * @param string $uuid UUID to search for.
	 *
	 * @return QuickLinkConfirmation|null The confirmation or null if not found.
	 */
	public function find_by_uuid( string $uuid );

	/**
	 * Find a confirmation by UUID with pessimistic locking.
	 *
	 * This method should be used when updating the confirmation to prevent
	 * race conditions and double-execution.
	 *
	 * @param string $uuid UUID to search for.
	 *
	 * @return QuickLinkConfirmation|null The confirmation or null if not found.
	 */
	public function find_by_uuid_for_update( string $uuid );

	/**
	 * Find confirmations by scheduled order ID.
	 *
	 * @param int $scheduled_order_id Scheduled order ID.
	 *
	 * @return QuickLinkConfirmation[] Array of confirmations.
	 */
	public function find_by_scheduled_order( int $scheduled_order_id ): array;

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
	public function find_pending_for_slug_and_order( string $slug, int $scheduled_order_id );

	/**
	 * Delete confirmations older than specified days.
	 *
	 * @param int $days Number of days to retain.
	 *
	 * @return int Number of records deleted.
	 */
	public function delete_older_than( int $days ): int;

	/**
	 * Delete a confirmation by ID.
	 *
	 * @param int $id Confirmation ID.
	 *
	 * @return bool True if deleted successfully.
	 */
	public function delete( int $id ): bool;

	/**
	 * Count confirmations by status.
	 *
	 * @param string $status Status to count.
	 *
	 * @return int Number of confirmations with the given status.
	 */
	public function count_by_status( string $status ): int;

	/**
	 * Begin a database transaction.
	 *
	 * @return bool True if transaction started.
	 */
	public function begin_transaction(): bool;

	/**
	 * Commit the current transaction.
	 *
	 * @return bool True if committed successfully.
	 */
	public function commit(): bool;

	/**
	 * Rollback the current transaction.
	 *
	 * @return bool True if rolled back successfully.
	 */
	public function rollback(): bool;

	/**
	 * Check if the repository is available.
	 *
	 * @return bool True if the table exists and is ready.
	 */
	public function is_available(): bool;

	/**
	 * Get the table name.
	 *
	 * @return string The full table name.
	 */
	public function get_table_name(): string;
}
