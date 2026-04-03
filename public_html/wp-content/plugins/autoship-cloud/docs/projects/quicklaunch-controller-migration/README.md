# Quicklaunch Controller Migration

Migrate the Quicklaunch module from the legacy handler pattern to the modern controller pattern used in QuickLinks.

## Background

The Quicklaunch module was the first module built in the `app/` folder and uses a handler-based architecture with `BaseStepHandler`, `StepHandlerInterface`, and 8 separate handler classes. The QuickLinks module (the latest) uses a cleaner controller pattern with consolidated classes, explicit `register()` methods, and better separation of concerns.

## Current Architecture (Handlers)

```
app/Modules/Quicklaunch/
├── BaseStepHandler.php          # Abstract base with POST helpers
├── StepHandlerInterface.php     # Interface with single handle() method
├── SetupStepHandler.php         # Admin menu, assets, page rendering + step dispatch
├── LoginStepHandler.php         # AJAX: login flow
├── RegistrationStepHandler.php  # AJAX: registration flow
├── ProductStepHandler.php       # AJAX: product configuration
├── PaymentMethodStepHandler.php # AJAX: payment method selection
├── LeadStepHandler.php          # AJAX: lead capture
├── LogoutStepHandler.php        # AJAX: logout
├── ResetStepHandler.php         # AJAX: reset wizard
├── WidgetHandler.php            # Dashboard widget
└── QuicklaunchModule.php        # Module with 9 container registrations
```

### Problems with Current Pattern

1. **Hook registration in constructors** — Each handler registers its AJAX hook in `__construct()`, coupling instantiation to side effects
2. **Scattered dependencies** — 8 handlers with overlapping dependencies (FeatureManager, AccountManager, SitesManager)
3. **9 container registrations** — Module has to register each handler separately
4. **Mixed concerns** — `SetupStepHandler` handles admin menu, asset enqueue, page rendering, AND step dispatch
5. **39 procedural calls** — Heavy use of `autoship_*()` legacy functions
6. **Verbose module boot** — Must `$container->get()` each handler to trigger hook registration

## Target Architecture (Controllers)

```
app/Modules/Quicklaunch/
├── Controllers/
│   ├── QuicklaunchController.php         # All AJAX handlers consolidated
│   └── QuicklaunchSettingsController.php # Admin menu, assets, page rendering
└── QuicklaunchModule.php                 # Simplified: 2 registrations
```

### Benefits

1. **Explicit `register()` method** — Hook registration separate from construction
2. **Consolidated DI** — One constructor with all dependencies
3. **2 container registrations** — Controller + SettingsController
4. **Clear separation** — Settings/UI vs request handling
5. **Simpler boot** — Get controller, call `register()`
6. **Testable** — Mock dependencies, not WordPress functions

## Migration Plan

### Phase 1: Create Controllers (no deletion yet)

#### 1.1 Create `QuicklaunchController`

**File:** `app/Modules/Quicklaunch/Controllers/QuicklaunchController.php`

```php
namespace Autoship\Modules\Quicklaunch\Controllers;

class QuicklaunchController {
    private FeatureManagerInterface $feature_manager;
    private AccountManagerInterface $account_manager;
    private SitesManagerInterface $sites_manager;
    private EnvironmentInterface $environment;
    private InstallerInterface $installer;
    private LoggerInterface $logger;

    public function __construct(
        FeatureManagerInterface $feature_manager,
        AccountManagerInterface $account_manager,
        SitesManagerInterface $sites_manager,
        EnvironmentInterface $environment,
        InstallerInterface $installer,
        LoggerInterface $logger
    ) { ... }

    public function register(): void {
        add_action( 'wp_ajax_autoship_quicklaunch_login_handler', array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_registration_handler', array( $this, 'handle_registration' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_product_handler', array( $this, 'handle_product' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_payment_method_handler', array( $this, 'handle_payment_method' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_lead_handler', array( $this, 'handle_lead' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_logout_handler', array( $this, 'handle_logout' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_reset_handler', array( $this, 'handle_reset' ) );
    }

    // Move logic from each handler's handle() method
    public function handle_login(): void { ... }
    public function handle_registration(): void { ... }
    public function handle_product(): void { ... }
    public function handle_payment_method(): void { ... }
    public function handle_lead(): void { ... }
    public function handle_logout(): void { ... }
    public function handle_reset(): void { ... }

    // Move BaseStepHandler helpers as private methods
    private function get_post_string_value( string $key ): string { ... }
    private function get_post_int_value( string $key ): int { ... }
    private function get_post_bool_value( string $key ): bool { ... }
}
```

#### 1.2 Create `QuicklaunchSettingsController`

**File:** `app/Modules/Quicklaunch/Controllers/QuicklaunchSettingsController.php`

Extract from `SetupStepHandler`:
- `enqueue_menu()` — admin menu registration
- `enqueue_assets()` — CSS/JS loading
- `handle()` step rendering logic
- Template loading methods

```php
namespace Autoship\Modules\Quicklaunch\Controllers;

class QuicklaunchSettingsController {
    private FeatureManagerInterface $feature_manager;
    private EnvironmentInterface $environment;

    public function __construct(
        FeatureManagerInterface $feature_manager,
        EnvironmentInterface $environment
    ) { ... }

    public function register(): void {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_autoship_quicklaunch_step_handler', array( $this, 'handle_step' ) );
    }

    public function add_menu(): void { ... }
    public function enqueue_assets( string $hook ): void { ... }
    public function handle_step(): void { ... }

    // Template rendering methods
    private function render_welcome(): void { ... }
    private function render_lead(): void { ... }
    private function render_register(): void { ... }
    private function render_login(): void { ... }
    private function render_connection(): void { ... }
    private function render_product(): void { ... }
    private function render_payments(): void { ... }
    private function render_completed(): void { ... }
    private function render_dismiss(): void { ... }
}
```

