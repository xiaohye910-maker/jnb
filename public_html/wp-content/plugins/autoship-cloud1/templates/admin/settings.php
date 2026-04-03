<?php
/**
 * This template is used to display the Autoship Cloud Settings in the admin area.
 *
 * @package Autoship
 * @since 1.0.0
 */

?>

<?php
$site_parameters     = autoship_get_site_parameters();
$subscription_status = autoship_get_subscription_status();
$autoship_settings   = autoship_get_settings_fields();

// Get the current settings page tabs and associated callback functions.
$tabs       = autoship_settings_tabs(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$active_tab = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? $_GET['tab'] : apply_filters( 'autoship_admin_settings_default_tab', key( $tabs ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

?>

<div id="asc-settings" class="wrap">
	<h1>
		<?php echo esc_html( __( 'Autoship Cloud powered by', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/768-qpilot-account" target="_blank" style="text-decoration: none"></span><?php echo wp_kses_post( __( 'QPilot&trade;', 'autoship' ) ); ?></a>
	</h1>
	<p>
		<a href="https://merchants.qpilot.cloud/login/register?utm_source=AutoshipCloudPlugin&utm_medium=Settings&utm_campaign=Autoship_Cloud_Plugin" target="_blank">
			<?php echo esc_html( __( 'Create a free QPilot Merchant Account', 'autoship' ) ); ?>
		</a>
		<?php echo esc_html( __( ' to get started with Autoship Cloud.', 'autoship' ) ); ?>
		<br/>
		<?php echo esc_html( __( 'Need help?', 'autoship' ) ); ?><a href="https://support.autoship.cloud/" target="_blank"><?php echo esc_html( __( 'Click here for Autoship Cloud Online Support.', 'autoship' ) ); ?>
	</a>

	<?php if ( autoship_rights_checker( 'autoship_cloud_main_page_options_security', array( 'administrator' ) ) ) : ?>

		<?php if ( 'None' === $subscription_status ) : ?>
		<div class="subscription-status-none">
			<?php
			$merchant_url = esc_attr( autoship_get_merchants_url() );

			// translators: %s is the URL to the QPilot Merchant Center.
			echo wp_kses_post( sprintf( __( "Your subscription is not active. Log in to the <a href='%s'>QPilot Merchant Center</a> to activate your subscription.", 'autoship' ), $merchant_url ) );
			?>
		</div>
		<?php endif; ?>

		<?php do_action( 'autoship_after_admin_settings_header', $autoship_settings, $active_tab, $tabs ); ?>

		<h2 class="nav-tab-wrapper">

		<?php foreach ( $tabs as $item => $values ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=autoship&tab=' . $item ) ); ?>" class="nav-tab <?php echo esc_attr( $values['link_class'] ); ?> <?php echo $active_tab === $item ? 'nav-tab-active' : ''; ?>"><?php echo wp_kses_post( $values['label'] ); ?></a>
		<?php endforeach; ?>

		</h2>

		<form method="post" action="options.php" autocomplete="off">

			<?php
			settings_fields( 'autoship-settings-group' );
			do_settings_sections( 'autoship-settings-group' );
			$autoship_user = get_current_user_id();
			$user_meta     = get_userdata( $autoship_user );
			?>

			<input type="hidden" id="uemail" value="<?php echo esc_attr( $user_meta->user_email ); ?>">

			<?php foreach ( $tabs as $item => $values ) : ?>

			<div id="<?php echo esc_attr( $item ); ?>" class="wrap" style="display:<?php echo esc_attr( $active_tab === $item ? 'block' : 'none' ); ?>;">
				<?php
				$function = $values['callback'];
				?>
				<?php if ( is_array( $function ) ) : ?>
					<?php call_user_func( $function, $autoship_settings ); ?>
				<?php elseif ( function_exists( $function ) ) : ?>
					<?php $function( $autoship_settings ); ?>
				<?php endif; ?>
			</div>

			<?php endforeach; ?>

			<?php if ( apply_filters( 'autoship_admin_settings_tab_include_submit', true, $active_tab ) ) : ?>
				<?php submit_button( 'Update' ); ?>
			<?php endif; ?>
		</form>
	<?php endif; ?>
</div>
