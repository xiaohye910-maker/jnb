# QuickLinks WordPress/WooCommerce Documentation

**Feature Status**: ✅ Production Ready
**Last Updated**: November 28, 2025
**Version**: 1.0.0

---

## 🎯 What is QuickLinks?

QuickLinks enables customers to manage their subscriptions through secure, one-click URLs sent via email or SMS. Customers can resume, pause, process, or reactivate their subscriptions with a two-step confirmation flow for maximum security.

**Supported Actions**: Resume (0) • Pause (1) • Process Now (2) • Reactivate (3)

---

## 📚 Documentation Map

### For Developers

| Need | Document | Description |
|------|----------|-------------|
| 🚀 **Getting Started** | [Developer Guide](developer-guide.md) | Complete guide to implementing QuickLinks |
| 🏗️ **Architecture** | [Architecture](references/architecture.md) | WordPress-specific design patterns and decisions |
| 🧪 **Testing** | [Testing Guide](testing-guide.md) | Testing procedures and patterns |
| 🔒 **Security** | [Security Guide](security-guide.md) | Security controls and best practices |
| 📝 **Coding Standards** | [Coding Standards](references/coding-standards.md) | WordPress Coding Standards compliance guide |

### Reference Implementation

| Need | Document | Description |
|------|----------|-------------|
| 🔍 **Implementation Comparison** | [Shopify vs WooCommerce](shopify-vs-woo-implementation.md) | Feature parity comparison |

---

## ⚡ Quick Overview

### WordPress URL Format
```
https://{site-domain}/autoship/l/{slug}/{order-id}?token={optional}
```

### Example
```
https://mystore.com/autoship/l/resume-now/12345
```

### Request Flow (Two-Step Confirmation)
```
Customer clicks link
  ↓
WordPress Rewrite Rules → QuickLinkController
  ↓
Rate Limiting Check (H-5)
  ↓
Email Scanner Detection (H-2)
  ↓
QPilot API: Verify QuickLink
  ↓
Create QuickLinkConfirmation (H-3)
  ↓
Show Confirmation Page with Order Summary
  ↓
Customer clicks "Confirm" button
  ↓
Validate Confirmation (check expiry, IP)
  ↓
Execute Action (Resume/Pause/ProcessNow/Reactivate)
  ↓
QPilot API: Record consumption
  ↓
Audit Log Entry (H-6)
  ↓
Show success template or redirect
```

---

## 🏗️ Architecture Pattern

QuickLinks follows the **Nextime Architecture Pattern** used throughout the plugin:

```
app/
├── Domain/QuickLinks/                    # Value Objects & Enums
│   ├── ActionType.php
│   ├── ActionResult.php
│   ├── QuickLinkVerification.php
│   ├── QuickLinkConfirmation.php         # H-3: Two-step confirmation entity
│   └── RedirectType.php
│
├── Services/QuickLinks/                  # Business Logic
│   ├── Interfaces/
│   │   ├── QuickLinkActionInterface.php
│   │   ├── QuickLinkServiceInterface.php
│   │   └── QuickLinkRepositoryInterface.php
│   ├── Implementations/
│   │   ├── QuickLinkService.php
│   │   └── QuickLinkRepository.php
│   ├── Actions/
│   │   ├── ResumeAction.php
│   │   ├── PauseAction.php
│   │   ├── ProcessNowAction.php
│   │   └── ReactivateAction.php
│   ├── DTOs/
│   │   ├── VerifyQuickLinkResponse.php
│   │   ├── ConsumeQuickLinkResponse.php
│   │   ├── OrderSummary.php
│   │   ├── OrderSummaryItem.php
│   │   └── CustomMetaTag.php
│   ├── RateLimiter/                      # H-5: Rate Limiting
│   │   ├── Interfaces/
│   │   │   └── RateLimiterStorageInterface.php
│   │   ├── Strategies/
│   │   │   ├── TransientStorage.php
│   │   │   ├── DatabaseStorage.php
│   │   │   ├── WpCacheStorage.php
│   │   │   └── FileStorage.php
│   │   ├── RateLimiter.php
│   │   └── RateLimiterStorageFactory.php
│   ├── EmailScanner/                     # H-2: Email Scanner Detection
│   │   ├── EmailScannerDetector.php
│   │   └── ScannerPatterns.php
│   ├── AuditLog/                         # H-6: Audit Logging
│   │   ├── Interfaces/
│   │   │   ├── AuditLoggerInterface.php
│   │   │   └── AuditFunctionInterface.php
│   │   ├── Strategies/
│   │   │   ├── DatabaseAuditLogger.php
│   │   │   └── FileAuditLogger.php
│   │   ├── Implementations/
│   │   │   └── WordPressAuditFunctions.php
│   │   ├── DTOs/
│   │   │   └── AuditEntry.php
│   │   ├── AuditLoggerFactory.php
│   │   └── QuickLinkAuditService.php
│   ├── Logging/                          # Centralized logging
│   │   └── Logger.php                    # Uses Logger::info(), Logger::error()
│   ├── Confirmation/                     # H-3: Two-Step Confirmation
│   │   ├── Interfaces/
│   │   │   ├── ConfirmationRepositoryInterface.php
│   │   │   └── ConfirmationFunctionInterface.php
│   │   ├── Implementations/
│   │   │   ├── ConfirmationRepository.php
│   │   │   └── WordPressConfirmationFunctions.php
│   │   ├── ConfirmationTableMigration.php
│   │   ├── ConfirmationCleanupScheduler.php
│   │   └── QuickLinkConfirmationService.php
│   └── QuickLinkActionFactory.php
│
└── Modules/QuickLinks/                   # WordPress Integration
    ├── QuickLinksModule.php
    ├── QuickLinksService.php
    └── Controllers/
        └── QuickLinkController.php

templates/quicklinks/                     # Theme-overridable templates
├── confirm.php                           # H-3: Confirmation page
├── cancelled.php                         # H-3: Cancellation page
├── already-processed.php                 # H-3: Double-submit protection
├── expired.php                           # H-3: Expired confirmation
├── thank-you.php                         # Success page
├── error.php                             # Error page
├── rate-limited.php                      # H-5: Rate limit exceeded
├── scanner-detected.php                  # H-2: Scanner blocked
└── partials/
    ├── styles.php                        # Shared styles
    └── powered-by.php                    # Footer branding
```

