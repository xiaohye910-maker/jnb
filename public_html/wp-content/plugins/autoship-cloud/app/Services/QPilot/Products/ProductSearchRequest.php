<?php  // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Products;

/**
 * Product search request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class ProductSearchRequest {
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
	private ?int $group_id = null;

	/**
	 * An array of product ids to search for.
	 *
	 * @var array<int>|null
	 */
	private ?array $product_ids = null;

	/**
	 * An array of metadata keys to search for.
	 *
	 * @var array<string>|null
	 */
	private ?array $metadata_key = null;

	/**
	 * An array of metadata values to search for.
	 *
	 * @var array<string>|null
	 */
	private ?array $metadata_value = null;

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
	 * Set the group ID.
	 *
	 * @param int $group_id A group id to search for.
	 * @return self
	 */
	public function set_group_id( int $group_id ): self {
		$this->group_id = $group_id;
		return $this;
	}

	/**
	 * Set the product IDs.
	 *
	 * @param array<int> $product_ids An array of product ids to search for.
	 * @return self
	 */
	public function set_product_ids( array $product_ids ): self {
		$this->product_ids = $product_ids;
		return $this;
	}

	/**
	 * Set the metadata keys.
	 *
	 * @param array<string> $metadata_key An array of metadata keys to search for.
	 * @return self
	 */
	public function set_metadata_key( array $metadata_key ): self {
		$this->metadata_key = $metadata_key;
		return $this;
	}

	/**
	 * Set the metadata values.
	 *
	 * @param array<string> $metadata_value An array of metadata values to search for.
	 * @return self
	 */
	public function set_metadata_value( array $metadata_value ): self {
		$this->metadata_value = $metadata_value;
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

		if ( null !== $this->external_id ) {
			$data['externalId'] = $this->external_id;
		}

		if ( null !== $this->group_id ) {
			$data['groupId'] = $this->group_id;
		}

		if ( null !== $this->product_ids ) {
			$data['productIds'] = $this->product_ids;
		}

		if ( null !== $this->metadata_key ) {
			$data['metadataKey'] = $this->metadata_key;
		}

		if ( null !== $this->metadata_value ) {
			$data['metadataValue'] = $this->metadata_value;
		}

		if ( null !== $this->active ) {
			$data['active'] = $this->active;
		}

		if ( null !== $this->valid ) {
			$data['valid'] = $this->valid;
		}

		if ( null !== $this->start_date ) {
			$data['startDate'] = $this->start_date;
		}

		if ( null !== $this->end_date ) {
			$data['endDate'] = $this->end_date;
		}

		return $data;
	}
}
