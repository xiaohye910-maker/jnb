# H-3: Two-Step Confirmation Implementation Plan

**Feature**: Two-Step Confirmation for QuickLinks
**Risk Level**: HIGH - Prevents race conditions and unauthorized access
**Estimated Effort**: 8-14 hours
**Date**: November 27, 2025

---

## Overview

Implement a two-step confirmation flow for QuickLink actions to prevent:
- Accidental clicks triggering actions
- Race condition double-charges
- Unauthorized access from compromised email accounts
- Phishing attacks exploiting one-click links

---

## Architecture Summary

### Flow Diagram

```
QuickLink URL Click
        │
        ▼
┌─────────────────────┐
│  Verify QuickLink   │
│  (existing flow)    │
└─────────────────────┘
        │
        ▼
┌─────────────────────┐
│ requiresConfirmation│──── false ───▶ Execute Action (existing)
│      check          │
└─────────────────────┘
        │ true
        ▼
┌─────────────────────┐
│ Create Confirmation │
│ Record (pending)    │
└─────────────────────┘
        │
        ▼
┌─────────────────────┐
│ Show Confirm Page   │◀────────────────────┐
│ (confirm.php)       │                     │
└─────────────────────┘                     │
        │                                   │
   ┌────┴────┐                              │
   ▼         ▼                              │
[Confirm] [Cancel]                          │
   │         │                              │
   ▼         ▼                              │
┌───────┐ ┌─────────┐                       │
│ POST  │ │  GET    │                       │
│confirm│ │ cancel  │                       │
└───────┘ └─────────┘                       │
   │         │                              │
   ▼         ▼                              │
┌───────────────────────┐  ┌─────────────┐  │
│ Validate:             │  │ Mark        │  │
│ - isPending?          │  │ Cancelled   │  │
│ - isExpired? (30 min) │  └─────────────┘  │
│ - Lock for update     │        │          │
└───────────────────────┘        ▼          │
        │                 [cancelled.php]   │
        │                                   │
   ┌────┴────────┬──────────┐               │
   │             │          │               │
   ▼             ▼          ▼               │
[Valid]    [Expired]  [Already Processed]   │
   │             │          │               │
   ▼             ▼          ▼               │
Execute    expired.php  already-processed.php
Action
   │
   ▼
Mark executed/failed
   │
   ▼
Redirect (thank-you/error)
```

---

## Implementation Components

### 1. Database Table

**Table**: `{prefix}autoship_quicklink_confirmations`

```sql
CREATE TABLE {prefix}autoship_quicklink_confirmations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    slug VARCHAR(255) NOT NULL,
    scheduled_order_id BIGINT NOT NULL,
    site_id BIGINT NOT NULL,
    action_type TINYINT NOT NULL,
    action_name VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    ip_address VARCHAR(45),
    submission_ip_address VARCHAR(45),
    ip_changed TINYINT(1) DEFAULT 0,
    user_agent TEXT,
    referer TEXT,
    customer_id BIGINT NULL,
    wp_user_id BIGINT NULL,
    shown_at DATETIME NOT NULL,
    submitted_at DATETIME NULL,
    executed_at DATETIME NULL,
    expired_at DATETIME NULL,
    time_to_submit_seconds INT NULL,
    verification_metadata LONGTEXT,
    execution_result LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_uuid (uuid),
    INDEX idx_status (status),
    INDEX idx_slug_order (slug, scheduled_order_id),
    INDEX idx_shown_at (shown_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status values**: `pending`, `confirmed`, `cancelled`, `expired`, `executed`, `failed`

---

### 2. Domain Layer

#### File: `app/Domain/QuickLinks/QuickLinkConfirmation.php`

```php
<?php
namespace Autoship\Domain\QuickLinks;

/**
 * QuickLink Confirmation entity with state machine.
 */
