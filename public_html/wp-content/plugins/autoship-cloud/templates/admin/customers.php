<?php
/**
 * Shows the Autoship admin customers page.
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

<?php do_action( 'autoship_before_autoship_admin_customers' ); ?>

<?php if ( $use_component_view ) : ?>

<!--    <h1 style="font-size: 24px; font-weight: bold;"><i class="pi pi-users" style="font-size: 24px; font-weight: bold;"></i> Customers</h1>-->
<!--    <h1 _ngcontent-ng-c2020289195="" class="page-title">Product Groups</h1>-->


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
            <span class="breadcrumb-current ng-star-inserted" aria-current="page" aria-label="Current page: Customers">
                <span class="breadcrumb-text">Customers</span>
            </span>
        </div>
    </nav>
    <div class="header-right">
        <div class="title-section ng-star-inserted">
            <h1 class="page-title">Customers</h1>
        </div>
    </div>
</div>











    <div id="qmc-customers-app" style="width: 100%; height: 100vh;">
        <qmc-customers-list
            id="qmc-customers"
            input-site-id="<?php echo $portal_site_id; ?>"
            input-token="<?php echo $portal_token; ?>"
            embedded-mode="true">
        </qmc-customers-list>
    </div>
    <script>
    (function() {
        const QMC_CONFIG = {
            siteId: '<?php echo $portal_site_id; ?>',
            token: '<?php echo $portal_token; ?>'
        };

        function showList() {
            const container = document.getElementById('qmc-customers-app');
            container.innerHTML = `
                <qmc-customers-list
                    id="qmc-customers"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    embedded-mode="true">
                </qmc-customers-list>`;
        }

        function showScheduledOrders(searchTerm) {
            const container = document.getElementById('qmc-customers-app');
            container.innerHTML = `
                <qmc-scheduled-orders-list
                    id="qmc-orders"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    embedded-mode="true"
                    ${searchTerm ? `input-search="${searchTerm}"` : ''}>
                </qmc-scheduled-orders-list>`;
        }

        window.addEventListener('qmc-navigate', function(event) {
            const detail = event.detail;
            const container = document.getElementById('qmc-customers-app');

            if (detail.action === 'edit' && detail.customerId) {
                container.innerHTML = `
                    <qmc-customer-edit
                        id="qmc-customer-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-customer-id="${detail.customerId}"
                        embedded-mode="true">
                    </qmc-customer-edit>`;
            } else if (detail.action === 'list') {
                showList();
            } else if (detail.action === 'viewScheduledOrders' || detail.action === 'searchScheduledOrders') {
                const searchTerm = detail.searchTerm || detail.customerEmail || '';
                showScheduledOrders(searchTerm);
            } else if (detail.action === 'create') {
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

        window.addEventListener('qmc-customer-saved', function(event) {
            showList();
        });

        window.addEventListener('qmc-navigate-back', function(event) {
            const detail = event.detail;
            const container = document.getElementById('qmc-customers-app');

            if (detail.component === 'processing-cycle-view' && detail.scheduledOrderId) {
                container.innerHTML = `
                    <qmc-edit-scheduled-order
                        id="qmc-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-order-id="${detail.scheduledOrderId}"
                        embedded-mode="true">
                    </qmc-edit-scheduled-order>`;
            } else if (detail.component === 'scheduled-order-edit' || detail.component === 'create-scheduled-order') {
                showScheduledOrders('');
            } else if (detail.component === 'scheduled-orders-list') {
                showList();
            } else {
                showList();
            }
        });

        window.addEventListener('qmc-order-saved', function(event) {
            showScheduledOrders('');
        });

        window.addEventListener('qmc-order-deleted', function(event) {
            showScheduledOrders('');
        });

        window.addEventListener('qmc-navigate-to-cycle', function(event) {
            const detail = event.detail;
            const container = document.getElementById('qmc-customers-app');
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
    })();
    </script>
<?php else: ?>
    <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/customers?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-customers-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php endif; ?>

<?php do_action( 'autoship_after_autoship_admin_customers' ); ?>
