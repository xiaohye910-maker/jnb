<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the credentials interface.
 *
 * @package Autoship
 * @since 2.12.0
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\CredentialsInterface;

/**
 * WordPress implementation of the credentials interface.
 *
 * Retrieves OAuth credentials from WordPress options.
 *
 * @package Autoship
 * @since 2.12.0
 */
class WordPressCredentials implements CredentialsInterface {

	/**
	 * Option name for client ID.
	 */
	private const OPTION_CLIENT_ID = 'autoship_client_id';

	/**
	 * Option name for client secret.
	 */
	private const OPTION_CLIENT_SECRET = 'autoship_client_secret';

	/**
	 * Option name for auth token.
	 */
	private const OPTION_TOKEN_AUTH = 'autoship_token_auth';

	/**
	 * Option name for refresh token.
	 */
	private const OPTION_REFRESH_TOKEN = 'autoship_refresh_token';

	/**
	 * Option name for site ID.
	 */
	private const OPTION_SITE_ID = 'autoship_site_id';

	/**
	 * Option name for token expiration.
	 */
	private const OPTION_TOKEN_EXPIRES_IN = 'autoship_token_expires_in';

	/**
	 * Option name for token creation time.
	 */
	private const OPTION_TOKEN_CREATED_AT = 'autoship_token_created_at';

	/**
	 * Checks if the site has valid API credentials (client ID and secret).
	 *
	 * @return bool True if credentials are configured, false otherwise.
	 */
	public function has_credentials(): bool {
		return ! empty( $this->get_client_id() ) && ! empty( $this->get_client_secret() );
	}

	/**
	 * Checks if the site has a valid authentication token.
	 *
	 * @return bool True if an auth token exists, false otherwise.
	 */
	public function has_auth_token(): bool {
		return ! empty( $this->get_auth_token() );
	}

	/**
	 * Checks if the site is fully connected (has both credentials and token).
	 *
	 * @return bool True if fully connected, false otherwise.
	 */
	public function is_connected(): bool {
		return $this->has_credentials() && $this->has_auth_token() && null !== $this->get_site_id();
	}

	/**
	 * Gets the client ID.
	 *
	 * @return string|null The client ID or null if not set.
	 */
	public function get_client_id(): ?string {
		$value = get_option( self::OPTION_CLIENT_ID, '' );
		return ! empty( $value ) ? (string) $value : null;
	}

	/**
	 * Gets the client secret.
	 *
	 * @return string|null The client secret or null if not set.
	 */
	public function get_client_secret(): ?string {
		$value = get_option( self::OPTION_CLIENT_SECRET, '' );
		return ! empty( $value ) ? (string) $value : null;
	}

	/**
	 * Gets the authentication token.
	 *
	 * @return string|null The auth token or null if not set.
	 */
	public function get_auth_token(): ?string {
		$value = get_option( self::OPTION_TOKEN_AUTH, '' );
		return ! empty( $value ) ? (string) $value : null;
	}

	/**
	 * Gets the refresh token.
	 *
	 * @return string|null The refresh token or null if not set.
	 */
	public function get_refresh_token(): ?string {
		$value = get_option( self::OPTION_REFRESH_TOKEN, '' );
		return ! empty( $value ) ? (string) $value : null;
	}

	/**
	 * Gets the site ID.
	 *
	 * @return int|null The site ID or null if not connected.
	 */
	public function get_site_id(): ?int {
		$value = get_option( self::OPTION_SITE_ID, 0 );
		$int_value = (int) $value;
		return $int_value > 0 ? $int_value : null;
	}

	/**
	 * Gets the token expiration time in seconds.
	 *
	 * @return int|null The expiration time or null if not set.
	 */
	public function get_token_expires_in(): ?int {
		$value = get_option( self::OPTION_TOKEN_EXPIRES_IN, 0 );
		$int_value = (int) $value;
		return $int_value > 0 ? $int_value : null;
	}

	/**
	 * Gets the timestamp when the token was created.
	 *
	 * @return int|null The creation timestamp or null if not set.
	 */
	public function get_token_created_at(): ?int {
		$value = get_option( self::OPTION_TOKEN_CREATED_AT, 0 );
		$int_value = (int) $value;
		return $int_value > 0 ? $int_value : null;
	}
}
