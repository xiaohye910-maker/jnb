<?php
/**
 * The product step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gets the top 10 products.
 *
 * @return array
 */
function get_top_10_sold_products(): array {
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => 10,
		'meta_key'       => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'total_sales',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		),
	);

	$query = new WP_Query( $args );
	return $query->have_posts() ? $query->posts : array();
}

$top_products = get_top_10_sold_products();
if ( ! empty( $top_products ) ) {
	foreach ( $top_products as $product_post ) {
		$products[] = wc_get_product( $product_post->ID );
	}
}

if ( empty( $products ) ) {
	$products = wc_get_products(
		array(
			'limit'   => 10,
			'orderby' => 'title',
			'order'   => 'DESC',
		)
	);
}


$has_products = count( $products ) > 0;
?>

<?php if ( ! $has_products ) : ?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-30">
		<span class="autoship-orange-text"><?php echo esc_html( __( 'Ups!', 'autoship' ) ); ?></span> <?php echo esc_html( __( "It seems you don't have products in your store", 'autoship' ) ); ?>
	</h1>

	<p class="autoship-font-md autoship-mb-50">
		<?php echo esc_html( __( 'To continue your setup, Autoship needs at least one product in your WooCommerce store catalog.', 'autoship' ) ); ?>
	</p>

	<img src="<?php echo esc_url( Autoship_Plugin_Url ); ?>/images/no-products-image.png"  style="width:80%" alt="Autoship"/>

	<p class="autoship-font-md autoship-mb-50"><strong><?php echo esc_html( __( 'Create a product and come back when ready.', 'autoship' ) ); ?></strong></p>

	<div class="autoship-quicklaunch-content-step-actions ">
		<div class="autoship-mb-20">
			<button type="button" id="product-creation-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Create Product', 'autoship' ) ); ?></button>
		</div>
	</div>

</div>

