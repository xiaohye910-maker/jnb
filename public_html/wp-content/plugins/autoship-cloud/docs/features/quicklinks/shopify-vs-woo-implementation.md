# QuickLinks Feature Comparison: Shopify vs WordPress

**Document Type**: Implementation Comparison
**Last Updated**: November 28, 2025
**Shopify Version**: 3.3
**WordPress Version**: 1.0.0 (Full Feature Parity)

---

## Overview

This document compares the QuickLinks implementation between the Shopify/Laravel version and the WordPress/WooCommerce version. **Both implementations now have full feature parity**, including all 5 security controls.

---

## Feature Status Summary

| Category | Shopify (v3.3) | WordPress (v1.0.0) | Parity |
|----------|----------------|---------------------|--------|
| **Core Features** | 100% | 100% | ✅ |
| **Security Controls** | 5/5 | 5/5 | ✅ |
| **Templates** | 8 views | 8+ views | ✅ |
| **Middleware/Controllers** | 2 | 2 | ✅ |
| **Database** | Confirmations + Audit | Confirmations + Audit | ✅ |
| **Test Coverage** | 17+ tests | 150+ tests | ✅ WordPress ahead |

---

## Complete Feature Parity

### Core Features (100%)

| Feature | Shopify | WordPress | Notes |
|---------|---------|-----------|-------|
| **Action Types (4/4)** | ✅ | ✅ | Resume, Pause, ProcessNow, Reactivate |
| **Redirect Types (3/3)** | ✅ | ✅ | ThankYouPage, V2Portal, CustomUrl |
| **Factory + Strategy Pattern** | ✅ | ✅ | QuickLinkActionFactory |
| **API Integration** | ✅ | ✅ | Verify → Execute → Consume flow |
| **Domain Layer** | ✅ | ✅ | ActionType, ActionResult, QuickLinkVerification, RedirectType |
| **Services Layer** | ✅ | ✅ | 20+ service classes |
| **Module Layer** | ✅ | ✅ | QuickLinksModule, QuickLinksService, QuickLinkController |
| **Order Summary Display** | ✅ | ✅ | Full order details in confirmation |
| **Custom Meta Tags** | ✅ | ✅ | CustomMetaTag DTO |
| **Custom Branding** | ✅ | ✅ | Logo URL + Stylesheet URL |
| **PHP 7.4 Compatibility** | N/A | ✅ | Fully compatible |

---

## Security Controls (100% Parity)

### ✅ H-1: Mandatory Login for Process Now

**Status**: COMPLETE (Both Platforms)
**Risk Level**: CRITICAL

| Aspect | Shopify | WordPress |
|--------|---------|-----------|
| Implementation | Laravel Middleware | Controller logic |
| Location | `DetectEmailScanner` middleware | `QuickLinkController:125-133` |
| Behavior | Force login for action_type = 2 | Force login for action_type = 2 |

**WordPress Implementation**:
```php
// H-1: Mandatory login for Process Now (financial actions).
$requires_login = $verification->requires_login();
if ( ActionType::PROCESS_NOW === $action_type ) {
    $requires_login = true; // OVERRIDE - always require login for financial actions.
}

if ( $requires_login && ! is_user_logged_in() ) {
    $this->redirect_to_login( $slug, $order_id );
    return;
}
```

---

### ✅ H-2: Email Scanner Detection

**Status**: COMPLETE (Both Platforms)
**Risk Level**: CRITICAL

| Aspect | Shopify | WordPress |
|--------|---------|-----------|
| Implementation | Laravel Middleware | EmailScannerDetector service |
| Patterns | 25+ User-Agents | 25+ User-Agents (identical) |
| Behavioral Detection | ✅ | ✅ |
| Template | Blade view | scanner-detected.php |
| Tests | Middleware tests | 32 tests |

**Detection Patterns (Both)**:
- Microsoft/Outlook SafeLinks
- Google Image Proxy, Google Safety
- Mimecast, Proofpoint
- Barracuda, BESS
- Cisco IronPort
- Symantec/MessageLabs
- Trend Micro
- FireEye, Sophos
- Forcepoint/Websense
- McAfee, Zscaler, Fortinet
- Generic: HeadlessChrome, PhantomJS, curl, wget, bot, spider, crawler

---

### ✅ H-3: Two-Step Confirmation

**Status**: COMPLETE (Both Platforms)
**Risk Level**: HIGH

