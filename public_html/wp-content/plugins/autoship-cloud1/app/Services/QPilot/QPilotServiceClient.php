<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QPilot Service Client
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot;

use Autoship\Services\QPilot\Access\AccessTokenResponse;
use Autoship\Services\QPilot\Access\OAuth2Request;
use Autoship\Services\QPilot\Access\OAuth2Response;
use Autoship\Services\QPilot\Common\BatchOperationResponse;
use Autoship\Services\QPilot\Coupons\CouponResponse;
use Autoship\Services\QPilot\Coupons\CouponSearchRequest;
use Autoship\Services\QPilot\Coupons\CouponValidationResponse;
use Autoship\Services\QPilot\Coupons\CreateCouponRequest;
use Autoship\Services\QPilot\Coupons\UpdateCouponRequest;
use Autoship\Services\QPilot\Coupons\ValidateCouponsRequest;
use Autoship\Services\QPilot\Customers\BatchCustomerRequest;
use Autoship\Services\QPilot\Customers\CustomerMetricsRequest;
use Autoship\Services\QPilot\Customers\CustomerMetricsResponse;
use Autoship\Services\QPilot\Customers\CustomerResponse;
use Autoship\Services\QPilot\Customers\CustomerSearchRequest;
use Autoship\Services\QPilot\Customers\CustomerSummariesRequest;
use Autoship\Services\QPilot\Customers\CustomerSummariesResponse;
use Autoship\Services\QPilot\Customers\UpsertCustomerRequest;
use Autoship\Services\QPilot\Implementations\AccessManagement;
use Autoship\Services\QPilot\Implementations\CouponManagement;
use Autoship\Services\QPilot\Implementations\CustomerManagement;
use Autoship\Services\QPilot\Implementations\IntegrationManagement;
use Autoship\Services\QPilot\Implementations\OrderManagement;
use Autoship\Services\QPilot\Implementations\PaymentManagement;
use Autoship\Services\QPilot\Implementations\ProductManagement;
use Autoship\Services\QPilot\Implementations\SiteManagement;
use Autoship\Services\QPilot\Integrations\IntegrationCheckResponse;
use Autoship\Services\QPilot\Integrations\IntegrationStatusResponse;
use Autoship\Services\QPilot\Integrations\MigrationStatusResponse;
use Autoship\Services\QPilot\Integrations\SiteIntegrationsResponse;
use Autoship\Services\QPilot\Orders\AddItemsToNextScheduledOrderRequest;
use Autoship\Services\QPilot\Orders\CreateOrderRequest;
use Autoship\Services\QPilot\Orders\CreateScheduledOrderItemRequest;
use Autoship\Services\QPilot\Orders\CreateScheduledOrderItemsRequest;
use Autoship\Services\QPilot\Orders\NextOccurrenceRequest;
use Autoship\Services\QPilot\Orders\NextOccurrenceResponse;
use Autoship\Services\QPilot\Orders\OrderResponse;
use Autoship\Services\QPilot\Orders\OrderSearchRequest;
use Autoship\Services\QPilot\Orders\ScheduledOrderItemResponse;
use Autoship\Services\QPilot\Orders\UpdateScheduledOrderItemRequest;
use Autoship\Services\QPilot\Orders\UpdateScheduledOrderRequest;
use Autoship\Services\QPilot\Payments\CreatePaymentMethodRequest;
use Autoship\Services\QPilot\Payments\PaymentMethodResponse;
use Autoship\Services\QPilot\Payments\UpsertPaymentMethodRequest;
use Autoship\Services\QPilot\Products\BatchProductRequest;
use Autoship\Services\QPilot\Products\ProductResponse;
use Autoship\Services\QPilot\Products\ProductSearchRequest;
use Autoship\Services\QPilot\Products\ProductSummaryRequest;
use Autoship\Services\QPilot\Products\ProductSummaryResponse;
use Autoship\Services\QPilot\Products\UpdateProductAvailabilityRequest;
use Autoship\Services\QPilot\Products\UpsertProductRequest;
use Autoship\Services\QPilot\Sites\CreateSiteRequest;
use Autoship\Services\QPilot\Sites\SiteResponse;
use Autoship\Services\QPilot\Sites\SiteSettingsResponse;
use Autoship\Services\QPilot\Sites\UpdateSiteMetadataRequest;
use Autoship\Services\QPilot\Sites\UpdateSiteUrlsRequest;

