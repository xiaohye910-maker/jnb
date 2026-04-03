<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Customer Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

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
 * Interface for customer management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface CustomerManagementInterface {
	/**
	 * Get a customer by ID.
	 *
	 * @param int $customer_id The customer ID.
	 * @return CustomerResponse The customer object.
	 */
	public function get_customer( int $customer_id ): CustomerResponse;

	/**
	 * Get customers with typed filtering parameters.
	 *
	 * @param CustomerSearchRequest $request The search parameters.
	 * @return array<CustomerResponse> An array of CustomerResponse objects.
	 */
	public function get_customers( CustomerSearchRequest $request ): array;

	/**
	 * Create or update a customer.
	 *
	 * @param UpsertCustomerRequest $request The customer data.
	 * @return CustomerResponse The created/updated customer object.
	 */
	public function upsert_customer( UpsertCustomerRequest $request ): CustomerResponse;

	/**
	 * Delete a customer.
	 *
	 * @param int $customer_id The customer ID.
	 * @return bool True on success.
	 */
	public function delete_customer( int $customer_id ): bool;

	/**
	 * Batch create or update customers.
	 *
	 * @param BatchCustomerRequest $request The batch customer request.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_upsert_customers( BatchCustomerRequest $request ): BatchOperationResponse;

	/**
	 * Batch delete customers.
	 *
	 * @param array<int> $customer_ids Array of customer IDs to delete.
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_delete_customers( array $customer_ids ): BatchOperationResponse;

	/**
	 * Get customer metrics.
	 *
	 * @param CustomerMetricsRequest $request The metrics request parameters.
	 * @return CustomerMetricsResponse The customer metrics response.
	 */
	public function get_customer_metrics( CustomerMetricsRequest $request ): CustomerMetricsResponse;

	/**
	 * Get customer summaries.
	 *
	 * @param CustomerSummariesRequest $request The summaries request parameters.
	 * @return CustomerSummariesResponse The customer summaries response.
	 */
	public function get_customer_summaries( CustomerSummariesRequest $request ): CustomerSummariesResponse;
}
