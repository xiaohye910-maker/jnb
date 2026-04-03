# Gateway Migration Estimates

Time estimates for migrating Authorize.Net, Braintree, PayPal, and Square payment gateways.

---

## Authorize.Net Migration

### What It Includes

| File | LOC | Status |
|------|-----|--------|
| `app/Domain/PaymentIntegrations/AuthorizeNetPaymentIntegration.php` | 241 | ✅ Exists |
| `app/Modules/Payments/Compatibility/AuthorizeNetCompatibility.php` | ~100 | ✅ Exists |
| `tests/Domain/PaymentIntegrations/AuthorizeNetPaymentIntegrationTest.php` | ~250 | ✅ Exists |
| `tests/Modules/Payments/Compatibility/AuthorizeNetCompatibilityTest.php` | ~300 | ⚠️ May need creation |
| Legacy: `src/payments-authorize-net.php` | 240 | Deprecate |

**Total: ~1,131 LOC**

### Integration Features

```php
class AuthorizeNetPaymentIntegration extends AbstractPaymentGateway {
    // Supported gateway IDs
    private static array $allowed = ['authorize_net_cim_credit_card'];

    // Implemented methods
    public static function build(string $gateway_id, array $settings);
    public function initialize(): void;
    public function get_order_payment_data(int $order_id, WC_Order $order): ?QPilotPaymentData;
    public function add_payment_method(array $data, string $type, WC_Payment_Token $token): array;
    public function delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool;
}
```

### Legacy Functions to Route

| Legacy Function | New Method |
|-----------------|------------|
| `autoship_get_authorize_net_order_payment_data()` | `get_order_payment_data()` |
| `autoship_add_authorize_net_payment_method()` | `add_payment_method()` |
| `autoship_delete_authorize_net_payment_method()` | `delete_payment_method()` |
| `autoship_authorize_net_valid_wc_payment_methods()` | Static filter method |

### Compatibility Hooks

```php
// AuthorizeNetCompatibility registers:
add_filter('autoship_add_AuthorizeNet_payment_method', [$integration, 'add_payment_method'], 10, 3);
add_filter('autoship_delete_AuthorizeNet_payment_method_qpilot_match', [$integration, 'delete_payment_method'], 10, 4);
add_action('wc_payment_gateway_authorize_net_cim_credit_card_payment_processed', ...);
add_action('wc_payment_gateway_authorize_net_cim_credit_card_payment_method_added', ...);
```

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy AuthorizeNetPaymentIntegration.php | 241 LOC, verify imports | 10 min |
| Copy AuthorizeNetCompatibility.php | Verify hook names | 10 min |
| Copy/verify integration test | ~250 LOC | 15 min |
| Create compatibility test (if missing) | ~300 LOC | 45 min |
| Run unit tests | Fix mock issues | 30 min |
| Update factory mapping | Verify factory creates correctly | 10 min |
| Test with WC Authorize.Net plugin | Integration testing | 45 min |
| Manual testing | Add/delete payment method | 30 min |
| Legacy routing verification | Ensure backward compatibility | 20 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** | 2.5 hours |
| **Realistic** | 3.5 hours |
| **Pessimistic** | 5 hours |

**Recommended Estimate: 3 - 4 hours**

### Risk Factors

| Risk | Impact | Notes |
|------|--------|-------|
| SkyVerge framework dependency | Medium | Authorize.Net uses SkyVerge base classes |
| CIM (Customer Information Manager) complexity | Low | Token handling is straightforward |
| Multiple action hooks | Low | More hooks than Stripe, but documented |

---

## Braintree Migration

### What It Includes

| File | LOC | Status |
|------|-----|--------|
| `app/Domain/PaymentIntegrations/BraintreePaymentIntegration.php` | 92 | ⚠️ Needs fix (inheritance) |
| `app/Modules/Payments/Compatibility/BraintreePaymentCompatibility.php` | ~100 | ✅ Exists |
| `tests/Domain/PaymentIntegrations/BraintreePaymentIntegrationTest.php` | ~200 | ✅ Exists |
| `tests/Modules/Payments/Compatibility/BraintreePaymentCompatibilityTest.php` | ~300 | ⚠️ May need creation |
| Legacy: `src/payments-braintree.php` | 368 | Deprecate |

**Total: ~1,060 LOC**

### ⚠️ Critical Issue: Inheritance Bug

```php
// CURRENT (WRONG):
class BraintreePaymentIntegration extends PaymentIntegration {

// SHOULD BE:
class BraintreePaymentIntegration extends AbstractPaymentGateway {
```

