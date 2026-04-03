<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for environment information.
 *
 * @package Autoship
 * @since 2.12.0
 */

namespace Autoship\Core;

/**
 * Interface for environment information.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface EnvironmentInterface {

	/**
	 * Returns the current user display name.
	 *
	 * @return string The current user display name.
	 */
	public function get_current_user_name(): string;

	/**
	 * Get the current user email.
	 *
	 * @return string The current user email.
	 */
	public function get_current_user_email(): string;

	/**
	 * Get the current user login.
	 *
	 * @return string The current user login.
	 */
	public function get_current_user_login(): string;

	/**
	 * Gets the current Autoship version.
	 *
	 * @return string The current Autoship version.
	 */
	public function get_autoship_version(): string;

	/**
	 * Gets the current WordPress locale.
	 *
	 * @return string The current WordPress locale.
	 */
	public function get_wordpress_language(): string;

	/**
	 * Gets the current WooCommerce version.
	 *
	 * @return string The current WooCommerce version.
	 */
	public function get_woocommerce_version(): string;

	/**
	 * Gets the current WooCommerce country.
	 *
	 * @return string The current WooCommerce country.
	 */
	public function get_woocommerce_country(): string;

	/**
	 * Gets the current WordPress version.
	 *
	 * @return string The current WordPress version.
	 */
	public function get_wordpress_version(): string;

	/**
	 * Gets the current PHP version.
	 *
	 * @return string The current PHP version.
	 */
	public function get_php_version(): string;

	/**
	 * Gets the site URL.
	 *
	 * @return string The site URL.
	 */
	public function get_site_url(): string;

	/**
	 * Gets the redirect uri for oauth authentication.
	 *
	 * @return string The site URL.
	 */
	public function get_oauth_redirect_url(): string;

	/**
	 * Gets the site name.
	 *
	 * @return string The site name.
	 */
	public function get_site_name(): string;

	/**
	 * Gets the current admin email.
	 *
	 * @return string The current admin email.
	 */
	public function get_admin_email(): string;

	/**
	 * Gets the current QPilot API URL.
	 *
	 * @return string
	 */
	public function get_api_url(): string;

	/**
	 * Gets the current Merchants API URL.
	 *
	 * @return string
	 */
	public function get_qmc_url(): string;

	/**
	 * Gets the current Autoship plugin URL.
	 *
	 * @return string
	 */
	public function get_autoship_plugin_url(): string;

	/**
	 * Generates a keyed hash of a string using WordPress salts.
	 *
	 * @param string $value The value to hash.
	 *
	 * @return string
	 */
	public function hash( string $value ): string;

	/**
	 * Sanitizes a filename, replacing whitespace and special characters.
	 *
	 * @param string $name The filename to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_file_name( string $name ): string;

	/**
	 * Appends a trailing slash to a path.
	 *
	 * @param string $path The path to trail.
	 *
	 * @return string
	 */
	public function trailingslashit( string $path ): string;

	/**
	 * Gets the plugin directory path.
	 *
	 * @return string The plugin directory path.
	 */
	public function get_plugin_dir(): string;
}
