# Migration Components Breakdown

Detailed description of each migration component with time estimates.

---

## 1. Foundation Layer

### Description

The Foundation Layer provides the base classes and contracts that all payment gateway integrations depend on. It establishes the object-oriented structure that replaces procedural legacy code.

### What It Includes

| File | LOC | Purpose |
|------|-----|---------|
| `app/Domain/PaymentMethodType.php` | 249 | Enum-like class defining payment type constants (STRIPE=7, AUTHORIZE_NET=1, etc.) |
| `app/Domain/PaymentIntegration.php` | 293 | Base class with gateway configuration properties (api keys, test mode, etc.) |
| `app/Domain/AbstractPaymentGateway.php` | 97 | Abstract class defining the contract for all gateway implementations |
| `app/Domain/PaymentIntegrationFactory.php` | 91 | Factory pattern for creating correct integration instance from gateway ID |

**Total: 730 LOC**

### Class Relationships

```
PaymentMethodType (Constants)
        │
        ▼
PaymentIntegration (Base Properties)
        │
        ▼
AbstractPaymentGateway (Abstract Contract)
        │
        ▼
PaymentIntegrationFactory (Creates Instances)
```

### Key Abstractions

**PaymentIntegration Properties:**
```php
- method_type: int      // PaymentMethodType constant
- method_id: string     // Gateway ID (e.g., 'stripe')
- method_name: string   // Display name
- api_account: string   // Account identifier
- api_key_1: string     // Primary API key (publishable key)
- api_key_2: string     // Secondary API key (secret key)
- test_mode: bool       // Test/live mode flag
- authorize_only: bool  // Auth-only vs auth+capture
```

**AbstractPaymentGateway Contract:**
```php
abstract public function initialize(): void;
abstract public function get_order_payment_data(int $order_id, WC_Order $order): ?QPilotPaymentData;
abstract public function add_payment_method(array $data, string $type, WC_Payment_Token $token): array;
abstract public function delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool;
public function tokenize_payment_method($token, string $id, string $type): array;
public function add_metadata(array $meta, WC_Order $order, $method): array;
public function display_apply_to_all_orders_button(array $item, WC_Payment_Token $token): string;
```

### Dependencies

- **External:** None (pure PHP)
- **WordPress:** None at this layer
- **WooCommerce:** Type hints only (WC_Order, WC_Payment_Token)

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy PaymentMethodType.php | Direct copy, no modifications needed | 5 min |
| Copy PaymentIntegration.php | Direct copy, verify namespace | 5 min |
| Copy AbstractPaymentGateway.php | Direct copy, verify use statements | 5 min |
| Copy PaymentIntegrationFactory.php | May need modification if integrations missing | 15 min |
| Create/copy tests | 4 test files for foundation classes | 30 min |
| Run tests and fix issues | Debug any failures | 30 min |
| Verify autoloading | Run `composer dump-autoload` | 5 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** (clean copy, tests pass) | 1 hour |
| **Realistic** (minor fixes needed) | 1.5 hours |
| **Pessimistic** (dependency issues) | 2.5 hours |

**Recommended Estimate: 1.5 - 2 hours**

---

## 2. Module Infrastructure

### Description

The Module Infrastructure provides the service layer that integrates payment gateways into the WordPress/WooCommerce ecosystem. It follows the plugin's modular architecture pattern with dependency injection via ServiceContainer.

### What It Includes

| File | LOC | Purpose |
|------|-----|---------|
| `app/Modules/Payments/PaymentsModule.php` | 129 | Module bootstrap implementing ModuleInterface |
| `app/Modules/Payments/Services/PaymentGatewayRegistry.php` | 84 | Central registry for payment gateway instances |
| `app/Modules/Payments/Services/PaymentGatewayService.php` | 101 | Main service for payment operations |
| `app/Modules/Payments/Services/PaymentsCompatibilityService.php` | 274 | Service wrapper for compatibility manager |
| `app/Modules/Payments/Helpers/PaymentMethodHelper.php` | ~100 | Utility functions for payment methods |

