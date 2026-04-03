<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Authentication Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

use Autoship\Services\QPilot\Access\OAuth2Request;
use Autoship\Services\QPilot\Access\OAuth2Response;
use Autoship\Services\QPilot\Access\AccessTokenResponse;

/**
 * Interface for authentication with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface AccessManagementInterface {
	/**
	 * Authenticate with OAuth2.
	 *
	 * @param OAuth2Request $request The OAuth2 request containing the code.
	 * @return OAuth2Response The OAuth2 response.
	 */
	public function oauth2( OAuth2Request $request ): OAuth2Response;

	/**
	 * Refresh OAuth2 token.
	 *
	 * @return OAuth2Response The OAuth2 response.
	 */
	public function refresh_oauth2(): OAuth2Response;

	/**
	 * Generate a merchant access token.
	 *
	 * @param string $secret_key The secret key.
	 * @return AccessTokenResponse The access token response.
	 */
	public function generate_merchant_access_token( string $secret_key ): AccessTokenResponse;

	/**
	 * Generate a customer access token.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $secret_key The secret key.
	 * @return AccessTokenResponse The access token response.
	 */
	public function generate_customer_access_token( int $customer_id, string $secret_key ): AccessTokenResponse;
}
