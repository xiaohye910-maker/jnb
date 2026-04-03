<?php
/**
 * The Dynamic Schedule Cart Template
 *
 * This template can be overridden by copying it to yourtheme/autoship-cloud/templates/scheduled-orders-cart/schedule-cart.php
 *
 * @package Autoship
 * @since 1.0.0
 */

?>
<div class="autoship-schedule-cart">
	<?php foreach ( $cart_items_grouped_by_frequency as $group ) : ?>
		<?php if ( null === $group['frequency_type'] ) : ?>
			<h3>
				<?php
				// translators: %s is Autoship.
				echo wp_kses_post( sprintf( __( 'Add to %s', 'autoship' ), autoship_translate_text( 'Autoship', false ) ) );
				?>
				<br />
				<a href="javascript:void(0);" onclick="autoshipScheduleCartOpenDialog( null, null, <?php echo esc_attr( wp_json_encode( array_keys( $group['items'] ) ) ); ?>, null );">
					<small>
						<?php
						// translators: %s is Autoship.
						echo esc_html( sprintf( __( 'Add to %s', 'autoship' ), autoship_translate_text( 'Autoship' ) ) );
						?>
					</small>
				</a>
			</h3>
			<table class="autoship-schedule-cart-items">
				<tbody>
				<?php foreach ( $group['items'] as $item ) : ?>
					<tr>
						<td class="image-cell"><?php echo $item['data']->get_image(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td class="name-cell"><?php echo esc_html( autoship_get_product_display_name( $item['data'] ) ); ?></td>
						<td class="quantity-cell"><?php echo esc_html( $item['quantity'] ); ?></td>
						<td class="price-cell"><?php echo wp_kses_post( wc_price( $item['line_subtotal'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="frequency-title">
				<h3>
					<?php echo esc_html( autoship_get_frequency_display_name( $group['frequency_type'], $group['frequency'] ) ); ?>
					<br />
					<a href="javascript:void(0);" onclick="autoshipScheduleCartOpenDialog(<?php echo esc_attr( wp_json_encode( $group['frequency_type'] ) ); ?>, <?php echo esc_attr( wp_json_encode( $group['frequency'] ) ); ?>, <?php echo esc_attr( wp_json_encode( array_keys( $group['items'] ) ) ); ?>, <?php echo esc_attr( wp_json_encode( $group['next_occurrence'] ) ); ?>);">
						<small><?php echo esc_html( __( 'Change Schedule', 'autoship' ) ); ?></small>
					</a>
				</h3>
				<div class="next-occurrence">

					<?php if ( ! empty( $group['next_occurrence'] ) ) : ?>
						<?php echo esc_html( __( 'Next Order:', 'autoship' ) ); ?>
						<a href="javascript:void(0);" onclick="autoshipScheduleCartOpenSelectNextOccurrenceDialog(<?php echo esc_attr( wp_json_encode( date_i18n( 'c', $group['next_occurrence'] ) ) ); ?>, <?php echo esc_attr( wp_json_encode( $group['frequency_type'] ) ); ?>, <?php echo esc_attr( wp_json_encode( $group['frequency'] ) ); ?>, <?php echo esc_attr( wp_json_encode( array_keys( $group['items'] ) ) ); ?> );">
							<?php echo wp_kses_post( __( date_i18n( get_option( 'date_format' ), $group['next_occurrence'] ), 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>
						</a>
					<?php else : ?>
						<a href="javascript:void(0);" onclick="autoshipScheduleCartOpenSelectNextOccurrenceDialog( null, <?php echo esc_attr( wp_json_encode( $group['frequency_type'] ) ); ?>, <?php echo esc_attr( wp_json_encode( $group['frequency'] ) ); ?>, <?php echo esc_attr( wp_json_encode( array_keys( $group['items'] ) ) ); ?> );">
							<?php echo esc_html( __( 'Schedule next order', 'autoship' ) ); ?>
						</a>
					<?php endif; ?>
				</div>

			</div>
			<table class="autoship-schedule-cart-items">
				<tbody>
				<?php foreach ( $group['items'] as $item ) : ?>
					<tr>
						<td class="image-cell"><?php echo $item['data']->get_image(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td class="name-cell"><?php echo esc_html( autoship_get_product_display_name( $item['data'] ) ); ?></td>
						<td class="quantity-cell"><?php echo esc_html( $item['quantity'] ); ?></td>
						<td class="price-cell"><?php echo wp_kses_post( wc_price( $item['line_subtotal'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	<?php endforeach; ?>
	<h3>
		Bulk Actions
		<br />
		<a href="javascript: void(0);" onclick="autoshipScheduleCartOpenDialog( null, null, <?php echo esc_attr( wp_json_encode( array_keys( WC()->cart->cart_contents ) ) ); ?>, null );"><small>Schedule all items</small></a>
	</h3>
</div>
