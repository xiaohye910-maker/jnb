# Executive Summary

## What Is This Plugin?

Autoship Cloud is a WordPress/WooCommerce plugin enabling subscription-based product ordering through integration with QPilot (a hosted subscription management service). It handles:

- Subscription product configuration
- Recurring order scheduling
- Payment method management (19+ gateways)
- Customer subscription management
- Real-time shipping calculations

**Business Impact**: This is revenue-critical infrastructure. Every bug affects real money.

---

## Current State: January 2026 Reassessment

### Progress Since December 2025

| Milestone | December 2025 | January 2026 | Status |
|-----------|---------------|--------------|--------|
| Coverage Reports | BROKEN | WORKING | FIXED |
| app/ Coverage | ~60% (estimated) | 73.65% (measured) | +13.65% |
| Domain Layer | ~75% | 97.49% | EXCEEDS TARGET |
| QPilot Services | ~40% | 97.94% | EXCEEDS TARGET |
| Core | ~70% | 86.68% | NEAR TARGET |
| Modules | ~50% | 37.88% | REGRESSED (code added) |

### The Good

| Aspect | Status |
|--------|--------|
| Coverage reporting | WORKING - full HTML reports generated |
| Domain layer | 97.49% coverage - production-grade |
| QPilot Services | 97.94% coverage - production-grade |
| Nextime Services | 99.29% coverage - excellent |
| Modern architecture | `app/` folder with PSR-4, DI, interfaces |
| Payment integrations | 19 gateways with tests in app/ |

### The Bad

| Aspect | Status |
|--------|--------|
| Legacy code | 36,219 lines, completely untested |
| God files | 5 files over 2,000 lines each |
| PHPCS ignores | 610 warnings suppressed |
| Modules coverage | 37.88% - significant gap |
| Deployment | Manual WordPress SVN - error-prone |

### The Ugly

| Aspect | Status |
|--------|--------|
| `scheduled-orders.php` | **8,162 lines**, 197 functions, 186 PHPCS ignores |
| `payments.php` | **4,350 lines**, 135 functions, 79 PHPCS ignores |
| Direct `$_POST` access | 146 instances across 17 files |
| Services/QuickLinks | 65.98% coverage - weak point in modern code |
| Modules/QuickLinks | 16.52% coverage - critical gap |

---

## Key Metrics (January 2026)

```
Codebase Size
├── Legacy (src/):     18 files, ~36,219 lines
├── Modern (app/):     187 files, ~8,190 lines
├── Tests:             66+ files (app/ only)
└── Total:             200+ PHP files

Code Quality
├── PHPCS Ignores:     610 (16.8 per 1000 LOC in legacy)
├── Procedural Funcs:  957 (untestable)
├── Interfaces:        25+ (all in app/)
├── Direct $_POST:     146 (security debt)
└── TODO/HACK/FIXME:   15+ (known broken things)

Test Coverage (MEASURED)
├── app/:              73.65% (6032/8190 lines)
├── src/:              0% (literally zero)
├── Integration:       None
└── E2E:               External (Playwright)

Coverage by Component
├── Domain:            97.49% (933/957 lines) - EXCELLENT
├── Services:          84.26% (3861/4582 lines) - GOOD
├── Core:              86.68% (332/383 lines) - GOOD
├── Common:            98.70% (76/77 lines) - EXCELLENT
└── Modules:           37.88% (830/2191 lines) - POOR
```

---

## Risk Assessment (Updated)

| Area | Risk Level | Reason | Trend |
|------|------------|--------|-------|
| Scheduled Orders | CRITICAL | 8,162 LOC, 0% tested, core functionality | UNCHANGED |
| Payment Processing | CRITICAL | 4,350 LOC, handles money, 79 PHPCS ignores | UNCHANGED |
| Modules/QuickLinks | HIGH | 16.52% coverage, 859 uncovered lines | NEW GAP |
| Services/QuickLinks | MEDIUM | 65.98% coverage, 609 uncovered lines | NEEDS WORK |
| Product Management | HIGH | 3,846 LOC, 0% tested, synchronous | UNCHANGED |
| Order Processing | HIGH | Multi-step with no transactions | UNCHANGED |
| Checkout Flow | HIGH | Customer-facing, untested | UNCHANGED |
| QPilot Integration | LOW | 97.94% coverage, well-tested | IMPROVED |
| Domain Layer | LOW | 97.49% coverage, production-grade | IMPROVED |

---

## What Changed

### Completed Actions
1. Test coverage reporting - NOW WORKING
2. Domain layer coverage - EXCEEDED 90% target (97.49%)
3. QPilot services coverage - EXCEEDED target (97.94%)
4. Core coverage - NEAR target (86.68%)
5. Nextime services - EXCELLENT (99.29%)

### Remaining Gaps
1. Modules/QuickLinks - 16.52% (critical)
2. Modules/Synchronizers - 42.71%
3. Modules/Quicklaunch - 53.73%
4. Services/QuickLinks - 65.98%
5. Services/Logging - 72.73%
6. All src/ files - 0%

---

## Next Major Project: payments.php Refactor

**File Stats:**
- 4,350 lines of code
- 135 functions
- 79 PHPCS ignores
- 7 direct $_POST/$_GET/$_REQUEST accesses
- 0% test coverage

**Why payments.php is the priority:**
1. Handles real money - PCI compliance concerns
2. Second-largest god file after scheduled-orders.php
3. Already has modern `app/Domain/PaymentIntegrations/` foundation (81.59% coverage)
4. Gateway-specific functions are well-documented in code
5. Natural extraction boundaries exist (standard vs non-standard gateways)

**Existing Modern Foundation:**
- `app/Domain/PaymentIntegrations/` - 81.59% coverage (257/315 lines)
- `app/Domain/PaymentMethodType.php` - 100% coverage
- `app/Domain/PaymentIntegrationFactory.php` - 89.19% coverage
- 15 payment gateway integration classes

---

## Investment Priority (Updated)

| Priority | Target | Current State | Business Impact |
|----------|--------|---------------|-----------------|
| 1 | payments.php refactor | 0% tested, 4,350 LOC | CRITICAL - handles money |
| 2 | Modules/QuickLinks | 16.52% | HIGH - customer-facing |
| 3 | Services/QuickLinks | 65.98% | HIGH - feature completion |
| 4 | scheduled-orders.php extraction | 0% tested, 8,162 LOC | CRITICAL - core feature |

---

## The Bottom Line (Updated)

**Progress has been made.** Coverage reporting works. The Domain and QPilot Services layers are now production-grade (97%+). The Core layer is near-target (86.68%).

**But critical debt remains:**
- `src/` directory is still 100% untested (36,219 lines)
- `payments.php` handles money with 79 security warnings suppressed
- `scheduled-orders.php` is an 8,162-line monster
- Modules directory regressed to 37.88% due to code additions

**The payments.php refactor is the correct next step:**
- Modern foundation already exists in app/Domain/PaymentIntegrations/
- Clear extraction patterns from QuickLinks module
- Addresses PCI compliance concerns
- Reduces the second-largest god file

**Updated Overall Score: 4.2/10** (up from 3.5/10)
- Improved due to working coverage, domain/services quality
- Still below acceptable due to legacy debt

---

*Assessment Date: January 2026*
*Previous Assessment: December 2025*
*Assessor: Technical Architecture Review*