**Total: ~688 LOC**

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      PaymentsModule                         │
│  - register(ServiceContainer): Register services            │
│  - boot(ServiceContainer): Initialize on woocommerce_init   │
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────────┐
│PaymentGateway   │ │PaymentGateway   │ │PaymentsCompatibility│
│Registry         │ │Service          │ │Service              │
│                 │ │                 │ │                     │
│- gateways[]     │ │- registry       │ │- manager reference  │
│- initialize()   │ │- handle_token() │ │- initialize()       │
│- get_gateway()  │ │- get_methods()  │ │- get_status()       │
└─────────────────┘ └─────────────────┘ └─────────────────────┘
```

### Service Registration Flow

```php
// PaymentsModule::register()
$container->register(PaymentGatewayRegistry::class, fn() => new PaymentGatewayRegistry());
$container->register(PaymentGatewayService::class, fn() => new PaymentGatewayService($registry));
$container->register(PaymentsCompatibilityService::class, fn() => new PaymentsCompatibilityService($registry));

// PaymentsModule::boot()
add_action('woocommerce_init', function() {
    $registry->initialize();           // Auto-discover WC gateways
    $compatibility->initialize();      // Register legacy hooks
}, 99);
```

### Dependencies

- **Internal:** Foundation Layer (AbstractPaymentGateway, PaymentIntegrationFactory)
- **WordPress:** add_action, add_filter, hooks system
- **WooCommerce:** WC(), payment_gateways(), WC_Payment_Tokens
- **Plugin Core:** ServiceContainer, ModuleInterface, Plugin, Logger

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Create directory structure | `app/Modules/Payments/Services/`, `Helpers/` | 5 min |
| Copy PaymentsModule.php | May need to verify Plugin.php registration | 15 min |
| Copy PaymentGatewayRegistry.php | Verify factory import | 10 min |
| Copy PaymentGatewayService.php | Verify dependencies | 10 min |
| Copy PaymentsCompatibilityService.php | Depends on compatibility layer | 15 min |
| Copy PaymentMethodHelper.php | If exists | 10 min |
| Register module in Plugin.php | Add to register_modules() | 15 min |
| Create/copy tests | 4-5 test files | 45 min |
| Run tests and fix issues | Debug service wiring | 45 min |
| Verify WooCommerce integration | Test with WC active | 20 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** (clean copy, services wire correctly) | 2 hours |
| **Realistic** (some dependency fixes) | 3 hours |
| **Pessimistic** (significant wiring issues) | 4.5 hours |

**Recommended Estimate: 3 - 4 hours**

---

## 3. Compatibility Layer

### Description

The Compatibility Layer bridges legacy procedural code to the new object-oriented implementation. It registers WordPress filter/action hooks that legacy code uses, routing them to the new integration classes.

### What It Includes

| File | LOC | Purpose |
|------|-----|---------|
| `app/Modules/Payments/Compatibility/AbstractPaymentCompatibility.php` | 180 | Base class for all compatibility implementations |
| `app/Modules/Payments/Compatibility/PaymentsCompatibilityManager.php` | 280 | Static manager that registers all compatibility classes |
| `app/Modules/Payments/Compatibility/StripePaymentCompatibility.php` | ~100 | Stripe-specific hook registration |
| `app/Modules/Payments/Compatibility/AuthorizeNetCompatibility.php` | ~100 | AuthorizeNet-specific hook registration |
| `app/Modules/Payments/Compatibility/SquarePaymentCompatibility.php` | ~100 | Square-specific hook registration |
| `app/Modules/Payments/Compatibility/BraintreePaymentCompatibility.php` | ~100 | Braintree-specific hook registration |

**Total: ~860 LOC (existing), needs ~1,200 more LOC for remaining gateways**

### Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PaymentsCompatibilityManager                         │
│  - $available_compatibility_classes = [gateway_id => ClassName]         │
│  - init(): Initialize all registered compatibility classes              │
│  - register_compatibility_classes(): Create instances                   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    AbstractPaymentCompatibility                         │
│  - $gateway_id: string                                                  │
│  - $gateway_name: string                                                │
│  - $enabled: bool                                                       │
│  + abstract init(): void                                                │
│  + abstract register_filter_hooks(): void                               │
│  + abstract register_action_hooks(): void                               │
│  + get_integration(): AbstractPaymentGateway                            │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
              ┌─────────────────────┼─────────────────────┐
              ▼                     ▼                     ▼
┌─────────────────────┐ ┌─────────────────────┐ ┌─────────────────────┐
│StripePayment        │ │AuthorizeNet         │ │SquarePayment        │
│Compatibility        │ │Compatibility        │ │Compatibility        │
│                     │ │                     │ │                     │
│Hooks:               │ │Hooks:               │ │Hooks:               │
│- autoship_add_      │ │- autoship_add_      │ │- autoship_add_      │
│  Stripe_payment_    │ │  AuthorizeNet_      │ │  Square_payment_    │
│  method             │ │  payment_method     │ │  method             │
│- autoship_delete_   │ │- autoship_delete_   │ │- autoship_delete_   │
│  Stripe_payment_    │ │  AuthorizeNet_...   │ │  Square_...         │
│  method_qpilot_     │ │                     │ │                     │
│  match              │ │                     │ │                     │
└─────────────────────┘ └─────────────────────┘ └─────────────────────┘
```

