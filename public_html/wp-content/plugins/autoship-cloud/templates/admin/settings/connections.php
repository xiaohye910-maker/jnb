<?php
/**
 * This template is used to display the Autoship Cloud connection settings in the admin settings area.
 *
 * @package Autoship
 * @since 2.8.0
 */

use Autoship\Core\Plugin;
use Autoship\Services\Logging\Logger;
use Autoship\Services\Nextime\NextimeSettingsInterface;

$key_author = autoship_get_refreshed_api_keys_author();
$user_meta  = get_userdata( $key_author );
$user_email = ! empty( $user_meta ) ? $user_meta->user_email : 'Unavailable';

// Retrieve the Autoship site connection params.
$site_parameters = autoship_get_site_parameters();

// get the current health statuses.
$healthy = autoship_get_integration_health_status();

// Get the Autoship Health Check specific notifications from the queue.
$messages = autoship_get_messages( 'autoship_health_checks', true );

// Check if one or more error's were returned by QPilot.
// This deals with the case that the statuses were populated by error returned.
foreach ( $messages as $message ) {
	if ( 'error' === $message['type'] ) {
		$healthy = false;
	}
}

$status_class = '';

// Check if the user has entered any connection info.
if ( autoship_is_new() ) {

	$current_status = '<span class="ahstatus">' . __( 'Not Connected', 'autoship' ) . '<i class="icon-asc"></i></span>';

	$current_status_message  = __( 'Your connection to QPilot cloud is not setup.', 'autoship' );
	$current_status_message .= '<br/><a href="https://merchants.qpilot.cloud/login" target="_blank">' . __( 'Login to your QPilot Merchant Account', 'autoship' ) . '</a>' . __( ' to get your QPilot Client ID and Secret.', 'autoship' );
	$current_status_message .= '<br/>' . __( 'First time? See our help guide for ', 'autoship' ) . '<a href="https://support.autoship.cloud/article/319-3-connecting-the-woocommerce-api" target="_blank">' . __( 'connecting your WooCommerce API to QPilot.', 'autoship' ) . '</a>';

	$status_class = 'health-notice';

	// Get the status to see if the connection is healthy or not.
} elseif ( false !== $healthy && 'healthy' === $healthy ) {

	$current_status         = '<span class="ahstatus valid">' . __( 'Healthy', 'autoship' ) . '<i class="icon-asc"></i></span>';
	$current_status_message = __( 'For help resolving common API issues, please see our help guide for ', 'autoship' ) . '<a role="button" class="wp-autoship-link" href="https://support.autoship.cloud/article/403-troubleshooting-wc-api" target="_blank">' . __( 'WooCommerce API Healthiness', 'autoship' ) . '</a>';

	$status_class = 'health-valid';

} else {

	$current_status         = '<span class="ahstatus error">' . __( 'UnHealthy', 'autoship' ) . '<i class="icon-asc"></i></span>';
	$current_status_message = __( 'Your Autoship connection is not currently healthy and one or more connection requirements can not be confirmed.', 'autoship' );

	if ( ! empty( $messages ) ) {
		$current_status_message .= '<br/>' . __( 'Details about the connection issue(s) are listed below.', 'autoship' );
	}

	$current_status_message .= '<br/>' . __( 'For help resolving common API issues, please see our help guide for ', 'autoship' );
	$current_status_message .= '<a role="button" class="wp-autoship-link" href="https://support.autoship.cloud/article/403-troubleshooting-wc-api" target="_blank">' . __( 'WooCommerce API Healthiness', 'autoship' ) . '</a>';

	$status_class = 'health-error';
}

$show_nextime = false;
try {
	$container        = Plugin::get_service_container();
	$nextime_settings = $container->get( NextimeSettingsInterface::class );
	$nextime_site_id  = $nextime_settings->get_site_id();
	if ( ! empty( $nextime_site_id ) ) {
		$show_nextime = true;
	}
} catch ( Exception $e ) {
	Logger::log( 'error', 'Error getting Nextime settings: ' . $e->getMessage() );
	$show_nextime = false;
}

$subscription_status = autoship_get_subscription_status();

?>

<?php if ( 'None' === $subscription_status ) : ?>
    <div class="asc-alert asc-alert-warning">
        <i class="pi pi-exclamation-triangle"></i>
        <div class="asc-alert-content">
            <?php
            $merchant_url = esc_attr( autoship_get_qmc_portal_url() );

            // translators: %s is the URL to the QPilot Merchant Center.
            echo wp_kses_post( sprintf( __( "Your subscription is not active. Log in to the <a href='%s'>QPilot Merchant Center</a> to activate your subscription.", 'autoship' ), $merchant_url ) );
            ?>
        </div>
    </div>