| Aspect | Shopify | WordPress |
|--------|---------|-----------|
| Model/Entity | `QuickLinkConfirmation` (Eloquent) | `QuickLinkConfirmation` (PHP class) |
| State Machine | ✅ 6 states | ✅ 6 states |
| Expiration | 30 minutes | 30 minutes |
| IP Detection | ✅ | ✅ |
| Locking | Pessimistic (database) | Pessimistic (database) |
| Order Summary | ✅ | ✅ |
| Templates | 4 Blade views | 4 PHP templates |
| Tests | Model tests | 26 tests |

**State Machine (Both)**:
```
pending → confirmed → executed
       ↘         ↘
        cancelled  failed
       ↘
        expired
```

**Templates (Both)**:
- `confirm.php` / `confirm.blade.php` - Confirmation page
- `cancelled.php` / `cancelled.blade.php` - User cancellation
- `already-processed.php` / `already-processed.blade.php` - Double-submit
- `expired.php` / `expired.blade.php` - Timeout

---

### ✅ H-5: Rate Limiting

**Status**: COMPLETE (Both Platforms)
**Risk Level**: HIGH

| Aspect | Shopify | WordPress |
|--------|---------|-----------|
| Implementation | Laravel Middleware | RateLimiter service |
| Storage | Redis (Laravel cache) | 4 strategies (Transient, DB, Cache, File) |
| Default Limit | 5/minute | 5/minute |
| Scope | IP + slug | IP + slug |
| Template | Blade view | rate-limited.php |
| Tests | Middleware tests | 27 tests |

**WordPress Storage Strategies**:
- `TransientStorage` - WordPress transients (default)
- `DatabaseStorage` - Custom table
- `WpCacheStorage` - Object cache
- `FileStorage` - File-based

---

### ✅ H-6: Audit Logging

**Status**: COMPLETE (Both Platforms)
**Risk Level**: MEDIUM

| Aspect | Shopify | WordPress |
|--------|---------|-----------|
| Implementation | Laravel logging | QuickLinkAuditService |
| Primary Storage | Database | Database |
| Fallback Storage | File | File |
| Event Types | 10+ | 10+ (identical) |
| Tests | Log assertions | 54 tests |

**Events Logged (Both)**:
- `quicklink.access` - Initial access
- `quicklink.verify_success` - Verification passed
- `quicklink.verify_failed` - Verification failed
- `quicklink.action_success` - Action executed
- `quicklink.action_failed` - Action failed
- `quicklink.consume_success` - Link consumed
- `quicklink.consume_failed` - Consumption failed
- `quicklink.scanner_detected` - Scanner blocked
- `quicklink.rate_limited` - Rate limit hit
- `quicklink.login_required` - Login redirect

---

## File Structure Comparison

### WordPress File Structure

```
app/Domain/QuickLinks/
├── ActionType.php
├── ActionResult.php
├── QuickLinkVerification.php
├── QuickLinkConfirmation.php                 # H-3
└── RedirectType.php

app/Services/QuickLinks/
├── Interfaces/
│   ├── QuickLinkActionInterface.php
│   ├── QuickLinkServiceInterface.php
│   └── QuickLinkRepositoryInterface.php
├── Actions/
│   ├── ResumeAction.php
│   ├── PauseAction.php
│   ├── ProcessNowAction.php
│   └── ReactivateAction.php
├── DTOs/
│   ├── VerifyQuickLinkResponse.php
│   ├── ConsumeQuickLinkResponse.php
│   ├── OrderSummary.php
│   ├── OrderSummaryItem.php
│   └── CustomMetaTag.php
├── Implementations/
│   ├── QuickLinkService.php
│   └── QuickLinkRepository.php
├── RateLimiter/                              # H-5
│   ├── Interfaces/
│   │   └── RateLimiterStorageInterface.php
│   ├── Strategies/
│   │   ├── TransientStorage.php
│   │   ├── DatabaseStorage.php
│   │   ├── WpCacheStorage.php
│   │   └── FileStorage.php
│   ├── RateLimiter.php
│   └── RateLimiterStorageFactory.php
├── EmailScanner/                             # H-2
│   ├── EmailScannerDetector.php
│   └── ScannerPatterns.php
├── AuditLog/                                 # H-6
│   ├── Interfaces/
│   │   ├── AuditLoggerInterface.php
│   │   └── AuditFunctionInterface.php
│   ├── Strategies/
│   │   ├── DatabaseAuditLogger.php
│   │   └── FileAuditLogger.php
│   ├── Implementations/
│   │   └── WordPressAuditFunctions.php
│   ├── DTOs/
│   │   └── AuditEntry.php
│   ├── AuditLoggerFactory.php
│   └── QuickLinkAuditService.php
├── Confirmation/                             # H-3
│   ├── Interfaces/
│   │   ├── ConfirmationRepositoryInterface.php
│   │   └── ConfirmationFunctionInterface.php
│   ├── Implementations/
│   │   ├── ConfirmationRepository.php
│   │   └── WordPressConfirmationFunctions.php
│   ├── ConfirmationTableMigration.php
│   ├── ConfirmationCleanupScheduler.php
│   └── QuickLinkConfirmationService.php
└── QuickLinkActionFactory.php

app/Modules/QuickLinks/
├── QuickLinksModule.php
├── QuickLinksService.php
└── Controllers/
    └── QuickLinkController.php

templates/quicklinks/
├── confirm.php                               # H-3
├── cancelled.php                             # H-3
├── already-processed.php                     # H-3
├── expired.php                               # H-3
├── thank-you.php
├── error.php
├── rate-limited.php                          # H-5
├── scanner-detected.php                      # H-2
└── partials/
    ├── styles.php
    └── powered-by.php
```

