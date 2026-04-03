<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Order Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

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

/**
 * Interface for order management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface OrderManagementInterface {
	/**
	 * Get an order by ID.
	 *
	 * @param int $order_id The order ID.
	 * @return OrderResponse The order object.
	 */
	public function get_order( int $order_id ): OrderResponse;

	/**
	 * Get orders for a customer with typed filtering parameters.
	 *
	 * @param int                $customer_id The customer ID.
	 * @param OrderSearchRequest $request The search parameters.
	 * @return array<OrderResponse> An array of OrderResponse objects.
	 */
	public function get_orders( int $customer_id, OrderSearchRequest $request ): array;

	/**
	 * Get the next scheduled order for a customer.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param int    $frequency Optional. The frequency to match.
	 * @param string $frequency_type Optional. The frequency type to match.
	 * @param string $status Optional. The status to match.
	 * @return OrderResponse The next scheduled order.
	 */
	public function get_next_scheduled_order( int $customer_id, ?int $frequency = null, ?string $frequency_type = null, ?string $status = null ): OrderResponse;

	/**
	 * Create a scheduled order.
	 *
	 * @param CreateOrderRequest $request The order creation request.
	 * @return OrderResponse The created order.
	 */
	public function create_order( CreateOrderRequest $request ): OrderResponse;

	/**
	 * Update a scheduled order.
	 *
	 * @param int                         $order_id The order ID.
	 * @param UpdateScheduledOrderRequest $request The order data.
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order( int $order_id, UpdateScheduledOrderRequest $request ): OrderResponse;

	/**
	 * Update a scheduled order's frequency.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $frequency_type The frequency type.
	 * @param int    $frequency The frequency value.
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_frequency( int $order_id, string $frequency_type, int $frequency ): OrderResponse;

	/**
	 * Update a scheduled order's next occurrence date.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $next_occurrence The next occurrence date.
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_next_occurrence( int $order_id, string $next_occurrence ): OrderResponse;

	/**
	 * Update a scheduled order's status.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $status The new status.
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_status( int $order_id, string $status ): OrderResponse;

	/**
	 * Update a scheduled order's payment method.
	 *
	 * @param int $order_id The order ID.
	 * @param int $payment_method_id The payment method ID.
	 * @return OrderResponse The updated order object.
	 */
	public function update_scheduled_order_payment_method( int $order_id, int $payment_method_id ): OrderResponse;

	/**
	 * Get a scheduled order item.
	 *
	 * @param int $item_id The item ID.
	 * @return ScheduledOrderItemResponse The item object.
	 */
	public function get_scheduled_order_item( int $item_id ): ScheduledOrderItemResponse;

	/**
	 * Create a scheduled order item.
	 *
	 * @param int                                  $order_id The order ID.
	 * @param int                                  $product_id The product ID.
	 * @param CreateScheduledOrderItemRequest|null $request Optional. The item data.
	 * @return ScheduledOrderItemResponse The created item.
	 */
	public function create_scheduled_order_item( int $order_id, int $product_id, ?CreateScheduledOrderItemRequest $request = null ): ScheduledOrderItemResponse;

	/**
	 * Create multiple scheduled order items.
	 *
	 * @param int                              $order_id The order ID.
	 * @param CreateScheduledOrderItemsRequest $request The items to add.
	 * @return array<ScheduledOrderItemResponse> The created items.
	 */
	public function create_scheduled_order_items( int $order_id, CreateScheduledOrderItemsRequest $request ): array;

	/**
	 * Update a scheduled order item.
	 *
	 * @param int                             $item_id The item ID.
	 * @param UpdateScheduledOrderItemRequest $request The item data.
	 * @return ScheduledOrderItemResponse The updated item.
	 */
	public function update_scheduled_order_item( int $item_id, UpdateScheduledOrderItemRequest $request ): ScheduledOrderItemResponse;

	/**
	 * Delete a scheduled order item.
	 *
	 * @param int $item_id The item ID.
	 * @return bool True on success.
	 */
	public function delete_scheduled_order_item( int $item_id ): bool;

	/**
	 * Add items to the next scheduled order.
	 *
	 * @param int                                 $customer_id The customer ID.
	 * @param AddItemsToNextScheduledOrderRequest $request The items to add.
	 * @return OrderResponse The updated order.
	 */
	public function add_items_to_next_scheduled_order( int $customer_id, AddItemsToNextScheduledOrderRequest $request ): OrderResponse;

	/**
	 * Generate the next occurrence date in UTC.
	 *
	 * @param NextOccurrenceRequest $request The next occurrence request.
	 * @return NextOccurrenceResponse The next occurrence date information.
	 */
	public function get_next_occurrence_utc( NextOccurrenceRequest $request ): NextOccurrenceResponse;
}