class QuickLinkConfirmation {
    // Properties matching database columns
    private int $id;
    private string $uuid;
    private string $slug;
    private int $scheduled_order_id;
    private int $site_id;
    private int $action_type;
    private string $action_name;
    private string $status = 'pending';
    private ?string $ip_address;
    private ?string $submission_ip_address;
    private bool $ip_changed = false;
    private ?string $user_agent;
    private ?string $referer;
    private ?int $customer_id;
    private ?int $wp_user_id;
    private string $shown_at;
    private ?string $submitted_at;
    private ?string $executed_at;
    private ?string $expired_at;
    private ?int $time_to_submit_seconds;
    private array $verification_metadata = [];
    private array $execution_result = [];

    // State machine methods
    public function is_pending(): bool;
    public function is_expired(): bool; // 30 min check
    public function mark_confirmed( string $submission_ip ): void;
    public function mark_cancelled(): void;
    public function mark_executed( array $result ): void;
    public function mark_failed( array $error ): void;
    public function mark_expired(): void;

    // Helper methods
    public function get_action_display_name(): string;
    public function get_expiration_minutes(): int; // Returns 30

    // Getters/setters with fluent interface
}
```

---

### 3. Service Layer

#### File: `app/Services/QuickLinks/Confirmation/Interfaces/ConfirmationRepositoryInterface.php`

```php
<?php
namespace Autoship\Services\QuickLinks\Confirmation\Interfaces;

use Autoship\Domain\QuickLinks\QuickLinkConfirmation;

interface ConfirmationRepositoryInterface {
    public function create( array $data ): QuickLinkConfirmation;
    public function find_by_uuid( string $uuid ): ?QuickLinkConfirmation;
    public function find_by_uuid_for_update( string $uuid ): ?QuickLinkConfirmation; // Pessimistic lock
    public function update( QuickLinkConfirmation $confirmation ): bool;
    public function delete_expired( int $days = 90 ): int; // Cleanup old records
}
```

#### File: `app/Services/QuickLinks/Confirmation/Interfaces/ConfirmationFunctionInterface.php`

```php
<?php
namespace Autoship\Services\QuickLinks\Confirmation\Interfaces;

/**
 * Abstracts WordPress functions for testability.
 */
interface ConfirmationFunctionInterface {
    public function current_time( string $type, bool $gmt = false );
    public function wp_generate_uuid4(): string;
    public function wp_create_nonce( string $action ): string;
    public function wp_verify_nonce( string $nonce, string $action );
    public function wp_json_encode( $data, int $flags = 0 );
    public function get_wpdb(): object;
    public function is_user_logged_in(): bool;
    public function get_current_user_id(): int;
    public function home_url( string $path = '' ): string;
    public function wp_safe_redirect( string $location, int $status = 302 ): void;
}
```

#### File: `app/Services/QuickLinks/Confirmation/Implementations/ConfirmationRepository.php`

```php
<?php
namespace Autoship\Services\QuickLinks\Confirmation\Implementations;

/**
 * WordPress database implementation of confirmation storage.
 *
 * Key features:
 * - Uses $wpdb for database operations
 * - Implements pessimistic locking via SELECT ... FOR UPDATE
 * - JSON encode/decode for metadata columns
 */
class ConfirmationRepository implements ConfirmationRepositoryInterface {

    private ConfirmationFunctionInterface $wp_functions;
    private string $table_name;

    public function __construct( ConfirmationFunctionInterface $wp_functions ) {
        $this->wp_functions = $wp_functions;
        $wpdb = $wp_functions->get_wpdb();
        $this->table_name = $wpdb->prefix . 'autoship_quicklink_confirmations';
    }

