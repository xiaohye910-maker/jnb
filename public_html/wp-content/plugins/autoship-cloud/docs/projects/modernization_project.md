# Legacy `src/` Modernization Project

> **Date:** 2026-02-23 | **Updated:** 2026-02-24
> **Status:** In Progress — Phase 0 complete
> **Scope:** Migrate all procedural code from `src/` to the modern `app/` architecture, delete WC-AUTOSHIP legacy code.
> **Excluded:** `payments.php` (separate branch, another engineer), `scheduled-orders.php` (separate project).

---

## 1. Current State

### Lines of Code

| Directory | PHP LOC | Files | % of Total |
|-----------|---------|-------|------------|
| `src/` (legacy) | ~36,288 | 28 | 67.2% |
| `app/` (modern) | 17,431 | ~80+ | 32.3% |
| `autoship.php` | 255 | 1 | — |
| **Total** | **~53,974** | — | — |

> **Phase 0 removed ~1,660 LOC** (import.php 808, export.php 506, upgrade.php 330, plus edits to autoship.php, api.php, admin.php, batch.js, admin.js).

### What Has Already Been Migrated to `app/`

| Module/Service | Location | Replaces |
|---|---|---|
| HealthcheckModule | `app/Modules/Healthcheck/` | `src/legacy/healthchecks.php` |
| PaymentsModule | `app/Modules/Payments/` | `src/payments.php` (in progress) |
| NextimeModule | `app/Modules/Nextime/` | Shipping integrations |
| QuicklaunchModule | `app/Modules/Quicklaunch/` | Setup wizard |
| QuickLinksModule | `app/Modules/QuickLinks/` | One-click subscription management |
| ProductSynchronizerModule | `app/Modules/Synchronizers/Products/` | Product sync with QPilot |
| QPilot Service Client | `app/Services/QPilot/` | ~60% of `src/QPilot/Client.php` |
| Logging Service | `app/Services/Logging/` | `src/legacy/logger.php` |
| Labels Service | `app/Services/Labels/` | White-label text customization |

### What Remains in `src/` (In Scope)

| File | LOC | Summary |
|------|-----|---------|
| `products.php` | 3,846 | Product management, admin UI, QPilot sync |
| `product-page.php` | 2,248 | Frontend product display and interactions |
| `admin.php` | ~2,130 | Main admin interface and settings |
| `orders.php` | 2,203 | Scheduled order management |
| `QPilot/Client.php` | 2,043 | Legacy API client (60% duplicated in `app/`) |
| `wholesale-pricing.php` | 1,880 | Wholesale tier pricing system |
| `bulk.php` | 1,495 | Batch product operations |
| `customers.php` | 1,162 | Customer data sync with QPilot |
| `cart.php` | 956 | Cart schedule options and AJAX |
| `utilities.php` | 832 | Date helpers, template functions, CSV export |
| `coupons.php` | 763 | Coupon validation with QPilot |
| `api.php` | ~644 | REST API endpoints |
| `pages.php` | 540 | My Account endpoints |
| `checkout.php` | 386 | Checkout flow integration |
| `shipping.php` | 378 | Shipping address and zones |
| `scripts.php` | 249 | Asset enqueuing |
| `shortcodes.php` | 233 | Shortcode registrations |
| `ajax.php` | 220 | Frontend AJAX handlers |
| `api-wc.php` | 203 | WooCommerce REST API extensions |
| `free-shipping.php` | 114 | Free shipping method |
| `QPilot/PaymentData.php` | 86 | Payment data transformation |

> **Deleted in Phase 0:** `import.php` (808), `export.php` (506), `upgrade.php` (330)

---

## 2. WC-AUTOSHIP Legacy Audit

> **Status: COMPLETED** — All WC-AUTOSHIP legacy code removed in Phase 0 (2026-02-24).

All WC-AUTOSHIP code has been deleted. The following files and references were removed:

### Files Deleted

| File | LOC | What It Contained |
|------|-----|-------------------|
| `src/import.php` | 808 | 23 functions for one-time migration from old WC-Autoship plugin to QPilot. 3 AJAX hooks. |
| `src/export.php` | 506 | 9 functions for CSV export of legacy scheduled orders. 2 hooks (`init` + AJAX). |
| `src/upgrade.php` | 330 | Plugin version migration routines (v2→v3 processing version, pre-1.2.32 upgrade paths). All code paths dead — all customers are 2.10+, no more version upgrades/downgrades. |

### References Cleaned Up

| File | What Was Removed |
|------|-----------------|
| `autoship.php` | `require_once` lines for import.php, export.php, upgrade.php |
| `src/api.php` | `autoship_has_legacy_origin()` call and `_qpilot_wc_autoship_legacy` sitemeta key |
| `src/admin.php` | `autoship_migrations_section()` function, `'autoship-migrations'` from tab exclusion array |
| `js/batch.js` | Legacy import batch handler (lines 1-134: `initBatch('autoship_import_*')`) |
| `js/admin.js` | `migrationFormsubmit` function and `#autoship-bulk-export-csv` event binding |

### NOT Legacy (Kept)

- `wc-autoshipcloud/v1` REST namespace in `src/api.php` — This is the **current** API endpoint for QPilot webhooks. Not related to old WC-Autoship.

### Orphaned Database Artifacts (left in place)

