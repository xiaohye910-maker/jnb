<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle InfusionSoft OAuth Non-Hub implementation.
 *
 * This class extends the base Hustle InfusionSoft OAuth functionality
 * for non-hub OAuth implementations, providing API key and secret key
 * management for InfusionSoft integration.
 *
 * @since 7.8.9
 * @package Hustle
 * @subpackage Providers\InfusionSoft
 */
class Hustle_Infusion_Soft_OAuth_Non_Hub extends Hustle_Infusion_Soft_OAuth {

	/**
	 * The API key for InfusionSoft authentication.
	 *
	 * @since 7.8.9
	 * @var string
	 */
	private $api_key;

	/**
	 * The secret key for InfusionSoft authentication.
	 *
	 * @since 7.8.9
	 * @var string
	 */
	private $secret_key;

	/**
	 * Get the API key.
	 *
	 * @since 7.8.9
	 * @return string The API key.
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Set the API key.
	 *
	 * @since 7.8.9
	 * @param string $api_key The API key to set.
	 * @return void
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Get the secret key.
	 *
	 * @since 7.8.9
	 * @return string The secret key.
	 */
	public function get_secret_key() {
		return $this->secret_key;
	}

	/**
	 * Set the secret key.
	 *
	 * @since 7.8.9
	 * @param string $secret_key The secret key to set.
	 * @return void
	 */
	public function set_secret_key( $secret_key ) {
		$this->secret_key = $secret_key;
	}

	/**
	 * Validate the OAuth callback request.
	 *
	 * @since 7.8.9
	 * @param string $provider_slug The provider slug to validate against.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_callback_request( $provider_slug = '' ) {

		// Validate required GET parameters.
		if ( empty( $_GET['state'] ) || empty( $_GET['code'] ) || empty( $_GET['client_id'] ) ) {
			return false;
		}

		// Validate provider slug.
		if ( empty( $_GET['provider'] ) || $_GET['provider'] !== $provider_slug ) {
			return false;
		}

		// Validate that client_id matches the public key (API key).
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- OAuth callback validation uses state parameter for security.
		$client_id    = sanitize_text_field( wp_unslash( $_GET['client_id'] ) );
		$expected_key = $this->get_api_key();
		if ( $expected_key !== $client_id ) {
			return false;
		}

		// Validate state format - should be base64 encoded with nonce|site_url format.
		$state = sanitize_text_field( wp_unslash( $_GET['state'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$decoded_state = base64_decode( $state, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( false === $decoded_state ) {
			return false;
		}

		$params = json_decode( $decoded_state, true );
		if ( ! $params || count( $params ) < 2 ) {
			return false;
		}

		$nonce = $params['nonce'];
		$url   = isset( $params['url'] ) ? $params['url'] : '';

		if ( site_url( '/' ) !== $url ) {
			return false;
		}

		return $this->verify_nonce( $nonce );
	}

	/**
	 * Prepare state param for OAuth requests.
	 *
	 * @return string
	 */
	protected function prepare_state_param() {
		$state_data = array(
			'nonce' => $this->get_nonce_value(),
			'url'   => site_url( '/' ),
		);

		return base64_encode( wp_json_encode( $state_data ) ); // phpcs:ignore
	}

	/**
	 * Compose redirect_uri to use on request argument.
	 * The redirect uri must be constant and should not be change per request.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	protected function get_redirect_uri( $args = array() ) {
		$params = wp_parse_args(
			$args,
			array(
				'action'    => 'authorize',
				'provider'  => 'infusionsoft',
				'client_id' => $this->get_api_key(),
			)
		);

		return add_query_arg( $params, get_site_url() );
	}

	/**
	 * Request the access token from Infusionsoft using the authorization code.
	 *
	 * @param string $code The authorization code.
	 * @param string $redirect_uri The redirect URI used in the authorization request.
	 * @return array|WP_Error The response containing the access token or an error.
	 */
	protected function get_auth_token( $code, $redirect_uri ) {
		$body = array(
			'client_id'     => $this->get_api_key(),
			'client_secret' => $this->get_secret_key(),
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'redirect_uri'  => $redirect_uri,
		);

		$response = wp_remote_post(
			'https://api.infusionsoft.com/token',
			array(
				'body'    => $body,
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		return $response;
	}
}