**This must be fixed during migration** - requires implementing missing abstract methods.

### Integration Features (After Fix)

```php
class BraintreePaymentIntegration extends AbstractPaymentGateway {
    // Supported gateway IDs
    private static array $allowed = ['braintree_credit_card', 'braintree_paypal'];

    // Methods to implement (currently missing due to wrong inheritance)
    public function initialize(): void;
    public function get_order_payment_data(int $order_id, WC_Order $order): ?QPilotPaymentData;
    public function add_payment_method(array $data, string $type, WC_Payment_Token $token): array;
    public function delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool;
}
```

### Legacy Functions to Route

| Legacy Function | New Method |
|-----------------|------------|
| `autoship_get_braintree_order_payment_data()` | `get_order_payment_data()` |
| `autoship_add_braintree_payment_method()` | `add_payment_method()` |
| `autoship_delete_braintree_payment_method()` | `delete_payment_method()` |
| `autoship_braintree_valid_wc_gateway_payment_methods()` | Static filter method |
| `autoship_get_braintree_payment_token_data()` | Helper method |

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy BraintreePaymentIntegration.php | 92 LOC | 10 min |
| **FIX: Change inheritance** | Extend AbstractPaymentGateway | 15 min |
| **FIX: Implement abstract methods** | ~150 LOC new code | 2 hours |
| Copy BraintreePaymentCompatibility.php | Verify hook names | 10 min |
| Copy/verify integration test | Update for new methods | 30 min |
| Create compatibility test (if missing) | ~300 LOC | 45 min |
| Run unit tests | Fix failures from inheritance change | 45 min |
| Test with WC Braintree plugin | Integration testing | 45 min |
| Manual testing | Add/delete for credit card AND PayPal | 45 min |
| Legacy routing verification | Ensure backward compatibility | 20 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** | 5 hours |
| **Realistic** | 7 hours |
| **Pessimistic** | 9 hours |

**Recommended Estimate: 6 - 8 hours**

### Risk Factors

| Risk | Impact | Notes |
|------|--------|-------|
| **Inheritance fix required** | **High** | Must implement abstract methods |
| Dual payment types (CC + PayPal) | Medium | Two different token types |
| Braintree SDK complexity | Medium | May need Braintree API calls |
| Test coverage gaps | Medium | Tests may not cover new methods |

---

## PayPal Migration

### What It Includes

| File | LOC | Status |
|------|-----|--------|
| `app/Domain/PaymentIntegrations/PayPalPaymentIntegration.php` | 90 | ✅ Exists (basic) |
| `app/Modules/Payments/Compatibility/PayPalPaymentCompatibility.php` | - | ❌ Needs creation |
| `tests/Domain/PaymentIntegrations/PayPalPaymentIntegrationTest.php` | ~150 | ✅ Exists |
| `tests/Modules/Payments/Compatibility/PayPalPaymentCompatibilityTest.php` | - | ❌ Needs creation |
| Legacy: `src/payments-ppxc.php` | 292 | Deprecate (Express Checkout) |
| Legacy: `src/payments-paypalv3.php` | 60 | Deprecate (PayPal V3) |

**Total: ~592 LOC existing + ~400 LOC to create**

### PayPal Complexity

PayPal has **multiple gateway implementations**:

| Gateway ID | Plugin | Status |
|------------|--------|--------|
| `ppec_paypal` | PayPal Express Checkout | Legacy, deprecated by PayPal |
| `ppcp-gateway` | PayPal Commerce Platform | Current recommended |
| `paypal` | Basic PayPal Standard | Simple redirect |

### Integration Features

```php
class PayPalPaymentIntegration extends AbstractPaymentGateway {
    // Supported gateway IDs
    private static array $allowed = ['ppec_paypal', 'ppcp-gateway', 'paypal'];

    // Implemented methods
    public static function build(string $gateway_id, array $settings);
    public function initialize(): void;
    public function get_order_payment_data(int $order_id, WC_Order $order): ?QPilotPaymentData;
    public function add_payment_method(array $data, string $type, WC_Payment_Token $token): array;
    public function delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool;
}
```

### Legacy Functions to Route

