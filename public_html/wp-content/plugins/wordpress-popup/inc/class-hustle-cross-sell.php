<?php
/**
 * Hustle Cross Sell Class
 *
 * Set's and loads the Cross Sell sub module
 *
 * @package Hustle
 * @since 7.8.7
 */

/**
 * File for Hustle_Cross_Sell class.
 *
 * @package Hustle
 * @since 7.8.7
 */
class Hustle_Cross_Sell {

	/**
	 * Initiate sub module
	 *
	 * @since 7.8.7
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_cross_sell_module' ) );
	}

	/**
	 * Get the arguments used when rendering the main page.
	 *
	 * @since 7.8.7
	 * @return void
	 */
	public function register_cross_sell_module() {
		$cross_sell_path = Opt_In::$plugin_path . 'lib/plugins-cross-sell-page/plugin-cross-sell.php';
		if ( ! file_exists( $cross_sell_path ) ) {
			return;
		}
		static $cross_sell = null;
		if ( is_null( $cross_sell ) ) {
			if ( ! class_exists( '\WPMUDEV\Modules\Plugin_Cross_Sell' ) ) {
				require_once $cross_sell_path;
			}

			$submenu_params = array(
				'slug'            => 'wordpress-popup', // Required.
				'parent_slug'     => 'hustle', // Required.
				'capability'      => 'manage_options', // Optional.
				'menu_slug'       => 'hustle_cross_sell', // Optional - Strongly recommended to set in order to avoid admin page conflicts with other WPMU DEV plugins.
				'position'        => 13, // Optional – Usually a specific position will be required.
				'translation_dir' => dirname( Opt_In::$plugin_path ) . '/languages', // Optional – The directory where the translation files are located.
				// 'menu_hook_priority' => 99, // Optional – The priority of the menu hook.
			);

			$cross_sell = new \WPMUDEV\Modules\Plugin_Cross_Sell( $submenu_params );
		}
	}
}
