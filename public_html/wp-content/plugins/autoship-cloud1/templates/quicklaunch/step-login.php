<?php
/**
 * The login step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;

$wizard_user = wp_get_current_user();
$username    = $wizard_user->user_login;
$email       = $wizard_user->user_email;

?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-28"><?php echo esc_html( __( 'Sign in to your' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'Autoship', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'account', 'autoship' ) ); ?></h1>
	
	<p class="autoship-font-sm">
		<?php echo esc_html( __( 'Log in to your Autoship account and take control of your recurring revenue strategy.', 'autoship' ) ); ?>
	</p>

	<div class="autoship-quicklaunch-content-step-fields">
		<div class="autoship-mb-20">
			<label for="autoship-login-email" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'Email', 'autoship' ) ); ?>
			</label>
			<input type="email" maxlength="128" required="required" name="autoship-login-email" id="autoship-login-email" class="autoship-quicklaunch-field-text" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php echo esc_html( __( 'Your email address', 'autoship' ) ); ?>"/>
		</div>

		<div class="autoship-mb-20">
			<label for="autoship-login-password" class="autoship-quicklaunch-field-label">
				<?php echo esc_html( __( 'Password', 'autoship' ) ); ?>
			</label>
			<input type="password" minlength="10" maxlength="16" required="required" name="autoship-login-password" id="autoship-login-password" class="autoship-quicklaunch-field-text" placeholder="<?php echo esc_html( __( 'Your password', 'autoship' ) ); ?>" />
		</div>

		<div id="autoship-quicklaunch-form-errors" class="autoship-text-danger autoship-font-sm" style="text-align: center; min-height: 30px;"></div>

		<div class="autoship-quicklaunch-content-step-actions">
			<div class="autoship-mb-20">
				<button type="button" id="login-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Continue', 'autoship' ) ); ?></button>

				<p class="autoship-font-16">
					<?php echo esc_html( __( "Don't have an account?", 'autoship' ) ); ?>
					<a href="#" id="login-register-button" class="autoship-quicklaunch-content-steps-action-link autoship-font-16"><?php echo esc_html( __( 'Create an account', 'autoship' ) ); ?></a>
				</p>
			</div>
			<!--<button type="button" id="login-back-button" class="button button-hero">Back</button>-->
			<!--<button type="button" id="login-next-button" class="button button-primary button-hero" disabled>Continue</button>-->
		</div>
	</div>
</div>