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

if ( ! apply_filters( 'autoship_include_scheduled_order_schedule_summary', true, $autoship_order, $customer_id, $autoship_customer_id ) ) {
	return;
}


/*
* The Skins Filter Allows Devs to Completely customize the forms classes.
*/
$skin = apply_filters(
	'autoship_order_schedule_summary_skin',
	array(
		'container' => '',
		'header'    => '',
		'content'   => '',
		'notice'    => '',
		'subnotice' => '',
	)
);

$item_count = autoship_get_item_count( $autoship_order );

// Next Date.
$next_date = autoship_get_formatted_local_date( $autoship_order['nextOccurrenceUtc'] );

// Header Notices & SubNotices.
$notice_format = '%1$s <mark class="order-date">%2$s</mark>';

$notice = 'Active' === $autoship_order['status'] || 'Processing' === $autoship_order['status'] ?
sprintf( $notice_format, __( 'Next occurrence is on', 'autoship' ), $next_date ) :
sprintf( $notice_format, __( 'Next occurrence would be on', 'autoship' ), $next_date );

$frequency_types         = autoship_valid_relative_next_occurrence_types();
$frequency_type_nicename = $frequency_types[ $autoship_order['frequencyType'] ];
$status_nicename         = autoship_get_scheduled_order_status_nicename( $autoship_order['status'] );
$status_translated_name  = autoship_get_scheduled_order_status_translatedname( $status_nicename );

$subnotice = 'Active' === $autoship_order['status'] || 'Processing' === $autoship_order['status'] ?

	sprintf(
		// translators: %1$d is the item count, %2$s is the frequency, %3$s is the frequency type (e.g., day, week, month).
		__( '<mark class="order-quantity">%1$d</mark> item(s) currently scheduled for <mark class="order-frequency">Every %2$s %3$s</mark>', 'autoship' ),
		$item_count,
		$autoship_order['frequency'],
		strtolower( $frequency_type_nicename )
	) :
	sprintf(
		// translators: %1$d is the item count, %2$s is the frequency, %3$s is the frequency type (e.g., day, week, month).
		__( '<mark class="order-quantity">%1$d</mark> item(s) currently paused but originally scheduled for <mark class="order-frequency">Every %2$s %3$s</mark>', 'autoship' ),
		$item_count,
		$autoship_order['frequency'],
		strtolower( $frequency_type_nicename )
	);

$notice    = apply_filters( 'autoship_order_details_summary_notice', $notice, $next_date, $status_translated_name, $autoship_order );
$subnotice = apply_filters( 'autoship_order_details_summary_subnotice', $subnotice, $next_date, $status_translated_name, $autoship_order );

?>

<div class="<?php echo esc_attr( $skin['container'] ); ?> <?php echo apply_filters( 'autoship_view_scheduled_order_summary_template_classes', 'autoship-scheduled-order-summary', $autoship_order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">

	<?php do_action( 'autoship_before_autoship_scheduled_order_summary', $autoship_order['status'], $autoship_order ); ?>

	<h3 class="<?php echo esc_attr( $skin['header'] ); ?>">
		<?php echo esc_html( __( 'Status', 'autoship' ) ); ?>: <mark class="order-status"><?php echo esc_html( $status_translated_name ); ?></mark>
	</h3>

	<div class="schedule-summary <?php echo esc_attr( $skin['content'] ); ?>">
		<p class="notice <?php echo esc_attr( $skin['notice'] ); ?>">
			<?php
			echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</p>
		<p class="subnotice <?php echo esc_attr( $skin['subnotice'] ); ?>">
			<?php
			echo $subnotice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</p>
	</div>

	<?php do_action( 'autoship_after_autoship_scheduled_order_summary', $autoship_order['status'], $autoship_order ); ?>

</div>
