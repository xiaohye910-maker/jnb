<?php
/**
 * Opt_In Entry point
 *
 * @package Hustle
 */

/**
 * Opt_In class.
 */
class Opt_In {

	const VERSION = '7.8.7';

	const VIEWS_FOLDER = 'views';

	/**
	 * Base file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $plugin_base_file;

	/**
	 * Plugin URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $plugin_url;

	/**
	 * Plugin path.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $plugin_path;

	/**
	 * Path to "vendor".
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $vendor_path;

	/**
	 * Path to "views" files.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $template_path;

	/**
	 * Array container for the registered providers.
	 *
	 * @since 3.0.5
	 * @var array
	 */
	protected static $registered_providers = array();

	/**
	 * Opt_In constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$plugin_base_file = plugin_basename( HUSTLE_BASE_FILE );
		self::$plugin_url       = plugin_dir_url( self::$plugin_base_file );
		self::$plugin_path      = trailingslashit( dirname( HUSTLE_BASE_FILE ) );
		self::$vendor_path      = self::$plugin_path . 'vendor/';
		self::$template_path    = trailingslashit( dirname( HUSTLE_BASE_FILE ) ) . 'views/';

		add_action( 'after_setup_theme', array( $this, 'load_text_domain' ) );

		// check caps.
		add_action( 'admin_init', array( $this, 'hustle_check_caps' ), 999 );

		new Hustle_Init();
	}

	/**
	 * Returns list of optin providers based on their declared classes that implement Opt_In_Provider_Interface
	 *
	 * @return array
	 */
	public function get_providers() {
		if ( empty( self::$registered_providers ) ) {
			self::$registered_providers = Hustle_Provider_Utils::get_activable_providers_list();
		}
		return self::$registered_providers;
	}

	/**
	 * Loads text domain
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'hustle', false, dirname( plugin_basename( self::$plugin_base_file ) ) . '/languages/' );
	}

	/**
	 * Callback function when user migrates from 3x to 4x from ftp.
	 * The activation hook won't run we'd have to check it in init.
	 *
	 * @since 4.0.0
	 */
	public function hustle_check_caps() {
		$admin = get_role( 'administrator' );
		$roles = get_editable_roles();
		if ( ( $admin && ! $admin->has_cap( 'hustle_menu' ) ) || ( ! $admin && ! empty( $roles ) ) ) {
			hustle_activation();
		}
	}
}