### How Legacy Code Routes Through Compatibility

```php
// Legacy code calls:
$result = apply_filters('autoship_add_Stripe_payment_method', [], 'Stripe', $token);

// StripePaymentCompatibility registers:
add_filter('autoship_add_Stripe_payment_method', [$integration, 'add_payment_method'], 10, 3);

// Routes to:
StripePaymentIntegration::add_payment_method($data, $type, $token)
```

### Dependencies

- **Internal:** Module Infrastructure (PaymentGatewayRegistry), Foundation Layer
- **WordPress:** add_filter, add_action, apply_filters
- **WooCommerce:** Payment gateway availability checks

### Existing vs Missing Compatibility Classes

| Gateway | Compatibility Class | Status |
|---------|---------------------|--------|
| Stripe | StripePaymentCompatibility | ✅ Exists |
| AuthorizeNet | AuthorizeNetCompatibility | ✅ Exists |
| Square | SquarePaymentCompatibility | ✅ Exists |
| Braintree | BraintreePaymentCompatibility | ✅ Exists |
| NMI | - | ❌ Needs creation |
| CyberSource | - | ❌ Needs creation |
| PayPal | - | ❌ Needs creation |
| Paya | - | ❌ Needs creation |
| Sage | - | ❌ Needs creation |
| TrustCommerce | - | ❌ Needs creation |
| Checkout.com | - | ❌ Needs creation |
| Airwallex | - | ❌ Needs creation |

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy AbstractPaymentCompatibility.php | Base class, direct copy | 10 min |
| Copy PaymentsCompatibilityManager.php | Verify gateway mappings | 20 min |
| Copy existing compatibility classes (4) | Stripe, AuthorizeNet, Square, Braintree | 20 min |
| Create/copy tests for base class | AbstractPaymentCompatibilityTest | 30 min |
| Create/copy tests for manager | PaymentsCompatibilityManagerTest | 30 min |
| Run tests and debug | Fix hook registration issues | 45 min |
| **Optional:** Create missing compatibility classes | 8 classes × 30 min each | 4 hours |

### Time Estimate (Existing Classes Only)

| Scenario | Time |
|----------|------|
| **Optimistic** (clean copy) | 2 hours |
| **Realistic** (hook debugging) | 3 hours |
| **Pessimistic** (significant issues) | 4 hours |

**Recommended Estimate: 2.5 - 3.5 hours**

### Time Estimate (Including Missing Classes)

**Additional 4-6 hours** to create compatibility classes for remaining gateways.

---

## 4. Stripe Migration

### Description

The Stripe Migration is the reference implementation for migrating a payment gateway. It includes the integration class, compatibility layer, and full test coverage. Stripe is the most feature-complete implementation (556 LOC).

### What It Includes

| File | LOC | Purpose |
|------|-----|---------|
| `app/Domain/PaymentIntegrations/StripePaymentIntegration.php` | 556 | Full Stripe integration with all abstract methods |
| `app/Modules/Payments/Compatibility/StripePaymentCompatibility.php` | ~100 | Legacy hook registration |
| `tests/Domain/PaymentIntegrations/StripePaymentIntegrationTest.php` | ~400 | Unit tests for integration |
| `tests/Modules/Payments/Compatibility/StripePaymentCompatibilityTest.php` | ~600 | Compatibility layer tests |

