# QuickLinks WordPress Developer Guide

**Version**: 1.0.0
**Last Updated**: November 28, 2025
**Status**: Production Ready

---

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [File Structure](#file-structure)
3. [Common Development Tasks](#common-development-tasks)
4. [Code Examples](#code-examples)
5. [Working with Confirmations (H-3)](#working-with-confirmations-h-3)
6. [Security Controls](#security-controls)
7. [Adding New Action Types](#adding-new-action-types)
8. [Customizing Templates](#customizing-templates)
9. [Working with the Repository](#working-with-the-repository)
10. [WordPress Integration](#wordpress-integration)
11. [Debugging Guide](#debugging-guide)

---

## Quick Start

### Setting Up Development Environment

```bash
# 1. Navigate to plugin directory
cd wp-content/plugins/autoship-cloud

# 2. Install dependencies
composer install

# 3. Enable QuickLinks feature
wp option update autoship_features '{"quicklinks": true}' --format=json

# 4. Flush rewrite rules
wp rewrite flush

# 5. Run tests
composer test

# 6. Check specific QuickLinks tests
vendor/bin/phpunit --filter="QuickLink"
```

### Key URLs

| URL Pattern | Purpose |
|-------------|---------|
| `/autoship/l/{slug}/{order_id}` | Main QuickLink entry point |
| `/autoship/l/confirm/{uuid}` | Confirmation page |
| `/autoship/l/cancel/{uuid}` | Cancel confirmation |

---

## File Structure

```
app/
├── Domain/QuickLinks/
│   ├── ActionType.php              # Action type enum (Resume=0, Pause=1, ProcessNow=2, Reactivate=3)
│   ├── ActionResult.php            # Success/failure result value object
│   ├── QuickLinkVerification.php   # API verification response wrapper
│   ├── QuickLinkConfirmation.php   # H-3: Confirmation entity with state machine
│   └── RedirectType.php            # Redirect type enum
│
├── Services/QuickLinks/
│   ├── Interfaces/                 # Service interfaces for DI
│   ├── Implementations/            # Interface implementations
│   ├── Actions/                    # Action strategies (Resume, Pause, ProcessNow, Reactivate)
│   ├── DTOs/                       # Data transfer objects
│   ├── RateLimiter/               # H-5: Rate limiting service
│   ├── EmailScanner/              # H-2: Email scanner detection
│   ├── AuditLog/                  # H-6: Audit logging service
│   ├── Confirmation/              # H-3: Two-step confirmation service
│   └── QuickLinkActionFactory.php  # Factory for action strategies
│
├── Modules/QuickLinks/
│   ├── QuickLinksModule.php        # Module registration
│   ├── QuickLinksService.php       # WordPress integration facade
│   └── Controllers/
│       └── QuickLinkController.php # Main request handler
│
templates/quicklinks/
├── confirm.php                     # H-3: Confirmation page
├── cancelled.php                   # H-3: Cancellation page
├── already-processed.php           # H-3: Double-submit protection
├── expired.php                     # H-3: Expiration page
├── thank-you.php                   # Success page
├── error.php                       # Error page
├── rate-limited.php               # H-5: Rate limit exceeded
├── scanner-detected.php           # H-2: Email scanner blocked
└── partials/
    ├── styles.php                 # Shared styles (deprecated)
    └── powered-by.php             # Footer branding

tests/
├── Domain/QuickLinks/             # Domain layer tests
├── Services/QuickLinks/           # Service layer tests
└── Modules/QuickLinks/            # Module layer tests
```

---

## Common Development Tasks

### Task 1: Get QuickLinks Service

```php
use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Core\ServiceContainer;

$container = ServiceContainer::get_instance();
$service = $container->get( QuickLinkServiceInterface::class );
```

### Task 2: Verify a QuickLink

```php
$verification = $service->verify_quicklink(
    $site_id,
    $slug,
    $scheduled_order_id,
    $customer_id,
    $token,
    $ip_address,
    $user_agent
);

if ( ! $verification->is_valid() ) {
    // Handle invalid link
}

// Check if confirmation required (H-3)
if ( $verification->requires_confirmation() ) {
    // Create confirmation and show confirm.php template
}
```

### Task 3: Execute an Action

```php
use Autoship\Domain\QuickLinks\ActionType;

$result = $service->execute_action(
    $site_id,
    $verification->get_action_type(),
    $scheduled_order_id
);

if ( $result->is_successful() ) {
    // Action succeeded
} else {
    $error_code = $result->get_error_code();
    $error_message = $result->get_error_message();
}
```

### Task 4: Use the Action Factory Directly

```php
use Autoship\Services\QuickLinks\QuickLinkActionFactory;
use Autoship\Domain\QuickLinks\ActionType;

$factory = $container->get( QuickLinkActionFactory::class );
$action = $factory->create( ActionType::RESUME );

$result = $action->execute( $site_id, $scheduled_order_id );
```

---

## Code Examples

### Complete QuickLink Processing (With Confirmation)

```php
use Autoship\Domain\QuickLinks\ActionType;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService;

/**
 * Process a QuickLink request.
 */
public function handle_quicklink_request() {
    $slug = sanitize_text_field( get_query_var( 'slug' ) );
    $order_id = absint( get_query_var( 'order_id' ) );

    $site_id = get_option( 'autoship_site_id' );
    $customer_id = $this->get_qpilot_customer_id();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Step 1: Verify QuickLink
    $verification = $this->quicklink_service->verify_quicklink(
        $site_id,
        $slug,
        $order_id,
        $customer_id,
        $_GET['token'] ?? null,
        $ip_address,
        $user_agent
    );

    if ( ! $verification->is_valid() ) {
        $this->render_error( $verification->get_message() );
        return;
    }

    // Step 2: Check if confirmation required (H-3)
    if ( $verification->requires_confirmation() ) {
        $confirmation = $this->confirmation_service->create_confirmation(
            $slug,
            $order_id,
            $site_id,
            $verification->get_action_type(),
            ActionType::get_name( $verification->get_action_type() ),
            $ip_address,
            $user_agent
        );

        $confirmation->set_verification_metadata( $verification );
        $this->confirmation_service->save( $confirmation );

        $this->render_confirmation_page( $verification, $confirmation );
        return;
    }

    // Step 3: Execute action directly (no confirmation required)
    $this->execute_and_redirect( $verification, $site_id, $slug, $order_id );
}
```

### Processing a Confirmation Submission

```php
/**
 * Handle confirmation form submission.
 */
public function handle_confirmation_submit() {
    $uuid = sanitize_text_field( get_query_var( 'uuid' ) );
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Load confirmation
    $confirmation = $this->confirmation_service->find_by_uuid( $uuid );

    if ( ! $confirmation ) {
        $this->render_error( __( 'Confirmation not found.', 'autoship' ) );
        return;
    }

    // Check if already processed
    if ( $confirmation->is_processed() ) {
        $this->render_already_processed( $confirmation );
        return;
    }

    // Check if expired (30 minutes)
    if ( $confirmation->is_expired() ) {
        $confirmation->mark_expired();
        $this->confirmation_service->save( $confirmation );
        $this->render_expired( $confirmation );
        return;
    }

    // Mark as confirmed (records IP change)
    $confirmation->mark_confirmed( $ip_address );

    // Execute the action
    $result = $this->quicklink_service->execute_action(
        $confirmation->get_site_id(),
        $confirmation->get_action_type(),
        $confirmation->get_scheduled_order_id()
    );

    // Update confirmation status
    if ( $result->is_successful() ) {
        $confirmation->mark_executed( $result );
    } else {
        $confirmation->mark_failed( $result->get_error_message() );
    }

    $this->confirmation_service->save( $confirmation );

    // Consume the QuickLink
    $this->quicklink_service->consume_quicklink( /* ... */ );

    // Show result
    if ( $result->is_successful() ) {
        $this->render_thank_you( $confirmation );
    } else {
        $this->render_error( $result->get_error_message() );
    }
}
```

---

## Working with Confirmations (H-3)

### QuickLinkConfirmation Entity

```php
use Autoship\Domain\QuickLinks\QuickLinkConfirmation;

// Create a new confirmation
$confirmation = new QuickLinkConfirmation(
    $slug,
    $scheduled_order_id,
    $site_id,
    $action_type,
    $action_name,
    $ip_address,
    $user_agent
);

// Set optional data
$confirmation->set_customer_id( $customer_id );
$confirmation->set_wp_user_id( get_current_user_id() );
$confirmation->set_verification_metadata( $verification );

// State transitions
$confirmation->mark_confirmed( $submission_ip );  // pending → confirmed
$confirmation->mark_executed( $result );          // confirmed → executed
$confirmation->mark_failed( $error_message );     // confirmed → failed
$confirmation->mark_cancelled();                   // pending → cancelled
$confirmation->mark_expired();                     // pending → expired

// State checks
$confirmation->is_pending();                       // true if status is 'pending'
$confirmation->is_expired();                       // true if expired (30 min) or status is 'expired'
$confirmation->is_processed();                     // true if not 'pending'
$confirmation->has_ip_changed();                   // true if IP changed between show and submit
```

### State Machine Diagram

```
pending → confirmed → executed
       ↘         ↘
        cancelled  failed
       ↘
        expired
```

### Confirmation Service

```php
use Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService;

// Create confirmation
$confirmation = $this->service->create_confirmation(
    $slug,
    $order_id,
    $site_id,
    $action_type,
    $action_name,
    $ip_address,
    $user_agent
);

// Save to database
$this->service->save( $confirmation );

// Find by UUID
$confirmation = $this->service->find_by_uuid( $uuid );

// Find pending for slug/order
$confirmation = $this->service->find_pending( $slug, $order_id );
```

---

## Security Controls

### H-1: Mandatory Login for Process Now

```php
use Autoship\Domain\QuickLinks\ActionType;

// In controller - override API setting for financial actions
$requires_login = $verification->requires_login();

if ( ActionType::PROCESS_NOW === $action_type ) {
    $requires_login = true; // Always require login for financial actions
}

if ( $requires_login && ! is_user_logged_in() ) {
    $this->redirect_to_login( $slug, $order_id );
    return;
}
```

### H-2: Email Scanner Detection

```php
use Autoship\Services\QuickLinks\EmailScanner\EmailScannerDetector;

$detector = new EmailScannerDetector();

if ( $detector->is_scanner( $user_agent, $headers ) ) {
    // Check if this action should be blocked for scanners
    if ( in_array( $action_type, array( ActionType::PROCESS_NOW ), true ) ) {
        $this->render_scanner_detected();
        return;
    }
}
```

### H-5: Rate Limiting

```php
use Autoship\Services\QuickLinks\RateLimiter\RateLimiter;

if ( $this->rate_limiter->is_limited( $ip_address, $slug ) ) {
    $retry_after = $this->rate_limiter->get_retry_after( $ip_address, $slug );
    $this->render_rate_limited( $retry_after );
    return;
}

$this->rate_limiter->record_attempt( $ip_address, $slug );
```

### H-6: Audit Logging

```php
use Autoship\Services\QuickLinks\AuditLog\QuickLinkAuditService;

$this->audit_service->log_access( $slug, $order_id, $ip_address, $user_agent );
$this->audit_service->log_verify_success( $verification, $ip_address, $user_agent );
$this->audit_service->log_action_success( $action_type, $site_id, $slug, $order_id, ... );
$this->audit_service->log_scanner_detected( $slug, $order_id, $scanner_type, $ip_address, $user_agent );
```

---

## Adding New Action Types

### Step 1: Add Constant to ActionType

```php
// app/Domain/QuickLinks/ActionType.php
class ActionType {
    public const RESUME     = 0;
    public const PAUSE      = 1;
    public const PROCESS_NOW = 2;
    public const REACTIVATE = 3;
    public const NEW_ACTION = 4; // Add new constant

    private const NAMES = array(
        self::RESUME      => 'Resume',
        self::PAUSE       => 'Pause',
        self::PROCESS_NOW => 'ProcessNow',
        self::REACTIVATE  => 'Reactivate',
        self::NEW_ACTION  => 'NewAction', // Add name
    );
}
```

### Step 2: Create Action Strategy

```php
// app/Services/QuickLinks/Actions/NewAction.php
<?php
namespace Autoship\Services\QuickLinks\Actions;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkActionInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Domain\QuickLinks\ActionResult;
use Autoship\Domain\QuickLinks\ActionType;

class NewAction implements QuickLinkActionInterface {
    private QuickLinkRepositoryInterface $repository;

    public function __construct( QuickLinkRepositoryInterface $repository ) {
        $this->repository = $repository;
    }

    public function execute( int $site_id, int $scheduled_order_id ): ActionResult {
        try {
            $success = $this->repository->new_action_method( $site_id, $scheduled_order_id );

            if ( $success ) {
                return ActionResult::success( array(
                    'action'             => 'NewAction',
                    'scheduled_order_id' => $scheduled_order_id,
                ) );
            }

            return ActionResult::failure(
                'NEW_ACTION_FAILED',
                __( 'Failed to perform action.', 'autoship' )
            );
        } catch ( \Exception $e ) {
            return ActionResult::failure(
                'NEW_ACTION_EXCEPTION',
                $e->getMessage()
            );
        }
    }

    public function get_action_type(): int {
        return ActionType::NEW_ACTION;
    }

    public function get_action_name(): string {
        return 'NewAction';
    }
}
```

### Step 3: Register in Factory

```php
// app/Services/QuickLinks/QuickLinkActionFactory.php
public function create( int $action_type ): QuickLinkActionInterface {
    switch ( $action_type ) {
        case ActionType::RESUME:
            return new ResumeAction( $this->repository );
        case ActionType::PAUSE:
            return new PauseAction( $this->repository );
        case ActionType::PROCESS_NOW:
            return new ProcessNowAction( $this->repository );
        case ActionType::REACTIVATE:
            return new ReactivateAction( $this->repository );
        case ActionType::NEW_ACTION:
            return new NewAction( $this->repository ); // Add case
        default:
            throw new \InvalidArgumentException( "Unsupported action type: {$action_type}" );
    }
}
```

### Step 4: Add Tests

```php
// tests/Services/QuickLinks/Actions/NewActionTest.php
<?php
namespace Autoship\Tests\Services\QuickLinks\Actions;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Autoship\Services\QuickLinks\Actions\NewAction;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Domain\QuickLinks\ActionType;

/**
 * @covers \Autoship\Services\QuickLinks\Actions\NewAction
 */
class NewActionTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_execute_returns_success_when_repository_succeeds(): void {
        $repository = $this->createMock( QuickLinkRepositoryInterface::class );
        $repository->method( 'new_action_method' )->willReturn( true );

        $action = new NewAction( $repository );
        $result = $action->execute( 123, 456 );

        $this->assertTrue( $result->is_successful() );
    }
}
```

---

## Customizing Templates

### Theme Override

Templates can be overridden by copying to your theme:

```
yourtheme/
└── autoship/
    └── quicklinks/
        ├── confirm.php
        ├── thank-you.php
        └── error.php
```

### Template Variables

**confirm.php**:
```php
$verification   // QuickLinkVerification object
$confirmation   // QuickLinkConfirmation object
$action_name    // e.g., "Resume", "Pause"
$order_summary  // OrderSummary DTO (if available)
$site_name      // Site/store name
```

**thank-you.php**:
```php
$message        // Success message
$action_name    // e.g., "Resume"
$order_id       // Scheduled order ID
```

**error.php**:
```php
$message        // Error message
$error_code     // Error code (if available)
```

### Custom Styling

Each template includes standalone CSS for self-contained styling. To customize:

1. Override the template in your theme
2. Modify the `<style>` section
3. Or add your own CSS after the existing styles

---

## Working with the Repository

### Repository Interface

```php
interface QuickLinkRepositoryInterface {
    public function verify(
        int $site_id,
        string $slug,
        int $scheduled_order_id,
        ?int $customer_id = null,
        ?string $token = null,
        ?string $ip_address = null,
        ?string $user_agent = null
    ): ?array;

    public function consume(
        int $site_id,
        string $slug,
        int $scheduled_order_id,
        ?int $customer_id = null,
        ?string $token = null,
        bool $success = true,
        ?string $error_code = null,
        ?string $error_message = null,
        ?string $ip_address = null,
        ?string $user_agent = null
    ): ?array;

    public function change_status( int $site_id, int $scheduled_order_id, string $status ): bool;

    public function retry_order( int $site_id, int $scheduled_order_id ): bool;

    public function safe_activate( int $site_id, int $scheduled_order_id ): bool;
}
```

### Mock Repository for Testing

```php
$repository = $this->createMock( QuickLinkRepositoryInterface::class );

$repository->expects( $this->once() )
    ->method( 'verify' )
    ->willReturn( array(
        'valid' => true,
        'actionType' => 0,
        'requiresLogin' => false,
        'requiresConfirmation' => true,
    ) );

$repository->expects( $this->once() )
    ->method( 'change_status' )
    ->with( 123, 456, 'Active' )
    ->willReturn( true );
```

---

## WordPress Integration

### Registering Rewrite Rules

```php
// In QuickLinksService::register_rewrite_rules()
add_rewrite_rule(
    '^autoship/l/([^/]+)/([0-9]+)/?',
    'index.php?autoship_quicklink=1&slug=$matches[1]&order_id=$matches[2]',
    'top'
);

add_rewrite_rule(
    '^autoship/l/confirm/([a-f0-9-]+)/?',
    'index.php?autoship_quicklink_confirm=1&uuid=$matches[1]',
    'top'
);
```

### Handling Template Redirect

```php
// In QuickLinksService::handle_template_redirect()
add_action( 'template_redirect', function() {
    if ( get_query_var( 'autoship_quicklink' ) ) {
        $this->controller->handle_request();
        exit;
    }

    if ( get_query_var( 'autoship_quicklink_confirm' ) ) {
        $this->controller->handle_confirmation();
        exit;
    }
} );
```

### Service Container Registration

```php
// In QuickLinksModule::register()
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
```

---

## Logging

QuickLinks uses the centralized `Logger` service for detailed operational logging.

### Logger Usage

All QuickLink actions and repository methods log their operations:

```php
use Autoship\Services\Logging\Logger;

// Info level - normal operations
Logger::info( 'Autoship QuickLinks', 'Pause: Starting execution for order 12345 on site 123' );

// Error level - failures and exceptions
Logger::error( 'Autoship QuickLinks', 'Pause: Failed to pause order 12345. API returned false.' );
```

### What Gets Logged

**Action Classes** (PauseAction, ResumeAction, ProcessNowAction, ReactivateAction):
- Action start with order ID and site ID
- Success with order ID
- Failure with order ID and error details
- Exceptions with full error message

**Repository Methods** (QuickLinkRepository):
- API call details (endpoint, order ID, target status)
- Success responses with order ID
- API failures with error code and message

**Audit Service** (QuickLinkAuditService):
- Whether audit logging is enabled/disabled
- What entry is being logged (action type, order ID, success status)
- Which logger strategy succeeded or failed
- Fallback behavior when primary logger fails

**Database Audit Logger** (DatabaseAuditLogger):
- Table creation failures
- Database insert errors (includes `$wpdb->last_error`)

### Log Location

Logs are written to the Autoship log file:
```
wp-content/uploads/autoship-logs/autoship-{hash}.log
```

### Enabling Logging

```php
// Enable logging via WordPress option
update_option( 'autoship_logging_state', 'active' );

// Or via wp-config.php
define( 'AUTOSHIP_DEBUG', true );
```

### Example Log Output

```
[2025-11-28 10:30:45] [INFO] [Autoship QuickLinks] Pause: Starting execution for order 12345 on site 123
[2025-11-28 10:30:45] [INFO] [Autoship QuickLinks] ChangeStatus API call: PUT /Sites/123/ScheduledOrders/12345/Status/Paused
[2025-11-28 10:30:46] [INFO] [Autoship QuickLinks] ChangeStatus API success for order 12345. New status: Paused.
[2025-11-28 10:30:46] [INFO] [Autoship QuickLinks] Pause: Successfully paused order 12345
[2025-11-28 10:30:46] [INFO] [QuickLink Audit] Logging audit entry: action=1, order=12345, success=true, error=none
[2025-11-28 10:30:46] [INFO] [QuickLink Audit] Audit entry logged successfully via database
```

---

## Debugging Guide

### Enable Debug Mode

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'AUTOSHIP_DEBUG', true );
```

### Check Audit Log

```sql
-- View recent QuickLink events
SELECT * FROM wp_autoship_quicklinks_audit_log
ORDER BY created_at DESC
LIMIT 100;

-- Find events for specific IP
SELECT * FROM wp_autoship_quicklinks_audit_log
WHERE ip_address = '192.168.1.1'
ORDER BY created_at DESC;
```

### Check Confirmations

```sql
-- View recent confirmations
SELECT * FROM wp_autoship_quicklink_confirmations
ORDER BY created_at DESC
LIMIT 50;

-- Find pending confirmations
SELECT * FROM wp_autoship_quicklink_confirmations
WHERE status = 'pending'
ORDER BY shown_at DESC;
```

### Common Issues

**Issue**: QuickLinks return 404
**Solution**: Flush rewrite rules with `wp rewrite flush`

**Issue**: Rate limit blocking legitimate users
**Solution**: Check transients with `wp transient delete autoship_ratelimit_*`

**Issue**: Scanner detection false positives
**Solution**: Check User-Agent patterns in `ScannerPatterns.php`, adjust config

**Issue**: Confirmation expired immediately
**Solution**: Check server timezone settings, ensure consistent DateTime handling

---

## 🔗 Related Documentation

- [README](README.md) - Overview and quick start
- [Architecture](references/architecture.md) - Design patterns and decisions
- [Security Guide](security-guide.md) - Security controls
- [Testing Guide](testing-guide.md) - Testing procedures
- [Coding Standards](references/coding-standards.md) - WordPress standards

---

**Last Updated**: November 28, 2025
**Version**: 1.0.0
**Maintained By**: Development Team