    public function find_by_uuid_for_update( string $uuid ): ?QuickLinkConfirmation {
        $wpdb = $this->wp_functions->get_wpdb();

        // Start transaction for pessimistic locking
        $wpdb->query( 'START TRANSACTION' );

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE uuid = %s FOR UPDATE",
                $uuid
            ),
            ARRAY_A
        );

        if ( ! $row ) {
            $wpdb->query( 'ROLLBACK' );
            return null;
        }

        return $this->hydrate( $row );
    }

    public function update( QuickLinkConfirmation $confirmation ): bool {
        $wpdb = $this->wp_functions->get_wpdb();

        $result = $wpdb->update(
            $this->table_name,
            $this->dehydrate( $confirmation ),
            [ 'id' => $confirmation->get_id() ]
        );

        // Commit transaction if one was started
        $wpdb->query( 'COMMIT' );

        return $result !== false;
    }
}
```

#### File: `app/Services/QuickLinks/Confirmation/QuickLinkConfirmationService.php`

```php
<?php
namespace Autoship\Services\QuickLinks\Confirmation;

/**
 * Service for managing QuickLink confirmations.
 *
 * Responsibilities:
 * - Creating confirmation records
 * - Processing confirmation submissions
 * - Handling cancellations
 * - Expiration checks
 */
class QuickLinkConfirmationService {

    public function __construct(
        private ConfirmationRepositoryInterface $repository,
        private ConfirmationFunctionInterface $wp_functions,
        private QuickLinkAuditService $audit_service
    ) {}

    /**
     * Create a new confirmation and return it.
     */
    public function create_confirmation(
        string $slug,
        int $scheduled_order_id,
        int $site_id,
        int $action_type,
        string $ip_address,
        string $user_agent,
        ?string $referer,
        ?int $customer_id,
        array $verification_metadata
    ): QuickLinkConfirmation;

    /**
     * Process a confirmation submission.
     * Returns: ['success' => bool, 'confirmation' => obj, 'error' => string|null]
     */
    public function process_confirmation( string $uuid, string $submission_ip ): array;

    /**
     * Cancel a pending confirmation.
     */
    public function cancel_confirmation( string $uuid ): ?QuickLinkConfirmation;

    /**
     * Get confirmation for display (no lock).
     */
    public function get_confirmation( string $uuid ): ?QuickLinkConfirmation;
}
```

---

### 4. Controller Updates

#### File: `app/Modules/QuickLinks/Controllers/QuickLinkController.php`

**New methods to add:**

```php
/**
 * Show confirmation page (H-3).
 * Called when verify response has requires_confirmation = true.
 */
private function show_confirmation(
    VerifyQuickLinkResponse $verification,
    int $site_id,
    string $slug,
    int $order_id,
    ?int $customer_id,
    string $ip_address,
    string $user_agent
): void {
    // Create confirmation record
    $confirmation = $this->confirmation_service->create_confirmation(
        $slug,
        $order_id,
        $site_id,
        $verification->action_type(),
        $ip_address,
        $user_agent,
        $_SERVER['HTTP_REFERER'] ?? null,
        $customer_id,
        [
            'redirect' => $verification->redirect() ? $verification->redirect()->to_array() : null,
            'error_redirect_url' => $verification->error_redirect_url(),
            'order_summary' => $verification->order_summary(),
        ]
    );

    // Log audit event
    $this->audit_service->log_confirmation_shown( $confirmation, $ip_address, $user_agent );

    // Render confirmation template
    $this->render_confirmation( $confirmation, $verification );
}

/**
 * Handle POST to /autoship/lc/confirm/{uuid}
 */
public function handle_confirm_submission( string $uuid ): void {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'quicklink_confirm_' . $uuid ) ) {
        $this->render_error( 'Invalid security token. Please try again.' );
        return;
    }

    $ip_address = $this->get_client_ip();
    $result = $this->confirmation_service->process_confirmation( $uuid, $ip_address );

    if ( ! $result['success'] ) {
        switch ( $result['error'] ) {
            case 'NOT_FOUND':
                $this->render_error( 'Confirmation not found.' );
                break;
            case 'ALREADY_PROCESSED':
                $this->render_already_processed( $result['confirmation'] );
                break;
            case 'EXPIRED':
                $this->render_expired( $result['confirmation'] );
                break;
        }
        return;
    }

    $confirmation = $result['confirmation'];

    // Log IP change if detected
    if ( $confirmation->is_ip_changed() ) {
        $this->audit_service->log_ip_change_detected( $confirmation );
    }

    // Execute the action
    $action_result = $this->execute_action_for_confirmation( $confirmation );

    // Handle result
    if ( $action_result->is_successful() ) {
        $this->handle_successful_confirmation( $confirmation, $action_result );
    } else {
        $this->handle_failed_confirmation( $confirmation, $action_result );
    }
}