/**
 * The QPilot Service Client
 *
 * @package Autoship\Services\QPilot
 * @since 1.0.0
 */
class QPilotServiceClient implements QPilotServiceInterface {
	/**
	 * The API client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $api_client;

	/**
	 * Access management implementation.
	 *
	 * @var AccessManagement
	 */
	private AccessManagement $access_management;

	/**
	 * Customer management implementation.
	 *
	 * @var CustomerManagement
	 */
	private CustomerManagement $customer_management;

	/**
	 * Product management implementation.
	 *
	 * @var ProductManagement
	 */
	private ProductManagement $product_management;

	/**
	 * Order management implementation.
	 *
	 * @var OrderManagement
	 */
	private OrderManagement $order_management;

	/**
	 * Payment management implementation.
	 *
	 * @var PaymentManagement
	 */
	private PaymentManagement $payment_management;

	/**
	 * Coupon management implementation.
	 *
	 * @var CouponManagement
	 */
	private CouponManagement $coupon_management;

	/**
	 * Integration management implementation.
	 *
	 * @var IntegrationManagement
	 */
	private IntegrationManagement $integration_management;

	/**
	 * Site management implementation.
	 *
	 * @var SiteManagement
	 */
	private SiteManagement $site_management;

	/**
	 * Constructor.
	 *
	 * @param string $api_url The QPilot API URL.
	 */
	public function __construct( string $api_url ) {
		$this->api_client = new QPilotHttpClient( $api_url );

		// Initialize all implementation classes.
		$this->access_management      = new Implementations\AccessManagement( $this->api_client );
		$this->customer_management    = new Implementations\CustomerManagement( $this->api_client );
		$this->product_management     = new Implementations\ProductManagement( $this->api_client );
		$this->order_management       = new Implementations\OrderManagement( $this->api_client );
		$this->payment_management     = new Implementations\PaymentManagement( $this->api_client );
		$this->coupon_management      = new Implementations\CouponManagement( $this->api_client );
		$this->integration_management = new Implementations\IntegrationManagement( $this->api_client );
		$this->site_management        = new Implementations\SiteManagement( $this->api_client );
	}

	/**
	 * Authenticate with OAuth2.
	 *
	 * @param OAuth2Request $request The OAuth2 request containing the code.
	 *
	 * @return OAuth2Response The OAuth2 response.
	 */
	public function oauth2( OAuth2Request $request ): OAuth2Response {
		return $this->access_management->oauth2( $request );
	}

	/**
	 * Refresh OAuth2 token.
	 *
	 * @return OAuth2Response The OAuth2 response.
	 */
	public function refresh_oauth2(): OAuth2Response {
		return $this->access_management->refresh_oauth2();
	}

	/**
	 * Generate a merchant access token.
	 *
	 * @param string $secret_key The secret key.
	 *
	 * @return AccessTokenResponse The access token response.
	 */
	public function generate_merchant_access_token( string $secret_key ): AccessTokenResponse {
		return $this->access_management->generate_merchant_access_token( $secret_key );
	}

	/**
	 * Generate a customer access token.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $secret_key The secret key.
	 *
	 * @return AccessTokenResponse The access token response.
	 */
	public function generate_customer_access_token( int $customer_id, string $secret_key ): AccessTokenResponse {
		return $this->access_management->generate_customer_access_token( $customer_id, $secret_key );
	}

