<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * ProductResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Products;

/**
 * Response object for product data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class ProductResponse {
	/**
	 * The product ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * The product title/name.
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The parent product ID (for variations).
	 *
	 * @var int|null
	 */
	private ?int $parent_product_id = null;

	/**
	 * The product SKU.
	 *
	 * @var string|null
	 */
	private ?string $sku = null;

	/**
	 * The Global Trade Item Number for the product.
	 *
	 * @var string|null
	 */
	private ?string $gtin = null;

	/**
	 * The Manufacturer Part Number for the product.
	 *
	 * @var string|null
	 */
	private ?string $mpn = null;

	/**
	 * The product description.
	 *
	 * @var string|null
	 */
	private ?string $description = null;

	/**
	 * The product price.
	 *
	 * @var float|null
	 */
	private ?float $price = null;

	/**
	 * The product sale price.
	 *
	 * @var float|null
	 */
	private ?float $sale_price = null;

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
	 * The product weight.
	 *
	 * @var float|null
	 */
	private ?float $weight = null;

	/**
	 * The unit of weight.
	 *
	 * @var string|null
	 */
	private ?string $weight_unit_type = null;

	/**
	 * The unit of length.
	 *
	 * @var string|null
	 */
	private ?string $length_unit_type = null;

	/**
	 * The shipping class.
	 *
	 * @var string|null
	 */
	private ?string $shipping_class = null;

	/**
	 * The tax class.
	 *
	 * @var string|null
	 */
	private ?string $tax_class = null;

	/**
	 * Whether the product can be added to scheduled orders.
	 *
	 * @var bool|null
	 */
	private ?bool $add_to_scheduled_order = null;

	/**
	 * Whether the product can be processed in scheduled orders.
	 *
	 * @var bool|null
	 */
	private ?bool $process_scheduled_order = null;

	/**
	 * The product availability status.
	 *
	 * @var string|null
	 */
	private ?string $availability = null;

	/**
	 * The product stock amount.
	 *
	 * @var int|null
	 */
	private ?int $stock = null;

	/**
	 * The lifetime value for the product.
	 *
	 * @var float|null
	 */
	private ?float $lifetime_value = null;

	/**
	 * Whether the product is active.
	 *
	 * @var bool|null
	 */
	private ?bool $active = null;

	/**
	 * Whether the product is valid.
	 *
	 * @var bool|null
	 */
	private ?bool $valid = null;

	/**
	 * The group IDs the product is assigned to.
	 *
	 * @var array|null
	 */
	private ?array $product_group_ids = null;

	/**
	 * The available frequencies for the product.
	 *
	 * @var array|null
	 */
	private ?array $available_frequencies = null;

	/**
	 * Array of key-value pairs of metadata.
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
	 * @param \stdClass $data The response data.
	 */
	public function __construct( \stdClass $data ) {
		$this->raw_data                = $data;
		$this->id                      = $data->id;
		$this->title                   = $data->title;
		$this->parent_product_id       = $data->parentProductId ?? null; // phpcs:ignore
		$this->sku                     = $data->sku ?? null;
		$this->gtin                    = $data->gtin ?? null;
		$this->mpn                     = $data->mpn ?? null;
		$this->description             = $data->description ?? null;
		$this->price                   = $data->price ?? null;
		$this->sale_price              = $data->salePrice ?? null; // phpcs:ignore
		$this->length                  = $data->length ?? null;
		$this->width                   = $data->width ?? null;
		$this->height                  = $data->height ?? null;
		$this->weight                  = $data->weight ?? null;
		$this->weight_unit_type        = $data->weightUnitType ?? null; // phpcs:ignore
		$this->length_unit_type        = $data->lengthUnitType ?? null; // phpcs:ignore
		$this->shipping_class          = $data->shippingClass ?? null; // phpcs:ignore
		$this->tax_class               = $data->taxClass ?? null; // phpcs:ignore
		$this->add_to_scheduled_order  = $data->addToScheduledOrder ?? null; // phpcs:ignore
		$this->process_scheduled_order = $data->processScheduledOrder ?? null; // phpcs:ignore
		$this->availability            = $data->availability ?? null;
		$this->stock                   = $data->stock ?? null;
		$this->lifetime_value          = $data->lifetimeValue ?? null; // phpcs:ignore
		$this->active                  = $data->active ?? null;
		$this->valid                   = $data->valid ?? null;
		$this->product_group_ids       = $data->productGroupIds ?? null; // phpcs:ignore
		$this->available_frequencies   = $data->availableFrequencies ?? null; // phpcs:ignore
		$this->metadata                = $data->metadata ?? null;
	}

	/**
	 * Get the product ID.
	 *
	 * @return int The product ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the product title/name.
	 *
	 * @return string The product title/name.
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * Get the parent product ID.
	 *
	 * @return int|null The parent product ID.
	 */
	public function get_parent_product_id(): ?int {
		return $this->parent_product_id;
	}

	/**
	 * Get the product SKU.
	 *
	 * @return string|null The product SKU.
	 */
	public function get_sku(): ?string {
		return $this->sku;
	}

	/**
	 * Get the Global Trade Item Number.
	 *
	 * @return string|null The Global Trade Item Number.
	 */
	public function get_gtin(): ?string {
		return $this->gtin;
	}

	/**
	 * Get the Manufacturer Part Number.
	 *
	 * @return string|null The Manufacturer Part Number.
	 */
	public function get_mpn(): ?string {
		return $this->mpn;
	}

	/**
	 * Get the product description.
	 *
	 * @return string|null The product description.
	 */
	public function get_description(): ?string {
		return $this->description;
	}

	/**
	 * Get the product price.
	 *
	 * @return float|null The product price.
	 */
	public function get_price(): ?float {
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
	 * Get the length unit type.
	 *
	 * @return string|null The length unit type.
	 */
	public function get_length_unit_type(): ?string {
		return $this->length_unit_type;
	}

	/**
	 * Get the shipping class.
	 *
	 * @return string|null The shipping class.
	 */
	public function get_shipping_class(): ?string {
		return $this->shipping_class;
	}

	/**
	 * Get the tax class.
	 *
	 * @return string|null The tax class.
	 */
	public function get_tax_class(): ?string {
		return $this->tax_class;
	}

	/**
	 * Get whether the product can be added to scheduled orders.
	 *
	 * @return bool|null Whether the product can be added to scheduled orders.
	 */
	public function get_add_to_scheduled_order(): ?bool {
		return $this->add_to_scheduled_order;
	}

	/**
	 * Get whether the product can be processed in scheduled orders.
	 *
	 * @return bool|null Whether the product can be processed in scheduled orders.
	 */
	public function get_process_scheduled_order(): ?bool {
		return $this->process_scheduled_order;
	}

	/**
	 * Get the product availability status.
	 *
	 * @return string|null The product availability status.
	 */
	public function get_availability(): ?string {
		return $this->availability;
	}

	/**
	 * Get the product stock amount.
	 *
	 * @return int|null The product stock amount.
	 */
	public function get_stock(): ?int {
		return $this->stock;
	}

	/**
	 * Get the lifetime value for the product.
	 *
	 * @return float|null The lifetime value for the product.
	 */
	public function get_lifetime_value(): ?float {
		return $this->lifetime_value;
	}

	/**
	 * Get whether the product is active.
	 *
	 * @return bool|null Whether the product is active.
	 */
	public function get_active(): ?bool {
		return $this->active;
	}

	/**
	 * Get whether the product is valid.
	 *
	 * @return bool|null Whether the product is valid.
	 */
	public function get_valid(): ?bool {
		return $this->valid;
	}

	/**
	 * Get the group IDs the product is assigned to.
	 *
	 * @return array|null The group IDs the product is assigned to.
	 */
	public function get_product_group_ids(): ?array {
		return $this->product_group_ids;
	}

	/**
	 * Get the available frequencies for the product.
	 *
	 * @return array|null The available frequencies for the product.
	 */
	public function get_available_frequencies(): ?array {
		return $this->available_frequencies;
	}

	/**
	 * Get the metadata for the product.
	 *
	 * @return array|null The metadata for the product.
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
