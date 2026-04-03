<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CustomerMetricsResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Customers;

use stdClass;

/**
 * Response object for customer metrics data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CustomerMetricsResponse {
	/**
	 * Total number of customers.
	 *
	 * @var int|null
	 */
	private ?int $total_customers = null;

	/**
	 * Number of active customers.
	 *
	 * @var int|null
	 */
	private ?int $active_customers = null;

	/**
	 * Number of inactive customers.
	 *
	 * @var int|null
	 */
	private ?int $inactive_customers = null;

	/**
	 * Number of customers with scheduled orders.
	 *
	 * @var int|null
	 */
	private ?int $customers_with_scheduled_orders = null;

	/**
	 * Total number of scheduled orders.
	 *
	 * @var int|null
	 */
	private ?int $total_scheduled_orders = null;

	/**
	 * Number of active scheduled orders.
	 *
	 * @var int|null
	 */
	private ?int $active_scheduled_orders = null;

	/**
	 * Number of paused scheduled orders.
	 *
	 * @var int|null
	 */
	private ?int $paused_scheduled_orders = null;

	/**
	 * Number of failed scheduled orders.
	 *
	 * @var int|null
	 */
	private ?int $failed_scheduled_orders = null;

	/**
	 * Number of cancelled scheduled orders.
	 *
	 * @var int|null
	 */
	private ?int $cancelled_scheduled_orders = null;

	/**
	 * The raw response data.
	 *
	 * @var \stdClass
	 */
	private \stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param stdClass $data The response data.
	 */
	public function __construct( stdClass $data ) {
		$this->raw_data                        = $data;
		$this->total_customers                 = $data->totalCustomers ?? null;  // phpcs:ignore
		$this->active_customers                = $data->activeCustomers ?? null; // phpcs:ignore
		$this->inactive_customers              = $data->inactiveCustomers ?? null; // phpcs:ignore
		$this->customers_with_scheduled_orders = $data->customersWithScheduledOrders ?? null; // phpcs:ignore
		$this->total_scheduled_orders          = $data->totalScheduledOrders ?? null; // phpcs:ignore
		$this->active_scheduled_orders         = $data->activeScheduledOrders ?? null; // phpcs:ignore
		$this->paused_scheduled_orders         = $data->pausedScheduledOrders ?? null; // phpcs:ignore
		$this->failed_scheduled_orders         = $data->failedScheduledOrders ?? null; // phpcs:ignore
		$this->cancelled_scheduled_orders      = $data->cancelledScheduledOrders ?? null; // phpcs:ignore
	}

	/**
	 * Get the total number of customers.
	 *
	 * @return int|null The total number of customers.
	 */
	public function get_total_customers(): ?int {
		return $this->total_customers;
	}

	/**
	 * Get the number of active customers.
	 *
	 * @return int|null The number of active customers.
	 */
	public function get_active_customers(): ?int {
		return $this->active_customers;
	}

	/**
	 * Get the number of inactive customers.
	 *
	 * @return int|null The number of inactive customers.
	 */
	public function get_inactive_customers(): ?int {
		return $this->inactive_customers;
	}

	/**
	 * Get the number of customers with scheduled orders.
	 *
	 * @return int|null The number of customers with scheduled orders.
	 */
	public function get_customers_with_scheduled_orders(): ?int {
		return $this->customers_with_scheduled_orders;
	}

	/**
	 * Get the total number of scheduled orders.
	 *
	 * @return int|null The total number of scheduled orders.
	 */
	public function get_total_scheduled_orders(): ?int {
		return $this->total_scheduled_orders;
	}

	/**
	 * Get the number of active scheduled orders.
	 *
	 * @return int|null The number of active scheduled orders.
	 */
	public function get_active_scheduled_orders(): ?int {
		return $this->active_scheduled_orders;
	}

	/**
	 * Get the number of paused scheduled orders.
	 *
	 * @return int|null The number of paused scheduled orders.
	 */
	public function get_paused_scheduled_orders(): ?int {
		return $this->paused_scheduled_orders;
	}

	/**
	 * Get the number of failed scheduled orders.
	 *
	 * @return int|null The number of failed scheduled orders.
	 */
	public function get_failed_scheduled_orders(): ?int {
		return $this->failed_scheduled_orders;
	}

	/**
	 * Get the number of cancelled scheduled orders.
	 *
	 * @return int|null The number of cancelled scheduled orders.
	 */
	public function get_cancelled_scheduled_orders(): ?int {
		return $this->cancelled_scheduled_orders;
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
