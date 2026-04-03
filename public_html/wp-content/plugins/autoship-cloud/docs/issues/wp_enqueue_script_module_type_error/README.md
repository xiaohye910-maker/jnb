# PHP Fatal Error: wp_enqueue_script_module() Argument Type Mismatch

**Status: RESOLVED IN PRODUCTION**

## Error Message

```
PHP Fatal error: Uncaught TypeError: wp_enqueue_script_module(): Argument #5 ($args) must be of type array, bool given
```

## Location

- **File:** `src/scripts.php`
- **Line:** 61

## Compatibility Requirements

- Plugin must maintain compatibility with **PHP 7.4+**
- This error manifests on **PHP 8.0+** due to stricter type enforcement
- **WordPress:** 6.9
- **WooCommerce:** 10.4.2

## Cause

The `wp_enqueue_script_module()` function was being called with `false` as the 5th argument:

```php
// PROBLEMATIC CODE (old)
wp_enqueue_script_module( 'autoship-v2-portal-main-script', trailingslashit( autoship_get_v2_portal_scripts_url() ) . 'main.js', array(), Autoship_Version, false );
```

The function signature for `wp_enqueue_script_module()` (introduced in WordPress 6.5) is:

```php
wp_enqueue_script_module( string $id, string $src = '', array $deps = array(), string|false|null $version = false, array $args = array() )
```

The 5th parameter `$args` expects an array, not a boolean. The `false` was likely mistakenly carried over from `wp_enqueue_script()`, which uses a boolean 5th parameter to control footer placement.

## Solution (Applied)

The fix was applied by replacing `false` with `array()`:

```php
// FIXED CODE (current production)
wp_enqueue_script_module( 'autoship-v2-portal-main-script', trailingslashit( autoship_get_v2_portal_scripts_url() ) . 'main.js', array(), Autoship_Version, array() );
```

## Environment

- Occurs on WordPress 6.5+ where `wp_enqueue_script_module()` is available
- Triggered when the v2 portal is enabled (`autoship_get_scheduled_orders_display_version()` returns `'v2_portal'`)
- Fatal error during `wp_enqueue_scripts` hook
- Site fails to render (`wp_head`)

## Impact

- **Severity:** Critical (Fatal Error)
- **User Impact:** Site crashes when loading frontend pages with the v2 portal enabled
- **Affected Environments:** All sites using this plugin version on WordPress 6.5+ with PHP 8.0+

## Support Team Notes

### Findings

- Switching themes and deactivating plugins revealed that deactivating **Yotpo** prevented the critical error from appearing, but this is an **interaction, not a root cause**
- Yotpo forces product pricing evaluation and variation rendering, which exposes Autoship's PHP 8 incompatibilities
- The underlying issue remains in the plugin code regardless of Yotpo

### Root Cause Analysis

This is a **PHP 8+ compatibility issue**:
- PHP 8+ enforces stricter type checking
- The plugin assumption (passing boolean to array parameter) no longer holds in PHP 8+
- This mirrors legacy `wp_enqueue_script()` usage but is invalid for script modules

### Related Issue

This issue is part of a set of PHP 8 compatibility problems. See also:
- [Division by Zero in Percent Discount Calculation](../division_by_zero_percent_discount/README.md)