<?php endif; ?>


<?php if ( $show_nextime ) : ?>
	<div id="autoship-status-box-summary"
			class="autoship-meta-boxes-summary autoship-box autoship_general_admin_notice <?php echo $nextime_settings->get_is_enabled() ? 'health-valid' : 'health-error'; ?>">
		<div class="autoship-box-content">
			<img src="https://nextime.ai/wp-content/uploads/2025/08/Nextime-Logo-Transparent.svg" width="150" alt="Nextime Logo" style="position:absolute; top: 20px; right: 20px;" />
			<ul class="autoship-stats-list">
				<li>
					<span class="list-label title-label">
						<?php if ( $nextime_settings->get_is_enabled() ) : ?>
							<span class="autoship-status-label">
								<?php esc_html_e( 'Autoship Advanced Shipping Features Enabled', 'autoship' ); ?>
							</span>

						<?php else : ?>
							<span class="autoship-status-label">
								<?php esc_html_e( 'Autoship Advanced Shipping Features Disabled', 'autoship' ); ?>
							</span>
						<?php endif; ?>
					</span>
				</li>
				<li>
					<span class="list-label">
						<?php if ( $nextime_settings->get_is_enabled() ) : ?>
							<span class="autoship-status-label">
								<p class="wp-autoship-label-message">
									<?php esc_html_e( 'Your store has a new Shipping Method available in WooCommerce powered by Nextime. If you need assistance, contact support.' ); ?>
								</p>
							</span>
						<?php else : ?>
							<span class="autoship-status-label">
								<p class="wp-autoship-label-message">
									<?php esc_html_e( 'You can enable Autoship Advanced Shipping Features directly in your QPilot Merchant Center. If you need assistance, contact support.' ); ?>
								</p>
							</span>
						<?php endif; ?>
					</span>
				</li>
			</ul>

		</div>
	</div>
<?php endif; ?>


<?php
    $alert_status_class = 'health-error' === $status_class ? 'asc-alert-danger' : 'asc-alert-info';
?>
<div id="autoship-status-box-summary" class="asc-alert <?php echo esc_attr($alert_status_class); ?> <?php echo esc_attr( $status_class ); ?>">
    <i class="pi pi-info-circle"></i>
    <div class="asc-alert-content">
        <h3><?php echo esc_html( __( 'API Health Check: Your Site Connection is ', 'autoship' ) ); ?><?php echo wp_kses_post( $current_status ); ?></h3>

        <?php echo wp_kses_post( $current_status_message ); ?>

        <?php if ( ! empty( $messages ) ) : ?>

            <div style="margin-top: 1rem;">
                <p><strong>Here are the details of the test:</strong></p>

            <?php foreach ( $messages as $message ) : ?>
                <p class="wp-autoship-label-message <?php echo esc_attr( $message['type'] ); ?>"> <?php echo wp_kses_post( $message['message'] ); ?></p>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>






