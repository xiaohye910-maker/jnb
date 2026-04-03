<?php
/**
 * This template is used to display the Autoship Cloud Reports in the admin area.
 *
 * @package Autoship
 * @since 1.0.0
 */

do_action( 'autoship_before_autoship_admin_reports' );

$tabs       = autoship_reports_tabs(); //phpcs:ignore
$active_tab = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? $_GET['tab'] : apply_filters( 'autoship_admin_reports_default_tab', key( $tabs ) ); //phpcs:ignore

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
            <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Reports">
                <span class="breadcrumb-text">Reports</span>
            </span>
        </div>
    </nav>
    <div class="header-right">
        <div class="title-section ng-star-inserted">
            <h1 class="page-title">Reports</h1>
        </div>
    </div>
</div>



<div id="asc-settings" >
	<?php do_action( 'autoship_after_admin_reports_header', $active_tab, $tabs ); ?>
	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab => $values ) { //phpcs:ignore ?>
			<a href="<?php echo admin_url( 'admin.php?page=reports&tab='. $tab ); ?>" class="nav-tab <?php echo $values['link_class']; ?> <?php echo $active_tab == $tab ? 'nav-tab-active' : ''; ?>"><?php echo $values['label']; // phpcs:ignore ?></a>
		<?php } ?>
	</h2>
	<?php foreach ( $tabs as $tab => $values ) { //phpcs:ignore ?>
		<div id="<?php echo $tab; ?>" style="display:<?php echo $active_tab == $tab ? 'block' : 'none'; //phpcs:ignore ?>;">

			<?php
			$function = $values['callback'];
			if ( function_exists( $function ) && $active_tab == $tab ) { //phpcs:ignore
				$function( $active_tab );
			}
			?>

		</div>
	<?php } ?>
</div>

<?php do_action( 'autoship_after_autoship_admin_reports' ); ?>