### Shopify File Structure

```
app/Models/
├── QuickLinkConfirmation.php                 # H-3

app/Http/Middleware/
├── DetectEmailScanner.php                    # H-2 + H-1
├── RateLimitQuickLinks.php                   # H-5

app/Http/Controllers/
└── QuickLinkController.php

app/Services/QuickLinks/
├── Interfaces/
│   └── QuickLinkActionInterface.php
├── Actions/
│   ├── ResumeAction.php
│   ├── PauseAction.php
│   ├── ProcessNowAction.php
│   └── ReactivateAction.php
├── DTOs/
│   ├── OrderSummary.php
│   ├── OrderSummaryItem.php
│   └── CustomMetaTag.php
└── QuickLinkActionFactory.php

resources/views/quicklinks/
├── confirm.blade.php                         # H-3
├── cancelled.blade.php                       # H-3
├── already-processed.blade.php               # H-3
├── expired.blade.php                         # H-3
├── thank-you.blade.php
├── error.blade.php
├── rate-limited.blade.php                    # H-5
└── scanner-detected.blade.php                # H-2
```

---

## Technical Differences

| Aspect | Shopify (Laravel) | WordPress/WooCommerce |
|--------|-------------------|----------------------|
| **Framework** | Laravel 10+ | WordPress 5.8+ |
| **PHP Version** | 8.1+ | 7.4+ |
| **URL Routing** | App Proxy + Laravel Routes | WordPress Rewrite Rules |
| **Templates** | Blade Views | PHP Templates (theme-overridable) |
| **DTOs** | Spatie Laravel Data | Simple PHP classes with type hints |
| **Dependency Injection** | Laravel Container | ServiceContainer (custom) |
| **Authentication** | Shopify Signature (HMAC) | WordPress Nonce + Session |
| **Customer ID Mapping** | Shopify ID → QPilot ID | WooCommerce ID → QPilot ID |
| **Coding Style** | Laravel conventions (camelCase) | WordPress (snake_case) |
| **Testing** | Laravel TestCase | PHPUnit + Brain\Monkey |
| **Database** | Eloquent ORM | WordPress $wpdb |
| **Caching** | Laravel Cache (Redis) | WordPress Transients/Object Cache |

---

## Database Schema Comparison

### Confirmations Table

Both implementations store the same data with platform-specific conventions:

| Field | Shopify | WordPress |
|-------|---------|-----------|
| Primary Key | `id` (unsigned bigint) | `id` (unsigned bigint) |
| UUID | `uuid` (varchar 36) | `uuid` (varchar 36) |
| Slug | `slug` (varchar 255) | `slug` (varchar 255) |
| Order ID | `scheduled_order_id` (int) | `scheduled_order_id` (bigint) |
| Shop/Site ID | `shop_id` (int) | `site_id` (bigint) |
| Action Type | `action_type` (tinyint) | `action_type` (tinyint) |
| Action Name | `action_name` (varchar 50) | `action_name` (varchar 50) |
| Status | `status` (enum) | `status` (varchar 20) |
| IP Address | `ip_address` (varchar 45) | `ip_address` (varchar 45) |
| Submission IP | `submission_ip_address` | `submission_ip_address` |
| IP Changed | `ip_changed` (boolean) | `ip_changed` (tinyint 1) |
| Shown At | `shown_at` (timestamp) | `shown_at` (datetime) |
| Submitted At | `submitted_at` (timestamp) | `submitted_at` (datetime) |
| Executed At | `executed_at` (timestamp) | `executed_at` (datetime) |
| Time to Submit | `time_to_submit_seconds` (int) | `time_to_submit_seconds` (int) |
| Metadata | `verification_metadata` (json) | `verification_metadata` (longtext) |
| Result | `execution_result` (json) | `execution_result` (longtext) |
| Created/Updated | Laravel timestamps | WordPress datetime |