| Type | Key/Table | Notes |
|------|-----------|-------|
| DB table | `wp_wc_autoship_schedules` | Created by old WC-Autoship plugin. No code references it. |
| DB table | `wp_wc_autoship_schedule_items` | Same. |
| DB table | `wp_wc_autoship_import` | Created by import.php tracking system. |
| WP option | `autoship_upsert_stats` | Written by import code. Harmless orphan. |
| WP option | `autoship_last_export` | Written by export code. Harmless orphan. |
| WP option | `_autoship_transition_version` | Written by upgrade.php. Harmless orphan. |
| WP transient | `autoship_sync_upgrade_activated` | Written by upgrade.php. Harmless orphan. |
| Post meta | `_wc_autoship_enable_autoship`, `_wc_autoship_price` | Legacy product meta. Left as orphans. |
| User meta | `wc_autoship_authorize_net_id`, `wc_autoship_braintree_id`, `wc_autoship_cyber_source_id` | Legacy payment meta. Left as orphans. |

---

## 3. File Scoring Matrix

Each file scored **1-5** per dimension (1 = easiest/best, 5 = hardest/worst). **Lower total = easier to migrate.**

| Category | What It Measures |
|----------|-----------------|
| **Simplicity** | LOC, cyclomatic complexity, function count |
| **Coupling (Inbound)** | How many other files depend on this file's functions |
| **Coupling (Outbound)** | How many other `src/` functions this file calls |
| **Hook Density** | WordPress `add_action`/`add_filter`/`apply_filters`/`do_action` calls |
| **QPilot API Surface** | Direct API calls that overlap with `app/Services/QPilot/` |
| **Security Surface** | AJAX handlers, user input, nonces, capability checks |
| **Already Covered** | How much functionality already exists in modern `app/` code |

### Tier 1: Quick Wins (score 7-13)

| # | File | LOC | Simple | In | Out | Hooks | QPilot | Security | Covered | **Total** |
|---|------|-----|--------|-----|-----|-------|--------|----------|---------|-----------|
| 1 | `QPilot/PaymentData.php` | 86 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | **7** |
| 2 | `free-shipping.php` | 114 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | **7** |
| 3 | `shortcodes.php` | 233 | 1 | 1 | 2 | 2 | 1 | 1 | 1 | **9** |
| 4 | `scripts.php` | 249 | 1 | 1 | 2 | 2 | 1 | 1 | 1 | **9** |
| 5 | `ajax.php` | 220 | 1 | 2 | 2 | 2 | 1 | 3 | 1 | **12** |
| 6 | `api-wc.php` | 203 | 2 | 2 | 2 | 2 | 1 | 3 | 1 | **13** |

**Notes:**
- `PaymentData.php`: Pure data class. Move to `app/Domain/`. Zero hooks, zero coupling.
- `free-shipping.php`: Self-contained WC shipping method. 4 hooks, 2 DB queries. Becomes a small module.
- `shortcodes.php`: 5 shortcodes, thin wrappers. Delegates to other functions.
- `scripts.php`: Asset enqueuing only. 9 `wp_enqueue` calls. Pure configuration.
- `ajax.php`: 4 AJAX handlers. Security checks present but need careful migration.
- `api-wc.php`: WC REST filters. Prevents duplicate QPilot orders. Critical but small.
- ~~`upgrade.php`~~: **Deleted in Phase 0** — all version migration routines removed.

### Tier 2: Moderate Effort (score 14-22)

| # | File | LOC | Simple | In | Out | Hooks | QPilot | Security | Covered | **Total** |
|---|------|-----|--------|-----|-----|-------|--------|----------|---------|-----------|
| 8 | `shipping.php` | 378 | 2 | 2 | 2 | 2 | 1 | 2 | 3 | **14** |
| 9 | `pages.php` | 540 | 2 | 2 | 2 | 4 | 1 | 3 | 1 | **15** |
| 10 | `checkout.php` | 386 | 2 | 2 | 3 | 3 | 2 | 3 | 1 | **16** |
| 11 | `utilities.php` | 832 | 2 | 5 | 2 | 3 | 2 | 2 | 1 | **17** |
| 12 | `cart.php` | 956 | 3 | 3 | 3 | 4 | 1 | 3 | 1 | **18** |
| 13 | `bulk.php` | 1,495 | 3 | 2 | 3 | 3 | 2 | 3 | 2 | **18** |
| 14 | `wholesale-pricing.php` | 1,880 | 4 | 2 | 3 | 4 | 1 | 3 | 1 | **18** |
| 15 | `coupons.php` | 763 | 3 | 2 | 3 | 3 | 3 | 3 | 2 | **19** |
| 16 | `product-page.php` | 2,248 | 4 | 3 | 3 | 5 | 1 | 4 | 1 | **21** |
| 17 | `customers.php` | 1,162 | 3 | 3 | 3 | 4 | 4 | 3 | 2 | **22** |