	/**
	 * Get a coupon by ID.
	 *
	 * @param int $coupon_id The coupon ID.
	 *
	 * @return CouponResponse The coupon object.
	 */
	public function get_coupon( int $coupon_id ): CouponResponse {
		return $this->coupon_management->get_coupon( $coupon_id );
	}

	/**
	 * Get coupons with typed filtering parameters.
	 *
	 * @param CouponSearchRequest $request The search parameters.
	 *
	 * @return array<CouponResponse> An array of coupon objects.
	 */
	public function get_coupons( CouponSearchRequest $request ): array {
		return $this->coupon_management->get_coupons( $request );
	}

	/**
	 * Get a coupon by code.
	 *
	 * @param string $code The coupon code.
	 *
	 * @return CouponResponse The coupon object.
	 */
	public function get_coupon_by_code( string $code ): CouponResponse {
		return $this->coupon_management->get_coupon_by_code( $code );
	}

	/**
	 * Create a coupon.
	 *
	 * @param CreateCouponRequest $request The coupon data.
	 *
	 * @return CouponResponse The created coupon object.
	 */
	public function create_coupon( CreateCouponRequest $request ): CouponResponse {
		return $this->coupon_management->create_coupon( $request );
	}

	/**
	 * Update a coupon.
	 *
	 * @param int                 $coupon_id The coupon ID.
	 * @param UpdateCouponRequest $request The coupon data.
	 *
	 * @return CouponResponse The updated coupon object.
	 */
	public function update_coupon( int $coupon_id, UpdateCouponRequest $request ): CouponResponse {
		return $this->coupon_management->update_coupon( $coupon_id, $request );
	}

	/**
	 * Delete a coupon.
	 *
	 * @param int $coupon_id The coupon ID.
	 *
	 * @return bool True on success.
	 */
	public function delete_coupon( int $coupon_id ): bool {
		return $this->coupon_management->delete_coupon( $coupon_id );
	}

	/**
	 * Validate coupons for an order.
	 *
	 * @param int                    $order_id The order ID.
	 * @param ValidateCouponsRequest $request The coupon codes to validate.
	 *
	 * @return CouponValidationResponse The validation results.
	 */
	public function validate_coupons( int $order_id, ValidateCouponsRequest $request ): CouponValidationResponse {
		return $this->coupon_management->validate_coupons( $order_id, $request );
	}

	/**
	 * Get a customer by ID.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return CustomerResponse The customer object.
	 */
	public function get_customer( int $customer_id ): CustomerResponse {
		return $this->customer_management->get_customer( $customer_id );
	}

	/**
	 * Get customers with typed filtering parameters.
	 *
	 * @param CustomerSearchRequest $request The search parameters.
	 *
	 * @return array<CustomerResponse> An array of CustomerResponse objects.
	 */
	public function get_customers( CustomerSearchRequest $request ): array {
		return $this->customer_management->get_customers( $request );
	}

	/**
	 * Create or update a customer.
	 *
	 * @param UpsertCustomerRequest $request The customer data.
	 *
	 * @return CustomerResponse The created/updated customer object.
	 */
	public function upsert_customer( UpsertCustomerRequest $request ): CustomerResponse {
		return $this->customer_management->upsert_customer( $request );
	}

	/**
	 * Delete a customer.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return bool True on success.
	 */
	public function delete_customer( int $customer_id ): bool {
		return $this->customer_management->delete_customer( $customer_id );
	}

	/**
	 * Batch create or update customers.
	 *
	 * @param BatchCustomerRequest $request The batch customer request.
	 *
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_upsert_customers( BatchCustomerRequest $request ): BatchOperationResponse {
		return $this->customer_management->batch_upsert_customers( $request );
	}

	/**
	 * Batch delete customers.
	 *
	 * @param array<int> $customer_ids Array of customer IDs to delete.
	 *
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_delete_customers( array $customer_ids ): BatchOperationResponse {
		return $this->customer_management->batch_delete_customers( $customer_ids );
	}

	/**
	 * Get customer metrics.
	 *
	 * @param CustomerMetricsRequest $request The metrics request parameters.
	 *
	 * @return CustomerMetricsResponse The customer metrics response.
	 */
	public function get_customer_metrics( CustomerMetricsRequest $request ): CustomerMetricsResponse {
		return $this->customer_management->get_customer_metrics( $request );
	}

