<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * OAuth2Response class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Access;

use stdClass;

/**
 * Response object for OAuth2 authentication.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class OAuth2Response {
	/**
	 * The access token.
	 *
	 * @var string
	 */
	private string $access_token;

	/**
	 * The token type.
	 *
	 * @var string
	 */
	private string $token_type;

	/**
	 * The expiration time in seconds.
	 *
	 * @var int
	 */
	private int $expires_in;

	/**
	 * The refresh token.
	 *
	 * @var string|null
	 */
	private ?string $refresh_token;

	/**
	 * The raw response data.
	 *
	 * @var stdClass
	 */
	private stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param stdClass $data The response data.
	 */
	public function __construct( stdClass $data ) {
		$this->raw_data      = $data;
		$this->access_token  = $data->access_token;
		$this->token_type    = $data->token_type;
		$this->expires_in    = $data->expires_in;
		$this->refresh_token = $data->refresh_token ?? null;
	}

	/**
	 * Get the access token.
	 *
	 * @return string The access token.
	 */
	public function get_access_token(): string {
		return $this->access_token;
	}

	/**
	 * Get the token type.
	 *
	 * @return string The token type.
	 */
	public function get_token_type(): string {
		return $this->token_type;
	}

	/**
	 * Get the expiration time in seconds.
	 *
	 * @return int The expiration time in seconds.
	 */
	public function get_expires_in(): int {
		return $this->expires_in;
	}

	/**
	 * Get the refresh token.
	 *
	 * @return string|null The refresh token.
	 */
	public function get_refresh_token(): ?string {
		return $this->refresh_token;
	}

	/**
	 * Get the raw response data.
	 *
	 * @return stdClass The raw response data.
	 */
	public function get_raw_data(): stdClass {
		return $this->raw_data;
	}

	/**
	 * Convert the response to an array.
	 *
	 * @return array The response as an array.
	 */
	public function to_array(): array {
		return (array) $this->raw_data;
	}
}
