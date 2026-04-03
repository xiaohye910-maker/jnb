<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Create scheduled order item request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Request object for creating a scheduled order item.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CreateScheduledOrderItemRequest {
	/**
	 * The product ID.
	 *
	 * @var int|null
	 */
	private ?int $product_id = null;

	/**
	 * The product title.
	 *
	 * @var string|null
	 */
	private ?string $product_title = null;

	/**
	 * The product SKU.
	 *
	 * @var string|null
	 */
	private ?string $product_sku = null;

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
	 * The product quantity.
	 *
	 * @var int|null
	 */
	private ?int $quantity = null;

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
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Set the product ID.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return self
	 */
	public function set_product_id( int $product_id ): self {
		$this->product_id = $product_id;

		return $this;
	}

	/**
	 * Set the product title.
	 *
	 * @param string $product_title The product title.
	 *
	 * @return self
	 */
	public function set_product_title( string $product_title ): self {
		$this->product_title = $product_title;

		return $this;
	}

	/**
	 * Set the product SKU.
	 *
	 * @param string $product_sku The product SKU.
	 *
	 * @return self
	 */
	public function set_product_sku( string $product_sku ): self {
		$this->product_sku = $product_sku;

		return $this;
	}

	/**
	 * Set the product price.
	 *
	 * @param float $price The product price.
	 *
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
	 *
	 * @return self
	 */
	public function set_sale_price( float $sale_price ): self {
		$this->sale_price = $sale_price;

		return $this;
	}

	/**
	 * Set the product quantity.
	 *
	 * @param int $quantity The product quantity.
	 *
	 * @return self
	 */
	public function set_quantity( int $quantity ): self {
		$this->quantity = $quantity;

		return $this;
	}

	/**
	 * Set the product weight.
	 *
	 * @param float $weight The product weight.
	 *
	 * @return self
	 */
	public function set_weight( float $weight ): self {
		$this->weight = $weight;

		return $this;
	}

	/**
	 * Set the weight unit type.
	 *
	 * @param string $weight_unit_type The weight unit type.
	 *
	 * @return self
	 */
	public function set_weight_unit_type( string $weight_unit_type ): self {
		$this->weight_unit_type = $weight_unit_type;

		return $this;
	}

	/**
	 * Set the product dimensions.
	 *
	 * @param float $length The product length.
	 * @param float $width The product width.
	 * @param float $height The product height.
	 *
	 * @return self
	 */
	public function set_dimensions( float $length, float $width, float $height ): self {
		$this->length = $length;
		$this->width  = $width;
		$this->height = $height;

		return $this;
	}

	/**
	 * Set the length unit type.
	 *
	 * @param string $length_unit_type The length unit type.
	 *
	 * @return self
	 */
	public function set_length_unit_type( string $length_unit_type ): self {
		$this->length_unit_type = $length_unit_type;

		return $this;
	}

	/**
	 * Set the product tax class.
	 *
	 * @param string $tax_class The product tax class.
	 *
	 * @return self
	 */
	public function set_tax_class( string $tax_class ): self {
		$this->tax_class = $tax_class;

		return $this;
	}

	/**
	 * Set the product shipping class.
	 *
	 * @param string $shipping_class The product shipping class.
	 *
	 * @return self
	 */
	public function set_shipping_class( string $shipping_class ): self {
		$this->shipping_class = $shipping_class;

		return $this;
	}

	/**
	 * Set the product metadata.
	 *
	 * @param array $metadata The product metadata.
	 *
	 * @return self
	 */
	public function set_metadata( array $metadata ): self {
		$this->metadata = $metadata;

		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array();

		if ( null !== $this->product_id ) {
			$data['productId'] = $this->product_id;
		}

		if ( null !== $this->product_title ) {
			$data['productTitle'] = $this->product_title;
		}

		if ( null !== $this->product_sku ) {
			$data['productSku'] = $this->product_sku;
		}

		if ( null !== $this->price ) {
			$data['price'] = $this->price;
		}

		if ( null !== $this->sale_price ) {
			$data['salePrice'] = $this->sale_price;
		}

		if ( null !== $this->quantity ) {
			$data['quantity'] = $this->quantity;
		}

		if ( null !== $this->weight ) {
			$data['weight'] = $this->weight;
		}

		if ( null !== $this->weight_unit_type ) {
			$data['weightUnitType'] = $this->weight_unit_type;
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

		if ( null !== $this->length_unit_type ) {
			$data['lengthUnitType'] = $this->length_unit_type;
		}

		if ( null !== $this->tax_class ) {
			$data['taxClass'] = $this->tax_class;
		}

		if ( null !== $this->shipping_class ) {
			$data['shippingClass'] = $this->shipping_class;
		}

		if ( null !== $this->metadata ) {
			$data['metadata'] = $this->metadata;
		}

		return $data;
	}
}
