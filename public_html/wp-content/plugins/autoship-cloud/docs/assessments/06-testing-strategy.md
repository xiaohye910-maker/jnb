# Testing Strategy & TDD Roadmap

> **Goal**: Enable daily deployments with confidence through comprehensive test coverage and TDD practices.

---

## Current Testing State (January 2026)

### What Exists

| Layer | Technology | Coverage | Status |
|-------|------------|----------|--------|
| Unit Tests | PHPUnit 9.6.29 + Brain\Monkey | 73.65% of app/ | WORKING |
| Integration Tests | None | 0% | MISSING |
| E2E Tests | Playwright | EXISTS | EXTERNAL |
| Compliance | PHPCS | Running | WORKING |
| Coverage Reports | PHPUnit + PCOV | 73.65% | **WORKING** |

### Test Infrastructure

```
tests/
├── bootstrap.php          # Brain\Monkey setup, WC stubs
├── phpunit.xml            # PHPUnit config with strict coverage
├── Core/                  # Core tests (ServiceContainer, Plugin, ModuleManager)
├── Domain/                # Domain model tests (PaymentIntegrations, QuickLinks, Nextime)
├── Modules/               # Module tests (Quicklaunch, QuickLinks, Synchronizers)
├── Services/              # Service tests (QPilot, Logging, Nextime, QuickLinks)
└── Common/                # Common utility tests

Test Files:     66+
Test Methods:   ~350+
app/ Files:     187
app/ Coverage:  73.65% (6032/8190 lines)
src/ Files:     18
src/ Coverage:  0%
```

### Coverage by Component (Measured)

| Component | Coverage | Lines | Target | Status |
|-----------|----------|-------|--------|--------|
| Domain | 97.49% | 933/957 | 90% | EXCEEDS |
| Common | 98.70% | 76/77 | 90% | EXCEEDS |
| Services | 84.26% | 3861/4582 | 85% | ON TARGET |
| Core | 86.68% | 332/383 | 90% | CLOSE |
| Modules | 37.88% | 830/2191 | 80% | **CRITICAL GAP** |

### Services Breakdown

| Service | Coverage | Lines | Target | Status |
|---------|----------|-------|--------|--------|
| QPilot | 97.94% | 2232/2279 | 85% | EXCEEDS |
| Nextime | 99.29% | 280/282 | 85% | EXCEEDS |
| QuickLinks | 65.98% | 1181/1790 | 85% | NEEDS WORK |
| Logging | 72.73% | 168/231 | 85% | NEEDS WORK |

### Modules Breakdown

| Module | Coverage | Lines | Target | Status |
|--------|----------|-------|--------|--------|
| Nextime | 88.32% | 174/197 | 80% | EXCEEDS |
| Quicklaunch | 53.73% | 360/670 | 80% | NEEDS WORK |
| Synchronizers | 42.71% | 126/295 | 80% | NEEDS WORK |
| QuickLinks | 16.52% | 170/1029 | 80% | **CRITICAL** |

---

## Testing Pyramid Analysis

### Current State (Improving but Unbalanced)

```
         ┌─────────────────────────────────────┐
         │         E2E (Playwright)            │  <-- External
         │           EXISTS EXTERNALLY         │
         └─────────────────────────────────────┘

    ┌─────────────────────────────────────────────┐
    │           Integration Tests                  │  <-- ZERO
    │               STILL MISSING                  │
    └─────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│                   Unit Tests                         │  <-- 73.65% app/
│            Strong in Domain/Services, Weak Modules   │
└─────────────────────────────────────────────────────┘
```

### Target State (Proper Pyramid)

```
                    ┌───────────────┐
                    │     E2E       │  10%
                    │   Playwright  │  Critical paths only
                    └───────────────┘

              ┌─────────────────────────┐
              │   Integration Tests     │  20%
              │  WooCommerce + QPilot   │
              └─────────────────────────┘

        ┌─────────────────────────────────────┐
        │            Unit Tests               │  70%
        │    90%+ on app/, 50%+ on src/       │
        └─────────────────────────────────────┘
```

---

## Testing Challenges Encountered

### 1. WordPress Function Dependencies

**Problem**: Code calls WordPress/WooCommerce functions that don't exist in test environment.

