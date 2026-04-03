<?php
/**
 * Autoship Cloud for WooCommerce
 *
 * @package Autoship
 *
 * Plugin Name: Autoship Cloud powered by QPilot
 * Plugin URI: https://autoship.cloud
 * Description: Autoship Cloud for WooCommerce
 * Version: 2.12.3
 * Author: Patterns In the Cloud LLC
 * Author URI: https://qpilot.cloud
 * Text Domain: autoship
 * Domain Path: /languages
 * WC requires at least: 3.4.1
 * WC tested up to: 10.4.3
 */

define( 'Autoship_Version', '2.12.3' ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase

if ( ! defined( 'Autoship_Plugin_Dir' ) ) {
	define( 'Autoship_Plugin_Dir', __DIR__ ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
}

if ( ! defined( 'Autoship_Plugin_File' ) ) {
	define( 'Autoship_Plugin_File', __FILE__ ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
}

if ( ! defined( 'Autoship_Options_Count' ) ) {
	define( 'Autoship_Options_Count', 5 ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
}

if ( ! defined( 'Autoship_Plugin_Folder_Name' ) ) {
	define( 'Autoship_Plugin_Folder_Name', 'autoship-cloud' ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
}

if ( ! defined( 'Autoship_Plugin_Url' ) ) {
	define( 'Autoship_Plugin_Url', plugin_dir_url( __FILE__ ) ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
}

// Include Composer autoloader.
$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
}


// This class is loaded here since it must exist before calling the autoship init.
require_once 'app/Core/Plugin.php';

/**
 * Activate the plugin.
 */
function autoship_activate() {
	// Set the flush rewrite rules if not set.
	if ( ! get_option( 'autoship_flush_rewrite_rules_flag' ) ) {
		add_option( 'autoship_flush_rewrite_rules_flag', true );
	}

	if ( ! get_option( 'autoship_first_activation' ) ) {
		update_option( 'autoship_first_activation', true );
	}

	Autoship\Core\Plugin::activate();
}

register_activation_hook( __FILE__, 'autoship_activate' );


/**
 * Deactivate the plugin.
 */
function autoship_deactivate() {
	// Flush the Rewrite rules on deactivation.
	flush_rewrite_rules();

	Autoship\Core\Plugin::deactivate();
}

register_deactivation_hook( __FILE__, 'autoship_deactivate' );


/**
 * Uninstall the plugin.
 */
function autoship_uninstall() {
	// Flush the Rewrite rules on uninstallation.
	flush_rewrite_rules();

	Autoship\Core\Plugin::uninstall();
}
register_uninstall_hook( __FILE__, 'autoship_uninstall' );

/**
 * Show action links on the plugin screen.
 *
 * @param mixed $links Plugin Action links.
 *
 * @return array
 */
function autoship_plugin_action_links( $links ): array {
	$action_links = array(
		'connection' => '<a href="' . admin_url( 'admin.php?page=autoship' ) . '" aria-label="' . esc_attr__( 'View Autoship Connection', 'autoship' ) . '">' . esc_html__( 'Connection', 'autoship' ) . '</a>',
		'options'    => '<a href="' . admin_url( 'admin.php?page=autoship&tab=autoship-options' ) . '" aria-label="' . esc_attr__( 'View Autoship Options', 'autoship' ) . '">' . esc_html__( 'Options', 'autoship' ) . '</a>',
		'utilities'  => '<a href="' . admin_url( 'admin.php?page=autoship&tab=autoship-utilities' ) . '" aria-label="' . esc_attr__( 'View Autoship Utilities', 'autoship' ) . '">' . esc_html__( 'Utilities', 'autoship' ) . '</a>',
		'support'    => '<a href="https://support.autoship.cloud/?utm_source=Autoship+Cloud+Plugin&utm_medium=WP-Admin+Plugins" aria-label="' . esc_attr__( 'View Autoship Support', 'autoship' ) . '" target="_blank">' . esc_html__( 'Support', 'autoship' ) . '</a>',
	);

	return array_merge( $action_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'autoship_plugin_action_links' );

/**
 * Add Admin Notice on Failed Requirements
 */
function autoship_requirement_notice() {
	$message = __( 'Autoship Cloud Requires WooCommerce to be installed and active. Please activate WooCommerce in order to use Autoship Cloud.', 'autoship' );

	echo wp_kses_post( "<div class=\"asc-notice notice notice-error\"><p><strong>$message</strong></p></div>" );
}

/**
 * Add Admin Notice on Failed Payment Requirements
 */
function autoship_payment_requirement_notice() {

	// If the Quicklaunch feature is enabled, we don't want to show the notice since when the quicklaunch is not completed yet.
	$features = Autoship\Core\Plugin::get_service_container()->get( Autoship\Core\FeatureManagerInterface::class );
	if ( $features->is_enabled( 'quicklaunch' ) ) {
		$installed = get_option( 'autoship_quicklaunch_completed', false );
		if ( ! $installed ) {
			return;
		}
	}

	// translators: %s is the URL to the Autoship Cloud supported payment gateways documentation.
	$message = sprintf( __( '<h3>No Autoship Payment Gateways Enabled</h3><p><strong>Autoship Cloud requires one or more supported WooCommerce Payment Gateways to be installed and active in order for customers to be able to schedule products for autoship. Please see a list of Autoship Cloud supported payment gateways <a href="%s">here</a>.</strong></p>', 'autoship' ), 'https://support.autoship.cloud/article/1002-payment-integrations' );

	echo wp_kses_post( "<div class=\"asc-notice notice notice-error\">$message</div>" );
}

/**
 * Check minimum requirements to use Autoship.
 */
function autoship_check_min_requirements(): bool {
	// Get the Active Plugins.
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	// Check that WooCommerce is installed and active.
	if ( ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, 3.2, '>=' ) ) {
		return true;
	}

	add_action( 'admin_notices', 'autoship_requirement_notice' );

	return false;
}

/**
 * Loads the Core Language file.
 */
function autoship_load_languages() {
	$plugin_rel_path = basename( Autoship_Plugin_Dir ) . '/languages';

	load_plugin_textdomain( 'autoship', false, $plugin_rel_path );
}

/**
 * Loads the Core Files
 */
function autoship_load_includes() {
	require_once 'src/QPilot/Client.php';
	require_once 'src/legacy/logger.php';

	require_once 'src/admin.php';
	require_once 'src/utilities.php';
	require_once 'src/api.php';
	require_once 'src/api-wc.php';
	require_once 'src/legacy/healthchecks.php';
	require_once 'src/orders.php';
	require_once 'src/checkout.php';
	require_once 'src/cart.php';
	require_once 'src/coupons.php';
	require_once 'src/products.php';
	require_once 'src/product-page.php';
	require_once 'src/payments.php';
	require_once 'src/customers.php';
	require_once 'src/scripts.php';
	require_once 'src/ajax.php';
	require_once 'src/shortcodes.php';
	require_once 'src/pages.php';
	require_once 'src/legacy/languages.php';

	require_once 'src/bulk.php';
	require_once 'src/scheduled-orders.php';
	require_once 'src/shipping.php';
	require_once 'src/free-shipping.php';
	require_once 'src/legacy/deprecated.php';
	require_once 'src/wholesale-pricing.php';
}

/**
 * Declare compatibility with WooCommerce HPOS and WooCommerce Blocks.
 * https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
 */
function autoship_woocommerce_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
}

add_action( 'before_woocommerce_init', 'autoship_woocommerce_hpos_compatibility' );

/**
 * Start it up.
 */
function autoship_init() {

	// While we allow Autoship to be active when requirements fail, no functionality is included.
	if ( ! autoship_check_min_requirements() ) {
		return;
	}

	autoship_load_includes();
	autoship_load_languages();

	// Initialize the new architecture if autoloader exists.
	Autoship\Core\Plugin::run();

	$features = Autoship\Core\Plugin::get_service_container()->get( Autoship\Core\FeatureManagerInterface::class );
	if ( $features->is_enabled( 'quicklaunch' ) ) {
		if ( get_option( 'autoship_first_activation' ) ) {
			delete_option( 'autoship_first_activation' );

			if ( ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Verify that the quicklaunch must be redirected or not.
				$redirect = Autoship\Modules\Quicklaunch\QuicklaunchModule::must_redirect_on_activation();
				if ( $redirect ) {
					wp_safe_redirect( admin_url( 'admin.php?page=quicklaunch' ) );
					exit;
				}
			}
		}
	}
}

add_action( 'plugins_loaded', 'autoship_init' );

if ( function_exists( 'autoship_confirm_valid_payment_gateways' ) ) {
	add_action( 'woocommerce_init', 'autoship_confirm_valid_payment_gateways', 99 );
}
