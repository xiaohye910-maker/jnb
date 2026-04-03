<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CustomerResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Customers;

/**
 * Response object for customer data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CustomerResponse {
	/**
	 * The customer ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * The customer's email address.
	 *
	 * @var string
	 */
	private string $email;

	/**
	 * The customer's first name.
	 *
	 * @var string|null
	 */
	private ?string $first_name = null;

	/**
	 * The customer's last name.
	 *
	 * @var string|null
	 */
	private ?string $last_name = null;

	/**
	 * The raw response data.
	 *
	 * @var \stdClass
	 */
	private \stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param \stdClass $data The response data.
	 */
	public function __construct( \stdClass $data ) {
		$this->raw_data   = $data;
		$this->id         = $data->id;
		$this->email      = $data->email;
		$this->first_name = $data->firstName ?? null; // phpcs:ignore
		$this->last_name  = $data->lastName ?? null; // phpcs:ignore
	}

	/**
	 * Get the customer ID.
	 *
	 * @return int The customer ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the customer's email address.
	 *
	 * @return string The customer's email address.
	 */
	public function get_email(): string {
		return $this->email;
	}

	/**
	 * Get the customer's first name.
	 *
	 * @return string|null The customer's first name.
	 */
	public function get_first_name(): ?string {
		return $this->first_name;
	}

	/**
	 * Get the customer's last name.
	 *
	 * @return string|null The customer's last name.
	 */
	public function get_last_name(): ?string {
		return $this->last_name;
	}

	/**
	 * Get the raw response data.
	 *
	 * @return \stdClass The raw response data.
	 */
	public function get_raw_data(): \stdClass {
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
