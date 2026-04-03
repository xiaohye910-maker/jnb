<?php
/**
 * This template is used to display the Autoship Cloud Scheduled Orders in the admin area.
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

<?php do_action( 'autoship_before_autoship_admin_scheduled_orders' ); ?>
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
                    <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Scheduled Orders">
                <span class="breadcrumb-text">Scheduled Orders</span>
            </span>
                </div>
            </nav>
            <div class="header-right">
                <div class="title-section ng-star-inserted">
                    <h1 class="page-title">Scheduled Orders</h1>
                </div>
            </div>
        </div>


        <div id="qmc-scheduled-orders-app" style="width: 100%; height: 100vh;">
            <qmc-scheduled-orders-list
                    id="qmc-orders"
                    input-site-id="<?php echo $portal_site_id; ?>"
                    input-token="<?php echo $portal_token; ?>"
                    embedded-mode="true">
            </qmc-scheduled-orders-list>
        </div>
        <script>
        (function() {
            const QMC_CONFIG = {
                siteId: '<?php echo $portal_site_id; ?>',
                token: '<?php echo $portal_token; ?>'
            };

            function showList() {
                const container = document.getElementById('qmc-scheduled-orders-app');
                container.innerHTML = `
                    <qmc-scheduled-orders-list
                        id="qmc-orders"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        embedded-mode="true">
                    </qmc-scheduled-orders-list>`;
            }

            window.addEventListener('qmc-navigate', function(event) {
                const detail = event.detail;
                const container = document.getElementById('qmc-scheduled-orders-app');

                if (detail.action === 'create') {
                    container.innerHTML = `
                        <qmc-create-scheduled-order
                            id="qmc-create"
                            input-site-id="${QMC_CONFIG.siteId}"
                            input-token="${QMC_CONFIG.token}"
                            embedded-mode="true">
                        </qmc-create-scheduled-order>`;
                } else if (detail.action === 'edit' && detail.orderId) {
                    container.innerHTML = `
                        <qmc-edit-scheduled-order
                            id="qmc-edit"
                            input-site-id="${QMC_CONFIG.siteId}"
                            input-token="${QMC_CONFIG.token}"
                            input-order-id="${detail.orderId}"
                            embedded-mode="true">
                        </qmc-edit-scheduled-order>`;
                }
            });

            window.addEventListener('qmc-navigate-to-cycle', function(event) {
                const detail = event.detail;
                const container = document.getElementById('qmc-scheduled-orders-app');
                container.innerHTML = `
                    <qmc-processing-cycle-view
                        id="qmc-cycle"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-scheduled-order-id="${detail.scheduledOrderId}"
                        input-processing-cycle-id="${detail.processingCycleId}"
                        embedded-mode="true">
                    </qmc-processing-cycle-view>`;
            });

            window.addEventListener('qmc-navigate-back', function(event) {
                const detail = event.detail;
                const container = document.getElementById('qmc-scheduled-orders-app');

                if (detail.targetComponent === 'scheduled-order-edit' && detail.scheduledOrderId) {
                    container.innerHTML = `
                        <qmc-edit-scheduled-order
                            id="qmc-edit"
                            input-site-id="${QMC_CONFIG.siteId}"
                            input-token="${QMC_CONFIG.token}"
                            input-order-id="${detail.scheduledOrderId}"
                            embedded-mode="true">
                        </qmc-edit-scheduled-order>`;
                } else {
                    showList();
                }
            });

            window.addEventListener('qmc-order-saved', function(event) {
                showList();
            });

            window.addEventListener('qmc-order-deleted', function(event) {
                showList();
            });
        })();
        </script>
    <?php else: ?>
        <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/scheduled-orders?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-scheduled-orders-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
    <?php endif; ?>

<?php do_action( 'autoship_after_autoship_admin_scheduled_orders' ); ?>