**Total: ~1,656 LOC**

### StripePaymentIntegration Features

```php
class StripePaymentIntegration extends AbstractPaymentGateway {

    // Supported gateway IDs
    private static array $allowed = ['stripe', 'stripe_sepa', 'fkwcs_stripe'];

    // Factory method
    public static function build(string $gateway_id, array $settings): StripePaymentIntegration;

    // Core contract implementations
    public function initialize(): void;
    public function get_order_payment_data(int $order_id, WC_Order $order): ?QPilotPaymentData;
    public function add_payment_method(array $data, string $type, WC_Payment_Token $token): array;
    public function delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool;

    // Stripe-specific methods
    private function get_stripe_payment_method_data_by_id(string $pm_id, string $customer_id): ?QPilotPaymentData;
    private function get_stripe_customer_id_from_order(WC_Order $order): string;

    // Static filter methods for legacy compatibility
    public static function filter_autoship_valid_payment_methods(array $methods): array;
    public static function filter_autoship_payment_method_metadata(array $meta, WC_Order $order, $method): array;
}
```

### Legacy Functions Replaced

| Legacy Function (src/payments-stripe.php) | New Method |
|-------------------------------------------|------------|
| `autoship_get_stripe_order_payment_data()` | `StripePaymentIntegration::get_order_payment_data()` |
| `autoship_add_stripe_payment_method()` | `StripePaymentIntegration::add_payment_method()` |
| `autoship_delete_stripe_payment_method()` | `StripePaymentIntegration::delete_payment_method()` |
| `autoship_get_stripe_payment_method_data_by_id()` | `StripePaymentIntegration::get_stripe_payment_method_data_by_id()` |
| `autoship_stripe_valid_wc_payment_methods()` | `StripePaymentIntegration::filter_autoship_valid_payment_methods()` |

### Dependencies

- **Internal:** Foundation Layer (AbstractPaymentGateway, PaymentMethodType)
- **WordPress:** get_option, add_filter
- **WooCommerce:** WC_Order, WC_Payment_Token_CC, WC_Stripe_API (optional)
- **QPilot:** QPilotPaymentData

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy StripePaymentIntegration.php | 556 LOC, verify imports | 15 min |
| Copy StripePaymentCompatibility.php | Verify hook names | 10 min |
| Copy StripePaymentIntegrationTest.php | ~400 LOC test file | 10 min |
| Copy StripePaymentCompatibilityTest.php | ~600 LOC test file | 10 min |
| Run unit tests | Fix any mock issues | 30 min |
| Run integration tests | Test with WC Stripe plugin | 45 min |
| Verify factory mapping | Ensure factory creates Stripe correctly | 15 min |
| Manual testing | Add/delete payment method in WC | 30 min |
| Debug and fix issues | Address any failures | 45 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** (clean copy, tests pass) | 2.5 hours |
| **Realistic** (some test fixes needed) | 3.5 hours |
| **Pessimistic** (significant debugging) | 5 hours |

**Recommended Estimate: 3 - 4 hours**

---

## 5. Legacy Code Removal

### Description

Legacy Code Removal is the gradual deprecation and eventual removal of procedural payment code in `src/payments*.php`. This is done carefully to ensure backward compatibility during transition.

### What It Includes