/**
 * Handle GET to /autoship/lc/confirm/{uuid}
 * Shows status page (cannot confirm via GET for security).
 */
public function handle_confirm_get( string $uuid ): void {
    $confirmation = $this->confirmation_service->get_confirmation( $uuid );

    if ( ! $confirmation ) {
        $this->render_error( 'Confirmation not found.' );
        return;
    }

    if ( $confirmation->is_pending() ) {
        if ( $confirmation->is_expired() ) {
            $this->confirmation_service->mark_expired( $uuid );
            $this->render_expired( $confirmation );
        } else {
            // Cannot confirm via GET
            $this->render_error( 'This confirmation link cannot be accessed directly. Please use the button in your email.' );
        }
        return;
    }

    // Show appropriate status page
    switch ( $confirmation->get_status() ) {
        case 'expired':
            $this->render_expired( $confirmation );
            break;
        case 'cancelled':
            $this->render_cancelled( $confirmation );
            break;
        default:
            $this->render_already_processed( $confirmation );
    }
}

/**
 * Handle GET to /autoship/lc/cancel/{uuid}
 */
public function handle_cancel( string $uuid ): void {
    $confirmation = $this->confirmation_service->cancel_confirmation( $uuid );

    if ( ! $confirmation ) {
        $this->render_error( 'Confirmation not found.' );
        return;
    }

    $this->audit_service->log_confirmation_cancelled( $confirmation );
    $this->render_cancelled( $confirmation );
}
```

**Update to existing `handle_request()` method:**

```php
// After verification succeeds, check for confirmation requirement
$requires_confirmation = $verification->requires_confirmation() ?? true; // Default to true for safety

if ( $requires_confirmation ) {
    $this->audit_service->log_confirmation_required(
        $slug, $order_id, $action_type, $ip_address, $user_agent
    );
    $this->show_confirmation(
        $verification, $site_id, $slug, $order_id,
        $customer_id, $ip_address, $user_agent
    );
    return;
}

// Continue with direct execution (existing flow)...
```

---

### 5. URL Routing

**Route Structure** (mirroring Shopify's `/lc/` prefix pattern):

| Method | WordPress Route | Shopify Route | Handler |
|--------|-----------------|---------------|---------|
| GET | `/autoship/l/{slug}/{order_id}` | `/proxy/l/{slug}/{orderId}` | `handle_request()` (existing) |
| POST | `/autoship/lc/confirm/{uuid}` | `/proxy/lc/confirm/{uuid}` | `handle_confirm_submission()` |
| GET | `/autoship/lc/confirm/{uuid}` | `/proxy/lc/confirm/{uuid}` | `handle_confirm_get()` |
| GET | `/autoship/lc/cancel/{uuid}` | `/proxy/lc/cancel/{uuid}` | `handle_cancel()` |

**Note**: The `/lc/` prefix stands for "Link Confirmation" and avoids collision with the main `/l/{slug}/{orderId}` route.

**UUID Regex**: `[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}`

**Middleware**: Confirmation routes don't need email scanner detection or rate limiting (the confirmation flow itself provides protection).

**Update `QuickLinksModule::boot()`:**

```php
// Add confirmation routes
add_action( 'init', [ $this, 'register_confirmation_routes' ] );

public function register_confirmation_routes(): void {
    // UUID pattern for rewrite rules
    $uuid_pattern = '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})';

    // POST/GET /autoship/lc/confirm/{uuid}
    add_rewrite_rule(
        '^autoship/lc/confirm/' . $uuid_pattern . '/?$',
        'index.php?autoship_confirm_uuid=$matches[1]',
        'top'
    );

    // GET /autoship/lc/cancel/{uuid}
    add_rewrite_rule(
        '^autoship/lc/cancel/' . $uuid_pattern . '/?$',
        'index.php?autoship_cancel_uuid=$matches[1]',
        'top'
    );

    add_filter( 'query_vars', function( $vars ) {
        $vars[] = 'autoship_confirm_uuid';
        $vars[] = 'autoship_cancel_uuid';
        return $vars;
    } );
}