| Legacy Function | New Method |
|-----------------|------------|
| `autoship_get_ppxc_order_payment_data()` | `get_order_payment_data()` |
| `autoship_add_ppxc_payment_method()` | `add_payment_method()` |
| `autoship_delete_ppxc_payment_method()` | `delete_payment_method()` |
| `autoship_ppxc_valid_wc_gateway_payment_methods()` | Static filter method |

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy PayPalPaymentIntegration.php | 90 LOC, verify completeness | 15 min |
| **CREATE: PayPalPaymentCompatibility.php** | ~100 LOC new class | 1 hour |
| Copy/verify integration test | ~150 LOC | 15 min |
| **CREATE: Compatibility test** | ~300 LOC new file | 1 hour |
| Verify abstract method implementations | May need enhancements | 45 min |
| Run unit tests | Fix mock issues | 30 min |
| Test with PayPal Express Checkout | Legacy plugin | 30 min |
| Test with PayPal Commerce Platform | Current plugin | 45 min |
| Manual testing | Add/delete payment method | 30 min |
| Legacy routing verification | Both PPXC and PPCP | 30 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** | 4.5 hours |
| **Realistic** | 6 hours |
| **Pessimistic** | 8 hours |

**Recommended Estimate: 5 - 7 hours**

### Risk Factors

| Risk | Impact | Notes |
|------|--------|-------|
| **No compatibility class exists** | **High** | Must create from scratch |
| Multiple PayPal plugins | High | Different token structures |
| PayPal Express Checkout deprecated | Medium | Users may still use it |
| PayPal Commerce Platform complexity | Medium | Newer, different API |
| Billing agreement handling | Medium | Subscription tokens differ |

---

## Square Migration

### What It Includes

| File | LOC | Status |
|------|-----|--------|
| `app/Domain/PaymentIntegrations/SquarePaymentIntegration.php` | 335 | ✅ Exists (comprehensive) |
| `app/Modules/Payments/Compatibility/SquarePaymentCompatibility.php` | ~100 | ✅ Exists |
| `tests/Domain/PaymentIntegrations/SquarePaymentIntegrationTest.php` | ~300 | ✅ Exists |
| `tests/Modules/Payments/Compatibility/SquarePaymentCompatibilityTest.php` | ~300 | ⚠️ May need creation |
| Legacy: `src/payments-square.php` | 172 | Deprecate |

**Total: ~1,207 LOC**

### Integration Features

```php
class SquarePaymentIntegration extends AbstractPaymentGateway {
    // Supported gateway IDs
    private static array $allowed = ['square_credit_card', 'square_cash_app_pay'];

    // Implemented methods (comprehensive - 335 LOC)
    public static function build(string $gateway_id, array $settings);
    public function initialize(): void;
    public function get_order_payment_data(int $order_id, WC_Order $order): ?QPilotPaymentData;
    public function add_payment_method(array $data, string $type, WC_Payment_Token $token): array;
    public function delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool;

    // Square-specific methods
    private function get_square_customer_id(WC_Order $order): ?string;
    private function get_square_card_data(WC_Payment_Token $token): array;
}
```

### Legacy Functions to Route

| Legacy Function | New Method |
|-----------------|------------|
| `autoship_get_square_order_payment_data()` | `get_order_payment_data()` |
| `autoship_add_square_payment_method()` | `add_payment_method()` |
| `autoship_delete_square_payment_method()` | `delete_payment_method()` |
| `autoship_square_valid_wc_gateway_payment_methods()` | Static filter method |

### Compatibility Hooks

```php
// SquarePaymentCompatibility registers:
add_filter('autoship_add_Square_payment_method', [$integration, 'add_payment_method'], 10, 3);
add_filter('autoship_delete_Square_payment_method_qpilot_match', [$integration, 'delete_payment_method'], 10, 4);
```

### Migration Tasks

| Task | Description | Est. Time |
|------|-------------|-----------|
| Copy SquarePaymentIntegration.php | 335 LOC, comprehensive | 15 min |
| Copy SquarePaymentCompatibility.php | Verify hook names | 10 min |
| Copy integration test | ~300 LOC | 15 min |
| Create compatibility test (if missing) | ~300 LOC | 45 min |
| Run unit tests | Fix mock issues | 30 min |
| Test with WC Square plugin | Integration testing | 45 min |
| Test Cash App Pay | Secondary payment type | 30 min |
| Manual testing | Add/delete payment method | 30 min |
| Legacy routing verification | Ensure backward compatibility | 20 min |

### Time Estimate

| Scenario | Time |
|----------|------|
| **Optimistic** | 3 hours |
| **Realistic** | 4 hours |
| **Pessimistic** | 5.5 hours |

**Recommended Estimate: 3.5 - 4.5 hours**

### Risk Factors

| Risk | Impact | Notes |
|------|--------|-------|
| Cash App Pay variant | Low | Secondary gateway ID, same class |
| Square API version changes | Low | Token structure is stable |
| SkyVerge framework (older versions) | Low | Similar to Authorize.Net |

