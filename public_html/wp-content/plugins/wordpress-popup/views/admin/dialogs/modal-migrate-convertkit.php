<?php
/**
 * Modal for when migrating ConvertKit (Non-Hub users).
 *
 * @package Hustle
 * @since 4.6.0
 */

$ck_instance = Hustle_ConvertKit::get_instance();
?>

<div class="sui-modal sui-modal-sm">

	<div
		role="dialog"
		id="hustle-dialog-migrate--convertkit"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="hustle-dialog-migrate--convertkit-title"
		aria-describedby="hustle-dialog-migrate--convertkit-description"
	>

		<div class="sui-box">

			<div class="sui-box-header sui-content-center sui-flatten sui-spacing-top--60">

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'hustle' ); ?></span>
				</button>

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_url( $ck_instance->get_logo_2x() ); ?>" alt="Kit" />
				</figure>

				<h3 id="hustle-dialog-migrate--convertkit-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Migrate Kit', 'hustle' ); ?></h3>

				<?php
				$ck_developer_link = "<a href='https://app.kit.com/account_settings/developer_settings' target='_blank' rel='noopener noreferrer'>" . esc_html__( 'API Key', 'hustle' ) . '</a>';
				?>
				<?php /* translators: 1. Kit developer link */ ?>
				<p id="hustle-dialog-migrate--convertkit-description" class="sui-description"><?php printf( esc_html__( 'Re-authorize Hustle to use API V4 authentication for improved security and reliability. Get your %s.', 'hustle' ), $ck_developer_link ); // phpcs:ignore ?></p>

			</div>

			<form class="sui-box-body sui-content-center sui-spacing-top--20">

				<div class="sui-form-field" style="text-align: left;">

					<label for="convertkit-api-key" id="label-convertkit-api-key" class="sui-label">
						<?php esc_html_e( 'API Key', 'hustle' ); ?>
					</label>

					<input
						id="convertkit-api-key"
						name="api_key"
						placeholder="<?php printf( esc_html__( 'Enter your API Key', 'hustle' ) ); ?>"
						class="sui-form-control"
						aria-labelledby="label-convertkit-api-key"
						aria-describedby="error-convertkit-api-key"
					/>

					<span id="error-convertkit-api-key" class="sui-error-message sui-hidden"><?php esc_html_e( 'Please enter a valid API Key', 'hustle' ); ?></span>

				</div>

			</form>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<button
					id="integration-migrate"
					class="hustle-convertkit-migrate sui-button sui-button-blue hustle-provider-next hustle-onload-icon-action sui-button-center"
					data-id=""
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'hustle_provider_action' ) ); ?>"
				>
					<span class="sui-loading-text"><?php esc_html_e( 'Authorize', 'hustle' ); ?></span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>

			</div>

		</div>

	</div>

</div>