**Examples**:
- `WC()` - WooCommerce singleton
- `wc_get_product()` - Product retrieval
- `wp_send_json_error()` - AJAX responses (doesn't die in tests)
- `autoship_has_credentials()` - Plugin-specific function

**Solution**: Brain\Monkey mocking + skip complex handlers

```php
// Mock WordPress functions
Functions\expect('get_option')->with('option_name')->andReturn('value');
Functions\expect('update_option')->andReturn(true);

// For complex WC functions - skip in unit tests, test via integration
```

### 2. AJAX Handler Testing

**Problem**: `wp_send_json_*` functions don't stop execution in tests.

**Solution**: Test up to the response point, use output buffering, or mark as integration test candidates.

### 3. Service Container Dependencies

**Problem**: Handlers depend on services from container.

**Solution**: Mock container and inject test doubles.

```php
$container = Mockery::mock(ServiceContainer::class);
$container->shouldReceive('get')->with('service_name')->andReturn($mock_service);
Plugin::set_service_container($container);
```

---

## Why Legacy Code Is Untestable

### Problem: Procedural Functions with Global State

```php
// Example from src/payments.php
function autoship_get_order_payment_data( $order_id ) {
    // Direct WooCommerce call - cannot mock
    $order = wc_get_order( $order_id );
    $payment_method_id = $order->get_payment_method();

    // Global state dependency
    $valid_methods = autoship_get_valid_payment_methods();

    // Function calls function - hidden dependency
    $function = "autoship_get_{$valid_methods[$payment_method_id]}_order_payment_data";
    if ( function_exists( $function ) ) {
        $payment_data = $function( $order_id, $order );
    }

    return apply_filters( 'autoship_order_payment_data', $payment_data, $order_id );
}
```

**Why this can't be tested**:
1. Direct `wc_get_order()` call - cannot mock
2. Dynamic function name construction - cannot intercept
3. Global state via filters
4. No interface - cannot substitute

### Solution: Service Extraction Pattern

```php
// Step 1: Create interface
interface PaymentDataServiceInterface {
    public function getOrderPaymentData(int $orderId): ?PaymentData;
}

// Step 2: Create service that wraps legacy
class PaymentDataService implements PaymentDataServiceInterface {
    public function __construct(
        private OrderRepositoryInterface $orders,
        private PaymentMethodResolverInterface $resolver
    ) {}

    public function getOrderPaymentData(int $orderId): ?PaymentData {
        $order = $this->orders->find($orderId);
        if (!$order) return null;

        return $this->resolver->resolve($order);
    }
}

// Step 3: Now testable with mocks
class PaymentDataServiceTest extends TestCase {
    public function test_returns_null_for_missing_order(): void {
        $orders = Mockery::mock(OrderRepositoryInterface::class);
        $orders->shouldReceive('find')->with(123)->andReturnNull();

        $service = new PaymentDataService($orders, $this->resolver);

        $this->assertNull($service->getOrderPaymentData(123));
    }
}
```

---

## TDD Implementation Plan (Updated)

### Phase 1: Complete Module Coverage (Weeks 1-4)

**Goal**: Get Modules to 80%+ coverage

| Module | Current | Target | Gap | Priority |
|--------|---------|--------|-----|----------|
| QuickLinks | 16.52% | 80% | 653 lines | **P1** |
| Synchronizers | 42.71% | 80% | 110 lines | P2 |
| Quicklaunch | 53.73% | 80% | 176 lines | P3 |

**Note**: Some code is untestable without WooCommerce (StepHandlers with WC() calls). Mark these as integration test candidates.

### Phase 2: Complete Services Coverage (Weeks 5-8)

**Goal**: Get Services to 85%+ coverage

| Service | Current | Target | Gap | Priority |
|---------|---------|--------|-----|----------|
| QuickLinks | 65.98% | 85% | 341 lines | P1 |
| Logging | 72.73% | 85% | 28 lines | P2 |

### Phase 3: payments.php Refactor (Weeks 9-16)

**Goal**: Extract testable services from payments.php

**Extraction Order**:

1. **PaymentMethodService** (from payments.php)
   - Extract payment method lookup/validation
   - Create interface
   - Write unit tests
   - ~500 lines

2. **TokenizationService** (from payments.php)
   - Extract token management
   - Create interface
   - Write unit tests
   - ~800 lines

3. **GatewayResolverService** (from payments.php)
   - Extract gateway type resolution
   - Create interface
   - Write unit tests
   - ~300 lines

4. **OrderPaymentService** (from payments.php)
   - Extract order payment data extraction
   - Create interface
   - Write unit tests
   - ~1000 lines

### Phase 4: Add Integration Tests (Ongoing)

**Goal**: 20+ integration tests for critical paths

**Priority Order** (by business risk):

1. **Checkout Flow**
   - Autoship product adds to cart correctly
   - Scheduled order created on checkout
   - Payment method captured

2. **Payment Processing**
   - Token creation works for each gateway
   - Payment method updates propagate

3. **Order Creation**
   - QPilot webhook creates WC order
   - Order metadata correct
   - Duplicate prevention works

---

## Testing Metrics (January 2026)

### Track Weekly

```
Coverage:
├── app/ Total:           73.65% → Target: 85%
├── app/Core/:            86.68% → Target: 90%
├── app/Modules/:         37.88% → Target: 80%  ❌
├── app/Services/:        84.26% → Target: 85%  ✓
├── app/Domain/:          97.49% → Target: 90%  ✓
└── src/ (legacy):        0%     → Target: 50%

Tests:
├── Unit Tests:           350+   → Target: 500+
├── Integration Tests:    0      → Target: 50+
├── E2E Tests:            Yes    → Maintain
└── Flaky Tests:          0      → Target: 0

CI:
├── Build Time:           ~2min  → Target: <5min
├── Test Time:            ~45s   → Target: <3min
├── Coverage Report:      Yes    → Maintain
└── Failures Block Merge: No     → Target: Yes
```

---

## Composer Scripts

### Current

```json
{
  "scripts": {
    "test": "vendor/bin/phpunit -c tests/phpunit.xml",
    "coverage": "vendor/bin/phpunit -c tests/phpunit.xml --coverage-html build/coverage",
    "compliance": "vendor/bin/phpcs ...",
    "loc": "vendor/bin/phploc ..."
  }
}
```

### Run Commands

```bash
# Run all tests
composer test

# Run tests with coverage report
composer coverage

# Run specific test file
vendor/bin/phpunit -c tests/phpunit.xml tests/Services/QPilot/QPilotServiceClientTest.php

# Run specific test method
vendor/bin/phpunit -c tests/phpunit.xml --filter test_method_name

# Run tests for a module
vendor/bin/phpunit -c tests/phpunit.xml tests/Modules/Quicklaunch/
```

---

## TDD Workflow

### For New Features

```
1. Write failing test
2. Write minimal code to pass
3. Refactor
4. Repeat

Example:
┌─────────────────────────────────────────────────────────┐
│  Feature: Extract PaymentMethodService from payments.php │
├─────────────────────────────────────────────────────────┤
│  1. Create PaymentMethodServiceInterface                 │
│  2. Write test for getValidMethods()                     │
│  3. Implement minimal PaymentMethodService               │
│  4. Write test for getGatewayType()                      │
│  5. Implement getGatewayType()                           │
│  6. Continue until all methods extracted                 │
│  7. Update legacy to call new service                    │
└─────────────────────────────────────────────────────────┘
```

### For Legacy Refactoring

```
1. Write integration test for current behavior
2. Extract code to new service
3. Write unit tests for new service
4. Replace legacy with calls to new service
5. Verify integration test still passes
6. Deprecate legacy function

Example:
┌─────────────────────────────────────────────────────────┐
│  Extract: Token validation from payments.php             │
├─────────────────────────────────────────────────────────┤
│  1. Document current autoship_get_valid_payment_methods()│
│  2. Create PaymentMethodValidator interface              │
│  3. Write unit tests for validator                       │
│  4. Implement validator service                          │
│  5. Create facade that wraps legacy                      │
│  6. Update callers to use service                        │
│  7. Mark legacy function @deprecated                     │
└─────────────────────────────────────────────────────────┘
```

---

## Immediate Actions

### This Week
- [x] Debug and fix coverage reporting - DONE
- [ ] Increase Modules/QuickLinks coverage from 16.52%
- [ ] Create PaymentMethodServiceInterface for payments.php refactor
- [ ] Document payments.php extraction boundaries

### This Month
- [ ] Get Modules coverage to 60%+
- [ ] Get Services coverage to 85%+
- [ ] Extract first service from payments.php
- [ ] Write 5 integration tests for payment flow

---

*Last Updated: January 2026*
*Previous Update: December 2025*
*Review Frequency: Monthly*
