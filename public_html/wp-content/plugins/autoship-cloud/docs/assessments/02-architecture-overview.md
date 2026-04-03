# Architecture Overview

## The Dual Architecture Problem

This codebase has **two completely different architectures** that must coexist:

```
autoship-cloud/
├── app/                    # MODERN: PSR-4, OOP, testable
│   ├── Core/               # Plugin, ServiceContainer, ModuleManager
│   ├── Domain/             # Value objects, entities
│   ├── Modules/            # Feature modules (QuickLinks, Quicklaunch, etc.)
│   └── Services/           # External integrations (QPilot, Nextime)
│
├── src/                    # LEGACY: Procedural, untestable, dangerous
│   ├── *.php               # 31 files, 37,000 lines, 957 functions
│   ├── QPilot/             # Old API client (superseded but still used)
│   └── Payments/           # Test gateway
│
└── tests/                  # Only covers app/, zero for src/
```

---

## Modern Architecture (app/)

### Core Infrastructure

| Component | File | Purpose |
|-----------|------|---------|
| Plugin | `Core/Plugin.php` | Singleton orchestrator, lifecycle |
| ServiceContainer | `Core/ServiceContainer.php` | Lazy-loading DI container |
| ModuleManager | `Core/ModuleManager.php` | Module lifecycle management |
| FeatureManager | `Core/FeatureManager.php` | Feature flag control |
| Environment | `Core/Environment.php` | System info provider |
| Installer | `Core/Installer.php` | Database schema management |

### Module System

Modules implement `ModuleInterface`:

```php
interface ModuleInterface {
    public function register(ServiceContainer $container): void;
    public function boot(ServiceContainer $container): void;
    public function deactivate(ServiceContainer $container): void;
    public function uninstall(ServiceContainer $container): void;
}
```

**Current Modules**:

| Module | Status | Test Coverage |
|--------|--------|---------------|
| QuickLinks | Production | ~70% |
| Quicklaunch | Production | ~50% |
| ProductSynchronizer | Production | LOW |
| Nextime | Production | LOW |

### Services Layer

| Service | Location | Purpose |
|---------|----------|---------|
| QPilot | `Services/QPilot/` | QPilot REST API client |
| Logging | `Services/Logging/` | Centralized logging |
| Nextime | `Services/Nextime/` | Shipping integration |
| QuickLinks | `Services/QuickLinks/` | QuickLinks business logic |

### Design Patterns Used

- **Service Container**: Dependency injection with lazy loading
- **Module Pattern**: Feature encapsulation with lifecycle hooks
- **Repository Pattern**: Data access abstraction
- **Strategy Pattern**: Storage backends (rate limiter, audit logging)
- **Factory Pattern**: Payment integrations, actions

---

## Legacy Architecture (src/)

### The Problem

```
src/
├── scheduled-orders.php    # 8,162 lines, 205 functions
├── payments.php            # 4,350 lines, 134 functions
├── products.php            # 3,841 lines, 113 functions
├── admin.php               # 2,965 lines, 93 functions
├── product-page.php        # 2,243 lines, 35 functions
├── orders.php              # 2,203 lines, 56 functions
├── [25 more files...]
└── Total: 31 files, ~37,000 lines, 957 functions
```

### Why It's Untestable

1. **No classes**: Everything is procedural functions
2. **No interfaces**: Cannot mock dependencies
3. **No DI**: Functions directly call WordPress globals
4. **Global state**: Direct `$_POST`, `$_GET`, `$wpdb` access
5. **Side effects**: Functions do I/O, database, API calls inline

### Coupling Diagram

```
scheduled-orders.php ──┬──> products.php
                       ├──> orders.php
                       ├──> customers.php
                       ├──> payments.php
                       ├──> admin.php
                       └──> [WordPress globals]

payments.php ──────────┬──> products.php
                       ├──> customers.php
                       ├──> QPilot/Client.php
                       └──> [WooCommerce globals]

[Every file calls every other file]
```

---

## Integration Points

### WordPress Hooks

```
Legacy src/ files register 464 hooks across 26 files:
- add_action: ~280
- add_filter: ~184

These are scattered, undocumented, and form a hidden dependency graph.
```

### WooCommerce Integration

| Hook | File | Purpose |
|------|------|---------|
| `woocommerce_process_product_meta` | products.php | Save product settings |
| `woocommerce_payment_complete` | checkout.php | Create scheduled orders |
| `woocommerce_checkout_order_processed` | checkout.php | Order creation |
| `woocommerce_my_account_*` | scheduled-orders.php | My Account pages |
| [100+ more hooks] | scattered | various |

