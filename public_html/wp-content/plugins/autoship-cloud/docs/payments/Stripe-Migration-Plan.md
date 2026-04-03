# Stripe Payment Gateway Migration Plan

> **Target Release**: 2.12.0
> **Status**: PLANNED
> **Priority**: P1
> **Estimated Effort**: 16-23 hours (3-4 days)
> **Last Updated**: January 2026

---

## Overview

Stripe is the first payment gateway to migrate from legacy procedural code to the modern module architecture. This serves as the template for all subsequent gateway migrations.

### Why Stripe First?

1. **Most complete integration** - 556 LOC with all abstract methods implemented
2. **Highest adoption** - Most commonly used gateway by merchants
3. **Best test coverage** - Integration class already at 100%
4. **Compatibility exists** - `StripePaymentCompatibility` already written
5. **Low risk** - Stable API, well-documented

---

## Current State

### Modern Code (EXISTS)

| File | LOC | Coverage | Status |
|------|-----|----------|--------|
| `app/Domain/PaymentIntegrations/StripePaymentIntegration.php` | 556 | 100% | COMPLETE |
| `tests/Domain/PaymentIntegrations/StripePaymentIntegrationTest.php` | ~400 | - | COMPLETE |

### Legacy Code (TO DEPRECATE)

| Function | Location | Lines |
|----------|----------|-------|
| `autoship_get_stripe_order_payment_data()` | src/payments.php | ~50 |
| `autoship_add_stripe_payment_method()` | src/payments.php | ~60 |
| `autoship_delete_stripe_payment_method()` | src/payments.php | ~30 |
| `autoship_get_stripe_payment_method_data_by_id()` | src/payments.php | ~40 |
| Various filters and hooks | src/payments.php | ~50 |
| **Total Legacy Stripe Code** | | **~230 lines** |

### Supported Gateway IDs

```php
private static array $allowed = [
    'stripe',           // WooCommerce Stripe Gateway
    'stripe_sepa',      // Stripe SEPA Direct Debit
    'link',             // Stripe Link
    'fkwcs_stripe',     // FunnelKit Stripe
    'fkwcs_stripe_ach', // FunnelKit Stripe ACH
];
```

---

## Implementation Tasks

### Task 1: Create Payments Module Structure

**Estimated Time**: 2-3 hours

Create the module infrastructure that will host all payment gateway services.

```
app/Modules/Payments/
├── PaymentsModule.php
├── Services/
│   ├── PaymentGatewayRegistry.php
│   ├── PaymentGatewayService.php
│   └── PaymentsCompatibilityService.php
├── Compatibility/
│   ├── AbstractPaymentCompatibility.php
│   ├── PaymentsCompatibilityManager.php
│   └── StripePaymentCompatibility.php
└── Helpers/
    └── PaymentMethodHelper.php
```

**Sub-tasks**:
- [ ] Create `PaymentsModule.php` implementing `ModuleInterface`
- [ ] Create `PaymentGatewayRegistry.php` for gateway instance management
- [ ] Create `PaymentGatewayService.php` for payment operations
- [ ] Register module in `Plugin.php`

### Task 2: Create Compatibility Layer

**Estimated Time**: 2-3 hours

Build the bridge that routes legacy filter calls to modern implementations.

**Sub-tasks**:
- [ ] Create `AbstractPaymentCompatibility.php` base class
- [ ] Create `PaymentsCompatibilityManager.php` for registration
- [ ] Create `StripePaymentCompatibility.php` for Stripe-specific hooks
- [ ] Verify hook names match legacy code exactly

**Legacy Hooks to Route**:
```php
// Filters
'autoship_add_Stripe_payment_method'
'autoship_delete_Stripe_payment_method_qpilot_match'
'autoship_Stripe_payment_method_metadata'
'autoship_valid_payment_method_ids'
'autoship_valid_payment_method_types'
'autoship_payment_method_gateway_type'

// Actions
'woocommerce_new_payment_token'
'woocommerce_payment_token_deleted'
```

### Task 3: Implement Feature Flag

