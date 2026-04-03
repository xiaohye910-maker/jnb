<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\OrderManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Orders\OrderSearchRequest;
use Autoship\Services\QPilot\Orders\UpdateScheduledOrderRequest;
use Autoship\Services\QPilot\Orders\OrderResponse;
use Autoship\Services\QPilot\Orders\CreateOrderRequest;
use Autoship\Services\QPilot\Orders\ScheduledOrderItemResponse;
use Autoship\Services\QPilot\Orders\CreateScheduledOrderItemRequest;
use Autoship\Services\QPilot\Orders\CreateScheduledOrderItemsRequest;
use Autoship\Services\QPilot\Orders\UpdateScheduledOrderItemRequest;
use Autoship\Services\QPilot\Orders\AddItemsToNextScheduledOrderRequest;
use Autoship\Services\QPilot\Orders\NextOccurrenceRequest;
use Autoship\Services\QPilot\Orders\NextOccurrenceResponse;
use Exception;

/**
 * Implementation of the OrderManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class OrderManagement implements OrderManagementInterface {
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
	 * Get an order by ID.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return OrderResponse The order object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_order( int $order_id ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}";
		$response = $this->api_client->get( $endpoint );

		return new OrderResponse( $response );
	}

	/**
	 * Get orders for a customer with typed filtering parameters.
	 *
	 * @param int                $customer_id The customer ID.
	 * @param OrderSearchRequest $request The search parameters.
	 *
	 * @return array<OrderResponse> An array of OrderResponse objects.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_orders( int $customer_id, OrderSearchRequest $request ): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/{$customer_id}/Orders";

		$search_data = $request->to_array();
		$response    = $this->api_client->get( $endpoint, $search_data );

		$orders = array();
		foreach ( $response as $order_data ) {
			$orders[] = new OrderResponse( $order_data );
		}

		return $orders;
	}

	/**
	 * Get the next scheduled order for a customer.
	 *
	 * @param int     $customer_id The customer ID.
	 * @param ?int    $frequency Optional. The frequency to match.
	 * @param ?string $frequency_type Optional. The frequency type to match.
	 * @param ?string $status Optional. The status to match.
	 *
	 * @return OrderResponse The next scheduled order.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_next_scheduled_order( int $customer_id, ?int $frequency = null, ?string $frequency_type = null, ?string $status = null ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/{$customer_id}/NextScheduledOrder";

		$params = array(
			'customerId' => $customer_id,
		);

		if ( null !== $frequency ) {
			$params['frequency'] = $frequency;
		}

		if ( null !== $frequency_type ) {
			$params['frequencyType'] = $frequency_type;
		}

		if ( null !== $status ) {
			$params['status'] = $status;
		}

		$response = $this->api_client->get( $endpoint, $params );

		return new OrderResponse( $response );
	}

	/**
	 * Create a scheduled order.
	 *
	 * @param CreateOrderRequest $request The order creation request.
	 *
	 * @return OrderResponse The created order.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function create_order( CreateOrderRequest $request ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders";

		$data = $request->to_array();

		$response = $this->api_client->post( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Update a scheduled order.
	 *
	 * @param int                         $order_id The order ID.
	 * @param UpdateScheduledOrderRequest $request The order data.
	 *
	 * @return OrderResponse The updated order object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_scheduled_order( int $order_id, UpdateScheduledOrderRequest $request ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}";

		$data = $request->to_array();

		$response = $this->api_client->put( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Update a scheduled order's frequency.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $frequency_type The frequency type.
	 * @param int    $frequency The frequency value.
	 *
	 * @return OrderResponse The updated order object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_scheduled_order_frequency( int $order_id, string $frequency_type, int $frequency ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}/Frequency";

		$data = array(
			'frequencyType' => $frequency_type,
			'frequency'     => $frequency,
		);

		$response = $this->api_client->put( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Update a scheduled order's next occurrence date.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $next_occurrence The next occurrence date.
	 *
	 * @return OrderResponse The updated order object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_scheduled_order_next_occurrence( int $order_id, string $next_occurrence ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}/NextOccurrence";

		$data = array(
			'nextOccurrenceUtc' => $next_occurrence,
		);

		$response = $this->api_client->put( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Update a scheduled order's status.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $status The new status.
	 *
	 * @return OrderResponse The updated order object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_scheduled_order_status( int $order_id, string $status ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}/Status";

		$data = array(
			'status' => $status,
		);

		$response = $this->api_client->put( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Update a scheduled order's payment method.
	 *
	 * @param int $order_id The order ID.
	 * @param int $payment_method_id The payment method ID.
	 *
	 * @return OrderResponse The updated order object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_scheduled_order_payment_method( int $order_id, int $payment_method_id ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}/PaymentMethod";

		$data = array(
			'paymentMethodId' => $payment_method_id,
		);

		$response = $this->api_client->put( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Get a scheduled order item.
	 *
	 * @param int $item_id The item ID.
	 *
	 * @return ScheduledOrderItemResponse The item object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_scheduled_order_item( int $item_id ): ScheduledOrderItemResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/OrderItems/{$item_id}";

		$response = $this->api_client->get( $endpoint );

		return new ScheduledOrderItemResponse( $response );
	}

	/**
	 * Create a scheduled order item.
	 *
	 * @param int                                  $order_id The order ID.
	 * @param int                                  $product_id The product ID.
	 * @param CreateScheduledOrderItemRequest|null $request Optional. The item data.
	 * @return ScheduledOrderItemResponse The created item.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function create_scheduled_order_item( int $order_id, int $product_id, ?CreateScheduledOrderItemRequest $request = null ): ScheduledOrderItemResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}/Items";

		$data = array(
			'productId' => $product_id,
		);

		if ( null !== $request ) {
			$data = array_merge( $data, $request->to_array() );
		}

		$response = $this->api_client->post( $endpoint, $data );

		return new ScheduledOrderItemResponse( $response );
	}

	/**
	 * Create multiple scheduled order items.
	 *
	 * @param int                              $order_id The order ID.
	 * @param CreateScheduledOrderItemsRequest $request The items to add.
	 * @return array<ScheduledOrderItemResponse> The created items.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function create_scheduled_order_items( int $order_id, CreateScheduledOrderItemsRequest $request ): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Orders/{$order_id}/Items/Batch";

		$data = $request->to_array();

		$response = $this->api_client->post( $endpoint, $data );

		$items = array();
		foreach ( $response as $item_data ) {
			$items[] = new ScheduledOrderItemResponse( $item_data );
		}

		return $items;
	}

	/**
	 * Update a scheduled order item.
	 *
	 * @param int                             $item_id The item ID.
	 * @param UpdateScheduledOrderItemRequest $request The item data.
	 * @return ScheduledOrderItemResponse The updated item.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_scheduled_order_item( int $item_id, UpdateScheduledOrderItemRequest $request ): ScheduledOrderItemResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/OrderItems/{$item_id}";

		$data = $request->to_array();

		$response = $this->api_client->put( $endpoint, $data );

		return new ScheduledOrderItemResponse( $response );
	}

	/**
	 * Delete a scheduled order item.
	 *
	 * @param int $item_id The item ID.
	 *
	 * @return bool True on success.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function delete_scheduled_order_item( int $item_id ): bool {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/OrderItems/{$item_id}";

		$this->api_client->delete( $endpoint );

		return true;
	}

	/**
	 * Add items to the next scheduled order.
	 *
	 * @param int                                 $customer_id The customer ID.
	 * @param AddItemsToNextScheduledOrderRequest $request The items to add.
	 *
	 * @return OrderResponse The updated order.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function add_items_to_next_scheduled_order( int $customer_id, AddItemsToNextScheduledOrderRequest $request ): OrderResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/{$customer_id}/NextScheduledOrder/Items";

		$data               = $request->to_array();
		$data['customerId'] = $customer_id;

		$response = $this->api_client->post( $endpoint, $data );

		return new OrderResponse( $response );
	}

	/**
	 * Generate the next occurrence date in UTC.
	 *
	 * @param NextOccurrenceRequest $request The next occurrence request.
	 *
	 * @return NextOccurrenceResponse The next occurrence date information.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_next_occurrence_utc( NextOccurrenceRequest $request ): NextOccurrenceResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/NextOccurrence";

		$params = array(
			'frequencyType' => $request->get_frequency_type(),
			'frequency'     => $request->get_frequency(),
		);

		if ( null !== $request->get_reference_date() ) {
			$params['fromUtc'] = $request->get_reference_date();
		}

		if ( null !== $request->get_utc_offset() ) {
			$params['utcOffset'] = $request->get_utc_offset();
		}

		$response = $this->api_client->get( $endpoint, $params );

		return new NextOccurrenceResponse( $response );
	}
}
