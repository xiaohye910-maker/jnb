# Payment Gateway Migration Review Report

**Date:** 2026-03-04
**Branch:** release/2.12.1
**Reviewer:** Claude Code (automated review)
**Reference Implementations (QA-validated):** Stripe, Authorize.NET, PayPal

---

## Executive Summary

A thorough code review was performed on 8 payment gateway migrations comparing the new OOP implementations (`app/Domain/PaymentIntegrations/`, `app/Modules/Payments/Compatibility/`) against the legacy procedural code (`src/payments.php`) and the 3 QA-validated reference gateways.

### Overall Status

| Gateway | Integration | Compatibility | Factory | Tests | Verdict |
|---------|:-----------:|:-------------:|:-------:|:-----:|---------|
| NMI | PASS | PASS | PASS | GOOD | **Ready** |
| PayaV1 | PASS | PASS | PASS | GOOD | **Ready** |
| Sage | PASS | PASS | PASS | GOOD | **Ready** |
| Airwallex | PASS | PASS | PASS | PARTIAL | **Ready** (needs test expansion) |
| TrustCommerce | PASS | GAP | PASS | GOOD | **Needs Fix** |
| Checkout | GAP | PASS | PASS | PARTIAL | **Needs Fix** |
| Braintree | GAP | GAP | PASS | PARTIAL | **Needs Fix** |
| CyberSourceV2 | GAP | GAP | PASS | PARTIAL | **Needs Fixes** |

**Summary:** 4 gateways are ready (or nearly ready), 4 gateways require fixes before QA validation.

---

## Detailed Gateway Reviews

---

