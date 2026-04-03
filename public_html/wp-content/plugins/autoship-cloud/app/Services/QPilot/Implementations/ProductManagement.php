<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\ProductManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Common\BatchOperationResponse;
use Autoship\Services\QPilot\Products\BatchProductRequest;
use Autoship\Services\QPilot\Products\ProductSearchRequest;
use Autoship\Services\QPilot\Products\ProductSummaryRequest;
use Autoship\Services\QPilot\Products\ProductSummaryResponse;
use Autoship\Services\QPilot\Products\UpdateProductAvailabilityRequest;
use Autoship\Services\QPilot\Products\UpsertProductRequest;
use Autoship\Services\QPilot\Products\ProductResponse;

/**
 * Implementation of the ProductManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class ProductManagement implements ProductManagementInterface {
	/**
	 * The API client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $api_client;

	/**
	 * Constructor.
	 *
	 * @param QPilotHttpClient $api_client The API client.
	 */
	public function __construct( QPilotHttpClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Get a product by ID.
	 *
	 * @param int $product_id The product ID.
	 * @return ProductResponse The product object.
	 */
	public function get_product( int $product_id ): ProductResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/{$product_id}";
		$response = $this->api_client->get( $endpoint );

		return new ProductResponse( $response );
	}

	/**
	 * Get products with typed filtering parameters.
	 *
	 * @param ProductSearchRequest $request The search parameters.
	 * @return array<ProductResponse> An array of ProductResponse objects.
	 */
	public function get_products( ProductSearchRequest $request ): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products";

		$search_data = $request->to_array();
		$response    = $this->api_client->get( $endpoint, $search_data );

		$products = array();
		foreach ( $response as $product_data ) {
			$products[] = new ProductResponse( $product_data );
		}

		return $products;
	}

	/**
	 * Create or update a product.
	 *
	 * @param UpsertProductRequest $request The product data.
	 * @return ProductResponse The created/updated product object.
	 */
	public function upsert_product( UpsertProductRequest $request ): ProductResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/Upsert";

		$product_data = $request->to_array();

		$response = $this->api_client->post( $endpoint, $product_data );

		return new ProductResponse( $response );
	}

	/**
	 * Delete a product.
	 *
	 * @param int $product_id The product ID.
	 * @return bool True on success.
	 */
	public function delete_product( int $product_id ): bool {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/{$product_id}";

		$this->api_client->delete( $endpoint );

		return true;
	}

	/**
	 * Batch create or update products.
	 *
	 * @param BatchProductRequest $request The batch product request.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_upsert_products( BatchProductRequest $request ): BatchOperationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/Batch";

		$data     = $request->to_array();
		$response = $this->api_client->post( $endpoint, $data );

		return new BatchOperationResponse( $response );
	}

	/**
	 * Batch delete products.
	 *
	 * @param array<int> $product_ids Array of product IDs to delete.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_delete_products( array $product_ids ): BatchOperationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/Batch";

		$data = array(
			'productIds' => $product_ids,
		);

		$response = $this->api_client->delete( $endpoint, $data );

		return new BatchOperationResponse( $response );
	}

	/**
	 * Get product summaries.
	 *
	 * @param ProductSummaryRequest $request The request containing product IDs and optional parameters.
	 * @return ProductSummaryResponse The product summaries response.
	 */
	public function get_products_summary( ProductSummaryRequest $request ): ProductSummaryResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/Summaries";

		$params = $request->to_params_array();
		$data   = $request->to_body_array();

		$response = $this->api_client->post( $endpoint, $data, $params );

		return new ProductSummaryResponse( $response );
	}

	/**
	 * Update product availability.
	 *
	 * @param int                              $product_id The product ID.
	 * @param UpdateProductAvailabilityRequest $request The availability configuration.
	 * @return bool True on success.
	 */
	public function update_product_availability( int $product_id, UpdateProductAvailabilityRequest $request ): bool {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/{$product_id}/Availability";

		$data = array(
			'AddToScheduledOrder'   => $request->get_add_to_scheduled_order(),
			'ProcessScheduledOrder' => $request->get_process_scheduled_order(),
		);

		$this->api_client->put( $endpoint, $data );

		return true;
	}

	/**
	 * Batch activation of products.
	 *
	 * @param array $product_ids An array of ids to not include in those that are changed.
	 * @return BatchOperationResponse
	 */
	public function batch_activate_products( array $product_ids ): BatchOperationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/BatchActivation";

		$data = array(
			'active' => 'true',
			'ids'    => $product_ids,
		);

		$response = $this->api_client->patch( $endpoint, $data );

		return new BatchOperationResponse( $response );
	}

	/**
	 * Batch deactivation of products.
	 *
	 * @param array $product_ids An array of ids to not include in those that are changed.
	 * @return BatchOperationResponse
	 */
	public function batch_deactivate_products( array $product_ids ): BatchOperationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Products/BatchActivation";

		$data = array(
			'active' => 'false',
			'ids'    => $product_ids,
		);

		$response = $this->api_client->patch( $endpoint, $data );

		return new BatchOperationResponse( $response );
	}
}
