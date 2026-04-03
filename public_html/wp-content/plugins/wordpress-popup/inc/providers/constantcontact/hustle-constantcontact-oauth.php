<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Constant Contact PKCE OAuth implementation
 * Class Hustle_ConstantContact_OAuth
 *
 * @package Hustle
 */
class Hustle_ConstantContact_OAuth {
	const AUTH_URL  = 'https://authz.constantcontact.com/oauth2/default/v1/authorize';
	const TOKEN_URL = 'https://authz.constantcontact.com/oauth2/default/v1/token';

	/**
	 * Constant Contact API Key
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * PKCE code verifier
	 *
	 * @var string PKCE code verifier.
	 */
	private $code_verifier = '';

	/**
	 * PKCE code challenge
	 *
	 * @var string PKCE code challenge.
	 */
	private $code_challenge = '';

	/**
	 * Constructor.
	 *
	 * @param string $api_key Constant Contact API Key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
		$this->init();
	}

	/**
	 * Initialize the OAuth class.
	 */
	private function init() {
		$auth_keys = get_transient( 'hustle_constantcontact_auth_keys' );
		if ( $auth_keys ) {
			$this->code_verifier  = $auth_keys['code_verifier'];
			$this->code_challenge = $auth_keys['code_challenge'];
		}
	}

	/**
	 * Get the access token from the authorization code.
	 *
	 * @param string $code The authorization code.
	 * @param string $redirect_uri The original redirect URI.
	 * @return array The access tokenS.
	 *
	 * @throws Exception If the access token cannot be retrieved.
	 */
	public function get_access_token( $code, $redirect_uri ) {
		// Sanitize parameters before usage.
		$code          = sanitize_text_field( $code );
		$code_verifier = sanitize_text_field( $this->code_verifier );
		$redirect_uri  = esc_url_raw( $redirect_uri );

		$response = wp_remote_post(
			self::TOKEN_URL,
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'authorization_code',
					'client_id'     => $this->api_key,
					'code'          => $code,
					'redirect_uri'  => $redirect_uri,
					'code_verifier' => $code_verifier,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error retrieving access token: ' . $response->get_error_message() );//phpcs:ignore
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status ) {
			$error = wp_remote_retrieve_body( $response );
			// Throw exception when access token retrieval fails.
			throw new Exception( sprintf( 'Error retrieving access token: %d. %s', $status, $error ) );//phpcs:ignore
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['access_token'] ) ) {
			return $this->prepare_token_data( $data );
		}

		return array();
	}

	/**
	 * Prepare token data from response.
	 *
	 * @param array $response_data The response data.
	 * @return array The prepared token data.
	 */
	private function prepare_token_data( $response_data ) {
		$token_data = array(
			'access_token'    => sanitize_text_field( $response_data['access_token'] ),
			'refresh_token'   => sanitize_text_field( $response_data['refresh_token'] ),
			'scope'           => sanitize_text_field( $response_data['scope'] ),
			'expiration_time' => time() + (int) $response_data['expires_in'],
		);
		return $token_data;
	}

	/**
	 * Refresh the access token using the refresh token.
	 *
	 * @param string $refresh_token The refresh token.
	 * @return array The new access token data.
	 *
	 * @throws Exception If the access token cannot be refreshed.
	 */
	public function refresh_access_token( $refresh_token ) {
		// Sanitize parameters before usage.
		$refresh_token = sanitize_text_field( $refresh_token );

		$response = wp_remote_post(
			self::TOKEN_URL,
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'refresh_token',
					'client_id'     => sanitize_text_field( $this->api_key ),
					'refresh_token' => $refresh_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error refreshing access token: ' . $response->get_error_message() );//phpcs:ignore
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status ) {
			$error = wp_remote_retrieve_body( $response );
			// Throw exception when access token retrieval fails.
			throw new Exception( sprintf( 'Error refreshing access token: %d. %s', $status, $error ) );//phpcs:ignore
		}

		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		if ( isset( $data['access_token'] ) ) {
			return $this->prepare_token_data( $data );
		}

		return array();
	}

	/**
	 * Get the authorization URL for the OAuth flow.
	 *
	 * @param string $redirect_uri The redirect URI.
	 * @param string $state  Additional data.
	 * @return string The authorization URL.
	 */
	public function get_authorization_url( $redirect_uri = '', $state = '' ) {

		if ( empty( $this->code_verifier ) || empty( $this->code_challenge ) ) {
			// Generate new PKCE keys.
			$this->code_verifier  = $this->generate_pkce_verifier();
			$this->code_challenge = $this->generate_pkce_hash( $this->code_verifier );

			$auth_keys = array(
				'code_verifier'  => $this->code_verifier,
				'code_challenge' => $this->code_challenge,
			);
			set_transient( 'hustle_constantcontact_auth_keys', $auth_keys, DAY_IN_SECONDS );
		}

		$query_args = array(
			'response_type'         => 'code',
			'scope'                 => 'offline_access%20account_read%20contact_data',
			'client_id'             => $this->api_key,
			'redirect_uri'          => rawurlencode( $redirect_uri ),
			'code_challenge'        => $this->code_challenge,
			'code_challenge_method' => 'S256',
			'state'                 => $state,
		);

		return add_query_arg( $query_args, self::AUTH_URL );
	}

	/**
	 * Generate a PKCE code challenge from the code verifier.
	 *
	 * @param string $code_verifier The code verifier.
	 * @return string The generated code challenge.
	 */
	private function generate_pkce_hash( $code_verifier ) {
		$code_challenge = rtrim( strtr( base64_encode( hash( 'sha256', $code_verifier, true ) ), '+/', '-_' ), '=' );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return $code_challenge;
	}

	/**
	 * Generate a PKCE code verifier.
	 *
	 * @return string The generated code verifier.
	 */
	private function generate_pkce_verifier() {
		return bin2hex( random_bytes( 43 ) );
	}

	/**
	 * Remove the stored PKCE keys from the database.
	 *
	 * @return void
	 */
	public function reset_auth_keys() {
		delete_transient( 'hustle_constantcontact_auth_keys' );
		$this->code_verifier  = '';
		$this->code_challenge = '';
	}
}
