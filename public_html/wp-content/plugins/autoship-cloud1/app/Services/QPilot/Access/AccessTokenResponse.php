<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * AccessTokenResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Access;

use stdClass;

/**
 * Response object for access token data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class AccessTokenResponse {
	/**
	 * The access token.
	 *
	 * @var string
	 */
	private string $token;

	/**
	 * The token type (e.g., "bearer").
	 *
	 * @var string
	 */
	private string $token_type;

	/**
	 * The expiration time in seconds.
	 *
	 * @var int|null
	 */
	private ?int $expires_in;

	/**
	 * The scope of the token.
	 *
	 * @var string|null
	 */
	private ?string $scope;

	/**
	 * The customer ID (for customer access tokens).
	 *
	 * @var int|null
	 */
	private ?int $customer_id;

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
		$this->raw_data    = $data;
		$this->token       = $data->token ?? $data->access_token;
		$this->token_type  = $data->token_type ?? 'bearer';
		$this->expires_in  = $data->expires_in ?? null;
		$this->scope       = $data->scope ?? null;
		$this->customer_id = $data->customer_id ?? null;
	}

	/**
	 * Get the access token.
	 *
	 * @return string The access token.
	 */
	public function get_token(): string {
		return $this->token;
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
	 * @return int|null The expiration time in seconds.
	 */
	public function get_expires_in(): ?int {
		return $this->expires_in;
	}

	/**
	 * Get the scope of the token.
	 *
	 * @return string|null The scope of the token.
	 */
	public function get_scope(): ?string {
		return $this->scope;
	}

	/**
	 * Get the customer ID.
	 *
	 * @return int|null The customer ID.
	 */
	public function get_customer_id(): ?int {
		return $this->customer_id;
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