	/**
	 * Get customer summaries.
	 *
	 * @param CustomerSummariesRequest $request The summaries request parameters.
	 *
	 * @return CustomerSummariesResponse The customer summaries response.
	 */
	public function get_customer_summaries( CustomerSummariesRequest $request ): CustomerSummariesResponse {
		return $this->customer_management->get_customer_summaries( $request );
	}

	/**
	 * Get integration status.
	 *
	 * @return IntegrationStatusResponse The integration status response.
	 */
	public function get_integration_status(): IntegrationStatusResponse {
		return $this->integration_management->get_integration_status();
	}

	/**
	 * Check integration.
	 *
	 * @return IntegrationCheckResponse The integration check response.
	 */
	public function check_integration(): IntegrationCheckResponse {
		return $this->integration_management->check_integration();
	}

	/**
	 * Get site integrations.
	 *
	 * @return SiteIntegrationsResponse The site integrations response.
	 */
	public function get_site_integrations(): SiteIntegrationsResponse {
		return $this->integration_management->get_site_integrations();
	}

	/**
	 * Migrate processing version.
	 *
	 * @return MigrationStatusResponse The migration status response.
	 */
	public function migrate_processing_version(): MigrationStatusResponse {
		return $this->integration_management->migrate_processing_version();
	}

	/**
	 * Get an order by ID.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return OrderResponse The order object.
	 */
	public function get_order( int $order_id ): OrderResponse {
		return $this->order_management->get_order( $order_id );
	}

	/**
	 * Get orders for a customer with typed filtering parameters.
	 *
	 * @param int                $customer_id The customer ID.
	 * @param OrderSearchRequest $request The search parameters.
	 *
	 * @return array<OrderResponse> An array of OrderResponse objects.
	 */
	public function get_orders( int $customer_id, OrderSearchRequest $request ): array {
		return $this->order_management->get_orders( $customer_id, $request );
	}

	/**
	 * Get the next scheduled order for a customer.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param int    $frequency Optional. The frequency to match.
	 * @param string $frequency_type Optional. The frequency type to match.
	 * @param string $status Optional. The status to match.
	 *
	 * @return OrderResponse The next scheduled order.
	 */
	public function get_next_scheduled_order( int $customer_id, ?int $frequency = null, ?string $frequency_type = null, ?string $status = null ): OrderResponse {
		return $this->order_management->get_next_scheduled_order( $customer_id, $frequency, $frequency_type, $status );
	}

	/**
	 * Create a scheduled order.
	 *
	 * @param CreateOrderRequest $request The order creation request.
	 *
	 * @return OrderResponse The created order.
	 */
	public function create_order( CreateOrderRequest $request ): OrderResponse {
		return $this->order_management->create_order( $request );
	}

