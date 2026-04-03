# Technical Debt Inventory

> **Purpose**: Comprehensive catalog of all technical debt in the codebase, prioritized by impact on daily deployment capability and team scaling.
> **Updated**: January 2026

---

## Debt Classification

| Severity | Definition | Impact |
|----------|------------|--------|
| CRITICAL | Blocks daily deployment, active security risk | Must fix before any safe changes |
| HIGH | Blocks team scaling, major maintenance burden | Fix within 30 days |
| MEDIUM | Significant friction, accumulating cost | Fix within 90 days |
| LOW | Minor friction, cleanup opportunity | Fix opportunistically |

---

## Progress Since December 2025

| Debt Item | Dec 2025 Status | Jan 2026 Status | Change |
|-----------|-----------------|----------------|--------|
| DEBT-002: Coverage Reporting | BROKEN | **FIXED** | RESOLVED |
| DEBT-001: Legacy Coverage | 0% | 0% | UNCHANGED |
| DEBT-003: PHPCS Ignores | 659 | 610 | -49 |
| DEBT-004: God Files | 5 | 5 | UNCHANGED |
| DEBT-005: Superglobal Access | 209 | 146 | -63 |
| DEBT-007: Two QPilot Clients | Both active | Modern dominant | IMPROVING |

---

## Critical Debt (Deployment Blockers)

### DEBT-001: Zero Test Coverage on Legacy Code

**Location**: All `src/*.php` files
**Severity**: CRITICAL
**Effort**: HIGH (ongoing)
**Status**: UNCHANGED

**Evidence**:
```
Files:           18
Lines of Code:   ~36,219
Functions:       957
Test Files:      0
Coverage:        0%
```

**Top Files by Size**:
| File | Lines | Functions | PHPCS Ignores |
|------|-------|-----------|---------------|
| scheduled-orders.php | 8,162 | 197 | 186 |
| payments.php | 4,350 | 135 | 79 |
| products.php | 3,846 | 109 | 65 |
| admin.php | 2,146 | 95 | 32 |
| product-page.php | 2,248 | 35 | 20 |

**Impact**:
- Cannot safely modify any legacy code
- No regression detection
- Changes require manual testing of entire system
- Blocks daily deployment completely

**Resolution Path**:
1. Extract testable services from payments.php (NEXT PROJECT)
2. Add integration tests for critical paths
3. Gradually increase unit test coverage
4. Apply same pattern to other god files

---

### ~~DEBT-002: Broken Test Coverage Reporting~~ - RESOLVED

**Location**: `tests/phpunit.xml`, coverage configuration
**Severity**: ~~CRITICAL~~ RESOLVED
**Status**: **FIXED** - January 2026

**Evidence**:
- Coverage reports now generate correctly
- HTML reports available in `build/coverage/`
- Measured coverage: 73.65% for app/

**Resolution Applied**:
- PHPUnit configuration corrected
- PCOV enabled for coverage
- `composer coverage` works

---

### DEBT-003: PHPCS Ignores (Security Warnings Suppressed)

**Location**: All `src/*.php` files
**Severity**: CRITICAL
**Effort**: HIGH
**Status**: SLIGHTLY IMPROVED (-49 ignores)

**Evidence (January 2026)**:
```
Total PHPCS Ignores:        610 (was 659)
By File:
├── scheduled-orders.php:   186
├── payments.php:           79
├── products.php:           65
├── admin.php:              32
├── orders.php:             29
├── import.php:             28
├── wholesale-pricing.php:  26
├── bulk.php:               22
├── product-page.php:       20
└── Other files:            123
```

**Impact**:
- Hidden security vulnerabilities
- False sense of compliance
- Audit failures

**Resolution**:
1. Audit each ignore for severity
2. Create fix priority list
3. Systematically address during payments.php refactor
4. Apply same rigor to scheduled-orders.php

---

### DEBT-004: God Files

