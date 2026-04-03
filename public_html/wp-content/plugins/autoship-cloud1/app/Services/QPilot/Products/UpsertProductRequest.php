<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * UpsertProductRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Products;

/**
 * Request object for creating or updating a product.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class UpsertProductRequest {
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
	 * Whether to sync on update.
	 *
	 * @var bool|null
	 */
	private ?bool $sync_on_update = null;

	/**
	 * Constructor.
	 *
	 * @param int    $id    The product ID.
	 * @param string $title The product title/name.
	 */
	public function __construct( int $id, string $title ) {
		$this->id    = $id;
		$this->title = $title;
	}

	/**
	 * Set the parent product ID.
	 *
	 * @param int $parent_product_id The parent product ID.
	 * @return self
	 */
	public function set_parent_product_id( int $parent_product_id ): self {
		$this->parent_product_id = $parent_product_id;
		return $this;
	}

	/**
	 * Set the product SKU.
	 *
	 * @param string $sku The product SKU.
	 * @return self
	 */
	public function set_sku( string $sku ): self {
		$this->sku = $sku;
		return $this;
	}

	/**
	 * Set the Global Trade Item Number.
	 *
	 * @param string $gtin The Global Trade Item Number.
	 * @return self
	 */
	public function set_gtin( string $gtin ): self {
		$this->gtin = $gtin;
		return $this;
	}

	/**
	 * Set the Manufacturer Part Number.
	 *
	 * @param string $mpn The Manufacturer Part Number.
	 * @return self
	 */
	public function set_mpn( string $mpn ): self {
		$this->mpn = $mpn;
		return $this;
	}

	/**
	 * Set the product description.
	 *
	 * @param string $description The product description.
	 * @return self
	 */
	public function set_description( string $description ): self {
		$this->description = $description;
		return $this;
	}

	/**
	 * Set the product price.
	 *
	 * @param float $price The product price.
	 * @return self
	 */
	public function set_price( float $price ): self {
		$this->price = $price;
		return $this;
	}

	/**
	 * Set the product sale price.
	 *
	 * @param float $sale_price The product sale price.
	 * @return self
	 */
	public function set_sale_price( float $sale_price ): self {
		$this->sale_price = $sale_price;
		return $this;
	}

	/**
	 * Set the product dimensions.
	 *
	 * @param float $length The product length.
	 * @param float $width  The product width.
	 * @param float $height The product height.
	 * @return self
	 */
	public function set_dimensions( float $length, float $width, float $height ): self {
		$this->length = $length;
		$this->width  = $width;
		$this->height = $height;
		return $this;
	}

	/**
	 * Set the product weight.
	 *
	 * @param float $weight The product weight.
	 * @return self
	 */
	public function set_weight( float $weight ): self {
		$this->weight = $weight;
		return $this;
	}

	/**
	 * Set the weight unit type.
	 *
	 * @param string $weight_unit_type The weight unit type ('Pound', 'Ounce', 'Kilogram', 'Gram').
	 * @return self
	 */
	public function set_weight_unit_type( string $weight_unit_type ): self {
		$this->weight_unit_type = $weight_unit_type;
		return $this;
	}

	/**
	 * Set the length unit type.
	 *
	 * @param string $length_unit_type The length unit type ('Inch', 'Foot', 'Yard', 'Milimeter', 'Centimeter', 'Meter').
	 * @return self
	 */
	public function set_length_unit_type( string $length_unit_type ): self {
		$this->length_unit_type = $length_unit_type;
		return $this;
	}

	/**
	 * Set the shipping class.
	 *
	 * @param string $shipping_class The shipping class.
	 * @return self
	 */
	public function set_shipping_class( string $shipping_class ): self {
		$this->shipping_class = $shipping_class;
		return $this;
	}

	/**
	 * Set the tax class.
	 *
	 * @param string $tax_class The tax class.
	 * @return self
	 */
	public function set_tax_class( string $tax_class ): self {
		$this->tax_class = $tax_class;
		return $this;
	}

	/**
	 * Set whether the product can be added to scheduled orders.
	 *
	 * @param bool $add_to_scheduled_order Whether the product can be added to scheduled orders.
	 * @return self
	 */
	public function set_add_to_scheduled_order( bool $add_to_scheduled_order ): self {
		$this->add_to_scheduled_order = $add_to_scheduled_order;
		return $this;
	}

	/**
	 * Set whether the product can be processed in scheduled orders.
	 *
	 * @param bool $process_scheduled_order Whether the product can be processed in scheduled orders.
	 * @return self
	 */
	public function set_process_scheduled_order( bool $process_scheduled_order ): self {
		$this->process_scheduled_order = $process_scheduled_order;
		return $this;
	}

	/**
	 * Set the product availability status.
	 *
	 * @param string $availability The availability status ('Undefined', 'InStock', 'OutOfStock', 'Preorder').
	 * @return self
	 */
	public function set_availability( string $availability ): self {
		$this->availability = $availability;
		return $this;
	}

	/**
	 * Set the product stock amount.
	 *
	 * @param int $stock The stock amount.
	 * @return self
	 */
	public function set_stock( int $stock ): self {
		$this->stock = $stock;
		return $this;
	}

	/**
	 * Set the lifetime value for the product.
	 *
	 * @param float $lifetime_value The lifetime value.
	 * @return self
	 */
	public function set_lifetime_value( float $lifetime_value ): self {
		$this->lifetime_value = $lifetime_value;
		return $this;
	}

	/**
	 * Set whether the product is active.
	 *
	 * @param bool $active Whether the product is active.
	 * @return self
	 */
	public function set_active( bool $active ): self {
		$this->active = $active;
		return $this;
	}

	/**
	 * Set whether the product is valid.
	 *
	 * @param bool $valid Whether the product is valid.
	 * @return self
	 */
	public function set_valid( bool $valid ): self {
		$this->valid = $valid;
		return $this;
	}

	/**
	 * Set the group IDs the product is assigned to.
	 *
	 * @param array $product_group_ids The group IDs.
	 * @return self
	 */
	public function set_product_group_ids( array $product_group_ids ): self {
		$this->product_group_ids = $product_group_ids;
		return $this;
	}

	/**
	 * Set the available frequencies for the product.
	 *
	 * @param array $available_frequencies The available frequencies.
	 * @return self
	 */
	public function set_available_frequencies( array $available_frequencies ): self {
		$this->available_frequencies = $available_frequencies;
		return $this;
	}

	/**
	 * Set the metadata for the product.
	 *
	 * @param array $metadata The metadata.
	 * @return self
	 */
	public function set_metadata( array $metadata ): self {
		$this->metadata = $metadata;
		return $this;
	}

	/**
	 * Set whether to sync on update.
	 *
	 * @param bool $sync_on_update Whether to sync on update.
	 * @return self
	 */
	public function set_sync_on_update( bool $sync_on_update ): self {
		$this->sync_on_update = $sync_on_update;
		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'id'    => $this->id,
			'title' => $this->title,
		);

		if ( null !== $this->parent_product_id ) {
			$data['parentProductId'] = $this->parent_product_id;
		}

		if ( null !== $this->sku ) {
			$data['sku'] = $this->sku;
		}

		if ( null !== $this->gtin ) {
			$data['gtin'] = $this->gtin;
		}

		if ( null !== $this->mpn ) {
			$data['mpn'] = $this->mpn;
		}

		if ( null !== $this->description ) {
			$data['description'] = $this->description;
		}

		if ( null !== $this->price ) {
			$data['price'] = $this->price;
		}

		if ( null !== $this->sale_price ) {
			$data['salePrice'] = $this->sale_price;
		}

		if ( null !== $this->length ) {
			$data['length'] = $this->length;
		}

		if ( null !== $this->width ) {
			$data['width'] = $this->width;
		}

		if ( null !== $this->height ) {
			$data['height'] = $this->height;
		}

		if ( null !== $this->weight ) {
			$data['weight'] = $this->weight;
		}

		if ( null !== $this->weight_unit_type ) {
			$data['weightUnitType'] = $this->weight_unit_type;
		}

		if ( null !== $this->length_unit_type ) {
			$data['lengthUnitType'] = $this->length_unit_type;
		}

		if ( null !== $this->shipping_class ) {
			$data['shippingClass'] = $this->shipping_class;
		}

		if ( null !== $this->tax_class ) {
			$data['taxClass'] = $this->tax_class;
		}

		if ( null !== $this->add_to_scheduled_order ) {
			$data['addToScheduledOrder'] = $this->add_to_scheduled_order;
		}

		if ( null !== $this->process_scheduled_order ) {
			$data['processScheduledOrder'] = $this->process_scheduled_order;
		}

		if ( null !== $this->availability ) {
			$data['availability'] = $this->availability;
		}

		if ( null !== $this->stock ) {
			$data['stock'] = $this->stock;
		}

		if ( null !== $this->lifetime_value ) {
			$data['lifetimeValue'] = $this->lifetime_value;
		}

		if ( null !== $this->active ) {
			$data['active'] = $this->active;
		}

		if ( null !== $this->valid ) {
			$data['valid'] = $this->valid;
		}

		if ( null !== $this->product_group_ids ) {
			$data['productGroupIds'] = $this->product_group_ids;
		}

		if ( null !== $this->available_frequencies ) {
			$data['availableFrequencies'] = $this->available_frequencies;
		}

		if ( null !== $this->metadata ) {
			$data['metadata'] = $this->metadata;
		}

		if ( null !== $this->sync_on_update ) {
			$data['syncOnUpdate'] = $this->sync_on_update;
		}

		return $data;
	}
}