---

## 🎬 Action Types

| Type | ID | Name | QPilot Endpoint | Strategy Class |
|------|----|----|-----------------|----------------|
| Resume | 0 | Resume | `PUT /ScheduledOrders/{id}/Status/Active` | `ResumeAction` |
| Pause | 1 | Pause | `PUT /ScheduledOrders/{id}/Status/Paused` | `PauseAction` |
| Process Now | 2 | ProcessNow | `POST /ScheduledOrders/{id}/Retry` | `ProcessNowAction` |
| Reactivate | 3 | Reactivate | `PUT /ScheduledOrders/{id}/SafeActivate` | `ReactivateAction` |

---

## 🔄 Redirect Types

| Type | ID | Name | Behavior |
|------|----|----|----------|
| Thank You Page | 0 | ThankYouPage | Show success template with custom message |
| V2 Portal | 1 | V2Portal | Redirect to customer portal subscription page |
| Custom URL | 2 | CustomUrl | Redirect to merchant-specified URL with UTM params |

---

## 🔒 Security Controls

QuickLinks implements defense-in-depth with 5 security layers:

| Control | ID | Status | Description |
|---------|-----|--------|-------------|
| Mandatory Login | H-1 | ✅ Complete | Force login for Process Now (financial actions) |
| Email Scanner Detection | H-2 | ✅ Complete | Block automated email scanner prefetch |
| Two-Step Confirmation | H-3 | ✅ Complete | Confirmation page with order summary |
| Rate Limiting | H-5 | ✅ Complete | 5 requests per minute per IP/slug |
| Audit Logging | H-6 | ✅ Complete | Full security event logging |

**Security Score**: 100/100 (Full parity with Shopify implementation)

See [Security Guide](security-guide.md) for detailed implementation.

---

## 📊 Implementation Status

### Phase 1: Domain Layer ✅
- [x] ActionType enum
- [x] ActionResult value object
- [x] QuickLinkVerification value object
- [x] QuickLinkConfirmation entity (H-3)
- [x] RedirectType enum

### Phase 2: Services Layer ✅
- [x] Interfaces (QuickLinkActionInterface, QuickLinkServiceInterface, QuickLinkRepositoryInterface)
- [x] Action implementations (Resume, Pause, ProcessNow, Reactivate)
- [x] DTOs (VerifyQuickLinkResponse, ConsumeQuickLinkResponse, OrderSummary, OrderSummaryItem, CustomMetaTag)
- [x] QuickLinkActionFactory
- [x] QuickLinkService implementation
- [x] QuickLinkRepository implementation
- [x] RateLimiter with 4 storage strategies (H-5)
- [x] EmailScannerDetector with 25+ patterns (H-2)
- [x] AuditLog with dual-storage strategy (H-6)
- [x] Confirmation service and repository (H-3)

### Phase 3: Module Layer ✅
- [x] QuickLinksModule
- [x] QuickLinksService
- [x] QuickLinkController
- [x] URL rewrite rules registration