**Location**: 5 files with >2000 LOC
**Severity**: CRITICAL
**Effort**: VERY HIGH
**Status**: UNCHANGED

**Evidence**:
| File | Lines | Functions | Why It's a Problem |
|------|-------|-----------|-------------------|
| scheduled-orders.php | 8,162 | 197 | Cannot comprehend, modify, or test |
| payments.php | 4,350 | 135 | Handles money, untestable |
| products.php | 3,846 | 109 | Core feature, untestable |
| product-page.php | 2,248 | 35 | Product UI, untestable |
| orders.php | 2,203 | 56 | Order handling, untestable |
| admin.php | 2,146 | 95 | All admin logic in one place |

**Impact**:
- Cannot safely modify
- Cannot parallelize development
- Cannot test
- Cognitive overload for developers

**Resolution**:
1. Start with payments.php (NEXT PROJECT)
2. Map function dependencies
3. Identify extraction boundaries
4. Create new services in `app/`
5. Apply pattern to other files

---

### DEBT-005: Direct Superglobal Access

**Location**: Primarily `src/*.php`
**Severity**: CRITICAL
**Effort**: MEDIUM
**Status**: IMPROVED (-63 instances)

**Evidence (January 2026)**:
```
Total instances:          146 (was 209)
By File:
├── scheduled-orders.php: 63
├── bulk.php:             18
├── cart.php:             9
├── orders.php:           7
├── payments.php:         7
├── ajax.php:             7
└── Other files:          35
```

**Impact**:
- Security vulnerabilities (XSS, injection)
- Cannot test (no way to mock input)
- PHPCS compliance failures

**Resolution**:
1. Create input abstraction layer
2. Replace direct access with sanitized methods
3. Address during service extraction

---

## High Debt (Team Scaling Blockers)

### DEBT-006: Procedural Functions (No DI)

**Location**: All `src/*.php`
**Severity**: HIGH
**Effort**: VERY HIGH
**Status**: UNCHANGED

**Evidence**:
```
Functions with autoship_ prefix:  957
Functions with interfaces:        0
Functions with DI:                0
```

**Impact**:
- Cannot unit test (no way to mock dependencies)
- Cannot modify safely
- Functions form hidden dependency web

**Resolution**:
1. Create interfaces during service extraction
2. Build facade classes that wrap legacy functions
3. Test facades, gradually extract logic

---

### DEBT-007: Two QPilot API Clients

**Location**: `src/QPilot/Client.php`, `app/Services/QPilot/`
**Severity**: HIGH
**Effort**: MEDIUM
**Status**: IMPROVING

**Evidence**:
- Legacy client used by some src/ files
- Modern client has 97.94% coverage
- Modern client is now dominant

**Impact**:
- Confusion about which to use
- Maintenance burden

**Resolution**:
1. Audit remaining legacy client usage
2. Add deprecation notices to legacy
3. Migrate remaining callers
4. Remove legacy client

---

### DEBT-008: Scattered WordPress Hooks

**Location**: All `src/*.php`
**Severity**: HIGH
**Effort**: MEDIUM
**Status**: UNCHANGED

**Evidence**:
```
add_action calls:  ~280
add_filter calls:  ~184
Total:             464
Spread across:     26 files
Documentation:     None
```

**Impact**:
- Cannot discover what hooks when
- Hidden dependencies
- Testing impossible

---

### DEBT-009: Module Coverage Gap

**Location**: `app/Modules/`
**Severity**: HIGH
**Effort**: MEDIUM
**Status**: NEW DEBT (code added faster than tests)

**Evidence**:
```
Modules Total:        37.88% (830/2191 lines)
├── Nextime:          88.32% (174/197) - GOOD
├── Quicklaunch:      53.73% (360/670) - NEEDS WORK
├── Synchronizers:    42.71% (126/295) - NEEDS WORK
└── QuickLinks:       16.52% (170/1029) - CRITICAL
```

