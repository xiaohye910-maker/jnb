<?php
/**
 * Shows the Autoship admin retention workflows page within Retain & Grow.
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
    <div id="qmc-retention-workflows-app" style="width: 100%; height: 100vh;">
        <qmc-retention-workflows-list
            id="qmc-retention-workflows"
            input-site-id="<?php echo $portal_site_id; ?>"
            input-token="<?php echo $portal_token; ?>"
            embedded-mode="true">
        </qmc-retention-workflows-list>
    </div>
    <script>
    (function() {
        const QMC_CONFIG = {
            siteId: '<?php echo $portal_site_id; ?>',
            token: '<?php echo $portal_token; ?>'
        };

        function showList() {
            const container = document.getElementById('qmc-retention-workflows-app');
            container.innerHTML = `
                <qmc-retention-workflows-list
                    id="qmc-retention-workflows"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    embedded-mode="true">
                </qmc-retention-workflows-list>`;
        }

        window.addEventListener('qmc-navigate', function(event) {
            const detail = event.detail;
            const container = document.getElementById('qmc-retention-workflows-app');

            if (detail.action === 'create') {
                container.innerHTML = `
                    <qmc-retention-workflow-create-edit
                        id="qmc-workflow-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-mode="create"
                        embedded-mode="true">
                    </qmc-retention-workflow-create-edit>`;
            } else if (detail.action === 'edit' && detail.workflowId) {
                container.innerHTML = `
                    <qmc-retention-workflow-create-edit
                        id="qmc-workflow-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-workflow-id="${detail.workflowId}"
                        input-mode="edit"
                        embedded-mode="true">
                    </qmc-retention-workflow-create-edit>`;
            } else if (detail.action === 'view' && detail.workflowId) {
                container.innerHTML = `
                    <qmc-retention-workflow-create-edit
                        id="qmc-workflow-edit"
                        input-site-id="${QMC_CONFIG.siteId}"
                        input-token="${QMC_CONFIG.token}"
                        input-workflow-id="${detail.workflowId}"
                        input-mode="view"
                        embedded-mode="true">
                    </qmc-retention-workflow-create-edit>`;
            }
        });

        window.addEventListener('qmc-workflow-saved', function(event) {
            showList();
        });

        window.addEventListener('qmc-navigate-back', function(event) {
            showList();
        });
    })();
    </script>
<?php else: ?>
    <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/retain-and-grow/retention?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-scheduled-orders-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php endif; ?>