**Notes:**
- `shipping.php`: Nextime module in `app/` already covers some shipping. Overlap to audit.
- `pages.php`: 23 hooks. WooCommerce My Account endpoint registration.
- `checkout.php`: Scheduled order creation on `woocommerce_payment_complete`. Deep WC hooks.
- `utilities.php`: **Highest inbound coupling (25+ callers).** Template functions, date helpers, CSV export. Must migrate early with a legacy bridge — everything depends on this.
- `cart.php`: 30 hooks, 2 AJAX, 3 template renders. Heart of the cart UX.
- `bulk.php`: Complex SQL JOINs for batch product queries.
- `wholesale-pricing.php`: 56 hooks. Self-contained but deeply hooked into product pricing.
- `coupons.php`: QPilot coupon validation. `app/Services/QPilot/Coupons/` already has request/response DTOs.
- `product-page.php`: 100+ hooks, 3 AJAX, 4 template renders. Frontend product display.
- `customers.php`: 10 QPilot API calls, 27 hooks. `app/Services/QPilot/Customers/` has the DTO layer already.

### Tier 3: Heavy Lift (score 26-33)

| # | File | LOC | Simple | In | Out | Hooks | QPilot | Security | Covered | **Total** |
|---|------|-----|--------|-----|-----|-------|--------|----------|---------|-----------|
| 18 | `QPilot/Client.php` | 2,043 | 4 | 5 | 1 | 3 | 5 | 4 | 4 | **26** |
| 19 | `admin.php` | 2,144 | 4 | 4 | 4 | 5 | 4 | 5 | 2 | **28** |
| 20 | `orders.php` | 2,203 | 4 | 4 | 4 | 5 | 4 | 5 | 2 | **28** |
| 21 | `products.php` | 3,846 | 5 | 5 | 5 | 5 | 5 | 5 | 3 | **33** |

**Notes:**
- `QPilot/Client.php`: 20+ callers. 50+ HTTP calls. `app/Services/QPilot/` already duplicates ~60%. Migration = feature parity audit + swap callers + delete.
- `admin.php`: 79+ hooks, 5 AJAX, 20+ DB queries. The most coupled admin file.
- `orders.php`: 91+ hooks, 12 QPilot calls. Deep WC order lifecycle integration.
- `products.php`: **Hardest file in the codebase.** 138+ hooks, 18 QPilot calls, 12 DB queries, 5 AJAX handlers. The god file.

### ~~Delete Candidates (Not Migration — Removal)~~ — COMPLETED

> All delete candidates were removed in Phase 0 (2026-02-24). Total removed: **~1,660 LOC**.
>
> Files deleted: `import.php` (808), `export.php` (506), `upgrade.php` (330).
> Files edited: `autoship.php`, `api.php`, `admin.php`, `js/batch.js`, `js/admin.js`.

---

## 4. Analysis Lenses

### Lens 1: Impact vs Effort

```
HIGH IMPACT
    |
    |  QPilot/Client.php          products.php
    |  utilities.php              orders.php
    |  customers.php              admin.php
    |     MODERATE EFFORT            HIGH EFFORT
    |
    |  checkout.php               product-page.php
    |  cart.php                   wholesale-pricing.php
    |  coupons.php                bulk.php
    |     MODERATE EFFORT            MODERATE EFFORT
    |
    |  scripts.php
    |  shortcodes.php
    |  ajax.php, free-shipping
    |  PaymentData.php
    |     LOW EFFORT
    |
LOW IMPACT
```

### Lens 2: Dependency Chain (Migration Order Matters)

```
Layer 0 — No dependencies, migrate first:
  PaymentData.php -> free-shipping.php -> scripts.php -> shortcodes.php

Layer 1 — Depends on Layer 0 only:
  ajax.php -> api-wc.php -> shipping.php -> pages.php

Layer 2 — Depends on utilities + Layer 1:
  utilities.php  <-- 25+ files depend on this. Migrate early, keep legacy bridge.
  checkout.php -> coupons.php

Layer 3 — Depends on QPilot client + Layer 2:
  QPilot/Client.php  <-- 20+ callers. Parity audit with app/Services/QPilot/.
  customers.php -> cart.php -> bulk.php

Layer 4 — Most coupled, migrate last:
  orders.php -> product-page.php -> wholesale-pricing.php -> admin.php -> products.php
```

### Lens 3: Security Improvement Priority

| Risk Level | Files | Why |
|------------|-------|-----|
| **Critical** | `admin.php`, `products.php`, `orders.php` | Most AJAX handlers, most user input, deepest admin access |
| **High** | `ajax.php`, `api-wc.php`, `checkout.php`, `customers.php` | Payment tokens, order creation, customer PII |
| **Medium** | `cart.php`, `bulk.php`, `coupons.php` | AJAX handlers, POST data, pricing manipulation |
| **Low** | `scripts.php`, `shortcodes.php`, `free-shipping.php`, `pages.php`, `PaymentData.php` | Read-only, configuration, or display-only |

### Lens 4: Test Coverage Opportunity

Files in `src/` **cannot be unit tested** (procedural functions + WordPress globals). Every file migrated to `app/` gains testability via Brain\Monkey. Highest-value targets:

1. **`utilities.php`** — Date calculations are bug-prone and used by 25+ files. Testing these alone prevents regressions across the entire plugin.
2. **`coupons.php`** — Coupon validation logic is complex. Bugs here = revenue loss.
3. **`checkout.php`** — Order creation is the money path. Bugs = failed sales.
4. **`QPilot/Client.php`** — Already 60% covered by `app/Services/QPilot/`. Completing this eliminates the biggest untested surface.

### Lens 5: LOC Distribution After Phase 0

After Phase 0 deletions (`import.php`, `export.php`, `upgrade.php`, and WC-AUTOSHIP references):

