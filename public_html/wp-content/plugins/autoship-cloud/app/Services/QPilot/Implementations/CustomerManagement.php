<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\CustomerManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Common\BatchOperationResponse;
use Autoship\Services\QPilot\Customers\BatchCustomerRequest;
use Autoship\Services\QPilot\Customers\CustomerResponse;
use Autoship\Services\QPilot\Customers\CustomerSearchRequest;
use Autoship\Services\QPilot\Customers\UpsertCustomerRequest;
use Autoship\Services\QPilot\Customers\CustomerMetricsRequest;
use Autoship\Services\QPilot\Customers\CustomerMetricsResponse;
use Autoship\Services\QPilot\Customers\CustomerSummariesRequest;
use Autoship\Services\QPilot\Customers\CustomerSummariesResponse;

/**
 * Implementation of the CustomerManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class CustomerManagement implements CustomerManagementInterface {
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
	 * Get a customer by ID.
	 *
	 * @param int $customer_id The customer ID.
	 * @return CustomerResponse The customer object.
	 */
	public function get_customer( int $customer_id ): CustomerResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/{$customer_id}";
		$response = $this->api_client->get( $endpoint );

		return new CustomerResponse( $response );
	}

	/**
	 * Get customers with typed filtering parameters.
	 *
	 * @param CustomerSearchRequest $request The search parameters.
	 * @return array<CustomerResponse> An array of CustomerResponse objects.
	 */
	public function get_customers( CustomerSearchRequest $request ): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers";

		$search_data = $request->to_array();
		$response    = $this->api_client->get( $endpoint, $search_data );

		$customers = array();
		foreach ( $response as $customer_data ) {
			$customers[] = new CustomerResponse( $customer_data );
		}

		return $customers;
	}

	/**
	 * Create or update a customer.
	 *
	 * @param UpsertCustomerRequest $request The customer data.
	 * @return CustomerResponse The created/updated customer object.
	 */
	public function upsert_customer( UpsertCustomerRequest $request ): CustomerResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers";

		$customer_data = $request->to_array();
		$response      = $this->api_client->post( $endpoint, $customer_data );

		return new CustomerResponse( $response );
	}

	/**
	 * Delete a customer.
	 *
	 * @param int $customer_id The customer ID.
	 * @return bool True on success.
	 */
	public function delete_customer( int $customer_id ): bool {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/{$customer_id}";

		$this->api_client->delete( $endpoint );

		return true;
	}

	/**
	 * Batch create or update customers.
	 *
	 * @param BatchCustomerRequest $request The batch customer request.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_upsert_customers( BatchCustomerRequest $request ): BatchOperationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/Batch";

		$data     = $request->to_array();
		$response = $this->api_client->post( $endpoint, $data );

		return new BatchOperationResponse( $response );
	}

	/**
	 * Batch delete customers.
	 *
	 * @param array<int> $customer_ids Array of customer IDs to delete.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_delete_customers( array $customer_ids ): BatchOperationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/Batch";

		$data = array(
			'customerIds' => $customer_ids,
		);

		$response = $this->api_client->delete( $endpoint, $data );

		return new BatchOperationResponse( $response );
	}

	/**
	 * Get customer metrics.
	 *
	 * @param CustomerMetricsRequest $request The metrics request parameters.
	 * @return CustomerMetricsResponse The customer metrics response.
	 */
	public function get_customer_metrics( CustomerMetricsRequest $request ): CustomerMetricsResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/Metrics";

		$search_data = $request->to_array();
		$response    = $this->api_client->get( $endpoint, $search_data );

		return new CustomerMetricsResponse( $response );
	}

	/**
	 * Get customer summaries.
	 *
	 * @param CustomerSummariesRequest $request The summaries request parameters.
	 * @return CustomerSummariesResponse The customer summaries response.
	 */
	public function get_customer_summaries( CustomerSummariesRequest $request ): CustomerSummariesResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/Summaries";

		$search_data = $request->to_array();
		$response    = $this->api_client->get( $endpoint, $search_data );

		return new CustomerSummariesResponse( $response );
	}
}
