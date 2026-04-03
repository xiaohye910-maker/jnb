<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\AccessManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Access\OAuth2Request;
use Autoship\Services\QPilot\Access\OAuth2Response;
use Autoship\Services\QPilot\Access\AccessTokenResponse;
use Exception;

/**
 * Implementation of the AccessManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class AccessManagement implements AccessManagementInterface {
	/**
	 * The API client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $api_client;

	/**
	 * Constructor.
	 *
	 * @param QPilotHttpClient $api_client The API client.
	 */
	public function __construct( QPilotHttpClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Authenticate with OAuth2.
	 *
	 * @param OAuth2Request $request The OAuth2 request containing the code.
	 *
	 * @return OAuth2Response The OAuth2 response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function oauth2( OAuth2Request $request ): OAuth2Response {
		$data = array(
			'code'          => $request->get_code(),
			'client_secret' => $request->get_client_secret(),
			'client_id'     => $request->get_client_id(),
			'grant_type'    => $request->get_grant_type(),
			'redirect_uri'  => $request->get_redirect_uri(),
		);

		$response = $this->api_client->post( 'oauth/token', $data );

		return new OAuth2Response( $response );
	}

	/**
	 * Refresh OAuth2 token.
	 *
	 * @return OAuth2Response The OAuth2 response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function refresh_oauth2(): OAuth2Response {
		$data = array(
			'refresh_token' => get_option( 'autoship_refresh_token' ),
			'client_secret' => get_option( 'autoship_client_secret' ),
			'client_id'     => get_option( 'autoship_client_id' ),
			'grant_type'    => 'refresh_token',
		);

		$response = $this->api_client->post( 'oauth/token', $data );

		return new OAuth2Response( $response );
	}

	/**
	 * Generate a merchant access token.
	 *
	 * @param string $secret_key The secret key.
	 *
	 * @return AccessTokenResponse The access token response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function generate_merchant_access_token( string $secret_key ): AccessTokenResponse {
		$data = array(
			'accessScope' => 'Merchant',
			'secretKey'   => $secret_key,
		);

		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/AccessTokens/Generate";
		$response = $this->api_client->post( $endpoint, $data );

		return new AccessTokenResponse( $response );
	}

	/**
	 * Generate a customer access token.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $secret_key The secret key.
	 *
	 * @return AccessTokenResponse The access token response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function generate_customer_access_token( int $customer_id, string $secret_key ): AccessTokenResponse {
		$data = array(
			'accessScope' => 'Customer',
			'customerId'  => $customer_id,
			'secretKey'   => $secret_key,
		);

		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/AccessTokens/Generate";
		$response = $this->api_client->post( $endpoint, $data );

		return new AccessTokenResponse( $response );
	}
}
