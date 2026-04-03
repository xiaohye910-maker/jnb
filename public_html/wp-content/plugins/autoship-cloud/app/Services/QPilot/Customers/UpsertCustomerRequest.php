<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * UpsertCustomerRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Customers;

/**
 * Request object for creating or updating a customer.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class UpsertCustomerRequest {
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
	 * Constructor.
	 *
	 * @param int    $id The customer ID.
	 * @param string $email The customer's email address.
	 */
	public function __construct( int $id, string $email ) {
		$this->id    = $id;
		$this->email = $email;
	}

	/**
	 * Set the customer's first name.
	 *
	 * @param string $first_name The customer's first name.
	 *
	 * @return self
	 */
	public function set_first_name( string $first_name ): self {
		$this->first_name = $first_name;

		return $this;
	}

	/**
	 * Set the customer's last name.
	 *
	 * @param string $last_name The customer's last name.
	 *
	 * @return self
	 */
	public function set_last_name( string $last_name ): self {
		$this->last_name = $last_name;

		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'id'    => $this->id,
			'email' => $this->email,
		);

		if ( null !== $this->first_name ) {
			$data['firstName'] = $this->first_name;
		}

		if ( null !== $this->last_name ) {
			$data['lastName'] = $this->last_name;
		}

		return $data;
	}
}
