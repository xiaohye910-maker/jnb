<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CustomerMetricsRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Customers;

/**
 * Request object for fetching customer metrics.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CustomerMetricsRequest {
	/**
	 * Filter for metrics from this start date.
	 *
	 * @var string|null
	 */
	private ?string $start_date = null;

	/**
	 * Filter for metrics until this end date.
	 *
	 * @var string|null
	 */
	private ?string $end_date = null;

	/**
	 * Whether to include inactive customers in metrics.
	 *
	 * @var bool|null
	 */
	private ?bool $include_inactive = null;

	/**
	 * Whether to include deleted customers in metrics.
	 *
	 * @var bool|null
	 */
	private ?bool $include_deleted = null;

	/**
	 * Set the start date.
	 *
	 * @param string $start_date Filter for metrics from this start date.
	 * @return self
	 */
	public function set_start_date( string $start_date ): self {
		$this->start_date = $start_date;
		return $this;
	}

	/**
	 * Set the end date.
	 *
	 * @param string $end_date Filter for metrics until this end date.
	 * @return self
	 */
	public function set_end_date( string $end_date ): self {
		$this->end_date = $end_date;
		return $this;
	}

	/**
	 * Set whether to include inactive customers.
	 *
	 * @param bool $include_inactive Whether to include inactive customers.
	 * @return self
	 */
	public function set_include_inactive( bool $include_inactive ): self {
		$this->include_inactive = $include_inactive;
		return $this;
	}

	/**
	 * Set whether to include deleted customers.
	 *
	 * @param bool $include_deleted Whether to include deleted customers.
	 * @return self
	 */
	public function set_include_deleted( bool $include_deleted ): self {
		$this->include_deleted = $include_deleted;
		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array();

		if ( null !== $this->start_date ) {
			$data['startDate'] = $this->start_date;
		}

		if ( null !== $this->end_date ) {
			$data['endDate'] = $this->end_date;
		}

		if ( null !== $this->include_inactive ) {
			$data['includeInactive'] = $this->include_inactive;
		}

		if ( null !== $this->include_deleted ) {
			$data['includeDeleted'] = $this->include_deleted;
		}

		return $data;
	}
}
