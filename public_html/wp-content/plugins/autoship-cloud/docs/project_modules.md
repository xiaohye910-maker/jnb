# Autoship Cloud - Project Modules

> **Note**: This document has been superseded by the comprehensive assessment in `/docs/assessments/`.
>
> See the [Assessment Documentation](./assessments/README.md) for:
> - [Executive Summary](./assessments/01-executive-summary.md)
> - [Architecture Overview](./assessments/02-architecture-overview.md)
> - [Feature Analysis](./assessments/03-feature-analysis.md)
> - [Technical Debt](./assessments/04-technical-debt.md)
> - [Security Assessment](./assessments/05-security-assessment.md)
> - [Testing Strategy](./assessments/06-testing-strategy.md)
> - [Observability Plan](./assessments/07-observability-plan.md)
> - [Deployment Reality](./assessments/08-deployment-reality.md)
> - [Modernization Roadmap](./assessments/09-modernization-roadmap.md)
> - [Immediate Actions](./assessments/10-immediate-actions.md)

---

# Critical Architecture Assessment (Legacy)

> **Purpose**: This document provides a brutally honest assessment of the codebase to inform the modernization roadmap, enable team scalability, and establish the foundation for TDD and daily deployments.

---

## Executive Summary: Current State

| Metric | Value | Assessment |
|--------|-------|------------|
| Total PHP Files | 218 | Split across two architectures |
| Legacy Code (src/) | 31 files, ~37,000 lines | **UNTESTED, UNTESTABLE** |
| Modern Code (app/) | 187 files | Partially tested |
| Test Files | 66 | Only cover app/, zero for src/ |
| Test Coverage | **BROKEN** | Cannot generate reports |
| Legacy Functions | 957 | All procedural, no DI |
| PHPCS Ignores | 659 | Warnings suppressed, not fixed |
| Direct Superglobal Access | 209 | Security debt |
| God Files (>2000 LOC) | 5 | Maintenance nightmare |

### Critical Verdict

**This codebase is NOT enterprise-ready.** The legacy layer (`src/`) represents ~70% of the business logic but has:
- Zero test coverage
- No dependency injection
- No interfaces
- Procedural code that cannot be unit tested
- Security patterns bypassed with PHPCS ignore comments

---

## Feature-Based Analysis

### 1. Subscription Product Management

**Files**: `src/products.php` (3,841 LOC), `src/product-page.php` (2,243 LOC)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Testability | IMPOSSIBLE | 113 procedural functions, no DI |
| Coupling | EXTREME | Direct calls to 6+ other src/ files |
| PHPCS Ignores | 64 | Input validation bypassed |

**Critical Issues**:
- Functions directly access `$_POST`, `$_GET` without proper sanitization
- No separation between data access, business logic, and presentation
- Product sync logic is synchronous and blocks UI
- `autoship_` prefix is the only "namespace" - no autoloading
- Changes here require manual regression testing of 113 functions

**Risk Level**: HIGH - Core revenue functionality with zero safety net

---

### 2. Scheduled Orders Management

**Files**: `src/scheduled-orders.php` (8,162 LOC - **LARGEST FILE**)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Testability | IMPOSSIBLE | 205 procedural functions |
| PHPCS Ignores | 186 | Highest in codebase |
| Hook Registrations | 104 | Scattered, undocumented |

**Critical Issues**:
- **8,162 lines in a single file** - This is unmaintainable
- 205 functions with no class organization
- 186 PHPCS ignores - the most of any file
- Deeply nested conditionals (some 6+ levels deep)
- Mixed concerns: UI rendering, API calls, data transformation, validation
- No clear entry points - functions call each other in a web

**Risk Level**: CRITICAL - Cannot safely modify without breaking production

---

### 3. Payment Processing

**Files**: `src/payments.php` (4,350 LOC), `app/Domain/PaymentIntegrations/` (15+ files)

| Aspect | Status | Details |
|--------|--------|---------|
| Legacy Test Coverage | 0% | src/payments.php untested |
| Modern Test Coverage | ~80% | PaymentIntegrations tested |
| PHPCS Ignores | 79 | Security-sensitive area |
| Payment Gateways | 19 | Complex integration matrix |

