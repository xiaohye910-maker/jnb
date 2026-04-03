
# Developer Guide: Using the Logging Service

The Autoship Cloud plugin includes a robust logging service located in the `app\Services\Logging` namespace. This guide explains how to use this service effectively in your development work.

## Overview

The logging service provides:
- Multiple log levels (trace, debug, info, warning, error)
- Performance timing capabilities
- File-based logging with automatic rotation
- Log management features (listing, downloading, cleanup)

## Basic Usage

### Logging Messages

To log messages at different severity levels:

```php
use Autoship\Services\Logging\Logger;

// Log an informational message
Logger::info('User Registration', 'New user registered with ID: 123');

// Log a debug message
Logger::debug('Cart Processing', 'Cart contents: ' . json_encode($cart));

// Log an error
Logger::error('Payment Gateway', 'Transaction failed: ' . $error_message);

// Log a warning
Logger::warning('Inventory', 'Product #456 stock is low (2 remaining)');

// Log a trace message (for performance tracking)
Logger::trace('API Request', 'Fetched customer data from external API');
```

The first parameter is always the context (category/component), and the second is the actual message.

### Performance Timing

The logging service includes built-in timing capabilities:

```php
// Method 1: Using start_timer and stop_timer
Logger::start_timer('import_process');
// ... perform operations ...
Logger::stop_timer('import_process', 'Data Import', 'Completed importing 500 products');

// Method 2: Manual timing
$start_time = floor(microtime(true) * 1000);
// ... perform operations ...
Logger::log_with_timing('API Request', 'Fetched customer data', $start_time);
```

## Advanced Usage

### Checking if Logging is Enabled

Before performing expensive operations to prepare log messages, check if logging is enabled:

```php
if (Logger::is_logging_enabled()) {
    // Perform expensive operations to prepare log message
    $detailed_data = prepare_detailed_log_data();
    Logger::debug('Data Processing', $detailed_data);
}
```

### Retrieving Available Logs

To get a list of available log files:

```php
$logs = Logger::get_logs();
foreach ($logs as $log) {
    echo "Log file: " . $log;
}
```

### Using Custom Sinks

The logging service uses a "sink" system for output destinations. By default, logs are written to files, but you can implement custom sinks:

```php
use Autoship\Services\Logging\SinkInterface;
use Autoship\Services\Logging\SinkFactory;
use Autoship\Services\Logging\Logger;

// Create a custom sink that implements SinkInterface
class DatabaseSink implements SinkInterface {
    // Implement required methods
}

// Register your custom sink
$factory = SinkFactory::get_instance();
$factory->register_sink('database', new DatabaseSink());

// Use your custom sink
$logger = Logger::get_instance();
$logger->set_sink($factory->get_sink('database'));
```

## Configuration

The logging service is enabled by default. The configuration is stored in WordPress options:

- `autoship_logging_state`: Controls whether logging is active ('active' or 'inactive')

## Best Practices

1. **Use Appropriate Log Levels**:
    - `trace`: For detailed execution flow and performance tracking
    - `debug`: For development-time debugging information
    - `info`: For normal application events
    - `warning`: For non-critical issues that should be monitored
    - `error`: For errors that need attention

2. **Include Meaningful Context**:
    - Always provide a descriptive context as the first parameter
    - This helps with filtering and organizing logs

3. **Structure Log Messages**:
    - Include relevant IDs (user ID, order ID, etc.)
    - For complex data, use JSON encoding
    - Keep messages concise but informative

4. **Performance Considerations**:
    - Check if logging is enabled before performing expensive operations
    - Use timing functions to identify performance bottlenecks

5. **Log Rotation**:
    - The FileSink automatically handles log rotation
    - Old logs are cleaned up based on configured retention settings

## Example Workflow

Here's a complete example of using the logging service in a typical workflow:

```php
use Autoship\Services\Logging\Logger;

function process_order($order_id) {
    Logger::info('Order Processing', "Starting to process order #{$order_id}");
    
    try {
        Logger::start_timer("process_order_{$order_id}");
        
        // Validate order
        if (!validate_order($order_id)) {
            Logger::warning('Order Processing', "Order #{$order_id} validation failed");
            return false;
        }
        
        // Process payment
        try {
            $payment_result = process_payment($order_id);
            Logger::debug('Payment Processing', "Payment result for order #{$order_id}: " . json_encode($payment_result));
        } catch (Exception $e) {
            Logger::error('Payment Processing', "Payment failed for order #{$order_id}: " . $e->getMessage());
            throw $e;
        }
        
        // Complete order
        complete_order($order_id);
        
        Logger::stop_timer("process_order_{$order_id}", 'Order Processing', "Completed processing order #{$order_id}");
        return true;
    } catch (Exception $e) {
        Logger::error('Order Processing', "Error processing order #{$order_id}: " . $e->getMessage());
        return false;
    }
}
```

By following this guide, you'll be able to effectively use the logging service to monitor, debug, and optimize your code.