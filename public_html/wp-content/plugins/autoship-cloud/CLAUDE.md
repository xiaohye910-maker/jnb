# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Autoship Cloud is a WordPress/WooCommerce plugin that enables subscription-based product ordering. It integrates with QPilot (a hosted subscription management service) to provide merchants with automated recurring orders, payment processing, and customer subscription management.

**Plugin Details:**
- **Namespace:** `Autoship\`
- **Current Version:** 2.10.5
- **PHP Version:** 7.4+
- **WooCommerce:** 3.4.1+ (tested up to 10.0.4)
- **WordPress:** 4.6+ (tested up to 6.8.2)

## Development Commands

### Testing
```bash
# Run all tests
composer test
# Or directly:
vendor/bin/phpunit -c tests/phpunit.xml

# Run tests with coverage report
composer coverage
# Or directly:
vendor/bin/phpunit -c tests/phpunit.xml --coverage-html build/coverage

# Run single test file
vendor/bin/phpunit -c tests/phpunit.xml tests/Core/ServiceContainerTest.php

# Run specific test method
vendor/bin/phpunit -c tests/phpunit.xml --filter testMethodName
```

### Code Quality
```bash
# Check WordPress coding standards compliance
composer compliance
# Or directly:
vendor/bin/phpcs -d memory_limit=512M . --extensions=php --colors --standard=WordPress --report=summary --ignore=vendor/,.github/,.idea/,.phpunit.cache/,assets/,build/,docs/,config/ --exclude=Generic.Functions.CallTimePassByReference,Generic.Files.LineEndings

