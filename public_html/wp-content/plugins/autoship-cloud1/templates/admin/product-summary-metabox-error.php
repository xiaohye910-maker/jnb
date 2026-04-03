<?php
/**
 * This template is used to display the Product Summary Metabox for Errors.
 *
 * This template can be overridden by copying it to yourtheme/autoship-cloud/templates/product/product-summary-metabox-error.php
 *
 * @package Autoship
 * @since 1.0.0
 */

?>

<div id="autoship_product_summary_container">
	<p class="autoship-sync-<?php echo esc_attr( $error['type'] ); ?> <?php echo esc_attr( $error['code'] ); ?>">
		<?php echo wp_kses_post( apply_filters( 'autoship_product_summary_metabox_notice', $error['msg'], 'error', $autoship_summary, $product, $error ) ); ?>
	</p>
	<?php do_action( 'autoship_no_product_summary_metabox_table', $autoship_summary, $product ); ?>
</div>
