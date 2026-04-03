<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for credentials management.
 *
 * @package Autoship
 * @since 2.12.0
 */

namespace Autoship\Core;

/**
 * Interface for credentials management.
 *
 * Provides methods to check and retrieve OAuth credentials
 * for the QPilot API connection.
 *
 * @package Autoship
 * @since 2.12.0
 */
interface CredentialsInterface {

	/**
	 * Checks if the site has valid API credentials (client ID and secret).
	 *
	 * @return bool True if credentials are configured, false otherwise.
	 */
	public function has_credentials(): bool;

	/**
	 * Checks if the site has a valid authentication token.
	 *
	 * @return bool True if an auth token exists, false otherwise.
	 */
	public function has_auth_token(): bool;

	/**
	 * Checks if the site is fully connected (has both credentials and token).
	 *
	 * @return bool True if fully connected, false otherwise.
	 */
	public function is_connected(): bool;

	/**
	 * Gets the client ID.
	 *
	 * @return string|null The client ID or null if not set.
	 */
	public function get_client_id(): ?string;

	/**
	 * Gets the client secret.
	 *
	 * @return string|null The client secret or null if not set.
	 */
	public function get_client_secret(): ?string;

	/**
	 * Gets the authentication token.
	 *
	 * @return string|null The auth token or null if not set.
	 */
	public function get_auth_token(): ?string;

	/**
	 * Gets the refresh token.
	 *
	 * @return string|null The refresh token or null if not set.
	 */
	public function get_refresh_token(): ?string;

	/**
	 * Gets the site ID.
	 *
	 * @return int|null The site ID or null if not connected.
	 */
	public function get_site_id(): ?int;
}
