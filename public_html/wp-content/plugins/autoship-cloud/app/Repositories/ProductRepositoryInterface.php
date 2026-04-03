<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Product Repository Interface.
 *
 * @package Autoship
 * @since 2.12.1
 */

namespace Autoship\Repositories;

/**
 * Interface for querying product IDs from the database.
 *
 * Consolidates the legacy procedural query functions into a reusable
 * contract that any module can depend on.
 *
 * @package Autoship\Repositories
 * @since 2.12.1
 */
interface ProductRepositoryInterface {

	/**
	 * Get all product IDs by type (no meta filters).
	 *
	 * Replaces autoship_batch_query_product_ids().
	 *
	 * @param string $type 'simple'|'variable'|'variation'|'product'|'all'.
	 *
	 * @return int[]
	 */
	public function get_product_ids( string $type = 'all' ): array;

	/**
	 * Get product IDs where _autoship_sync_active_enabled = 'yes'.
	 *
	 * Replaces autoship_batch_query_active_product_ids().
	 *
	 * @param string $type 'simple'|'variable'|'variation'|'product'|'all'.
	 *
	 * @return int[]
	 */
	public function get_active_product_ids( string $type = 'all' ): array;

	/**
	 * Get product IDs where any schedule meta-key = 'yes'.
	 *
	 * Replaces autoship_batch_query_maybe_active_product_ids().
	 *
	 * @param string $type 'simple'|'variable'|'variation'.
	 *
	 * @return int[]
	 */
	public function get_maybe_active_product_ids( string $type = 'all' ): array;
}