| Bucket | LOC | % |
|--------|-----|---|
| Already migrating (`payments.php`, `scheduled-orders.php`) | 12,513 | 36% |
| ~~Delete (WC-AUTOSHIP legacy)~~ | ~~1,660~~ | ~~— DONE~~ |
| Merge into existing `app/` service (`QPilot/Client.php`) | 2,129 | 6% |
| **Actual new migration work** | **~20,583** | **58%** |

Of that ~20,583 LOC, the top 5 files (`products.php`, `product-page.php`, `orders.php`, `admin.php`, `wholesale-pricing.php`) account for **12,321 LOC (60%)**. The remaining 15 files are ~8,262 LOC.

---

## 5. Migration Phases

### Phase 0: Delete WC-AUTOSHIP Legacy (~1,660 LOC removed) — COMPLETED

> **Completed:** 2026-02-24

**What was removed:**
- Deleted `src/import.php` (808 LOC) — 23 functions, 3 AJAX hooks
- Deleted `src/export.php` (506 LOC) — 9 functions, 2 hooks
- Deleted `src/upgrade.php` (330 LOC) — all version migration routines (v2→v3 processing, pre-1.2.32 upgrade paths, PayPal warning). All code paths were dead for customers on 2.10+.
- Removed `autoship_has_legacy_origin()` call and `_qpilot_wc_autoship_legacy` sitemeta key from `src/api.php`
- Removed `autoship_migrations_section()` and `'autoship-migrations'` tab reference from `src/admin.php`
- Removed legacy import batch handler from `js/batch.js` (134 lines)
- Removed migration export handler from `js/admin.js` (54 lines)
- Removed `require_once` lines from `autoship.php`

**Orphaned DB artifacts left in place** (harmless, see Section 2 for full inventory).

### Phase 1: Quick Wins + Foundation (~1,734 LOC)

`PaymentData.php` (86) -> `free-shipping.php` (114) -> `scripts.php` (249) -> `shortcodes.php` (233) -> `ajax.php` (220) -> `api-wc.php` (203) -> `shipping.php` (378) -> API endpoint registration from `api.php` (~251)

**Why:** Builds confidence, establishes migration patterns, low risk. Each file is self-contained enough to migrate independently.

### Phase 2: High-Value Infrastructure (~2,875 LOC)

`utilities.php` (832) -> `QPilot/Client.php` (2,043 — parity audit + caller swap)

**Why:** Unblocks all downstream migrations. `utilities.php` is called by 25+ files — migrating it with a legacy bridge removes the biggest shared dependency. `QPilot/Client.php` has 60% overlap with `app/Services/QPilot/` — completing parity eliminates the largest duplicated code.

### Phase 3: Business Logic (~5,347 LOC)

`pages.php` (540) -> `checkout.php` (386) -> `coupons.php` (763) -> `cart.php` (956) -> `customers.php` (1,162) -> `bulk.php` (1,495) (+ remaining API logic from `api.php` ~396)

**Why:** Core commerce flow. Each can be an independent module. `app/Services/QPilot/` already has DTO layers for coupons and customers, reducing the API integration work.

### Phase 4: Heavy Hitters (~12,321 LOC)

`wholesale-pricing.php` (1,880) -> `product-page.php` (2,248) -> `orders.php` (2,203) -> `admin.php` (2,144) -> `products.php` (3,846)

**Why:** Largest files, most hooks, most risk. Requires full test coverage before and after. `products.php` is the final boss — 138+ hooks, 18 QPilot calls, touches every part of the plugin.

---

## 6. Per-File Detailed Characteristics

### `QPilot/PaymentData.php` (86 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 2 files |
| Outbound coupling | 0 |
| WordPress hooks | 0 |
| QPilot API calls | 0 (data class only) |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 0 |
| Security surface | Low — data container |

### `free-shipping.php` (114 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 2 files |
| Outbound coupling | 4 functions |
| WordPress hooks | 1 `add_action`, 1 `add_filter`, 2 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 2 (`$wpdb` direct) |
| JS/CSS enqueues | 0 |
| Security surface | Low |

### `shortcodes.php` (233 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 3 files |
| Outbound coupling | 12 functions |
| WordPress hooks | 5 `add_shortcode`, 4 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 2 (`autoship_render_template`) |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 0 |
| Security surface | Medium — `do_shortcode`, customer IDs |

### `scripts.php` (249 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 2 files |
| Outbound coupling | 8 functions |
| WordPress hooks | 2 `add_action`, 5 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 9 |
| Security surface | Low — reads settings only |

### `ajax.php` (220 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 8 (AJAX endpoints) |
| Outbound coupling | 9 functions |
| WordPress hooks | 4 `add_action`, 3 `apply_filters` |
| QPilot API calls | 2 |
| Templates | 0 |
| AJAX handlers | 4 `wp_ajax` actions |
| REST endpoints | 0 |
| DB queries | 1 (`delete_user_meta`) |
| JS/CSS enqueues | 0 |
| Security surface | High — POST data, capability checks, auth tokens |

### `api-wc.php` (203 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 6 (WC REST filters) |
| Outbound coupling | 7 functions |
| WordPress hooks | 3 `add_filter`, 1 `add_action`, 4 `apply_filters` |
| QPilot API calls | 2 |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | Filters on 6 WC REST endpoints |
| DB queries | 5+ (`wc_get_orders` with meta queries) |
| JS/CSS enqueues | 0 |
| Security surface | High — order creation validation, prevents duplicates |