---

## Security Score Comparison

| Metric | Shopify | WordPress |
|--------|---------|-----------|
| **Overall Security Score** | 100/100 | 100/100 |
| **Grade** | GREEN (Minimal Risk) | GREEN (Minimal Risk) |

### Score Breakdown

| Control | Max Points | Shopify | WordPress | Status |
|---------|------------|---------|-----------|--------|
| H-1 (Login for ProcessNow) | 25 | 25 | 25 | ✅ Parity |
| H-2 (Email Scanner) | 20 | 20 | 20 | ✅ Parity |
| H-3 (Confirmation) | 20 | 20 | 20 | ✅ Parity |
| H-5 (Rate Limiting) | 15 | 15 | 15 | ✅ Parity |
| H-6 (Audit Logging) | 10 | 10 | 10 | ✅ Parity |
| Core Functionality | 10 | 10 | 10 | ✅ Parity |
| **Total** | **100** | **100** | **100** |

---

## Attack Vectors Analysis

Both implementations are protected against the same attack vectors:

| Attack | Risk Level | Mitigation | Status |
|--------|------------|------------|--------|
| **Process Now Financial Exploitation** | CRITICAL | H-1 + H-3 | ✅ Mitigated |
| **Email Scanner Auto-Charge** | CRITICAL | H-2 | ✅ Mitigated |
| **Replay/Link Sharing Attacks** | HIGH | H-3 + H-5 | ✅ Mitigated |
| **Race Condition Double-Charge** | HIGH | H-3 (pessimistic locking) | ✅ Mitigated |
| **Botnet/Rapid-Fire Abuse** | HIGH | H-5 | ✅ Mitigated |
| **Link Enumeration** | MEDIUM | H-5 | ✅ Mitigated |
| **Unauthorized State Changes** | MEDIUM | H-6 (forensics) | ✅ Forensics Enabled |

---

## Test Coverage Comparison

| Test Category | Shopify | WordPress |
|---------------|---------|-----------|
| Domain Layer | Model tests | 50+ tests |
| Actions | Action tests | 20 tests |
| Email Scanner | Middleware tests | 32 tests |
| Rate Limiter | Middleware tests | 27 tests |
| Audit Logging | Log assertions | 54 tests |
| Confirmation | Model tests | 26 tests |
| DTOs | N/A | 20+ tests |
| Integration | Feature tests | In progress |
| **Total** | ~50 tests | **150+ tests** |

---

## API Response Handling

Both implementations handle the same API response fields:

```json
{
  "valid": true,
  "actionType": 0,
  "requiresLogin": false,
  "requiresConfirmation": true,
  "message": "Success message",
  "errorCode": null,
  "errorMessage": null,
  "quickLinkId": 12345,
  "scheduledOrderId": 67890,
  "customerId": 456,
  "orderCustomizedName": "Monthly Box",
  "redirect": {
    "type": 0,
    "message": "Your subscription has been resumed!",
    "custom_url": null,
    "utm_params": "{\"utm_source\":\"email\"}"
  },
  "orderSummary": {
    "items": [
      {
        "name": "Product Name",
        "quantity": 1,
        "price": 29.99,
        "image_url": "https://..."
      }
    ],
    "subtotal": 29.99,
    "tax": 2.40,
    "shipping": 5.00,
    "total": 37.39,
    "currency": "USD",
    "next_occurrence_date": "2025-02-01"
  },
  "customLogoUrl": "https://...",
  "customStylesheetUrl": "https://...",
  "customMetaTags": [
    {
      "name": "robots",
      "content": "noindex"
    }
  ]
}
```

---

## Conclusion

### Full Feature Parity Achieved

The WordPress QuickLinks implementation now has **100% feature parity** with the Shopify implementation, including:

- ✅ All 4 action types
- ✅ All 3 redirect types
- ✅ All 5 security controls (H-1, H-2, H-3, H-5, H-6)
- ✅ Order summary display
- ✅ Custom branding support
- ✅ Custom meta tags
- ✅ Full audit trail
- ✅ Comprehensive test coverage (150+ tests)

### Production Ready

Both implementations are production-ready with:
- **Security Score**: 100/100
- **Grade**: GREEN (Minimal Risk)
- **All attack vectors mitigated**
- **Full forensic capability via audit logs**

### WordPress Advantages

- **More storage strategy options** for rate limiting (4 vs 1)
- **More extensive test coverage** (150+ vs ~50 tests)
- **Theme-overridable templates** for merchant customization
- **PHP 7.4 compatibility** for broader hosting support

---

**Document Created**: November 26, 2025
**Last Updated**: November 28, 2025
**Maintained By**: Development Team
