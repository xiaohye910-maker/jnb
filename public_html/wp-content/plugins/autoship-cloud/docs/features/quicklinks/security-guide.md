# QuickLinks Security Guide

**Version**: 1.0.0
**Last Updated**: November 28, 2025
**Security Score**: 100/100 (GREEN - Minimal Risk)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Security Score](#security-score)
3. [H-1: Mandatory Login for Process Now](#h-1-mandatory-login-for-process-now)
4. [H-2: Email Scanner Detection](#h-2-email-scanner-detection)
5. [H-3: Two-Step Confirmation](#h-3-two-step-confirmation)
6. [H-5: Rate Limiting](#h-5-rate-limiting)
7. [H-6: Audit Logging](#h-6-audit-logging)
8. [Attack Vectors Analysis](#attack-vectors-analysis)
9. [Configuration](#configuration)
10. [Security Testing](#security-testing)
11. [Incident Response](#incident-response)

---

## Overview

QuickLinks implements a **defense-in-depth** security strategy with 5 security controls that work together to protect against various attack vectors.

### Security Architecture

```
┌─────────────────────────────────────────────┐
│  Layer 1: Rate Limiting (H-5)               │
│  • Prevents brute force attacks             │
│  • 5 requests per minute per IP/slug        │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 2: Email Scanner Detection (H-2)     │
│  • Blocks automated email prefetch          │
│  • 25+ known scanner patterns               │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 3: Mandatory Login (H-1)             │
│  • Process Now requires authentication      │
│  • Protects financial actions               │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 4: Two-Step Confirmation (H-3)       │
│  • User must confirm before execution       │
│  • 30-minute expiration                     │
│  • IP change detection                      │
└─────────────────┬───────────────────────────┘
                  │ passed
┌─────────────────▼───────────────────────────┐
│  Layer 5: Audit Logging (H-6)               │
│  • All events logged for forensics          │
│  • Dual storage (DB + file fallback)        │
└─────────────────────────────────────────────┘
```

---

## Security Score

### Current Status: 100/100 (GREEN)

| Control | Max Points | Score | Status |
|---------|------------|-------|--------|
| H-1 (Mandatory Login) | 25 | 25 | ✅ Complete |
| H-2 (Email Scanner) | 20 | 20 | ✅ Complete |
| H-3 (Two-Step Confirmation) | 20 | 20 | ✅ Complete |
| H-5 (Rate Limiting) | 15 | 15 | ✅ Complete |
| H-6 (Audit Logging) | 10 | 10 | ✅ Complete |
| Core Functionality | 10 | 10 | ✅ Complete |
| **Total** | **100** | **100** | **GREEN** |

### Risk Assessment

| Grade | Score Range | Description |
|-------|-------------|-------------|
| **GREEN** | 90-100 | Minimal Risk - All critical controls implemented |
| YELLOW | 70-89 | Low Risk - Most controls implemented |
| ORANGE | 50-69 | Medium Risk - Core controls missing |
| RED | 0-49 | High Risk - Critical vulnerabilities |

---

## H-1: Mandatory Login for Process Now

### Risk Level: CRITICAL

**Attack Mitigated**: Unauthorized financial charges from intercepted QuickLinks

### The Problem

Process Now (action_type = 2) triggers immediate payment processing. Without mandatory authentication:
- Anyone with the link could charge a customer's stored payment method
- Leaked links (forwarded emails, screenshots) become attack vectors
- Email scanners could accidentally trigger charges

### Implementation

**Location**: `app/Modules/QuickLinks/Controllers/QuickLinkController.php`

```php
// H-1: Mandatory login for Process Now (financial actions).
// Process Now (action_type = 2) ALWAYS requires login regardless of API settings.
$requires_login = $verification->requires_login();

if ( ActionType::PROCESS_NOW === $action_type ) {
    $requires_login = true; // OVERRIDE - always require login for financial actions.
}

if ( $requires_login && ! is_user_logged_in() ) {
    $this->audit_service->log_login_required( $slug, $order_id, $ip_address, $user_agent );
    $this->redirect_to_login( $slug, $order_id );
    return;
}
```

### Key Points

1. **Overrides API Setting**: Even if QPilot API returns `requiresLogin: false`, Process Now still requires login
2. **Login Redirect**: Unauthenticated users are redirected to login with return URL
3. **Audit Logged**: All login redirects are logged for forensics

### Testing

```bash
# Run H-1 specific tests
vendor/bin/phpunit --filter="MandatoryLogin"
```

---

## H-2: Email Scanner Detection

### Risk Level: CRITICAL

**Attack Mitigated**: Email security scanners accidentally triggering subscription actions

### The Problem

Enterprise email systems (Outlook SafeLinks, Mimecast, etc.) automatically scan links in emails:
- Scanners make HTTP requests to validate links
- This can accidentally trigger Process Now charges
- Users may not realize their subscription was modified

### Implementation

**Location**: `app/Services/QuickLinks/EmailScanner/`

```php
// EmailScannerDetector.php
class EmailScannerDetector {
    public function is_scanner( string $user_agent, array $headers = array() ): bool {
        // Layer 1: Allow known browsers
        if ( $this->is_allowed_browser( $user_agent ) ) {
            return false;
        }

        // Layer 2: Check scanner patterns
        if ( $this->matches_scanner_pattern( $user_agent ) ) {
            return true;
        }

        // Layer 3: Behavioral detection
        return $this->has_suspicious_behavior( $headers );
    }
}
```

### Detection Patterns

**Scanner Patterns** (from `ScannerPatterns.php`):
- Microsoft/Outlook: `outlook-safelinks`, `Microsoft Office Protocol`
- Google: `GoogleImageProxy`, `Google-Safety`
- Mimecast: `Mimecast`
- Proofpoint: `Proofpoint`
- Barracuda: `Barracuda`, `BESS`
- Cisco: `IronPort`
- Symantec: `Symantec`, `MessageLabs`
- And 15+ more patterns...

**Browser Allow-List**:
- Chrome (excludes HeadlessChrome)
- Firefox
- Safari (excludes Chrome-based)
- Edge
- Internet Explorer
- Opera

**Behavioral Detection**:
- Missing `Accept-Language` header
- Missing `text/html` in Accept header
- Configurable suspicion threshold

### Configuration

```php
// Default scanner config (stored in autoship_quicklinks_scanner_config option)
$config = array(
    'enabled' => true,
    'block_actions' => array( 2 ), // Only block Process Now
    'behavioral_detection' => array(
        'enabled' => true,
        'require_accept_language' => true,
        'require_html_accept' => true,
        'min_suspicious_count' => 2,
    ),
);
```

### Response

When a scanner is detected:
1. Request is blocked (for configured action types)
2. `scanner-detected.php` template is displayed
3. Event is logged to audit log
4. Page includes JavaScript for real browser auto-retry

---

## H-3: Two-Step Confirmation

### Risk Level: HIGH

**Attacks Mitigated**:
- Race condition double-charges
- Link sharing/replay attacks
- Accidental clicks

### The Problem

Single-click execution is vulnerable to:
- Double-clicks causing duplicate actions
- Shared links being used multiple times
- Users accidentally clicking before reading

### Implementation

**Location**: `app/Services/QuickLinks/Confirmation/`

#### State Machine

```
pending → confirmed → executed
       ↘         ↘
        cancelled  failed
       ↘
        expired
```

#### Entity: QuickLinkConfirmation

```php
// app/Domain/QuickLinks/QuickLinkConfirmation.php
class QuickLinkConfirmation {
    // Status constants
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_EXECUTED  = 'executed';
    public const STATUS_FAILED    = 'failed';

    // State transitions
    public function mark_confirmed( string $submission_ip ): bool {
        if ( $this->status !== self::STATUS_PENDING ) {
            return false;
        }
        $this->status = self::STATUS_CONFIRMED;
        $this->submission_ip_address = $submission_ip;
        $this->ip_changed = ( $this->ip_address !== $submission_ip );
        $this->submitted_at = new \DateTime();
        return true;
    }
}
```

#### Flow

1. **Initial Request**: Create confirmation in `pending` state
2. **Show Confirmation Page**: Display order summary and action details
3. **User Clicks Confirm**: Transition to `confirmed` state
4. **Execute Action**: Call QPilot API
5. **Mark Result**: Transition to `executed` or `failed`

### Security Features

**30-Minute Expiration**:
```php
public function is_expired(): bool {
    if ( $this->status !== self::STATUS_PENDING ) {
        return $this->status === self::STATUS_EXPIRED;
    }
    $expiry = clone $this->shown_at;
    $expiry->modify( '+30 minutes' );
    return new \DateTime() > $expiry;
}
```

**IP Change Detection**:
```php
// Detects if user confirms from different IP than initial request
// Could indicate link was shared or used from different network
$this->ip_changed = ( $this->ip_address !== $submission_ip );
```

**Pessimistic Locking**:
```php
// ConfirmationRepository uses database transactions
$wpdb->query( 'START TRANSACTION' );
// ... update confirmation ...
$wpdb->query( 'COMMIT' );
```

### Database Schema

```sql
CREATE TABLE {prefix}autoship_quicklink_confirmations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    slug VARCHAR(255) NOT NULL,
    scheduled_order_id BIGINT NOT NULL,
    site_id BIGINT NOT NULL,
    customer_id BIGINT NULL,
    wp_user_id BIGINT NULL,
    action_type TINYINT NOT NULL,
    action_name VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    ip_address VARCHAR(45),
    submission_ip_address VARCHAR(45) NULL,
    ip_changed TINYINT(1) DEFAULT 0,
    user_agent TEXT,
    shown_at DATETIME NOT NULL,
    submitted_at DATETIME NULL,
    executed_at DATETIME NULL,
    time_to_submit_seconds INT NULL,
    verification_metadata LONGTEXT,
    execution_result LONGTEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Cleanup Scheduler

Expired confirmations are cleaned up automatically:

```php
// ConfirmationCleanupScheduler.php
// Runs daily via WordPress cron
// Deletes confirmations older than retention period (default 30 days)
```

---

## H-5: Rate Limiting

### Risk Level: HIGH

**Attacks Mitigated**:
- Brute force attacks
- Botnet/rapid-fire abuse
- Link enumeration

### Implementation

**Location**: `app/Services/QuickLinks/RateLimiter/`

```php
// RateLimiter.php
class RateLimiter {
    public function is_limited( string $ip_address, string $slug ): bool {
        $key = $this->get_key( $ip_address, $slug );
        $attempts = $this->storage->get( $key );
        return $attempts >= $this->max_attempts;
    }

    public function record_attempt( string $ip_address, string $slug ): void {
        $key = $this->get_key( $ip_address, $slug );
        $this->storage->increment( $key, $this->window_seconds );
    }

    public function get_retry_after( string $ip_address, string $slug ): int {
        $key = $this->get_key( $ip_address, $slug );
        return $this->storage->get_ttl( $key );
    }
}
```

### Configuration

```php
// Default rate limiter config (stored in autoship_quicklinks_rate_limiter option)
$config = array(
    'enabled' => true,
    'strategy' => 'transient',
    'max_attempts' => 5,
    'window_seconds' => 60,
    'settings' => array(
        'transient' => array(),
        'database' => array( 'table_name' => 'autoship_rate_limits' ),
        'wp_cache' => array( 'group' => 'autoship_ratelimit' ),
        'file' => array( 'directory' => '' ),
    ),
);
```

### Storage Strategies

| Strategy | Use Case | Pros | Cons |
|----------|----------|------|------|
| **TransientStorage** | Default | Simple, no config | Single server only |
| **DatabaseStorage** | Persistent | Survives restarts | Slower |
| **WpCacheStorage** | Object cache | Fast, distributed | Requires Redis/Memcached |
| **FileStorage** | Debug/fallback | Always works | Disk I/O |

### Response

When rate limited:
1. HTTP 429 response with `Retry-After` header
2. `rate-limited.php` template shown
3. Countdown timer for auto-retry
4. Event logged to audit log

---

## H-6: Audit Logging

### Risk Level: MEDIUM

**Purpose**: Security event tracking for forensics and incident response

### Implementation

**Location**: `app/Services/QuickLinks/AuditLog/`

```php
// QuickLinkAuditService.php
class QuickLinkAuditService {
    public function log_access( ... ): void;
    public function log_verify_success( ... ): void;
    public function log_verify_failed( ... ): void;
    public function log_action_success( ... ): void;
    public function log_action_failed( ... ): void;
    public function log_consume_success( ... ): void;
    public function log_consume_failed( ... ): void;
    public function log_scanner_detected( ... ): void;
    public function log_rate_limited( ... ): void;
    public function log_login_required( ... ): void;
}
```

### Events Logged

| Event | Description | Severity |
|-------|-------------|----------|
| `quicklink.access` | Initial request received | INFO |
| `quicklink.verify_success` | API verification passed | INFO |
| `quicklink.verify_failed` | API verification failed | WARNING |
| `quicklink.action_success` | Action executed successfully | INFO |
| `quicklink.action_failed` | Action execution failed | ERROR |
| `quicklink.consume_success` | Link consumed | INFO |
| `quicklink.consume_failed` | Consumption failed | WARNING |
| `quicklink.scanner_detected` | Email scanner blocked | WARNING |
| `quicklink.rate_limited` | Rate limit exceeded | WARNING |
| `quicklink.login_required` | Login redirect triggered | INFO |

### Data Captured

```php
// AuditEntry.php - Data captured for each event
$entry = new AuditEntry();
$entry->set_event_type( 'quicklink.action_success' )
      ->set_ip_address( '192.168.1.1' )
      ->set_user_agent( 'Mozilla/5.0...' )
      ->set_customer_id( 456 )
      ->set_wp_user_id( 123 )
      ->set_site_id( 789 )
      ->set_slug( 'resume-now' )
      ->set_scheduled_order_id( 12345 )
      ->set_action_type( ActionType::RESUME )
      ->set_status( 'success' )
      ->set_context( array( 'extra' => 'data' ) );
```

### Dual Storage Strategy

```php
// AuditLoggerFactory.php
// Primary: Database storage
// Fallback: File storage (if DB fails)

try {
    $primary_logger->log( $entry );
} catch ( \Exception $e ) {
    $fallback_logger->log( $entry );
}
```

### Retention

- Default: 90 days
- Configurable via `autoship_quicklinks_audit_config` option
- Cleanup runs via WordPress cron

---

## Attack Vectors Analysis

### All Attack Vectors Mitigated

| Attack | Risk | Mitigation | Status |
|--------|------|------------|--------|
| **Process Now Exploitation** | CRITICAL | H-1 (mandatory login) + H-3 (confirmation) | ✅ Mitigated |
| **Email Scanner Auto-Charge** | CRITICAL | H-2 (scanner detection) | ✅ Mitigated |
| **Replay/Link Sharing** | HIGH | H-3 (one-time use confirmation) | ✅ Mitigated |
| **Race Condition Double-Charge** | HIGH | H-3 (pessimistic locking) | ✅ Mitigated |
| **Botnet/Brute Force** | HIGH | H-5 (rate limiting) | ✅ Mitigated |
| **Link Enumeration** | MEDIUM | H-5 (rate limiting) | ✅ Mitigated |
| **Forensic Investigation** | MEDIUM | H-6 (audit logging) | ✅ Enabled |

---

## Configuration

### All Security Settings

```php
// Rate Limiter
update_option( 'autoship_quicklinks_rate_limiter', array(
    'enabled' => true,
    'strategy' => 'transient',
    'max_attempts' => 5,
    'window_seconds' => 60,
) );

// Email Scanner Detection
update_option( 'autoship_quicklinks_scanner_config', array(
    'enabled' => true,
    'block_actions' => array( 2 ), // Process Now only
    'behavioral_detection' => array(
        'enabled' => true,
        'min_suspicious_count' => 2,
    ),
) );

// Audit Logging
update_option( 'autoship_quicklinks_audit_config', array(
    'enabled' => true,
    'primary_strategy' => 'database',
    'fallback_strategy' => 'file',
    'retention_days' => 90,
) );
```

---

## Security Testing

### Run All Security Tests

```bash
# All QuickLinks tests
composer test

# Specific security tests
vendor/bin/phpunit --filter="RateLimiter"
vendor/bin/phpunit --filter="EmailScanner"
vendor/bin/phpunit --filter="Confirmation"
vendor/bin/phpunit --filter="AuditLog"
```

### Manual Testing Checklist

- [ ] Rate limiting blocks after 5 requests
- [ ] Email scanner patterns are detected
- [ ] Process Now requires login
- [ ] Confirmation page displays order summary
- [ ] Confirmation expires after 30 minutes
- [ ] IP change is detected and logged
- [ ] Audit log captures all events
- [ ] Double-click protection works

---

## Incident Response

### Investigating Suspicious Activity

1. **Check Audit Log**:
```sql
SELECT * FROM {prefix}autoship_quicklinks_audit_log
WHERE ip_address = '192.168.1.1'
ORDER BY created_at DESC;
```

2. **Look for Patterns**:
- Multiple failed verifications from same IP
- Rate limit events clustering
- Scanner detections followed by real requests
- IP changes on confirmations

3. **Response Actions**:
- Block IP at firewall level if attacking
- Reset rate limit if legitimate user blocked
- Review confirmation with IP change for fraud

### Emergency Contacts

If a security incident is discovered:
1. Document the incident (timestamps, IPs, affected orders)
2. Check audit logs for full activity history
3. Escalate to security team
4. Consider temporarily disabling QuickLinks if active attack

---

## 🔗 Related Documentation

- [README](README.md) - Overview and quick start
- [Architecture](references/architecture.md) - Technical architecture
- [Developer Guide](developer-guide.md) - Implementation guide
- [Testing Guide](testing-guide.md) - Testing procedures
- [Shopify Comparison](shopify-vs-woo-implementation.md) - Feature parity

---

**Last Updated**: November 28, 2025
**Security Version**: 1.0.0
**Maintained By**: Development Team