| File | LOC | Status After Migration |
|------|-----|------------------------|
| `src/payments.php` | 1,443 | Keep - Core routing functions |
| `src/payments-stripe.php` | 473 | Deprecate - Routed via compatibility |
| `src/payments-braintree.php` | 368 | Deprecate - Routed via compatibility |
| `src/payments-ppxc.php` | 292 | Deprecate - Routed via compatibility |
| `src/payments-skyverge.php` | 245 | Deprecate - Routed via compatibility |
| `src/payments-cybersource.php` | 243 | Deprecate - Routed via compatibility |
| `src/payments-authorize-net.php` | 240 | Deprecate - Routed via compatibility |
| `src/payments-sagepaydirect.php` | 210 | Deprecate - Routed via compatibility |
| `src/payments-square.php` | 172 | Deprecate - Routed via compatibility |
| `src/payments-nmi.php` | 169 | Deprecate - Routed via compatibility |
| `src/payments-checkoutcom.php` | 135 | Deprecate - Routed via compatibility |
| `src/payments-airwallex.php` | 87 | Deprecate - Routed via compatibility |
| `src/payments-trustcommerce.php` | 72 | Deprecate - Routed via compatibility |
| `src/payments-paypalv3.php` | 60 | Deprecate - Routed via compatibility |
| `src/payments-test.php` | 49 | Keep - Test gateway |
| `src/payments-payav1.php` | 40 | Deprecate - Routed via compatibility |
| `src/payments-cod.php` | 17 | Keep - Simple, no migration needed |

**Total Legacy: 4,315 LOC**

### Deprecation Strategy

```
Phase 1: COEXISTENCE (Current)
├── Legacy code remains active
├── New code disabled by default (feature flags)
├── Compatibility layer routes when enabled
└── No breaking changes

Phase 2: PARALLEL OPERATION
├── Feature flags enable new code per-gateway
├── Legacy code still called if flag disabled
├── Monitor for issues
└── Gradual enablement

Phase 3: DEPRECATION NOTICES
├── Add _doing_it_wrong() notices to legacy functions
├── Log when legacy functions called directly
├── Update documentation
└── Notify integrators

Phase 4: REMOVAL (Future)
├── Remove deprecated function bodies
├── Keep function signatures with error
├── Eventually remove files entirely
└── Major version bump
```

### Legacy Functions That Need Routing (src/payments.php)

```php
// These functions should check feature flag and route accordingly:

function autoship_get_order_payment_data($order_id, $order) {
    $gateway_id = $order->get_payment_method();

    // If new implementation enabled, use it
    if (FeatureManager::is_payment_gateway_enabled($gateway_id)) {
        $registry = Plugin::get_service_container()->get(PaymentGatewayRegistry::class);
        $gateway = $registry->get_gateway($gateway_id);
        if ($gateway) {
            return $gateway->get_order_payment_data($order_id, $order);
        }
    }

    // Fall back to legacy
    return apply_filters("autoship_get_{$gateway_type}_order_payment_data", null, $order_id, $order);
}
```

### Migration Tasks (Stripe Only)

| Task | Description | Est. Time |
|------|-------------|-----------|
| Identify Stripe functions in legacy | List all functions to deprecate | 30 min |
| Add deprecation comments | Document in legacy files | 15 min |
| Verify routing works | Test legacy → compatibility → new | 45 min |
| Add deprecation notices (optional) | _doing_it_wrong() calls | 30 min |
| Update function bodies (optional) | Route to new implementation | 1 hour |
| Test backward compatibility | Ensure legacy callers still work | 45 min |

### Time Estimate (Stripe Only)

| Scenario | Time |
|----------|------|
| **Optimistic** (routing works, minimal changes) | 2 hours |
| **Realistic** (some routing fixes) | 3 hours |
| **Pessimistic** (complex legacy dependencies) | 4.5 hours |

**Recommended Estimate: 2.5 - 3.5 hours**

### Note on Full Legacy Removal

Full legacy code removal for ALL gateways is a **major undertaking** (4,315 LOC) and should be:
- Done incrementally per gateway
- Spread across multiple releases
- Thoroughly tested with each gateway's WC plugin
- Coordinated with deprecation notices

**Full removal estimate: 20-40 hours** (not recommended in initial migration)

---

## 6. Testing for Stripe

### Description

Comprehensive test coverage for the Stripe payment gateway migration, including unit tests, integration tests, and manual testing procedures.

### What It Includes

| Test File | Tests | LOC | Coverage |
|-----------|-------|-----|----------|
| `tests/Domain/PaymentIntegrations/StripePaymentIntegrationTest.php` | ~15 | ~400 | Unit tests for integration class |
| `tests/Modules/Payments/Compatibility/StripePaymentCompatibilityTest.php` | ~17 | ~600 | Compatibility layer tests |
| Manual test procedures | - | - | E2E payment flow validation |

**Total: ~32 automated tests, ~1,000 LOC**