---

## Comparison Summary

| Gateway | LOC | Compatibility | Tests | Issues | Time Estimate |
|---------|-----|---------------|-------|--------|---------------|
| **Authorize.Net** | 1,131 | ✅ Exists | ⚠️ Partial | None | 3 - 4 hours |
| **Braintree** | 1,060 | ✅ Exists | ⚠️ Partial | ⚠️ Inheritance bug | 6 - 8 hours |
| **PayPal** | 992 | ❌ Missing | ⚠️ Partial | ❌ Create compatibility | 5 - 7 hours |
| **Square** | 1,207 | ✅ Exists | ⚠️ Partial | None | 3.5 - 4.5 hours |

### Combined Total Estimate

| Scenario | Authorize.Net | Braintree | PayPal | Square | **Total** |
|----------|---------------|-----------|--------|--------|-----------|
| **Optimistic** | 2.5 hrs | 5 hrs | 4.5 hrs | 3 hrs | **15 hours** |
| **Realistic** | 3.5 hrs | 7 hrs | 6 hrs | 4 hrs | **20.5 hours** |
| **Pessimistic** | 5 hrs | 9 hrs | 8 hrs | 5.5 hrs | **27.5 hours** |

**Recommended Total: 18 - 23 hours (2.5 - 3 working days)**

---

## Recommended Migration Order

Based on complexity and dependencies:

| Order | Gateway | Reason | Time |
|-------|---------|--------|------|
| 1 | **Square** | Most complete, low risk, good template | 4 hours |
| 2 | **Authorize.Net** | Complete, low risk | 3.5 hours |
| 3 | **PayPal** | Missing compatibility, but straightforward | 6 hours |
| 4 | **Braintree** | Requires inheritance fix, highest risk | 7 hours |

### Migration Timeline

```
Day 1 (8 hours):
├── Square Migration (4 hours)
└── Authorize.Net Migration (3.5 hours)
    └── Buffer (0.5 hours)

Day 2 (8 hours):
├── PayPal Migration (6 hours)
└── Buffer / Start Braintree (2 hours)

Day 3 (6 hours):
├── Complete Braintree Migration (5 hours)
└── Integration Testing All Gateways (1 hour)

Total: 22 hours across 3 days
```

---

## Per-Gateway Checklist Template

```markdown
## [Gateway] Migration Checklist

### Prerequisites
- [ ] Foundation Layer migrated
- [ ] Module Infrastructure migrated
- [ ] Compatibility base classes migrated

### Integration Class
- [ ] Copy [Gateway]PaymentIntegration.php
- [ ] Verify extends AbstractPaymentGateway
- [ ] Verify all abstract methods implemented
- [ ] Update PaymentIntegrationFactory mapping

### Compatibility Class
- [ ] Copy or create [Gateway]PaymentCompatibility.php
- [ ] Register in PaymentsCompatibilityManager.$available_compatibility_classes
- [ ] Verify hook names match legacy filters

### Tests
- [ ] Copy [Gateway]PaymentIntegrationTest.php
- [ ] Copy or create [Gateway]PaymentCompatibilityTest.php
- [ ] Run tests: vendor\bin\phpunit --filter [Gateway]
- [ ] Achieve 80%+ coverage

### Integration Testing
- [ ] Install WooCommerce [Gateway] plugin
- [ ] Configure test API credentials
- [ ] Enable feature flag
- [ ] Test add payment method
- [ ] Test delete payment method
- [ ] Test order payment data sync
- [ ] Test with feature flag disabled (legacy)

### Documentation
- [ ] Update Gateway-Migration-Tasks.md
- [ ] Record any issues found
- [ ] Note any deviations from plan
```

---

## Detailed Task Breakdown by Gateway

### Authorize.Net Tasks

```
□ Migration Setup (15 min)
  □ Verify directory structure exists
  □ Check factory mapping

□ Integration Class (25 min)
  □ Copy AuthorizeNetPaymentIntegration.php
  □ Verify imports and namespace
  □ Check abstract method implementations

□ Compatibility Class (20 min)
  □ Copy AuthorizeNetCompatibility.php
  □ Verify hook registration
  □ Check integration retrieval

□ Unit Tests (45 min)
  □ Copy AuthorizeNetPaymentIntegrationTest.php
  □ Run tests, fix failures
  □ Verify coverage

□ Compatibility Tests (45 min)
  □ Create or copy AuthorizeNetCompatibilityTest.php
  □ Run tests, fix failures

□ Integration Testing (45 min)
  □ Install WC Authorize.Net CIM plugin
  □ Configure test credentials
  □ Test add/delete payment methods
  □ Verify QPilot sync

□ Legacy Verification (20 min)
  □ Disable feature flag
  □ Verify legacy code works
  □ Check no regressions

Total: ~3.5 hours
```