**Critical Issues**:
- Legacy `src/payments.php` contains 134 functions handling sensitive payment data
- HACK comments in code: "Most of these tokens are not giving four digit expiration years"
- Direct `$_POST` access in payment processing flows
- No payment data encryption at rest
- Modern `app/` payment integrations are well-structured but disconnected from legacy

**Risk Level**: CRITICAL - Financial/PCI compliance exposure

---

### 4. Customer Management

**Files**: `src/customers.php` (1,157 LOC)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Functions | 35 | All procedural |
| PHPCS Ignores | 11 | |

**Critical Issues**:
- Customer data synchronized without validation
- No retry mechanism for failed syncs
- Mixed WooCommerce and QPilot data models

**Risk Level**: MEDIUM

---

### 5. Order Processing

**Files**: `src/orders.php` (2,203 LOC), `src/checkout.php` (386 LOC)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Functions | 66 (orders) + 10 (checkout) | All procedural |
| PHPCS Ignores | 29 (orders) + 3 (checkout) | |
| wpdb Direct Calls | 7 | In orders.php |

**Critical Issues**:
- Direct database queries mixed with business logic
- `var_export` calls in production code (debug artifacts)
- Order creation is tightly coupled to WooCommerce hooks
- No transaction handling for multi-step order operations

**Risk Level**: HIGH

---

### 6. Cart & Checkout Integration

**Files**: `src/cart.php` (956 LOC), `src/checkout.php` (386 LOC)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Direct Superglobal Access | 19 (cart) | Security concern |

**Critical Issues**:
- Cart manipulation through direct `$_POST` access
- Frequency/schedule data passed through form fields without validation
- Cart session data not properly sanitized

**Risk Level**: HIGH - Direct customer interaction point

---

### 7. Admin Interface

**Files**: `src/admin.php` (2,965 LOC), multiple template files

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Functions | 93 | All procedural |
| PHPCS Ignores | 39 | |
| Hook Registrations | 24 | |

**Critical Issues**:
- Admin pages built with procedural functions
- No separation of routing, controller logic, and views
- Settings scattered across multiple options without schema
- OAuth callback handling has PHPCS ignores for input validation

**Risk Level**: MEDIUM - Admin-only but affects all configuration

---

### 8. REST API Layer

**Files**: `src/api.php` (847 LOC), `src/api-wc.php` (varies), `src/api-health.php` (696 LOC)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| REST Routes | 5 | Custom endpoints |
| PHPCS Ignores | 11 (api.php) | |

**Critical Issues**:
- TODO comments: "legacy code needs review for refactor"
- Duplicate order prevention logic marked as "TODO: Once all merchants have migrated..."
- No API versioning strategy
- No rate limiting on endpoints
- Mixed authentication patterns

**Risk Level**: HIGH - External attack surface

---

### 9. QPilot Integration (Legacy)

**Files**: `src/QPilot/Client.php`, `src/QPilot/PaymentData.php`

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| Status | SUPERSEDED | Modern app/Services/QPilot/ exists |

**Critical Issues**:
- Old client still in use by legacy code
- Two different API clients exist (old and new)
- Token management duplicated
- No clear deprecation path

**Risk Level**: MEDIUM - Technical debt but modern alternative exists

---

### 10. QuickLinks Module (Modern)

**Files**: `app/Modules/QuickLinks/` (full module)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | ~70% | Best in codebase |
| Architecture | Modern | DI, Interfaces, Services |
| Testability | HIGH | Can mock dependencies |

**Strengths**:
- Clean separation of concerns
- Interface-based design
- Multiple storage strategy support
- Proper audit logging

**Issues**:
- Still depends on legacy code for some operations
- Rate limiter storage could use distributed cache

**Risk Level**: LOW - Well-architected

---

### 11. Quicklaunch Module (Modern)

**Files**: `app/Modules/Quicklaunch/`

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | ~50% | Partial |
| Architecture | Modern | Step handlers pattern |

