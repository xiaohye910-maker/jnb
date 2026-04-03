# QPilot-Powered Feature Flags for Payment Gateways

This document describes the implementation of QPilot-powered feature flags for controlling payment gateway availability across all Autoship Cloud plugin installations.

---

## Overview

Payment gateway feature flags (`payments_gateway_*`) are now resolved through a **two-tier priority chain**, where QPilot acts as the centralized remote authority:

| Priority | Source | Scope | Use Case |
|----------|--------|-------|----------|
| **1 (highest)** | QPilot remote flags | Global + per-site | Centralized rollout control |
| **2 (lowest)** | Hardcoded defaults | All sites (shipped with plugin) | Safe fallback when QPilot is unreachable |

Non-payment features (`quicklaunch`, `product_sync`, etc.) continue to use hardcoded defaults only and are unaffected by this change.

---

## Architecture

### How It Works

```
┌──────────────────────────────────────────────────────────────────┐
│  WordPressFeatureManager::is_enabled('payments_gateway_stripe')  │
└──────────────────────┬───────────────────────────────────────────┘
                       │
         ┌─────────────▼──────────────┐
         │  1. QPilot Remote Flags?   │  GET /api/FeatureFlags
         │     (cached 1 hour)        │  GET /api/Sites/{siteId}/FeatureFlags
         └─────────────┬──────────────┘
                 null   │  array → in_array() check
                       │
         ┌─────────────▼──────────────┐
         │  2. Hardcoded Default      │  $features['payments_gateway_stripe'] => true
         └────────────────────────────┘
```

### QPilot API Endpoints

The plugin calls two QPilot API endpoints and merges the results:

| Endpoint | Auth | Description |
|----------|------|-------------|
| `GET /api/FeatureFlags` | None | Returns globally enabled flags (all sites) |
| `GET /api/Sites/{siteId}/FeatureFlags` | Bearer token | Returns flags enabled for a specific site |

**QPilot's server-side logic:**
- A global flag like `payments_gateway_stripe` enables it for ALL sites.
- A site-specific flag like `payments_gateway_stripe-1781` enables it for site 1781 only.
- `IsFeatureFlagEnabledForSite()` checks global first, then per-site.

### Caching Strategy

- Remote flags are cached in a **WordPress transient** (`autoship_qpilot_feature_flags`) with a **1-hour TTL**.
- Within a single PHP request, the provider callable is invoked **at most once** (in-memory lazy cache).
- If QPilot is unreachable (network error, invalid credentials), the provider returns `null` and hardcoded defaults apply.
- The transient cache is not set on failure, so the next request will retry.

---

## Files Changed

### New Files

| File | Purpose |
|------|---------|
| `app/Services/QPilot/Interfaces/FeatureFlagManagementInterface.php` | Interface for feature flag API operations |
| `app/Services/QPilot/Implementations/FeatureFlagManagement.php` | Implementation calling QPilot endpoints |
| `tests/Services/QPilot/Implementations/FeatureFlagManagementTest.php` | Unit tests for API integration |

### Modified Files

| File | Change |
|------|--------|
| `app/Core/Implementations/WordPressFeatureManager.php` | Added remote flags provider and priority chain logic |
| `app/Core/Plugin.php` | Wired `fetch_qpilot_feature_flags()` as the remote provider |
| `app/Services/QPilot/QPilotServiceClient.php` | Added `FeatureFlagManagement` delegation |
| `app/Services/QPilot/QPilotServiceInterface.php` | Extended with `FeatureFlagManagementInterface` |
| `tests/Core/FeatureManagerTest.php` | Added 8 new tests for remote flags behavior |

---

## Usage

### Enabling a Gateway Globally (All Sites)

In QPilot's feature flag configuration (Azure App Configuration / appsettings.json):

```json
{
  "FeatureManagement": {
    "FeatureFlags": [
      { "Name": "payments_gateway_braintree", "Enabled": true }
    ]
  }
}
```

This enables Braintree for **every site** running this plugin version.

### Enabling a Gateway for a Specific Site

```json
{
  "FeatureManagement": {
    "FeatureFlags": [
      { "Name": "payments_gateway_braintree-1781", "Enabled": true }
    ]
  }
}
```

This enables Braintree **only for site ID 1781**.

### Flag Naming Convention

| WordPress Feature Key | QPilot Global Flag | QPilot Site-Specific Flag |
|-----------------------|--------------------|--------------------------|
| `payments_gateway_stripe` | `payments_gateway_stripe` | `payments_gateway_stripe-{siteId}` |
| `payments_gateway_braintree` | `payments_gateway_braintree` | `payments_gateway_braintree-{siteId}` |
| `payments_gateway_authorize_net` | `payments_gateway_authorize_net` | `payments_gateway_authorize_net-{siteId}` |

---

## Rollout Workflow

### Releasing a New Payment Gateway

1. **Ship code disabled** - The gateway code ships with the plugin, hardcoded default set to `false`.
2. **Test per-site** - Enable the site-specific flag in QPilot for internal/beta sites.
3. **Gradual rollout** - Enable site-specific flags for selected merchant sites.
4. **Global enable** - Enable the global flag in QPilot to activate for all sites.
5. **Harden default** - In a future plugin release, flip the hardcoded default to `true`.

### Emergency Disable

If a gateway has issues after global enablement:

- **QPilot-level**: Remove the global flag. Sites without a site-specific flag fall back to hardcoded defaults.
- **Per-site**: Remove the site-specific flag. That site falls back to hardcoded defaults.

---

## Fallback Behavior

| Scenario | Behavior |
|----------|----------|
| QPilot connected, flags returned | Remote flags are **authoritative** (override defaults) |
| QPilot unreachable (network error) | Hardcoded defaults apply |
| No QPilot credentials configured | Hardcoded defaults apply |
| Transient cache present | Cached flags used (no API call) |

---

## Testing

Run the feature flag tests:

```bash
# Feature flag management (API layer)
vendor/bin/phpunit -c tests/phpunit.xml tests/Services/QPilot/Implementations/FeatureFlagManagementTest.php

# Feature manager (priority chain + remote flags)
vendor/bin/phpunit -c tests/phpunit.xml tests/Core/FeatureManagerTest.php

# Both together
vendor/bin/phpunit -c tests/phpunit.xml --filter "FeatureFlag|FeatureManager"
```

### Test Coverage

| Test | What It Verifies |
|------|-----------------|
| `test_remote_provider_enables_disabled_gateway` | QPilot can enable a gateway that's disabled by default |
| `test_remote_provider_disables_enabled_gateway` | QPilot can disable a gateway that's enabled by default |
| `test_remote_provider_null_falls_back_to_defaults` | Graceful fallback when QPilot is unreachable |
| `test_remote_provider_called_only_once` | In-memory lazy caching works |
| `test_remote_flags_do_not_affect_non_payment_features` | Non-payment flags are unaffected |
| `test_is_payment_gateway_enabled_with_remote_flags` | Gateway ID normalization works with remote flags |
| `test_remote_empty_array_disables_all_payment_gateways` | QPilot returning empty list disables all gateways |
| `test_set_remote_flags_provider_resets_cache` | Changing provider resets the cached result |
