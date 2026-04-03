<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Create scheduled order items request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Request object for creating multiple scheduled order items.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CreateScheduledOrderItemsRequest {
	/**
	 * Array of scheduled order items.
	 *
	 * @var array<CreateScheduledOrderItemRequest>
	 */
	private array $items = array();

	/**
	 * Constructor.
	 *
	 * @param array<CreateScheduledOrderItemRequest> $items Optional. Initial items.
	 */
	public function __construct( array $items = array() ) {
		$this->items = $items;
	}

	/**
	 * Add an item to the request.
	 *
	 * @param CreateScheduledOrderItemRequest $item The item to add.
	 *
	 * @return self
	 */
	public function add_item( CreateScheduledOrderItemRequest $item ): self {
		$this->items[] = $item;

		return $this;
	}

	/**
	 * Get all items.
	 *
	 * @return array<CreateScheduledOrderItemRequest> The items.
	 */
	public function get_items(): array {
		return $this->items;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$items = array();

		foreach ( $this->items as $item ) {
			$items[] = $item->to_array();
		}

		return $items;
	}
}
