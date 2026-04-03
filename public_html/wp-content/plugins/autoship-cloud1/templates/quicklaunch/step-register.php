<?php
/**
 * The register capture step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

use Autoship\Core\FeatureManager;

defined( 'ABSPATH' ) || exit;

$wizard_user    = wp_get_current_user();
$username       = $wizard_user->user_login;
$email          = $wizard_user->user_email;
$phone_required = FeatureManager::is_enabled( 'quicklaunch_phone_required' );
?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-30"><?php echo esc_html( __( "Let's create your", 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'free', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'account', 'autoship' ) ); ?></h1>

	<p class="autoship-font-sm">
		<?php echo esc_html( __( 'Creating your Autoship account gives you the tools and support you need to scale your recurring revenue with confidence.', 'autoship' ) ); ?>
	</p>

	<div class="autoship-quicklaunch-content-step-fields">
		<div class="autoship-mb-20">
			<label for="autoship-registration-email" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'Email', 'autoship' ) ); ?>
			</label>
			<input type="email" maxlength="128" required="required" name="autoship-registration-email" id="autoship-registration-email" class="autoship-quicklaunch-field-text" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php echo esc_html( __( 'Your email address', 'autoship' ) ); ?>"/>
		</div>
		<div class="autoship-mb-20">
			<label for="autoship-registration-password" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'Password', 'autoship' ) ); ?>
			</label>
			<input type="password" minlength="10" maxlength="16" required="required" name="autoship-registration-password" id="autoship-registration-password" class="autoship-quicklaunch-field-text" placeholder="<?php echo esc_html( __( 'Your password', 'autoship' ) ); ?>" />
		</div>

		<div class="autoship-mb-20">
			<label for="autoship-registration-confirmation" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'Confirmation', 'autoship' ) ); ?>
			</label>
			<input type="password" minlength="10" maxlength="16" id="autoship-registration-confirmation" name="autoship-registration-confirmation" class="autoship-quicklaunch-field-text" placeholder="<?php echo esc_attr( __( 'Confirm your password', 'autoship' ) ); ?>"/>
		</div>

		<div class="autoship-mb-20">
			<div class="autoship-mb-20">
				<label id="autoship-registration-phone-local-label" for="autoship-registration-phone-local" class="autoship-quicklaunch-field-label">
					<?php echo esc_html( __( 'Phone Number', 'autoship' ) ); ?> 
					<?php if ( ! $phone_required ) : ?>
						<small><?php echo esc_html( __( '(optional)', 'autoship' ) ); ?></small>
					<?php endif; ?>
				</label>
				<input type="text" id="autoship-registration-phone-local" class="autoship-quicklaunch-field-text" placeholder="<?php echo esc_attr( __( 'Enter your phone number', 'autoship' ) ); ?>" inputmode="numeric" pattern="\d{7,15}" maxlength="15" />
			</div>
		</div>

		<div id="autoship-quicklaunch-form-errors" class="autoship-text-danger autoship-font-sm" style="text-align: center; min-height: 30px;"></div>

		<div class="autoship-quicklaunch-content-step-actions">
			<div class="autoship-mb-20">
				<button type="button" id="register-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Continue', 'autoship' ) ); ?></button>
				<p class="autoship-font-16">
					<?php echo esc_html( __( 'Already have an account?', 'autoship' ) ); ?>
					<a href="#" id="register-login-button" class="autoship-quicklaunch-content-steps-action-link autoship-font-16"><?php echo esc_html( __( 'Sign in', 'autoship' ) ); ?></a>
				</p>
			</div>
		</div>
	</div>
</div>