### ~~`upgrade.php` (342 LOC)~~ — Deleted in Phase 0

### `shipping.php` (378 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 3 files |
| Outbound coupling | 6 functions |
| WordPress hooks | 2 `add_action`, 4 `add_filter`, 3 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 4 (`$wpdb` — shipping zones) |
| JS/CSS enqueues | 0 |
| Security surface | Medium — address data |

### `pages.php` (540 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 4 files |
| Outbound coupling | 9 functions |
| WordPress hooks | 10 `add_action`, 8 `add_filter`, 5 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 1 (`do_shortcode`) |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 0 |
| Security surface | High — account endpoints, redirects, capabilities |

### `checkout.php` (386 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 5 files |
| Outbound coupling | 11 functions |
| WordPress hooks | 7 `add_action`, 4 `add_filter`, 6 `apply_filters` |
| QPilot API calls | 2 |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 3 |
| Security surface | High — checkout flow, payment methods, guest restrictions |

### `utilities.php` (832 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | **25+ files** (highest in codebase) |
| Outbound coupling | 8 functions |
| WordPress hooks | 11 `add_action`/`add_filter`, 20 `apply_filters` |
| QPilot API calls | 6 |
| Templates | 2 (the `autoship_include_template` and `autoship_render_template` functions **live here**) |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 3 |
| JS/CSS enqueues | 0 |
| Security surface | Medium — file I/O in CSV export helpers |

### `coupons.php` (763 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 4 files |
| Outbound coupling | 13 functions |
| WordPress hooks | 3 `add_action`, 3 `add_filter`, 8 `apply_filters` |
| QPilot API calls | 5 |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 1 filter on REST orders |
| DB queries | 1 |
| JS/CSS enqueues | 0 |
| Security surface | High — coupon validation, REST, logging |

### `cart.php` (956 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 9 files |
| Outbound coupling | 18 functions |
| WordPress hooks | 11 `add_action`, 7 `add_filter`, 12 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 3 |
| AJAX handlers | 2 `wp_ajax` |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 5 |
| Security surface | High — cart manipulation, AJAX, POST data |

### `customers.php` (1,162 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 8 files |
| Outbound coupling | 16 functions |
| WordPress hooks | 8 `add_action`, 5 `add_filter`, 14 `apply_filters` |
| QPilot API calls | 10 |
| Templates | 0 |
| AJAX handlers | 1 (indirect) |
| REST endpoints | 0 |
| DB queries | 3 (user meta) |
| JS/CSS enqueues | 0 |
| Security surface | High — customer PII sync, address updates, capabilities |

### `bulk.php` (1,495 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 3 files |
| Outbound coupling | 11 functions |
| WordPress hooks | 7 `add_action`, 6 `add_filter`, 3 `apply_filters` |
| QPilot API calls | 2 |
| Templates | 0 |
| AJAX handlers | 1 major `wp_ajax` |
| REST endpoints | 0 |
| DB queries | 5 major (complex JOINs) |
| JS/CSS enqueues | 0 |
| Security surface | Medium — batch AJAX, POST validation |

### `wholesale-pricing.php` (1,880 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 4 files |
| Outbound coupling | 12 functions |
| WordPress hooks | 38 `add_action`/`add_filter`, 18 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 2 |
| AJAX handlers | 2 `wp_ajax` |
| REST endpoints | 0 |
| DB queries | 8 (product meta, tier queries) |
| JS/CSS enqueues | 2 |
| Security surface | High — pricing logic, AJAX, tier access |

### `product-page.php` (2,248 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 8 files |
| Outbound coupling | 16 functions |
| WordPress hooks | 80 `add_action`/`add_filter`, 20 `apply_filters` |
| QPilot API calls | 0 |
| Templates | 4 |
| AJAX handlers | 3 `wp_ajax` |
| REST endpoints | 0 |
| DB queries | 5 (product meta) |
| JS/CSS enqueues | 4 |
| Security surface | High — AJAX, user input, capabilities |

### `QPilot/Client.php` (2,043 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | **20+ files** (second highest) |
| Outbound coupling | 2 (Logger) |
| WordPress hooks | 18 `apply_filters`/`do_action` |
| QPilot API calls | **50+ direct HTTP calls** |
| Templates | 0 |
| AJAX handlers | 0 |
| REST endpoints | 0 |
| DB queries | 0 |
| JS/CSS enqueues | 0 |
| Security surface | Very High — OAuth2, API auth, exception handling |

### `admin.php` (2,144 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 12+ files |
| Outbound coupling | 22 functions |
| WordPress hooks | 49 `add_action`/`add_filter`, 30 `apply_filters` |
| QPilot API calls | 15+ |
| Templates | 2 |
| AJAX handlers | 5 `wp_ajax` |
| REST endpoints | 0 |
| DB queries | 20+ |
| JS/CSS enqueues | 0 |
| Security surface | Very High — admin pages, nonces, capabilities, settings |

### `orders.php` (2,203 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | 10+ files |
| Outbound coupling | 19 functions |
| WordPress hooks | 66 `add_action`/`add_filter`, 25 `apply_filters` |
| QPilot API calls | 12 |
| Templates | 2 |
| AJAX handlers | 3 `wp_ajax` |
| REST endpoints | 0 |
| DB queries | 8 (order meta, post queries) |
| JS/CSS enqueues | 2 |
| Security surface | Very High — order operations, AJAX, capabilities |

