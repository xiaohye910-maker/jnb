<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * ProductSummaryResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Products;

/**
 * Response object for product summaries data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class ProductSummaryResponse {
	/**
	 * The total number of items.
	 *
	 * @var int
	 */
	private int $total_count;

	/**
	 * The current page.
	 *
	 * @var int
	 */
	private int $page;

	/**
	 * The page size.
	 *
	 * @var int
	 */
	private int $page_size;

	/**
	 * The product summaries.
	 *
	 * @var array
	 */
	private array $items;

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
		$this->raw_data    = $data;
		$this->total_count = $data->totalCount ?? 0; // phpcs:ignore
		$this->page        = $data->page ?? 1;
		$this->page_size   = $data->pageSize ?? 0; // phpcs:ignore
		$this->items       = $data->items ?? array();
	}

	/**
	 * Get the total count.
	 *
	 * @return int The total count.
	 */
	public function get_total_count(): int {
		return $this->total_count;
	}

	/**
	 * Get the current page.
	 *
	 * @return int The current page.
	 */
	public function get_page(): int {
		return $this->page;
	}

	/**
	 * Get the page size.
	 *
	 * @return int The page size.
	 */
	public function get_page_size(): int {
		return $this->page_size;
	}

	/**
	 * Get the product summaries.
	 *
	 * @return array The product summaries.
	 */
	public function get_items(): array {
		return $this->items;
	}

	/**
	 * Get a specific product summary by index.
	 *
	 * @param int $index The index of the product summary.
	 * @return \stdClass|null The product summary or null if not found.
	 */
	public function get_item( int $index ): ?\stdClass {
		return $this->items[ $index ] ?? null;
	}

	/**
	 * Get a product summary by product ID.
	 *
	 * @param int $product_id The product ID.
	 * @return \stdClass|null The product summary or null if not found.
	 */
	public function get_item_by_id( int $product_id ): ?\stdClass {
		foreach ( $this->items as $item ) {
			if ( isset( $item->id ) && $product_id === $item->id ) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * Get the total number of pages.
	 *
	 * @return int The total number of pages.
	 */
	public function get_total_pages(): int {
		if ( 0 === $this->page_size ) {
			return 0;
		}

		return (int) ceil( $this->total_count / $this->page_size );
	}

	/**
	 * Check if there is a next page.
	 *
	 * @return bool True if there is a next page, false otherwise.
	 */
	public function has_next_page(): bool {
		return $this->page < $this->get_total_pages();
	}

	/**
	 * Check if there is a previous page.
	 *
	 * @return bool True if there is a previous page, false otherwise.
	 */
	public function has_previous_page(): bool {
		return $this->page > 1;
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