**Issues**:
- Step handlers have some code duplication
- No observability into wizard completion rates
- OAuth token storage not encrypted

**Risk Level**: LOW-MEDIUM

---

### 12. Product Synchronizer Module (Modern)

**Files**: `app/Modules/Synchronizers/Products/`

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | LOW | Minimal tests |
| Architecture | Modern | But thin |

**Issues**:
- Synchronous API calls block product saves
- No background processing
- No retry mechanism
- Large catalogs will timeout

**Risk Level**: MEDIUM

---

### 13. Bulk Operations

**Files**: `src/bulk.php` (1,495 LOC)

| Aspect | Status | Details |
|--------|--------|---------|
| Test Coverage | 0% | No unit tests |
| PHPCS Ignores | 22 | |
| Direct POST Access | Heavy | |

**Critical Issues**:
- Bulk operations execute synchronously
- No job queue or background processing
- Timeout risk on large operations
- Progress tracking is client-side only

**Risk Level**: HIGH - Data integrity during bulk ops

---

## Missing Feature Groups to Consider

### 1. Error Recovery & Resilience
**Currently Missing**:
- Circuit breaker for QPilot API
- Retry queues for failed operations
- Dead letter handling
- Graceful degradation

### 2. Observability Stack
**Currently Missing**:
- Structured logging (JSON format)
- Request correlation IDs
- APM integration
- Error aggregation (Sentry, etc.)
- Performance metrics
- Business metrics dashboards

### 3. Background Processing
**Currently Missing**:
- Job queue system
- Async operations
- Scheduled task management (beyond WP-Cron)
- Long-running operation support

### 4. Configuration Management
**Currently Missing**:
- Centralized settings schema
- Environment-based configuration
- Feature flag external service integration
- Settings validation

### 5. Security Infrastructure
**Currently Missing**:
- Input validation layer
- Output encoding standards
- Token encryption at rest
- Security headers
- Audit logging for admin actions
- RBAC beyond WordPress capabilities

### 6. Data Integrity
**Currently Missing**:
- Database transactions for multi-step operations
- Data validation layer
- Consistency checks
- Migration rollback capability

### 7. Developer Experience
**Currently Missing**:
- Integration test suite
- End-to-end tests
- Local development environment
- CI/CD pipeline documentation
- API documentation (OpenAPI/Swagger)

---

## Technical Debt Inventory

### Critical (Block Daily Deployment)

| Issue | Location | Impact | Effort |
|-------|----------|--------|--------|
| Zero test coverage on src/ | All legacy | Cannot safely deploy | HIGH |
| Broken test coverage reports | tests/ | No visibility | LOW |
| 659 PHPCS ignores | src/ | Hidden security debt | HIGH |
| God files (5 files >2000 LOC) | src/ | Unmaintainable | HIGH |
| Direct superglobal access (209) | src/ | Security vulnerability | MEDIUM |

### High (Block Team Scaling)

| Issue | Location | Impact | Effort |
|-------|----------|--------|--------|
| 957 procedural functions | src/ | Cannot parallelize work | HIGH |
| No interfaces in legacy | src/ | Cannot mock for testing | HIGH |
| 464 scattered hook registrations | src/ | No discoverability | MEDIUM |
| Two QPilot clients | src/ + app/ | Confusion, duplication | MEDIUM |
| Synchronous API operations | All | Performance, timeouts | HIGH |

### Medium (Block Enterprise Readiness)

| Issue | Location | Impact | Effort |
|-------|----------|--------|--------|
| No structured logging | All | Cannot aggregate logs | MEDIUM |
| No retry mechanisms | API calls | Failed operations lost | MEDIUM |
| No background processing | All | UI blocking, timeouts | HIGH |
| Settings scattered | wp_options | No validation | MEDIUM |
| No API versioning | src/api.php | Breaking changes risk | LOW |

---

## Quantified Code Quality Metrics