### `products.php` (3,846 LOC)

| Metric | Value |
|--------|-------|
| Inbound coupling | **15+ files** |
| Outbound coupling | **25+ functions** |
| WordPress hooks | **103 `add_action`/`add_filter`, 35 `apply_filters`** |
| QPilot API calls | **18** |
| Templates | 3 |
| AJAX handlers | **5 `wp_ajax`** |
| REST endpoints | 2 filters |
| DB queries | **12** |
| JS/CSS enqueues | 3 |
| Security surface | **Very High** — product admin, AJAX, REST, metadata |

---

## 7. Legacy `autoship_*()` Calls Already in `app/`

These procedural functions from `src/` are called inside the modern `app/` directory. Each one is a coupling point that needs resolution as its parent file gets migrated.

| Category | Calls | Unique Functions |
|---|---|---|
| Credential/auth checks | 10 | 2 (`autoship_has_credentials`, `autoship_has_auth_token`) |
| OAuth connection | 1 | 1 (`autoship_oauth2_connect_site`) |
| Config getters | 6 | 5 (`autoship_get_api_url`, `autoship_get_merchants_url`, `autoship_get_site_id`, `autoship_get_token_auth`, `autoship_get_scheduled_orders_url`) |
| Template rendering | 11 | 1 (`autoship_include_template`) |
| Product operations | 11 | 10 (`autoship_push_product`, `autoship_set_product_*`, etc.) |
| **Total** | **39** | **19** |

See `docs/roadmap.md` for the full call-site inventory.

---

## 8. Migration Pattern Reference

The established pattern (from HealthcheckModule and PaymentsModule) is:

1. Create interface in `app/Services/{Name}/` or `app/Domain/{Name}/`
2. Create implementation in `app/Services/{Name}/Implementations/`
3. If major feature: create module in `app/Modules/{Name}/` implementing `ModuleInterface`
4. Register service in `app/Core/Plugin::register_core_services()`
5. Register module in `app/Core/Plugin::register_modules()`
6. Add feature flag in `app/Core/FeatureManager.php` if needed
7. Rewrite `src/` functions as thin delegates to the service container (legacy bridge)
8. Write PHPUnit tests with Brain\Monkey mocking in `tests/`

---

## 9. Phase 0 Deep Dive: WC-AUTOSHIP Removal Changeset

> **Status:** COMPLETED (2026-02-24)
> **LOC removed:** ~1,660
> **Includes:** Full deletion of `upgrade.php` (330 LOC) in addition to original scope — all version migration routines were dead code for customers on 2.10+.

### 9.1 Coupling Analysis

Every function in `import.php` and `export.php` is **100% self-contained**. No function defined in either file is called by any other PHP file in the codebase. The only external callers are JavaScript AJAX requests from `js/batch.js` and `js/admin.js`.

Cross-file references are limited to:

| Reference | Location | Direction |
|-----------|----------|-----------|
| `autoship_has_legacy_origin()` | Defined in `src/upgrade.php` line 68 | Called by `src/api.php` line 348 |
| `_qpilot_wc_autoship_legacy` | Sent in sitemeta payload | `src/api.php` line 355 |
| `autoship_migrations_section()` | Defined in `src/admin.php` line 1659 | Dead code — template `admin/migrations` does not exist |
| `'autoship-migrations'` string | `src/admin.php` line 1530 | In `autoship_exclude_submit_on_tab()` array |
| `require_once 'src/export.php'` | `autoship.php` line 198 | File loader |
| `require_once 'src/import.php'` | `autoship.php` line 199 | File loader |
| `initBatch('autoship_import_*')` | `js/batch.js` lines 131-132 | JS AJAX callers |
| `migrationFormsubmit` | `js/admin.js` lines 368-418 | JS AJAX caller for export |

### 9.2 Complete Changeset

#### DELETE — `src/import.php` (808 LOC)

Delete the entire file. Contains 23 functions, all internal-only:

| Function | LOC | Purpose |
|----------|-----|---------|
| `autoship_query_legacy_upsert_stats()` | 28 | Import progress tracking |
| `autoship_query_legacy_orders()` | 35 | Queries `wp_wc_autoship_schedules` |
| `autoship_query_legacy_order_items()` | 21 | Queries `wp_wc_autoship_schedule_items` |
| `autoship_ajax_import_schedule_data()` | 89 | AJAX: batch import scheduled orders to QPilot |
| `autoship_ajax_import_product_settings()` | 99 | AJAX: migrate product `_wc_autoship_*` meta |
| `autoship_import_create_scheduled_order()` | 124 | Creates QPilot orders from legacy data |
| `autoship_import_product_settings_price()` | 11 | Converts `_wc_autoship_price` to new fields |
| `autoship_import_payment_integrations()` | 142 | AJAX: migrates 5 gateway configs to QPilot |
| `autoship_import_get_payment_row()` | 12 | Reads WC payment token with JOINs |
| `wc_autoship_import_get_stripe_method()` | 5 | Gets Stripe token + `_stripe_customer_id` |
| `wc_autoship_import_get_authorize_method()` | 6 | Gets AuthorizeNet token + `wc_autoship_authorize_net_id` |
| `wc_autoship_import_get_braintree_method()` | 6 | Gets Braintree token + `wc_autoship_braintree_id` |
| `wc_autoship_import_get_paypal_method()` | 6 | Gets PayPal token (no customer ID) |
| `wc_autoship_import_get_cyber_source_method()` | 4 | Gets CyberSource token + `wc_autoship_cyber_source_id` |
| `wc_autoship_update_db_import_schedules()` | 12 | Writes to `wp_wc_autoship_import` tracking table |
| `wc_autoship_update_test_connection()` | 11 | Writes test connection result to tracking table |
| `get_import_values()` | 11 | Reads from `wp_wc_autoship_import` table |
| `format_date()` | 4 | Generic date formatter (global name — removing eliminates collision risk) |
| `format_time()` | 4 | Generic time formatter (same) |
| `check_table_exist()` | 9 | Checks if `wp_wc_autoship_import` table exists |

