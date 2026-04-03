<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Options Request Item.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Carriers;

/**
 * Represents an item for the Nextime shipping options request.
 */
class ShippingOptionsRequestItem {

	/**
	 * The item id.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * The product id.
	 *
	 * @var string
	 */
	private string $product_id;

	/**
	 * The quantity.
	 *
	 * @var int
	 */
	private int $quantity;

	/**
	 * The regular price.
	 *
	 * @var float
	 */
	private float $regular_price;

	/**
	 * The sale price.
	 *
	 * @var float
	 */
	private float $sale_price;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id            = 1;
		$this->product_id    = '';
		$this->quantity      = 0;
		$this->regular_price = 0.0;
		$this->sale_price    = 0.0;
	}

	/**
	 * Gets the id.
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Gets the product id.
	 *
	 * @return string
	 */
	public function get_product_id(): string {
		return $this->product_id;
	}

	/**
	 * Gets the quantity.
	 *
	 * @return int
	 */
	public function get_quantity(): int {
		return $this->quantity;
	}

	/**
	 * Gets the regular price.
	 *
	 * @return float
	 */
	public function get_regular_price(): float {
		return $this->regular_price;
	}

	/**
	 * Gets the sale price.
	 *
	 * @return float
	 */
	public function get_sale_price(): float {
		return $this->sale_price;
	}

	/**
	 * Sets the id.
	 *
	 * @param int $id The id of the line item.
	 *
	 * @return void
	 */
	public function set_id( int $id ): void {
		$this->id = $id;
	}

	/**
	 * Sets the product id.
	 *
	 * @param string $product_id The product id of the line item.
	 *
	 * @return void
	 */
	public function set_product_id( string $product_id ): void {
		$this->product_id = $product_id;
	}

	/**
	 * Sets the quantity.
	 *
	 * @param int $quantity The quantity of the line item.
	 *
	 * @return void
	 */
	public function set_quantity( int $quantity ): void {
		$this->quantity = $quantity;
	}

	/**
	 * Sets the regular price.
	 *
	 * @param float $regular_price The regular price of the line item.
	 *
	 * @return void
	 */
	public function set_regular_price( float $regular_price ): void {
		$this->regular_price = $regular_price;
	}

	/**
	 * Sets the sale price.
	 *
	 * @param float $sale_price The sale price of the line item.
	 *
	 * @return void
	 */
	public function set_sale_price( float $sale_price ): void {
		$this->sale_price = $sale_price;
	}
}
