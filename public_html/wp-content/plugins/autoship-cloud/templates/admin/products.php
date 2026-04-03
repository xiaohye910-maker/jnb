<?php
/**
 * This template is used to display the Autoship Cloud Products in the admin area.
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

<?php do_action( 'autoship_before_autoship_admin_products' ); ?>

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
                <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Products">
                <span class="breadcrumb-text">Products</span>
            </span>
            </div>
        </nav>
        <div class="header-right">
            <div class="title-section ng-star-inserted">
                <h1 class="page-title">Products</h1>
            </div>
        </div>
    </div>


    <div id="qmc-products-app" style="width: 100%; height: 100vh;">
        <qmc-products-list
            id="qmc-products"
            input-site-id="<?php echo $portal_site_id; ?>"
            input-token="<?php echo $portal_token; ?>"
            embedded-mode="true">
        </qmc-products-list>
    </div>
    <script>
    (function() {
        const QMC_CONFIG = {
            siteId: '<?php echo $portal_site_id; ?>',
            token: '<?php echo $portal_token; ?>'
        };

        function showList() {
            const container = document.getElementById('qmc-products-app');
            container.innerHTML = `
                <qmc-products-list
                    id="qmc-products"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    embedded-mode="true">
                </qmc-products-list>`;
        }

        window.addEventListener('qmc-navigate-to-product', function(event) {
            const detail = event.detail;
            const container = document.getElementById('qmc-products-app');

            container.innerHTML = `
                <qmc-product-detail
                    id="qmc-product-detail"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    input-external-id="${detail.externalId}"
                    embedded-mode="true">
                </qmc-product-detail>`;
        });

        window.addEventListener('qmc-navigate-back', function(event) {
            const detail = event.detail;

            if (detail.targetComponent === 'products-list' || !detail.targetComponent) {
                showList();
            }
        });
    })();
    </script>
<?php else: ?>
    <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/products?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-products-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php endif; ?>

<?php do_action( 'autoship_after_autoship_admin_products' ); ?>