### Braintree Tasks

```
□ Migration Setup (15 min)
  □ Verify directory structure exists
  □ Check factory mapping

□ Integration Class - FIX REQUIRED (2.5 hours)
  □ Copy BraintreePaymentIntegration.php
  □ Change: extends PaymentIntegration → extends AbstractPaymentGateway
  □ Implement initialize()
  □ Implement get_order_payment_data() - reference src/payments-braintree.php
  □ Implement add_payment_method() - reference legacy code
  □ Implement delete_payment_method() - reference legacy code
  □ Handle both credit_card and paypal types

□ Compatibility Class (20 min)
  □ Copy BraintreePaymentCompatibility.php
  □ Verify hook registration for both payment types
  □ Check integration retrieval

□ Unit Tests (1 hour)
  □ Copy BraintreePaymentIntegrationTest.php
  □ Add tests for new abstract method implementations
  □ Run tests, fix failures
  □ Test both CC and PayPal token types

□ Compatibility Tests (45 min)
  □ Create or copy BraintreePaymentCompatibilityTest.php
  □ Run tests, fix failures

□ Integration Testing (1 hour)
  □ Install WC Braintree plugin
  □ Configure test credentials
  □ Test add credit card payment method
  □ Test add PayPal payment method
  □ Test delete both types
  □ Verify QPilot sync

□ Legacy Verification (20 min)
  □ Disable feature flag
  □ Verify legacy code works
  □ Check no regressions

Total: ~7 hours
```

### PayPal Tasks

```
□ Migration Setup (15 min)
  □ Verify directory structure exists
  □ Check factory mapping for ppec_paypal, ppcp-gateway

□ Integration Class (1 hour)
  □ Copy PayPalPaymentIntegration.php
  □ Verify completeness of implementations
  □ May need to enhance for PPCP support
  □ Handle both Express Checkout and Commerce Platform

□ Compatibility Class - CREATE NEW (1.5 hours)
  □ Create PayPalPaymentCompatibility.php
  □ Define gateway_id and gateway_name
  □ Implement init()
  □ Implement register_filter_hooks()
    □ autoship_add_PayPal_payment_method
    □ autoship_delete_PayPal_payment_method_qpilot_match
  □ Implement register_action_hooks()
  □ Register in PaymentsCompatibilityManager

□ Unit Tests (45 min)
  □ Copy PayPalPaymentIntegrationTest.php
  □ Run tests, fix failures
  □ Add tests for both gateway types

□ Compatibility Tests - CREATE NEW (1 hour)
  □ Create PayPalPaymentCompatibilityTest.php
  □ Test hook registration
  □ Test integration retrieval
  □ Test enable/disable

□ Integration Testing (1 hour)
  □ Install WC PayPal Express Checkout (legacy)
  □ Install WC PayPal Payments (PPCP)
  □ Configure test credentials for both
  □ Test add payment method (both)
  □ Test delete payment method
  □ Verify QPilot sync

□ Legacy Verification (30 min)
  □ Disable feature flag
  □ Verify legacy PPXC code works
  □ Verify legacy PPCP code works
  □ Check no regressions

Total: ~6 hours
```

### Square Tasks

```
□ Migration Setup (15 min)
  □ Verify directory structure exists
  □ Check factory mapping for square_credit_card, square_cash_app_pay

□ Integration Class (25 min)
  □ Copy SquarePaymentIntegration.php (335 LOC - comprehensive)
  □ Verify imports and namespace
  □ Check all implementations complete

□ Compatibility Class (20 min)
  □ Copy SquarePaymentCompatibility.php
  □ Verify hook registration
  □ Check integration retrieval

□ Unit Tests (45 min)
  □ Copy SquarePaymentIntegrationTest.php
  □ Run tests, fix failures
  □ Verify coverage

□ Compatibility Tests (45 min)
  □ Create or copy SquarePaymentCompatibilityTest.php
  □ Run tests, fix failures

□ Integration Testing (1 hour)
  □ Install WC Square plugin
  □ Configure test credentials
  □ Test add credit card payment method
  □ Test Cash App Pay (if available)
  □ Test delete payment method
  □ Verify QPilot sync

□ Legacy Verification (20 min)
  □ Disable feature flag
  □ Verify legacy code works
  □ Check no regressions

Total: ~4 hours
```
