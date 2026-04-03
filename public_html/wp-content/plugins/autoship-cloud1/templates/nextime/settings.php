<?php
/**
 * The template that is shown in the Autoship Settings for the Nextime Integration.
 * Author: Patterns In the Cloud LLC
 * Author URI: https://qpilot.cloud
 *
 * @package Autoship
 * @subpackage Nextime
 */

if ( ! isset( $autoship_settings ) ) {
	$autoship_settings = array();
}

?>
	<h2><?php echo esc_html( __( 'Advanced Shipping "Nextime" Settings', 'autoship' ) ); ?></h2>
	<p class="autoship-nextime-description"><?php echo esc_html( __( 'These are the global settings for the Nextime integration. Please configure this settings to enable the Nextime Shipping Method.', 'autoship' ) ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><?php echo esc_html( __( 'Nextime API Key', 'autoship' ) ); ?></th>
			<td>
				<input type="text" id="autoship_nextime_api_key" name="autoship_nextime_api_key" value="<?php echo esc_attr( $autoship_settings['autoship_nextime_api_key'] ); ?>" placeholder="Ex. E44ABAB9-2D97-42EB-AA6C-61D293C4964C" />
				<p class="help-text-wrapper"><label for="autoship_nextime_api_key"><?php echo esc_html( __( 'Enter the API key for your Nextime subscription.', 'autoship' ) ); ?></label></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html( __( 'Nextime Site ID', 'autoship' ) ); ?></th>
			<td>
				<input type="text" id="autoship_nextime_site_id" name="autoship_nextime_site_id" value="<?php echo esc_attr( $autoship_settings['autoship_nextime_site_id'] ); ?>" placeholder="Ex. 1234" />
				<p class="help-text-wrapper"><label for="autoship_nextime_site_id"><?php echo esc_html( __( 'Enter the Site ID from the Merchant Center.', 'autoship' ) ); ?></label></p>
			</td>
		</tr>
	</table>
<?php
