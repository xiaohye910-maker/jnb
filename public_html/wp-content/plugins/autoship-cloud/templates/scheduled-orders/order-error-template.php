<?php
/**
 * Scheduled Order Error Template
 *
 * Displays the Error Information when the API Call fails on the Scheduled Order.
 *
 * This template can be overridden by copying it to yourtheme/autoship-cloud/templates/scheduled-orders/order-error-template.php
 *
 * @package Autoship
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'autoship_include_scheduled_order_error', true, $autoship_order, $customer_id, $autoship_customer_id, $autoship_order_id ) ) {
	return;
}

/*
* The Skins Filter Allows Devs to Completely customize the forms classes.
*/
$skin = apply_filters(
	'autoship_scheduled_order_error_skin',
	array(
		'container' => '',
		'content'   => '',
		'header'    => '',
		'notice'    => '',
		'subnotice' => '',
	)
);


// Add fall back.
if ( empty( $autoship_order ) || ! is_wp_error( $autoship_order ) ) {
	// translators: %s is the Scheduled Order ID.
	$autoship_order = new WP_Error( 'Order Display Failed', sprintf( __( 'A problem was encountered while trying to display %1$s #%2$s', 'autoship' ), autoship_translate_text( 'Scheduled Order' ), '<mark class="order-number">' . $autoship_order_id . '</mark>' ) );
}

do_action( 'autoship_before_schedule_order_error', $autoship_order, $customer_id, $autoship_customer_id, $autoship_order_id );
?>

<div class="<?php echo esc_attr( $skin['container'] ); ?> <?php echo apply_filters( 'autoship_scheduled_order_error_template_classes', 'autoship-scheduled-order-template autoship-error-template', $autoship_order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">

	<div class="autoship-error <?php echo esc_attr( $skin['content'] ); ?>">

		<h2 class="<?php echo esc_attr( $skin['header'] ); ?>">
			<?php
			// translators: %1$s is the Scheduled Order label, %2$s is the Scheduled Order ID.
			printf( __( '%1$s #%2$s', 'autoship' ), autoship_translate_text( 'Scheduled Order' ), '<mark class="order-number">' . $autoship_order_id . '</mark>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</h2>

		<h3 class="<?php echo esc_attr( $skin['notice'] ); ?>"><span><?php echo wp_kses_post( $autoship_order->get_error_code() ); ?></span></h3>

	</div>

	<p class="autoship_order_error <?php echo esc_attr( $skin['subnotice'] ); ?>"><?php echo wp_kses_post( $autoship_order->get_error_message() ); ?></p>

</div>
<?php
do_action( 'autoship_after_schedule_order_error', $autoship_order, $customer_id, $autoship_customer_id, $autoship_order_id );
