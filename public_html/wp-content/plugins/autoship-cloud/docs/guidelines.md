
# Autoship Cloud Development Guidelines

This document provides essential information for developers working on the Autoship Cloud plugin. It covers build/configuration instructions, testing procedures, and development guidelines specific to this project.

## Build/Configuration Instructions

### Prerequisites

- PHP 7.4 or higher
- WordPress 5.0 or higher
- WooCommerce 3.4.1 or higher
- Composer for PHP dependencies
- Node.js and npm for frontend asset compilation

### Initial Setup

1. **Clone the repository**:
   ```powershell
   git clone <repository-url>
   cd autoship-cloud
   ```

2. **Install PHP dependencies**:
   ```powershell
   composer install
   ```

3. **Install JavaScript dependencies**:
   ```powershell
   npm install
   ```

4. **Compile SCSS files**:
   ```powershell
   npm run scss
   ```

### Development Environment Configuration

1. **Configure WooCommerce**:
   - Ensure WooCommerce is installed and activated
   - Configure at least one supported payment gateway (Stripe, PayPal, Authorize.Net, etc.)

2. **Configure Autoship Cloud**:
   - Navigate to WP Admin > Autoship Cloud > Settings > Connection or Follow up the Autoship Quicklaunch.
   - Set up the connection to QPilot services
   - Configure the plugin options under WP Admin > Autoship > Settings > Options

## Testing Information

### Testing Environment Setup

1. **Configure PHPUnit**:
   The project uses PHPUnit for unit testing. The configuration is in `tests/phpunit.xml`.

2. **Environment Variables**:
   Set the `WP_PHPUNIT__DIR` environment variable to point to your WordPress test installation.

### Running Tests

1. **Run all tests**:
   ```powershell
   cd autoship-cloud
   composer test
   ```

2. **Run coverage report**:
   ```powershell
   composer coverage
   ```

3. **Run compliance report**:
   ```powershell
   composer compliance
   ```

4. **Run static (Lines of Code) analysis**:
   ```powershell
   composer loc
   ```

### Test Coverage Requirements

All new code must have unit tests with a **minimum of 80% code coverage per module**. Coverage reports should be generated and reviewed before submitting pull requests.

### Creating New Tests

1. **Test File Naming Convention**:
   - Test files should be named with the class name followed by `Test.php`
   - Example: `TemplateLoaderTest.php` for testing the `TemplateLoader` class

2. **Test Class Structure**:
   ```php
   <?php
   
   namespace Autoship\Tests\YourNamespace;
   
   use PHPUnit\Framework\TestCase;
   use Brain\Monkey\Functions;
   use function Brain\Monkey\setUp;
   use function Brain\Monkey\tearDown;
   
   class YourClassTest extends TestCase {
       protected function setUp(): void {
           parent::setUp();
           setUp();
           // Additional setup code
       }
       
       protected function tearDown(): void {
           tearDown();
           parent::tearDown();
           // Additional teardown code
       }
       
       public function test_your_method(): void {
           // Test code here
           $this->assertTrue(true);
       }
   }
   ```

3. **Mocking WordPress Functions**:
   The project uses Brain\Monkey for mocking WordPress functions:
   ```php
   // Mock a WordPress function
   Functions\expect('wp_function_name')
       ->once()
       ->with($expected_param)
       ->andReturn($mock_return_value);
   ```

### Example Test

Here's a simple test for a utility function:

```php
<?php

namespace Autoship\Tests\Utilities;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

class UtilityTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        setUp();
    }
    
    protected function tearDown(): void {
        tearDown();
        parent::tearDown();
    }
    
    public function test_format_price(): void {
        // Mock WordPress/WooCommerce functions
        Functions\expect('wc_price')
            ->once()
            ->with(10.99)
            ->andReturn('$10.99');
            
        // Call the function being tested
        $result = \autoship_format_price(10.99);
        
        // Assert the result
        $this->assertEquals('$10.99', $result);
    }
}
```

## Additional Development Information

### Code Architecture

The plugin is transitioning to a fully object-oriented architecture following MVC principles:

- **Legacy Code**: Located in the `src/` directory, uses a procedural approach (being phased out)
- **Modern Code**: Located in the `app/` directory, uses OOP with PSR-4 autoloading

### Coding Standards

1. **PHP Coding Standards**:
   - **All PHP code must strictly follow WordPress Coding Standards**
   - Use PHP 7.4+ features including type hints where appropriate
   - PSR-4 autoloading for classes in the `app/` directory
   - All new code must be object-oriented and placed in the `app/` directory
   - Follow SOLID principles and design patterns where appropriate

2. **JavaScript Coding Standards**:
   - Use ES6+ syntax for new JavaScript code
   - Follow WordPress JavaScript Coding Standards

### Development Workflow

1. **Feature Development**:
   - Create a feature branch from `test`
   - Implement the feature with appropriate tests (minimum 80% coverage)
   - Submit a pull request to `test`

2. **Bug Fixes**:
   - Create a bugfix branch from `test`
   - Fix the issue with appropriate tests
   - Submit a pull request to `test`

### MVC Architecture

The plugin is moving toward an MVC (Model-View-Controller) architecture:

1. **Models**: Represent data structures and business logic
   - Located in `app/Modules/YourModule/Models/`
   - Handle data validation, storage, and retrieval

2. **Views**: Handle presentation logic
   - Templates located in `templates/` directory
   - No business logic should be in templates

3. **Controllers**: Handle request processing and coordinate between models and views
   - Located in `app/Modules/YourModule/Controllers/`
   - Process user input and determine appropriate responses

### Module System

The plugin uses a module system for organizing features:

1. **Module Structure**:
   ```
   app/Modules/YourModule/
   ├── YourModuleModule.php (main module class)
   ├── Controllers/
   ├── Models/
   ├── Services/
   └── Views/ (for view-related logic)
   ```

2. **Creating a New Module**:
   - Create a new directory in `app/Modules/`
   - Create a main module class that extends `Autoship\Core\Module`
   - Register the module in `app/Core/Plugin.php`
   - Ensure each module has appropriate unit tests with a minimum 80% coverage

### Template System

The plugin uses a template system that allows for theme overrides:

1. **Template Location**:
   - Plugin templates are located in the `/templates/` directory at the root level
   - Theme overrides should be placed in `your-theme/autoship/yourmodule/`

2. **Rendering Templates**:
   ```php
   \Autoship\Core\TemplateLoader::render('YourModule', 'template-name', $data);
   ```

### Integration with QPilot

The plugin integrates with QPilot services for managing autoship functionality:

1. **API Client**:
   - Legacy client located in `src/QPilot/Client.php`
   - New client located in `app/Services/QPilotServiceClient.php`
   - Used for communicating with QPilot services


2. **Authentication**:
   - Site authentication is managed through the Connection tab in the admin interface
   - API requests use the configured API key

### Logging

1. **Logging System**:
   - **DEPRECATED**: The old `autoship_log_entry()` function should not be used in new code
   - **RECOMMENDED**: Use `Logger::log()` or `Logger::trace()` methods for all logging
   - Example:
     ```php
     use Autoship\Services\Logging\Logger;
     
     // Log an informational message
     Logger::log('info', 'Processing order #123');
     
     // Log a detailed trace for debugging
     Logger::trace('Order processing details', array('order_id' => 123, 'status' => 'processing'));
     ```

2. **Debug Mode**:
   - Enable WordPress debug mode in `wp-config.php` to see more detailed error messages
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```