```
Source Code Analysis (src/):
---------------------------
Files:                     31
Lines of Code:         37,000 (approx)
Functions:                957
Average LOC/function:      39
God Files (>2000 LOC):      5
PHPCS Ignores:            659 (17.8 per 1000 LOC)
Direct $_POST/$_GET:      209
Try-Catch Blocks:          93
WP_Error Returns:         167
Direct wpdb Calls:         74

Modern Code Analysis (app/):
----------------------------
Files:                    187
Interfaces:                25
Classes:                  ~160
Test Files:                66
Exception Handling:        36
Testability:            GOOD
```

---

## Roadmap to Enterprise Readiness

### Phase 1: Foundation (Enable TDD)
**Goal**: Make changes safe

1. **Fix test coverage reporting** - Cannot measure what we can't see
2. **Add integration tests for critical paths** - Orders, Payments, Checkout
3. **Establish CI/CD pipeline** - Block merges without passing tests
4. **Add pre-commit hooks** - Enforce standards before code review

### Phase 2: Safety Net (Enable Daily Deployment)
**Goal**: Deploy without fear

1. **Extract testable services from legacy** - Start with highest-risk areas
2. **Add circuit breaker to QPilot client** - Prevent cascade failures
3. **Implement structured logging** - Enable debugging in production
4. **Add error tracking** (Sentry/similar) - Know when things break

### Phase 3: Modernization (Enable Team Scaling)
**Goal**: Multiple features in parallel

1. **Break up god files** - scheduled-orders.php, payments.php, products.php
2. **Introduce domain layer** - Extract business logic from procedural code
3. **Deprecate legacy QPilot client** - Single source of truth
4. **Add background job processing** - Remove synchronous bottlenecks

### Phase 4: Enterprise Features
**Goal**: Production-grade operations

1. **Implement proper configuration management**
2. **Add observability stack** (metrics, tracing, dashboards)
3. **Security audit and remediation** - Address all PHPCS ignores
4. **API documentation and versioning**
5. **WordPress Multisite support**

---

## Recommended Immediate Actions

### Week 1-2: Visibility
- [ ] Fix test coverage reporting
- [ ] Add basic CI pipeline
- [ ] Document all current PHPCS ignores with severity

### Week 3-4: Safety
- [ ] Add integration tests for checkout flow
- [ ] Add integration tests for payment processing
- [ ] Implement error tracking (Sentry)

### Month 2: First Extractions
- [ ] Extract order creation into testable service
- [ ] Extract payment processing into testable service
- [ ] Add circuit breaker to API client

---

## Appendix: File Size Distribution

| File | Lines | Functions | PHPCS Ignores | Risk |
|------|-------|-----------|---------------|------|
| scheduled-orders.php | 8,162 | 205 | 186 | CRITICAL |
| payments.php | 4,350 | 134 | 79 | CRITICAL |
| products.php | 3,841 | 113 | 64 | HIGH |
| admin.php | 2,965 | 93 | 39 | MEDIUM |
| product-page.php | 2,243 | 35 | 20 | HIGH |
| orders.php | 2,203 | 56 | 29 | HIGH |
| wholesale-pricing.php | 1,880 | 24 | 26 | MEDIUM |
| bulk.php | 1,495 | 27 | 22 | HIGH |
| customers.php | 1,157 | 35 | 11 | MEDIUM |

---

## Conclusion

This codebase was built for speed-to-market, not maintainability. The agency delivered working software but accumulated significant technical debt:

- **The legacy layer cannot support TDD** - It must be refactored or rewritten
- **Daily deployments are not safe** - No test coverage on critical paths
- **Team scaling is blocked** - Procedural code prevents parallel development
- **Enterprise features require foundation work first**

The good news: The modern `app/` architecture provides a template. The QuickLinks module demonstrates the target state. The path forward is incremental extraction from legacy to modern patterns, with testing as the forcing function.

**The single most important investment**: Fix test coverage reporting and add integration tests for the highest-risk flows (checkout, payment, order creation).

---

*Document Version: 2.0*
*Last Updated: December 2024*
*Assessment Scope: Full codebase analysis*
