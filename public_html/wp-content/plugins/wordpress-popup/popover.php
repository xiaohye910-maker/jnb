<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle plugin.
 *
 * @link              http://wpmudev.com/projects/hustle/
 * @since             1.0.0
 * @package           Hustle
 *
 * @wordpress-plugin
 * Plugin Name: Hustle
 * Plugin URI: https://wordpress.org/plugins/wordpress-popup/
 * Description: Start collecting email addresses and quickly grow your mailing list with big bold pop-ups, slide-ins, widgets, or in post opt-in forms.
 * Version: 7.8.12.1
 * Author: WPMU DEV
 * Author URI: https://wpmudev.com
 * Tested up to: 6.9
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: hustle
 * 
 */

// +----------------------------------------------------------------------+
// | Copyright Incsub (http://incsub.com/)                                |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+

if ( ! defined( 'HUSTLE_MIN_PHP_VERSION' ) ) {
	define( 'HUSTLE_MIN_PHP_VERSION', '7.4' );
}

if ( ! defined( 'HUSTLE_BASE_FILE' ) ) {
	define( 'HUSTLE_BASE_FILE', __FILE__ );
}

if ( ! function_exists( 'hustle_insecure_php_version_notice' ) ) {
	/**
	 * Display admin notice, if the site is using unsupported PHP version.
	 */
	function hustle_insecure_php_version_notice() {

		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					wp_kses( /* translators: %1$s - URL to an article about our hosting benefits. */
						__( 'Your site is running an outdated version of PHP that is no longer supported or receiving security updates. Please update PHP to at least version %1$s at your current hosting provider in order to activate Hustle, or consider switching to <a href="%2$s" target="_blank" rel="noopener noreferrer">WPMU DEV Hosting</a>.', 'hustle' ),
						array(
							'a'      => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
							'strong' => array(),
						)
					),
					esc_html( HUSTLE_MIN_PHP_VERSION ),
					'https://wpmudev.com/hosting/'
				);
				?>
			</p>
		</div>

		<?php

		// In case this is on plugin activation.
		if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
			unset( $_GET['activate'] ); //phpcs:ignore
		}
	}
}

/**
 * Display admin notice and prevent plugin code execution, if the server is
 * using old/insecure PHP version.
 */
if ( version_compare( phpversion(), HUSTLE_MIN_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'hustle_insecure_php_version_notice' );

	return;
}


add_action( 'activated_plugin', 'hustle_activated', 10, 2 );

if ( ! function_exists( 'hustle_activated' ) ) {

	/**
	 * Handles the deactivation of the free version if "pro" is active, and activation flags.
	 *
	 * @since unknown
	 *
	 * @param string $plugin             Path to the plugin file relative to the plugins directory.
	 * @param bool   $network_activation Whether to enable the plugin for all sites in the network or just the current site.
	 */
	function hustle_activated( $plugin, $network_activation ) {

		if ( is_plugin_active( 'hustle/opt-in.php' ) && is_plugin_active( 'wordpress-popup/popover.php' ) ) {

			// deactivate free version.
			deactivate_plugins( 'wordpress-popup/popover.php' );

			if ( 'hustle/opt-in.php' === $plugin ) {
				// Store in database about free version deactivated, in order to show a notice on page load.
				update_site_option( 'hustle_free_deactivated', 1 );
			} elseif ( 'wordpress-popup/popover.php' === $plugin ) {
				// Store in database about free version being activated even pro is already active.
				update_site_option( 'hustle_free_activated', 1 );
			}
		}
	}
}

// Require autoloader.
if ( ! class_exists( 'ComposerAutoloaderInitd45a15be3ceca75ee1c0c2f87d2b07c1' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! defined( 'HUSTLE_SUI_VERSION' ) ) {
	define( 'HUSTLE_SUI_VERSION', '2.12.25' );
}

if ( ! class_exists( 'Opt_In' ) ) {
	require_once __DIR__ . '/inc/class-opt-in.php';
}

if ( ! function_exists( 'hustle_init' ) ) {

	/**
	 * Instantiate Opt_In
	 */
	function hustle_init() {
		new Opt_In();
	}

	add_action( 'after_setup_theme', 'hustle_init' );
}

if ( ! function_exists( 'hustle_activation' ) ) {

	/**
	 * Handle tables creating if needed.
	 *
	 * @since unknown
	 */
	function hustle_activation() {

		if ( ! class_exists( 'Hustle_Db' ) ) {
			require_once trailingslashit( __DIR__ ) . 'inc/hustle-db.php';
		}
		update_option( 'hustle_activated_flag', 1 );

		Hustle_Db::maybe_create_tables( true );

		/**
		 * Add Hustle's custom capabilities.
		 *
		 * @since 4.0.1
		 */
		$hustle_capabilities = array(
			'hustle_menu',
			'hustle_edit_module',
			'hustle_create',
			'hustle_edit_integrations',
			'hustle_access_emails',
			'hustle_edit_settings',
			'hustle_analytics',
		);

		$admin = get_role( 'administrator' );

		if ( $admin ) {
			// If there's an "administrator" role.
			foreach ( $hustle_capabilities as $cap ) {
				$admin->add_cap( $cap );
			}
		} else {
			// If there's no "administrator".
			$roles = get_editable_roles();

			foreach ( $roles as $role_name => $data ) {

				// Add the capabilities to anyone who can manage options. This was the checked capability in 3.x.
				if ( isset( $data['capabilities']['manage_options'] ) && $data['capabilities']['manage_options'] ) {

					$role = get_role( $role_name );
					foreach ( $hustle_capabilities as $cap ) {
						if ( $role ) {
							$role->add_cap( $cap );
						}
					}
				}
			}
		}
	}
}
register_activation_hook( __FILE__, 'hustle_activation' );


if ( ! function_exists( 'hustle_deactivation' ) ) {
	/**
	 * On deactivation hook
	 *
	 * @since 4.2.0
	 */
	function hustle_deactivation() {
		// Remove the cron for data protection cleanup.
		wp_clear_scheduled_hook( 'hustle_general_data_protection_cleanup' );
		wp_clear_scheduled_hook( 'hustle_background_conversion_log_cron' );
	}
}

register_deactivation_hook( __FILE__, 'hustle_deactivation' );