### QPilot API

**Two clients exist**:

| Client | Location | Status |
|--------|----------|--------|
| Legacy | `src/QPilot/Client.php` | Still used by legacy code |
| Modern | `app/Services/QPilot/` | Proper architecture, interfaces |

This duplication causes:
- Confusion about which to use
- Token management in two places
- Different error handling patterns
- Maintenance burden

### REST API

| Endpoint | File | Purpose |
|----------|------|---------|
| `/autoshipcloud/v1/product/{id}` | api.php | Update product |
| `/wc-autoshipcloud/v1/orders` | api.php | Create orders from QPilot |
| [3 more endpoints] | api.php | Various |

**Problems**:
- No versioning strategy
- No rate limiting
- Mixed auth patterns
- TODO comments about migration

---

## Database Schema

### WordPress Options (Settings)

```php
// OAuth (plain text - security issue)
'autoship_token_auth'      // Access token
'autoship_refresh_token'   // Refresh token
'autoship_site_id'         // QPilot site ID
'autoship_client_id'       // OAuth client
'autoship_client_secret'   // OAuth secret (plain text!)

// Feature state
'autoship_quicklaunch_completed'
'autoship_quicklaunch_last_step'

// QuickLinks (110+ settings scattered)
'autoship_quicklinks_settings'
```

### Custom Tables

| Table | Purpose | Module |
|-------|---------|--------|
| `wp_autoship_rate_limits` | Rate limiting | QuickLinks |
| `wp_autoship_quicklink_audit_log` | Audit trail | QuickLinks |
| `wp_autoship_quicklink_confirmations` | Confirmations | QuickLinks |

### Legacy Tables (Deprecated)

| Table | Purpose | Status |
|-------|---------|--------|
| `wp_wc_autoship_schedules` | Old scheduled orders | DEPRECATED |
| `wp_wc_autoship_schedule_items` | Old order items | DEPRECATED |

---

## Request Flow Examples

### Product Save (Legacy)

```
1. User saves product in WP Admin
2. woocommerce_process_product_meta hook fires
3. products.php::autoship_save_product_meta() runs
4. Calls 5+ other functions in products.php
5. Calls src/QPilot/Client.php to sync
6. No error handling if API fails
7. No logging of what happened
8. No tests to verify behavior
```

### QuickLink Click (Modern)

```
1. Customer clicks link in email
2. WordPress rewrite rule matches /autoship/l/{slug}/{id}
3. QuickLinksService::handle_request() runs
4. Rate limiter checks (via interface, multiple backends)
5. Email scanner detection
6. Confirmation service
7. Action execution (via factory pattern)
8. Audit logging (via interface, multiple backends)
9. All steps testable and mockable
```

---

## The Migration Challenge

### What Works

- Modern `app/` architecture is solid
- QuickLinks proves the pattern works
- Service container enables proper DI
- Module system allows feature encapsulation

### What's Blocking Progress

1. **Legacy code is the core business logic** - Can't just delete it
2. **No test coverage** - Can't refactor safely
3. **Tight WooCommerce coupling** - Must maintain compatibility
4. **464 hooks** - Hidden dependencies everywhere
5. **Two API clients** - Which one is canonical?

### Migration Strategy

```
Phase 1: Wrap
├── Create interfaces for legacy functionality
├── Add facades that delegate to legacy code
└── Write tests against facades

Phase 2: Extract
├── Move logic from legacy into modern services
├── Keep facades working (same interface)
└── Tests prove nothing broke

Phase 3: Replace
├── Delete legacy code
├── Facades now use modern services
└── Tests still pass
```

---

## Recommendations

### Immediate

1. **Deprecate legacy QPilot client** - Add deprecation notices, migrate callers
2. **Document hook dependencies** - Create hook reference doc
3. **Add interfaces for legacy facades** - Enable testing

### Short-term

1. **Extract order creation service** - Highest risk area
2. **Extract payment processing service** - PCI implications
3. **Consolidate settings** - Single configuration object

### Long-term

1. **Complete legacy elimination** - All business logic in app/
2. **Full test coverage** - 80%+ on all code
3. **Proper event system** - Replace scattered hooks

---

*Document Version: 1.0*
*Last Updated: December 2024*
