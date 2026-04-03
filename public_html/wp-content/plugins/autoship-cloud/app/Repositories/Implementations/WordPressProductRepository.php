<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress Product Repository implementation.
 *
 * @package Autoship
 * @since 2.12.1
 */

namespace Autoship\Repositories\Implementations;

use Autoship\Repositories\ProductRepositoryInterface;

/**
 * WordPress implementation of the Product Repository.
 *
 * Queries the WordPress database directly using $wpdb for
 * product ID retrieval. Preserves the exact SQL from the legacy
 * procedural functions.
 *
 * @package Autoship\Repositories\Implementations
 * @since 2.12.1
 */
class WordPressProductRepository implements ProductRepositoryInterface {

	/**
	 * Get all product IDs by type (no meta filters).
	 *
	 * @param string $type 'simple'|'variable'|'variation'|'product'|'all'.
	 *
	 * @return int[]
	 */
	public function get_product_ids( string $type = 'all' ): array {
		global $wpdb;
		$wp = $wpdb->prefix;

		$query = "
		SELECT parent.ID
		FROM {$wp}posts as parent";

		if ( 'simple' === $type ) {

			$query .= "
			WHERE parent.ID NOT IN (
				SELECT child.post_parent
				FROM {$wp}posts as child WHERE child.post_parent > 0 AND child.post_type = 'product_variation' )
				AND parent.post_type = 'product'";

		} elseif ( 'variable' === $type ) {

			$query .= "
			WHERE parent.ID IN (
				SELECT child.post_parent
				FROM {$wp}posts as child WHERE child.post_parent > 0 AND child.post_type = 'product_variation' )
			AND parent.post_type = 'product'";

		} elseif ( 'variation' === $type ) {

			$query .= "
			WHERE parent.post_parent > 0 AND parent.post_type = 'product_variation'";

		} elseif ( 'product' === $type ) {

			$query .= "
			WHERE parent.post_type = 'product'";

		} else {

			$query .= "
			WHERE parent.post_type IN ('product','product_variation')";

		}

		$query .= ' ORDER BY ID ASC';

		return array_map( 'intval', $wpdb->get_col( $query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get product IDs where _autoship_sync_active_enabled = 'yes'.
	 *
	 * @param string $type 'simple'|'variable'|'variation'|'product'|'all'.
	 *
	 * @return int[]
	 */
	public function get_active_product_ids( string $type = 'all' ): array {
		global $wpdb;
		$wp = $wpdb->prefix;

		$query = "
		SELECT parent.ID
		FROM {$wp}posts as parent ";

		if ( 'simple' === $type ) {

			$query .= "
			INNER JOIN {$wp}postmeta as meta on parent.ID = meta.post_id
			WHERE parent.ID IN (
				SELECT object_id FROM {$wp}term_relationships as term_index WHERE term_index.term_taxonomy_id IN ( SELECT terms.term_id FROM {$wp}terms as terms WHERE terms.name = 'simple' ) )
				AND parent.post_type = 'product' AND meta.meta_key = '_autoship_sync_active_enabled' AND meta.meta_value = 'yes'";

		} elseif ( 'variable' === $type ) {

			$query .= "
			INNER JOIN {$wp}postmeta as meta on parent.ID = meta.post_id
			WHERE parent.ID IN (
				SELECT object_id FROM {$wp}term_relationships as term_index WHERE term_index.term_taxonomy_id IN ( SELECT terms.term_id FROM {$wp}terms as terms WHERE terms.name = 'variable' ) )
			AND parent.post_type = 'product' AND meta.meta_key = '_autoship_sync_active_enabled' AND meta.meta_value = 'yes'";

		} elseif ( 'variation' === $type ) {

			$query .= "
			WHERE parent.post_parent > 0 AND parent.post_type = 'product_variation'
			AND parent.post_parent IN (
				SELECT child.ID
				FROM {$wp}posts as child
				INNER JOIN {$wp}postmeta as childmeta on child.ID = childmeta.post_id
				WHERE child.ID IN (
					SELECT object_id FROM {$wp}term_relationships as term_index WHERE term_index.term_taxonomy_id IN ( SELECT terms.term_id FROM {$wp}terms as terms WHERE terms.name = 'variable' ) )
				AND childmeta.meta_key = '_autoship_sync_active_enabled' AND childmeta.meta_value = 'yes' )";

		} elseif ( 'product' === $type ) {

			$query .= "
			INNER JOIN {$wp}postmeta as meta on parent.ID = meta.post_id
			WHERE parent.post_type = 'product'
			AND meta.meta_key = '_autoship_sync_active_enabled' AND meta.meta_value = 'yes'";

		} else {

			$query .= "
			INNER JOIN {$wp}postmeta as meta on parent.ID = meta.post_id
			WHERE parent.post_type IN ('product','product_variation')
			AND meta.meta_key = '_autoship_sync_active_enabled' AND meta.meta_value = 'yes'";

		}

		$query .= ' ORDER BY ID ASC';

		return array_map( 'intval', $wpdb->get_col( $query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get product IDs where any schedule meta-key = 'yes'.
	 *
	 * @param string $type 'simple'|'variable'|'variation'.
	 *
	 * @return int[]
	 */
	public function get_maybe_active_product_ids( string $type = 'all' ): array {
		global $wpdb;
		$wp = $wpdb->prefix;

		$query = "
		SELECT parent.ID
		FROM {$wp}posts as parent ";

		if ( 'simple' === $type ) {

			$query .= "
			INNER JOIN {$wp}postmeta as meta on parent.ID = meta.post_id
			WHERE parent.ID NOT IN (
				SELECT child.post_parent
				FROM {$wp}posts as child WHERE child.post_parent > 0 AND child.post_type = 'product_variation' )
				AND parent.post_type = 'product' AND
				( meta.meta_key LIKE '_autoship_schedule_process_enabled' OR meta.meta_key LIKE '_autoship_schedule_order_enabled' OR meta.meta_key LIKE '_autoship_schedule_options_enabled' ) AND meta.meta_value = 'yes' ";

		} elseif ( 'variable' === $type ) {

			$query .= "
			INNER JOIN {$wp}postmeta as meta on parent.ID = meta.post_id
			WHERE parent.ID IN (
				SELECT child.post_parent
				FROM {$wp}posts as child WHERE child.post_parent > 0 AND child.post_type = 'product_variation' )
			AND parent.post_type = 'product' AND
			( meta.meta_key LIKE '_autoship_schedule_process_enabled' OR meta.meta_key LIKE '_autoship_schedule_order_enabled' OR meta.meta_key LIKE '_autoship_schedule_options_enabled' ) AND meta.meta_value = 'yes' ";

		} elseif ( 'variation' === $type ) {

			$query .= "
			WHERE parent.post_parent > 0 AND parent.post_type = 'product_variation'
			AND parent.post_parent IN (
				SELECT child.ID
				FROM {$wp}posts as child
				INNER JOIN {$wp}postmeta as childmeta on child.ID = childmeta.post_id
				WHERE ( childmeta.meta_key LIKE '_autoship_schedule_process_enabled' OR childmeta.meta_key LIKE '_autoship_schedule_order_enabled' OR childmeta.meta_key LIKE '_autoship_schedule_options_enabled' ) AND childmeta.meta_value = 'yes' )";

		}

		$query .= 'GROUP BY parent.ID ORDER BY ID ASC';

		return array_map( 'intval', $wpdb->get_col( $query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