**Impact**:
- New code not tested
- Regression risk
- Modules are customer-facing

**Resolution**:
1. Prioritize QuickLinks module coverage
2. Add tests during any module changes
3. Block merges without test coverage

---

## Medium Debt (Maintenance Friction)

### DEBT-010: Synchronous API Operations

**Location**: All QPilot integrations
**Severity**: MEDIUM (downgraded - modern client handles better)
**Effort**: HIGH
**Status**: UNCHANGED

**Evidence**:
- Product sync blocks product save
- Order operations block checkout
- No background processing

**Resolution**:
1. Implement Action Scheduler integration
2. Move sync to background jobs
3. Add retry mechanism

---

### DEBT-011: Settings Scattered in wp_options

**Location**: Throughout codebase
**Severity**: MEDIUM
**Effort**: MEDIUM
**Status**: UNCHANGED

**Evidence**:
```
autoship_* options:  ~50+
No schema:           True
No validation:       True
```

---

### DEBT-012: Services/QuickLinks Coverage Gap

**Location**: `app/Services/QuickLinks/`
**Severity**: MEDIUM
**Effort**: MEDIUM
**Status**: NEW

**Evidence**:
```
Services/QuickLinks: 65.98% (1181/1790 lines)
Target:              85%
Gap:                 341 lines
```

**Impact**:
- QuickLinks is customer-facing feature
- Untested code paths

**Resolution**:
1. Increase coverage during feature work
2. Focus on uncovered critical paths

---

### DEBT-013: No Error Tracking Integration

**Location**: N/A (missing)
**Severity**: MEDIUM
**Effort**: LOW
**Status**: UNCHANGED

---

## Debt Burndown Priority (Updated)

### Phase 1: payments.php Refactor (Weeks 1-8)

| Action | Target | Effort |
|--------|--------|--------|
| Extract PaymentMethodService | 200 lines | MEDIUM |
| Extract TokenizationService | 600 lines | HIGH |
| Extract OrderPaymentService | 800 lines | HIGH |
| Migrate gateway handlers | 2,000 lines | HIGH |
| Add tests for each service | 80%+ coverage | MEDIUM |

### Phase 2: Module Coverage (Weeks 9-12)

| Action | Target | Effort |
|--------|--------|--------|
| QuickLinks module tests | 16% → 60%+ | HIGH |
| Synchronizers tests | 42% → 70%+ | MEDIUM |
| Quicklaunch tests | 53% → 70%+ | MEDIUM |

### Phase 3: Services Completion (Weeks 13-16)

| Action | Target | Effort |
|--------|--------|--------|
| QuickLinks services | 65% → 85%+ | MEDIUM |
| Logging services | 72% → 85%+ | LOW |

### Phase 4: Legacy Extraction (Months 5-12)

| Action | Target | Effort |
|--------|--------|--------|
| scheduled-orders.php | Extract services | VERY HIGH |
| products.php | Extract services | HIGH |
| Deprecate legacy QPilot client | Remove | MEDIUM |

---

## Debt Metrics Dashboard (January 2026)

### Track Weekly

```
Code Quality:
├── PHPCS Ignores:          610  → Target: <100
├── God Files (>2000 LOC):  5    → Target: 0
├── Functions without DI:   957  → Target: <200
└── Direct superglobals:    146  → Target: 0

Test Coverage (MEASURED):
├── Legacy (src/):          0%      → Target: 50%
├── Modern (app/):          73.65%  → Target: 85%
├── Modern/Domain:          97.49%  → Maintain
├── Modern/Services:        84.26%  → Target: 85%
├── Modern/Modules:         37.88%  → Target: 80%
└── Modern/Core:            86.68%  → Target: 90%

Architecture:
├── Interfaces:             25+  → Target: 50+
├── Modules:                4    → Target: 10+
└── Legacy files:           18   → Target: <10
```

---

*Last Updated: January 2026*
*Previous Update: December 2025*
*Next Review: Monthly*