### Unit Test Coverage

```php
// StripePaymentIntegrationTest.php

// Build/Construction Tests
- test_extends_abstract_payment_gateway()
- test_build_creates_instance_from_settings()
- test_build_sets_method_id()
- test_build_sets_method_type_to_stripe()
- test_build_extracts_test_mode_from_settings()
- test_build_extracts_api_keys_in_test_mode()
- test_build_extracts_api_keys_in_live_mode()

// Validation Tests
- test_is_valid_returns_true_when_properly_configured()
- test_is_valid_returns_false_when_missing_api_key()
- test_is_valid_returns_false_when_missing_method_id()

// Order Payment Data Tests
- test_get_order_payment_data_returns_null_for_non_stripe_order()
- test_get_order_payment_data_extracts_stripe_source_id()
- test_get_order_payment_data_extracts_stripe_card_id_legacy()
- test_get_order_payment_data_handles_link_payment_type()

// Add Payment Method Tests
- test_add_payment_method_returns_array_with_payment_data()
- test_add_payment_method_sets_gateway_payment_id()
- test_add_payment_method_sets_last_four()
- test_add_payment_method_sets_expiration()
- test_add_payment_method_handles_sepa()

// Delete Payment Method Tests
- test_delete_payment_method_returns_true_for_matching_token()
- test_delete_payment_method_returns_false_for_non_matching_token()
- test_delete_payment_method_compares_gateway_payment_id()
```

### Integration Test Coverage

```php
// StripePaymentCompatibilityTest.php

// Structure Tests
- test_extends_abstract_payment_compatibility()
- test_gateway_id_is_stripe()
- test_gateway_name_is_stripe()

// Hook Registration Tests
- test_init_registers_add_payment_method_filter()
- test_init_registers_delete_payment_method_filter()
- test_hooks_not_registered_when_integration_missing()

// Enable/Disable Tests
- test_is_enabled_returns_true_by_default()
- test_set_enabled_can_disable_compatibility()
- test_disabled_compatibility_does_not_register_hooks()

// Integration Retrieval Tests
- test_get_integration_returns_stripe_integration()
- test_get_integration_returns_null_when_not_registered()

// Filter Callback Tests
- test_add_payment_method_filter_calls_integration()
- test_delete_payment_method_filter_calls_integration()
```

### Manual Testing Checklist

```markdown
## Stripe Manual Testing Checklist

### Prerequisites
- [ ] WooCommerce Stripe Gateway plugin installed and configured
- [ ] Test API keys configured (pk_test_*, sk_test_*)
- [ ] QPilot test environment connected
- [ ] Feature flag enabled: define('AUTOSHIP_GATEWAY_STRIPE_ENABLED', true)

### Test: Add Payment Method
1. [ ] Log in as customer
2. [ ] Navigate to My Account → Payment Methods
3. [ ] Click "Add Payment Method"
4. [ ] Enter test card: 4242 4242 4242 4242
5. [ ] Submit form
6. [ ] Verify payment method appears in list
7. [ ] Verify payment method synced to QPilot (check API/dashboard)
8. [ ] Check logs for any errors

### Test: Delete Payment Method
1. [ ] From Payment Methods list, click Delete on Stripe card
2. [ ] Confirm deletion
3. [ ] Verify removed from WooCommerce
4. [ ] Verify removed from QPilot
5. [ ] Check logs for any errors

### Test: Order with Stripe Payment
1. [ ] Add product with Autoship options to cart
2. [ ] Proceed to checkout
3. [ ] Select Stripe payment method
4. [ ] Complete order with test card
5. [ ] Verify order created successfully
6. [ ] Verify payment data synced to QPilot
7. [ ] Check scheduled order has correct payment method

### Test: Update Payment on Scheduled Orders
1. [ ] Navigate to My Account → Scheduled Orders
2. [ ] Select a scheduled order
3. [ ] Change payment method to different Stripe card
4. [ ] Verify update in QPilot
5. [ ] Test "Apply to All Orders" button

### Test: Feature Flag Disabled
1. [ ] Remove or set to false: AUTOSHIP_GATEWAY_STRIPE_ENABLED
2. [ ] Repeat Add Payment Method test
3. [ ] Verify legacy code handles (no errors)
4. [ ] Verify no new code is executed (check logs)

### Test Cards
| Number | Description |
|--------|-------------|
| 4242 4242 4242 4242 | Succeeds |
| 4000 0000 0000 0002 | Declined |
| 4000 0000 0000 9995 | Insufficient funds |
| 4000 0025 0000 3155 | Requires 3DS |
```

