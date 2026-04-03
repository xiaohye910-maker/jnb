<?php
/**
 * Hustle Auth Provider Interface
 *
 * @package Hustle
 */

/**
 * Interface for authentication providers.
 */
interface Hustle_Auth_Provider {
	/**
	 * Get the authentication token.
	 *
	 * @param string $code Authorization code.
	 * @return Hustle_Auth_Token|null
	 */
	public function get_access_token( $code );

	/**
	 * Refresh the authentication token.
	 *
	 * @param string $refresh_token Refresh token.
	 * @return Hustle_Auth_Token|null
	 */
	public function refresh_access_token( $refresh_token );
}
