<?php
/**
 * Main Schedule Orders V2 Portal and app Template
 * This template can be overridden by copying it to yourtheme/autoship-cloud/templates/scheduled-orders-v2-portal.php
 *
 * @package Autoship
 * @since 1.0.0
 *
 * Pre Scheduled Orders APP hooks:
 * @hooked autoship_scheduled_orders_header_wp_notices_display  - 9
 * @hooked autoship_scheduled_orders_custom_html_header_display - 10
 * @hooked autoship_non_hosted_scheduled_order_error_display    - 11
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="autoship-container">
	<?php do_action( 'autoship_before_autoship_scheduled_orders', $customer_id, $autoship_customer_id ); ?>
	<div id="content-container" style="background-color: white; position: relative; overflow: scroll">
		<subscribers-portal-app site-id="<?php echo esc_attr( $site_id ); ?>" customer-id="<?php echo esc_attr( $customer_id ); ?>" token="<?php echo esc_attr( $token ); ?>" style="height: 100%; display: block; overflow: auto;"></subscribers-portal-app>
	</div>
	<?php do_action( 'autoship_after_autoship_scheduled_orders', $customer_id, $autoship_customer_id ); ?>
</div>
