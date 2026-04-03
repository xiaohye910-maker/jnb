# QuickLinks WordPress Implementation Plan

**Version**: 1.0-draft
**Last Updated**: November 18, 2025
**Estimated Time**: 20-25 hours

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Phase 1: Domain Layer](#phase-1-domain-layer)
3. [Phase 2: Services Layer](#phase-2-services-layer)
4. [Phase 3: Module Layer](#phase-3-module-layer)
5. [Phase 4: Templates](#phase-4-templates)
6. [Phase 5: Testing](#phase-5-testing)
7. [Phase 6: Configuration & Integration](#phase-6-configuration--integration)
8. [Verification Checklist](#verification-checklist)

---

## Overview

This plan adapts the Shopify/Laravel QuickLinks implementation to WordPress/WooCommerce using the **Nextime architecture pattern** (Domain/Services/Modules).

### Key Adaptations

| Aspect | Shopify | WordPress |
|--------|---------|-----------|
| **URL Routing** | Laravel routes | WordPress rewrite rules |
| **Templates** | Blade views | PHP templates (theme-overridable) |
| **DTOs** | Spatie Data | Simple PHP classes |
| **DI Container** | Laravel | ServiceContainer |
| **Validation** | HMAC signature | Nonce + optional token |
| **Coding Style** | Laravel | WordPress Coding Standards (WPCS 3.0) |

---

## Phase 1: Domain Layer

**Estimated Time**: 2-3 hours

### 1.1 Create ActionType Enum

**File**: `app/Domain/QuickLinks/ActionType.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Enum for QuickLink action types.
 *
 * @package Autoship\Domain\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * QuickLink action type enumeration.
 *
 * Represents the different types of actions that can be performed
 * via QuickLinks: Resume, Pause, Process Now, and Reactivate.
 */
class ActionType {
	/**
	 * Resume scheduled order (change status to Active).
	 *
	 * @var int
	 */
	public const RESUME = 0;

	/**
	 * Pause scheduled order (change status to Paused).
	 *
	 * @var int
	 */
	public const PAUSE = 1;

	/**
	 * Process scheduled order immediately (Retry endpoint).
	 *
	 * @var int
	 */
	public const PROCESS_NOW = 2;

	/**
	 * Reactivate scheduled order (SafeActivate endpoint).
	 *
	 * @var int
	 */
	public const REACTIVATE = 3;

	/**
	 * Get human-readable name for action type.
	 *
	 * @param int $type The action type constant.
	 *
	 * @return string The action name.
	 */
	public static function get_name( int $type ): string {
		return match ( $type ) {
			self::RESUME      => 'Resume',
			self::PAUSE       => 'Pause',
			self::PROCESS_NOW => 'ProcessNow',
			self::REACTIVATE  => 'Reactivate',
			default           => 'Unknown',
		};
	}

	/**
	 * Check if action type is valid.
	 *
	 * @param int $type The action type to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid( int $type ): bool {
		return in_array(
			$type,
			array( self::RESUME, self::PAUSE, self::PROCESS_NOW, self::REACTIVATE ),
			true
		);
	}

	/**
	 * Parse action type from string.
	 *
	 * Handles various string formats: "Resume", "resume", "process_now", "Process Now".
	 *
	 * @param string|int $value The value to parse.
	 *
	 * @return int|null The action type constant or null if invalid.
	 */
	public static function parse( $value ): ?int {
		if ( is_int( $value ) ) {
			return self::is_valid( $value ) ? $value : null;
		}

		$normalized = strtolower( str_replace( array( ' ', '-' ), '_', $value ) );

		return match ( $normalized ) {
			'resume', '0'           => self::RESUME,
			'pause', '1'            => self::PAUSE,
			'processnow', 'process_now', 'process now', '2' => self::PROCESS_NOW,
			'reactivate', '3'       => self::REACTIVATE,
			default                 => null,
		};
	}
}
```

### 1.2 Create ActionResult Value Object

**File**: `app/Domain/QuickLinks/ActionResult.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Value object for QuickLink action execution results.
 *
 * @package Autoship\Domain\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * Represents the result of executing a QuickLink action.
 *
 * Encapsulates success/failure status, error information,
 * and optional metadata about the action execution.
 */
class ActionResult {
	/**
	 * Whether the action was successful.
	 *
	 * @var bool
	 */
	private bool $success;

	/**
	 * Error code if action failed.
	 *
	 * @var string|null
	 */
	private ?string $error_code;

	/**
	 * Error message if action failed.
	 *
	 * @var string|null
	 */
	private ?string $error_message;

	/**
	 * Additional metadata about the action.
	 *
	 * @var array
	 */
	private array $metadata;

	/**
	 * Constructor.
	 *
	 * @param bool        $success       Whether the action succeeded.
	 * @param string|null $error_code    Error code if failed.
	 * @param string|null $error_message Error message if failed.
	 * @param array       $metadata      Additional metadata.
	 */
	private function __construct(
		bool $success,
		?string $error_code = null,
		?string $error_message = null,
		array $metadata = array()
	) {
		$this->success       = $success;
		$this->error_code    = $error_code;
		$this->error_message = $error_message;
		$this->metadata      = $metadata;
	}

	/**
	 * Create a successful result.
	 *
	 * @param array $metadata Optional metadata.
	 *
	 * @return self
	 */
	public static function success( array $metadata = array() ): self {
		return new self( true, null, null, $metadata );
	}

	/**
	 * Create a failed result.
	 *
	 * @param string $error_code    Error code.
	 * @param string $error_message Error message.
	 * @param array  $metadata      Optional metadata.
	 *
	 * @return self
	 */
	public static function failure(
		string $error_code,
		string $error_message,
		array $metadata = array()
	): self {
		return new self( false, $error_code, $error_message, $metadata );
	}

	/**
	 * Check if action was successful.
	 *
	 * @return bool
	 */
	public function is_successful(): bool {
		return $this->success;
	}

	/**
	 * Get error code.
	 *
	 * @return string|null
	 */
	public function get_error_code(): ?string {
		return $this->error_code;
	}

	/**
	 * Get error message.
	 *
	 * @return string|null
	 */
	public function get_error_message(): ?string {
		return $this->error_message;
	}

	/**
	 * Get metadata.
	 *
	 * @return array
	 */
	public function get_metadata(): array {
		return $this->metadata;
	}

	/**
	 * Get specific metadata value.
	 *
	 * @param string $key     The metadata key.
	 * @param mixed  $default Default value if key not found.
	 *
	 * @return mixed
	 */
	public function get_metadata_value( string $key, $default = null ) {
		return $this->metadata[ $key ] ?? $default;
	}
}
```

### 1.3 Create QuickLinkVerification Value Object

**File**: `app/Domain/QuickLinks/QuickLinkVerification.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Value object for QuickLink verification results.
 *
 * @package Autoship\Domain\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * Represents the result of verifying a QuickLink.
 *
 * Contains validation status, action type, login requirements,
 * and redirect configuration from the QPilot API.
 */
class QuickLinkVerification {
	/**
	 * Whether the QuickLink is valid.
	 *
	 * @var bool
	 */
	private bool $valid;

	/**
	 * Whether the QuickLink requires login.
	 *
	 * @var bool
	 */
	private bool $requires_login;

	/**
	 * The action type to execute.
	 *
	 * @var int|null
	 */
	private ?int $action_type;

	/**
	 * Redirect configuration.
	 *
	 * @var array
	 */
	private array $redirect;

	/**
	 * Message to display.
	 *
	 * @var string|null
	 */
	private ?string $message;

	/**
	 * Constructor.
	 *
	 * @param bool        $valid          Whether QuickLink is valid.
	 * @param bool        $requires_login Whether login is required.
	 * @param int|null    $action_type    Action type to execute.
	 * @param array       $redirect       Redirect configuration.
	 * @param string|null $message        Message to display.
	 */
	public function __construct(
		bool $valid,
		bool $requires_login,
		?int $action_type,
		array $redirect = array(),
		?string $message = null
	) {
		$this->valid          = $valid;
		$this->requires_login = $requires_login;
		$this->action_type    = $action_type;
		$this->redirect       = $redirect;
		$this->message        = $message;
	}

	/**
	 * Check if QuickLink is valid.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		return $this->valid;
	}

	/**
	 * Check if login is required.
	 *
	 * @return bool
	 */
	public function requires_login(): bool {
		return $this->requires_login;
	}

	/**
	 * Get action type.
	 *
	 * @return int|null
	 */
	public function get_action_type(): ?int {
		return $this->action_type;
	}

	/**
	 * Get redirect configuration.
	 *
	 * @return array
	 */
	public function get_redirect(): array {
		return $this->redirect;
	}

	/**
	 * Get redirect type.
	 *
	 * @return int|null
	 */
	public function get_redirect_type(): ?int {
		return $this->redirect['type'] ?? null;
	}

	/**
	 * Get custom redirect URL.
	 *
	 * @return string|null
	 */
	public function get_custom_url(): ?string {
		return $this->redirect['customUrl'] ?? null;
	}

	/**
	 * Get UTM parameters for redirect.
	 *
	 * @return array
	 */
	public function get_utm_params(): array {
		$utm_json = $this->redirect['utmParams'] ?? '{}';
		$params   = json_decode( $utm_json, true );

		return is_array( $params ) ? $params : array();
	}

	/**
	 * Get message.
	 *
	 * @return string|null
	 */
	public function get_message(): ?string {
		return $this->message;
	}
}
```

### 1.4 Create RedirectType Enum

**File**: `app/Domain/QuickLinks/RedirectType.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Enum for QuickLink redirect types.
 *
 * @package Autoship\Domain\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * QuickLink redirect type enumeration.
 *
 * Determines where to redirect the customer after
 * successfully executing a QuickLink action.
 */
class RedirectType {
	/**
	 * Show thank you page with custom message.
	 *
	 * @var int
	 */
	public const THANK_YOU_PAGE = 0;

	/**
	 * Redirect to V2 customer portal.
	 *
	 * @var int
	 */
	public const V2_PORTAL = 1;

	/**
	 * Redirect to custom URL.
	 *
	 * @var int
	 */
	public const CUSTOM_URL = 2;

	/**
	 * Get human-readable name for redirect type.
	 *
	 * @param int $type The redirect type constant.
	 *
	 * @return string The redirect type name.
	 */
	public static function get_name( int $type ): string {
		return match ( $type ) {
			self::THANK_YOU_PAGE => 'ThankYouPage',
			self::V2_PORTAL      => 'V2Portal',
			self::CUSTOM_URL     => 'CustomUrl',
			default              => 'Unknown',
		};
	}

	/**
	 * Check if redirect type is valid.
	 *
	 * @param int $type The redirect type to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid( int $type ): bool {
		return in_array(
			$type,
			array( self::THANK_YOU_PAGE, self::V2_PORTAL, self::CUSTOM_URL ),
			true
		);
	}
}
```

### Phase 1 Testing

Create tests for each domain class:

- `tests/Domain/QuickLinks/ActionTypeTest.php`
- `tests/Domain/QuickLinks/ActionResultTest.php`
- `tests/Domain/QuickLinks/QuickLinkVerificationTest.php`
- `tests/Domain/QuickLinks/RedirectTypeTest.php`

**Verification**:
```bash
composer test:unit -- --filter="Domain\\\\QuickLinks"
```

---

## Phase 2: Services Layer

**Estimated Time**: 6-8 hours

### 2.1 Create Interfaces

#### 2.1.1 QuickLinkActionInterface

**File**: `app/Services/QuickLinks/Interfaces/QuickLinkActionInterface.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for QuickLink actions.
 *
 * @package Autoship\Services\QuickLinks\Interfaces
 * @since 3.2.0
 */

namespace Autoship\Services\QuickLinks\Interfaces;

use Autoship\Domain\QuickLinks\ActionResult;

/**
 * Interface for QuickLink action strategies.
 *
 * Each action type (Resume, Pause, ProcessNow, Reactivate)
 * implements this interface to handle its specific logic.
 */
interface QuickLinkActionInterface {
	/**
	 * Execute the QuickLink action.
	 *
	 * @param int $site_id             The QPilot site ID.
	 * @param int $scheduled_order_id  The scheduled order ID.
	 *
	 * @return ActionResult The result of the action execution.
	 */
	public function execute( int $site_id, int $scheduled_order_id ): ActionResult;

	/**
	 * Get the action type ID.
	 *
	 * @return int The action type constant from ActionType enum.
	 */
	public function get_action_type(): int;

	/**
	 * Get human-readable action name.
	 *
	 * @return string The action name.
	 */
	public function get_action_name(): string;
}
```

#### 2.1.2 QuickLinkServiceInterface

**File**: `app/Services/QuickLinks/Interfaces/QuickLinkServiceInterface.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for QuickLink service.
 *
 * @package Autoship\Services\QuickLinks\Interfaces
 * @since 3.2.0
 */

namespace Autoship\Services\QuickLinks\Interfaces;

use Autoship\Domain\QuickLinks\QuickLinkVerification;
use Autoship\Domain\QuickLinks\ActionResult;

/**
 * Service interface for QuickLink operations.
 *
 * Orchestrates the verify → execute → consume flow.
 */
interface QuickLinkServiceInterface {
	/**
	 * Verify a QuickLink.
	 *
	 * @param int         $site_id             The QPilot site ID.
	 * @param string      $slug                The QuickLink slug.
	 * @param int         $scheduled_order_id  The scheduled order ID.
	 * @param int|null    $customer_id         The QPilot customer ID.
	 * @param string|null $token               Optional security token.
	 * @param string|null $ip_address          User's IP address.
	 * @param string|null $user_agent          User's user agent.
	 *
	 * @return QuickLinkVerification The verification result.
	 */
	public function verify_quicklink(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		?string $ip_address = null,
		?string $user_agent = null
	): QuickLinkVerification;

	/**
	 * Execute a QuickLink action.
	 *
	 * @param int $site_id             The QPilot site ID.
	 * @param int $action_type         The action type to execute.
	 * @param int $scheduled_order_id  The scheduled order ID.
	 *
	 * @return ActionResult The action execution result.
	 */
	public function execute_action(
		int $site_id,
		int $action_type,
		int $scheduled_order_id
	): ActionResult;

	/**
	 * Record QuickLink consumption.
	 *
	 * @param int         $site_id             The QPilot site ID.
	 * @param string      $slug                The QuickLink slug.
	 * @param int         $scheduled_order_id  The scheduled order ID.
	 * @param int|null    $customer_id         The QPilot customer ID.
	 * @param string|null $token               Optional security token.
	 * @param bool        $success             Whether action succeeded.
	 * @param string|null $error_code          Error code if failed.
	 * @param string|null $error_message       Error message if failed.
	 * @param string|null $ip_address          User's IP address.
	 * @param string|null $user_agent          User's user agent.
	 *
	 * @return bool Whether consumption was recorded successfully.
	 */
	public function consume_quicklink(
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
	): bool;
}
```

#### 2.1.3 QuickLinkRepositoryInterface

**File**: `app/Services/QuickLinks/Interfaces/QuickLinkRepositoryInterface.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for QuickLink repository.
 *
 * @package Autoship\Services\QuickLinks\Interfaces
 * @since 3.2.0
 */

namespace Autoship\Services\QuickLinks\Interfaces;

/**
 * Repository interface for QuickLink data operations.
 *
 * Handles communication with QPilot API for QuickLink operations.
 */
interface QuickLinkRepositoryInterface {
	/**
	 * Call QPilot verify endpoint.
	 *
	 * @param int         $site_id             The QPilot site ID.
	 * @param string      $slug                The QuickLink slug.
	 * @param int         $scheduled_order_id  The scheduled order ID.
	 * @param int|null    $customer_id         The QPilot customer ID.
	 * @param string|null $token               Optional security token.
	 * @param string|null $ip_address          User's IP address.
	 * @param string|null $user_agent          User's user agent.
	 *
	 * @return array|null Response data or null on failure.
	 */
	public function verify(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		?string $ip_address = null,
		?string $user_agent = null
	): ?array;

	/**
	 * Call QPilot consume endpoint.
	 *
	 * @param int         $site_id             The QPilot site ID.
	 * @param string      $slug                The QuickLink slug.
	 * @param int         $scheduled_order_id  The scheduled order ID.
	 * @param int|null    $customer_id         The QPilot customer ID.
	 * @param string|null $token               Optional security token.
	 * @param bool        $success             Whether action succeeded.
	 * @param string|null $error_code          Error code if failed.
	 * @param string|null $error_message       Error message if failed.
	 * @param string|null $ip_address          User's IP address.
	 * @param string|null $user_agent          User's user agent.
	 *
	 * @return array|null Response data or null on failure.
	 */
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

	/**
	 * Change scheduled order status.
	 *
	 * @param int    $site_id             The QPilot site ID.
	 * @param int    $scheduled_order_id  The scheduled order ID.
	 * @param string $status              The new status ('Active' or 'Paused').
	 *
	 * @return bool Whether status was changed successfully.
	 */
	public function change_status( int $site_id, int $scheduled_order_id, string $status ): bool;

	/**
	 * Retry scheduled order (process now).
	 *
	 * @param int $site_id             The QPilot site ID.
	 * @param int $scheduled_order_id  The scheduled order ID.
	 *
	 * @return bool Whether retry was successful.
	 */
	public function retry_order( int $site_id, int $scheduled_order_id ): bool;

	/**
	 * Safe activate scheduled order.
	 *
	 * @param int $site_id             The QPilot site ID.
	 * @param int $scheduled_order_id  The scheduled order ID.
	 *
	 * @return bool Whether activation was successful.
	 */
	public function safe_activate( int $site_id, int $scheduled_order_id ): bool;
}
```

### 2.2 Create Action Implementations

Create four action classes following the same pattern:

- `app/Services/QuickLinks/Actions/ResumeAction.php`
- `app/Services/QuickLinks/Actions/PauseAction.php`
- `app/Services/QuickLinks/Actions/ProcessNowAction.php`
- `app/Services/QuickLinks/Actions/ReactivateAction.php`

**Example: ResumeAction.php**

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Resume action implementation.
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
 * Changes scheduled order status to Active.
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
	 * @param int $site_id             The QPilot site ID.
	 * @param int $scheduled_order_id  The scheduled order ID.
	 *
	 * @return ActionResult
	 */
	public function execute( int $site_id, int $scheduled_order_id ): ActionResult {
		try {
			$success = $this->repository->change_status( $site_id, $scheduled_order_id, 'Active' );

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
	 * @return int
	 */
	public function get_action_type(): int {
		return ActionType::RESUME;
	}

	/**
	 * Get action name.
	 *
	 * @return string
	 */
	public function get_action_name(): string {
		return 'Resume';
	}
}
```

### 2.3 Create DTOs

Create simple DTO classes:

- `app/Services/QuickLinks/DTOs/VerifyQuickLinkResponse.php`
- `app/Services/QuickLinks/DTOs/ConsumeQuickLinkResponse.php`

### 2.4 Create QuickLinkActionFactory

**File**: `app/Services/QuickLinks/QuickLinkActionFactory.php`

```php
<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Factory for creating QuickLink action strategies.
 *
 * @package Autoship\Services\QuickLinks
 * @since 3.2.0
 */

namespace Autoship\Services\QuickLinks;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkActionInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QuickLinks\Actions\ResumeAction;
use Autoship\Services\QuickLinks\Actions\PauseAction;
use Autoship\Services\QuickLinks\Actions\ProcessNowAction;
use Autoship\Services\QuickLinks\Actions\ReactivateAction;
use Autoship\Domain\QuickLinks\ActionType;

/**
 * Factory for creating QuickLink action instances.
 *
 * Uses the Factory pattern to create appropriate action
 * strategy based on action type.
 */
class QuickLinkActionFactory {
	/**
	 * Map of action types to class names.
	 *
	 * @var array<int, class-string<QuickLinkActionInterface>>
	 */
	private const ACTION_MAP = array(
		ActionType::RESUME      => ResumeAction::class,
		ActionType::PAUSE       => PauseAction::class,
		ActionType::PROCESS_NOW => ProcessNowAction::class,
		ActionType::REACTIVATE  => ReactivateAction::class,
	);

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
	 * Create action instance for given type.
	 *
	 * @param int $action_type The action type constant.
	 *
	 * @return QuickLinkActionInterface
	 * @throws \InvalidArgumentException If action type is not supported.
	 */
	public function create( int $action_type ): QuickLinkActionInterface {
		if ( ! isset( self::ACTION_MAP[ $action_type ] ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					/* translators: %d: action type */
					__( 'Unsupported action type: %d', 'autoship' ),
					$action_type
				)
			);
		}

		$class_name = self::ACTION_MAP[ $action_type ];

		return new $class_name( $this->repository );
	}

	/**
	 * Check if action type is supported.
	 *
	 * @param int $action_type The action type to check.
	 *
	 * @return bool
	 */
	public function is_supported( int $action_type ): bool {
		return isset( self::ACTION_MAP[ $action_type ] );
	}
}
```

### 2.5 Create Service Implementation

**File**: `app/Services/QuickLinks/Implementations/QuickLinkService.php`

(See developer-guide.md for complete implementation)

### 2.6 Create Repository Implementation

**File**: `app/Services/QuickLinks/Implementations/QuickLinkRepository.php`

Uses `QPilotServiceClient` to make API calls.

### Phase 2 Testing

Create tests for:
- Each action class
- QuickLinkActionFactory
- QuickLinkService
- QuickLinkRepository
- DTOs

**Verification**:
```bash
composer test:unit -- --filter="Services\\\\QuickLinks"
```

---

## Phase 3: Module Layer

**Estimated Time**: 4-5 hours

### 3.1 Create QuickLinksModule

**File**: `app/Modules/QuickLinks/QuickLinksModule.php`

Implements `ModuleInterface` and registers services in container.

### 3.2 Create QuickLinksService (Facade)

**File**: `app/Modules/QuickLinks/QuickLinksService.php`

WordPress-specific facade that:
- Registers URL rewrite rules
- Handles rewrite rule flushing
- Provides initialization hook

### 3.3 Create QuickLinkController

**File**: `app/Modules/QuickLinks/Controllers/QuickLinkController.php`

Handles the request flow:
1. Validate nonce/token
2. Get customer ID mapping (WooCommerce → QPilot)
3. Verify QuickLink
4. Check login requirement
5. Execute action
6. Consume QuickLink
7. Redirect or render template

### 3.4 Register URL Rewrite Rules

In `QuickLinksService::register_rewrite_rules()`:

```php
add_rewrite_rule(
	'^autoship/l/([^/]+)/([0-9]+)/?',
	'index.php?autoship_quicklink=1&slug=$matches[1]&order_id=$matches[2]',
	'top'
);

add_rewrite_tag( '%autoship_quicklink%', '([^&]+)' );
add_rewrite_tag( '%slug%', '([^&]+)' );
add_rewrite_tag( '%order_id%', '([0-9]+)' );
```

### Phase 3 Testing

Create tests for:
- QuickLinksModule registration
- QuickLinksService initialization
- QuickLinkController request handling
- Rewrite rules

**Verification**:
```bash
composer test:unit -- --filter="Modules\\\\QuickLinks"
```

---

## Phase 4: Templates

**Estimated Time**: 2-3 hours

### 4.1 Create Thank You Template

**File**: `templates/quicklinks/thank-you.php`

Theme-overridable template showing success message.

### 4.2 Create Error Template

**File**: `templates/quicklinks/error.php`

Theme-overridable template showing error message.

### 4.3 Update TemplateLoader

Ensure `TemplateLoader::render()` can locate QuickLinks templates.

### Phase 4 Testing

- Verify template rendering
- Test theme override functionality
- Check escaping and translation

---

## Phase 5: Testing

**Estimated Time**: 4-5 hours

### 5.1 Write Unit Tests

Following `tests/Services/Logging/LoggerTest.php` patterns:

- Use `PHPUnit\Framework\TestCase`
- Use `Brain\Monkey` for WordPress functions
- Use `@covers` annotations
- Mock interfaces with `createMock()`

### 5.2 Write Integration Tests

Test complete flow:
- URL request → controller → service → repository → template

### 5.3 Coverage Goals

- Domain: 100% coverage
- Services: 90%+ coverage
- Module: 85%+ coverage

### Verification

```bash
composer test:quicklinks
composer test:coverage
```

---

## Phase 6: Configuration & Integration

**Estimated Time**: 1-2 hours

### 6.1 Register Feature Flag

**File**: `app/Core/FeatureManager.php`

Add `'quicklinks'` to available features.

### 6.2 Register Module

**File**: `app/Core/Plugin.php`

```php
protected function register_modules() {
	// ... existing modules ...

	if ( FeatureManager::is_enabled( 'quicklinks' ) ) {
		$this->module_manager->register_module( new QuickLinksModule() );
	}
}
```

### 6.3 Update Composer Scripts

**File**: `composer.json`

```json
{
	"scripts": {
		"test:quicklinks": "phpunit --filter=QuickLinks"
	}
}
```

### Verification

```bash
# Enable feature
wp option update autoship_features '{"quicklinks": true}' --format=json

# Test module loads
composer test:quicklinks

# Check compliance
composer compliance
```

---

## Verification Checklist

### Code Quality

- [ ] All files have proper DocBlocks
- [ ] Methods use `snake_case` naming
- [ ] Indentation uses TABS
- [ ] All output is escaped
- [ ] All strings are translatable
- [ ] `composer compliance` passes
- [ ] No `phpcs:ignore` without justification

### Testing

- [ ] All domain classes have tests
- [ ] All service classes have tests
- [ ] All action classes have tests
- [ ] Module has integration tests
- [ ] `composer test` passes
- [ ] Coverage meets targets

### Functionality

- [ ] URL routing works (`/autoship/l/{slug}/{order-id}`)
- [ ] All 4 action types execute correctly
- [ ] All 3 redirect types work
- [ ] Templates render correctly
- [ ] Theme override works
- [ ] Customer ID mapping works
- [ ] Nonce validation works
- [ ] Error handling works

### Documentation

- [ ] All classes documented
- [ ] All methods documented
- [ ] Inline comments for complex logic
- [ ] README updated
- [ ] CHANGELOG updated

---

## Next Steps After Implementation

1. **Manual Testing**: Test with actual QPilot API
2. **Security Review**: Review nonce/token validation
3. **Performance Testing**: Measure response times
4. **User Acceptance Testing**: Get merchant feedback
5. **Documentation**: Create user guide for merchants

---

**Last Updated**: November 18, 2025
**Implementation Time**: 20-25 hours
**Priority**: Medium
