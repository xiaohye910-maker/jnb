<?php
/**
 * This template is used to display the Autoship Cloud Retain and Grow in the admin area.
 *
 * @package Autoship
 * @since 1.0.0
 */

?>

<?php

// Retrieve all the Autoship fields into an array.
$autoship_settings = autoship_get_settings_fields();

$tabs          = autoship_retain_and_grow_tabs(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$default_item  = apply_filters( 'autoship_admin_settings_default_tab', key( $tabs ) );
$selected_item = $default_item;

if ( isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$selected_item = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

?>

<div id="asc-settings" class="wrap">
	<?php do_action( 'autoship_after_admin_retain_and_grow_header', $selected_item, $tabs ); ?>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $item => $values ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=retain-and-grow&tab=' . $item ) ); ?>" class="nav-tab <?php echo esc_attr( $values['link_class'] ); ?> <?php echo $selected_item === $item ? 'nav-tab-active' : ''; ?>"><?php echo wp_kses_post( $values['label'] ); ?></a>
		<?php endforeach; ?>
	</h2>
	<?php foreach ( $tabs as $item => $values ) : ?>
		<div id="<?php echo esc_attr( $item ); ?>" class="wrap" style="display:<?php echo esc_attr( $selected_item === $item ? 'block' : 'none' ); ?>;">
			<?php
			$function = $values['callback'];
			?>
			<?php if ( function_exists( $function ) && $selected_item === $item ) : ?>
				<?php $function( $selected_item ); ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