	/**
	 * Update a scheduled order.
	 *
	 * @param int                         $order_id The order ID.
	 * @param UpdateScheduledOrderRequest $request The order data.
	 *
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order( int $order_id, UpdateScheduledOrderRequest $request ): OrderResponse {
		return $this->order_management->update_scheduled_order( $order_id, $request );
	}

	/**
	 * Update a scheduled order's frequency.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $frequency_type The frequency type.
	 * @param int    $frequency The frequency value.
	 *
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_frequency( int $order_id, string $frequency_type, int $frequency ): OrderResponse {
		return $this->order_management->update_scheduled_order_frequency( $order_id, $frequency_type, $frequency );
	}

	/**
	 * Update a scheduled order's next occurrence date.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $next_occurrence The next occurrence date.
	 *
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_next_occurrence( int $order_id, string $next_occurrence ): OrderResponse {
		return $this->order_management->update_scheduled_order_next_occurrence( $order_id, $next_occurrence );
	}

	/**
	 * Update a scheduled order's status.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $status The new status.
	 *
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_status( int $order_id, string $status ): OrderResponse {
		return $this->order_management->update_scheduled_order_status( $order_id, $status );
	}

	/**
	 * Update a scheduled order's payment method.
	 *
	 * @param int $order_id The order ID.
	 * @param int $payment_method_id The payment method ID.
	 *
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_payment_method( int $order_id, int $payment_method_id ): OrderResponse {
		return $this->order_management->update_scheduled_order_payment_method( $order_id, $payment_method_id );
	}

	/**
	 * Get a scheduled order item.
	 *
	 * @param int $item_id The item ID.
	 *
	 * @return ScheduledOrderItemResponse The item object.
	 */
	public function get_scheduled_order_item( int $item_id ): ScheduledOrderItemResponse {
		return $this->order_management->get_scheduled_order_item( $item_id );
	}

	/**
	 * Create a scheduled order item.
	 *
	 * @param int                                  $order_id The order ID.
	 * @param int                                  $product_id The product ID.
	 * @param CreateScheduledOrderItemRequest|null $request Optional. The item data.
	 *
	 * @return ScheduledOrderItemResponse The created item.
	 */
	public function create_scheduled_order_item( int $order_id, int $product_id, ?CreateScheduledOrderItemRequest $request = null ): ScheduledOrderItemResponse {
		return $this->order_management->create_scheduled_order_item( $order_id, $product_id, $request );
	}

	/**
	 * Create multiple scheduled order items.
	 *
	 * @param int                              $order_id The order ID.
	 * @param CreateScheduledOrderItemsRequest $request The items to add.
	 *
	 * @return array<ScheduledOrderItemResponse> The created items.
	 */
	public function create_scheduled_order_items( int $order_id, CreateScheduledOrderItemsRequest $request ): array {
		return $this->order_management->create_scheduled_order_items( $order_id, $request );
	}

	/**
	 * Update a scheduled order item.
	 *
	 * @param int                             $item_id The item ID.
	 * @param UpdateScheduledOrderItemRequest $request The item data.
	 *
	 * @return ScheduledOrderItemResponse The updated item.
	 */
	public function update_scheduled_order_item( int $item_id, UpdateScheduledOrderItemRequest $request ): ScheduledOrderItemResponse {
		return $this->order_management->update_scheduled_order_item( $item_id, $request );
	}

	/**
	 * Delete a scheduled order item.
	 *
	 * @param int $item_id The item ID.
	 *
	 * @return bool True on success.
	 */
	public function delete_scheduled_order_item( int $item_id ): bool {
		return $this->order_management->delete_scheduled_order_item( $item_id );
	}

	/**
	 * Add items to the next scheduled order.
	 *
	 * @param int                                 $customer_id The customer ID.
	 * @param AddItemsToNextScheduledOrderRequest $request The items to add.
	 *
	 * @return OrderResponse The updated order.
	 */
	public function add_items_to_next_scheduled_order( int $customer_id, AddItemsToNextScheduledOrderRequest $request ): OrderResponse {
		return $this->order_management->add_items_to_next_scheduled_order( $customer_id, $request );
	}

	/**
	 * Generate the next occurrence date in UTC.
	 *
	 * @param NextOccurrenceRequest $request The next occurrence request.
	 *
	 * @return NextOccurrenceResponse The next occurrence date information.
	 */
	public function get_next_occurrence_utc( NextOccurrenceRequest $request ): NextOccurrenceResponse {
		return $this->order_management->get_next_occurrence_utc( $request );
	}

	/**
	 * Get a payment method by ID.
	 *
	 * @param int $method_id The payment method ID.
	 *
	 * @return PaymentMethodResponse The payment method object.
	 */
	public function get_payment_method( int $method_id ): PaymentMethodResponse {
		return $this->payment_management->get_payment_method( $method_id );
	}

