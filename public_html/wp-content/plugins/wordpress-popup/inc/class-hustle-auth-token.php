<?php
/**
 * Hustle Auth Token Class
 *
 * @package Hustle
 */

/**
 * Class representing an authentication token.
 */
class Hustle_Auth_Token {
	/**
	 * Access token.
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Refresh token.
	 *
	 * @var string
	 */
	private $refresh_token;

	/**
	 * Expiration time (Unix timestamp).
	 *
	 * @var int
	 */
	private $expiration_time;

	/**
	 * Scope of the token.
	 *
	 * @var string
	 */
	private $scope;

	/**
	 * Hustle_Auth_Token constructor.
	 *
	 * @param string $access_token  Access token.
	 * @param string $refresh_token Refresh token.
	 * @param int    $expiration_time Token expiration time (Unix timestamp).
	 * @param string $scope         Scope of the token.
	 */
	public function __construct( $access_token, $refresh_token, $expiration_time, $scope = '' ) {
		$this->access_token    = $access_token;
		$this->refresh_token   = $refresh_token;
		$this->expiration_time = $expiration_time;
		$this->scope           = $scope;
	}

	/**
	 * Set the expiration time.
	 *
	 * @param int $expiration_time Expiration time (Unix timestamp).
	 */
	public function set_expiration_time( $expiration_time ) {
		$this->expiration_time = $expiration_time;
	}

	/**
	 * Set the token lifetime in seconds from now.
	 *
	 * @param int $seconds Lifetime in seconds.
	 */
	public function set_token_lifetime( $seconds ) {
		$this->expiration_time = time() + (int) $seconds;
	}

	/**
	 * Get the scope.
	 *
	 * @return string
	 */
	public function get_scope() {
		return $this->scope;
	}

	/**
	 * Set the scope.
	 *
	 * @param string $scope Scope.
	 */
	public function set_scope( $scope ) {
		$this->scope = $scope;
	}

	/**
	 * Get the access token.
	 *
	 * @return string
	 */
	public function get_access_token() {
		return $this->access_token;
	}

	/**
	 * Get the refresh token.
	 *
	 * @return string
	 */
	public function get_refresh_token() {
		return $this->refresh_token;
	}

	/**
	 * Get the expiration time.
	 *
	 * @return int
	 */
	public function get_expiration_time() {
		return $this->expiration_time;
	}

	/**
	 * Create an instance from an associative array.
	 *
	 * @param array $data Associative array with keys: access_token, refresh_token, expires_in, scope.
	 * @return Hustle_Auth_Token|null Returns an instance of Hustle_Auth_Token or null if data is invalid.
	 */
	public static function from_array( $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return null;
		}

		$token = new self(
			$data['access_token'] ?? '',
			$data['refresh_token'] ?? '',
			$data['expiration_time'] ?? 0,
			$data['scope'] ?? ''
		);

		if ( isset( $data['expires_in'] ) ) {
			$token->set_token_lifetime( (int) $data['expires_in'] );
		}

		return $token;
	}
}