Hook registrations removed with the file:
- `add_action( 'wp_ajax_autoship_import_schedule_data', ... )` (line 218)
- `add_action( 'wp_ajax_autoship_import_product_settings', ... )` (line 321)
- `add_action( 'wp_ajax_autoship_import_payment_integrations', ... )` (line 619)

#### DELETE — `src/export.php` (506 LOC)

Delete the entire file. Contains 9 functions, all internal-only:

| Function | LOC | Purpose |
|----------|-----|---------|
| `autoship_legacy_export_query_ids()` | 21 | JOINs `wp_wc_autoship_schedules` + `wp_wc_autoship_schedule_items` |
| `autoship_export_filepath_url()` | 3 | Returns export path (`wp-content/uploads/schedule-data/`) |
| `autoship_get_export_download_link()` | 18 | Builds download URL for `?page=migrations&autoship_download_csv` |
| `autoship_export_retrieve_payment_token()` | 10 | Wraps `WC_Payment_Token_CC` lookup |
| `autoship_export_ajax_scheduled_orders()` | 335 | Main CSV export logic — iterates legacy orders, builds CSV rows |
| `autoship_set_html_content_type()` | 1 | Returns `'text/html'` for `wp_mail` |
| `autoship_export_email_notification()` | 21 | Emails admin with CSV download link |
| `autoship_download_csv()` | 29 | `init` hook: serves CSV file download on `?autoship_download_csv` |
| `autoship_ajax_initiate_schedule_export()` | 4 | AJAX wrapper for export |

Hook registrations removed with the file:
- `add_action( 'init', 'autoship_download_csv' )` (line 496)
- `add_action( 'wp_ajax_autoship_initiate_schedule_export', ... )` (line 506)

#### EDIT — `autoship.php` (remove 2 lines)

Remove lines 198-199:

```php
// DELETE:
require_once 'src/export.php';
require_once 'src/import.php';
require_once 'src/upgrade.php';
```

#### DELETE — `src/upgrade.php` (330 LOC)

Delete the entire file. All version migration routines are dead code — all customers are 2.10+, v2→v3 processing version upgrades/downgrades are no longer needed, and pre-1.2.32 upgrade paths are unreachable. The file registered 8 WordPress hooks on every admin page load that all evaluated to no-ops.

#### EDIT — `src/api.php` (remove 2 lines)

In `autoship_qpilot_get_sitemeta()`, remove the legacy origin call and sitemeta key:

```php
// DELETE line 348:
$legacy      = autoship_has_legacy_origin();

// DELETE line 355 (from the $defaults array):
'_qpilot_wc_autoship_legacy'       => $legacy,
```

#### EDIT — `src/admin.php` (2 changes)

**Change 1:** Remove `autoship_migrations_section()` (lines 1654-1661). This function is dead code — the template `admin/migrations` does not exist as a file, and the `autoship-migrations` tab slug is not registered in the default `autoship_admin_settings_tabs()` array (lines 1077-1107):

```php
// DELETE lines 1654-1661:
/**
 * Generates the content for a Autoship Cloud > Settings > Migrations section.
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_migrations_section( $autoship_settings ) {
	autoship_include_template( 'admin/migrations', array( 'autoship_settings' => $autoship_settings ) );
}
```

**Change 2:** Remove `'autoship-migrations'` from the tab exclusion array in `autoship_exclude_submit_on_tab()` (line 1530):

```php
// BEFORE (line 1530):
return ! in_array( $active_tab, array( 'autoship-extensions', 'autoship-utilities', 'autoship-migrations', 'autoship-logs' ), true );

// AFTER:
return ! in_array( $active_tab, array( 'autoship-extensions', 'autoship-utilities', 'autoship-logs' ), true );
```

#### EDIT — `js/batch.js` (remove lines 1-134)

The first `jQuery(document).ready(...)` block (lines 1-134) is the **legacy import batch handler**. It initializes the two import AJAX calls and handles progress/cancel UI for the migrations page.

Lines 136+ contain the **current bulk action processor** (product sync, wholesale pricing, frequency options) and must be kept.

```javascript
// DELETE lines 1-134 (entire first jQuery(document).ready block):
jQuery(document).ready(function ($) {
    // ... cancel button handler ...
    // ... initBatch() function definition ...
    // ... initBatch('autoship_import_schedule_data', '#autoship-import-schedules');
    // ... initBatch('autoship_import_product_settings', '#autoship-import-product-settings');
});
```

The DOM selectors referenced (`#autoship-import-schedules`, `#autoship-import-product-settings`, `.cancel-import-schedules`) only exist in the migrations template which doesn't exist as a file.

