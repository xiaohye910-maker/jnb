<?php
/**
 * Automated email section.
 *
 * @package Hustle
 * @since 4.0.0
 */

// Tinymce editor styles.
ob_start();
require Opt_In::$plugin_path . 'assets/css/sui-editor.min.css';
$editor_css = ob_get_clean();
$editor_css = '<style>' . $editor_css . '</style>';

// Schedule tab content.
ob_start();
?>

<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">

		<span class="sui-settings-label"><?php esc_html_e( 'Notification Email', 'hustle' ); ?></span>

		<span class="sui-description"><?php esc_html_e( "Send a notification email to the site admin after user's subscribed.", 'hustle' ); ?></span>

	</div>

	<div class="sui-box-settings-col-2">

		<div class="sui-form-field">

			<label for="hustle-notification-email" class="sui-toggle hustle-toggle-with-container" data-toggle-on="notification-email">
				<input type="checkbox"
					name="notification_email"
					data-attribute="notification_email"
					id="hustle-notification-email"
					aria-labelledby="hustle-notification-email-label"
					<?php checked( $settings['notification_email'], '1' ); ?>
				/>
				<span class="sui-toggle-slider" aria-hidden="true"></span>

				<span id="hustle-notification-email-label" class="sui-toggle-label"><?php esc_html_e( 'Send a notification email to the site admin', 'hustle' ); ?></span>
			</label>

			<div class="sui-border-frame sui-toggle-content" data-toggle-content="notification-email">

					<div class="sui-form-field">

						<label for="hustle-email-recipient" class="sui-label">
							<?php esc_html_e( 'Recipient', 'hustle' ); ?>
							<span class="sui-label-note"><?php esc_html_e( 'Separate multiple emails with a comma', 'hustle' ); ?></span>
						</label>

						<div class="sui-insert-variables">

							<input type="text"
								name="notification_email_recipient"
								value="<?php echo esc_attr( $settings['notification_email_recipient'] ); ?>"
								placeholder="Email {email-1}"
								id="hustle-email-recipient"
								class="sui-form-control"
								data-attribute="notification_email_recipient"
							/>

							<select
								class="sui-variables hustle-field-options hustle-select-variables"
								data-for="hustle-email-recipient"
								data-behavior="insert"
								data-type="email"
							></select>

						</div>

					</div>

					<div class="sui-form-field">

						<label for="hustle-notification-email-subject" class="sui-label"><?php esc_html_e( 'Subject', 'hustle' ); ?></label>

						<div class="sui-insert-variables">

							<input type="text"
								placeholder="<?php esc_html_e( 'Email copy subject', 'hustle' ); ?>"
								name="notification_email_subject"
								data-attribute="notification_email_subject"
								value="<?php echo esc_attr( $settings['notification_email_subject'] ); ?>"
								id="hustle-notification-email-subject"
								class="sui-form-control" />

							<select
								class="sui-variables hustle-field-options hustle-select-variables"
								data-for="hustle-notification-email-subject"
								data-behavior="insert"
							></select>

						</div>

					</div>

					<div class="sui-form-field">

						<label class="sui-label sui-label-editor"><?php esc_html_e( 'Email body', 'hustle' ); ?></label>

						<?php
						wp_editor(
							wp_kses_post( $settings['notification_email_body'] ),
							'notification_email_body',
							array(
								'media_buttons'    => false,
								'textarea_name'    => 'notification_email_body',
								'editor_css'       => $editor_css,
								'tinymce'          => array(
									'content_css' => self::$plugin_url . 'assets/css/sui-editor.min.css',
								),
								// remove more tag from text tab.
								'quicktags'        => $this->tinymce_quicktags,
								'editor_height'    => 192,
								'drag_drop_upload' => false,
							)
						);
						?>

					</div>

			</div>

		</div>

	</div>

</div>
