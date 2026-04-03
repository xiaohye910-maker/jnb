<?php
/**
 * Scheduled Order Schedule Summary Template
 *
 * This template can be overridden by copying it to yourtheme/autoship-cloud/templates/scheduled-orders/scheduled-order-schedule-summary-template.php
 *
 * @package Autoship
 * @subpackage Templates
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'autoship_include_scheduled_order_upsell_products', true, $autoship_order, $customer_id, $autoship_customer_id, $args, $upsell_ids, $disable_carousel_js ) ) {
	return;
}

wc_set_loop_prop( 'name', 'up-sells' );
wc_set_loop_prop( 'columns', apply_filters( 'autoship_upsells_columns', $args['columns'] ) );

$orderby             = apply_filters( 'autoship_upsells_orderby', $args['orderby'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$order               = apply_filters( 'autoship_upsells_order', $args['order'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$columns             = apply_filters( 'autoship_upsells_columns', $args['columns'] );
$disable_carousel_js = apply_filters( 'autoship_scheduled_order_upsell_disable_carousel_js', $disable_carousel_js );

/**
 * Filter the number of upsell products should on the product page.
 *
 * @param int $limit number of upsell products.
 */
$limit = intval( apply_filters( 'autoship_upsells_total', $args['posts_per_page'] ) );

// Get visible upsells then sort them at random, then limit result set.
$args = array(
	'include'                          => $upsell_ids,
	'_autoship_schedule_order_enabled' => 'yes',
	'meta_key'                         => '_wc_average_rating', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	'orderby'                          => 'meta_value_num',
);

$products = wc_get_products( $args );
$products = array_filter( $products, 'autoship_products_array_filter_is_in_stock' );
$upsells  = array_filter( $products, 'wc_products_array_filter_visible' );
$upsells  = wc_products_array_orderby( $upsells, $orderby, $order );
$upsells  = $limit > 0 ? array_slice( $upsells, 0, $limit ) : $upsells;
?>
	
<div class="autoship-scheduled-order-upsell <?php echo 'yes' === $disable_carousel_js ? '' : 'flexslider'; ?>">

	<h5>
		<?php
		// translators: %s is the Scheduled Order text.
		echo apply_filters( 'autoship_product_upsells_products_heading', sprintf( __( 'Goes Well With Your %s', 'autoship' ), autoship_translate_text( 'Scheduled Order' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</h5>
	<?php if ( $upsells ) : ?>
		<ul class="up-sells upsells products">
			<?php foreach ( $upsells as $upsell ) : ?>
				<li>
					<?php

					$post_object = get_post( $upsell->get_id() );

					setup_postdata( $GLOBALS['post'] = &$post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

					$display_price_target_type = $upsell->get_type();

					$prices = autoship_get_product_prices( $upsell->get_id() );
					$pid    = $upsell->get_id();
					$args   = array(
						'item'     => $pid,
						'freqtype' => $autoship_order['frequencyType'],
						'order'    => $autoship_order['id'],
						'freq'     => $autoship_order['frequency'],
						'qty'      => 1,
						'redirect' => autoship_get_scheduled_order_url( $autoship_order['id'] ),
					);

					$args_one_time        = $args;
					$args_one_time['min'] = '0';
					$args_one_time['max'] = '1';

					?>
					<div class="autoship_upsell_product_wrap">
					<div class="autoship_upsell_product_wrap-left">
						<a href="<?php echo esc_url( get_the_permalink() ); ?>">
						<?php
							echo woocommerce_get_product_thumbnail(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						</a>
					</div>
					<div class="autoship_upsell_product_wrap-right">
						<p class="product_title entry-title">
						<a href="<?php echo esc_url( get_the_permalink() ); ?>">
							<?php
								echo wp_html_excerpt( get_the_title(), 20, '...' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</a>
						</p>
						<?php if ( post_type_supports( 'product', 'comments' ) ) : ?>
							<?php
							wc_get_template( 'single-product/rating.php' );
							?>
						<?php endif; ?>

						<?php
						// Run the display price through the WC Sale Price format and add the suffix if needed.
						$display_price = autoship_get_formatted_price(
							$prices['checkout_display_price'],
							array(
								'original' => $prices['checkout_price_is_autoship'] ? $prices['price'] : null,
								'suffix'   => $upsell->get_price_suffix(),
							)
						);

						// Get the Price HTML to show in place of the current price.
						$display_price = apply_filters( "autoship_{$display_price_target_type}_product_discount_price_html", $display_price, $prices['checkout_display_price'], $upsell );
						?>
						
						<div>
							<?php
								echo $display_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					</div>
					</div>
					<div class="autoship_upsell_product_wrap-bottom">
						<a href="<?php echo esc_url( autoship_get_scheduled_order_item_add_url( $args ) ); ?>" class="button add_to_cart_button autoship_add_btn"><?php esc_html( __( 'Add', 'autoship' ) ); ?></a>

						<a href="<?php echo esc_url( autoship_get_scheduled_order_item_add_url( $args_one_time ) ); ?>" class="autoship_add_one_time_btn"><?php echo esc_html( __( 'Add One-Time', 'autoship' ) ); ?></a>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php wp_reset_postdata(); ?>

	<?php if ( 'yes' !== $disable_carousel_js ) : ?>
		<div class="autoship-flex-direction-nav">
			<a class="flex-prev" href="#">
				<svg class="custom-arrow" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
					<circle cx="50" cy="50" r="48" stroke="#8A8A8A" stroke-width="2" fill="none" />
					<path d="M60,30 L40,50 L60,70" stroke="#8A8A8A" stroke-width="5" fill="none" stroke-linecap="butt" stroke-linejoin="miter" />
				</svg>
			</a>
			<a class="flex-next" href="#">
				<svg class="custom-arrow" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
					<circle cx="50" cy="50" r="48" stroke="#8A8A8A" stroke-width="2" fill="none" />
					<path d="M40,30 L60,50 L40,70" stroke="#8A8A8A" stroke-width="5" fill="none" stroke-linecap="butt" stroke-linejoin="miter" />
				</svg>
			</a>
		</div>
	<?php endif; ?>
</div>
