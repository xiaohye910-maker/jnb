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


<div class="breadcrumb-header-compact">
    <nav aria-label="Breadcrumb navigation" role="navigation" class="inline-breadcrumb">
        <div class="breadcrumb-container">
            <a class="breadcrumb-link home-link ng-star-inserted" aria-label="Navigate to Home" href="/wp-admin">
                <i class="home-icon pi pi-home"></i>
            </a>
            <i aria-hidden="true" class="separator pi pi-angle-right ng-star-inserted"></i>

            <a class="breadcrumb-link ng-star-inserted" aria-label="Navigate to Sites" href="/wp-admin/admin.php?page=dashboard">
                <span class="breadcrumb-text">Autoship</span>
            </a>
            <i aria-hidden="true" class="separator pi pi-angle-right ng-star-inserted"></i>
            <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Retain & Grow">
                <span class="breadcrumb-text">Retain & Grow</span>
            </span>
        </div>
    </nav>
    <div class="header-right">
        <div class="title-section ng-star-inserted">
            <h1 class="page-title">Retain & Grow</h1>
        </div>
    </div>
</div>


<div id="asc-settings" >
	<?php do_action( 'autoship_after_admin_retain_and_grow_header', $selected_item, $tabs ); ?>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $item => $values ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=retain-and-grow&tab=' . $item ) ); ?>" class="nav-tab <?php echo esc_attr( $values['link_class'] ); ?> <?php echo $selected_item === $item ? 'nav-tab-active' : ''; ?>"><?php echo wp_kses_post( $values['label'] ); ?></a>
		<?php endforeach; ?>
	</h2>
	<?php foreach ( $tabs as $item => $values ) : ?>
		<div id="<?php echo esc_attr( $item ); ?>" style="display:<?php echo esc_attr( $selected_item === $item ? 'block' : 'none' ); ?>;">
			<?php
			$function = $values['callback'];
			?>
			<?php if ( function_exists( $function ) && $selected_item === $item ) : ?>
				<?php $function( $selected_item ); ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
