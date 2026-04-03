<?php
/**
 * Shows the Autoship admin coupons page within Retain & Grow.
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

<?php if ( $use_component_view ) : ?>
    <div id="qmc-coupons-app" style="width: 100%; height: 100vh;">
        <qmc-coupons-list
            id="qmc-coupons"
            input-site-id="<?php echo $portal_site_id; ?>"
            input-token="<?php echo $portal_token; ?>"
            embedded-mode="true">
        </qmc-coupons-list>
    </div>
    <script>
    (function() {
        const QMC_CONFIG = {
            siteId: '<?php echo $portal_site_id; ?>',
            token: '<?php echo $portal_token; ?>'
        };

        function showList() {
            const container = document.getElementById('qmc-coupons-app');
            container.innerHTML = `
                <qmc-coupons-list
                    id="qmc-coupons"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    embedded-mode="true">
                </qmc-coupons-list>`;
        }

        window.addEventListener('qmc-navigate', function(event) {
            const detail = event.detail;
            const container = document.getElementById('qmc-coupons-app');

            if (detail.action === 'create') {
                container.innerHTML = `
                    <qmc-coupon-edit
                        id="qmc-coupon-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-mode="create"
                        embedded-mode="true">
                    </qmc-coupon-edit>`;
            } else if (detail.action === 'edit' && detail.couponId) {
                container.innerHTML = `
                    <qmc-coupon-edit
                        id="qmc-coupon-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-coupon-id="${detail.couponId}"
                        input-mode="edit"
                        embedded-mode="true">
                    </qmc-coupon-edit>`;
            }
        });

        window.addEventListener('qmc-coupon-saved', function(event) {
            showList();
        });

        window.addEventListener('qmc-navigate-back', function(event) {
            showList();
        });
    })();
    </script>
<?php else: ?>
    <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/coupons?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-scheduled-orders-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php endif; ?>
