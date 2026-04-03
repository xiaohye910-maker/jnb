<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Products;

/**
 * Batch product request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class BatchProductRequest {
	/**
	 * Array of product requests.
	 *
	 * @var array<UpsertProductRequest>
	 */
	private array $products = array();

	/**
	 * Constructor.
	 *
	 * @param array<UpsertProductRequest> $products Array of product requests.
	 */
	public function __construct( array $products = array() ) {
		$this->products = $products;
	}

	/**
	 * Add a product request.
	 *
	 * @param UpsertProductRequest $product The product request.
	 * @return self
	 */
	public function add_product( UpsertProductRequest $product ): self {
		$this->products[] = $product;
		return $this;
	}

	/**
	 * Get the products.
	 *
	 * @return array<UpsertProductRequest> Array of product requests.
	 */
	public function get_products(): array {
		return $this->products;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$products = array();
		foreach ( $this->products as $product ) {
			$products[] = $product->to_array();
		}
		return array( 'products' => $products );
	}
}
