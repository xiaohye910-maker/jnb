<?php
/**
 * The functional code that handles the bulk utilities.
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Core\FeatureManager;
use Autoship\Core\Plugin;
use Autoship\Modules\Synchronizers\Products\ProductSynchronizer;

/**
 * Updates the Checkout Price for Products.
 *
 * @return void
 */
function autoship_batch_update_products() {
	// Default Batch Actions.
	$actions = apply_filters(
		'autoship_batch_update_products_actions',
		array(
			'autoship_bulk_update_checkout_price'      => 'autoship_batch_update_product_checkout',
			'autoship_bulk_update_recurring_price'     => 'autoship_batch_update_product_recurring',
			'autoship_bulk_update_enable_autoship'     => 'autoship_batch_update_product_enable_autoship',
			'autoship_bulk_update_enable_availability' => 'autoship_batch_update_product_enable_availability',
			'autoship_bulk_update_active_sync'         => 'autoship_batch_update_product_active_sync',
			'autoship_bulk_update_reset_active_sync'   => 'autoship_batch_reset_product_active_sync',
			'autoship_bulk_update_customer_metrics'    => 'autoship_batch_update_customer_metrics',
		)
	);

	// Retrieve the posted action.
	$args                 = array();
	$args['batch_action'] = ! isset( $_POST['batch_action'] ) || empty( $_POST['batch_action'] ) ? '' : $_POST['batch_action']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	// Validate the Action.
	if ( ! isset( $actions[ $args['batch_action'] ] ) || ! function_exists( $actions[ $args['batch_action'] ] ) ) {
		autoship_ajax_result(
			400,
			array(
				'success' => false,
				'notice'  => __( 'Invalid autoship batch action.', 'autoship' ),
			)
		);
	}

	// Retrieve the next set of options.
	$args['batch_function'] = $actions[ $args['batch_action'] ];

	$args['batch_size'] = ! isset( $_POST['batch_size'] ) || empty( $_POST['batch_size'] ) ? 10 : absint( $_POST['batch_size'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$args['current_count'] = ! isset( $_POST['current_count'] ) || empty( $_POST['current_count'] ) ? 0 : absint( $_POST['current_count'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$args['current_page'] = ! isset( $_POST['current_page'] ) || empty( $_POST['current_page'] ) ? 0 : absint( $_POST['current_page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$args['total_count'] = ! isset( $_POST['total_count'] ) || empty( $_POST['total_count'] ) ? 0 : absint( $_POST['total_count'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	// Allow devs to extend the gathered options.
	$args = apply_filters( "autoship_batch_update_products_{$args['batch_action']}_args", $args );

	// Run the function.
	$function = $args['batch_function'];
	$results  = $function( $args );

	do_action( $args['batch_action'] . '_complete', $results, $args );

	autoship_ajax_result( 200, $results );
	die();
}

add_action( 'wp_ajax_autoship_batch_update_products', 'autoship_batch_update_products' );

/**
 * Gathers the Additional Posted Vals for the Batch Update Active
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_active_sync_added_args( $args ) {

	$args['include_availability'] = ! isset( $_POST['autoship_include_availability_on_sync'] ) || empty( $_POST['autoship_include_availability_on_sync'] ) || ( 'yes' !== $_POST['autoship_include_availability_on_sync'] ) ? false : true; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_active_sync_args', 'autoship_batch_update_product_active_sync_added_args', 10, 1 );

/**
 * Gathers the Additional Posted Vals for the Batch Update Checkout
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_checkout_added_args( $args ) {
	$args['checkout_pct'] = ! isset( $_POST['checkout_pct'] ) || empty( $_POST['checkout_pct'] ) ? '' : round( floatval( $_POST['checkout_pct'] ), 2 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$args['base_price'] = ! isset( $_POST['base_price'] ) || empty( $_POST['base_price'] ) ? 'regular' : $_POST['base_price']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_checkout_price_args', 'autoship_batch_update_product_checkout_added_args', 10, 1 );

/**
 * Gathers the Additional Posted Vals for the Batch Update Recurring
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_recurring_added_args( $args ) {
	$args['recurring_pct'] = ! isset( $_POST['recurring_pct'] ) || empty( $_POST['recurring_pct'] ) ? '' : round( floatval( $_POST['recurring_pct'] ), 2 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$args['base_price'] = ! isset( $_POST['base_recurring_price'] ) || empty( $_POST['base_recurring_price'] ) ? 'regular' : $_POST['base_recurring_price']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_recurring_price_args', 'autoship_batch_update_product_recurring_added_args', 10, 1 );

/**
 * Sets the Enable Option for the autoship_bulk_update_enable_autoship action
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_enable_autoship_added_args( $args ) {

	$args['enable_option'] = ! isset( $_POST['enable_autoship_option'] ) || empty( $_POST['enable_autoship_option'] ) || 'no' !== $_POST['enable_autoship_option'] ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_enable_autoship_args', 'autoship_batch_update_enable_autoship_added_args', 10, 1 );

/**
 * Sets the Enable Option for the autoship_bulk_update_enable_availability action
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_enable_availability_added_args( $args ) {

	$args['enable_option'] = ! isset( $_POST['enable_availability_option'] ) || empty( $_POST['enable_availability_option'] ) || 'no' !== $_POST['enable_availability_option'] ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_enable_availability_args', 'autoship_batch_update_enable_availability_added_args', 10, 1 );

/**
 * Sets the Enable Option for the autoship_bulk_update_active_sync action
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_active_sync_added_args( $args ) {

	$args['enable_option'] = ! isset( $_POST['enable_active_sync_option'] ) || empty( $_POST['enable_active_sync_option'] ) || 'no' !== $_POST['enable_active_sync_option'] ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_active_sync_args', 'autoship_batch_update_active_sync_added_args', 10, 1 );

/**
 * Sets the Enable Option for the autoship_bulk_update_reset_active_sync action
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_active_reset_sync_added_args( $args ) {

	$args['enable_option'] = ! isset( $_POST['enable_reset_active_sync_option'] ) || empty( $_POST['enable_reset_active_sync_option'] ) || 'no' !== $_POST['enable_reset_active_sync_option'] ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $args;
}

add_filter( 'autoship_batch_update_products_autoship_bulk_update_reset_active_sync_args', 'autoship_batch_update_active_reset_sync_added_args', 10, 1 );

/**
 * Retrieves Product IDs where the Enable Schedule Options is checked or
 * _autoship_schedule_process_enabled is enabled or _autoship_schedule_order_enabled is
 * enabled
 *
 * @param string $type The post types to retrieve.
 *
 * @return array
 */
function autoship_batch_query_maybe_active_product_ids( $type = 'all' ) {

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

	$products = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	return $products;
}

/**
 * Retrieves Product IDs where the Active flag is checked.
 *
 * @param string $type The post types to retrieve.
 *
 * @return array of IDs
 */
function autoship_batch_query_active_product_ids( $type = 'all' ) {

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

	$products = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	return $products;
}

/**
 * Retrieves Product IDs
 *
 * @param string $type The post types to retrieve.
 *
 * @return array of IDs
 */
function autoship_batch_query_product_ids( $type = 'all' ) {

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

	$products = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	return $products;
}

/**
 * Batch Updates the Autoship Checkout Price
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_checkout( $args ) {

	$args = wp_parse_args(
		$args,
		array(
			'checkout_pct'  => 0,
			'current_count' => 0,
			'total_count'   => 0,
			'current_page'  => 1,
			'base_price'    => 'regular',
		)
	);

	// Retrieve the IDs
	// We only update simple and variations.
	// Depending on Global get active or all are active.
	$query_function = autoship_global_sync_active_enabled() ? 'autoship_batch_query_product_ids' : 'autoship_batch_query_active_product_ids';

	$query_ids = array_merge( $query_function( 'simple' ), $query_function( 'variation' ) );

	// Don't paginate if full run is expected
	// Otherwise get pages.
	$page = array();
	if ( $args['current_page'] > 0 ) {

		$pages = array_chunk( $query_ids, $args['batch_size'] );
		$page  = isset( $pages[ $args['current_page'] - 1 ] ) ? $pages[ $args['current_page'] - 1 ] : array();

	}

	$last  = 0;
	$count = 0;
	foreach ( $page as $product_id ) {

		// Grab the Product.
		$product = wc_get_product( $product_id );

		// Add Check for missing or invalid product.
		if ( ! $product || empty( $product ) ) {
			continue;
		}

		// Grab the current checkout price.
		$args['current_checkout_price'] = autoship_get_product_checkout_price( $product_id );

		if ( '' !== $args['checkout_pct'] ) {

			// Grab the base price so we can calculate the checkout.
			if ( 'regular' === $args['base_price'] ) {
				$args['base_price_val'] = floatval( $product->get_regular_price() );
			} elseif ( 'sale' === $args['base_price'] ) {
				$args['base_price_val'] = floatval( $product->get_price() );
			} else {
				$args['base_price_val'] = apply_filters( "autoship_batch_update_product_checkout_base_{$args['base_price']}price", floatval( $product->get_regular_price() ), $args, $product );
			}

			$checkout_price = apply_filters( 'autoship_batch_update_product_checkout_price_calculation', round( floatval( $args['base_price_val'] - ( $args['base_price_val'] * $args['checkout_pct'] ) ), 2 ), $args, $product );

		} else {

			// Clear the Checkout Price.
			$checkout_price = $args['checkout_pct'];

		}

		// Set the checkout price.
		$updated = autoship_set_product_checkout_price( $product->get_id(), $checkout_price );

		if ( false === $updated ) {
			autoship_log_entry(
				__( 'Autoship Bulk Update Utility', 'autoship' ),
				'' === $args['checkout_pct'] ?
					// translators: %1$d is the product ID, %2$f is the current checkout price.
					sprintf( __( 'Batch Clear Checkout Price for Product %1$d from %2$f to nothing Failed or was Not Needed.', 'autoship' ), $product->get_id(), $args['current_checkout_price'] ) :
					// translators: %1$d is the product ID, %2$f is the current checkout price, %3$f is the new checkout price.
					sprintf( __( 'Batch Update Checkout Price for Product %1$d from %2$f to %3$f Failed or was Not Needed.', 'autoship' ), $product->get_id(), $args['current_checkout_price'], $checkout_price )
			);
		}

		// Adjust the counters.
		if ( $updated ) {
			$ids[ $product->get_id() ] = $product->get_id();
		}

		$last = $product->get_id();
		++$count;
	}

	// Finally calculate the percentage completed and update for next round.
	$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;
	$pct = ! empty( $page ) ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the products.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		// translators: %1$s is the percentage, %2$d is the total count of products.
		'notice'         => sprintf( __( '%1$s%% of the %2$d Products and Product Variations have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] ),
	);
}

/**
 * Batch Updates the Autoship Recurring Price
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_recurring( $args ) {

	$args = wp_parse_args(
		$args,
		array(
			'recurring_pct' => 0,
			'current_count' => 0,
			'total_count'   => 0,
			'current_page'  => 1,
			'base_price'    => 'regular',
		)
	);

	// Retrieve the IDs
	// We only update simple and variations.
	// Depending on Global get active or all are active.
	$query_function = autoship_global_sync_active_enabled() ? 'autoship_batch_query_product_ids' : 'autoship_batch_query_active_product_ids';

	$query_ids = array_merge( $query_function( 'simple' ), $query_function( 'variation' ) );

	// Don't paginate if full run is expected
	// Otherwise get pages.
	$page = array();
	if ( $args['current_page'] > 0 ) {

		$pages = array_chunk( $query_ids, $args['batch_size'] );
		$page  = isset( $pages[ $args['current_page'] - 1 ] ) ? $pages[ $args['current_page'] - 1 ] : array();

	}

	$ids   = array();
	$last  = 0;
	$count = 0;
	foreach ( $page as $product_id ) {

		// Get the Product.
		$product = wc_get_product( $product_id );

		// Add Check for missing or invalid product.
		if ( ! $product || empty( $product ) ) {
			continue;
		}

		// Grab the current checkout & recurring prices.
		$args['current_checkout_price']  = autoship_get_product_checkout_price( $product_id );
		$args['current_recurring_price'] = autoship_get_product_recurring_price( $product_id );

		if ( '' !== $args['recurring_pct'] ) {

			// Grab the base price so we can calculate the recurring.
			if ( 'regular' === $args['base_price'] ) {
				$args['base_price_val'] = floatval( $product->get_regular_price() );
			} elseif ( 'sale' === $args['base_price'] ) {
				$args['base_price_val'] = floatval( $product->get_price() );
			} elseif ( 'checkout' === $args['base_price'] ) {
				$args['base_price_val'] = $args['current_checkout_price'];
			} else {
				$args['base_price_val'] = apply_filters( "autoship_batch_update_product_recurring_base_{$args['base_price']}price", floatval( $product->get_regular_price() ), $args, $product );
			}

			// Calculate the recurring price.
			$checkout_price  = apply_filters( 'autoship_batch_update_product_recurring_price_calculation', round( floatval( $args['base_price_val'] - ( $args['base_price_val'] * $args['recurring_pct'] ) ), 2 ), $args, $product );
			$recurring_price = $checkout_price;

		} else {

			// Clear the recurring Price.
			$recurring_price = $args['recurring_pct'];
		}

		// Update the Recurring price.
		$updated = autoship_set_product_recurring_price( $product->get_id(), $recurring_price );

		if ( false === $updated ) {
			autoship_log_entry(
				__( 'Autoship Bulk Update Utility', 'autoship' ),
				'' === $args['recurring_pct'] ?
					// translators: %1$d is the product ID, %2$f is the current recurring price.
					sprintf( __( 'Batch Clear Recurring Price for Product %1$d from %2$f to nothing Failed or was Not Needed.', 'autoship' ), $product->get_id(), $args['current_recurring_price'] ) :
					// translators: %1$d is the product ID, %2$f is the current recurring price, %3$f is the new recurring price.
					sprintf( __( 'Batch Update Recurring Price for Product %1$d from %2$f to %3$f Failed or was Not Needed.', 'autoship' ), $product->get_id(), $args['current_recurring_price'], $recurring_price )
			);
		}

		// Do not Upsert any orphaned Variations - i.e. Variations with no parent ids.
		$valid_product = 'variation' === $product->get_type() ? $product->get_parent_id() : $product->get_id();

		// Upsert the Simple and Variations to QPilot.
		if ( apply_filters( 'autoship_upsert_on_batch_update_products', true && ! empty( $valid_product ), 'autoship_bulk_update_recurring_price', $product ) ) {
			autoship_push_product( $product->get_id() );
		}

		// Adjust all counters.

		if ( $updated ) {
			$ids[ $product->get_id() ] = $product->get_id();
		}

		$last = $product->get_id();
		++$count;
	}

	// Finally calculate the percentage completed and update for next round.
	$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;
	$pct = ! empty( $page ) ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the products.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		// translators: %1$s is the percentage, %2$d is the total number of products.
		'notice'         => sprintf( __( '%1$s%% of the %2$d Products and Product Variations have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] ),
	);
}

/**
 * Batch Updates the Enable Autoship option
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_enable_autoship( $args ) {

	$args = wp_parse_args(
		$args,
		array(
			'current_page'  => - 1,
			'current_count' => 0,
			'enable_option' => 'yes',
			'batch_size'    => 10,
			'total_count'   => 0,
		)
	);

	// Retrieve the IDs
	// If we're enabling the option we need Simple, Variable, and Variations
	// Else just Simple and Variable.
	$query_ids = array();

	if ( 'yes' === $args['enable_option'] ) {

		$query_ids = array_merge( autoship_batch_query_product_ids( 'simple' ), autoship_batch_query_product_ids( 'variation' ), autoship_batch_query_product_ids( 'variable' ) );

	} else {

		$query_ids = array_merge( autoship_batch_query_product_ids( 'simple' ), autoship_batch_query_product_ids( 'variable' ) );

	}

	// As long as we haven't processed the full set.
	if ( $args['current_count'] < $args['total_count'] ) {

		// Don't paginate if full run is expected
		// Otherwise get pages.
		$page = array();
		if ( $args['current_page'] > 0 ) {

			$pages = array_chunk( $query_ids, $args['batch_size'] );
			$page  = isset( $pages[ $args['current_page'] - 1 ] ) ? $pages[ $args['current_page'] - 1 ] : array();

		}

		$ids   = array();
		$last  = 0;
		$count = 0;
		foreach ( $page as $product_id ) {

			// Get the Product & type.
			$product = wc_get_product( $product_id );

			// Add Check for missing or invalid product.
			if ( ! $product || empty( $product ) ) {
				continue;
			}

			$type = $product->get_type();

			$updated = true;

			// Only update the parent level for variations and simple products.
			$id = 'variation' === $type ? $product->get_parent_id() : $product->get_id();

			$updated = autoship_set_product_autoship_enabled( $id, $args['enable_option'] );

			// If we're enabling the option we update Simple, Variable and we uncheck the disable option for variations.
			if ( ( 'yes' === $args['enable_option'] ) && ( 'variation' === $type ) ) {
				$updated = autoship_set_product_variation_autoship_disabled( $product->get_id() );
			}

			if ( false === $updated ) {
				// translators: %d is the product ID.
				autoship_log_entry( __( 'Autoship Bulk Update Utility', 'autoship' ), sprintf( __( 'Batch Update Enable Autoship for Product %d Failed or was Not Needed.', 'autoship' ), $product->get_id() ) );
			}

			// Adjust all counters.
			if ( $updated ) {
				$ids[ $product->get_id() ] = $product->get_id();
			}

			$last = $product->get_id();
			++$count;
		}

		$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;
	} else {

		$pct = 100;
	}

	// Finally calculate the percentage completed and update for next round.
	$pct = ! empty( $page ) ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the orders.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		'notice'         => 'yes' === $args['enable_option'] ?
			sprintf(
				// translators: %1$s is the percentage, %2$d is the total number of products.
				__( '%1$s%% of the %2$d Simple Products, Variable Products, and Product Variations have been processed.', 'autoship' ),
				$pct < 5 ? 5 : $pct,
				$args['total_count']
			) :
			sprintf(
				// translators: %1$s is the percentage, %2$d is the total number of products.
				__( '%1$s%% of the %2$d Simple Products and Variable Products have been processed.', 'autoship' ),
				$pct < 5 ? 5 : $pct,
				$args['total_count']
			),
	);
}

/**
 * Batch Updates the Active Autoship Sync option
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_active_sync( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'current_page'         => - 1,
			'current_count'        => 0,
			'enable_option'        => 'yes',
			'batch_size'           => 10,
			'total_count'          => 0,
			'include_availability' => true,
		)
	);

	// Retrieve the IDs.
	$query_ids = array_merge( autoship_batch_query_product_ids( 'simple' ), autoship_batch_query_product_ids( 'variation' ), autoship_batch_query_product_ids( 'variable' ) );

	$count = 0;
	// As long as we haven't processed the full set.
	if ( $args['current_count'] < $args['total_count'] ) {
		// Don't paginate if full run is expected
		// Otherwise get pages.
		$page = array();
		if ( $args['current_page'] > 0 ) {

			$pages = array_chunk( $query_ids, $args['batch_size'] );
			$page  = isset( $pages[ $args['current_page'] - 1 ] ) ? $pages[ $args['current_page'] - 1 ] : array();
		}

		$overrides = ( 'yes' === $args['enable_option'] ) && $args['include_availability'] ? array(
			'addToScheduledOrder'   => true,
			'processScheduledOrder' => true,
		) : array();

		$count = 0;
		foreach ( $page as $product_id ) {

			// Get the Product & type.
			$product = wc_get_product( $product_id );

			// Add Check for missing or invalid product.
			if ( ! $product || empty( $product ) ) {
				continue;
			}

			$type = $product->get_type();

			$updated = true;

			// Enable or Disable the option based on submit.
			$updated = autoship_set_product_sync_active_enabled( $product->get_id(), $args['enable_option'] );

			// Update the availability flags if needed.
			if ( ( 'yes' === $args['enable_option'] ) && $args['include_availability'] && ( 'variable' !== $product->get_type() ) ) {

				$updated = autoship_set_product_add_to_scheduled_order( $product->get_id(), 'yes' );
				$updated = autoship_set_product_process_on_scheduled_order( $product->get_id(), 'yes' );

			}

			// Update the Simple, Variable or Variation in QPilot.
			autoship_push_product( $product->get_id(), $overrides );

			if ( false === $updated ) {
				// translators: %d is the product ID.
				autoship_log_entry( __( 'Autoship Bulk Update Utility', 'autoship' ), sprintf( __( 'Batch Update Active Sync for Product %d Failed or was Not Needed.', 'autoship' ), $product->get_id() ) );
			}

			// Adjust all counters.
			if ( $updated ) {
				$ids[ $product->get_id() ] = $product->get_id();
			}

			$last = $product->get_id();
			++$count;
		}

		$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;

	} else {
		$pct = 100;

	}

	// Finally calculate the percentage completed and update for next round.
	$pct = ! empty( $page ) ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the Products.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		// translators: %1$s is the percentage, %2$d is the total number of products.
		'notice'         => 'yes' === $args['enable_option'] ? sprintf( __( '%1$s%% of the %2$d Simple Products, Variable Products, and Product Variations have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] ) : sprintf( __( '%1$s%% of the %2$d Simple Products and Variable Products have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] ),
	);
}

/**
 * Batch Updates the Autoship Add To Scheduled Order and Process on Scheduled Orders Options
 * NOTE: Due to the way these flags are stored both are either enabled or disabled via the api.
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_product_enable_availability( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'current_page'  => - 1,
			'current_count' => 0,
			'enable_option' => 'yes',
			'batch_size'    => 10,
			'total_count'   => 0,
		)
	);

	// Retrieve the IDs
	// Depending on Global get active or all are active.
	$query_function = autoship_global_sync_active_enabled() ? 'autoship_batch_query_product_ids' : 'autoship_batch_query_active_product_ids';

	$query_ids = array_merge( $query_function( 'simple' ), $query_function( 'variation' ) );

	$page  = array();
	$last  = 0;
	$count = 0;
	// As long as we haven't processed the full set.
	if ( $args['current_count'] < $args['total_count'] ) {

		// Don't paginate if full run is expected
		// Otherwise get pages.
		if ( $args['current_page'] > 0 ) {

			$pages = array_chunk( $query_ids, $args['batch_size'] );
			$page  = isset( $pages[ $args['current_page'] - 1 ] ) ? $pages[ $args['current_page'] - 1 ] : array();
		}

		$ids = array();
		foreach ( $page as $product_id ) {

			$updated = true;

			// Get the Product & type.
			$product = wc_get_product( $product_id );

			// Add Check for missing or invalid product.
			if ( ! $product || empty( $product ) ) {
				continue;
			}

			// Update the Options in WC.
			$updated = autoship_set_product_add_to_scheduled_order( $product_id, $args['enable_option'] );
			$updated = $updated && autoship_set_product_process_on_scheduled_order( $product_id, $args['enable_option'] );

			if ( false === $updated ) {
				// translators: %d is the product ID.
				autoship_log_entry( __( 'Autoship Bulk Update Utility', 'autoship' ), sprintf( __( 'Batch Update Availability for Product %d Failed or was Not Needed.', 'autoship' ), $product_id ) );
			}

			// Update the Simple or Variation in QPilot.
			autoship_update_product_availability( $product_id, 'yes' === $args['enable_option'] ? 'AddToScheduledOrder,ProcessScheduledOrder' : 'none' );

			// Adjust all counters.
			if ( $updated ) {
				$ids[ $product_id ] = $product_id;
			}

			$last = $product_id;
			++$count;
		}

		$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;
	} else {
		$pct = 100;
	}

	// Finally calculate the percentage completed and update for next round.
	$pct = ! empty( $page ) ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the orders.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => $last,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		// translators: %1$s is the percentage, %2$d is the total number of products.
		'notice'         => sprintf( __( '%1$s%% of the %2$d Simple Products and Product Variations have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] ),
	);
}

/**
 * Batch Resets the Active Autoship Sync option
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_reset_product_active_sync( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'current_page'  => - 1,
			'current_count' => 0,
			'enable_option' => 'yes',
			'batch_size'    => 10,
			'total_count'   => 0,
		)
	);

	// Check if the global option is set and if so initialize fresh.
	if ( autoship_global_sync_active_enabled() ) {
		// Call the client to flip the flag.
		autoship_reset_all_products_activate( 'no' );

		// Flip the global flag.
		autoship_set_global_sync_active_enabled( 'no' );
	}

	if ( 'no' === $args['enable_option'] ) {

		// Retrieve the IDs.
		$query_ids = array_merge( autoship_batch_query_maybe_active_product_ids( 'simple' ), autoship_batch_query_maybe_active_product_ids( 'variation' ), autoship_batch_query_maybe_active_product_ids( 'variable' ) );

		$activate_val = 'yes';
	} else {

		// Retrieve the IDs.
		$query_ids    = array_merge( autoship_batch_query_product_ids( 'simple' ), autoship_batch_query_product_ids( 'variation' ), autoship_batch_query_product_ids( 'variable' ) );
		$activate_val = 'no';
	}

	// As long as we haven't processed the full set.
	if ( $args['current_count'] < $args['total_count'] ) {
		// Don't paginate if full run is expected
		// Otherwise get pages.
		$page = array();
		if ( $args['current_page'] > 0 ) {

			$pages = array_chunk( $query_ids, $args['batch_size'] );
			$page  = isset( $pages[ $args['current_page'] - 1 ] ) ? $pages[ $args['current_page'] - 1 ] : array();
		}

		$ids   = array();
		$count = 0;
		foreach ( $page as $product_id ) {

			// Get the Product & type.
			$product = wc_get_product( $product_id );

			// Add Check for missing or invalid product.
			if ( ! $product || empty( $product ) ) {
				continue;
			}

			$type = $product->get_type();

			$updated = true;

			// Enable or Disable the option based on submit.
			$updated = autoship_set_product_sync_active_enabled( $product->get_id(), $activate_val );

			// Update the Simple, Variable or Variation in QPilot.
			autoship_push_product( $product->get_id() );

			if ( false === $updated ) {
				// translators: %d is the product ID.
				autoship_log_entry( __( 'Autoship Bulk Update Utility', 'autoship' ), sprintf( __( 'Batch Update Active Sync for Product %d Failed or was Not Needed.', 'autoship' ), $product->get_id() ) );
			}

			// Adjust all counters.
			if ( $updated ) {
				$ids[ $product->get_id() ] = $product->get_id();
			}

			$last = $product->get_id();
			++$count;
		}

		$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;

	} else {
		$pct = 100;
	}

	// Finally calculate the percentage completed and update for next round.
	$pct = ! empty( $page ) ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the Products.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		'notice'         => 'yes' === $args['enable_option'] ?
			sprintf(
				// translators: %1$s is the percentage, %2$d is the total number of products.
				__( '%1$s%% of the %2$d Simple Products, Variable Products, and Product Variations have been processed.', 'autoship' ),
				$pct < 5 ? 5 : $pct,
				$args['total_count']
			) :
			sprintf(
				// translators: %1$s is the percentage, %2$d is the total number of products.
				__( '%1$s%% of the %2$d Simple Products and Variable Products have been processed.', 'autoship' ),
				$pct < 5 ? 5 : $pct,
				$args['total_count']
			),
	);
}

/**
 * Batch Updates the Customer Metrics Data
 *
 * @param array $args The arguments to be passed to the batch update.
 */
function autoship_batch_update_customer_metrics( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'current_page'  => - 1,
			'current_count' => 0,
			'batch_size'    => 10,
			'total_count'   => 0,
		)
	);

	// As long as we haven't processed the full set.
	if ( $args['current_count'] < $args['total_count'] ) {
		$results = autoship_available_customer_metrics(
			array(
				'page'     => $args['current_page'],
				'pageSize' => $args['batch_size'],
			)
		);

		if ( empty( $results->data ) ) {

			$pct   = 100;
			$count = $args['total_count'];

		} else {

			foreach ( $results->data as $metrics_data ) {
				autoship_save_customer_metrics_data( $metrics_data->customerId, $metrics_data, true ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}

			$count = count( $results->data );
			$pct   = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;
		}
	} else {
		$pct = 100;
	}

	// Finally calculate the percentage completed and update for next round.
	$pct = $count ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the Customers.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		// translators: %1$s is the percentage, %2$d is the total number of customers.
		'notice'         => sprintf( __( '%1$s%% of the %2$d Customers have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] ),
	);
}


/**
 * Retrieves a batch of enabled Autoship products.
 *
 * @param int $page       The page number to retrieve.
 * @param int $batch_size The number of products to retrieve per batch.
 *
 * @return array An array of WC_Product objects for the specified page and batch size.
 */
function autoship_products_get_enabled( $page, $batch_size ) {
	$page       = absint( $page );
	$batch_size = absint( $batch_size );

	if ( $page < 1 || $batch_size < 1 ) {
		return array();
	}

	$identifiers = array_merge( autoship_batch_query_product_ids( 'simple' ), autoship_batch_query_product_ids( 'variation' ), autoship_batch_query_product_ids( 'variable' ) );

	if ( ! is_array( $identifiers ) ) {
		return array();
	}

	sort( $identifiers );

	$chunks = array_chunk( $identifiers, $batch_size );
	$chunk  = isset( $chunks[ $page - 1 ] ) ? $chunks[ $page - 1 ] : array();

	return array_filter( array_map( 'wc_get_product', array_map( 'absint', $chunk ) ) );
}

/**
 * Updates the frequency options for a product.
 *
 * @param WC_Product $product     The product to update.
 * @param array      $frequencies The frequency options to set.
 *
 * @return bool True if the update was successful, false otherwise.
 */
function autoship_products_update_frequency_options( $product, $frequencies ) {
	if ( ! is_a( $product, 'WC_Product' ) || ! $product->get_id() ) {
		return false;
	}

	$product_id    = $product->get_id();
	$overriden     = autoship_override_frequency_options_enabled( $product );
	$updateable    = get_post_meta( $product_id, '_autoship_allow_frequency_options_bulk_update', true );
	$updateable    = ( 'yes' === $updateable ) ? 'yes' : 'no';
	$options_count = defined( 'Autoship_Options_Count' ) ? (int) Autoship_Options_Count : 5;

	if ( 'yes' === $overriden && 'no' === $updateable ) {
		return true;
	}

	update_post_meta( $product_id, '_autoship_override_frequency_options', 'yes' );
	update_post_meta( $product_id, '_autoship_allow_frequency_options_bulk_update', 'yes' );

	$current_meta = get_post_meta( $product_id );

	for ( $i = 0; $i < $options_count; ++$i ) {
		$type   = isset( $frequencies[ $i ]['frequency_type'] ) ? sanitize_text_field( $frequencies[ $i ]['frequency_type'] ) : '';
		$name   = isset( $frequencies[ $i ]['display_name'] ) ? sanitize_text_field( $frequencies[ $i ]['display_name'] ) : '';
		$number = isset( $frequencies[ $i ]['frequency_number'] ) ? sanitize_text_field( $frequencies[ $i ]['frequency_number'] ) : '';

		if ( ! isset( $current_meta[ "_autoship_frequency_type_{$i}" ][0] ) || $current_meta[ "_autoship_frequency_type_{$i}" ][0] !== $type ) {
			update_post_meta( $product_id, "_autoship_frequency_type_{$i}", $type );
		}

		if ( ! isset( $current_meta[ "_autoship_frequency_{$i}" ][0] ) || $current_meta[ "_autoship_frequency_{$i}" ][0] !== $number ) {
			update_post_meta( $product_id, "_autoship_frequency_{$i}", $number );
		}

		if ( ! isset( $current_meta[ "_autoship_frequency_display_name_{$i}" ][0] ) || $current_meta[ "_autoship_frequency_display_name_{$i}" ][0] !== $name ) {
			update_post_meta( $product_id, "_autoship_frequency_display_name_{$i}", $name );
		}
	}

	return true;
}

/**
 * AJAX handler to start the bulk update of frequency options for products.
 *
 * This function processes the request to update frequency options in bulk.
 * It validates the input data, retrieves products in batches, and updates their frequency options.
 */
function autoship_bulk_update_frequency_options_process_start() {
	if ( ! current_user_can( 'manage_options' ) ) {
		autoship_ajax_send_json_error( __( 'Unauthorized access', 'autoship' ) );
	}

	$frequencies = isset( $_POST['frequencies'] ) ? $_POST['frequencies'] : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$input                 = autoship_ajax_get_bulk_update_frequency_options_request_data();
	$validated_frequencies = autoship_ajax_validate_bulk_update_frequency_options_frequencies( $frequencies );

	if ( is_wp_error( $validated_frequencies ) ) {
		autoship_ajax_send_json_error( $validated_frequencies->get_error_message() );
	}

	$batch_size    = $input['batch_size'];
	$total_count   = $input['total_count'];
	$current_count = $input['current_count'];
	$current_page  = $input['current_page'];

	$count = 0;
	$pct   = 100;

	if ( $current_count < $total_count ) {
		$products = autoship_products_get_enabled( $current_page, $batch_size );

		if ( empty( $products ) ) {
			$pct   = 100;
			$count = $total_count;
		} else {
			foreach ( $products as $product ) {
				autoship_products_update_frequency_options( $product, $validated_frequencies );
			}

			$count = count( $products );
			$pct   = $count && $total_count ? round( 100 * ( ( $count + $current_count ) / $total_count ), 2 ) : 0;
		}
	}

	$pct = $count ? $pct : 100;
	$pct = $pct >= 100 ? 100 : $pct;

	wp_send_json_success(
		array(
			'page'           => $current_page + 1,
			'last_record'    => 0,
			'updated_record' => array(),
			'count'          => $count,
			'current_count'  => 100 === $pct ? $total_count : $count + $current_count,
			'total_pct'      => $pct < 5 ? 5 : $pct,
			// translators: %1$s is the percentage completed, %2$d is the total number of products.
			'notice'         => sprintf( __( '%1$s%% of the %2$d Products have been processed.', 'autoship' ), $pct < 5 ? 5 : $pct, $total_count ),
		)
	);
	wp_die();
}

add_action( 'wp_ajax_autoship_bulk_update_frequency_options_process_start', 'autoship_bulk_update_frequency_options_process_start' );


/**
 * Retrieve and sanitize request data.
 */
function autoship_ajax_get_bulk_update_frequency_options_request_data() {

	$filter_options = array(
		'batch_size'    => FILTER_VALIDATE_INT,
		'total_count'   => FILTER_VALIDATE_INT,
		'current_count' => FILTER_VALIDATE_INT,
		'current_page'  => FILTER_VALIDATE_INT,
	);

	$input = filter_input_array( INPUT_POST, $filter_options );

	return array(
		'batch_size'    => isset( $input['batch_size'] ) ? $input['batch_size'] : 10,
		'total_count'   => isset( $input['total_count'] ) ? $input['total_count'] : 0,
		'current_count' => isset( $input['current_count'] ) ? $input['current_count'] : 0,
		'current_page'  => isset( $input['current_page'] ) ? $input['current_page'] : 1,
	);
}


/**
 * Validate and sanitize frequency options.
 *
 * @param array $frequencies The frequencies to validate.
 */
function autoship_ajax_validate_bulk_update_frequency_options_frequencies( $frequencies ) {
	if ( empty( $frequencies ) || ! is_array( $frequencies ) ) {
		return new WP_Error( 'invalid_data', __( 'Frequencies data is missing or invalid.', 'autoship' ) );
	}

	$allowed_types = array(
		'Days'          => array(
			'min' => 1,
			'max' => 365,
		),
		'Weeks'         => array(
			'min' => 1,
			'max' => 52,
		),
		'Months'        => array(
			'min' => 1,
			'max' => 12,
		),
		'DayOfTheWeek'  => array(
			'min' => 1,
			'max' => 7,
		),
		'DayOfTheMonth' => array(
			'min' => 1,
			'max' => 31,
		),
	);

	$validated_frequencies = array();

	foreach ( $frequencies as $index => $frequency ) {
		if ( ! isset( $frequency['id'], $frequency['frequency_type'], $frequency['frequency_number'], $frequency['display_name'] ) ) {
			// translators: %d is the frequency option index.
			return new WP_Error( 'missing_data', sprintf( __( 'Missing data in frequency option %d.', 'autoship' ), $index + 1 ) );
		}

		$frequency_id     = absint( $frequency['id'] );
		$frequency_type   = sanitize_text_field( $frequency['frequency_type'] );
		$frequency_number = absint( $frequency['frequency_number'] );
		$frequency_name   = sanitize_text_field( $frequency['display_name'] );

		if ( ! isset( $allowed_types[ $frequency_type ] ) ) {
			// translators: %d is the frequency option index.
			return new WP_Error( 'invalid_type', sprintf( __( 'Invalid data in frequency option %d.', 'autoship' ), $index + 1 ) );
		}

		$range = $allowed_types[ $frequency_type ];
		if ( $frequency_number < $range['min'] || $frequency_number > $range['max'] ) {
			// translators: %d is the frequency number.
			return new WP_Error( 'invalid_number', sprintf( __( 'Invalid frequency number in option %d.', 'autoship' ), $index + 1 ) );
		}

		$validated_frequencies[] = array(
			'id'               => $frequency_id,
			'frequency_type'   => $frequency_type,
			'frequency_number' => $frequency_number,
			'display_name'     => $frequency_name,
		);
	}

	return $validated_frequencies;
}

/**
 * Sends a JSON error response with a message.
 *
 * @param string $message The error message to send.
 */
function autoship_ajax_send_json_error( $message ) {
	wp_send_json_error(
		array(
			'total_pct'     => 0,
			'current_count' => 0,
			'notice'        => $message,
		)
	);
}

/**
 * Enables the Autoship product sync utility feature.
 *
 * This function checks if the product sync feature is enabled and then enables the sync.
 * It sends a JSON response indicating success or failure.
 */
function autoship_product_sync_enable() {
	if ( ! Autoship\Core\FeatureManager::is_enabled( 'product_sync' ) ) {
		wp_send_json_error( array( 'notice' => __( 'The autoship product sync utility feature is not enabled.', 'autoship' ) ) );
	}

	$container    = Plugin::get_service_container();
	$synchronizer = $container->get( ProductSynchronizer::class );
	$synchronizer->enable_sync();

	wp_send_json_success( array( 'notice' => __( 'The product sync has been enabled.', 'autoship' ) ) );
}

/**
 * Disables the Autoship product sync utility feature.
 *
 * This function checks if the product sync feature is enabled and then disables the sync.
 * It sends a JSON response indicating success or failure.
 */
function autoship_product_sync_disable() {
	if ( ! Autoship\Core\FeatureManager::is_enabled( 'product_sync' ) ) {
		wp_send_json_error( array( 'notice' => __( 'The autoship product sync utility feature is not enabled.', 'autoship' ) ) );
	}

	$container    = Plugin::get_service_container();
	$synchronizer = $container->get( ProductSynchronizer::class );
	$synchronizer->disable_sync();

	wp_send_json_success( array( 'notice' => __( 'The product sync has been disabled.', 'autoship' ) ) );
}

/**
 * Updates the Autoship product sync settings.
 *
 * This function checks if the product sync feature is enabled and updates the settings based on user input.
 * It sends a JSON response indicating success or failure.
 */
function autoship_product_sync_update() {
	if ( ! Autoship\Core\FeatureManager::is_enabled( 'product_sync' ) ) {
		wp_send_json_error( array( 'notice' => __( 'The autoship product sync utility feature is not enabled.', 'autoship' ) ) );
	}

	// Grab and sanitize raw input.
	$raw_batch_size = sanitize_text_field( wp_unslash( $_POST['batch'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$raw_interval   = sanitize_text_field( wp_unslash( $_POST['interval'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$raw_logging    = sanitize_text_field( wp_unslash( $_POST['logging'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// Validate as integers with a default fallback if out of range or invalid.
	$size = filter_var(
		$raw_batch_size,
		FILTER_VALIDATE_INT,
		array(
			'options' => array(
				'min_range' => 10,
				'max_range' => 500,
				'default'   => 10,
			),
		)
	);

	$interval = filter_var(
		$raw_interval,
		FILTER_VALIDATE_INT,
		array(
			'options' => array(
				'min_range' => 300,
				'max_range' => 86400,
				'default'   => 300,
			),
		)
	);

	$logging      = filter_var( $raw_logging, FILTER_VALIDATE_BOOLEAN );
	$container    = Plugin::get_service_container();
	$synchronizer = $container->get( ProductSynchronizer::class );

	$current_size     = $synchronizer->get_batch_size();
	$current_interval = $synchronizer->get_interval();
	$current_logging  = $synchronizer->is_logging_enabled();

	if ( $current_size === $size && $current_interval === $interval && $current_logging === $logging ) {
		wp_send_json_success( array( 'notice' => __( 'The settings were updated.', 'autoship' ) ) );
		wp_die();
	}

	// Update the batch size if needed. This operation does not require a cron job update.
	if ( $current_size !== $size ) {
		$synchronizer->set_batch_size( $size );
	}

	// Update the interval if needed.  This will also update the cron job.
	if ( $current_interval !== $interval ) {
		$synchronizer->set_interval( $interval );

		// Remove the current tasks from the action scheduler.
		$synchronizer->unschedule_task();

		// Create new tasks in the action scheduler with the new settings.
		$synchronizer->schedule_task();
	}

	if ( $current_logging !== $logging ) {
		if ( $logging ) {
			$synchronizer->enable_logging();
		} else {
			$synchronizer->disable_logging();
		}
	}

	wp_send_json_success( array( 'notice' => __( 'The settings were updated.', 'autoship' ) ) );
}

add_action( 'wp_ajax_autoship_product_sync_enable', 'autoship_product_sync_enable' );
add_action( 'wp_ajax_autoship_product_sync_disable', 'autoship_product_sync_disable' );
add_action( 'wp_ajax_autoship_product_sync_update', 'autoship_product_sync_update' );
