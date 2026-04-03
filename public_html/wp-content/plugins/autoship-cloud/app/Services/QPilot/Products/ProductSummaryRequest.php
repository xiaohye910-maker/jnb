<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * ProductSummaryRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Products;

/**
 * Request object for retrieving product summaries.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class ProductSummaryRequest {
	/**
	 * The product IDs to retrieve summaries for.
	 *
	 * @var array<int>
	 */
	private array $product_ids;

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
	 * A product property to sort the results by.
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
	 * Query for a specific product.
	 *
	 * @var int|null
	 */
	private ?int $external_id = null;

	/**
	 * A group id to search for.
	 *
	 * @var int|null
	 */
	private ?int $product_group_id = null;

	/**
	 * True for Active products.
	 *
	 * @var bool|null
	 */
	private ?bool $active = null;

	/**
	 * True for valid products.
	 *
	 * @var bool|null
	 */
	private ?bool $valid = null;

	/**
	 * Filter for all products created on or after a date.
	 *
	 * @var string|null
	 */
	private ?string $start_date = null;

	/**
	 * Filter for all products created before a date.
	 *
	 * @var string|null
	 */
	private ?string $end_date = null;

	/**
	 * Constructor.
	 *
	 * @param array<int> $product_ids The product IDs to retrieve summaries for.
	 */
	public function __construct( array $product_ids ) {
		$this->product_ids = $product_ids;
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
	 * @param string $order_by A product property to sort the results by.
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
	 * Set the external ID.
	 *
	 * @param int $external_id Query for a specific product.
	 * @return self
	 */
	public function set_external_id( int $external_id ): self {
		$this->external_id = $external_id;
		return $this;
	}

	/**
	 * Set the product group ID.
	 *
	 * @param int $product_group_id A group id to search for.
	 * @return self
	 */
	public function set_product_group_id( int $product_group_id ): self {
		$this->product_group_id = $product_group_id;
		return $this;
	}

	/**
	 * Set the active flag.
	 *
	 * @param bool $active True for Active products.
	 * @return self
	 */
	public function set_active( bool $active ): self {
		$this->active = $active;
		return $this;
	}

	/**
	 * Set the valid flag.
	 *
	 * @param bool $valid True for valid products.
	 * @return self
	 */
	public function set_valid( bool $valid ): self {
		$this->valid = $valid;
		return $this;
	}

	/**
	 * Set the start date.
	 *
	 * @param string $start_date Filter for all products created on or after a date.
	 * @return self
	 */
	public function set_start_date( string $start_date ): self {
		$this->start_date = $start_date;
		return $this;
	}

	/**
	 * Set the end date.
	 *
	 * @param string $end_date Filter for all products created before a date.
	 * @return self
	 */
	public function set_end_date( string $end_date ): self {
		$this->end_date = $end_date;
		return $this;
	}

	/**
	 * Get the product IDs.
	 *
	 * @return array<int> The product IDs.
	 */
	public function get_product_ids(): array {
		return $this->product_ids;
	}

	/**
	 * Convert the request to an array for API parameters.
	 *
	 * @return array The request as an array for API parameters.
	 */
	public function to_params_array(): array {
		$params = array(
			'page'     => $this->page,
			'pageSize' => $this->page_size,
		);

		if ( null !== $this->order_by ) {
			$params['orderBy'] = $this->order_by;
		}

		if ( null !== $this->order ) {
			$params['order'] = $this->order;
		}

		if ( null !== $this->search ) {
			$params['search'] = $this->search;
		}

		if ( null !== $this->external_id ) {
			$params['externalId'] = $this->external_id;
		}

		if ( null !== $this->product_group_id ) {
			$params['productGroupId'] = $this->product_group_id;
		}

		if ( null !== $this->active ) {
			$params['active'] = $this->active;
		}

		if ( null !== $this->valid ) {
			$params['valid'] = $this->valid;
		}

		if ( null !== $this->start_date ) {
			$params['startDate'] = $this->start_date;
		}

		if ( null !== $this->end_date ) {
			$params['endDate'] = $this->end_date;
		}

		return $params;
	}

	/**
	 * Convert the request to an array for API body.
	 *
	 * @return array The request as an array for API body.
	 */
	public function to_body_array(): array {
		return array(
			'externalIds' => $this->product_ids,
		);
	}
}
