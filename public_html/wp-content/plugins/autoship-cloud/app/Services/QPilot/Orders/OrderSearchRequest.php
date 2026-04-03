<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Order search request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Request object for searching orders.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class OrderSearchRequest {
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
	 * Array of status names to search for.
	 *
	 * @var array<string>|null
	 */
	private ?array $status_names = null;

	/**
	 * Array of order metadata keys to search for.
	 *
	 * @var array<string>|null
	 */
	private ?array $metadata_key = null;

	/**
	 * Array of order metadata values to search for.
	 *
	 * @var array<string>|null
	 */
	private ?array $metadata_value = null;

	/**
	 * A query string to search for.
	 *
	 * @var string|null
	 */
	private ?string $search = null;

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
	 *
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
	 *
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
	 *
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
	 *
	 * @return self
	 */
	public function set_order( string $order ): self {
		$this->order = $order;

		return $this;
	}

	/**
	 * Set the status names.
	 *
	 * @param array<string> $status_names Array of status names to search for.
	 *
	 * @return self
	 */
	public function set_status_names( array $status_names ): self {
		$this->status_names = $status_names;

		return $this;
	}

	/**
	 * Set the metadata keys.
	 *
	 * @param array<string> $metadata_key Array of order metadata keys to search for.
	 *
	 * @return self
	 */
	public function set_metadata_key( array $metadata_key ): self {
		$this->metadata_key = $metadata_key;

		return $this;
	}

	/**
	 * Set the metadata values.
	 *
	 * @param array<string> $metadata_value Array of order metadata values to search for.
	 *
	 * @return self
	 */
	public function set_metadata_value( array $metadata_value ): self {
		$this->metadata_value = $metadata_value;

		return $this;
	}

	/**
	 * Set the search query.
	 *
	 * @param string $search A query string to search for.
	 *
	 * @return self
	 */
	public function set_search( string $search ): self {
		$this->search = $search;

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

		if ( null !== $this->status_names ) {
			$data['statusNames'] = $this->status_names;
		}

		if ( null !== $this->metadata_key ) {
			$data['metadataKey'] = $this->metadata_key;
		}

		if ( null !== $this->metadata_value ) {
			$data['metadataValue'] = $this->metadata_value;
		}

		if ( null !== $this->search ) {
			$data['search'] = $this->search;
		}

		return $data;
	}
}