### Phase 4: Templates ✅
- [x] confirm.php (H-3)
- [x] cancelled.php (H-3)
- [x] already-processed.php (H-3)
- [x] expired.php (H-3)
- [x] thank-you.php
- [x] error.php
- [x] rate-limited.php (H-5)
- [x] scanner-detected.php (H-2)
- [x] partials/styles.php
- [x] partials/powered-by.php

### Phase 5: Testing ✅
- [x] Domain layer tests (50+ tests)
- [x] Services layer tests (100+ tests)
- [x] Module layer tests
- [x] Security control tests
- [x] Integration tests
- [x] Total: 651 tests, 1453 assertions

### Phase 6: Configuration ✅
- [x] Feature flag registration
- [x] Module registration in Plugin.php
- [x] Database migrations for confirmations table
- [x] Cleanup scheduler for expired confirmations

### Phase 7: Logging & Debugging ✅
- [x] Detailed action logging (Resume, Pause, ProcessNow, Reactivate)
- [x] Repository API call logging (change_status, safe_activate, retry_order)
- [x] Audit service debug logging
- [x] Database audit logger error capture

---

## 🔗 Key Differences from Shopify Implementation

| Aspect | Shopify (Laravel) | WordPress/WooCommerce |
|--------|-------------------|----------------------|
| **Framework** | Laravel | WordPress/WooCommerce |
| **URL Routing** | App Proxy + Laravel Routes | WordPress Rewrite Rules |
| **Templates** | Blade Views | PHP Templates (theme-overridable) |
| **DTOs** | Spatie Laravel Data | Simple PHP classes with type hints |
| **Dependency Injection** | Laravel Container | ServiceContainer (custom) |
| **Validation** | Shopify Signature (HMAC) | WordPress Nonce + Optional Token |
| **Customer ID** | Shopify Customer ID → QPilot ID | WooCommerce Customer ID → QPilot Customer ID |
| **Coding Style** | Laravel conventions | WordPress Coding Standards (WPCS 3.0) |
| **Testing** | Laravel TestCase | PHPUnit + Brain\Monkey |
| **Security Parity** | 5/5 controls | 5/5 controls (100% parity) |

---

## 🚀 Getting Started

### Prerequisites

1. WordPress 5.8+
2. WooCommerce 5.0+
3. PHP 7.4+
4. Composer dependencies installed
5. QPilot API credentials configured

### Quick Start

1. **Read Architecture**: Understand the [Architecture](references/architecture.md)
2. **Review Security**: Check [Security Guide](security-guide.md)
3. **Follow Developer Guide**: Use [Developer Guide](developer-guide.md) for coding
4. **Write Tests**: Follow [Testing Guide](testing-guide.md)
5. **Verify Standards**: Check [Coding Standards](references/coding-standards.md) for compliance

---

## 🛠️ Development Workflow

```bash
# 1. Create feature branch
git checkout -b feature/quicklinks-enhancement

# 2. Run tests
composer test

# 3. Check coding standards compliance
composer compliance

# 4. Create pull request
```

---

## ✅ Compliance Checklist

Before submitting code:

- [ ] All classes have proper DocBlocks
- [ ] Methods use `snake_case` naming
- [ ] Indentation uses TABS (not spaces)
- [ ] All output is escaped (`esc_html()`, `esc_url()`, etc.)
- [ ] All strings are translatable (`__()`, `esc_html__()`, etc.)
- [ ] `composer compliance` passes with no errors
- [ ] `composer test` passes all tests
- [ ] Code follows Nextime architecture pattern

---

## 🆘 Getting Help

### Something Not Working?
1. Check [Testing Guide](testing-guide.md) for debugging tips
2. Review WordPress logs in `wp-content/debug.log`
3. Verify QPilot API credentials and connectivity

### Need Clarification?
1. Review [Architecture](references/architecture.md) for design decisions
2. Check [Developer Guide](developer-guide.md) for code examples
3. Reference [Security Guide](security-guide.md) for security controls

### Contributing?
1. Follow [Coding Standards](references/coding-standards.md)
2. Write tests following [Testing Guide](testing-guide.md)
3. Submit PR with clear description of changes

---

## 🔗 External Resources

- **WordPress Rewrite API**: https://developer.wordpress.org/reference/functions/add_rewrite_rule/
- **WordPress Coding Standards**: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
- **PHPUnit Documentation**: https://phpunit.de/documentation.html
- **Brain\Monkey**: https://giuseppe-mazzapica.gitbook.io/brain-monkey/
- **QPilot API Documentation**: (Internal)

---

**Maintained By**: Development Team
**Documentation Version**: 1.0.0
**Last Review**: November 28, 2025
