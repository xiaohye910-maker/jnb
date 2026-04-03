# QuickLinks WordPress Coding Standards

**Version**: 1.0-draft
**Last Updated**: November 18, 2025
**Standard**: WordPress Coding Standards (WPCS) 3.0

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [File Structure](#file-structure)
3. [Naming Conventions](#naming-conventions)
4. [Formatting](#formatting)
5. [Documentation](#documentation)
6. [Security](#security)
7. [Translation](#translation)
8. [Examples](#examples)
9. [Compliance Verification](#compliance-verification)

---

## Overview

All QuickLinks code MUST follow WordPress Coding Standards (WPCS) 3.0. This ensures consistency with the rest of the plugin and makes the code easier to maintain.

### Key Principles

- **snake_case** for methods and variables
- **TABS** for indentation (not spaces)
- **Comprehensive DocBlocks** for all classes, methods, and properties
- **Proper escaping** for all output
- **Translation-ready** strings

---

## File Structure

### File Headers

All PHP files must start with a phpcs:ignore comment and DocBlock:

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Brief description of the file.
 *
 * Longer description if needed. Explain the purpose
 * and responsibility of this file/class.
 *
 * @package Autoship\Domain\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Domain\QuickLinks;

// ... code ...
```

### File Organization

```php
<?php
// 1. File header (phpcs:ignore + DocBlock)

// 2. Namespace declaration
namespace Autoship\Services\QuickLinks;

// 3. Use statements (grouped and alphabetized)
use Autoship\Domain\QuickLinks\ActionResult;
use Autoship\Domain\QuickLinks\ActionType;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkActionInterface;

// 4. Class declaration
class ResumeAction implements QuickLinkActionInterface {
	// 5. Properties (private first, then protected, then public)

	// 6. Constructor

	// 7. Public methods

	// 8. Protected methods

	// 9. Private methods
}
```

### File Naming

- **Classes**: PascalCase matching class name
  - ✅ `ActionResult.php`
  - ❌ `action-result.php`

- **Interfaces**: PascalCase with "Interface" suffix
  - ✅ `QuickLinkActionInterface.php`

- **Templates**: kebab-case
  - ✅ `thank-you.php`
  - ✅ `error.php`

---

## Naming Conventions

### Classes

```php
// ✅ Correct: PascalCase
class QuickLinkActionFactory {
	// ...
}

// ❌ Wrong: snake_case
class quick_link_action_factory {
	// ...
}
```

### Methods

```php
// ✅ Correct: snake_case
public function execute_action(): ActionResult {
	// ...
}

public function get_action_type(): int {
	// ...
}

// ❌ Wrong: camelCase
public function executeAction(): ActionResult {
	// ...
}

public function getActionType(): int {
	// ...
}
```

### Variables

```php
// ✅ Correct: snake_case
$scheduled_order_id = 12345;
$action_result      = ActionResult::success();
$qpilot_customer_id = get_user_meta( $user_id, 'qpilot_customer_id', true );

// ❌ Wrong: camelCase
$scheduledOrderId = 12345;
$actionResult     = ActionResult::success();
$qpilotCustomerId = get_user_meta( $userId, 'qpilot_customer_id', true );
```

### Constants

```php
// ✅ Correct: SCREAMING_SNAKE_CASE
public const ACTION_TYPE_RESUME = 0;
public const PROCESS_NOW = 2;

private const ACTION_MAP = array(
	// ...
);

// ❌ Wrong: camelCase or PascalCase
public const ActionTypeResume = 0;
public const processNow = 2;
```

### Properties

```php
// ✅ Correct: snake_case with type hints
private QuickLinkRepositoryInterface $repository;
private int $site_id;
private ?string $error_message;

// ❌ Wrong: camelCase
private QuickLinkRepositoryInterface $repository;
private int $siteId;
private ?string $errorMessage;
```

---

## Formatting

### Indentation

**ALWAYS use TABS**, never spaces:

```php
<?php
// ✅ Correct: TABS
class ActionResult {
→   private bool $success;  // ← TAB (shown as →)
→
→   public function is_successful(): bool {
→   →   return $this->success;  // ← Two TABS
→   }
}

// ❌ Wrong: Spaces
class ActionResult {
    private bool $success;  // ← Four spaces (WRONG!)

    public function is_successful(): bool {
        return $this->success;  // ← Eight spaces (WRONG!)
    }
}
```

### Spacing

**Control structures**:
```php
// ✅ Correct: Space after control keyword
if ( $verification->is_valid() ) {
	// ...
}

foreach ( $items as $item ) {
	// ...
}

while ( $condition ) {
	// ...
}

// ❌ Wrong: No space after control keyword
if($verification->is_valid()) {
	// ...
}
```

**Function calls**:
```php
// ✅ Correct: Space after opening parenthesis, before closing
$result = $this->execute_action( $site_id, $order_id );
$items  = array( 'key' => 'value' );

// ❌ Wrong: No spaces
$result = $this->execute_action($site_id, $order_id);
$items  = array('key' => 'value');
```

**Operators**:
```php
// ✅ Correct: Spaces around operators
$total = $price + $tax;
$name  = $first_name . ' ' . $last_name;

if ( $x === 5 && $y !== 10 ) {
	// ...
}

// ❌ Wrong: No spaces
$total=$price+$tax;
$name=$first_name.' '.$last_name;

if ( $x===5 && $y!==10 ) {
	// ...
}
```

### Line Length

- Target: 100 characters max
- Acceptable: 120 characters
- Hard limit: 150 characters

```php
// ✅ Correct: Break long lines
$verification = $service->verify_quicklink(
	site_id: $site_id,
	slug: $slug,
	scheduled_order_id: $order_id,
	customer_id: $customer_id,
	token: $token,
	ip_address: $_SERVER['REMOTE_ADDR'],
	user_agent: $_SERVER['HTTP_USER_AGENT']
);

// ❌ Wrong: Single very long line
$verification = $service->verify_quicklink( $site_id, $slug, $order_id, $customer_id, $token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] );
```

### Arrays

**Short array syntax** (PHP 7.4+):
```php
// ✅ Correct: Short array syntax
$items = array( 'key' => 'value' );

// Note: WordPress still uses array() instead of []
// Follow plugin convention

// ❌ Wrong in WordPress context: Bracket syntax
$items = [ 'key' => 'value' ];
```

**Multi-line arrays**:
```php
// ✅ Correct: Aligned and formatted
$messages = array(
	'Resume'      => __( 'Subscription resumed!', 'autoship' ),
	'Pause'       => __( 'Subscription paused.', 'autoship' ),
	'ProcessNow'  => __( 'Order processing!', 'autoship' ),
	'Reactivate'  => __( 'Welcome back!', 'autoship' ),
);

// ❌ Wrong: Not aligned
$messages = array(
	'Resume' => __( 'Subscription resumed!', 'autoship' ),
	'Pause' => __( 'Subscription paused.', 'autoship' ),
	'ProcessNow' => __( 'Order processing!', 'autoship' ),
);
```

---

## Documentation

### Class DocBlocks

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Factory for creating QuickLink action strategies.
 *
 * Uses the Factory pattern to instantiate the appropriate action
 * implementation based on the action type. Each action type
 * (Resume, Pause, ProcessNow, Reactivate) has its own strategy class.
 *
 * @package Autoship\Services\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Services\QuickLinks;

class QuickLinkActionFactory {
	// ...
}
```

### Method DocBlocks

```php
/**
 * Execute the QuickLink action.
 *
 * Changes the scheduled order status to Active by calling
 * the QPilot API. Handles API failures gracefully.
 *
 * @param int $site_id             The QPilot site ID.
 * @param int $scheduled_order_id  The scheduled order ID.
 *
 * @return ActionResult The result of the action execution.
 */
public function execute( int $site_id, int $scheduled_order_id ): ActionResult {
	// ...
}
```

### Property DocBlocks

```php
/**
 * QuickLink repository instance.
 *
 * Handles communication with QPilot API for QuickLink operations.
 *
 * @var QuickLinkRepositoryInterface
 */
private QuickLinkRepositoryInterface $repository;

/**
 * Whether the QuickLink is valid.
 *
 * @var bool
 */
private bool $valid;

/**
 * Error message if action failed.
 *
 * @var string|null
 */
private ?string $error_message;
```

### Inline Comments

```php
// Good: Explain WHY, not WHAT
// Default to invalid if null for security
if ( ! ( $response->valid ?? false ) ) {
	// ...
}

// Security-first: Require login if unknown
if ( ( $response->requires_login ?? true ) && ! is_user_logged_in() ) {
	// ...
}

// Bad: Stating the obvious
// Check if valid is false
if ( ! $response->valid ) {
	// ...
}
```

---

## Security

### Output Escaping

**ALWAYS escape output**:

```php
// ✅ Correct: Escaped output
echo esc_html( $message );
echo esc_url( $redirect_url );
echo esc_attr( $class_name );

// Template
<h1><?php echo esc_html( $title ); ?></h1>
<a href="<?php echo esc_url( $url ); ?>">Link</a>
<div class="<?php echo esc_attr( $class ); ?>">Content</div>

// ❌ Wrong: Unescaped output
echo $message;
<h1><?php echo $title; ?></h1>
```

### Input Sanitization

**ALWAYS sanitize input**:

```php
// ✅ Correct: Sanitized input
$slug     = sanitize_text_field( $_GET['slug'] ?? '' );
$order_id = absint( $_GET['order_id'] ?? 0 );
$email    = sanitize_email( $_POST['email'] ?? '' );

// ❌ Wrong: Direct use of user input
$slug     = $_GET['slug'];
$order_id = $_GET['order_id'];
```

### Nonce Verification

```php
// ✅ Correct: Verify nonce
if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'autoship_quicklink_' . $slug . '_' . $order_id ) ) {
	wp_die( esc_html__( 'Security check failed.', 'autoship' ) );
}

// ❌ Wrong: No nonce verification
if ( ! isset( $_GET['_wpnonce'] ) ) {
	return;
}
```

### SQL Queries

```php
// ✅ Correct: Use WordPress APIs or prepared statements
$value = get_user_meta( $user_id, 'qpilot_customer_id', true );

global $wpdb;
$results = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}autoship_logs WHERE slug = %s",
	$slug
) );

// ❌ Wrong: Direct SQL injection risk
global $wpdb;
$results = $wpdb->get_results(
	"SELECT * FROM {$wpdb->prefix}autoship_logs WHERE slug = '{$slug}'"
);
```

---

## Translation

### Text Strings

**ALWAYS make strings translatable**:

```php
// ✅ Correct: Translatable
__( 'Your subscription has been resumed!', 'autoship' );
esc_html__( 'Invalid link.', 'autoship' );
esc_attr__( 'Resume subscription', 'autoship' );

// With variables (use sprintf)
sprintf(
	/* translators: %d: order ID */
	__( 'Order #%d has been updated.', 'autoship' ),
	$order_id
);

// ❌ Wrong: Hard-coded English
echo 'Your subscription has been resumed!';
```

### Pluralization

```php
// ✅ Correct: Use _n() for plurals
echo sprintf(
	_n(
		'%d item in your cart.',
		'%d items in your cart.',
		$count,
		'autoship'
	),
	$count
);

// ❌ Wrong: Manual plural handling
echo $count . ( $count === 1 ? ' item' : ' items' );
```

### Context

```php
// ✅ Correct: Use _x() for context
_x( 'Active', 'scheduled order status', 'autoship' );
_x( 'Active', 'user account status', 'autoship' );

// Different context, potentially different translation
```

---

## Examples

### Complete Class Example

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Resume action implementation.
 *
 * Changes scheduled order status to Active via QPilot API.
 *
 * @package Autoship\Services\QuickLinks\Actions
 * @since 3.2.0
 */

namespace Autoship\Services\QuickLinks\Actions;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkActionInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Domain\QuickLinks\ActionResult;
use Autoship\Domain\QuickLinks\ActionType;

/**
 * Resume action strategy.
 *
 * Implements the QuickLink action for resuming paused subscriptions.
 */
class ResumeAction implements QuickLinkActionInterface {
	/**
	 * QuickLink repository.
	 *
	 * @var QuickLinkRepositoryInterface
	 */
	private QuickLinkRepositoryInterface $repository;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkRepositoryInterface $repository The repository instance.
	 */
	public function __construct( QuickLinkRepositoryInterface $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Execute resume action.
	 *
	 * Changes the scheduled order status to Active.
	 *
	 * @param int $site_id             The QPilot site ID.
	 * @param int $scheduled_order_id  The scheduled order ID.
	 *
	 * @return ActionResult The result of the action execution.
	 */
	public function execute( int $site_id, int $scheduled_order_id ): ActionResult {
		try {
			$success = $this->repository->change_status(
				$site_id,
				$scheduled_order_id,
				'Active'
			);

			if ( $success ) {
				return ActionResult::success(
					array(
						'action'              => 'Resume',
						'scheduled_order_id'  => $scheduled_order_id,
					)
				);
			}

			return ActionResult::failure(
				'RESUME_FAILED',
				__( 'Failed to resume subscription.', 'autoship' ),
				array( 'scheduled_order_id' => $scheduled_order_id )
			);
		} catch ( \Exception $e ) {
			return ActionResult::failure(
				'RESUME_EXCEPTION',
				$e->getMessage(),
				array(
					'scheduled_order_id' => $scheduled_order_id,
					'exception'          => get_class( $e ),
				)
			);
		}
	}

	/**
	 * Get action type.
	 *
	 * @return int The action type constant.
	 */
	public function get_action_type(): int {
		return ActionType::RESUME;
	}

	/**
	 * Get action name.
	 *
	 * @return string The human-readable action name.
	 */
	public function get_action_name(): string {
		return 'Resume';
	}
}
```

### Template Example

```php
<?php
/**
 * QuickLink Success Template
 *
 * Displays success message after QuickLink action execution.
 *
 * @var string $action_name Action that was executed
 * @var string $message     Success message
 * @var int    $order_id    Scheduled order ID
 *
 * @package Autoship
 * @since 3.2.0
 */

defined( 'ABSPATH' ) || exit;

$messages = array(
	'Resume'      => __( 'Your subscription has been resumed!', 'autoship' ),
	'Pause'       => __( 'Your subscription has been paused.', 'autoship' ),
	'ProcessNow'  => __( 'Your order is being processed!', 'autoship' ),
	'Reactivate'  => __( 'Welcome back! Your subscription is active.', 'autoship' ),
);

$default_message = $messages[ $action_name ] ?? $message ?? __( 'Action completed successfully.', 'autoship' );
?>

<div class="autoship-quicklink-success">
	<h1><?php echo esc_html( $default_message ); ?></h1>

	<p><?php
		echo esc_html(
			sprintf(
				/* translators: %d: order ID */
				__( 'Order #%d has been updated.', 'autoship' ),
				$order_id
			)
		);
	?></p>

	<div class="actions">
		<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button">
			<?php esc_html_e( 'View My Account', 'autoship' ); ?>
		</a>
	</div>
</div>
```

---

## Compliance Verification

### Run PHP_CodeSniffer

```bash
# Check all QuickLinks code
composer compliance

# Check specific directory
vendor/bin/phpcs app/Domain/QuickLinks --standard=WordPress

# Check specific file
vendor/bin/phpcs app/Services/QuickLinks/Actions/ResumeAction.php --standard=WordPress

# Auto-fix issues (where possible)
vendor/bin/phpcbf app/Domain/QuickLinks --standard=WordPress
```

### Common PHPCS Errors

**1. Indentation (tabs vs spaces)**:
```
ERROR: Spaces must be used to indent lines; tabs are not allowed
```
**Fix**: Replace all spaces with tabs

**2. Missing DocBlock**:
```
ERROR: Missing doc comment for function execute()
```
**Fix**: Add complete DocBlock

**3. Unescaped output**:
```
ERROR: All output should be run through an escaping function
```
**Fix**: Use `esc_html()`, `esc_url()`, etc.

**4. Non-translatable string**:
```
WARNING: String literals should be internationalized
```
**Fix**: Wrap in `__()`, `esc_html__()`, etc.

### Pre-Commit Checklist

Before committing QuickLinks code:

- [ ] Run `composer compliance` with no errors
- [ ] All strings are translatable
- [ ] All output is escaped
- [ ] All input is sanitized
- [ ] DocBlocks are complete
- [ ] TABS for indentation
- [ ] snake_case for methods/variables
- [ ] Tests pass (`composer test:quicklinks`)

---

## Common Violations

### Violation 1: CamelCase in WordPress Context

```php
// ❌ Wrong: Laravel/PHP convention
public function getActionType(): int {
	return $this->actionType;
}

// ✅ Correct: WordPress convention
public function get_action_type(): int {
	return $this->action_type;
}
```

### Violation 2: Spaces Instead of Tabs

```php
// ❌ Wrong: 4 spaces
    public function execute() {
        return true;
    }

// ✅ Correct: 1 tab
→   public function execute() {
→   →   return true;
→   }
```

### Violation 3: Missing Translation

```php
// ❌ Wrong: Hard-coded string
throw new \Exception( 'Action failed' );

// ✅ Correct: Translatable
throw new \Exception( __( 'Action failed', 'autoship' ) );
```

### Violation 4: Unescaped Output

```php
// ❌ Wrong: Direct echo
echo $message;
<h1><?php echo $title; ?></h1>

// ✅ Correct: Escaped
echo esc_html( $message );
<h1><?php echo esc_html( $title ); ?></h1>
```

### Violation 5: Missing Nonce Check

```php
// ❌ Wrong: No security check
$action = $_GET['action'];

// ✅ Correct: Nonce verification
if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'my_action' ) ) {
	wp_die( esc_html__( 'Security check failed.', 'autoship' ) );
}
$action = sanitize_text_field( $_GET['action'] ?? '' );
```

---

## IDE Configuration

### PhpStorm

**Settings → Editor → Code Style → PHP**:
- Tab size: 4
- Indent: 4
- Use tab character: ✓ Checked

**Settings → PHP → Quality Tools → PHP_CodeSniffer**:
- Configuration: `/path/to/vendor/bin/phpcs`
- Coding standard: WordPress

### VS Code

**`.vscode/settings.json`**:
```json
{
	"editor.insertSpaces": false,
	"editor.tabSize": 4,
	"editor.detectIndentation": false,
	"phpcs.standard": "WordPress",
	"phpcs.executablePath": "./vendor/bin/phpcs"
}
```

---

## Quick Reference

| Aspect | WordPress Standard | Example |
|--------|-------------------|---------|
| **Methods** | snake_case | `get_action_type()` |
| **Variables** | snake_case | `$scheduled_order_id` |
| **Classes** | PascalCase | `QuickLinkActionFactory` |
| **Constants** | SCREAMING_SNAKE_CASE | `ACTION_TYPE_RESUME` |
| **Indentation** | TABS | `→   $x = 1;` |
| **Arrays** | `array()` syntax | `array( 'key' => 'value' )` |
| **Escaping** | Context-specific | `esc_html()`, `esc_url()` |
| **Translation** | `__()` family | `__( 'Text', 'autoship' )` |
| **Nonces** | `wp_*_nonce()` | `wp_verify_nonce()` |

---

## 🔗 Related Documentation

- [Implementation Plan](../wordpress/implementation-plan.md) - What to build
- [Developer Guide](../wordpress/developer-guide.md) - How to build
- [Testing Guide](../wordpress/testing-guide.md) - How to test
- [Architecture](architecture.md) - Design decisions

---

## External Resources

- [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- [WordPress Documentation Standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/)
- [PHP_CodeSniffer Wiki](https://github.com/squizlabs/PHP_CodeSniffer/wiki)

---

**Last Updated**: November 18, 2025
**Standard**: WordPress Coding Standards (WPCS) 3.0
**Maintained By**: Development Team
