<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Product Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

use Autoship\Services\QPilot\Common\BatchOperationResponse;
use Autoship\Services\QPilot\Products\BatchProductRequest;
use Autoship\Services\QPilot\Products\ProductSearchRequest;
use Autoship\Services\QPilot\Products\ProductSummaryRequest;
use Autoship\Services\QPilot\Products\ProductSummaryResponse;
use Autoship\Services\QPilot\Products\UpdateProductAvailabilityRequest;
use Autoship\Services\QPilot\Products\UpsertProductRequest;
use Autoship\Services\QPilot\Products\ProductResponse;

/**
 * Interface for product management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface ProductManagementInterface {
	/**
	 * Get a product by ID.
	 *
	 * @param int $product_id The product ID.
	 * @return ProductResponse The product object.
	 */
	public function get_product( int $product_id ): ProductResponse;

	/**
	 * Get products with typed filtering parameters.
	 *
	 * @param ProductSearchRequest $request The search parameters.
	 * @return array<ProductResponse> An array of ProductResponse objects.
	 */
	public function get_products( ProductSearchRequest $request ): array;

	/**
	 * Create or update a product.
	 *
	 * @param UpsertProductRequest $request The product data.
	 * @return ProductResponse The created/updated product object.
	 */
	public function upsert_product( UpsertProductRequest $request ): ProductResponse;

	/**
	 * Delete a product.
	 *
	 * @param int $product_id The product ID.
	 * @return bool True on success.
	 */
	public function delete_product( int $product_id ): bool;

	/**
	 * Batch create or update products.
	 *
	 * @param BatchProductRequest $request The batch product request.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_upsert_products( BatchProductRequest $request ): BatchOperationResponse;

	/**
	 * Batch delete products.
	 *
	 * @param array<int> $product_ids Array of product IDs to delete.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_delete_products( array $product_ids ): BatchOperationResponse;

	/**
	 * Get product summaries.
	 *
	 * @param ProductSummaryRequest $request The request containing product IDs and optional parameters.
	 * @return ProductSummaryResponse The product summaries response.
	 */
	public function get_products_summary( ProductSummaryRequest $request ): ProductSummaryResponse;

	/**
	 * Update product availability.
	 *
	 * @param int                              $product_id The product ID.
	 * @param UpdateProductAvailabilityRequest $request The availability configuration.
	 * @return bool True on success.
	 */
	public function update_product_availability( int $product_id, UpdateProductAvailabilityRequest $request ): bool;

	/**
	 * Batch activation of products.
	 *
	 * @param array $product_ids An array of ids to not include in those that are changed.
	 * @return BatchOperationResponse
	 */
	public function batch_activate_products( array $product_ids ): BatchOperationResponse;

	/**
	 * Batch deactivation of products.
	 *
	 * @param array $product_ids An array of ids to not include in those that are changed.
	 * @return BatchOperationResponse
	 */
	public function batch_deactivate_products( array $product_ids ): BatchOperationResponse;
}
