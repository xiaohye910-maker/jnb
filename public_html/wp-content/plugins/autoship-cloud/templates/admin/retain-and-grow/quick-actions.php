<?php
/**
 * This template is used to display the Autoship Cloud Quick Actions in the Retain & Grow tab.
 *
 * @package Autoship
 * @since 2.10.5
 */

$portal_site_id = rawurlencode( $site_id ?? 0 );
$portal_token   = $token_auth ?? '';

?>

<div id="qmc-quick-links-app" style="width: 100%; height: 100vh;">
    <qmc-quick-links-list
        id="qmc-quick-links"
        input-site-id="<?php echo $portal_site_id; ?>"
        input-token="<?php echo $portal_token; ?>"
        embedded-mode="true">
    </qmc-quick-links-list>
</div>
<script>
(function() {
    const QMC_CONFIG = {
        siteId: '<?php echo $portal_site_id; ?>',
        token: '<?php echo $portal_token; ?>'
    };

    function showList() {
        const container = document.getElementById('qmc-quick-links-app');
        container.innerHTML = `
            <qmc-quick-links-list
                id="qmc-quick-links"
                input-site-id="${QMC_CONFIG.siteId}"
                input-token="${QMC_CONFIG.token}"
                embedded-mode="true">
            </qmc-quick-links-list>`;
    }

    window.addEventListener('qmc-navigate', function(event) {
        const detail = event.detail;
        const container = document.getElementById('qmc-quick-links-app');

        if (detail.action === 'create') {
            container.innerHTML = `
                <qmc-quick-link-form
                    id="qmc-create"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    embedded-mode="true">
                </qmc-quick-link-form>`;
        } else if (detail.action === 'edit' && detail.quickLinkId) {
            container.innerHTML = `
                <qmc-quick-link-form
                    id="qmc-edit"
                    input-site-id="${QMC_CONFIG.siteId}"
                    input-token="${QMC_CONFIG.token}"
                    input-quick-link-id="${detail.quickLinkId}"
                    embedded-mode="true">
                </qmc-quick-link-form>`;
        }
    });

    window.addEventListener('qmc-quick-link-saved', function(event) {
        showList();
    });

    window.addEventListener('qmc-navigate-back', function(event) {
        showList();
    });
})();
</script>