<?php else : ?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-30"><?php echo esc_html( __( "Let's set up", 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'your first', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'product', 'autoship' ) ); ?></h1>
	<p class="autoship-font-sm">
		<?php echo esc_html( __( 'This step will enable the', 'autoship' ) ); ?> <strong><?php echo esc_html( __( 'Autoship & Save', 'autoship' ) ); ?></strong> <?php echo esc_html( __( 'option on the selected product page, giving your customers the ability to order on their schedule.', 'autoship' ) ); ?>
	</p>
	<div class="autoship-quicklaunch-content-step-fields">

		<div class="autoship-mb-20">
			<label for="autoship-product-selector" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'Which product would you like to set up?', 'autoship' ) ); ?></label>
			<select required="required" name="autoship-product-selector" id="autoship-product-selector" class="autoship-quicklaunch-field-select">
				<option value="" disabled="disabled" selected="selected"><?php echo esc_html( __( 'Please choose one', 'autoship' ) ); ?></option>
				
				<?php foreach ( $products as $product ) : ?>
				<option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_name() ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

  
		<div class="autoship-mb-20">
			<h2 class="autoship-mt-40"><?php echo esc_html( __( 'Autoship & Save', 'autoship' ) ); ?></h2>
			
			<p class="autoship-font-sm">
				<?php echo esc_html( __( 'We recommend you to offer a subscription discount on Autoship. This offer can turn one-time buyers into loyal subscribers.', 'autoship' ) ); ?>
			</p>
	
			<div class="autoship-mb-20">
				<input type="checkbox" name="autoship-discount-enabler" id="autoship-discount-enabler"/>
				<label for="autoship-discount-enabler"><?php echo esc_html( __( 'Offer a discount on Autoship?', 'autoship' ) ); ?></label>
			</div>
	
			<div id="autoship-discount-container" class="autoship-mt-40 autoship-mb-20" style="display:none;">
				<label for="autoship-product-discount" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'What is the percentage of the discount?', 'autoship' ) ); ?></label>
				<input type="number" name="autoship-product-discount" id="autoship-product-discount" min="1" max="99" step="1" value="10" class="autoship-quicklaunch-field-text" placeholder="<?php echo esc_attr( __( 'The discount percentage on a subscription', 'autoship' ) ); ?>" />
				<small><strong><?php echo esc_html( __( 'Tip', 'autoship' ) ); ?></strong>: <?php echo esc_html( __( 'Specifying at least 10% off will boost your recurring revenue.', 'autoship' ) ); ?></small>

				<div id="autoship-product-discount-errors" style="padding-top:10px; text-align: center; font-weight: 700"></div>
			</div>
		</div>
  
		<div class="autoship-mt-40 autoship-mb-50">
			<h2><?php echo esc_html( __( 'Frequency Options', 'autoship' ) ); ?></h2>
			<p class="autoship-font-sm"><?php echo esc_html( __( 'All Autoship enabled products use monthly frequencies by default.', 'autoship' ) ); ?> <strong><?php echo esc_html( __( 'You can specify custom frequencies at this time and customize their names later.', 'autoship' ) ); ?></strong></p>
			<div class="autoship-mb-20">
				<input type="checkbox" id="autoship-frequency-enabler" name="autoship-frequency-enabler" required="required" />
				<label for="autoship-frequency-enabler"><?php echo esc_html( __( 'Specify recurring order frequency options?', 'autoship' ) ); ?></label>
			</div>
			<div id="autoship-frequency-container" class="autoship-mt-40 autoship-mb-50" style="display:none;">
				<p class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'Create scheduled order every?', 'autoship' ) ); ?></p>
				<div class="autoship-mb-20"  id="autoship-frequency-container-1" >
					<input type="number" name="autoship-product-frequency-number-1" id="autoship-product-frequency-number-1" class="autoship-quicklaunch-field-text" value="1" style="width: 25% !important;" />
					<select name="autoship-product-frequency-type-1" id="autoship-product-frequency-type-1" class="autoship-quicklaunch-field-select"  style="width: 60% !important; vertical-align: initial;">
						<option value="Days"><?php echo esc_html( __( 'Days', 'autoship' ) ); ?></option>
						<option value="Weeks"><?php echo esc_html( __( 'Weeks', 'autoship' ) ); ?></option>
						<option value="Months" selected="selected"><?php echo esc_html( __( 'Months', 'autoship' ) ); ?></option>
						<option value="DayOfTheMonth"><?php echo esc_html( __( 'Day of the month', 'autoship' ) ); ?></option>
						<option value="DayOfTheWeek"><?php echo esc_html( __( 'Day of the week', 'autoship' ) ); ?></option>
					</select>
	  
					<span class="dashicons dashicons-info-outline" style="margin-left: 10px; line-height: 40px;"></span>

					<div id="autoship-product-frequency-errors-1"></div>
				</div>

				<div class="autoship-mb-20  autoship-frequency-container-row" id="autoship-frequency-container-2" style="display: none;">
					<input type="number" name="autoship-product-frequency-number-2" id="autoship-product-frequency-number-2" class="autoship-quicklaunch-field-text" value="2" style="width: 25% !important;" />
					<select name="autoship-product-frequency-type-2" id="autoship-product-frequency-type-2" class="autoship-quicklaunch-field-select"  style="width: 60% !important; vertical-align: initial;">
						<option value="Days"><?php echo esc_html( __( 'Days', 'autoship' ) ); ?></option>
						<option value="Weeks"><?php echo esc_html( __( 'Weeks', 'autoship' ) ); ?></option>
						<option value="Months" selected="selected"><?php echo esc_html( __( 'Months', 'autoship' ) ); ?></option>
						<option value="DayOfTheMonth"><?php echo esc_html( __( 'Day of the month', 'autoship' ) ); ?></option>
						<option value="DayOfTheWeek"><?php echo esc_html( __( 'Day of the week', 'autoship' ) ); ?></option>
					</select>

					<span class="autoship-frequency-container-toggler dashicons dashicons-trash" style="margin-left: 10px; line-height: 40px;"></span>

					<div id="autoship-product-frequency-errors-2">
					</div>
				</div>



				<div class="autoship-mb-20 autoship-frequency-container-row" id="autoship-frequency-container-3" style="display: none;">
					<input type="number" name="autoship-product-frequency-number-3" id="autoship-product-frequency-number-3" class="autoship-quicklaunch-field-text" value="3" style="width: 25% !important;" />
					<select name="autoship-product-frequency-type-3" id="autoship-product-frequency-type-3" class="autoship-quicklaunch-field-select"  style="width: 60% !important; vertical-align: initial;">
						<option value="Days"><?php echo esc_html( __( 'Days', 'autoship' ) ); ?></option>
						<option value="Weeks"><?php echo esc_html( __( 'Weeks', 'autoship' ) ); ?></option>
						<option value="Months" selected="selected"><?php echo esc_html( __( 'Months', 'autoship' ) ); ?></option>
						<option value="DayOfTheMonth"><?php echo esc_html( __( 'Day of the month', 'autoship' ) ); ?></option>
						<option value="DayOfTheWeek"><?php echo esc_html( __( 'Day of the week', 'autoship' ) ); ?></option>
					</select>

					<span class="autoship-frequency-container-toggler dashicons dashicons-trash" style="margin-left: 10px; line-height: 40px;"></span>

					<div id="autoship-product-frequency-errors-3">
					</div>
				</div>

				<a href="javascript:void(0)" id="autoship-frequency-add" class="autoship-text-no-decoration autoship-highlighted-text"><span class="dashicons dashicons-plus-alt"></span> <?php echo esc_html( __( 'Add frequency', 'autoship' ) ); ?></a>
			</div>
		</div>
		<div class="autoship-quicklaunch-content-step-actions ">
			<div class="autoship-mb-20">
				<button type="button" id="product-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Continue', 'autoship' ) ); ?></button>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>