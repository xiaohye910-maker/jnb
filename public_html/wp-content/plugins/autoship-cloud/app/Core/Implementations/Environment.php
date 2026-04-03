<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The environment utility class.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\EnvironmentInterface;

/**
 * Autoship_Utilities_Environment class.
 *
 * This class provides utility functions to retrieve information about the current environment,
 * including user details, plugin version, and system information.
 *
 * @package Autoship
 */
class Environment implements EnvironmentInterface {

	/**
	 * The current user login.
	 *
	 * @var string
	 */
	private string $login_account = '';

	/**
	 * The current user display name.
	 *
	 * @var string
	 */
	private string $login_name = '';

	/**
	 * The current user email.
	 *
	 * @var string
	 */
	private string $login_email;

	/**
	 * The current WordPress version.
	 *
	 * @var string
	 */
	private string $wp_language;

	/**
	 * The current WordPress version.
	 *
	 * @var string
	 */
	private string $wp_version;

	/**
	 * The current WooCommerce version.
	 *
	 * @var string
	 */
	private string $wc_version;

	/**
	 * The current php version.
	 *
	 * @var string
	 */
	private string $php_version;

	/**
	 * The current WooCommerce country.
	 *
	 * @var string
	 */
	private string $wc_country;

	/**
	 * The site URL.
	 *
	 * @var string
	 */
	private string $site_url;

	/**
	 * The site Name.
	 *
	 * @var string
	 */
	private string $site_name;

	/**
	 * The site URL.
	 *
	 * @var string
	 */
	private string $admin_email;

	/**
	 * The QPilot API URL.
	 *
	 * @var string
	 */
	private string $api_url;

	/**
	 * The QPilot Merchant Center URL.
	 *
	 * @var string
	 */
	private string $qmc_url;

	/**
	 * The constructor.
	 */
	public function __construct() {
		if ( is_user_logged_in() ) {
			// Get the current username.
			$current_user = wp_get_current_user();

			$this->login_account = $current_user->user_login;
			$this->login_name    = $current_user->display_name;
			$this->login_email   = $current_user->user_email;
		}

		$this->php_version = PHP_VERSION;
		$this->wp_version  = wp_get_wp_version();
		$this->wp_language = get_locale();

		if ( defined( 'WC_VERSION' ) ) {
			$this->wc_version = WC_VERSION;
		} else {
			$this->wc_version = WC()->version;
		}

		$this->wc_country  = get_option( 'woocommerce_default_country' );
		$this->site_url    = get_site_url();
		$this->admin_email = get_bloginfo( 'admin_email' );
		$this->site_name   = get_bloginfo( 'name' );

		// Get the API URL - using apply_filters directly to avoid legacy function dependency.
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$this->api_url = apply_filters( 'autoship-api-url', 'https://api.qpilot.cloud' );
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$this->qmc_url = apply_filters( 'autoship-merchants-url', 'https://merchants.qpilot.cloud' );
	}

	/**
	 * Returns the current environment.
	 *
	 * @return string The current environment.
	 */
	public function get_current_user_name(): string {
		return $this->login_name;
	}

	/**
	 * Get the current user email.
	 *
	 * @return string The current user email.
	 */
	public function get_current_user_email(): string {
		return $this->login_email;
	}

	/**
	 * Get the current user login.
	 *
	 * @return string The current user login.
	 */
	public function get_current_user_login(): string {
		return $this->login_account;
	}

	/**
	 * Gets the current Autoship version.
	 *
	 * @return string The current Autoship version.
	 */
	public function get_autoship_version(): string {
		return Autoship_Version;
	}

	/**
	 * Gets the current WordPress locale.
	 *
	 * @return string The current WordPress locale.
	 */
	public function get_wordpress_language(): string {
		return $this->wp_language;
	}

	/**
	 * Gets the current WooCommerce version.
	 *
	 * @return string The current WooCommerce version.
	 */
	public function get_woocommerce_version(): string {
		return $this->wc_version;
	}

	/**
	 * Gets the current WooCommerce country.
	 *
	 * @return string The current WooCommerce country.
	 */
	public function get_woocommerce_country(): string {
		return $this->wc_country;
	}

	/**
	 * Gets the current WordPress version.
	 *
	 * @return string The current WordPress version.
	 */
	public function get_wordpress_version(): string {
		return $this->wp_version;
	}

	/**
	 * Gets the current PHP version.
	 *
	 * @return string The current PHP version.
	 */
	public function get_php_version(): string {
		return $this->php_version;
	}

	/**
	 * Gets the site URL.
	 *
	 * @return string The site URL.
	 */
	public function get_site_url(): string {
		return $this->site_url;
	}

	/**
	 * Gets the redirect uri for oauth authentication.
	 *
	 * @return string The site URL.
	 */
	public function get_oauth_redirect_url(): string {
		return admin_url( '/admin-ajax.php?action=autoship_oauth2' );
	}

	/**
	 * Gets the site name.
	 *
	 * @return string The site name.
	 */
	public function get_site_name(): string {
		return $this->site_name;
	}

	/**
	 * Gets the current admin email.
	 *
	 * @return string The current admin email.
	 */
	public function get_admin_email(): string {
		return $this->admin_email;
	}

	/**
	 * Gets the current QPilot API URL.
	 *
	 * @return string
	 */
	public function get_api_url(): string {
		return $this->api_url;
	}

	/**
	 * Gets the current Merchants API URL.
	 *
	 * @return string
	 */
	public function get_qmc_url(): string {
		return $this->qmc_url;
	}

	/**
	 * Gets the current Autoship plugin URL.
	 *
	 * @return string
	 */
	public function get_autoship_plugin_url(): string {
		return plugin_dir_url( Autoship_Plugin_File );
	}

	/**
	 * Gets the plugin directory path.
	 *
	 * @return string The plugin directory path.
	 */
	public function get_plugin_dir(): string {
		return Autoship_Plugin_Dir;
	}

	/**
	 * Generates a keyed hash of a string using WordPress salts.
	 *
	 * @param string $value The value to hash.
	 *
	 * @return string
	 */
	public function hash( string $value ): string {
		return wp_hash( $value );
	}

	/**
	 * Sanitizes a filename, replacing whitespace and special characters.
	 *
	 * @param string $name The filename to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_file_name( string $name ): string {
		return \sanitize_file_name( $name );
	}

	/**
	 * Appends a trailing slash to a path.
	 *
	 * @param string $path The path to trail.
	 *
	 * @return string
	 */
	public function trailingslashit( string $path ): string {
		return \trailingslashit( $path );
	}
}
