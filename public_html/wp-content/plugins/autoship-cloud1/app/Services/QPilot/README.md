
# QPilot Service Implementation Guide

## Introduction

The QPilot service is a comprehensive API client for interacting with the QPilot platform, which provides subscription and recurring order management capabilities. This guide will help you understand how to implement and use the QPilot service in your application.

## Factory Implementation

First, let's create a factory to build the QPilot client:

```php
<?php

namespace App\Factories;

use Autoship\Services\QPilot\QPilotClient;
use Autoship\Services\QPilot\QPilotServiceInterface;

class QPilotFactory
{
    /**
     * Create a new QPilot client instance.
     *
     * @param string $api_url The QPilot API URL
     * @param string $token_auth The authentication token
     * @param int $site_id The site ID
     * @param int $user_id Optional user ID
     * @return QPilotServiceInterface
     */
    public static function create(
        string $api_url,
        string $token_auth,
        int $site_id,
        int $user_id = 0
    ): QPilotServiceInterface {
        $client = new QPilotClient($api_url);
        $client->set_token_auth($token_auth);
        $client->set_site_id($site_id);
        
        if ($user_id > 0) {
            $client->set_user_id($user_id);
        }
        
        // Set the source of the API call (e.g., 'wordpress', 'custom-app')
        $client->set_source('your-application-name');
        
        return $client;
    }
}
```

## Basic Usage

Here's how to use the factory to create a QPilot client:

```php
use App\Factories\QPilotFactory;

// Create a QPilot client
$qpilot_client = QPilotFactory::create(
    'https://api.qpilot.cloud/v1',
    'your-auth-token',
    123 // your site ID
);
```

## Working with Orders

### Searching for Orders

```php
use Autoship\Services\QPilot\Orders\OrderSearchRequest;

// Create a search request
$request = new OrderSearchRequest();
$request->set_page(1)
        ->set_page_size(20)
        ->set_status_names(['Active', 'Processing'])
        ->set_order_by('nextOccurrenceUtc')
        ->set_order('ASC');

// Get orders for a customer using the request
$orders = $qpilot_client->get_orders($customer_id, $request);

// Process the order responses
foreach ($orders as $order) {
    echo "Order ID: " . $order->get_id() . "\n";
    echo "Status: " . $order->get_status() . "\n";
    echo "Next Occurrence: " . $order->get_next_occurrence_utc() . "\n";
}
```

### Creating a Scheduled Order

```php
use Autoship\Services\QPilot\Orders\CreateOrderRequest;

// Create a new order request
$request = new CreateOrderRequest($customer_id);
$request->set_frequency(1)
        ->set_frequency_type('Months')
        ->set_status('Active')
        ->set_shipping_address(
            'John',
            'Doe',
            '123 Main St',
            'Anytown',
            'CA',
            '12345',
            'US'
        );

// Add items to the order
$item1 = [
    'productId' => 123,
    'quantity' => 1,
    'price' => 19.99
];
$item2 = [
    'productId' => 456,
    'quantity' => 2,
    'price' => 29.99
];
$request->set_scheduled_order_items([$item1, $item2]);

// Create the order
$order = $qpilot_client->create_order($request);

// Access order data
$order_id = $order->get_id();
$status = $order->get_status();
$next_occurrence = $order->get_next_occurrence_utc();
```

### Updating a Scheduled Order

```php
use Autoship\Services\QPilot\Orders\UpdateScheduledOrderRequest;

// Create a request to update a scheduled order
$update_request = new UpdateScheduledOrderRequest();
$update_request->set_next_occurrence_utc('2023-12-01T00:00:00Z')
              ->set_frequency(30)
              ->set_frequency_type('Day')
              ->set_shipping_rate_name('Standard Shipping');

// Update the scheduled order
$updated_order = $qpilot_client->update_scheduled_order($order_id, $update_request);

// Or update specific aspects of the order
$qpilot_client->update_scheduled_order_frequency($order_id, 'Weeks', 2);
$qpilot_client->update_scheduled_order_next_occurrence($order_id, '2023-12-15T00:00:00Z');
$qpilot_client->update_scheduled_order_status($order_id, 'Paused');
$qpilot_client->update_scheduled_order_payment_method($order_id, $payment_method_id);
```

## Working with Customers

### Getting a Customer

```php
// Get a customer by ID
$customer = $qpilot_client->get_customer($customer_id);
echo "Customer Name: " . $customer->get_first_name() . ' ' . $customer->get_last_name() . "\n";
```

### Searching for Customers

```php
use Autoship\Services\QPilot\Customers\CustomerSearchRequest;

// Create a search request
$request = new CustomerSearchRequest();
$request->set_page(1)
        ->set_page_size(20)
        ->set_search_term('john')
        ->set_order_by('lastName')
        ->set_order('ASC');

// Get customers using the request
$customers = $qpilot_client->get_customers($request);

// Process the customer responses
foreach ($customers as $customer) {
    echo "Customer ID: " . $customer->get_id() . "\n";
    echo "Name: " . $customer->get_first_name() . ' ' . $customer->get_last_name() . "\n";
    echo "Email: " . $customer->get_email() . "\n";
}
```

