<?php
/**
 * Shows the Autoship admin dashboard page.
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;

$portal_site_id     = rawurlencode( $site_id ?? 0 );
$portal_token       = $token_auth ?? '';
$use_component_view = Plugin::get_service_container()->get( FeatureManagerInterface::class )->is_enabled( 'qmc_components' );

?>

<?php do_action( 'autoship_before_autoship_admin_dashboard' ); ?>

<?php if ( $use_component_view ) : ?>

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
                <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Dashboard">
                <span class="breadcrumb-text">Dashboard</span>
            </span>
            </div>
        </nav>
        <div class="header-right">
            <div class="title-section ng-star-inserted">
                <h1 class="page-title">Dashboard</h1>
            </div>
        </div>
    </div>


<qmc-dashboard-container
        id="qmc-dashboard"
        input-site-id="<?php echo $portal_site_id; ?>"
        input-token="<?php echo $portal_token; ?>"
        embedded-mode="true"
        style="width: 100%; height: 100vh;">
</qmc-dashboard-container>
<?php else: ?>
    <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/dashboard?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-coupons-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php endif; ?>

<?php do_action( 'autoship_after_autoship_admin_dashboard' ); ?>
