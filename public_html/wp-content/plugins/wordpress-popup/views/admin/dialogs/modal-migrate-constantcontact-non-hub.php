<?php
/**
 * Modal for when migrating Aweber.
 *
 * @package Hustle
 * @since 4.1.1
 */

$redirect_uri = '';

$api = Hustle_ConstantContact::static_api( Hustle_ConstantContact::AUTH_FLOW_PKCE_CUSTOM );
if ( ! is_wp_error( $api ) ) {
	$redirect_uri = $api->get_redirect_uri();
}

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

				<h3 id="hustle-dialog-migrate--constantcontact-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Migrate Constant Contact', 'hustle' ); ?></h3>

				<?php
				$cc_developer_link = "<a href='https://developer.constantcontact.com/' target='_blank' rel='noopener noreferrer'>" . esc_html__( 'PKCE key here', 'hustle' ) . '</a>';
				?>
				<?php /* translators: 1. Constant Contact developer link */ ?>
				<p id="hustle-dialog-migrate--constantcontact-description" class="sui-description"><?php printf( esc_html__( 'Re-authorize Hustle to retrieve new access tokens for the v3 API and update your integration to the latest version. Get your %s.', 'hustle' ), $cc_developer_link ); // phpcs:ignore ?></p>

			</div>

			<form class="sui-box-body sui-content-center sui-spacing-top--20">
				<div class="sui-form-field" style="text-align: left;">
					<label for="constantcontact-redirect-uri" id="label-constantcontact-redirect-uri" class="sui-label">
						<?php esc_html_e( 'Redirect URI', 'hustle' ); ?>
					</label>
					<div class="sui-with-button sui-with-button-inside">
						<input
							id="constantcontact-redirect-uri"
							type="text"
							value="<?php echo esc_attr( $redirect_uri ); ?>"
							class="sui-form-control"
							readonly
						/>
						<button class="sui-button-icon sui-copy-button hustle-copy-shortcode-button" type="button" aria-label="<?php esc_attr_e( 'Copy to clipboard', 'hustle' ); ?>">
							<span class="sui-icon-copy" aria-hidden="true"></span>
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Copy to clipboard', 'hustle' ); ?></span>
						</button>
					</div>
					<p class="sui-description"><?php esc_html_e( 'Add this URL as the redirect / callback URL in your Constant Contact app settings.', 'hustle' ); ?></p>
				</div>
				<div class="sui-form-field">

					<label for="reuth-constantcontact" id="label-reuth-constantcontact" class="sui-label">
						<?php esc_html_e( 'PKCE Key', 'hustle' ); ?>
					</label>

					<input
						id="reuth-constantcontact"
						name="api_key"
						placeholder="<?php printf( esc_html__( 'Enter your PKCE key', 'hustle' ) ); ?>"
						class="sui-form-control"
						aria-labelledby="label-reuth-constantcontact"
						aria-describedby="error-reuth-constantcontact"
					/>

					<span id="error-reuth-constantcontact" class="sui-error-message sui-hidden"><?php esc_html_e( 'Please enter a valid Constant Code PKCE key', 'hustle' ); ?></span>

				</div>

			</form>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<a
					href="#"
					id="integration-migrate"
					class="hustle-constantcontact-migrate sui-button sui-button-blue"
					data-id=""
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'hustle_provider_action' ) ); ?>"
				>
					<span class="sui-loading-text"><?php esc_html_e( 'Authorize', 'hustle' ); ?></span>
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
				</a>

			</div>

		</div>

	</div>

</div>
