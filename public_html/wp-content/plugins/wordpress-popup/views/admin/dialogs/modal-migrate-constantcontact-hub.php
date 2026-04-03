<?php
/**
 * Modal for when migrating Aweber.
 *
 * @package Hustle
 * @since 4.1.1
 */

$cc_instance = Hustle_ConstantContact::get_instance();
?>

<div class="sui-modal sui-modal-sm">

	<div
		role="dialog"
		id="hustle-dialog-migrate--constantcontact"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="hustle-dialog-migrate--constantcontact-title"
		aria-describedby="hustle-dialog-migrate--constantcontact-description"
	>

		<div class="sui-box">

			<div class="sui-box-header sui-content-center sui-flatten sui-spacing-top--60">

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'hustle' ); ?></span>
				</button>

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_url( $cc_instance->get_logo_2x() ); ?>" alt="Constant Contact" />
				</figure>

				<h3 id="hustle-dialog-migrate--constantcontact-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Re-connect Constant Contact', 'hustle' ); ?></h3>
				<p id="hustle-dialog-migrate--constantcontact-description" class="sui-description"><?php esc_html_e( 'Click on the re-authenticate button below and authorize Hustle to retrieve access tokens for v3.0 API to update your integration to the latest API version.', 'hustle' ); ?></p>

			</div>
			<div class="sui-box-footer sui-flatten sui-content-center">
				<?php
					$api      = $cc_instance->static_api( Hustle_ConstantContact::AUTH_FLOW_PKCE );
					$auth_url = $api->get_authorization_uri( 0, true, Hustle_Data::INTEGRATIONS_PAGE );

				if ( $auth_url ) :
					?>
					<a id="integration-migrate" href="<?php echo esc_url( $auth_url ); ?>" class="hustle-constantcontact-migrate sui-button sui-button-blue" data-nonce="<?php echo esc_attr( wp_create_nonce( 'hustle_provider_action' ) ); ?>">
						<?php esc_html_e( 'Re-authorize now', 'hustle' ); ?>
					</a>
				<?php endif; ?>

			</div>

		</div>

	</div>

</div>
