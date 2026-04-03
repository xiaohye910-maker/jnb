<?php
/**
 * Schedule Order Edit - No Items content
 * This template can be overridden by copying it to yourtheme/autoship-cloud/templates/scheduled-orders/order-no-items-template.php
 *
 * @package Autoship
 * @subpackage Templates
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'autoship_include_scheduled_order_no_items_template', true, $autoship_order, $customer_id, $autoship_customer_id ) ) {
	return;
}

/*
* The Skins Filter Allows Devs to Completely customize the forms classes.
*/
$skin = apply_filters(
	'autoship_scheduled_order_items_not_items_skin',
	array(
		'table_row'   => 'woocommerce-cart-form__cart-item',
		'table_cell'  => 'autoship-update-action autoship-update-order-action actions',
		'content'     => '',
		'notice'      => '',
		'subnotice'   => '',
		'action_link' => 'autoship-action-link',
		'action_span' => '',
	)
);


$actions       = autoship_get_account_scheduled_orders_actions( $autoship_order['id'], $autoship_order['status'], 'empty-order', $customer_id );
$actions       = apply_filters( 'autoship_no_order_items_form_actions', $actions, $autoship_order, $customer_id, $autoship_customer_id );
$action_string = apply_filters(
	'autoship_no_order_items_form_call_to_action',
	sprintf(
		! empty( $actions ) ?
			// translators: %s is the Autoship Order Type, e.g. Scheduled Order.
			__( 'Add a Product to your %s or choose an option below:', 'autoship' ) :

			// translators: %s is the Autoship Order Type, e.g. Scheduled Order.
			__( 'Add a Product to your %s using the Add a Product drop down.', 'autoship' ),
		autoship_translate_text( 'Scheduled Order' )
	),
	$actions,
	$autoship_order,
	$customer_id,
	$autoship_customer_id
);

?>

<tr class="<?php echo esc_attr( $skin['table_row'] ); ?>">
	<td colspan="6" class="<?php echo esc_attr( $skin['table_cell'] ); ?> no-items">
		<div class="no-items-notice <?php echo esc_attr( $skin['content'] ); ?>">

			<h3 class="<?php echo esc_attr( $skin['notice'] ); ?>">
				<?php echo apply_filters( 'autoship_order_notice_no_items', __( 'No items currently scheduled.', 'autoship' ), $autoship_order, $customer_id, $autoship_customer_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</h3>

			<p class="<?php echo esc_attr( $skin['subnotice'] ); ?>">
				<?php echo apply_filters( 'autoship_order_details_no_items', $action_string, $autoship_order, $customer_id, $autoship_customer_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>

			<?php if ( ! empty( $actions ) ) : ?>
				<?php foreach ( $actions as $key => $action ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>

				<a title="<?php echo esc_html( $action['title'] ); ?>" href="<?php echo esc_url( $action['url'] ); ?>" class="<?php echo esc_attr( $skin['action_link'] ); ?> <?php echo sanitize_html_class( strtolower( $key ) ); ?>" data-autoship-order="<?php echo esc_attr( $autoship_order['id'] ); ?>" data-autoship-action="<?php echo esc_attr( $key ); ?>" data-autoship-view="order">
					<span class="<?php echo esc_attr( $skin['action_span'] ); ?>">
						<?php esc_html_e( $action['name'] ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>
					</span> 
				</a>

				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</td>
</tr>