	/**
	 * Get payment methods for a customer.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return array<PaymentMethodResponse> An array of PaymentMethodResponse objects.
	 */
	public function get_payment_methods( int $customer_id ): array {
		return $this->payment_management->get_payment_methods( $customer_id );
	}

	/**
	 * Create a payment method.
	 *
	 * @param CreatePaymentMethodRequest $request The payment method data.
	 *
	 * @return PaymentMethodResponse The created payment method object.
	 */
	public function create_payment_method( CreatePaymentMethodRequest $request ): PaymentMethodResponse {
		return $this->payment_management->create_payment_method( $request );
	}

	/**
	 * Update a payment method.
	 *
	 * @param UpsertPaymentMethodRequest $request The payment method data.
	 *
	 * @return PaymentMethodResponse The updated payment method object.
	 */
	public function upsert_payment_method( UpsertPaymentMethodRequest $request ): PaymentMethodResponse {
		return $this->payment_management->upsert_payment_method( $request );
	}

	/**
	 * Delete a payment method.
	 *
	 * @param int $method_id The payment method ID.
	 *
	 * @return bool True on success.
	 */
	public function delete_payment_method( int $method_id ): bool {
		return $this->payment_management->delete_payment_method( $method_id );
	}

	/**
	 * Get payment integrations.
	 *
	 * @return array An array of payment integration objects.
	 */
	public function get_payment_integrations(): array {
		return $this->payment_management->get_payment_integrations();
	}

	/**
	 * Create a payment integration.
	 *
	 * @param array $integration_data The payment integration data.
	 *
	 * @return object The created payment integration object.
	 */
	public function create_payment_integration( array $integration_data ): object {
		return $this->payment_management->create_payment_integration( $integration_data );
	}

	/**
	 * Get a product by ID.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return ProductResponse The product object.
	 */
	public function get_product( int $product_id ): ProductResponse {
		return $this->product_management->get_product( $product_id );
	}

	/**
	 * Get products with typed filtering parameters.
	 *
	 * @param ProductSearchRequest $request The search parameters.
	 *
	 * @return array<ProductResponse> An array of ProductResponse objects.
	 */
	public function get_products( ProductSearchRequest $request ): array {
		return $this->product_management->get_products( $request );
	}

	/**
	 * Create or update a product.
	 *
	 * @param UpsertProductRequest $request The product data.
	 *
	 * @return ProductResponse The created/updated product object.
	 */
	public function upsert_product( UpsertProductRequest $request ): ProductResponse {
		return $this->product_management->upsert_product( $request );
	}

	/**
	 * Delete a product.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return bool True on success.
	 */
	public function delete_product( int $product_id ): bool {
		return $this->product_management->delete_product( $product_id );
	}

	/**
	 * Batch create or update products.
	 *
	 * @param BatchProductRequest $request The batch product request.
	 *
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_upsert_products( BatchProductRequest $request ): BatchOperationResponse {
		return $this->product_management->batch_upsert_products( $request );
	}

	/**
	 * Batch delete products.
	 *
	 * @param array<int> $product_ids Array of product IDs to delete.
	 *
	 * @return BatchOperationResponse The batch operation response.
	 */
	public function batch_delete_products( array $product_ids ): BatchOperationResponse {
		return $this->product_management->batch_delete_products( $product_ids );
	}

	/**
	 * Get product summaries.
	 *
	 * @param ProductSummaryRequest $request The request containing product IDs and optional parameters.
	 *
	 * @return ProductSummaryResponse The product summaries response.
	 */
	public function get_products_summary( ProductSummaryRequest $request ): ProductSummaryResponse {
		return $this->product_management->get_products_summary( $request );
	}

