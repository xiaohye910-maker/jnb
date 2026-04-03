<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CustomerSummariesRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Customers;

/**
 * Request object for fetching customer summaries.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CustomerSummariesRequest {
	/**
	 * The search results page to return.
	 *
	 * @var int
	 */
	private int $page = 1;

	/**
	 * The page size.
	 *
	 * @var int
	 */
	private int $page_size = 100;

	/**
	 * A property to sort the results by.
	 *
	 * @var string|null
	 */
	private ?string $order_by = null;

	/**
	 * The sort direction (DESC vs ASC).
	 *
	 * @var string|null
	 */
	private ?string $order = null;

	/**
	 * A query string to search for.
	 *
	 * @var string|null
	 */
	private ?string $search = null;

	/**
	 * Filter for summaries from this start date.
	 *
	 * @var string|null
	 */
	private ?string $start_date = null;

	/**
	 * Filter for summaries until this end date.
	 *
	 * @var string|null
	 */
	private ?string $end_date = null;

	/**
	 * Whether to include inactive customers in summaries.
	 *
	 * @var bool|null
	 */
	private ?bool $include_inactive = null;

	/**
	 * Whether to include deleted customers in summaries.
	 *
	 * @var bool|null
	 */
	private ?bool $include_deleted = null;

	/**
	 * Constructor.
	 *
	 * @param int $page The search results page to return.
	 * @param int $page_size The page size.
	 */
	public function __construct( int $page = 1, int $page_size = 100 ) {
		$this->page      = $page;
		$this->page_size = $page_size;
	}

	/**
	 * Set the page.
	 *
	 * @param int $page The search results page to return.
	 * @return self
	 */
	public function set_page( int $page ): self {
		$this->page = $page;
		return $this;
	}

	/**
	 * Set the page size.
	 *
	 * @param int $page_size The page size.
	 * @return self
	 */
	public function set_page_size( int $page_size ): self {
		$this->page_size = $page_size;
		return $this;
	}

	/**
	 * Set the order by.
	 *
	 * @param string $order_by A property to sort the results by.
	 * @return self
	 */
	public function set_order_by( string $order_by ): self {
		$this->order_by = $order_by;
		return $this;
	}

	/**
	 * Set the order direction.
	 *
	 * @param string $order The sort direction (DESC vs ASC).
	 * @return self
	 */
	public function set_order( string $order ): self {
		$this->order = $order;
		return $this;
	}

	/**
	 * Set the search query.
	 *
	 * @param string $search A query string to search for.
	 * @return self
	 */
	public function set_search( string $search ): self {
		$this->search = $search;
		return $this;
	}

	/**
	 * Set the start date.
	 *
	 * @param string $start_date Filter for summaries from this start date.
	 * @return self
	 */
	public function set_start_date( string $start_date ): self {
		$this->start_date = $start_date;
		return $this;
	}

	/**
	 * Set the end date.
	 *
	 * @param string $end_date Filter for summaries until this end date.
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
		$data = array(
			'page'     => $this->page,
			'pageSize' => $this->page_size,
		);

		if ( null !== $this->order_by ) {
			$data['orderBy'] = $this->order_by;
		}

		if ( null !== $this->order ) {
			$data['order'] = $this->order;
		}

		if ( null !== $this->search ) {
			$data['search'] = $this->search;
		}

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
