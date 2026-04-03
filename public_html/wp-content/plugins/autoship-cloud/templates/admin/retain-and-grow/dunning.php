<?php
/**
 * Shows the Autoship admin dunning settings page within Retain & Grow.
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
    <div id="qmc-dunning-app" style="width: 100%; height: 100vh;">
        <qmc-dunning-settings
            id="qmc-dunning"
            input-site-id="<?php echo $portal_site_id; ?>"
            input-token="<?php echo $portal_token; ?>"
            embedded-mode="true">
        </qmc-dunning-settings>
    </div>
<?php else: ?>
    <iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/settings/site-dunning?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-scheduled-orders-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php endif; ?>
