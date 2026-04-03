# Payment Processing Refactor - Project Documentation

> **Project Status**: ACTIVE
> **Target Release**: 2.12.0
> **First Gateway**: Stripe
> **Last Updated**: January 2026

---

## Executive Summary

The `payments.php` refactor extracts 4,350 lines of procedural legacy code into testable, modern services. This project addresses the second-largest god file in the codebase and resolves critical PCI compliance concerns.

**Why Now:**
- Modern foundation already exists (`app/Domain/PaymentIntegrations/` at 81.59% coverage)
- Payment processing handles real money - highest business risk
- 79 PHPCS security warnings suppressed in legacy code
- Clear extraction patterns proven with QuickLinks module

---

## Project Documents

| Document | Description |
|----------|-------------|
| [README.md](README.md) | This file - project overview and strategy |
| [Migration-Components-Breakdown.md](Migration-Components-Breakdown.md) | Detailed component analysis with time estimates |
| [Gateway-Migration-Estimates.md](Gateway-Migration-Estimates.md) | Per-gateway migration estimates and task lists |
| [Stripe-Migration-Plan.md](Stripe-Migration-Plan.md) | **NEXT RELEASE** - Stripe-specific implementation plan |

---

## Current State (January 2026)

### Legacy Code (`src/payments.php`)

| Metric | Value |
|--------|-------|
| Lines of Code | 4,350 |
| Functions | 135 |
| PHPCS Ignores | 79 |
| Direct $_POST | 7 |
| Test Coverage | 0% |

### Modern Foundation (`app/Domain/PaymentIntegrations/`)

| Metric | Value |
|--------|-------|
| Line Coverage | 81.59% (257/315 lines) |
| Function Coverage | 94.44% (34/36) |
| Class Coverage | 80.00% (8/10) |
| Interfaces | 2 |

### Gateway Integration Status

| Gateway | Integration Class | Coverage | Compatibility | Status |
|---------|------------------|----------|---------------|--------|
| Stripe | StripePaymentIntegration | 100% | Exists | **NEXT** |
| AuthorizeNet | AuthorizeNetPaymentIntegration | 100% | Exists | Ready |
| Braintree | BraintreePaymentIntegration | 100% | Exists | Ready |
| PayPal | PayPalPaymentIntegration | 100% | Missing | Needs Work |
| Square | SquarePaymentIntegration | 100% | Exists | Ready |
| NMI | NmiPaymentIntegration | 100% | Missing | Needs Work |
| CyberSource | CyberSourcePaymentIntegration | 100% | Missing | Needs Work |
| Paya | PayaPaymentIntegration | 100% | Missing | Needs Work |
| TrustCommerce | TrustCommercePaymentIntegration | 100% | Missing | Needs Work |
| Checkout.com | CheckoutPaymentIntegration | 100% | Missing | Needs Work |
| Airwallex | AirwallexPaymentIntegration | 100% | Missing | Needs Work |
| FunnelKit | FunnelKitPaymentIntegration | Partial | Missing | Needs Work |
| Sage | SagePaymentIntegration | 100% | Missing | Needs Work |

---

## Migration Strategy

### Phase 1: Stripe (Release 2.12.0) - NEXT

**Timeline**: 3-4 development days

| Component | Status | Est. Hours |
|-----------|--------|------------|
| Foundation Layer | EXISTS | 1-2 |
| Module Infrastructure | NEEDS CREATION | 3-4 |
| Compatibility Layer | EXISTS | 2-3 |
| Stripe Integration | EXISTS | 3-4 |
| Legacy Routing | NEEDS WORK | 2-3 |
| Testing | NEEDS WORK | 5-7 |
| **Total** | | **16-23 hours** |

**Deliverables:**
1. `app/Modules/Payments/` module structure
2. `PaymentGatewayRegistry` service
3. `PaymentGatewayService` service
4. `StripePaymentCompatibility` hooks active
5. Feature flag: `AUTOSHIP_GATEWAY_STRIPE_ENABLED`
6. 80%+ test coverage on new code
7. Zero breaking changes to existing behavior

### Phase 2: High-Priority Gateways (Release 2.13.0)

| Gateway | Est. Hours | Notes |
|---------|------------|-------|
| Square | 3-4 | Most complete integration |
| AuthorizeNet | 3-4 | Complete, low risk |
| **Subtotal** | **6-8 hours** | |

### Phase 3: Remaining Gateways (Release 2.14.0+)

