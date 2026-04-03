<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the OAuth service interface.
 *
 * @package Autoship
 * @since 2.12.0
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\CredentialsInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Core\OAuthServiceInterface;
use Exception;

/**
 * WordPress implementation of the OAuth service interface.
 *
 * Manages OAuth authentication flow and site connection with QPilot API.
 *
 * @package Autoship
 * @since 2.12.0
 */
class WordPressOAuthService implements OAuthServiceInterface {

	/**
	 * The credentials interface.
	 *
	 * @var CredentialsInterface
	 */
	private CredentialsInterface $credentials;

	/**
	 * The environment interface.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * Constructor.
	 *
	 * @param CredentialsInterface $credentials The credentials interface.
	 * @param EnvironmentInterface $environment The environment interface.
	 */
	public function __construct( CredentialsInterface $credentials, EnvironmentInterface $environment ) {
		$this->credentials = $credentials;
		$this->environment = $environment;
	}

	/**
	 * Connects the current site to QPilot using stored OAuth credentials.
	 *
	 * @return int The site ID after successful connection.
	 * @throws Exception When the connection fails.
	 */
	public function connect_site(): int {
		// Verify credentials exist.
		if ( ! $this->credentials->has_credentials() || ! $this->credentials->has_auth_token() ) {
			throw new Exception( 'The site could not be connected because the credentials or tokens are not available.' );
		}

		// Delegate to the legacy function for now.
		// This maintains backwards compatibility while providing the interface.
		// Future: Implement directly using QPilotServiceClient.
		if ( function_exists( 'autoship_oauth2_connect_site' ) ) {
			$site = autoship_oauth2_connect_site();

			if ( null === $site ) {
				throw new Exception( 'The site could not be connected. Please try again.' );
			}
		}

		// Once the site is connected, return the site ID using the credentials interface as the connect_site stores it in the options.
		$site_id = $this->credentials->get_site_id();
		if ( null === $site_id || $site_id <= 0 ) {
			throw new Exception( 'The site could not be connected, please try again.' );
		}

		return $site_id;
	}

	/**
	 * Disconnects the current site from QPilot.
	 *
	 * @return bool True if disconnection was successful.
	 */
	public function disconnect_site(): bool {
		// Clear all credential options.
		$options_to_clear = array(
			'autoship_client_id',
			'autoship_client_secret',
			'autoship_token_auth',
			'autoship_refresh_token',
			'autoship_site_id',
			'autoship_user_id',
			'autoship_token_expires_in',
			'autoship_token_created_at',
		);

		foreach ( $options_to_clear as $option ) {
			delete_option( $option );
		}

		return true;
	}

	/**
	 * Refreshes the OAuth access token using the refresh token.
	 *
	 * @return bool True if the token was refreshed successfully.
	 * @throws Exception When token refresh fails.
	 */
	public function refresh_token(): bool {
		$refresh_token = $this->credentials->get_refresh_token();
		$client_id = $this->credentials->get_client_id();
		$client_secret = $this->credentials->get_client_secret();

		if ( empty( $refresh_token ) || empty( $client_id ) || empty( $client_secret ) ) {
			throw new Exception( 'Cannot refresh token: missing credentials or refresh token.' );
		}

		$response = wp_remote_post(
			$this->environment->get_api_url() . '/OAuth2/Token',
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Token refresh failed: ' . $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['access_token'] ) ) {
			throw new Exception( 'Token refresh failed: invalid response from server.' );
		}

		// Store the new tokens.
		update_option( 'autoship_token_auth', sanitize_text_field( $body['access_token'] ) );

		if ( ! empty( $body['refresh_token'] ) ) {
			update_option( 'autoship_refresh_token', sanitize_text_field( $body['refresh_token'] ) );
		}

		if ( ! empty( $body['expires_in'] ) ) {
			update_option( 'autoship_token_expires_in', (int) $body['expires_in'] );
			update_option( 'autoship_token_created_at', time() );
		}

		return true;
	}

	/**
	 * Checks if the current token is expired or about to expire.
	 *
	 * @param int $buffer_seconds Number of seconds before expiration to consider as "expiring soon". Default 3600 (1 hour).
	 * @return bool True if the token is expired or expiring soon.
	 */
	public function is_token_expired( int $buffer_seconds = 3600 ): bool {
		if ( ! $this->credentials->has_auth_token() ) {
			return true;
		}

		// Get token creation time and expiration.
		$created_at = get_option( 'autoship_token_created_at', 0 );
		$expires_in = get_option( 'autoship_token_expires_in', 0 );

		if ( empty( $created_at ) || empty( $expires_in ) ) {
			// Cannot determine expiration, assume not expired.
			return false;
		}

		$expiration_time = (int) $created_at + (int) $expires_in;
		$current_time = time();

		return ( $current_time + $buffer_seconds ) >= $expiration_time;
	}

	/**
	 * Gets the OAuth authorization URL for the initial connection flow.
	 *
	 * @return string The authorization URL.
	 */
	public function get_authorization_url(): string {
		$client_id = $this->credentials->get_client_id();
		$redirect_uri = $this->environment->get_oauth_redirect_url();

		$params = array(
			'client_id'     => $client_id,
			'response_type' => 'code',
			'redirect_uri'  => $redirect_uri,
			'scope'         => 'Merchant',
		);

		return $this->environment->get_api_url() . '/OAuth2/Authorize?' . http_build_query( $params );
	}

	/**
	 * Handles the OAuth callback and exchanges the authorization code for tokens.
	 *
	 * @param string $authorization_code The authorization code from the OAuth callback.
	 * @return bool True if tokens were successfully obtained and stored.
	 * @throws Exception When the token exchange fails.
	 */
	public function handle_callback( string $authorization_code ): bool {
		$client_id = $this->credentials->get_client_id();
		$client_secret = $this->credentials->get_client_secret();
		$redirect_uri = $this->environment->get_oauth_redirect_url();

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			throw new Exception( 'Cannot exchange code: missing client credentials.' );
		}

		$response = wp_remote_post(
			$this->environment->get_api_url() . '/OAuth2/Token',
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'authorization_code',
					'code'          => $authorization_code,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'redirect_uri'  => $redirect_uri,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Token exchange failed: ' . $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['access_token'] ) ) {
			throw new Exception( 'Token exchange failed: invalid response from server.' );
		}

		// Store the tokens.
		update_option( 'autoship_token_auth', sanitize_text_field( $body['access_token'] ) );

		if ( ! empty( $body['refresh_token'] ) ) {
			update_option( 'autoship_refresh_token', sanitize_text_field( $body['refresh_token'] ) );
		}

		if ( ! empty( $body['expires_in'] ) ) {
			update_option( 'autoship_token_expires_in', (int) $body['expires_in'] );
			update_option( 'autoship_token_created_at', time() );
		}

		return true;
	}
}
