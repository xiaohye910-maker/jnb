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
					<img src="<?php echo esc_url( $infusionsoft->get_logo_2x() ); ?>" alt="Infusionsoft" />
				</figure>

				<h3 id="hustle-dialog-migrate--infusionsoft-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Migrate Keap', 'hustle' ); ?></h3>
				<p id="hustle-dialog-migrate--infusionsoft-description" class="sui-description"><?php esc_html_e( "The latest InfusionSoft integration works with OAuth instead of API keys, and you need to authenticate your InfusionSoft account using the button below. Note that you'll be taken to the Infusionsoft website to grant access to Hustle and then redirected back to complete the migration.", 'hustle' ); ?></p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<?php
				$keap_auth = $infusionsoft->get_oauth();
				$auth_url  = $keap_auth->get_authorization_uri( 0, true, Hustle_Data::INTEGRATIONS_PAGE );
				?>
				<a
					href="<?php echo esc_url( $auth_url ); ?>"
					id="integration-migrate"
					class="hustle-infusionsoft-migrate sui-button"
					data-id=""
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'hustle_provider_action' ) ); ?>"
				>
					<span class="sui-loading-text"><?php esc_html_e( 'Authenticate', 'hustle' ); ?></span>
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
				</a>

			</div>

		</div>

	</div>

</div>