// In template_redirect handler:
add_action( 'template_redirect', [ $this, 'handle_confirmation_routes' ] );

public function handle_confirmation_routes(): void {
    $confirm_uuid = get_query_var( 'autoship_confirm_uuid' );
    $cancel_uuid = get_query_var( 'autoship_cancel_uuid' );

    // Validate UUID format
    $uuid_regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    if ( $confirm_uuid && preg_match( $uuid_regex, $confirm_uuid ) ) {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->controller->handle_confirm_submission( $confirm_uuid );
        } else {
            $this->controller->handle_confirm_get( $confirm_uuid );
        }
        exit;
    }

    if ( $cancel_uuid && preg_match( $uuid_regex, $cancel_uuid ) ) {
        $this->controller->handle_cancel( $cancel_uuid );
        exit;
    }
}
```

---

### 6. Templates

#### File: `templates/quicklinks/confirm.php`

Key elements:
- Store branding (logo or name)
- Action message: "You are about to **{action}** your subscription"
- Details box: Order ID, Action type
- Order summary (if available from API)
- Confirm button (POST form with nonce)
- Cancel link
- 30-minute expiration notice (subtle)
- Powered by Autoship footer

```php
<?php
/**
 * QuickLink Confirmation Page (H-3 Security Control)
 *
 * @var QuickLinkConfirmation $confirmation
 * @var array $verification_metadata
 * @var string $nonce
 */

defined( 'ABSPATH' ) || exit;