#### 1.3 Create `QuicklaunchWidgetController` (optional)

**File:** `app/Modules/Quicklaunch/Controllers/QuicklaunchWidgetController.php`

Extract from `WidgetHandler`:

```php
namespace Autoship\Modules\Quicklaunch\Controllers;

class QuicklaunchWidgetController {
    public function register(): void {
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
    }

    public function add_dashboard_widget(): void { ... }
    public function render_widget(): void { ... }
}
```

### Phase 2: Update Module

#### 2.1 Simplify `QuicklaunchModule::register()`

```php
public function register( ServiceContainer $container ): void {
    $feature_manager = $this->feature_manager;

    $container->register(
        QuicklaunchController::class,
        function () use ( $feature_manager ) {
            $c = Plugin::get_service_container();
            return new QuicklaunchController(
                $feature_manager,
                $c->get( AccountManagerInterface::class ),
                $c->get( SitesManagerInterface::class ),
                $c->get( EnvironmentInterface::class ),
                $c->get( InstallerInterface::class ),
                $c->get( LoggerInterface::class )
            );
        }
    );

    $container->register(
        QuicklaunchSettingsController::class,
        function () use ( $feature_manager ) {
            $c = Plugin::get_service_container();
            return new QuicklaunchSettingsController(
                $feature_manager,
                $c->get( EnvironmentInterface::class )
            );
        }
    );

    $container->register(
        QuicklaunchWidgetController::class,
        function () {
            return new QuicklaunchWidgetController();
        }
    );
}
```

#### 2.2 Simplify `QuicklaunchModule::boot()`

```php
public function boot( ServiceContainer $container ): void {
    try {
        $logger = $container->get( LoggerInterface::class );
    } catch ( Exception $e ) {
        $logger = null;
    }

    try {
        // Register all controllers
        $container->get( QuicklaunchController::class )->register();
        $container->get( QuicklaunchSettingsController::class )->register();
        $container->get( QuicklaunchWidgetController::class )->register();

        $this->initialize( $container );
    } catch ( Exception $exception ) {
        if ( null !== $logger ) {
            $logger->log(
                __( 'Autoship Quicklaunch Exception', 'autoship' ),
                sprintf( 'Boot failed: %s', $exception->getMessage() )
            );
        }
    }
}
```

### Phase 3: Delete Legacy Files

After controllers are working and tested:

```
DELETE: app/Modules/Quicklaunch/BaseStepHandler.php
DELETE: app/Modules/Quicklaunch/StepHandlerInterface.php
DELETE: app/Modules/Quicklaunch/SetupStepHandler.php
DELETE: app/Modules/Quicklaunch/LoginStepHandler.php
DELETE: app/Modules/Quicklaunch/RegistrationStepHandler.php
DELETE: app/Modules/Quicklaunch/ProductStepHandler.php
DELETE: app/Modules/Quicklaunch/PaymentMethodStepHandler.php
DELETE: app/Modules/Quicklaunch/LeadStepHandler.php
DELETE: app/Modules/Quicklaunch/LogoutStepHandler.php
DELETE: app/Modules/Quicklaunch/ResetStepHandler.php
DELETE: app/Modules/Quicklaunch/WidgetHandler.php
```

### Phase 4: Update Tests

Consolidate 10 test files into 2-3:

```
DELETE: tests/Modules/Quicklaunch/LeadStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/LoginStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/LogoutStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/PaymentMethodStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/ProductStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/QuicklaunchModuleTest.php
DELETE: tests/Modules/Quicklaunch/RegistrationStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/ResetStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/SetupStepHandlerTest.php
DELETE: tests/Modules/Quicklaunch/WidgetHandlerTest.php

CREATE: tests/Modules/Quicklaunch/Controllers/QuicklaunchControllerTest.php
CREATE: tests/Modules/Quicklaunch/Controllers/QuicklaunchSettingsControllerTest.php
CREATE: tests/Modules/Quicklaunch/QuicklaunchModuleTest.php (rewrite)
```

## File Summary

| Action | Count | Files |
|---|---|---|
| **Create** | 3 | `QuicklaunchController.php`, `QuicklaunchSettingsController.php`, `QuicklaunchWidgetController.php` |
| **Modify** | 1 | `QuicklaunchModule.php` |
| **Delete** | 11 | All `*Handler.php` files + `BaseStepHandler.php` + `StepHandlerInterface.php` |
| **Delete tests** | 10 | All handler test files |
| **Create tests** | 3 | Controller test files |

## Future Improvements (Out of Scope)

These can be addressed in separate projects:

1. **Wrap `autoship_*()` calls** — Create `ProductServiceInterface` to wrap the 11 product operation calls in `ProductStepHandler`
2. **URL-based routing** — Consider rewrite rules instead of AJAX for wizard steps (like QuickLinks)
3. **Template controller** — Extract template rendering to a dedicated service

## Dependencies

- LoggerInterface refactoring (completed)
- `autoship_log_entry` removal (completed)

## Estimated Effort

- **Phase 1 (Create Controllers):** Medium — logic migration, no deletion
- **Phase 2 (Update Module):** Small — straightforward rewiring
- **Phase 3 (Delete Legacy):** Small — just deletion
- **Phase 4 (Update Tests):** Medium — rewrite test structure

## References

- QuickLinks controller pattern: `app/Modules/QuickLinks/Controllers/`
- QuickLinks module wiring: `app/Modules/QuickLinks/QuickLinksModule.php`
- Current Quicklaunch handlers: `app/Modules/Quicklaunch/`