# Count lines of code (excluding vendor, assets, etc.)
composer loc
```

### SCSS Compilation
```bash
# Watch and compile SCSS files
npm run scss
```

## Architecture Overview

### Dual Architecture (Legacy + Modern)

This codebase uses a **hybrid architecture** transitioning from legacy procedural code to modern OOP:

1. **Legacy Code (`src/` directory)**
   - Procedural functions with WordPress hooks
   - Files loaded via direct `require_once` in `autoship.php`
   - Examples: `src/products.php`, `src/checkout.php`, `src/orders.php`

2. **Modern Code (`app/` directory)**
   - PSR-4 autoloaded classes with namespace `Autoship\`
   - Service container for dependency injection
   - Module-based architecture
   - Initialized via `Autoship\Core\Plugin::run()`

### Core Architecture Components

#### 1. Service Container (`app/Core/ServiceContainer.php`)
Manages dependency injection with lazy-loading:
```php
$container = new ServiceContainer();
$container->register('service_name', function() {
    return new Service();
});
```

#### 2. Module System (`app/Core/ModuleManager.php`)
Modules implement `ModuleInterface` with lifecycle methods:
- `register()` - Setup dependencies
- `boot()` - Initialize and hook into WordPress
- `deactivate()` - Cleanup on plugin deactivation
- `uninstall()` - Cleanup on plugin uninstall

**Active Modules:**
- `QuicklaunchModule` - Setup wizard
- `ProductSynchronizerModule` - Product sync with QPilot
- `NextimeModule` - Shipping integration
- `QuickLinksModule` - One-click email actions for scheduled orders

#### 3. Feature Flags (`app/Core/FeatureManager.php`)
Control features via static methods:
- `quicklaunch` - Guided setup wizard
- `product_sync` - Product synchronization
- `nextime` - Advanced shipping integration
- `quicklinks` - One-click email actions

### Key Integration Points

#### QPilot API Client (`app/Services/QPilot/`)
The plugin communicates with QPilot's subscription management backend:
- **OAuth2 Authentication** with automatic token refresh
- **HTTP Client** using WordPress `wp_remote_*()` functions
- **Endpoints:** Products, Orders, Customers, Payment Methods, Coupons, Sites
- **Token Storage:** WordPress options (`autoship_token_auth`, `autoship_refresh_token`)

**Service Files:**
- `QPilotServiceClient.php` - Main service
- `QPilotHttpClient.php` - HTTP handling
- `QPilotServiceFactory.php` - Service instantiation
- Request/Response classes for type safety

#### WooCommerce Integration
Key hooks and filters in `src/` files:
- **Products:** `woocommerce_process_product_meta` - Save autoship product settings
- **Checkout:** `woocommerce_payment_complete` - Create scheduled orders after checkout
- **Orders:** Custom order metadata linking WC orders to QPilot scheduled orders
- **REST API:** Filters in `src/api-wc.php` extend WooCommerce REST endpoints

#### REST API Endpoints (`src/api.php`)
Custom WordPress REST API routes:
- `POST /autoshipcloud/v1/product/{id}` - Update product availability
- `POST /wc-autoshipcloud/v1/orders` - Create WC orders from QPilot webhooks
- Prevents duplicate order creation via metadata checks

#### Payment Gateway Integration (`app/Domain/PaymentIntegrations/`)
Supports 15+ payment gateways including:
- Stripe, Authorize.Net, Braintree, PayPal, Square, NMI, CyberSource, CyberSourceV2, Airwallex, Checkout, FunnelKit, Paya, Sage, TrustCommerce

Each integration implements a standardized interface with:
- Method type enumeration
- API credentials configuration
- Test mode and authorization flags
- Validation logic

### Database Schema

#### WordPress Options (Settings)
- `autoship_token_auth` - OAuth2 access token
- `autoship_refresh_token` - OAuth2 refresh token
- `autoship_site_id` - QPilot site ID
- `autoship_client_id` / `autoship_client_secret` - OAuth2 credentials

#### Order Metadata
- `_qpilot_scheduled_order_processing_id` - Links WC order to scheduled order
- `_qpilot_site_meta` - Environment/version tracking

#### Legacy Tables (Being Phased Out)
- `{prefix}wc_autoship_schedules` - Legacy scheduled orders
- `{prefix}wc_autoship_schedule_items` - Legacy order items
- **Note:** Migrating to QPilot's cloud-hosted database

### Testing Infrastructure

**Framework:** PHPUnit 9.6 with Brain\Monkey for WordPress function mocking

**Test Structure:**
- `tests/bootstrap.php` - Initializes Brain\Monkey and defines constants
- Tests mirror `app/` structure (e.g., `tests/Core/ServiceContainerTest.php`)
- Coverage configured for `app/` directory only
- Strict configuration with `forceCoversAnnotation="true"`

**Writing Tests:**
- Use Brain\Monkey for WordPress function mocking
- All tests must have `@covers` annotations
- Test files must match the class being tested
- Run individual tests with `--filter` flag

## Directory Structure

```
autoship-cloud/
├── app/                          # Modern PSR-4 code (Autoship\ namespace)
│   ├── Core/                     # Core infrastructure (Plugin, ServiceContainer, ModuleManager, FeatureManager)
│   ├── Domain/                   # Domain models (PaymentIntegration, PaymentMethodType, QuickLinks, Nextime)
│   ├── Modules/                  # Feature modules (Quicklaunch, Nextime, ProductSync, QuickLinks)
│   └── Services/                 # External service clients (QPilot, Nextime, Logging, QuickLinks)
├── src/                          # Legacy procedural code
│   ├── api.php                   # Custom REST API endpoints
│   ├── api-wc.php                # WooCommerce REST API extensions
│   ├── admin.php                 # Admin pages and settings
│   ├── products.php              # Product management
│   ├── checkout.php              # Checkout integration
│   ├── orders.php                # Order handling
│   ├── payments.php              # Payment processing
│   └── [other functional modules]
├── tests/                        # PHPUnit tests (mirrors app/ structure)
├── js/                           # JavaScript files
├── templates/                    # WordPress template overrides
├── styles/                       # SCSS source files
└── vendor/                       # Composer dependencies
```

## Important Patterns and Conventions

### When Adding New Features

1. **Prefer Modern Architecture (`app/` directory)**
   - Use PSR-4 autoloading with `Autoship\` namespace
   - Implement as a Module if it's a major feature
   - Use the ServiceContainer for dependencies
   - Add feature flags via FeatureManager if needed

2. **For WooCommerce Integration**
   - Hook into existing lifecycle events (don't duplicate)
   - Check `src/` files for existing integration points
   - Use WooCommerce's data stores and CRUD when possible

3. **For QPilot API Integration**
   - Use `QPilotServiceClient` for API calls
   - Handle OAuth2 token refresh automatically
   - Log API requests/responses via the Logger service
   - Use Request/Response classes for type safety

### Code Standards

- **WordPress Coding Standards** are enforced via `composer compliance`
- **Excluded checks:** `Generic.Functions.CallTimePassByReference`, `Generic.Files.LineEndings`
- **Memory limit:** Set to 512M for PHPCS runs
- Follow PSR-4 autoloading conventions in `app/`

### Working with Modules

To create a new module:
1. Create class implementing `Autoship\Core\ModuleInterface`
2. Place in `app/Modules/YourModuleName/`
3. Register in `app/Core/Plugin.php` via ModuleManager
4. Implement all lifecycle methods: `register()`, `boot()`, `deactivate()`, `uninstall()`

### Logging

Use the centralized Logger service (`app/Services/Logging/Logger.php`):
- File-based logging via `FileSink`
- Configurable log levels
- Used throughout for API tracing and debugging

## Main Plugin Entry Point

**File:** `autoship.php`

The main plugin file:
1. Defines plugin metadata and constants (`Autoship_Version`, `Autoship_Plugin_Dir`, etc.)
2. Loads Composer autoloader
3. Registers activation/deactivation/uninstall handlers
4. On `plugins_loaded`: Checks WooCommerce requirements, loads legacy `src/` files, initializes `Autoship\Core\Plugin::run()`
5. Declares WooCommerce HPOS and Blocks compatibility via `before_woocommerce_init` hook

## Common Development Tasks

### Adding a New Product Setting
1. Add field in `src/products.php` admin UI
2. Save via `woocommerce_process_product_meta` hook
3. Sync to QPilot via `autoship_qpilot_product_upsert()`
4. Update product REST API response in `src/api-wc.php`

### Adding a New Payment Gateway
1. Create integration class in `app/Domain/PaymentIntegrations/`
2. Implement required methods from base integration pattern
3. Register in `PaymentIntegrationFactory.php`
4. Add gateway-specific metadata handling

### Creating a New REST Endpoint
1. Add endpoint registration in `src/api.php` via `register_rest_route()`
2. Implement permission callback
3. Add request validation
4. Handle QPilot synchronization if needed

### Running Single Test During Development
```bash
# Watch mode doesn't exist, but you can use:
vendor/bin/phpunit -c tests/phpunit.xml --filter YourTestClass

# For specific method:
vendor/bin/phpunit -c tests/phpunit.xml --filter testYourMethod
```

## External Documentation

- **Plugin Documentation:** https://support.autoship.cloud
- **QPilot API Docs:** https://docs.qpilot.cloud/reference/introduction
- **Developer Docs:** https://support.autoship.cloud/collection/598-developers

## Important Notes

- **HPOS Compatibility:** Plugin declares compatibility with WooCommerce High-Performance Order Storage
- **No Active Cron Jobs:** Background processing handled by QPilot hosted service
- **OAuth2 Tokens:** Automatically refresh when < 1 week from expiration
- **Environment Constants:** Defined in `app/Core/Environment.php` for API URLs
- **Template Overrides:** Customers can override templates by copying to their theme's `autoship-cloud/` directory
