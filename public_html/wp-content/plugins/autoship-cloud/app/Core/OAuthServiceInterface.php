<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for OAuth service operations.
 *
 * @package Autoship
 * @since 2.12.0
 */

namespace Autoship\Core;

use Exception;

/**
 * Interface for OAuth service operations.
 *
 * Provides methods to manage OAuth authentication flow
 * and site connection with the QPilot API.
 *
 * @package Autoship
 * @since 2.12.0
 */
interface OAuthServiceInterface {

	/**
	 * Connects the current site to QPilot using stored OAuth credentials.
	 *
	 * This method should:
	 * 1. Verify credentials exist
	 * 2. Create or update the site in QPilot
	 * 3. Store the site ID locally
	 *
	 * @return int The site ID after successful connection.
	 * @throws Exception When the connection fails.
	 */
	public function connect_site(): int;

	/**
	 * Disconnects the current site from QPilot.
	 *
	 * This method should:
	 * 1. Clear stored tokens
	 * 2. Clear site ID
	 * 3. Optionally notify QPilot of disconnection
	 *
	 * @return bool True if disconnection was successful.
	 */
	public function disconnect_site(): bool;

	/**
	 * Refreshes the OAuth access token using the refresh token.
	 *
	 * @return bool True if the token was refreshed successfully.
	 * @throws Exception When token refresh fails.
	 */
	public function refresh_token(): bool;

	/**
	 * Checks if the current token is expired or about to expire.
	 *
	 * @param int $buffer_seconds Number of seconds before expiration to consider as "expiring soon". Default 3600 (1 hour).
	 * @return bool True if the token is expired or expiring soon.
	 */
	public function is_token_expired( int $buffer_seconds = 3600 ): bool;

	/**
	 * Gets the OAuth authorization URL for the initial connection flow.
	 *
	 * @return string The authorization URL.
	 */
	public function get_authorization_url(): string;

	/**
	 * Handles the OAuth callback and exchanges the authorization code for tokens.
	 *
	 * @param string $authorization_code The authorization code from the OAuth callback.
	 * @return bool True if tokens were successfully obtained and stored.
	 * @throws Exception When the token exchange fails.
	 */
	public function handle_callback( string $authorization_code ): bool;
}