**Estimated Time**: 1 hour

Add feature flag support to enable/disable modern implementation per gateway.

**Sub-tasks**:
- [ ] Add `payment_gateway_stripe` to FeatureManager
- [ ] Create routing logic in legacy functions
- [ ] Ensure fallback to legacy when disabled

```php
// In FeatureManager
public static function is_payment_gateway_enabled(string $gateway): bool {
    return self::is_enabled("payment_gateway_{$gateway}");
}

// In legacy function
function autoship_get_order_payment_data($order_id) {
    $order = wc_get_order($order_id);
    $gateway = $order->get_payment_method();

    if (FeatureManager::is_payment_gateway_enabled($gateway)) {
        $registry = Plugin::get_service_container()->get(PaymentGatewayRegistry::class);
        $integration = $registry->get($gateway);
        if ($integration) {
            return $integration->get_order_payment_data($order_id, $order);
        }
    }

    // Fallback to legacy
    return apply_filters("autoship_get_{$gateway_type}_order_payment_data", null, $order_id, $order);
}
```

### Task 4: Write Module Tests

**Estimated Time**: 4-5 hours

Create comprehensive test coverage for new module code.

**Test Files to Create**:
- [ ] `tests/Modules/Payments/PaymentsModuleTest.php`
- [ ] `tests/Modules/Payments/Services/PaymentGatewayRegistryTest.php`
- [ ] `tests/Modules/Payments/Services/PaymentGatewayServiceTest.php`
- [ ] `tests/Modules/Payments/Compatibility/StripePaymentCompatibilityTest.php`
- [ ] `tests/Modules/Payments/Compatibility/PaymentsCompatibilityManagerTest.php`

**Coverage Target**: 80%+

### Task 5: Integration Testing

**Estimated Time**: 3-4 hours

Test the complete flow with WooCommerce Stripe plugin.

**Test Scenarios**:
1. Add payment method via My Account
2. Delete payment method
3. Checkout with Stripe card
4. Payment data sync to QPilot
5. Update payment method on scheduled order
6. Apply payment to all scheduled orders

**Test with Feature Flag**:
1. Flag enabled → new code executes
2. Flag disabled → legacy code executes
3. No errors in either mode

### Task 6: Legacy Function Updates

**Estimated Time**: 2-3 hours

Update legacy functions to route through new implementation when enabled.

**Functions to Update in `src/payments.php`**:
- [ ] `autoship_get_order_payment_data()` - add routing
- [ ] `autoship_add_tokenized_payment_method()` - add routing
- [ ] `autoship_add_general_payment_method()` - add routing
- [ ] `autoship_delete_payment_method()` - add routing

### Task 7: Documentation & Cleanup

**Estimated Time**: 1-2 hours

- [ ] Add PHPDoc to all new classes
- [ ] Update CLAUDE.md with new module
- [ ] Add deprecation notices to legacy functions (comments only for now)
- [ ] Create migration notes for release

---

## File Changes Summary

### New Files to Create

| File | LOC (est.) |
|------|------------|
| `app/Modules/Payments/PaymentsModule.php` | ~130 |
| `app/Modules/Payments/Services/PaymentGatewayRegistry.php` | ~85 |
| `app/Modules/Payments/Services/PaymentGatewayService.php` | ~100 |
| `app/Modules/Payments/Services/PaymentsCompatibilityService.php` | ~80 |
| `app/Modules/Payments/Compatibility/AbstractPaymentCompatibility.php` | ~180 |
| `app/Modules/Payments/Compatibility/PaymentsCompatibilityManager.php` | ~150 |
| `app/Modules/Payments/Compatibility/StripePaymentCompatibility.php` | ~100 |
| `tests/Modules/Payments/PaymentsModuleTest.php` | ~150 |
| `tests/Modules/Payments/Services/PaymentGatewayRegistryTest.php` | ~200 |
| `tests/Modules/Payments/Compatibility/StripePaymentCompatibilityTest.php` | ~250 |
| **Total New Code** | **~1,425 LOC** |

### Files to Modify

