<?php
/**
 * The connection step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;

use Autoship\Core\Environment;
use Autoship\Core\FeatureManager;

$environment      = new Environment();
$wizard_site_name = $environment->get_site_name();

if ( ! FeatureManager::is_enabled( 'quicklaunch_account_register' ) ) {
	$wizard_sites = array(
		array(
			'id'   => '1',
			'name' => 'My Store 1',
		),
		array(
			'id'   => '2',
			'name' => 'My Store 2',
		),
		array(
			'id'   => '3',
			'name' => 'My Store 3',
		),
	);

	$wizard_sites_count = count( $wizard_sites );
} else {
	$wizard_sites       = get_option( 'autoship_quicklaunch_login_sites', array() );
	$wizard_sites_count = count( $wizard_sites );
}




?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-28">
		<?php echo esc_html( __( "Let's connect your store to", 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'QPilot', 'autoship' ) ); ?></span>
	</h1>

	<p class="autoship-font-sm">
		<?php echo esc_html( __( 'Autoship is powered by QPilot — the engine that runs your subscriptions logic.', 'autoship' ) ); ?>

		<?php echo esc_html( __( 'To get started, we will need to connect your store to QPilot.', 'autoship' ) ); ?>
		<br/>
		
		<?php echo esc_html( __( 'Please confirm your store name to continue.', 'autoship' ) ); ?>
	</p>
	<p class="autoship-mt-50  autoship-font-md">
		<strong>
		<?php echo esc_html( __( 'There are', 'autoship' ) ); ?>
		<strong class="autoship-highlighted-text"><?php echo esc_html( $wizard_sites_count ); ?></strong>
		<?php echo esc_html( __( 'stores associated with your account', 'autoship' ) ); ?>.
	</p>

	<div class="autoship-quicklaunch-content-step-fields">
		<div class="autoship-mb-20">
			<label for="autoship-connection-type" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'What would you like to do?', 'autoship' ) ); ?></label>
			<select required="required" name="autoship-connection-type" id="autoship-connection-type" class="autoship-quicklaunch-field-select">
				<option value="create" selected="selected"><?php echo esc_html( __( 'Create a new store on QPilot', 'autoship' ) ); ?></option>
				<option value="existing"><?php echo esc_html( __( 'Connect an existing store', 'autoship' ) ); ?></option>
			</select>
		</div>

		<div id="autoship-connection-sites-creator">
			<div class="autoship-mb-20">
				<label for="autoship-connection-store-name" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'Your site name', 'autoship' ) ); ?></label>
				<input type="text" maxlength="100" required="required" name="autoship-connection-store-name" id="autoship-connection-store-name" class="autoship-quicklaunch-field-text" placeholder="<?php echo esc_attr( __( 'Your site name', 'autoship' ) ); ?>" value="<?php echo esc_attr( $wizard_site_name ); ?>" />
			</div>
		</div>

		<div id="autoship-connection-sites-container" style="display:none;">
			<div class="autoship-mb-20">
				<label for="autoship-connection-site-id" class="autoship-quicklaunch-field-label"><?php echo esc_html( __( 'Your available sites', 'autoship' ) ); ?></label>
				<select required="required" name="autoship-connection-site-id" id="autoship-connection-site-id" class="autoship-quicklaunch-field-select">
					<option value="" disabled="disabled" selected="selected"><?php echo esc_html( __( 'Please choose one', 'autoship' ) ); ?></option>
					<?php foreach ( $wizard_sites as $site ) : ?>
					<option value="<?php echo esc_attr( $site['id'] ); ?>"><?php echo esc_html( $site['name'] ); ?></option>
					<?php endforeach; ?>

				</select>
			</div>
		</div>

		<div id="autoship-quicklaunch-form-errors" class="autoship-text-danger autoship-font-sm" style="text-align: center; min-height: 30px;"></div>

		<div class="autoship-quicklaunch-content-step-actions">
			<div class="autoship-mb-20">
				<button type="button" id="connection-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Continue', 'autoship' ) ); ?></button>
				<p class="autoship-font-16">
					<?php echo esc_html( __( 'Want to use another account?', 'autoship' ) ); ?>
					<a href="#" id="connection-back-button" class="autoship-quicklaunch-content-steps-action-link autoship-font-16"><?php echo esc_html( __( 'Sign out', 'autoship' ) ); ?></a>
				</p>
			</div>
		</div>
	</div>
</div>
