<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Scheduled order item response class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

use stdClass;

/**
 * Response object for scheduled order item data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class ScheduledOrderItemResponse {
	/**
	 * The item ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * The order ID.
	 *
	 * @var int
	 */
	private int $order_id;

	/**
	 * The product ID.
	 *
	 * @var int
	 */
	private int $product_id;

	/**
	 * The product title.
	 *
	 * @var string
	 */
	private string $product_title;

	/**
	 * The product SKU.
	 *
	 * @var string|null
	 */
	private ?string $product_sku = null;

	/**
	 * The product price.
	 *
	 * @var float
	 */
	private float $price;

	/**
	 * The product sale price.
	 *
	 * @var float|null
	 */
	private ?float $sale_price = null;

	/**
	 * The product quantity.
	 *
	 * @var int
	 */
	private int $quantity;

	/**
	 * The product weight.
	 *
	 * @var float|null
	 */
	private ?float $weight = null;

	/**
	 * The weight unit type.
	 *
	 * @var string|null
	 */
	private ?string $weight_unit_type = null;

	/**
	 * The product length.
	 *
	 * @var float|null
	 */
	private ?float $length = null;

	/**
	 * The product width.
	 *
	 * @var float|null
	 */
	private ?float $width = null;

	/**
	 * The product height.
	 *
	 * @var float|null
	 */
	private ?float $height = null;

	/**
	 * The length unit type.
	 *
	 * @var string|null
	 */
	private ?string $length_unit_type = null;

	/**
	 * The product tax class.
	 *
	 * @var string|null
	 */
	private ?string $tax_class = null;

	/**
	 * The product shipping class.
	 *
	 * @var string|null
	 */
	private ?string $shipping_class = null;

	/**
	 * The product metadata.
	 *
	 * @var array|null
	 */
	private ?array $metadata = null;

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
		$this->raw_data         = $data;
		$this->id               = $data->id;
		$this->order_id         = $data->orderId; // phpcs:ignore
		$this->product_id       = $data->productId; // phpcs:ignore
		$this->product_title    = $data->productTitle; // phpcs:ignore
		$this->product_sku      = $data->productSku ?? null; // phpcs:ignore
		$this->price            = $data->price;
		$this->sale_price       = $data->salePrice ?? null; // phpcs:ignore
		$this->quantity         = $data->quantity;
		$this->weight           = $data->weight ?? null;
		$this->weight_unit_type = $data->weightUnitType ?? null; // phpcs:ignore
		$this->length           = $data->length ?? null;
		$this->width            = $data->width ?? null;
		$this->height           = $data->height ?? null;
		$this->length_unit_type = $data->lengthUnitType ?? null; // phpcs:ignore
		$this->tax_class        = $data->taxClass ?? null; // phpcs:ignore
		$this->shipping_class   = $data->shippingClass ?? null; // phpcs:ignore
		$this->metadata         = $data->metadata ?? null;
	}

	/**
	 * Get the item ID.
	 *
	 * @return int The item ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the order ID.
	 *
	 * @return int The order ID.
	 */
	public function get_order_id(): int {
		return $this->order_id;
	}

	/**
	 * Get the product ID.
	 *
	 * @return int The product ID.
	 */
	public function get_product_id(): int {
		return $this->product_id;
	}

	/**
	 * Get the product title.
	 *
	 * @return string The product title.
	 */
	public function get_product_title(): string {
		return $this->product_title;
	}

	/**
	 * Get the product SKU.
	 *
	 * @return string|null The product SKU.
	 */
	public function get_product_sku(): ?string {
		return $this->product_sku;
	}

	/**
	 * Get the product price.
	 *
	 * @return float The product price.
	 */
	public function get_price(): float {
		return $this->price;
	}

	/**
	 * Get the product sale price.
	 *
	 * @return float|null The product sale price.
	 */
	public function get_sale_price(): ?float {
		return $this->sale_price;
	}

	/**
	 * Get the product quantity.
	 *
	 * @return int The product quantity.
	 */
	public function get_quantity(): int {
		return $this->quantity;
	}

	/**
	 * Get the product weight.
	 *
	 * @return float|null The product weight.
	 */
	public function get_weight(): ?float {
		return $this->weight;
	}

	/**
	 * Get the weight unit type.
	 *
	 * @return string|null The weight unit type.
	 */
	public function get_weight_unit_type(): ?string {
		return $this->weight_unit_type;
	}

	/**
	 * Get the product length.
	 *
	 * @return float|null The product length.
	 */
	public function get_length(): ?float {
		return $this->length;
	}

	/**
	 * Get the product width.
	 *
	 * @return float|null The product width.
	 */
	public function get_width(): ?float {
		return $this->width;
	}

	/**
	 * Get the product height.
	 *
	 * @return float|null The product height.
	 */
	public function get_height(): ?float {
		return $this->height;
	}

	/**
	 * Get the length unit type.
	 *
	 * @return string|null The length unit type.
	 */
	public function get_length_unit_type(): ?string {
		return $this->length_unit_type;
	}

	/**
	 * Get the product tax class.
	 *
	 * @return string|null The product tax class.
	 */
	public function get_tax_class(): ?string {
		return $this->tax_class;
	}

	/**
	 * Get the product shipping class.
	 *
	 * @return string|null The product shipping class.
	 */
	public function get_shipping_class(): ?string {
		return $this->shipping_class;
	}

	/**
	 * Get the product metadata.
	 *
	 * @return array|null The product metadata.
	 */
	public function get_metadata(): ?array {
		return $this->metadata;
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
