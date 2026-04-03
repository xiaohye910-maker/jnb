<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Customers;

/**
 * Batch customer request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class BatchCustomerRequest {
	/**
	 * Array of customer requests.
	 *
	 * @var array<UpsertCustomerRequest>
	 */
	private array $customers = array();

	/**
	 * Constructor.
	 *
	 * @param array<UpsertCustomerRequest> $customers Array of customer requests.
	 */
	public function __construct( array $customers = array() ) {
		$this->customers = $customers;
	}

	/**
	 * Add a customer request.
	 *
	 * @param UpsertCustomerRequest $customer The customer request.
	 *
	 * @return self
	 */
	public function add_customer( UpsertCustomerRequest $customer ): self {
		$this->customers[] = $customer;

		return $this;
	}

	/**
	 * Get the customers.
	 *
	 * @return array<UpsertCustomerRequest> Array of customer requests.
	 */
	public function get_customers(): array {
		return $this->customers;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$customers = array();
		foreach ( $this->customers as $customer ) {
			$customers[] = $customer->to_array();
		}

		return array( 'customers' => $customers );
	}
}
