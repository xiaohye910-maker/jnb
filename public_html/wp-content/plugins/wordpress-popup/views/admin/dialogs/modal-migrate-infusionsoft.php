<?php
/**
 * Modal for when migrating Aweber.
 *
 * @package Hustle
 * @since 4.1.1
 */

$infusionsoft = Hustle_Infusion_Soft::get_instance();
?>

<div class="sui-modal sui-modal-sm">

	<div
		role="dialog"
		id="hustle-dialog-migrate--infusionsoft"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="hustle-dialog-migrate--infusionsoft-title"
		aria-describedby="hustle-dialog-migrate--infusionsoft-description"
	>

		<div class="sui-box">

			<div class="sui-box-header sui-content-center sui-flatten sui-spacing-top--60">

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'hustle' ); ?></span>
				</button>

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_url( $infusionsoft->get_logo_2x() ); ?>" alt="Keap" />
				</figure>

				<h3 id="hustle-dialog-migrate--infusionsoft-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Migrate Keap', 'hustle' ); ?></h3>

				<?php $keys_link = '<a href="https://keys.developer.keap.com/">' . esc_html__( 'here', 'hustle' ) . '</a>'; ?>
				<?php /* translators: 1. infusionsoft developer dashboard */ ?>
				<p id="hustle-dialog-migrate--infusionsoft-description" class="sui-description"><?php printf( esc_html__( 'Re-authenticate your Hustle → Keap integration using OAuth2. Enter your Keap access tokens to update your integration. Get your API keys %s.', 'hustle' ), $keys_link );// phpcs:ignore ?></p>

			</div>

			<form class="sui-box-body sui-content-center sui-spacing-top--20">

				<div class="sui-form-field">

					<label for="infusionsoft-public-key" id="label-infusionsoft-public-key" class="sui-label">
					<?php esc_html_e( 'Client ID', 'hustle' ); ?>
					</label>

					<input
						id="infusionsoft"
						name="api_key"
					placeholder="<?php printf( esc_html__( 'Enter your client ID', 'hustle' ) ); ?>"
						class="sui-form-control"
						aria-labelledby="label-infusionsoft-public-key"
						aria-describedby="error-infusionsoft-public-key"
					/>

				<span id="error-infusionsoft-public-key" class="sui-error-message sui-hidden"><?php esc_html_e( 'Please enter a valid client ID', 'hustle' ); ?></span>

				</div>
				<div class="sui-form-field">

					<label for="infusionsoft-private-key" id="label-infusionsoft-private-key" class="sui-label">
						<?php esc_html_e( 'Private key', 'hustle' ); ?>
					</label>

					<input
						id="infusionsoft"
						name="private_key"
						placeholder="<?php printf( esc_html__( 'Enter your private key', 'hustle' ) ); ?>"
						class="sui-form-control"
						aria-labelledby="label-infusionsoft-private-key"
						aria-describedby="error-infusionsoft-private-key"
					/>

					<span id="error-infusionsoft-private-key" class="sui-error-message sui-hidden"><?php esc_html_e( 'Please enter a valid private key', 'hustle' ); ?></span>

				</div>

			</form>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<button
					id="integration-migrate"
					class="hustle-infusionsoft-migrate sui-button sui-button-blue hustle-provider-next hustle-onload-icon-action sui-button-center"
					data-id=""
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'hustle_provider_action' ) ); ?>"
				>
					<span class="sui-loading-text"><?php esc_html_e( 'Authorize', 'hustle' ); ?></span>
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
				</button>

			</div>

		</div>

	</div>

</div>