<div class="asc-settings-columns">
	<div class="asc-settings-column">
		<h3><i class="pi pi-link"></i> <?php echo esc_html( __( 'API Connection', 'autoship' ) ); ?></h3>

        <p style="margin: 1rem 0;">
            <?php echo esc_html( __( 'Enter your QPilot credentials to connect your store. You can find these in your QPilot Merchant Center.', 'autoship' ) ); ?>
        </p>

		<?php if ( ! autoship_is_new() ) { ?>
		<div class="asc-form-row">
			<div class="asc-form-field full-width">
				<label for="connected_uid"><?php echo esc_html( __( 'Connected WP-Admin User', 'autoship' ) ); ?></label>
				<input type="text" id="connected_uid" name="connected_uid" class="connected_uid" value="<?php echo esc_attr( $user_email ); ?>" autocomplete="false" readonly disabled/>
			</div>
		</div>
		<?php } ?>

		<div class="asc-form-row">
			<div class="asc-form-field">
				<label for="autoship_client_id"><?php echo esc_html( __( 'QPilot Client ID', 'autoship' ) ); ?></label>
				<input type="text" id="autoship_client_id" name="autoship_client_id" value="<?php echo esc_attr( $autoship_settings['autoship_client_id'] ); ?>" autocomplete="false"/>
			</div>
			<div class="asc-form-field">
				<label for="autoship_client_secret"><?php echo esc_html( __( 'QPilot Client Secret', 'autoship' ) ); ?></label>
				<input type="password" id="autoship_client_secret" name="autoship_client_secret" value="<?php echo esc_attr( $autoship_settings['autoship_client_secret'] ); ?>" autocomplete="new-password"/>
			</div>
		</div>

		<?php if ( ! autoship_is_new() ) { ?>
		<div class="asc-form-row">
			<div class="asc-form-field full-width">
<!--					<label>--><?php //echo esc_html( __( 'Connect Autoship', 'autoship' ) ); ?><!--</label>-->
				<div class="asc-button-group">
					<a href="<?php echo esc_attr( admin_url( '/admin-ajax.php?action=autoship_oauth2_disconnect' ) ); ?>"
						onclick="return confirm(<?php echo esc_attr( wp_json_encode( __( 'Are you sure you want to disconnect?', 'autoship' ) ) ); ?>)"
						class="button button-danger"><?php echo esc_html( __( 'Disconnect', 'autoship' ) ); ?></a>
					<a href="<?php echo esc_attr( admin_url( '/admin-ajax.php?action=autoship_test_integration' ) ); ?>"
						class="button button-secondary"><?php echo esc_html( __( 'Test Integration', 'autoship' ) ); ?></a>
				</div>
			</div>
		</div>
		<?php } elseif ( autoship_has_credentials() ) { ?>
		<div class="asc-form-row">
			<div class="asc-form-field full-width">
				<label><?php echo esc_html( __( 'Connect Autoship', 'autoship' ) ); ?></label>
				<div class="asc-button-group">
					<a id="autoship_connect_autoship_button"
						href="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/oauth2?<?php echo str_replace( '+', '%20', http_build_query( $site_parameters ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
						class="button button-primary"><?php echo esc_html( __( 'Connect', 'autoship' ) ); ?></a>
				</div>
			</div>
		</div>
		<?php } ?>

	</div>

	<?php if ( ! autoship_is_new() ) { ?>
	<div class="asc-settings-column">
		<h3><i class="pi pi-server"></i> <?php echo esc_html( __( 'Merchant Site Fields', 'autoship' ) ); ?></h3>
<!--			<div class="asc-alert asc-alert-info">-->
<!--				<i class="pi pi-exclamation-triangle"></i>-->
<!--				<div class="asc-alert-content">-->
<!--					--><?php //echo esc_html( __( 'These fields are populated for you after connecting your site. Please do not change them unless advised to do so!', 'autoship' ) ); ?>
<!--				</div>-->
<!--			</div>-->

        <p style="margin: 1rem 0;">
            <?php echo esc_html( __( 'These fields are populated for you after connecting your site. Please do not change them unless advised to do so!', 'autoship' ) ); ?>
        </p>

		<div class="asc-form-row">
			<div class="asc-form-field">
				<label for="autoship_user_id"><?php echo esc_html( __( 'User ID', 'autoship' ) ); ?></label>
				<input type="text" id="autoship_user_id" name="autoship_user_id" value="<?php echo esc_attr( $autoship_settings['autoship_user_id'] ); ?>" autocomplete="false"/>
			</div>
			<div class="asc-form-field">
				<label for="autoship_site_id"><?php echo esc_html( __( 'Site ID', 'autoship' ) ); ?></label>
				<input type="text" id="autoship_site_id" name="autoship_site_id" value="<?php echo esc_attr( $autoship_settings['autoship_site_id'] ); ?>" autocomplete="new-password"/>
			</div>
		</div>

		<div class="asc-form-row">
			<div class="asc-form-field">
				<label for="autoship_token_auth"><?php echo esc_html( __( 'Token Auth', 'autoship' ) ); ?></label>
				<input type="password" id="autoship_token_auth" name="autoship_token_auth" value="<?php echo esc_attr( $autoship_settings['autoship_token_auth'] ); ?>" autocomplete="new-password"/>
			</div>
			<div class="asc-form-field">
				<label for="autoship_refresh_token"><?php echo esc_html( __( 'Refresh Token', 'autoship' ) ); ?></label>
				<input type="password" id="autoship_refresh_token" name="autoship_refresh_token" value="<?php echo esc_attr( $autoship_settings['autoship_refresh_token'] ); ?>" autocomplete="new-password"/>
			</div>
		</div>

	</div>
	<?php } ?>
</div>