### Test Data Fixtures

```php
// tests/Fixtures/StripeTestData.php

class StripeTestData {

    public static function get_valid_settings(): array {
        return [
            'enabled'              => 'yes',
            'testmode'             => 'yes',
            'test_publishable_key' => 'pk_test_TYooMQauvdEDq54NiTphI7jx',
            'test_secret_key'      => 'sk_test_4eC39HqLyjWDarjtT1zdp7dc',
            'publishable_key'      => '',
            'secret_key'           => '',
            'statement_descriptor' => 'AUTOSHIP',
            'capture'              => 'yes',
        ];
    }

    public static function get_mock_order_meta(): array {
        return [
            '_stripe_source_id'   => 'pm_1234567890',
            '_stripe_customer_id' => 'cus_1234567890',
            '_payment_method'     => 'stripe',
        ];
    }

    public static function get_mock_token_data(): array {
        return [
            'token'        => 'pm_1234567890',
            'last4'        => '4242',
            'expiry_month' => '12',
            'expiry_year'  => '2025',
            'card_type'    => 'visa',
            'gateway_id'   => 'stripe',
        ];
    }
}
```

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy StripePaymentIntegrationTest.php | Unit test file | 10 min |
| Copy StripePaymentCompatibilityTest.php | Integration test file | 10 min |
| Copy/create test fixtures | StripeTestData, mocks | 30 min |
| Copy SafeMockTrait (if used) | Test utility trait | 15 min |
| Run unit tests | Debug failures | 45 min |
| Run integration tests | Debug failures | 45 min |
| Fix mocking issues | Brain\Monkey, Mockery setup | 30 min |
| Achieve 80%+ coverage | Add missing tests if needed | 1 hour |
| Manual testing | Full E2E checklist | 1.5 hours |
| Document test results | Record pass/fail | 30 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** (tests pass, quick manual testing) | 4 hours |
| **Realistic** (some test fixes, full manual testing) | 6 hours |
| **Pessimistic** (significant test debugging, issues found) | 8 hours |

**Recommended Estimate: 5 - 7 hours**

---

## Summary: Total Migration Time Estimates

### Core Migration (Foundation + Module + Compatibility + Stripe)

| Component | Optimistic | Realistic | Pessimistic |
|-----------|------------|-----------|-------------|
| Foundation Layer | 1 hour | 1.5 hours | 2.5 hours |
| Module Infrastructure | 2 hours | 3 hours | 4.5 hours |
| Compatibility Layer | 2 hours | 3 hours | 4 hours |
| Stripe Migration | 2.5 hours | 3.5 hours | 5 hours |
| Legacy Code (Stripe only) | 2 hours | 3 hours | 4.5 hours |
| Testing for Stripe | 4 hours | 6 hours | 8 hours |
| **Total** | **13.5 hours** | **20 hours** | **28.5 hours** |

### Recommended Timeline

| Phase | Duration | Deliverable |
|-------|----------|-------------|
| Day 1 | 8 hours | Foundation + Module Infrastructure |
| Day 2 | 8 hours | Compatibility Layer + Stripe Integration |
| Day 3 | 4 hours | Testing + Legacy Routing |
| Buffer | 4 hours | Issue resolution, documentation |
| **Total** | **24 hours** | **Complete Stripe migration with tests** |

### Risk Factors

| Risk | Impact | Mitigation |
|------|--------|------------|
| Dependency issues | +2-4 hours | Copy files in dependency order |
| Test failures | +2-4 hours | Debug incrementally, use TDD |
| WooCommerce compatibility | +2-4 hours | Test with specific WC Stripe version |
| Legacy routing complexity | +2-4 hours | Keep legacy functional during transition |
| Missing test coverage | +2-4 hours | Prioritize critical paths |
