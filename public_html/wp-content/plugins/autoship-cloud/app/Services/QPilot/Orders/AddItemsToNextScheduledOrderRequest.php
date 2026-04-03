<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Add items to next scheduled order request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Request object for adding items to the next scheduled order.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class AddItemsToNextScheduledOrderRequest {
	/**
	 * The items to add to the next scheduled order.
	 *
	 * @var array
	 */
	private array $items = array();

	/**
	 * The frequency type to match.
	 *
	 * @var string|null
	 */
	private ?string $frequency_type = null;

	/**
	 * The frequency value to match.
	 *
	 * @var int|null
	 */
	private ?int $frequency = null;

	/**
	 * The status to match.
	 *
	 * @var string|null
	 */
	private ?string $status = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Add an item to the next scheduled order.
	 *
	 * @param int $product_id The product ID.
	 * @param int $quantity The quantity.
	 *
	 * @return self
	 */
	public function add_item( int $product_id, int $quantity ): self {
		$this->items[] = array(
			'productId' => $product_id,
			'quantity'  => $quantity,
		);

		return $this;
	}

	/**
	 * Add an item with additional properties.
	 *
	 * @param int   $product_id The product ID.
	 * @param int   $quantity The quantity.
	 * @param array $properties Additional properties for the item.
	 *
	 * @return self
	 */
	public function add_item_with_properties( int $product_id, int $quantity, array $properties ): self {
		$item = array_merge(
			array(
				'productId' => $product_id,
				'quantity'  => $quantity,
			),
			$properties
		);

		$this->items[] = $item;

		return $this;
	}

	/**
	 * Set the frequency type to match.
	 *
	 * @param string $frequency_type The frequency type.
	 *
	 * @return self
	 */
	public function set_frequency_type( string $frequency_type ): self {
		$this->frequency_type = $frequency_type;

		return $this;
	}

	/**
	 * Set the frequency value to match.
	 *
	 * @param int $frequency The frequency value.
	 *
	 * @return self
	 */
	public function set_frequency( int $frequency ): self {
		$this->frequency = $frequency;

		return $this;
	}

	/**
	 * Set the status to match.
	 *
	 * @param string $status The status.
	 *
	 * @return self
	 */
	public function set_status( string $status ): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * Get the items.
	 *
	 * @return array The items.
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
		$data = array(
			'items' => $this->items,
		);

		if ( null !== $this->frequency_type ) {
			$data['frequencyType'] = $this->frequency_type;
		}

		if ( null !== $this->frequency ) {
			$data['frequency'] = $this->frequency;
		}

		if ( null !== $this->status ) {
			$data['status'] = $this->status;
		}

		return $data;
	}
}
