# QuickLinks WordPress Architecture

**Version**: 1.1.0
**Last Updated**: November 28, 2025
**Status**: Production Ready

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Nextime Architecture Pattern](#nextime-architecture-pattern)
3. [Design Patterns](#design-patterns)
4. [Component Architecture](#component-architecture)
5. [Data Flow](#data-flow)
6. [URL Routing](#url-routing)
7. [Template System](#template-system)
8. [Security Architecture](#security-architecture)
9. [Two-Step Confirmation Flow](#two-step-confirmation-flow)
10. [Database Schema](#database-schema)
11. [WordPress Adaptations](#wordpress-adaptations)
12. [Design Decisions](#design-decisions)

---

## Overview

QuickLinks WordPress implementation follows the **Nextime architecture pattern** used throughout the Autoship Cloud plugin. This pattern provides clear separation of concerns across three layers: Domain, Services, and Modules.

### Key Principles

1. **Domain-Driven Design**: Business logic in Domain layer
2. **Interface-Based**: Services use interfaces for dependency injection
3. **WordPress Integration**: Module layer handles WordPress-specific concerns
4. **Defense-in-Depth**: 5 security layers for comprehensive protection
5. **Testability**: Each layer independently testable
6. **SOLID Principles**: Single responsibility, Open/closed, etc.

---

## Nextime Architecture Pattern

### Three-Layer Structure

```
┌─────────────────────────────────────────────┐
│            Module Layer                     │
│  (WordPress Integration)                    │
│  - QuickLinksModule                        │
│  - QuickLinksService (Facade)              │
│  - QuickLinkController                     │
│  - URL Rewrite Rules                       │
└─────────────────┬───────────────────────────┘
                  │ depends on
┌─────────────────▼───────────────────────────┐
│            Services Layer                   │
│  (Business Logic)                          │
│  - Core Services & Interfaces              │
│  - Action Strategies                       │
│  - DTOs                                    │
│  - Security Controls (H-1 to H-6)          │
│    ├── RateLimiter (H-5)                   │
│    ├── EmailScanner (H-2)                  │
│    ├── AuditLog (H-6)                      │
│    └── Confirmation (H-3)                  │
└─────────────────┬───────────────────────────┘
                  │ depends on
┌─────────────────▼───────────────────────────┐
│            Domain Layer                     │
│  (Core Business Concepts)                  │
│  - ActionType (Enum)                       │
│  - ActionResult (Value Object)             │
│  - QuickLinkVerification (Value Object)    │
│  - QuickLinkConfirmation (Entity)          │
│  - RedirectType (Enum)                     │
└─────────────────────────────────────────────┘
```

### Layer Responsibilities

**Domain Layer** (`app/Domain/QuickLinks/`):
- ✅ Pure PHP classes (no WordPress dependencies)
- ✅ Value objects, entities, and enums
- ✅ Business rules and validations
- ✅ Immutable where possible
- ✅ State machine for confirmation flow
- ❌ No external dependencies
- ❌ No I/O operations

**Services Layer** (`app/Services/QuickLinks/`):
- ✅ Business logic implementation
- ✅ Interface definitions
- ✅ Action strategies
- ✅ API communication (via repository)
- ✅ Security controls (Rate Limiting, Email Scanner, Audit Log, Confirmation)
- ✅ Data transformation (DTOs)
- ✅ Centralized logging via `Logger` service
- ❌ No WordPress-specific code in core services
- ❌ No direct HTTP requests (use repository)

**Module Layer** (`app/Modules/QuickLinks/`):
- ✅ WordPress integration
- ✅ Hook registration
- ✅ URL rewrite rules
- ✅ Request handling
- ✅ Template rendering
- ✅ Coordination of security checks
- ❌ No business logic
- ❌ No direct API calls

---

## Design Patterns

### 1. Factory Pattern

**Implementation**: `QuickLinkActionFactory`

**Purpose**: Create appropriate action strategy based on action type

```php
// Without factory (bad)
switch ( $action_type ) {
	case ActionType::RESUME:
		$action = new ResumeAction( $repository );
		break;
	// ...
}

// With factory (good)
$action = $factory->create( $action_type );
```

---

### 2. Strategy Pattern

**Implementation**: `QuickLinkActionInterface` + concrete strategies

**Purpose**: Encapsulate action-specific logic

**Interface**:
```php
interface QuickLinkActionInterface {
	public function execute( int $site_id, int $scheduled_order_id ): ActionResult;
	public function get_action_type(): int;
	public function get_action_name(): string;
}
```

**Strategies**:
- `ResumeAction` → Changes status to Active
- `PauseAction` → Changes status to Paused
- `ProcessNowAction` → Calls Retry endpoint
- `ReactivateAction` → Calls SafeActivate endpoint

---

### 3. Repository Pattern

**Implementation**: `QuickLinkRepositoryInterface` + implementation

**Purpose**: Abstract data access layer for QPilot API

**Interface**:
```php
interface QuickLinkRepositoryInterface {
	public function verify( ... ): ?array;
	public function consume( ... ): ?array;
	public function change_status( ... ): bool;
	public function retry_order( ... ): bool;
	public function safe_activate( ... ): bool;
}
```

---

### 4. State Machine Pattern

**Implementation**: `QuickLinkConfirmation` entity

**Purpose**: Manage confirmation flow state transitions

**States**:
```
pending → confirmed → executed
       ↘         ↘
        cancelled  failed
       ↘
        expired
```

**State Transitions**:
```php
// Valid transitions
$confirmation->mark_confirmed( $ip );  // pending → confirmed
$confirmation->mark_cancelled();        // pending → cancelled
$confirmation->mark_expired();          // pending → expired
$confirmation->mark_executed( $result ); // confirmed → executed
$confirmation->mark_failed( $error );    // confirmed → failed
```

---

### 5. Facade Pattern

**Implementation**: `QuickLinksService` (Module layer)

**Purpose**: Simplify WordPress integration

```php
class QuickLinksService {
	public function initialize(): void {
		$this->register_rewrite_rules();
		$this->register_query_vars();
		$this->register_template_redirect();
	}
}
```

---

### 6. Storage Strategy Pattern

**Used By**: RateLimiter, AuditLog

**Purpose**: Pluggable storage backends

**RateLimiter Strategies**:
- `TransientStorage` - WordPress transients (default)
- `DatabaseStorage` - Custom table
- `WpCacheStorage` - Object cache
- `FileStorage` - File-based

**AuditLog Strategies**:
- `DatabaseAuditLogger` - Custom table (primary)
- `FileAuditLogger` - File-based (fallback)

---

## Component Architecture

### Full Dependency Flow

```
QuickLinkController
  ↓ uses
├── RateLimiter (H-5)
├── EmailScannerDetector (H-2)
├── QuickLinkConfirmationService (H-3)
├── QuickLinkAuditService (H-6)
└── QuickLinkServiceInterface
      ↓ uses
    ├── QuickLinkActionFactory → QuickLinkActionInterface (strategies)
    │     ↓ logs to
    │   Logger (centralized logging)
    └── QuickLinkRepositoryInterface → QPilotServiceClient
          ↓ logs to              ↓ calls
        Logger                 QPilot REST API
```

### Logging Architecture

All QuickLink actions and repository methods log to the centralized `Logger` service:

```
┌────────────────────────────────────────────────────┐
│              Logger Service                         │
│  (app/Services/Logging/Logger.php)                 │
├────────────────────────────────────────────────────┤
│  Logger::info( 'Autoship QuickLinks', $message )   │
│  Logger::error( 'Autoship QuickLinks', $message )  │
└────────────────────────┬───────────────────────────┘
                         │
          ┌──────────────┴──────────────┐
          ▼                              ▼
┌─────────────────────┐        ┌─────────────────────┐
│     FileSink        │        │    NullSink         │
│  (active logging)   │        │ (inactive logging)  │
│                     │        │                     │
│ autoship-logs/      │        │ No output           │
│ autoship-{hash}.log │        │                     │
└─────────────────────┘        └─────────────────────┘
```

**Logged Components**:
- `PauseAction`, `ResumeAction`, `ProcessNowAction`, `ReactivateAction` - action execution
- `QuickLinkRepository` - API calls (change_status, safe_activate, retry_order)
- `QuickLinkAuditService` - audit entry logging status
- `DatabaseAuditLogger` - database insert failures

### ServiceContainer Registration

```php
// In QuickLinksModule::register()

// Core services
$container->register(
	QuickLinkRepositoryInterface::class,
	fn() => new QuickLinkRepository( $container->get( QPilotServiceClient::class ) )
);

$container->register(
	QuickLinkActionFactory::class,
	fn() => new QuickLinkActionFactory( $container->get( QuickLinkRepositoryInterface::class ) )
);

$container->register(
	QuickLinkServiceInterface::class,
	fn() => new QuickLinkService(
		$container->get( QuickLinkRepositoryInterface::class ),
		$container->get( QuickLinkActionFactory::class )
	)
);

// Security services
$container->register( RateLimiter::class, fn() => new RateLimiter( $storage ) );
$container->register( EmailScannerDetector::class, fn() => new EmailScannerDetector() );
$container->register( QuickLinkAuditService::class, fn() => new QuickLinkAuditService( $logger ) );
$container->register( QuickLinkConfirmationService::class, fn() => new QuickLinkConfirmationService( $repo ) );
```

---

## Data Flow

### Complete Request Flow (Two-Step Confirmation)

```
1. Customer clicks QuickLink
   URL: https://store.com/autoship/l/resume-now/12345?token=abc
   ↓

2. WordPress Rewrite Rules
   Matches: ^autoship/l/([^/]+)/([0-9]+)/?
   Rewrites to: index.php?autoship_quicklink=1&slug=$1&order_id=$2
   ↓

3. Template Redirect Hook
   QuickLinksService::handle_template_redirect()
   Detects: get_query_var('autoship_quicklink')
   ↓

4. QuickLinkController::handle_request()
   a. H-5: Check rate limit
      If exceeded → render rate-limited.php, exit

   b. H-2: Check email scanner
      If detected → render scanner-detected.php, exit

   c. Validate input (sanitize slug, absint order_id)

   d. Get WooCommerce customer ID
      Map WC ID → QPilot Customer ID via user meta

   e. H-1: Check mandatory login for Process Now
      If action_type = 2 && !logged_in → redirect to login

   f. Verify QuickLink (API call #1)
      POST /Sites/{id}/Quicklinks/Verify/{slug}

   g. Check if valid
      If invalid → render error.php, exit

   h. H-3: Check if requires confirmation
      If requiresConfirmation = true:
        - Create QuickLinkConfirmation (pending)
        - Store in database
        - Render confirm.php with order summary
        - Exit
   ↓

5. User submits confirmation form (H-3)
   POST /autoship/l/confirm/{uuid}
   ↓

6. QuickLinkController::handle_confirmation()
   a. Load confirmation by UUID

   b. Check if already processed
      If not pending → render already-processed.php, exit

   c. Check if expired (30 minutes)
      If expired → mark_expired(), render expired.php, exit

   d. Mark confirmed (records IP change detection)

   e. Create action via Factory
      $action = QuickLinkActionFactory::create( $action_type )

   f. Execute action (API call #2)
      PUT/POST /Sites/{id}/ScheduledOrders/{id}/{endpoint}

   g. Mark executed or failed

   h. Consume QuickLink (API call #3)
      POST /Sites/{id}/Quicklinks/{slug}/Consume

   i. H-6: Log audit entry

   j. Determine redirect type

   k. Redirect or render thank-you.php
```

### API Calls Per Request

| Call | Endpoint | Purpose | Count |
|------|----------|---------|-------|
| Verify | `POST /Quicklinks/Verify/{slug}` | Validate and get config | 1 |
| Action | `PUT/POST /ScheduledOrders/{id}/{action}` | Execute action | 1 |
| Consume | `POST /Quicklinks/{slug}/Consume` | Record usage | 1 |
| **Total** | | | **3** |

---

## URL Routing

### WordPress Rewrite Rules

**Main QuickLink Route**:
```php
add_rewrite_rule(
	'^autoship/l/([^/]+)/([0-9]+)/?',
	'index.php?autoship_quicklink=1&slug=$matches[1]&order_id=$matches[2]',
	'top'
);
```

**Confirmation Routes** (H-3):
```php
// Show confirmation status
add_rewrite_rule(
	'^autoship/l/confirm/([a-f0-9-]+)/?',
	'index.php?autoship_quicklink_confirm=1&uuid=$matches[1]',
	'top'
);

// Cancel confirmation
add_rewrite_rule(
	'^autoship/l/cancel/([a-f0-9-]+)/?',
	'index.php?autoship_quicklink_cancel=1&uuid=$matches[1]',
	'top'
);
```

---

## Template System

### Theme Override Support

Templates are theme-overridable using `TemplateLoader`:

**Template Locations** (in priority order):
1. **Theme Override**: `{theme}/autoship/quicklinks/thank-you.php`
2. **Plugin Default**: `{plugin}/templates/quicklinks/thank-you.php`

### Available Templates

| Template | Purpose | Security Control |
|----------|---------|------------------|
| `confirm.php` | Confirmation page with order summary | H-3 |
| `cancelled.php` | User cancelled confirmation | H-3 |
| `already-processed.php` | Double-submit protection | H-3 |
| `expired.php` | Confirmation timeout (30 min) | H-3 |
| `thank-you.php` | Success message | Core |
| `error.php` | Error display | Core |
| `rate-limited.php` | Rate limit exceeded | H-5 |
| `scanner-detected.php` | Email scanner blocked | H-2 |

### Template Variables

**confirm.php** receives:
```php
$verification   // QuickLinkVerification object
$confirmation   // QuickLinkConfirmation object
$action_name    // e.g., "Resume", "Pause"
$order_summary  // OrderSummary DTO with line items
$site_name      // Site/store name
```

---

## Security Architecture

### Multi-Layer Security (Defense-in-Depth)

```
┌─────────────────────────────────────────────┐
│  Layer 1: Rate Limiting (H-5)               │
│  • 5 requests per minute per IP/slug        │
│  • Prevents brute force attacks             │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 2: Email Scanner Detection (H-2)     │
│  • 25+ User-Agent patterns                  │
│  • Behavioral analysis                      │
│  • Blocks Process Now for scanners          │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 3: Mandatory Login (H-1)             │
│  • Process Now always requires login        │
│  • Protects financial actions               │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 4: Two-Step Confirmation (H-3)       │
│  • Confirmation page with order details     │
│  • 30-minute expiration                     │
│  • IP change detection                      │
│  • Pessimistic locking for race conditions  │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 5: Audit Logging (H-6)               │
│  • All events logged                        │
│  • Dual storage (DB + file fallback)        │
│  • Forensic analysis capability             │
└─────────────────────────────────────────────┘
```

---

## Two-Step Confirmation Flow

### State Diagram

```
     ┌─────────────────────────────────────┐
     │                                     │
     │     ┌───────────┐                   │
     │     │           │                   │
     │     │  PENDING  │◄─────── Created   │
     │     │           │                   │
     │     └─────┬─────┘                   │
     │           │                         │
     │    ┌──────┼──────┐                  │
     │    │      │      │                  │
     │    ▼      ▼      ▼                  │
     │ ┌─────┐ ┌───────┐ ┌─────────┐       │
     │ │CANC │ │CONFIRM│ │ EXPIRED │       │
     │ │ELLED│ │  ED   │ │         │       │
     │ └─────┘ └───┬───┘ └─────────┘       │
     │             │                       │
     │      ┌──────┴──────┐                │
     │      │             │                │
     │      ▼             ▼                │
     │  ┌────────┐   ┌────────┐            │
     │  │EXECUTED│   │ FAILED │            │
     │  └────────┘   └────────┘            │
     │                                     │
     └─────────────────────────────────────┘
```

### Confirmation Data Captured

| Field | Description |
|-------|-------------|
| uuid | Unique confirmation identifier |
| slug | QuickLink slug |
| scheduled_order_id | Order being modified |
| site_id | QPilot site ID |
| action_type | 0-3 (Resume, Pause, ProcessNow, Reactivate) |
| status | pending/confirmed/cancelled/expired/executed/failed |
| ip_address | Initial request IP |
| submission_ip_address | Confirmation submission IP |
| ip_changed | Boolean flag if IP changed |
| shown_at | When confirmation page was displayed |
| submitted_at | When user confirmed |
| executed_at | When action was executed |
| time_to_submit_seconds | Time between shown and submitted |
| verification_metadata | Full API response stored |
| execution_result | Action result stored |

---

## Database Schema

### Confirmations Table (H-3)

```sql
CREATE TABLE {prefix}autoship_quicklink_confirmations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    slug VARCHAR(255) NOT NULL,
    scheduled_order_id BIGINT NOT NULL,
    site_id BIGINT NOT NULL,
    customer_id BIGINT NULL,
    wp_user_id BIGINT NULL,
    action_type TINYINT NOT NULL,
    action_name VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    ip_address VARCHAR(45),
    submission_ip_address VARCHAR(45) NULL,
    ip_changed TINYINT(1) DEFAULT 0,
    user_agent TEXT,
    shown_at DATETIME NOT NULL,
    submitted_at DATETIME NULL,
    executed_at DATETIME NULL,
    time_to_submit_seconds INT NULL,
    verification_metadata LONGTEXT,
    execution_result LONGTEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_uuid (uuid),
    INDEX idx_status (status),
    INDEX idx_slug_order (slug, scheduled_order_id),
    INDEX idx_shown_at (shown_at)
);
```

### Audit Log Table (H-6)

```sql
CREATE TABLE {prefix}autoship_quicklinks_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    customer_id BIGINT NULL,
    wp_user_id BIGINT NULL,
    site_id BIGINT NULL,
    slug VARCHAR(255),
    scheduled_order_id BIGINT NULL,
    action_type TINYINT NULL,
    status VARCHAR(20),
    error_code VARCHAR(50) NULL,
    error_message TEXT NULL,
    context LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at),
    INDEX idx_slug (slug),
    INDEX idx_ip (ip_address)
);
```

---

## WordPress Adaptations

### From Shopify/Laravel to WordPress

| Shopify Concept | WordPress Equivalent | Implementation |
|-----------------|---------------------|----------------|
| **App Proxy** | Rewrite Rules | `add_rewrite_rule()` |
| **Shopify Signature (HMAC)** | WordPress Nonce | `wp_verify_nonce()` |
| **Laravel Routes** | Rewrite Rules + Template Redirect | `template_redirect` hook |
| **Blade Templates** | PHP Templates | `TemplateLoader::render()` |
| **Spatie Data DTOs** | Simple PHP Classes | Type-hinted properties |
| **Laravel Container** | ServiceContainer | Custom DI container |
| **Shopify Customer ID** | WooCommerce Customer ID | User meta lookup |
| **Eloquent Models** | WordPress APIs | `$wpdb`, `get_user_meta()` |
| **Laravel Middleware** | Hook priorities | `add_action()` priority |
| **Database Migrations** | Custom migration class | `ConfirmationTableMigration` |

---

## Design Decisions

### Decision 1: Why WordPress Rewrite Rules vs REST API?

**Chosen**: WordPress Rewrite Rules

**Reasoning**:
- ✅ Clean URLs matching Shopify pattern
- ✅ No REST API authentication complexity
- ✅ Better performance (no REST overhead)
- ✅ Consistent branding (`/autoship/l/...`)
- ❌ Requires rewrite flush on activation

---

### Decision 2: Why Database Storage for Confirmations?

**Chosen**: Custom database table

**Reasoning**:
- ✅ Reliable state machine persistence
- ✅ Query efficiency for cleanup
- ✅ Audit trail
- ✅ Pessimistic locking support
- ❌ Requires migration management

---

### Decision 3: Why Dual Storage for Audit Log?

**Chosen**: Database (primary) + File (fallback)

**Reasoning**:
- ✅ Database queries for analysis
- ✅ File fallback if DB fails
- ✅ No data loss
- ✅ Configurable retention

---

### Decision 4: Why Standalone Templates?

**Chosen**: Standalone CSS in each template (not shared partials)

**Reasoning**:
- ✅ Theme-overridable without breaking shared styles
- ✅ Each page self-contained
- ✅ No cascade issues
- ✅ Easier merchant customization

---

## Performance Considerations

### Response Time Breakdown

| Phase | Time (est.) | Notes |
|-------|-------------|-------|
| WordPress load | ~50ms | Standard WordPress bootstrap |
| Rate limit check | ~5ms | Transient/cache lookup |
| Scanner detection | ~2ms | User-Agent pattern matching |
| Nonce validation | ~2ms | WordPress nonce check |
| Customer ID lookup | ~10ms | User meta query |
| Verify API | ~200ms | QPilot API call |
| Confirmation create | ~10ms | Database insert |
| Template render | ~30ms | PHP template rendering |
| **Initial Load** | **~310ms** | Show confirmation page |

### Optimization Strategies

1. **Caching**: Customer ID mappings, site config
2. **Object Cache**: Use Redis/Memcached for transients
3. **Lazy Loading**: Only load security services when needed
4. **Database Indexes**: Optimized queries for confirmations

---

## 🔗 Related Documentation

- [README](../README.md) - Overview and quick start
- [Developer Guide](../developer-guide.md) - Code examples and patterns
- [Testing Guide](../testing-guide.md) - Testing procedures
- [Security Guide](../security-guide.md) - Security controls detail
- [Coding Standards](coding-standards.md) - WordPress standards compliance

---

**Last Updated**: November 28, 2025
**Architecture Version**: 1.0.0