| File | Changes |
|------|---------|
| `app/Core/Plugin.php` | Register PaymentsModule |
| `app/Core/FeatureManager.php` | Add payment_gateway_* flags |
| `src/payments.php` | Add routing logic (~50 lines) |

---

## Rollback Plan

If issues are discovered after deployment:

1. **Immediate**: Disable feature flag
   ```php
   define('AUTOSHIP_GATEWAY_STRIPE_ENABLED', false);
   ```

2. **Automatic fallback**: Legacy code handles all requests

3. **No database changes**: Safe to toggle without migration

4. **Logging**: All routing decisions logged for debugging

---

## Manual Testing Checklist

### Prerequisites

- [ ] WooCommerce Stripe Gateway plugin installed (v7.0+)
- [ ] Test API keys configured (pk_test_*, sk_test_*)
- [ ] QPilot test environment connected
- [ ] Feature flag enabled

### Test: Add Payment Method

1. [ ] Log in as customer
2. [ ] Navigate to My Account → Payment Methods
3. [ ] Click "Add Payment Method"
4. [ ] Enter test card: `4242 4242 4242 4242`, `12/29`, `123`
5. [ ] Submit form
6. [ ] Verify payment method appears in list
7. [ ] Verify payment method synced to QPilot
8. [ ] Check logs for any errors

### Test: Delete Payment Method

1. [ ] From Payment Methods list, click Delete on Stripe card
2. [ ] Confirm deletion
3. [ ] Verify removed from WooCommerce
4. [ ] Verify removed from QPilot
5. [ ] Check logs for any errors

### Test: Checkout with Stripe

1. [ ] Add product with Autoship options to cart
2. [ ] Proceed to checkout
3. [ ] Select Stripe payment method
4. [ ] Complete order with test card
5. [ ] Verify order created successfully
6. [ ] Verify payment data synced to QPilot
7. [ ] Check scheduled order has correct payment method

### Test: Feature Flag Toggle

1. [ ] With flag enabled: verify new code path (check logs)
2. [ ] Disable flag: `define('AUTOSHIP_GATEWAY_STRIPE_ENABLED', false)`
3. [ ] Repeat add payment method test
4. [ ] Verify legacy code handles (check logs)
5. [ ] Re-enable flag

### Test Cards

| Number | Description |
|--------|-------------|
| 4242 4242 4242 4242 | Succeeds |
| 4000 0000 0000 0002 | Declined |
| 4000 0000 0000 9995 | Insufficient funds |
| 4000 0025 0000 3155 | Requires 3DS |

---

## Success Criteria

### Code Quality

- [ ] All new code passes PHPCS (zero ignores)
- [ ] 80%+ test coverage on new code
- [ ] All existing tests still pass
- [ ] No increase in PHPCS ignores

### Functionality

- [ ] Feature flag enables/disables correctly
- [ ] Legacy fallback works when disabled
- [ ] All Stripe operations work (add, delete, sync)
- [ ] QPilot receives correct payment data

### Performance

- [ ] No measurable performance regression
- [ ] Lazy loading of payment services

### Documentation

- [ ] All new classes have PHPDoc
- [ ] Module registered in CLAUDE.md
- [ ] Release notes prepared

---

## Timeline

| Day | Focus | Tasks |
|-----|-------|-------|
| 1 | Module Structure | Tasks 1, 2 |
| 2 | Feature Flag + Routing | Tasks 3, 6 |
| 3 | Testing | Tasks 4, 5 |
| 4 | Polish | Task 7, Bug fixes, Final testing |

---

## Dependencies

### Required Before Start

- [ ] Modern foundation exists (DONE - `app/Domain/PaymentIntegrations/`)
- [ ] StripePaymentIntegration tested (DONE - 100% coverage)
- [ ] ServiceContainer available (DONE)
- [ ] ModuleInterface defined (DONE)

### External Dependencies

- WooCommerce Stripe Gateway v7.0+
- WooCommerce 3.4.1+
- PHP 7.4+

---

*Document Created: January 2026*
*Author: Technical Architecture*
