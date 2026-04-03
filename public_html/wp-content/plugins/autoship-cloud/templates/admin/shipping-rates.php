<?php
/**
 * This template is used to display the Autoship Cloud Shipping Rates in the admin area.
 *
 * @package Autoship
 * @since 1.0.0
 */

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
            <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Shipping Rates">
                <span class="breadcrumb-text">Shipping Rates</span>
            </span>
        </div>
    </nav>
    <div class="header-right">
        <div class="title-section ng-star-inserted">
            <h1 class="page-title">Shipping Rates</h1>
        </div>
    </div>
</div>

<?php do_action( 'autoship_before_autoship_admin_shipping_rates' ); ?>
	<iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/shipping-rates?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-shipping-rates-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php do_action( 'autoship_after_autoship_admin_shiping_rates' ); ?>