### 1. NMI — PASS (No Issues)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/NmiPaymentIntegration.php`
- `app/Modules/Payments/Compatibility/NmiPaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/NmiPaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/NmiPaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway variants | PASS | Both `nmi_gateway_woocommerce_credit_card` and `nmi` supported |
| `build()` | PASS | Correct sandbox/live credential mapping |
| `is_valid()` | PASS | Validates method_type, method_id, api_account, api_key_1 |
| `get_order_payment_data()` | PASS | Dual-path routing for both variants; metadata fields match legacy exactly |
| `add_payment_method()` | PASS | `gatewayCustomerId = gatewayPaymentId` swap matches legacy |
| `delete_payment_method()` | PASS | Matches on `gatewayCustomerId === token` |
| `add_metadata()` | PASS | Transaction ID fields match legacy |
| `force_tokenization()` | PASS | Always returns `true` as in legacy |
| Compatibility hooks | PASS | All legacy hooks removed and re-registered correctly |
| Factory registration | PASS | Both gateway IDs registered |
| Test coverage | GOOD | 10 integration tests + comprehensive compatibility tests |

**Gaps:** None.

---

### 2. PayaV1 — PASS (No Issues)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/PayaV1PaymentIntegration.php`
- `app/Modules/Payments/Compatibility/PayaV1PaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/PayaV1PaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/PayaV1PaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway ID | PASS | `sagepaymentsusaapi` matches legacy |
| `build()` | PASS | Correct `testing`/`live` environment detection; maps `M_id`/`M_key` credentials |
| `is_valid()` | PASS | Validates all required fields |
| `get_order_payment_data()` | PASS | Reads `_SageToken` meta, uses `WC_Payment_Tokens::get_customer_tokens()`, MMYY format |
| `add_payment_method()` | PASS | Token-as-customer-ID swap matches legacy exactly |
| `delete_payment_method()` | PASS | Matches on `gatewayCustomerId === token` |
| `add_metadata()` | PASS | Sets payment method + title identical to legacy |
| Compatibility hooks | PASS | Correct removal and re-registration |
| Factory registration | PASS | `sagepaymentsusaapi` case present |
| Test coverage | GOOD | Comprehensive integration + compatibility tests |

**Gaps:** None.

---

### 3. Sage (SagePay Direct) — PASS (No Issues)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/SagePaymentIntegration.php`
- `app/Modules/Payments/Compatibility/SagePaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/SagePaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/SagePaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway ID | PASS | `sagepaydirect` matches legacy |
| `build()` | PASS | Correct configuration |
| `is_valid()` | NOTE | Returns `false` — intentionally disabled; not a bug |
| `get_order_payment_data()` | PASS | Reads `_SagePayDirectToken`, token retrieval, MMYY format all match |
| `add_payment_method()` | PASS | `gatewayCustomerId = null` matches legacy |
| `delete_payment_method()` | PASS | Matches on `gatewayPaymentId === token` |
| `checkout_order_patch()` | PASS | Handles `woocommerce_checkout_order_processed` hook, stores `_SagePayDirectToken` — matches legacy line-for-line |
| `add_metadata()` | PASS | Sets `_VendorTxCode`, payment method + title match legacy |
| Compatibility hooks | PASS | All filter AND action hooks correctly removed/re-registered, including checkout patch |
| Factory registration | PASS | `sagepaydirect` case present |
| Test coverage | GOOD | Comprehensive tests |

**Gaps:** None. The `is_valid() → false` behavior appears intentional (gateway disabled by design until feature flag enables it).

---

### 4. Airwallex — PASS (Minor Test Gap)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/AirwallexPaymentIntegration.php`
- `app/Modules/Payments/Compatibility/AirwallexPaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/AirwallexPaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/AirwallexPaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway ID | PASS | `airwallex_card` matches legacy |
| `build()` | PASS | Correct Airwallex-specific option keys for sandbox detection |
| `is_valid()` | PASS | Full validation |
| `get_order_payment_data()` | PASS | Reads `airwallex_consent_id` + `airwallex_customer_id`; fallback workaround with `last_four='0000'`, `expiration='01/30'` matches legacy lines 1484-1492 |
| `add_payment_method()` | PASS | Calls `autoship_get_airwallex_customer_id()` helper, matches legacy |
| `delete_payment_method()` | PASS | Calls helper, validates both customer and payment IDs |
| Compatibility hooks | PASS | Filter hooks correctly bridged; no action hooks (intentional) |
| Factory registration | PASS | `airwallex_card` case present |
| Test coverage | PARTIAL | Missing tests for `get_order_payment_data()`, `add_payment_method()`, `delete_payment_method()` |

**Gaps:**

| # | Severity | Description |
|---|----------|-------------|
| A-1 | LOW | Missing unit tests for `get_order_payment_data()` (including fallback scenario), `add_payment_method()`, and `delete_payment_method()`. The implementation is correct but these critical methods lack test coverage. |

---

### 5. TrustCommerce — NEEDS FIX (1 Issue)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/TrustCommercePaymentIntegration.php`
- `app/Modules/Payments/Compatibility/TrustCommercePaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/TrustCommercePaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/TrustCommercePaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway ID | PASS | `trustcommerce` matches legacy |
| `build()` | PASS | Correct environment detection |
| `is_valid()` | PASS | Proper validation |
| `get_order_payment_data()` | PASS | Reads `_trustcommerce_customer_id`, MMYY format, matches legacy |
| `add_payment_method()` | PASS | Token-as-customer-ID swap matches legacy |
| `delete_payment_method()` | PASS | Matches on `gatewayCustomerId === token` |
| `add_metadata()` | PASS | Sets payment method + title correctly |
| Factory registration | PASS | `trustcommerce` case present |
| Test coverage | GOOD | 17 integration tests + 30 compatibility tests |

**Gaps:**

| # | Severity | Description | Location |
|---|----------|-------------|----------|
| TC-1 | HIGH | **Missing `delete_payment_method` filter hook in compatibility layer.** `register_filter_hooks()` only registers `autoship_add_TrustCommerce_payment_method` but does NOT register `autoship_delete_TrustCommerce_payment_method_qpilot_match`. Compare to Sage, PayaV1, and NMI which all register both add AND delete hooks. The integration class has a correct `delete_payment_method()` implementation but it is never wired to the legacy hook system. | `TrustCommercePaymentCompatibility.php` → `register_filter_hooks()` |

**Note:** The legacy `src/payments.php` also lacks a dedicated `autoship_delete_trustcommerce_payment_method()` function, but the hook `autoship_delete_TrustCommerce_payment_method_qpilot_match` IS referenced in the payment method deletion flow. The compatibility layer should register it for consistency and correctness.

**Recommended Fix:**
```php
// In TrustCommercePaymentCompatibility::register_filter_hooks():
add_filter(
    'autoship_delete_TrustCommerce_payment_method_qpilot_match',
    array( $integration, 'delete_payment_method' ),
    10,
    4
);
```

---

### 6. Checkout.com — NEEDS FIX (2 Issues)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/CheckoutPaymentIntegration.php`
- `app/Modules/Payments/Compatibility/CheckoutPaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/CheckoutPaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/CheckoutPaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway ID | PASS | `wc_checkout_com_cards` matches legacy |
| `build()` | NOTE | Both sandbox and live branches set the same API keys — see issue CO-2 |
| `is_valid()` | NOTE | Returns `false` — appears intentional (disabled by design) |
| `get_order_payment_data()` | PASS | Reads `_cko_payment_id`, calls `autoship_get_checkout_com_charge_by_transaction_id()` |
| `add_payment_method()` | PASS | Sets `gatewayCustomerId = null` matches legacy |
| `delete_payment_method()` | PASS | Matches on `gatewayPaymentId === token` |
| `add_metadata()` | PASS | Sets `cko_payment_authorized`, `cko_payment_captured` fields correctly |
| Compatibility hooks | PASS | Both add and delete filter hooks registered; action hook for metadata registered |
| Factory registration | PASS | `wc_checkout_com_cards` case present |
| Test coverage | PARTIAL | Only covers `build()`, `is_valid()`, `initialize()` |

**Gaps:**

| # | Severity | Description | Location |
|---|----------|-------------|----------|
| CO-1 | HIGH | **Missing tests for core methods.** No tests for `get_order_payment_data()`, `add_payment_method()`, `delete_payment_method()`, or `add_metadata()`. These are the critical integration methods. | `CheckoutPaymentIntegrationTest.php` |
| CO-2 | MEDIUM | **Redundant `build()` logic.** Both test and live branches set API keys from the same settings keys (`ckocom_pk`, `ckocom_sk`). The Checkout.com plugin may use the same keys with environment-based routing, but this should be verified. If Checkout.com uses different keys per environment, the live branch should reference different settings. | `CheckoutPaymentIntegration.php` lines 59-69 |

---

### 7. Braintree — NEEDS FIX (3 Issues)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/BraintreePaymentIntegration.php`
- `app/Modules/Payments/Compatibility/BraintreePaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/BraintreePaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/BraintreePaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway variants | PASS | Both `braintree_credit_card` and `braintree_paypal` supported |
| `build()` | PASS | Correct environment detection, API credentials, PaymentMethodType::BRAINTREE |
| `is_valid()` | PASS | Validates correctly |
| `get_order_payment_data()` | PASS | All metadata fields match legacy for both CC and PayPal variants |
| `add_payment_method()` | PASS | Delegates to variant-specific methods correctly |
| Skyverge integration | PASS | `add_skyverge_payment_method()`, `add_my_account_skyverge_payment_method()` present |
| Factory registration | PASS | Both `braintree_credit_card` and `braintree_paypal` registered |

**Gaps:**

| # | Severity | Description | Location |
|---|----------|-------------|----------|
| BT-1 | **CRITICAL** | **`delete_payment_method()` hook signature mismatch.** The compatibility layer registers `delete_payment_method` on Skyverge action hooks (`wc_payment_gateway_braintree_credit_card_payment_method_deleted`, `wc_payment_gateway_braintree_paypal_payment_method_deleted`) which pass 2 parameters `($token_id, $user_id)`. However, the method signature is `delete_payment_method(bool $valid, string $type, WC_Payment_Token $token, object $method): bool` which expects 4 parameters of different types. **This will cause runtime errors or silent failures when users try to delete saved payment methods.** | `BraintreePaymentIntegration.php` lines 271-276; `BraintreePaymentCompatibility.php` lines 133-136 |
| BT-2 | HIGH | **Missing tests for payment method operations.** No tests for: `get_braintree_credit_card_order_payment_data()`, `get_braintree_paypal_order_payment_data()`, `add_credit_card_payment_method_data()`, `add_paypal_payment_method_data()`, `add_payment_method()`, `delete_payment_method()`, `add_skyverge_payment_method()`, `add_metadata()`. | `BraintreePaymentIntegrationTest.php` |
| BT-3 | MEDIUM | **PayPal variant `payment_method_added` hook not registered.** The compatibility layer registers `wc_payment_gateway_braintree_credit_card_payment_method_added` for the after-save notice but has no equivalent hook for `wc_payment_gateway_braintree_paypal_payment_method_added`. The legacy code also only handles credit card, so this is consistent but may be an oversight. | `BraintreePaymentCompatibility.php` |

**Recommended Fix for BT-1:**

The Skyverge deletion hooks and the filter deletion hooks have different signatures. The integration needs **separate methods** for:
1. Skyverge action hooks: `handle_skyverge_deletion($token_id, $user_id)` — called by `wc_payment_gateway_braintree_*_payment_method_deleted`
2. Filter hooks: `delete_payment_method($valid, $type, $token, $method)` — called by `autoship_delete_Braintree_payment_method_qpilot_match`

Or alternatively, create an adapter method that bridges the Skyverge signature to the internal deletion logic.

---

### 8. CyberSource V2 — NEEDS FIXES (4 Issues)

**Files reviewed:**
- `app/Domain/PaymentIntegrations/CyberSourceV2PaymentIntegration.php`
- `app/Modules/Payments/Compatibility/CyberSourceV2PaymentCompatibility.php`
- `tests/Domain/PaymentIntegrations/CyberSourceV2PaymentIntegrationTest.php`
- `tests/Modules/Payments/Compatibility/CyberSourceV2PaymentCompatibilityTest.php`

**Findings:**

| Aspect | Status | Notes |
|--------|--------|-------|
| Gateway ID | PASS | `cybersource_credit_card` for V2 variant |
| `build()` | PASS | Correct Skyverge environment detection |
| `get_order_payment_data()` | PASS | All metadata fields match, YY-MM to MMYY conversion correct |
| Skyverge integration | PASS | `add_skyverge_payment_method()`, `add_my_account_skyverge_payment_method()` present |
| Factory registration | PASS | Both `cybersource_credit_card` (V2) and `cybersource` (V1 stub) registered |

**Gaps:**

| # | Severity | Description | Location |
|---|----------|-------------|----------|
| CS-1 | **CRITICAL** | **Standard CyberSource (V1) not implemented.** The legacy code has TWO distinct CyberSource implementations: `cybersource_credit_card` (Skyverge/V2, type `CybersourceV2`) and `cybersource` (standard/token table, type `CyberSource`). The V1 `CyberSourcePaymentIntegration` exists but is a stub with `is_valid() → false` and no implemented methods for `get_order_payment_data()`, `add_payment_method()`, or `delete_payment_method()`. The legacy V1 uses different metadata (`cybersource_token_id` instead of `_wc_cybersource_credit_card_payment_token`) and different behavior (`gatewayCustomerId = null`). | `CyberSourcePaymentIntegration.php` |
| CS-2 | HIGH | **`add_payment_method()` registered for both CyberSource and CyberSourceV2 filter hooks with same implementation.** The compatibility layer hooks `autoship_add_CyberSource_payment_method` AND `autoship_add_CyberSourceV2_payment_method` to the same `add_payment_method()` method. But the legacy code has different behavior: Standard CyberSource sets `gatewayCustomerId = null` while V2 retrieves it from user meta `wc_cybersource_customer_id`. The new code always retrieves from user meta, which is **incorrect for standard CyberSource**. | `CyberSourceV2PaymentCompatibility.php` lines 82-83 |
| CS-3 | MEDIUM | **Incomplete filter hook removal.** The legacy function `autoship_add_CyberSource_payment_method` (line 4138 of `src/payments.php`) is not removed by the compatibility layer. This means both the legacy and new handlers could fire on the same filter, causing duplicate or conflicting behavior. | `CyberSourceV2PaymentCompatibility.php` → `register_filter_hooks()` |
| CS-4 | MEDIUM | **Missing tests for core methods.** No tests for `get_order_payment_data()`, `add_payment_method()`, `delete_payment_method()`, or `add_metadata()`. | `CyberSourceV2PaymentIntegrationTest.php` |

**Recommended Fixes:**

1. **CS-1:** Implement `CyberSourcePaymentIntegration` properly with:
   - `get_order_payment_data()` reading `cybersource_token_id` order meta
   - `add_payment_method()` setting `gatewayCustomerId = null`
   - `delete_payment_method()` matching on `gatewayPaymentId === token`
   - `is_valid()` returning `true` when properly configured

2. **CS-2:** Either:
   - Remove the `autoship_add_CyberSource_payment_method` hook from V2 compatibility (let V1 handle it), OR
   - Add conditional logic in `add_payment_method()` to check the type and set `gatewayCustomerId = null` for standard CyberSource

3. **CS-3:** Add `remove_filter('autoship_add_CyberSource_payment_method', 'autoship_add_cybersource_payment_method', 10)` to the cleanup section if V2 is handling both.

---

## Cross-Cutting Concerns

### Compatibility Manager Registration

All 8 gateways are properly registered in `PaymentsCompatibilityManager::$available_compatibility_classes`:

| Gateway ID | Compatibility Class | Status |
|-----------|-------------------|--------|
| `braintree_credit_card` | BraintreePaymentCompatibility | PASS |
| `braintree_paypal` | BraintreePaymentCompatibility | PASS |
| `cybersource_credit_card` | CyberSourceV2PaymentCompatibility | PASS |
| `nmi_gateway_woocommerce_credit_card` | NmiPaymentCompatibility | PASS |
| `nmi` | NmiPaymentCompatibility | PASS |
| `sagepaymentsusaapi` | PayaV1PaymentCompatibility | PASS |
| `trustcommerce` | TrustCommercePaymentCompatibility | PASS |
| `sagepaydirect` | SagePaymentCompatibility | PASS |
| `wc_checkout_com_cards` | CheckoutPaymentCompatibility | PASS |
| `airwallex_card` | AirwallexPaymentCompatibility | PASS |

**Note:** Missing `cybersource` (standard/V1) — the V1 variant has no compatibility class registered.

### Feature Manager Gateway Mappings

All gateways are mapped in `WordPressFeatureManager::$gateway_feature_map`:

| Gateway ID | Feature Key | Status |
|-----------|-------------|--------|
| `braintree_credit_card` | `braintree` | PASS |
| `braintree_paypal` | `braintree` | PASS |
| `cybersource_credit_card` | `cybersource` | PASS |
| `nmi_gateway_woocommerce_credit_card` | `nmi` | PASS |
| `nmi` | `nmi` | PASS |
| `trustcommerce` | `trustcommerce` | PASS |
| `sagepaymentsusaapi` | `sage` | PASS |
| `sagepaydirect` | `sage` | PASS |
| `wc_checkout_com_cards` | `checkoutcom` | PASS |
| `airwallex_card` | `airwallex` | PASS |

### Factory Registration

All gateways are registered in `PaymentIntegrationFactory::create()`:

| Case | Target Class | Status |
|------|-------------|--------|
| `braintree_credit_card` | BraintreePaymentIntegration | PASS |
| `braintree_paypal` | BraintreePaymentIntegration | PASS |
| `cybersource_credit_card` | CyberSourceV2PaymentIntegration | PASS |
| `cybersource` | CyberSourcePaymentIntegration (stub) | NEEDS IMPL |
| `nmi_gateway_woocommerce_credit_card` | NmiPaymentIntegration | PASS |
| `nmi` | NmiPaymentIntegration | PASS |
| `sagepaymentsusaapi` | PayaV1PaymentIntegration | PASS |
| `trustcommerce` | TrustCommercePaymentIntegration | PASS |
| `sagepaydirect` | SagePaymentIntegration | PASS |
| `wc_checkout_com_cards` | CheckoutPaymentIntegration | PASS |
| `airwallex_card` | AirwallexPaymentIntegration | PASS |

---

## Test Coverage Summary

| Gateway | Integration Tests | Compatibility Tests | Core Method Tests | Coverage Rating |
|---------|:-----------------:|:-------------------:|:-----------------:|:---------------:|
| NMI | 10 tests | Comprehensive | Partial | GOOD |
| PayaV1 | 9 tests | Comprehensive (36+) | Partial | GOOD |
| TrustCommerce | 17 tests | Comprehensive (30+) | Partial | GOOD |
| Sage | 4 tests | Comprehensive | Partial | GOOD |
| Airwallex | 11 tests | Comprehensive (50+) | None | PARTIAL |
| Checkout | 4 tests | Comprehensive | None | PARTIAL |
| Braintree | 4 tests | Comprehensive | None | PARTIAL |
| CyberSourceV2 | 4 tests | Comprehensive | None | PARTIAL |

**Note:** "Core Method Tests" refers to tests for `get_order_payment_data()`, `add_payment_method()`, `delete_payment_method()`, and `add_metadata()`. These are the most important methods for verifying parity with legacy behavior. All gateways have comprehensive compatibility layer tests but several lack integration-level method tests.

---

## Priority Action Items

### Critical (Must Fix Before QA)

1. **BT-1: Braintree `delete_payment_method` hook signature mismatch**
   - Skyverge action hooks pass `($token_id, $user_id)` but method expects `($valid, $type, $token, $method)`
   - Will cause runtime errors on payment method deletion
   - Fix: Create separate handler methods for Skyverge actions vs. filter hooks

2. **CS-1: Standard CyberSource (V1) not implemented**
   - Legacy code has a fully functional V1 implementation using different metadata fields
   - The stub `CyberSourcePaymentIntegration` needs full implementation
   - Fix: Implement all abstract methods using `cybersource_token_id` metadata

### High Priority (Should Fix Before QA)

3. **CS-2: CyberSource `add_payment_method` wrong behavior for V1**
   - V2 compatibility handles both CyberSource and CyberSourceV2 filters with the same method
   - Standard CyberSource should set `gatewayCustomerId = null` but current code retrieves from user meta
   - Fix: Separate handling or conditional logic

4. **TC-1: TrustCommerce missing delete hook registration**
   - `delete_payment_method()` is implemented but never wired to the hook system
   - Fix: Register `autoship_delete_TrustCommerce_payment_method_qpilot_match` filter

5. **BT-2, CS-4, CO-1: Missing core method tests**
   - Braintree, CyberSourceV2, and Checkout lack tests for critical integration methods
   - Fix: Add tests for `get_order_payment_data()`, `add_payment_method()`, `delete_payment_method()`

### Medium Priority (Should Fix Before Release)

6. **CS-3: Incomplete filter hook removal for CyberSource**
   - Legacy function not properly removed, could cause dual execution
   - Fix: Add missing `remove_filter()` call

7. **CO-2: Checkout.com redundant build logic**
   - Verify whether sandbox/live environments use different API key settings
   - Fix: Clarify or deduplicate the credential mapping

8. **BT-3: Braintree PayPal `payment_method_added` hook not registered**
   - Only credit card variant has the after-save notice hook
   - Fix: Verify if PayPal variant also needs this hook

### Low Priority (Recommended)

9. **A-1: Airwallex missing core method tests**
   - Implementation is correct but lacks test coverage for critical methods
   - Fix: Add unit tests, especially for the fallback workaround scenario

---

## Field Mapping Verification Matrix

This matrix verifies every gateway's field mappings against the legacy `src/payments.php`:

### Order Metadata Keys

| Gateway | Legacy Meta Key | New Implementation | Match |
|---------|----------------|-------------------|:-----:|
| Braintree CC | `_wc_braintree_credit_card_payment_token` | Same | PASS |
| Braintree CC | `_wc_braintree_credit_card_customer_id` | Same | PASS |
| Braintree CC | `_wc_braintree_credit_card_card_type` | Same | PASS |
| Braintree CC | `_wc_braintree_credit_card_account_four` | Same | PASS |
| Braintree CC | `_wc_braintree_credit_card_card_expiry_date` | Same | PASS |
| Braintree PayPal | `_wc_braintree_paypal_payment_token` | Same | PASS |
| Braintree PayPal | `_wc_braintree_paypal_customer_id` | Same | PASS |
| CyberSourceV2 | `_wc_cybersource_credit_card_payment_token` | Same | PASS |
| CyberSourceV2 | `_wc_cybersource_credit_card_customer_id` | Same | PASS |
| CyberSourceV2 | `_wc_cybersource_credit_card_card_expiry_date` | Same (YY-MM) | PASS |
| CyberSource V1 | `cybersource_token_id` | NOT IMPLEMENTED | FAIL |
| NMI WC | `_wc_nmi_gateway_woocommerce_credit_card_payment_token` | Same | PASS |
| NMI Enterprise | `_nmi_card_id` | Same | PASS |
| PayaV1 | `_SageToken` | Same | PASS |
| TrustCommerce | `_trustcommerce_customer_id` | Same | PASS |
| Sage | `_SagePayDirectToken` | Same | PASS |
| Checkout | `_cko_payment_id` | Same | PASS |
| Airwallex | `airwallex_consent_id` | Same | PASS |
| Airwallex | `airwallex_customer_id` | Same | PASS |

### QPilot Type Names

| Gateway | Legacy Type | New Type | Match |
|---------|-----------|---------|:-----:|
| Braintree | `Braintree` | `Braintree` | PASS |
| CyberSourceV2 | `CybersourceV2` | `CybersourceV2` | PASS |
| CyberSource V1 | `CyberSource` | NOT IMPL | FAIL |
| NMI | `Nmi` | `Nmi` | PASS |
| PayaV1 | `PayaV1` | `PayaV1` | PASS |
| TrustCommerce | `TrustCommerce` | `TrustCommerce` | PASS |
| Sage | `Sage` | `Sage` | PASS |
| Checkout | `Checkout` | `Checkout` | PASS |
| Airwallex | `Airwallex` | `Airwallex` | PASS |

### Gateway Customer ID / Payment ID Patterns

| Gateway | Customer ID Source | Payment ID Source | Match |
|---------|-------------------|------------------|:-----:|
| Braintree CC | From order meta | Token | PASS |
| Braintree PayPal | From order meta | Token (last 4) | PASS |
| CyberSourceV2 | From user meta | Token | PASS |
| CyberSource V1 | `null` | Token | NOT IMPL |
| NMI | Token (swapped) | `null` (unset) | PASS |
| PayaV1 | Token (swapped) | `null` | PASS |
| TrustCommerce | Token (swapped) | `null` | PASS |
| Sage | `null` | Token | PASS |
| Checkout | `null` | Token | PASS |
| Airwallex | From helper function | consent_id | PASS |

---

## Conclusion

The migration effort is substantially complete. **4 out of 8 gateways (NMI, PayaV1, Sage, Airwallex) are ready or nearly ready for QA validation.** The remaining 4 gateways (TrustCommerce, Checkout, Braintree, CyberSourceV2) require fixes ranging from a simple hook registration (TrustCommerce) to a critical signature mismatch (Braintree) and a missing implementation (CyberSource V1).

The most urgent fix is the **Braintree `delete_payment_method` signature mismatch (BT-1)** as it will cause runtime failures in production. The **CyberSource V1 stub (CS-1)** is the largest piece of missing work but may be deferred if V1 is being deprecated.
