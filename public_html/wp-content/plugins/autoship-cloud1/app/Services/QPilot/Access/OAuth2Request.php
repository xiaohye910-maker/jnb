<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * OAuth2Request class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Access;

/**
 * Request object for OAuth2 authentication.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class OAuth2Request {
	/**
	 * The authorization code.
	 *
	 * @var string
	 */
	private string $code;

	/**
	 * The client secret.
	 *
	 * @var string|null
	 */
	private ?string $client_secret = null;

	/**
	 * The client ID.
	 *
	 * @var string|null
	 */
	private ?string $client_id = null;

	/**
	 * The grant type.
	 *
	 * @var string
	 */
	private string $grant_type = 'authorization_code';

	/**
	 * The redirect URI.
	 *
	 * @var string|null
	 */
	private ?string $redirect_uri = null;

	/**
	 * Constructor.
	 *
	 * @param string $code The authorization code.
	 */
	public function __construct( string $code ) {
		$this->code = $code;
	}

	/**
	 * Get the authorization code.
	 *
	 * @return string The authorization code.
	 */
	public function get_code(): string {
		return $this->code;
	}

	/**
	 * Set the client secret.
	 *
	 * @param string $client_secret The client secret.
	 * @return self
	 */
	public function set_client_secret( string $client_secret ): self {
		$this->client_secret = $client_secret;
		return $this;
	}

	/**
	 * Get the client secret.
	 *
	 * @return string|null The client secret.
	 */
	public function get_client_secret(): ?string {
		return $this->client_secret;
	}

	/**
	 * Set the client ID.
	 *
	 * @param string $client_id The client ID.
	 * @return self
	 */
	public function set_client_id( string $client_id ): self {
		$this->client_id = $client_id;
		return $this;
	}

	/**
	 * Get the client ID.
	 *
	 * @return string|null The client ID.
	 */
	public function get_client_id(): ?string {
		return $this->client_id;
	}

	/**
	 * Set the grant type.
	 *
	 * @param string $grant_type The grant type.
	 * @return self
	 */
	public function set_grant_type( string $grant_type ): self {
		$this->grant_type = $grant_type;
		return $this;
	}

	/**
	 * Get the grant type.
	 *
	 * @return string The grant type.
	 */
	public function get_grant_type(): string {
		return $this->grant_type;
	}

	/**
	 * Set the redirect URI.
	 *
	 * @param string $redirect_uri The redirect URI.
	 * @return self
	 */
	public function set_redirect_uri( string $redirect_uri ): self {
		$this->redirect_uri = $redirect_uri;
		return $this;
	}

	/**
	 * Get the redirect URI.
	 *
	 * @return string|null The redirect URI.
	 */
	public function get_redirect_uri(): ?string {
		return $this->redirect_uri;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'code'       => $this->code,
			'grant_type' => $this->grant_type,
		);

		if ( null !== $this->client_secret ) {
			$data['client_secret'] = $this->client_secret;
		}

		if ( null !== $this->client_id ) {
			$data['client_id'] = $this->client_id;
		}

		if ( null !== $this->redirect_uri ) {
			$data['redirect_uri'] = $this->redirect_uri;
		}

		return $data;
	}
}