### Creating or Updating a Customer

```php
use Autoship\Services\QPilot\Customers\UpsertCustomerRequest;

// Create a request to create/update a customer
$request = new UpsertCustomerRequest();
$request->set_external_id('wp_123') // Your system's customer ID
        ->set_first_name('John')
        ->set_last_name('Doe')
        ->set_email('john.doe@example.com')
        ->set_phone('555-123-4567');

// Create or update the customer
$customer = $qpilot_client->upsert_customer($request);
$customer_id = $customer->get_id();
```

## Working with Products

### Getting a Product

```php
// Get a product by ID
$product = $qpilot_client->get_product($product_id);
echo "Product Name: " . $product->get_name() . "\n";
echo "Price: " . $product->get_price() . "\n";
```

### Searching for Products

```php
use Autoship\Services\QPilot\Products\ProductSearchRequest;

// Create a search request
$request = new ProductSearchRequest();
$request->set_page(1)
        ->set_page_size(20)
        ->set_search_term('coffee')
        ->set_order_by('name')
        ->set_order('ASC');

// Get products using the request
$products = $qpilot_client->get_products($request);

// Process the product responses
foreach ($products as $product) {
    echo "Product ID: " . $product->get_id() . "\n";
    echo "Name: " . $product->get_name() . "\n";
    echo "Price: " . $product->get_price() . "\n";
}
```

### Creating or Updating a Product

```php
use Autoship\Services\QPilot\Products\UpsertProductRequest;

// Create a request to create/update a product
$request = new UpsertProductRequest();
$request->set_external_id('wc_123') // Your system's product ID
        ->set_name('Premium Coffee')
        ->set_price(19.99)
        ->set_active(true)
        ->set_availability_type('Always')
        ->set_metadata([
            'category' => 'beverages',
            'featured' => true
        ]);

// Create or update the product
$product = $qpilot_client->upsert_product($request);
$product_id = $product->get_id();
```

## Working with Payment Methods

### Getting Payment Methods

```php
// Get payment methods for a customer
$payment_methods = $qpilot_client->get_payment_methods($customer_id);

// Process the payment method responses
foreach ($payment_methods as $method) {
    echo "Payment Method ID: " . $method->get_id() . "\n";
    echo "Type: " . $method->get_type() . "\n";
    echo "Last 4: " . $method->get_last_four() . "\n";
}
```

### Creating a Payment Method

```php
use Autoship\Services\QPilot\Payments\CreatePaymentMethodRequest;

// Create a request to create a payment method
$request = new CreatePaymentMethodRequest();
$request->set_customer_id($customer_id)
        ->set_type('CreditCard')
        ->set_token('payment-token-from-gateway')
        ->set_gateway('stripe')
        ->set_metadata([
            'card_type' => 'visa',
            'expiry' => '12/25'
        ]);

// Create the payment method
$payment_method = $qpilot_client->create_payment_method($request);
$payment_method_id = $payment_method->get_id();
```

## Best Practices

1. **Error Handling**: Always wrap API calls in try-catch blocks to handle potential exceptions:

```php
try {
    $order = $qpilot_client->get_order($order_id);
} catch (\Exception $e) {
    // Log the error
    error_log('QPilot API Error: ' . $e->getMessage());
    // Handle the error appropriately
}
```

2. **Pagination**: When retrieving lists of items, implement pagination to avoid performance issues:

```php
$page = 1;
$page_size = 20;
$all_orders = [];

do {
    $request = new OrderSearchRequest();
    $request->set_page($page)
            ->set_page_size($page_size);
    
    $orders = $qpilot_client->get_orders($customer_id, $request);
    $all_orders = array_merge($all_orders, $orders);
    
    $page++;
} while (count($orders) == $page_size);
```

3. **Caching**: Consider caching frequently accessed data to reduce API calls:

```php
$cache_key = 'qpilot_product_' . $product_id;
$product = wp_cache_get($cache_key);

if (false === $product) {
    $product = $qpilot_client->get_product($product_id);
    wp_cache_set($cache_key, $product, 'qpilot', 3600); // Cache for 1 hour
}
```

4. **Batch Operations**: Use batch operations when working with multiple items:

```php
use Autoship\Services\QPilot\Products\BatchProductRequest;

// Create a batch request
$batch_request = new BatchProductRequest();

// Add multiple products to the batch
foreach ($products_data as $product_data) {
    $product_request = new UpsertProductRequest();
    $product_request->set_external_id($product_data['id'])
                   ->set_name($product_data['name'])
                   ->set_price($product_data['price']);
    
    $batch_request->add_product($product_request);
}

// Process the batch
$batch_response = $qpilot_client->batch_upsert_products($batch_request);

// Check for errors
if ($batch_response->has_errors()) {
    foreach ($batch_response->get_errors() as $error) {
        error_log('Batch error: ' . $error->get_message());
    }
}
```

## Conclusion

The QPilot service provides a robust API for managing subscriptions and recurring orders. By using the factory pattern and following the examples in this guide, you can easily integrate QPilot functionality into your application.

Remember to handle errors appropriately, implement pagination for large datasets, and consider caching strategies to optimize performance.