#### EDIT — `js/admin.js` (remove lines 365-418)

Remove the `migrationFormsubmit` function and its event binding:

```javascript
// DELETE lines 365-418:
/**
 * Trigger Migration Ajax form submit
 */
var migrationFormsubmit = function( e ) {
    // ... AJAX POST to autoship_initiate_schedule_export ...
};

/**
 * Initiates the Scheduled CSV Export
 */
$('#autoship-bulk-export-csv').on( 'click', 'button.autoship-export-action', migrationFormsubmit );
```

The DOM selector `#autoship-bulk-export-csv` only exists in the migrations template.

### 9.3 Orphaned WordPress Options

These options were written by import/export/upgrade code. Left as harmless orphans:

| Option | Written By | Size | Status |
|--------|-----------|------|--------|
| `autoship_upsert_stats` | `import.php` | Small array | Orphaned |
| `autoship_last_export` | `export.php` | Filename string | Orphaned |
| `_autoship_transition_version` | `upgrade.php` | Version string | Orphaned |
| `autoship_sync_upgrade_activated` | `upgrade.php` (transient) | HTML string | Orphaned |

### 9.4 Orphaned Database Tables

These tables were created by the original WC-Autoship plugin (not by Autoship Cloud). The plugin only reads from them — it never creates or modifies their schema:

| Table | Created By | Recommendation |
|-------|-----------|----------------|
| `wp_wc_autoship_schedules` | Old WC-Autoship plugin | Leave as orphan. No code references it after removal. |
| `wp_wc_autoship_schedule_items` | Old WC-Autoship plugin | Same. |
| `wp_wc_autoship_import` | `import.php` tracking system | Can DROP in upgrade hook — only used during import. |

### 9.5 Orphaned Legacy User/Post Meta

After deletion, this meta remains in the database but is never read or written by any code:

| Meta Type | Key | Originally Read By |
|-----------|-----|--------------------|
| Post meta | `_wc_autoship_enable_autoship` | `import.php` line 259 |
| Post meta | `_wc_autoship_price` | `import.php` lines 404, 462; `export.php` line 283 |
| User meta | `wc_autoship_authorize_net_id` | `import.php` line 666 |
| User meta | `wc_autoship_braintree_id` | `import.php` line 680 |
| User meta | `wc_autoship_cyber_source_id` | `import.php` line 706 |
| User option | `_stripe_customer_id` | `import.php` line 652 (Note: this may still be used by the Stripe plugin itself — do NOT delete) |

Recommendation: Leave all as orphans. Bulk-deleting user/post meta carries risk and the storage cost is negligible.

### 9.6 Risk Assessment

| Risk | Level | Mitigation |
|------|-------|------------|
| Customer still needs migration UI | **Only real risk** | Confirm with QPilot/support. The old WC-Autoship plugin predates the QPilot era — any customer who hasn't migrated by now almost certainly never will. |
| Breaking existing functionality | **Zero** | All deleted functions are only called internally or by JS that targets DOM elements that don't exist. |
| QPilot API rejects missing sitemeta key | **Very Low** | QPilot receives `_qpilot_wc_autoship_legacy` as an informational boolean. API contracts don't enforce presence of optional metadata. Confirm with QPilot team. |
| `format_date()` / `format_time()` name collision | **Positive** | Removing these generic global function names eliminates a potential collision with themes or other plugins. |
| Orphaned JS DOM selectors | **Zero** | `#autoship-import-schedules`, `#autoship-import-product-settings`, `#autoship-bulk-export-csv` only exist in the non-existent migrations template. |
| Third-party extension hooking into import AJAX | **Very Low** | The AJAX actions `autoship_import_schedule_data`, `autoship_import_product_settings`, and `autoship_initiate_schedule_export` are internal. No known extension hooks into them. |

### 9.7 Changeset Summary

| File | Action | Lines Removed |
|------|--------|---------------|
| `src/import.php` | **DELETE file** | 808 |
| `src/export.php` | **DELETE file** | 506 |
| `src/upgrade.php` | **DELETE file** | 330 |
| `autoship.php` | Edit — remove 3 `require_once` | 3 |
| `src/api.php` | Edit — remove 2 lines (legacy flag) | 2 |
| `src/admin.php` | Edit — delete `autoship_migrations_section()`, edit tab exclusion array | 9 |
| `js/batch.js` | Edit — remove lines 1-134 (legacy import batch handler) | 134 |
| `js/admin.js` | Edit — remove lines 365-418 (export AJAX handler) | 54 |
| **Total** | | **~1,846** |

### 9.8 Verification After Removal — PASSED

All verification steps completed on 2026-02-24:

1. **PHPUnit tests** — All 2006 tests pass (10 pre-existing failures in FeatureManagerTest/FeatureFlagManagementTest are unrelated to this change).
2. **Grep for removed functions** — Zero references to `autoship_has_legacy_origin`, `autoship_migrations_section`, `autoship_import_schedule_data`, `autoship_import_product_settings`, `autoship_initiate_schedule_export`, `migrationFormsubmit`, `autoship_version_trigger`, `autoship_convert_site_processing` in any PHP or JS file (only documentation mentions remain).
3. **No dangling `require_once`** — Confirmed `src/export.php`, `src/import.php`, `src/upgrade.php` are not referenced in any PHP file.