| Gateway | Est. Hours | Notes |
|---------|------------|-------|
| Braintree | 6-8 | Dual payment types |
| PayPal | 5-7 | Create compatibility class |
| NMI | 3-4 | Create compatibility class |
| CyberSource | 3-4 | Create compatibility class |
| Other Gateways | 2-3 each | ~12 hours total |

### Phase 4: Legacy Deprecation (Release 2.15.0+)

1. Add `_doing_it_wrong()` notices to legacy functions
2. Log legacy function calls
3. Announce deprecation in release notes
4. Remove legacy function bodies (major version)

---

## Architecture

### Target Structure

```
app/
├── Domain/
│   ├── PaymentIntegration.php           # Base class (EXISTS)
│   ├── PaymentMethodType.php            # Type constants (EXISTS)
│   ├── PaymentIntegrationFactory.php    # Factory (EXISTS)
│   └── PaymentIntegrations/
│       ├── StripePaymentIntegration.php     # (EXISTS - 100%)
│       ├── AuthorizeNetPaymentIntegration.php
│       ├── BraintreePaymentIntegration.php
│       └── [other gateways]
│
└── Modules/
    └── Payments/                         # NEW MODULE
        ├── PaymentsModule.php            # Module entry point
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

### Service Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                     Legacy Code (src/payments.php)               │
│  autoship_get_order_payment_data($order_id)                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Feature Flag Check                            │
│  if (FeatureManager::is_payment_gateway_enabled('stripe'))      │
└─────────────────────────────────────────────────────────────────┘
                    │                              │
            (enabled)                        (disabled)
                    ▼                              ▼
┌───────────────────────────────┐  ┌──────────────────────────────┐
│     PaymentGatewayRegistry    │  │      Legacy Function         │
│     $registry->get('stripe')  │  │  apply_filters(...)          │
└───────────────────────────────┘  └──────────────────────────────┘
                    │
                    ▼
┌───────────────────────────────┐
│  StripePaymentIntegration     │
│  ->get_order_payment_data()   │
└───────────────────────────────┘
```

---

## Feature Flags

```php
// Enable new Stripe implementation
define('AUTOSHIP_GATEWAY_STRIPE_ENABLED', true);

// Enable for all gateways (future)
define('AUTOSHIP_MODERN_PAYMENTS_ENABLED', true);

// Or via FeatureManager (preferred)
FeatureManager::enable('payment_gateway_stripe');
FeatureManager::enable('payment_gateway_square');
```

---

## Testing Strategy

### Unit Tests Required

| Test Class | Coverage Target |
|------------|-----------------|
| PaymentsModuleTest | 80% |
| PaymentGatewayRegistryTest | 90% |
| PaymentGatewayServiceTest | 80% |
| StripePaymentCompatibilityTest | 80% |
| PaymentsCompatibilityManagerTest | 80% |

### Integration Tests Required

1. Stripe token creation via WC checkout
2. Payment method sync to QPilot
3. Payment method deletion cascade
4. Feature flag toggle behavior
5. Legacy fallback verification

### Manual Testing Checklist

See [Migration-Components-Breakdown.md](Migration-Components-Breakdown.md#6-testing-for-stripe) for detailed manual testing procedures.

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Payment data loss | CRITICAL | Feature flags, extensive testing |
| Token sync failure | HIGH | Compatibility layer, legacy fallback |
| QPilot API errors | HIGH | Error handling, retry logic |
| Gateway plugin conflicts | MEDIUM | Test with specific WC plugin versions |
| Performance regression | MEDIUM | Benchmark before/after |

---

## Success Criteria

### Phase 1 (Stripe) Complete When:

- [ ] All Stripe unit tests pass (80%+ coverage)
- [ ] Manual testing checklist complete
- [ ] Feature flag enables/disables correctly
- [ ] Legacy code works when flag disabled
- [ ] No PHPCS ignores in new code
- [ ] QPilot payment sync verified
- [ ] Zero production incidents after 2-week canary

---

## Related Documents

- [Assessment: 01-executive-summary.md](../assessments/01-executive-summary.md)
- [Assessment: 03-feature-analysis.md](../assessments/03-feature-analysis.md)
- [Assessment: 04-technical-debt.md](../assessments/04-technical-debt.md)
- [Assessment: 06-testing-strategy.md](../assessments/06-testing-strategy.md)

---

*Last Updated: January 2026*
*Project Owner: Technical Architecture*