	/**
	 * Update product availability.
	 *
	 * @param int                              $product_id The product ID.
	 * @param UpdateProductAvailabilityRequest $request The availability configuration.
	 *
	 * @return bool True on success.
	 */
	public function update_product_availability( int $product_id, UpdateProductAvailabilityRequest $request ): bool {
		return $this->product_management->update_product_availability( $product_id, $request );
	}

	/**
	 * Batch activation of products.
	 *
	 * @param array $product_ids An array of ids to not include in those that are changed.
	 *
	 * @return BatchOperationResponse
	 */
	public function batch_activate_products( array $product_ids ): BatchOperationResponse {
		return $this->product_management->batch_activate_products( $product_ids );
	}

	/**
	 * Batch deactivation of products.
	 *
	 * @param array $product_ids An array of ids to not include in those that are changed.
	 *
	 * @return BatchOperationResponse
	 */
	public function batch_deactivate_products( array $product_ids ): BatchOperationResponse {
		return $this->product_management->batch_deactivate_products( $product_ids );
	}

	/**
	 * Get the authentication token.
	 *
	 * @return string The authentication token.
	 */
	public function get_token_auth(): string {
		return $this->api_client->get_token_auth();
	}

	/**
	 * Set the authentication token.
	 *
	 * @param string $token_auth The authentication token.
	 *
	 * @return void
	 */
	public function set_token_auth( string $token_auth ): void {
		$this->api_client->set_token_auth( $token_auth );
	}

	/**
	 * Get the user ID.
	 *
	 * @return int The user ID.
	 */
	public function get_user_id(): int {
		return $this->api_client->get_user_id();
	}

	/**
	 * Set the user ID.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return void
	 */
	public function set_user_id( int $user_id ): void {
		$this->api_client->set_user_id( $user_id );
	}

	/**
	 * Get the site ID.
	 *
	 * @return int The site ID.
	 */
	public function get_site_id(): int {
		return $this->api_client->get_site_id();
	}

	/**
	 * Set the site ID.
	 *
	 * @param int $site_id The site ID.
	 *
	 * @return void
	 */
	public function set_site_id( int $site_id ): void {
		$this->api_client->set_site_id( $site_id );
	}

	/**
	 * Set the source of the API call.
	 *
	 * @param string $source The source of the call.
	 *
	 * @return void
	 */
	public function set_source( string $source ): void {
		$this->api_client->set_source( $source );
	}

	/**
	 * Get the default site.
	 *
	 * @return SiteResponse The site object.
	 */
	public function get_default_site(): SiteResponse {
		return $this->site_management->get_default_site();
	}

	/**
	 * Get a site by ID.
	 *
	 * @param int $site_id The site ID.
	 *
	 * @return SiteResponse The site object.
	 */
	public function get_site( int $site_id ): SiteResponse {
		return $this->site_management->get_site( $site_id );
	}

	/**
	 * Create a site.
	 *
	 * @param CreateSiteRequest $request The site creation request.
	 *
	 * @return SiteResponse The created site object.
	 */
	public function create_site( CreateSiteRequest $request ): SiteResponse {
		return $this->site_management->create_site( $request );
	}

	/**
	 * Update site metadata.
	 *
	 * @param UpdateSiteMetadataRequest $request The metadata update request.
	 *
	 * @return SiteResponse The updated site object.
	 */
	public function update_site_metadata( UpdateSiteMetadataRequest $request ): SiteResponse {
		return $this->site_management->update_site_metadata( $request );
	}

	/**
	 * Get site settings.
	 *
	 * @return SiteSettingsResponse The site settings.
	 */
	public function get_settings(): SiteSettingsResponse {
		return $this->site_management->get_settings();
	}

	/**
	 * Update My Account relative URLs.
	 *
	 * @param UpdateSiteUrlsRequest $request The URLs update request.
	 *
	 * @return SiteSettingsResponse The updated settings.
	 */
	public function update_my_account_relative_urls( UpdateSiteUrlsRequest $request ): SiteSettingsResponse {
		return $this->site_management->update_my_account_relative_urls( $request );
	}
}