$action_name = $confirmation->get_action_display_name();
$display_action = str_replace( ' Now', '', $action_name );
$confirm_url = home_url( '/autoship/lc/confirm/' . $confirmation->get_uuid() );
$cancel_url = home_url( '/autoship/lc/cancel/' . $confirmation->get_uuid() );
$order_summary = $verification_metadata['order_summary'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Action - <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
    <!-- Styles (inline for standalone page) -->
</head>
<body>
    <div class="confirmation-card">
        <!-- Store Name -->
        <div class="merchant-logo-section">
            <div class="merchant-name-large"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
        </div>

        <!-- Action Message -->
        <p class="action-message">
            You are about to <strong><?php echo esc_html( strtolower( $display_action ) ); ?></strong> your subscription.
            <br>Please confirm to proceed.
        </p>

        <!-- Action Details -->
        <div class="details-box">
            <div class="details-header">Action Details</div>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Order ID</span>
                    <span class="detail-value">#<?php echo esc_html( $confirmation->get_scheduled_order_id() ); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Action</span>
                    <span class="detail-value"><?php echo esc_html( $display_action ); ?></span>
                </div>
            </div>
        </div>

        <?php if ( $order_summary ) : ?>
        <!-- Order Summary -->
        <div class="order-summary">
            <!-- Items, subtotal, shipping, tax, total -->
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="footer-actions">
            <form method="POST" action="<?php echo esc_url( $confirm_url ); ?>">
                <?php wp_nonce_field( 'quicklink_confirm_' . $confirmation->get_uuid() ); ?>
                <button type="submit" class="btn btn-primary">Confirm Action</button>
            </form>
            <a href="<?php echo esc_url( $cancel_url ); ?>" class="cancel-link">Cancel</a>
        </div>

        <!-- Powered By Footer -->
        <?php include __DIR__ . '/partials/powered-by.php'; ?>
    </div>
</body>
</html>
```

#### Files needed:
- `templates/quicklinks/confirm.php` - Main confirmation page
- `templates/quicklinks/cancelled.php` - Cancellation confirmation
- `templates/quicklinks/expired.php` - Expired link page
- `templates/quicklinks/already-processed.php` - Already processed page
- `templates/quicklinks/partials/powered-by.php` - Shared footer partial

---

### 7. Audit Logging Integration

Add new events to `QuickLinkAuditService`:

```php
public function log_confirmation_required( ... ): void;
public function log_confirmation_shown( QuickLinkConfirmation $confirmation, ... ): void;
public function log_confirmation_submitted( QuickLinkConfirmation $confirmation, ... ): void;
public function log_confirmation_cancelled( QuickLinkConfirmation $confirmation ): void;
public function log_confirmation_expired( QuickLinkConfirmation $confirmation ): void;
public function log_ip_change_detected( QuickLinkConfirmation $confirmation ): void;
public function log_confirmed_action_success( QuickLinkConfirmation $confirmation, ... ): void;
public function log_confirmed_action_failed( QuickLinkConfirmation $confirmation, ... ): void;
```

---

### 8. Database Migration

#### File: `app/Services/QuickLinks/Confirmation/ConfirmationTableMigration.php`

```php
<?php
namespace Autoship\Services\QuickLinks\Confirmation;

class ConfirmationTableMigration {

    public static function up(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'autoship_quicklink_confirmations';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(36) UNIQUE NOT NULL,
            slug VARCHAR(255) NOT NULL,
            scheduled_order_id BIGINT NOT NULL,
            site_id BIGINT NOT NULL,
            action_type TINYINT NOT NULL,
            action_name VARCHAR(50) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            ip_address VARCHAR(45),
            submission_ip_address VARCHAR(45),
            ip_changed TINYINT(1) DEFAULT 0,
            user_agent TEXT,
            referer TEXT,
            customer_id BIGINT NULL,
            wp_user_id BIGINT NULL,
            shown_at DATETIME NOT NULL,
            submitted_at DATETIME NULL,
            executed_at DATETIME NULL,
            expired_at DATETIME NULL,
            time_to_submit_seconds INT NULL,
            verification_metadata LONGTEXT,
            execution_result LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_uuid (uuid),
            INDEX idx_status (status),
            INDEX idx_slug_order (slug, scheduled_order_id),
            INDEX idx_shown_at (shown_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function down(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'autoship_quicklink_confirmations';
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
    }
}
```

---

## File Structure

```
app/Domain/QuickLinks/
├── QuickLinkConfirmation.php          # NEW - Entity class

app/Services/QuickLinks/Confirmation/
├── Interfaces/
│   ├── ConfirmationRepositoryInterface.php    # NEW
│   └── ConfirmationFunctionInterface.php      # NEW
├── Implementations/
│   ├── ConfirmationRepository.php             # NEW
│   └── WordPressConfirmationFunctions.php     # NEW
├── QuickLinkConfirmationService.php           # NEW
└── ConfirmationTableMigration.php             # NEW

app/Modules/QuickLinks/
├── QuickLinksModule.php               # UPDATE - Add route registration
└── Controllers/
    └── QuickLinkController.php        # UPDATE - Add confirmation handlers

templates/quicklinks/
├── thank-you.php                      # UPDATE - New polished styling
├── error.php                          # UPDATE - New polished styling
├── confirm.php                        # NEW
├── cancelled.php                      # NEW
├── expired.php                        # NEW
├── already-processed.php              # NEW
└── partials/
    └── powered-by.php                 # NEW - Shared footer

tests/Domain/QuickLinks/
└── QuickLinkConfirmationTest.php      # NEW

tests/Services/QuickLinks/Confirmation/
├── QuickLinkConfirmationServiceTest.php       # NEW
├── ConfirmationRepositoryTest.php             # NEW
└── ConfirmationTableMigrationTest.php         # NEW
```

---

## Implementation Order

### Phase 1: Core Infrastructure (3-4 hours)
1. Create database migration class
2. Create `QuickLinkConfirmation` entity
3. Create `ConfirmationFunctionInterface` and implementation
4. Create `ConfirmationRepositoryInterface` and implementation
5. Write unit tests for entity and repository

### Phase 2: Service Layer (2-3 hours)
1. Create `QuickLinkConfirmationService`
2. Add pessimistic locking implementation
3. Add expiration logic (30 minutes)
4. Add IP change detection
5. Write unit tests for service

### Phase 3: Controller & Routing (2-3 hours)
1. Add rewrite rules for `/autoship/lc/confirm/{uuid}` and `/autoship/lc/cancel/{uuid}`
2. Add `handle_confirm_submission()` method
3. Add `handle_confirm_get()` method
4. Add `handle_cancel()` method
5. Update `handle_request()` to check `requires_confirmation`
6. Add audit logging integration

### Phase 4: Templates (2-3 hours)
1. Create `powered-by.php` partial (shared footer)
2. Update `thank-you.php` with new polished styling
3. Update `error.php` with new polished styling
4. Create `confirm.php` with full styling
5. Create `cancelled.php`
6. Create `expired.php`
7. Create `already-processed.php`

### Phase 5: Module Registration & Testing (1-2 hours)
1. Register services in `QuickLinksModule`
2. Add table creation on plugin activation
3. Integration testing
4. Update documentation

### Phase 6: Cleanup Scheduler (0.5-1 hour)
1. Add Action Scheduler integration for cleanup job
2. Schedule daily cleanup of expired confirmations (90+ days old)
3. Test cleanup functionality

---

## Security Considerations

1. **Pessimistic Locking**: Use `SELECT ... FOR UPDATE` to prevent race conditions
2. **Nonce Verification**: WordPress nonces on all POST forms
3. **30-minute Expiration**: Links expire after 30 minutes for security
4. **IP Change Detection**: Log and track IP changes between view and submit
5. **Status Guards**: Only pending confirmations can be confirmed
6. **GET vs POST**: Confirmation requires POST (GET shows status only)
7. **UUID**: Use v4 UUIDs for unpredictable confirmation IDs

---

## API Integration Note

The QPilot API verify response should include:
- `requiresConfirmation: boolean` - Whether to show confirmation page
- `orderSummary: object` - Optional order details for confirmation page

**Decisions:**
- If `requiresConfirmation` is not in the API response, **default to `true`** for safety
- Confirmation scope is controlled by the API (not hardcoded to specific actions)
- Code defensively - always protect even if API fields are missing

---

## Cleanup Scheduler

Use Action Scheduler (via Synchronization Module) to clean up expired confirmation records:

```php
// Register cleanup action in QuickLinksModule
add_action( 'autoship_quicklinks_cleanup_confirmations', [ $this, 'cleanup_expired_confirmations' ] );

// Schedule daily cleanup if not already scheduled
if ( ! as_next_scheduled_action( 'autoship_quicklinks_cleanup_confirmations' ) ) {
    as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'autoship_quicklinks_cleanup_confirmations' );
}

public function cleanup_expired_confirmations(): void {
    $this->confirmation_repository->delete_expired( 90 ); // Delete records older than 90 days
}
```

---

## Template Styling Update

**Important**: Port the Shopify Blade styles exactly. This includes:
- New confirmation templates (confirm, cancelled, expired, already-processed)
- **Update existing templates** (`thank-you.php`, `error.php`) to match the new polished style

This ensures visual consistency across all QuickLinks pages.

---

## Testing Strategy

### Unit Tests (~40 tests)
- `QuickLinkConfirmationTest` - Entity state machine (12 tests)
- `ConfirmationRepositoryTest` - Database operations (10 tests)
- `QuickLinkConfirmationServiceTest` - Service logic (18 tests)

### Integration Tests
- Full confirmation flow
- Expiration handling
- Race condition prevention
- Nonce validation

---

## Success Criteria

1. All confirmation pages render correctly
2. Pessimistic locking prevents double-execution
3. 30-minute expiration works correctly
4. IP change detection logs properly
5. All audit events fire correctly
6. 40+ unit tests passing
7. Security score increases to 95/100

---

**Document Created**: November 27, 2025
**Author**: Claude Code
