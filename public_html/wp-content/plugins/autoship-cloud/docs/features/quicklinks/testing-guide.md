# QuickLinks Testing Guide

**Version**: 1.1.0 (Production Ready)
**Last Updated**: November 28, 2025
**Test Count**: 651 tests, 1453 assertions (full plugin suite)

---

## Table of Contents

1. [Overview](#overview)
2. [Testing Setup](#testing-setup)
3. [Testing Patterns](#testing-patterns)
4. [Domain Layer Tests](#domain-layer-tests)
5. [Services Layer Tests](#services-layer-tests)
6. [Module Layer Tests](#module-layer-tests)
7. [Integration Tests](#integration-tests)
8. [Coverage Requirements](#coverage-requirements)
9. [Common Testing Scenarios](#common-testing-scenarios)

---

## Overview

QuickLinks testing follows the existing plugin patterns using:
- **PHPUnit 9.6**: Test framework
- **Brain\Monkey**: Mock WordPress functions
- **Mockery**: Mock PHP interfaces and classes

### Test Statistics

| Category | Test Files | Approx. Test Cases |
|----------|-----------|-------------------|
| Domain | 5 | ~25 |
| Services/Actions | 4 | ~20 |
| Services/AuditLog | 5 | ~30 |
| Services/Confirmation | 4 | ~26 |
| Services/DTOs | 4 | ~20 |
| Services/EmailScanner | 2 | ~15 |
| Services/RateLimiter | 3 | ~15 |
| Factory | 1 | ~5 |
| **Total** | **28** | **~150+** |

### Test Organization

```
tests/
├── Domain/QuickLinks/                    # Pure PHP tests (no WordPress)
│   ├── ActionTypeTest.php                # Action type enum tests
│   ├── ActionResultTest.php              # Result object tests
│   ├── QuickLinkVerificationTest.php     # Verification entity tests
│   ├── QuickLinkConfirmationTest.php     # H-3 confirmation entity tests
│   └── RedirectTypeTest.php              # Redirect type enum tests
│
├── Services/QuickLinks/                  # Business logic tests
│   ├── Actions/                          # Action handlers
│   │   ├── ResumeActionTest.php
│   │   ├── PauseActionTest.php
│   │   ├── ProcessNowActionTest.php
│   │   └── ReactivateActionTest.php
│   │
│   ├── AuditLog/                         # H-6 Audit logging tests
│   │   ├── AuditLoggerFactoryTest.php
│   │   ├── QuickLinkAuditServiceTest.php
│   │   ├── DTOs/
│   │   │   └── AuditEntryTest.php
│   │   └── Strategies/
│   │       ├── DatabaseAuditLoggerTest.php
│   │       └── FileAuditLoggerTest.php
│   │
│   ├── Confirmation/                     # H-3 Two-step confirmation tests
│   │   ├── ConfirmationCleanupSchedulerTest.php
│   │   ├── ConfirmationRepositoryTest.php
│   │   ├── ConfirmationTableMigrationTest.php
│   │   └── QuickLinkConfirmationServiceTest.php
│   │
│   ├── DTOs/                             # Data transfer objects tests
│   │   ├── CustomMetaTagTest.php
│   │   ├── OrderSummaryItemTest.php
│   │   ├── OrderSummaryTest.php
│   │   └── VerifyQuickLinkResponseTest.php
│   │
│   ├── EmailScanner/                     # H-2 Scanner detection tests
│   │   ├── EmailScannerDetectorTest.php
│   │   └── ScannerPatternsTest.php
│   │
│   ├── RateLimiter/                      # H-5 Rate limiting tests
│   │   ├── RateLimiterTest.php
│   │   ├── RateLimiterStorageFactoryTest.php
│   │   └── Strategies/
│   │       └── TransientStorageTest.php
│   │
│   └── QuickLinkActionFactoryTest.php    # Factory pattern tests
│
└── Modules/QuickLinks/                   # WordPress integration tests
    └── (module-level tests if needed)
```

---

## Testing Setup

### Run All Tests

```bash
# Run all plugin tests
composer test

# Run only QuickLinks tests
composer test:quicklinks

# Run specific test file
vendor/bin/phpunit tests/Domain/QuickLinks/ActionTypeTest.php

# Run with coverage
composer test:coverage

# Run with specific filter
vendor/bin/phpunit --filter=QuickLinks

# Run specific test method
vendor/bin/phpunit --filter=test_it_creates_success_result
```

### PHPUnit Configuration

**File**: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
	bootstrap="tests/bootstrap.php"
	colors="true"
	convertErrorsToExceptions="true"
	convertNoticesToExceptions="true"
	convertWarningsToExceptions="true"
	stopOnFailure="false">

	<testsuites>
		<testsuite name="Domain">
			<directory suffix="Test.php">./tests/Domain</directory>
		</testsuite>
		<testsuite name="Services">
			<directory suffix="Test.php">./tests/Services</directory>
		</testsuite>
		<testsuite name="Modules">
			<directory suffix="Test.php">./tests/Modules</directory>
		</testsuite>
	</testsuites>

	<coverage>
		<include>
			<directory suffix=".php">./app/Domain/QuickLinks</directory>
			<directory suffix=".php">./app/Services/QuickLinks</directory>
			<directory suffix=".php">./app/Modules/QuickLinks</directory>
		</include>
	</coverage>
</phpunit>
```

---

## Testing Patterns

### Pattern 1: Domain Layer Tests (Pure PHP)

Domain tests don't use WordPress functions, so no Brain\Monkey needed.

**Example**: `tests/Domain/QuickLinks/ActionResultTest.php`

```php
<?php

namespace Autoship\Tests\Domain\QuickLinks;

use PHPUnit\Framework\TestCase;
use Autoship\Domain\QuickLinks\ActionResult;

/**
 * @covers \Autoship\Domain\QuickLinks\ActionResult
 */
class ActionResultTest extends TestCase {
	public function test_it_creates_success_result() {
		$result = ActionResult::success( array( 'key' => 'value' ) );

		$this->assertTrue( $result->is_successful() );
		$this->assertNull( $result->get_error_code() );
		$this->assertNull( $result->get_error_message() );
		$this->assertEquals( 'value', $result->get_metadata_value( 'key' ) );
	}

	public function test_it_creates_failure_result() {
		$result = ActionResult::failure(
			'ERROR_CODE',
			'Error message',
			array( 'context' => 'test' )
		);

		$this->assertFalse( $result->is_successful() );
		$this->assertEquals( 'ERROR_CODE', $result->get_error_code() );
		$this->assertEquals( 'Error message', $result->get_error_message() );
		$this->assertEquals( 'test', $result->get_metadata_value( 'context' ) );
	}

	public function test_it_returns_default_for_missing_metadata() {
		$result = ActionResult::success();

		$this->assertEquals( 'default', $result->get_metadata_value( 'missing', 'default' ) );
	}
}
```

### Pattern 2: Services Layer Tests (Mock Repository + Logger)

Services tests mock the repository and require WordPress function stubs for the Logger.

**Example**: `tests/Services/QuickLinks/Actions/ResumeActionTest.php`

```php
<?php

namespace Autoship\Tests\Services\QuickLinks\Actions;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Autoship\Services\QuickLinks\Actions\ResumeAction;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\Logging\Logger;
use Autoship\Services\Logging\SinkFactory;
use Autoship\Domain\QuickLinks\ActionType;
use ReflectionClass;

/**
 * @covers \Autoship\Services\QuickLinks\Actions\ResumeAction
 */
class ResumeActionTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock WordPress translation functions
		Functions\when( '__' )->returnArg();

		// Stub WordPress functions needed by Logger/FileSink
		Functions\when( 'get_option' )->alias(
			function ( $option, $default_value = false ) {
				if ( 'autoship_logging_state' === $option ) {
					return 'inactive'; // Disable logging in tests
				}
				return $default_value;
			}
		);
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_bloginfo' )->justReturn( 'test-site' );
		Functions\when( 'wp_hash' )->alias( fn( $data ) => md5( $data ) );
		Functions\when( 'sanitize_file_name' )->returnArg( 1 );
		Functions\when( 'wp_upload_dir' )->justReturn( array(
			'basedir' => sys_get_temp_dir(),
			'baseurl' => 'http://example.com/wp-content/uploads',
		) );
		Functions\when( 'trailingslashit' )->alias(
			fn( $value ) => rtrim( $value, '/\\' ) . '/'
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();

		// Reset Logger and SinkFactory singletons for test isolation
		$this->reset_singleton( Logger::class, 'instance' );
		$this->reset_singleton( SinkFactory::class, 'instance' );

		parent::tearDown();
	}

	/**
	 * Reset a singleton instance using reflection.
	 */
	private function reset_singleton( string $class_name, string $property ): void {
		$reflection = new ReflectionClass( $class_name );
		$instance = $reflection->getProperty( $property );
		$instance->setAccessible( true );
		$instance->setValue( null, null );
	}

	public function test_it_executes_resume_successfully() {
		// Arrange
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$repository->expects( $this->once() )
			->method( 'change_status' )
			->with( 123, 12345, 'Active' )
			->willReturn( true );

		$action = new ResumeAction( $repository );

		// Act
		$result = $action->execute( 123, 12345 );

		// Assert
		$this->assertTrue( $result->is_successful() );
		$this->assertEquals( 'Resume', $result->get_metadata_value( 'action' ) );
		$this->assertEquals( 12345, $result->get_metadata_value( 'scheduled_order_id' ) );
	}

	public function test_it_handles_repository_failure() {
		// Arrange
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$repository->method( 'change_status' )->willReturn( false );

		$action = new ResumeAction( $repository );

		// Act
		$result = $action->execute( 123, 12345 );

		// Assert
		$this->assertFalse( $result->is_successful() );
		$this->assertEquals( 'RESUME_FAILED', $result->get_error_code() );
	}

	public function test_it_handles_exceptions() {
		// Arrange
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$repository->method( 'change_status' )
			->willThrowException( new \Exception( 'API Error' ) );

		$action = new ResumeAction( $repository );

		// Act
		$result = $action->execute( 123, 12345 );

		// Assert
		$this->assertFalse( $result->is_successful() );
		$this->assertEquals( 'RESUME_EXCEPTION', $result->get_error_code() );
		$this->assertEquals( 'API Error', $result->get_error_message() );
	}

	public function test_it_returns_correct_action_type() {
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$action     = new ResumeAction( $repository );

		$this->assertEquals( ActionType::RESUME, $action->get_action_type() );
	}

	public function test_it_returns_correct_action_name() {
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$action     = new ResumeAction( $repository );

		$this->assertEquals( 'Resume', $action->get_action_name() );
	}
}
```

### Pattern 3: Module Layer Tests (Mock WordPress Functions)

Module tests extensively mock WordPress functions using Brain\Monkey.

**Example**: `tests/Modules/QuickLinks/Controllers/QuickLinkControllerTest.php`

```php
<?php

namespace Autoship\Tests\Modules\QuickLinks\Controllers;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Autoship\Modules\QuickLinks\Controllers\QuickLinkController;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Domain\QuickLinks\QuickLinkVerification;
use Autoship\Domain\QuickLinks\ActionResult;

/**
 * @covers \Autoship\Modules\QuickLinks\Controllers\QuickLinkController
 */
class QuickLinkControllerTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock common WordPress functions
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'absint' )->returnArg();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_it_handles_valid_quicklink_successfully() {
		// Mock WordPress functions
		Functions\expect( 'is_user_logged_in' )->once()->andReturn( true );
		Functions\expect( 'get_current_user_id' )->once()->andReturn( 1 );
		Functions\expect( 'get_user_meta' )
			->once()
			->with( 1, 'qpilot_customer_id', true )
			->andReturn( '456' );
		Functions\expect( 'get_option' )
			->once()
			->with( 'autoship_site_id' )
			->andReturn( '123' );

		// Mock service
		$service = $this->createMock( QuickLinkServiceInterface::class );

		$verification = new QuickLinkVerification(
			valid: true,
			requires_login: false,
			action_type: 0,
			redirect: array( 'type' => 0 )
		);

		$service->expects( $this->once() )
			->method( 'verify_quicklink' )
			->willReturn( $verification );

		$service->expects( $this->once() )
			->method( 'execute_action' )
			->willReturn( ActionResult::success() );

		$service->expects( $this->once() )
			->method( 'consume_quicklink' )
			->willReturn( true );

		// Act & Assert
		$controller = new QuickLinkController( $service );
		// ... test controller behavior ...
	}

	public function test_it_redirects_when_login_required() {
		// Mock functions
		Functions\expect( 'is_user_logged_in' )->once()->andReturn( false );
		Functions\expect( 'wc_get_page_permalink' )
			->once()
			->with( 'myaccount' )
			->andReturn( 'https://store.com/my-account' );
		Functions\expect( 'add_query_arg' )
			->once()
			->andReturn( 'https://store.com/my-account?redirect_to=...' );
		Functions\expect( 'wp_safe_redirect' )->once();

		$service      = $this->createMock( QuickLinkServiceInterface::class );
		$verification = new QuickLinkVerification(
			valid: true,
			requires_login: true,
			action_type: 0
		);

		$service->method( 'verify_quicklink' )->willReturn( $verification );

		// Act & Assert
		$controller = new QuickLinkController( $service );
		// ... test redirect behavior ...
	}
}
```

---

## Domain Layer Tests

### ActionType Tests

**File**: `tests/Domain/QuickLinks/ActionTypeTest.php`

```php
public function test_it_validates_action_types() {
	$this->assertTrue( ActionType::is_valid( ActionType::RESUME ) );
	$this->assertTrue( ActionType::is_valid( ActionType::PAUSE ) );
	$this->assertTrue( ActionType::is_valid( ActionType::PROCESS_NOW ) );
	$this->assertTrue( ActionType::is_valid( ActionType::REACTIVATE ) );
	$this->assertFalse( ActionType::is_valid( 999 ) );
}

public function test_it_returns_correct_names() {
	$this->assertEquals( 'Resume', ActionType::get_name( ActionType::RESUME ) );
	$this->assertEquals( 'Pause', ActionType::get_name( ActionType::PAUSE ) );
	$this->assertEquals( 'ProcessNow', ActionType::get_name( ActionType::PROCESS_NOW ) );
	$this->assertEquals( 'Reactivate', ActionType::get_name( ActionType::REACTIVATE ) );
	$this->assertEquals( 'Unknown', ActionType::get_name( 999 ) );
}

/**
 * @dataProvider parseDataProvider
 */
public function test_it_parses_various_formats( $input, $expected ) {
	$this->assertEquals( $expected, ActionType::parse( $input ) );
}

public function parseDataProvider(): array {
	return array(
		'integer resume'       => array( 0, ActionType::RESUME ),
		'string resume'        => array( 'Resume', ActionType::RESUME ),
		'lowercase resume'     => array( 'resume', ActionType::RESUME ),
		'processnow'           => array( 'ProcessNow', ActionType::PROCESS_NOW ),
		'process_now'          => array( 'process_now', ActionType::PROCESS_NOW ),
		'process now'          => array( 'process now', ActionType::PROCESS_NOW ),
		'invalid'              => array( 'invalid', null ),
	);
}
```

### QuickLinkConfirmation Tests (H-3)

**File**: `tests/Domain/QuickLinks/QuickLinkConfirmationTest.php`

Tests the H-3 Two-Step Confirmation entity, including state machine transitions.

```php
public function test_it_creates_pending_confirmation() {
	$confirmation = QuickLinkConfirmation::createPending(
		confirmation_id: 'abc123',
		customer_id: 456,
		scheduled_order_id: 789,
		action_type: ActionType::RESUME,
		expires_at: new \DateTimeImmutable( '+5 minutes' )
	);

	$this->assertEquals( ConfirmationStatus::PENDING, $confirmation->get_status() );
	$this->assertFalse( $confirmation->is_expired() );
	$this->assertTrue( $confirmation->can_transition_to( ConfirmationStatus::CONFIRMED ) );
}

public function test_state_transitions_follow_rules() {
	$confirmation = QuickLinkConfirmation::createPending( /* ... */ );

	// PENDING -> CONFIRMED is allowed
	$this->assertTrue( $confirmation->can_transition_to( ConfirmationStatus::CONFIRMED ) );

	// PENDING -> EXECUTED is NOT allowed (must go through CONFIRMED first)
	$this->assertFalse( $confirmation->can_transition_to( ConfirmationStatus::EXECUTED ) );
}

public function test_it_detects_expired_confirmations() {
	$confirmation = QuickLinkConfirmation::createPending(
		// ... with expired timestamp
		expires_at: new \DateTimeImmutable( '-1 minute' )
	);

	$this->assertTrue( $confirmation->is_expired() );
}
```

---

## Services Layer Tests

### QuickLinkActionFactory Tests

**File**: `tests/Services/QuickLinks/QuickLinkActionFactoryTest.php`

```php
<?php

namespace Autoship\Tests\Services\QuickLinks;

use PHPUnit\Framework\TestCase;
use Autoship\Services\QuickLinks\QuickLinkActionFactory;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QuickLinks\Actions\ResumeAction;
use Autoship\Services\QuickLinks\Actions\PauseAction;
use Autoship\Domain\QuickLinks\ActionType;

/**
 * @covers \Autoship\Services\QuickLinks\QuickLinkActionFactory
 */
class QuickLinkActionFactoryTest extends TestCase {
	private QuickLinkRepositoryInterface $repository;
	private QuickLinkActionFactory $factory;

	protected function setUp(): void {
		parent::setUp();

		$this->repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$this->factory    = new QuickLinkActionFactory( $this->repository );
	}

	public function test_it_creates_resume_action() {
		$action = $this->factory->create( ActionType::RESUME );

		$this->assertInstanceOf( ResumeAction::class, $action );
		$this->assertEquals( ActionType::RESUME, $action->get_action_type() );
	}

	public function test_it_creates_pause_action() {
		$action = $this->factory->create( ActionType::PAUSE );

		$this->assertInstanceOf( PauseAction::class, $action );
		$this->assertEquals( ActionType::PAUSE, $action->get_action_type() );
	}

	public function test_it_throws_exception_for_unsupported_type() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Unsupported action type: 999' );

		$this->factory->create( 999 );
	}

	public function test_it_checks_if_type_is_supported() {
		$this->assertTrue( $this->factory->is_supported( ActionType::RESUME ) );
		$this->assertTrue( $this->factory->is_supported( ActionType::PAUSE ) );
		$this->assertFalse( $this->factory->is_supported( 999 ) );
	}
}
```

### QuickLinkService Tests

**File**: `tests/Services/QuickLinks/QuickLinkServiceTest.php`

```php
<?php

namespace Autoship\Tests\Services\QuickLinks;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Autoship\Services\QuickLinks\Implementations\QuickLinkService;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QuickLinks\QuickLinkActionFactory;

/**
 * @covers \Autoship\Services\QuickLinks\Implementations\QuickLinkService
 */
class QuickLinkServiceTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_it_verifies_quicklink() {
		// Arrange
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$factory    = $this->createMock( QuickLinkActionFactory::class );

		$repository->expects( $this->once() )
			->method( 'verify' )
			->with( 123, 'resume-now', 12345, 456, 'token', '127.0.0.1', 'Agent' )
			->willReturn( array(
				'valid'          => true,
				'requiresLogin'  => false,
				'actionType'     => 0,
				'redirect'       => array( 'type' => 0 ),
			) );

		$service = new QuickLinkService( $repository, $factory );

		// Act
		$verification = $service->verify_quicklink(
			123,
			'resume-now',
			12345,
			456,
			'token',
			'127.0.0.1',
			'Agent'
		);

		// Assert
		$this->assertTrue( $verification->is_valid() );
		$this->assertFalse( $verification->requires_login() );
		$this->assertEquals( 0, $verification->get_action_type() );
	}

	public function test_it_handles_null_verify_response() {
		// Arrange
		$repository = $this->createMock( QuickLinkRepositoryInterface::class );
		$factory    = $this->createMock( QuickLinkActionFactory::class );

		$repository->method( 'verify' )->willReturn( null );

		$service = new QuickLinkService( $repository, $factory );

		// Act
		$verification = $service->verify_quicklink( 123, 'test', 12345 );

		// Assert
		$this->assertFalse( $verification->is_valid() );
	}
}
```

### Email Scanner Detection Tests (H-2)

**File**: `tests/Services/QuickLinks/EmailScanner/EmailScannerDetectorTest.php`

Tests the H-2 email scanner detection that prevents automated link prefetching from executing actions.

```php
/**
 * @covers \Autoship\Services\QuickLinks\EmailScanner\EmailScannerDetector
 */
class EmailScannerDetectorTest extends TestCase {
	public function test_it_detects_outlook_safelinks() {
		$detector = new EmailScannerDetector();

		$this->assertTrue( $detector->is_scanner(
			user_agent: 'Mozilla/5.0 SafeLinks',
			ip_address: '40.107.0.1',  // Microsoft IP range
			headers: []
		) );
	}

	public function test_it_detects_gmail_image_proxy() {
		$detector = new EmailScannerDetector();

		$this->assertTrue( $detector->is_scanner(
			user_agent: 'GoogleImageProxy',
			ip_address: '66.249.0.1',  // Google IP range
			headers: []
		) );
	}

	public function test_it_allows_real_browsers() {
		$detector = new EmailScannerDetector();

		$this->assertFalse( $detector->is_scanner(
			user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
			ip_address: '192.168.1.100',
			headers: []
		) );
	}

	public function test_it_detects_by_timing() {
		$detector = new EmailScannerDetector();

		// Scanner typically hits within milliseconds of email delivery
		$this->assertTrue( $detector->is_likely_scanner_by_timing(
			link_created_at: new \DateTimeImmutable( '-500 milliseconds' ),
			request_time: new \DateTimeImmutable()
		) );
	}
}
```

**File**: `tests/Services/QuickLinks/EmailScanner/ScannerPatternsTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\EmailScanner\ScannerPatterns
 * @dataProvider knownScannerUserAgents
 */
public function test_it_identifies_known_scanner_patterns( string $user_agent, bool $expected ) {
	$patterns = new ScannerPatterns();

	$this->assertEquals( $expected, $patterns->matches_user_agent( $user_agent ) );
}

public function knownScannerUserAgents(): array {
	return [
		'outlook_safelinks'      => [ 'Mozilla/5.0 SafeLinks', true ],
		'barracuda'              => [ 'barracuda', true ],
		'proofpoint'             => [ 'proofpoint', true ],
		'mimecast'               => [ 'mimecast', true ],
		'google_image_proxy'     => [ 'GoogleImageProxy', true ],
		'real_chrome'            => [ 'Mozilla/5.0 Chrome/120', false ],
		'real_firefox'           => [ 'Mozilla/5.0 Firefox/121', false ],
	];
}
```

### Rate Limiter Tests (H-5)

**File**: `tests/Services/QuickLinks/RateLimiter/RateLimiterTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\RateLimiter\RateLimiter
 */
class RateLimiterTest extends TestCase {
	public function test_it_allows_requests_under_limit() {
		$storage = $this->createMock( RateLimiterStorageInterface::class );
		$storage->method( 'get_count' )->willReturn( 5 );

		$limiter = new RateLimiter( $storage, max_attempts: 10, window_seconds: 3600 );

		$result = $limiter->check( 'customer_123' );

		$this->assertFalse( $result->is_limited() );
		$this->assertEquals( 5, $result->get_remaining() );
	}

	public function test_it_blocks_requests_over_limit() {
		$storage = $this->createMock( RateLimiterStorageInterface::class );
		$storage->method( 'get_count' )->willReturn( 10 );

		$limiter = new RateLimiter( $storage, max_attempts: 10, window_seconds: 3600 );

		$result = $limiter->check( 'customer_123' );

		$this->assertTrue( $result->is_limited() );
		$this->assertEquals( 0, $result->get_remaining() );
		$this->assertGreaterThan( 0, $result->get_retry_after() );
	}

	public function test_it_increments_counter_on_record() {
		$storage = $this->createMock( RateLimiterStorageInterface::class );
		$storage->expects( $this->once() )
			->method( 'increment' )
			->with( 'customer_123', 3600 );

		$limiter = new RateLimiter( $storage, max_attempts: 10, window_seconds: 3600 );

		$limiter->record( 'customer_123' );
	}
}
```

**File**: `tests/Services/QuickLinks/RateLimiter/Strategies/TransientStorageTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\RateLimiter\Strategies\TransientStorage
 */
class TransientStorageTest extends TestCase {
	public function test_it_stores_and_retrieves_count() {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'ql_rate_customer_123' )
			->andReturn( 5 );

		$storage = new TransientStorage();

		$this->assertEquals( 5, $storage->get_count( 'customer_123' ) );
	}

	public function test_it_increments_count() {
		Functions\expect( 'get_transient' )
			->once()
			->andReturn( 3 );

		Functions\expect( 'set_transient' )
			->once()
			->with( 'ql_rate_customer_123', 4, 3600 );

		$storage = new TransientStorage();
		$storage->increment( 'customer_123', 3600 );
	}

	public function test_it_handles_missing_transient() {
		Functions\expect( 'get_transient' )
			->once()
			->andReturn( false );

		$storage = new TransientStorage();

		$this->assertEquals( 0, $storage->get_count( 'customer_123' ) );
	}
}
```

### Confirmation Service Tests (H-3)

**File**: `tests/Services/QuickLinks/Confirmation/QuickLinkConfirmationServiceTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService
 */
class QuickLinkConfirmationServiceTest extends TestCase {
	public function test_it_creates_pending_confirmation() {
		$repository = $this->createMock( ConfirmationRepositoryInterface::class );
		$repository->expects( $this->once() )
			->method( 'save' )
			->willReturn( true );

		$service = new QuickLinkConfirmationService( $repository );

		$confirmation = $service->create_confirmation(
			customer_id: 456,
			scheduled_order_id: 789,
			action_type: ActionType::RESUME
		);

		$this->assertNotNull( $confirmation );
		$this->assertEquals( ConfirmationStatus::PENDING, $confirmation->get_status() );
	}

	public function test_it_confirms_pending_confirmation() {
		$pending = QuickLinkConfirmation::createPending( /* ... */ );

		$repository = $this->createMock( ConfirmationRepositoryInterface::class );
		$repository->method( 'find_by_id' )->willReturn( $pending );
		$repository->expects( $this->once() )->method( 'save' );

		$service = new QuickLinkConfirmationService( $repository );

		$result = $service->confirm( 'abc123' );

		$this->assertTrue( $result->is_successful() );
	}

	public function test_it_rejects_expired_confirmation() {
		$expired = QuickLinkConfirmation::createPending(
			// ... with past expiration
			expires_at: new \DateTimeImmutable( '-1 minute' )
		);

		$repository = $this->createMock( ConfirmationRepositoryInterface::class );
		$repository->method( 'find_by_id' )->willReturn( $expired );

		$service = new QuickLinkConfirmationService( $repository );

		$result = $service->confirm( 'abc123' );

		$this->assertFalse( $result->is_successful() );
		$this->assertEquals( 'CONFIRMATION_EXPIRED', $result->get_error_code() );
	}
}
```

**File**: `tests/Services/QuickLinks/Confirmation/ConfirmationRepositoryTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\Confirmation\ConfirmationRepository
 */
class ConfirmationRepositoryTest extends TestCase {
	public function test_it_saves_confirmation() {
		global $wpdb;
		$wpdb = $this->createMock( \stdClass::class );
		$wpdb->prefix = 'wp_';
		$wpdb->expects( $this->once() )
			->method( 'insert' )
			->willReturn( 1 );

		$repository = new ConfirmationRepository();

		$confirmation = QuickLinkConfirmation::createPending( /* ... */ );
		$result = $repository->save( $confirmation );

		$this->assertTrue( $result );
	}

	public function test_it_finds_confirmation_by_id() {
		// ... database mock setup ...

		$repository = new ConfirmationRepository();

		$confirmation = $repository->find_by_id( 'abc123' );

		$this->assertInstanceOf( QuickLinkConfirmation::class, $confirmation );
	}
}
```

### Audit Logging Tests (H-6)

**File**: `tests/Services/QuickLinks/AuditLog/QuickLinkAuditServiceTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\AuditLog\QuickLinkAuditService
 */
class QuickLinkAuditServiceTest extends TestCase {
	public function test_it_logs_successful_action() {
		$logger = $this->createMock( AuditLoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'log' )
			->with( $this->callback( function ( AuditEntry $entry ) {
				return $entry->get_event_type() === 'quicklink_executed'
					&& $entry->get_customer_id() === 456
					&& $entry->is_successful();
			} ) );

		$service = new QuickLinkAuditService( $logger );

		$service->log_action(
			customer_id: 456,
			scheduled_order_id: 789,
			action_type: ActionType::RESUME,
			success: true,
			ip_address: '192.168.1.100'
		);
	}

	public function test_it_logs_failed_action() {
		$logger = $this->createMock( AuditLoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'log' )
			->with( $this->callback( function ( AuditEntry $entry ) {
				return ! $entry->is_successful()
					&& $entry->get_error_code() === 'RATE_LIMITED';
			} ) );

		$service = new QuickLinkAuditService( $logger );

		$service->log_action(
			customer_id: 456,
			scheduled_order_id: 789,
			action_type: ActionType::RESUME,
			success: false,
			error_code: 'RATE_LIMITED',
			ip_address: '192.168.1.100'
		);
	}
}
```

**File**: `tests/Services/QuickLinks/AuditLog/Strategies/DatabaseAuditLoggerTest.php`

```php
/**
 * @covers \Autoship\Services\QuickLinks\AuditLog\Strategies\DatabaseAuditLogger
 */
class DatabaseAuditLoggerTest extends TestCase {
	public function test_it_inserts_audit_entry() {
		global $wpdb;
		$wpdb = $this->createMock( \stdClass::class );
		$wpdb->prefix = 'wp_';
		$wpdb->expects( $this->once() )
			->method( 'insert' )
			->with(
				'wp_autoship_quicklink_audit',
				$this->arrayHasKey( 'customer_id' )
			);

		$logger = new DatabaseAuditLogger();

		$entry = new AuditEntry(
			event_type: 'quicklink_executed',
			customer_id: 456,
			scheduled_order_id: 789,
			action_type: ActionType::RESUME,
			success: true,
			ip_address: '192.168.1.100'
		);

		$logger->log( $entry );
	}

	public function test_it_queries_by_customer() {
		// ... mock setup for SELECT query ...

		$logger = new DatabaseAuditLogger();

		$entries = $logger->get_by_customer( 456, limit: 10 );

		$this->assertIsArray( $entries );
	}
}
```

---

## Module Layer Tests

### QuickLinksModule Tests

**File**: `tests/Modules/QuickLinks/QuickLinksModuleTest.php`

```php
<?php

namespace Autoship\Tests\Modules\QuickLinks;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Autoship\Modules\QuickLinks\QuickLinksModule;
use Autoship\Core\ServiceContainer;

/**
 * @covers \Autoship\Modules\QuickLinks\QuickLinksModule
 */
class QuickLinksModuleTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_it_registers_services_in_container() {
		// Arrange
		$container = $this->createMock( ServiceContainer::class );

		$container->expects( $this->atLeastOnce() )
			->method( 'register' );

		$module = new QuickLinksModule();

		// Act
		$module->register( $container );

		// Assert - expectations verified automatically
	}

	public function test_it_boots_when_enabled() {
		// Arrange
		Functions\expect( 'get_option' )
			->once()
			->with( 'autoship_features', array() )
			->andReturn( array( 'quicklinks' => true ) );

		$container = $this->createMock( ServiceContainer::class );
		$module    = new QuickLinksModule();

		// Act
		$module->boot( $container );

		// Assert - module should initialize
	}
}
```

---

## Integration Tests

### Full Flow Test

**File**: `tests/Integration/QuickLinks/QuickLinkFlowTest.php`

```php
<?php

namespace Autoship\Tests\Integration\QuickLinks;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
// ... imports ...

/**
 * @group integration
 * @covers \Autoship\Modules\QuickLinks\Controllers\QuickLinkController
 */
class QuickLinkFlowTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Set up all mocks for full flow
		$this->mock_wordpress_functions();
		$this->mock_service_container();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_complete_resume_flow() {
		// Arrange - Set up complete environment
		// ... mock all WordPress functions ...
		// ... mock all services ...

		// Act - Simulate request
		$_SERVER['REQUEST_URI'] = '/autoship/l/resume-now/12345';
		$_GET['token']          = 'test-token';

		// Trigger rewrite detection
		Functions\expect( 'get_query_var' )
			->with( 'autoship_quicklink' )
			->andReturn( '1' );

		// ... rest of flow ...

		// Assert
		// ... verify correct actions taken ...
	}
}
```

---

## Coverage Requirements

### Target Coverage Levels

| Layer | Target | Reason |
|-------|--------|--------|
| **Domain** | 100% | Pure logic, easy to test |
| **Services** | 90%+ | Core business logic |
| **Module** | 70%+ | WordPress integration |

### Check Coverage

```bash
# Generate coverage report
composer test:coverage

# View HTML report
open coverage/index.html

# View text summary
vendor/bin/phpunit --coverage-text
```

### Coverage Annotations

```php
/**
 * @covers \Autoship\Domain\QuickLinks\ActionResult
 * @uses \Autoship\Domain\QuickLinks\ActionType
 */
class ActionResultTest extends TestCase {
	// ... tests ...
}
```

---

## Common Testing Scenarios

### Scenario 1: Test Null Handling

```php
public function test_it_handles_null_values_from_api() {
	$repository = $this->createMock( QuickLinkRepositoryInterface::class );
	$repository->method( 'verify' )->willReturn( array(
		'valid'          => null,  // ← Null from malformed API response
		'requiresLogin'  => null,
		'actionType'     => null,
	) );

	$service      = new QuickLinkService( $repository, $factory );
	$verification = $service->verify_quicklink( 123, 'test', 12345 );

	// Should default to secure values
	$this->assertFalse( $verification->is_valid() );  // Default to invalid
	$this->assertTrue( $verification->requires_login() );  // Default to require login
}
```

### Scenario 2: Test WordPress Function Mocking

```php
public function test_it_uses_wordpress_functions() {
	// Mock translation
	Functions\when( '__' )->returnArg();
	Functions\when( 'esc_html__' )->returnArg();

	// Mock sanitization
	Functions\expect( 'sanitize_text_field' )
		->once()
		->with( 'test-slug' )
		->andReturn( 'test-slug' );

	// Mock database
	Functions\expect( 'get_option' )
		->once()
		->with( 'autoship_site_id' )
		->andReturn( '123' );

	// ... test code ...
}
```

### Scenario 3: Test Exception Handling

```php
public function test_it_handles_repository_exceptions() {
	$repository = $this->createMock( QuickLinkRepositoryInterface::class );
	$repository->method( 'change_status' )
		->willThrowException( new \RuntimeException( 'API timeout' ) );

	$action = new ResumeAction( $repository );
	$result = $action->execute( 123, 12345 );

	$this->assertFalse( $result->is_successful() );
	$this->assertEquals( 'RESUME_EXCEPTION', $result->get_error_code() );
	$this->assertStringContainsString( 'API timeout', $result->get_error_message() );
}
```

### Scenario 4: Test Data Providers

```php
/**
 * @dataProvider actionTypeProvider
 */
public function test_factory_creates_all_action_types( int $type, string $expected_class ) {
	$repository = $this->createMock( QuickLinkRepositoryInterface::class );
	$factory    = new QuickLinkActionFactory( $repository );

	$action = $factory->create( $type );

	$this->assertInstanceOf( $expected_class, $action );
}

public function actionTypeProvider(): array {
	return array(
		'Resume'      => array( ActionType::RESUME, ResumeAction::class ),
		'Pause'       => array( ActionType::PAUSE, PauseAction::class ),
		'ProcessNow'  => array( ActionType::PROCESS_NOW, ProcessNowAction::class ),
		'Reactivate'  => array( ActionType::REACTIVATE, ReactivateAction::class ),
	);
}
```

---

## Debugging Tests

### Run Single Test

```bash
# Run specific test method
vendor/bin/phpunit --filter=test_it_creates_success_result

# Run specific test class
vendor/bin/phpunit tests/Domain/QuickLinks/ActionResultTest.php

# Run with verbose output
vendor/bin/phpunit --verbose --debug
```

### Debug Test Failures

```php
// Add debug output
public function test_something() {
	$result = $this->doSomething();

	var_dump( $result );  // Debug output
	fwrite( STDERR, print_r( $result, true ) );  // To STDERR

	$this->assertTrue( $result->is_successful() );
}
```

### Common Test Failures

**1. Brain\Monkey not set up**:
```
Error: Call to undefined function __()
```
**Fix**: Add `Monkey\setUp()` and `Monkey\tearDown()`

**2. Mock not configured**:
```
Error: No matching handler found for...
```
**Fix**: Add `->method()` or `->expects()` to mock

**3. Missing @covers annotation**:
```
Warning: Risky test
```
**Fix**: Add `@covers` annotation to test class

**4. Logger WordPress function errors**:
```
Error: Call to undefined function Autoship\Services\Logging\get_option()
```
**Fix**: Add WordPress function stubs for Logger dependencies (see Pattern 2 above):
- `get_option` - Return 'inactive' for logging state
- `apply_filters`, `get_bloginfo`, `wp_hash`, `sanitize_file_name`, `wp_upload_dir`, `trailingslashit`

**5. Logger singleton state leaking between tests**:
```
Tests pass individually but fail when run together
```
**Fix**: Reset Logger and SinkFactory singletons in `tearDown()`:
```php
$this->reset_singleton( Logger::class, 'instance' );
$this->reset_singleton( SinkFactory::class, 'instance' );
```

---

## Best Practices

### DO:
- ✅ Use `@covers` annotations
- ✅ Mock external dependencies
- ✅ Test edge cases and error conditions
- ✅ Use data providers for similar tests
- ✅ Keep tests focused (one concept per test)
- ✅ Use descriptive test names (`test_it_does_something`)
- ✅ Arrange-Act-Assert pattern

### DON'T:
- ❌ Test WordPress core functionality
- ❌ Make real API calls
- ❌ Access database directly
- ❌ Depend on test execution order
- ❌ Use sleep() or time-dependent tests
- ❌ Test private methods directly

---

## Related Documentation

- [Developer Guide](developer-guide.md) - Implementation patterns and debugging
- [Architecture](references/architecture.md) - System design and component structure
- [Security Guide](security-guide.md) - H-1 through H-6 security controls
- [Coding Standards](references/coding-standards.md) - Code formatting standards
- [Shopify vs WooCommerce](shopify-vs-woo-implementation.md) - Feature parity comparison

---

**Last Updated**: November 28, 2025
**Version**: 1.0.0 (Production Ready)
**Maintained By**: Development Team
