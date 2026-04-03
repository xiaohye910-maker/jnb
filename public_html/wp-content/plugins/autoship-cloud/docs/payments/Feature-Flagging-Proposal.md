# Feature Flagging Proposal for Payment Gateway Releases

This document outlines different options for implementing feature flagging to enable rapid, silent releases of individual payment gateway implementations.

---

## Current Architecture Analysis

### Existing Infrastructure

The codebase already has a three-level control structure:

| Level | Component | Current Capability |
|-------|-----------|-------------------|
| **Module** | `FeatureManager` | Toggle entire `payments` module on/off |
| **Service** | `PaymentGatewayRegistry` | Register/unregister gateways dynamically |
| **Compatibility** | `PaymentsCompatibilityManager` | Per-gateway `set_compatibility_enabled()` |

### Key Insight: Compatibility Layer is the Control Point

The `AbstractPaymentCompatibility` class already has:
```php
protected bool $enabled = true;  // Per-gateway toggle

public function is_enabled(): bool { return $this->enabled; }
public function set_enabled( bool $enabled ): void { $this->enabled = $enabled; }
```

And `PaymentsCompatibilityManager` provides:
```php
public static function set_compatibility_enabled( string $gateway_id, bool $enabled ): bool
public static function get_compatibility_status(): array
```

**The compatibility layer is where legacy code meets new code** - controlling it effectively gates whether new implementations are used.

---

## Proposed Options

### Option 1: WordPress Options (Database-Driven)

**Concept:** Store feature flags in `wp_options` table, read at compatibility layer initialization.

```php
// Stored as: autoship_payment_gateway_flags
[
    'stripe'                        => true,   // New implementation enabled
    'stripe_sepa'                   => true,
    'authorize_net_cim_credit_card' => false,  // Silent release (disabled)
    'braintree_credit_card'         => false,
    'square_credit_card'            => false,
]
```

**Implementation:**

```php
// app/Modules/Payments/Services/PaymentGatewayFeatureFlags.php
namespace Autoship\Modules\Payments\Services;

class PaymentGatewayFeatureFlags {

    private const OPTION_KEY = 'autoship_payment_gateway_flags';

    private static array $defaults = [
        'stripe'                        => true,
        'stripe_sepa'                   => true,
        'authorize_net_cim_credit_card' => false,
        'braintree_credit_card'         => false,
        'braintree_paypal'              => false,
        'square_credit_card'            => false,
        'square_cash_app_pay'           => false,
        'paypal'                        => false,
        'ppcp-gateway'                  => false,
        'nmi_gateway_woocommerce'       => false,
        'cybersource_credit_card'       => false,
        'trustcommerce'                 => false,
        'sagepaymentsusaapi'            => false,
        'checkoutcom_card_payment'      => false,
        'opayo_direct'                  => false,
        'paya_gateway_credit_card'      => false,
    ];

    public static function is_gateway_enabled( string $gateway_id ): bool {
        $flags = self::get_all_flags();
        return $flags[ $gateway_id ] ?? false;
    }

    public static function set_gateway_enabled( string $gateway_id, bool $enabled ): bool {
        $flags = self::get_all_flags();
        $flags[ $gateway_id ] = $enabled;
        return update_option( self::OPTION_KEY, $flags );
    }

    public static function get_all_flags(): array {
        $stored = get_option( self::OPTION_KEY, [] );
        return array_merge( self::$defaults, $stored );
    }

    public static function enable_gateway( string $gateway_id ): bool {
        return self::set_gateway_enabled( $gateway_id, true );
    }

    public static function disable_gateway( string $gateway_id ): bool {
        return self::set_gateway_enabled( $gateway_id, false );
    }

    public static function reset_to_defaults(): bool {
        return delete_option( self::OPTION_KEY );
    }
}
```

**Integration with Compatibility Layer:**

```php
// Modify PaymentsCompatibilityManager::register_compatibility_classes()
protected static function register_compatibility_classes(): void {
    foreach ( self::$available_compatibility_classes as $gateway_id => $class ) {
        // Check feature flag BEFORE registering
        if ( ! PaymentGatewayFeatureFlags::is_gateway_enabled( $gateway_id ) ) {
            Logger::log( 'PaymentsCompatibilityManager',
                "Gateway {$gateway_id} disabled by feature flag, skipping registration" );
            continue;
        }

        // Existing registration logic...
        if ( class_exists( $class ) ) {
            $instance = new $class();
            self::register( $instance );
        }
    }
}
```

| Pros | Cons |
|------|------|
| Dynamic - change without deployment | Requires database access |
| Persistent across restarts | Slightly slower (DB read) |
| Can be modified via WP admin | Needs cache invalidation strategy |
| Per-site configuration | Migration needed for existing sites |

**Admin UI (Optional):**
```php
// Add to Autoship settings page
add_filter( 'autoship_settings_fields', function( $fields ) {
    $fields['payment_gateway_flags'] = [
        'title'  => 'Payment Gateway Feature Flags',
        'type'   => 'gateway_toggles',
        'desc'   => 'Enable/disable new payment gateway implementations',
    ];
    return $fields;
});
```

---

### Option 2: Environment Constants (wp-config.php)

**Concept:** Define constants in `wp-config.php` for environment-specific control.

```php
// wp-config.php
define( 'AUTOSHIP_GATEWAY_STRIPE_ENABLED', true );
define( 'AUTOSHIP_GATEWAY_BRAINTREE_ENABLED', false );
define( 'AUTOSHIP_GATEWAY_SQUARE_ENABLED', false );
define( 'AUTOSHIP_GATEWAY_AUTHORIZENET_ENABLED', true );

// Or a single array constant
define( 'AUTOSHIP_GATEWAY_FLAGS', json_encode([
    'stripe' => true,
    'braintree_credit_card' => false,
    'square_credit_card' => false,
]));
```

**Implementation:**

```php
// app/Modules/Payments/Services/PaymentGatewayFeatureFlags.php
namespace Autoship\Modules\Payments\Services;

class PaymentGatewayFeatureFlags {

    private static array $defaults = [
        'stripe'                        => false,
        'authorize_net_cim_credit_card' => false,
        // ... all gateways default to false (safe)
    ];

    private static array $constant_map = [
        'stripe'                        => 'AUTOSHIP_GATEWAY_STRIPE_ENABLED',
        'stripe_sepa'                   => 'AUTOSHIP_GATEWAY_STRIPE_ENABLED',
        'authorize_net_cim_credit_card' => 'AUTOSHIP_GATEWAY_AUTHORIZENET_ENABLED',
        'braintree_credit_card'         => 'AUTOSHIP_GATEWAY_BRAINTREE_ENABLED',
        'braintree_paypal'              => 'AUTOSHIP_GATEWAY_BRAINTREE_ENABLED',
        'square_credit_card'            => 'AUTOSHIP_GATEWAY_SQUARE_ENABLED',
        'square_cash_app_pay'           => 'AUTOSHIP_GATEWAY_SQUARE_ENABLED',
        // ... more mappings
    ];

    public static function is_gateway_enabled( string $gateway_id ): bool {
        // Priority 1: Check individual constant
        $constant = self::$constant_map[ $gateway_id ] ?? null;
        if ( $constant && defined( $constant ) ) {
            return (bool) constant( $constant );
        }

        // Priority 2: Check JSON array constant
        if ( defined( 'AUTOSHIP_GATEWAY_FLAGS' ) ) {
            $flags = json_decode( AUTOSHIP_GATEWAY_FLAGS, true );
            if ( isset( $flags[ $gateway_id ] ) ) {
                return (bool) $flags[ $gateway_id ];
            }
        }

        // Priority 3: Default (safe = disabled)
        return self::$defaults[ $gateway_id ] ?? false;
    }
}
```

| Pros | Cons |
|------|------|
| No database dependency | Requires file access to change |
| Fast (in-memory constants) | Not dynamic at runtime |
| Environment-specific (dev/staging/prod) | Requires deployment to enable |
| Clear separation of concerns | No admin UI possible |

**Best For:** Gradual rollout across environments (enable on staging first, then prod).

---

### Option 3: Hybrid (Config File + Database Override)

**Concept:** Use a configuration file for defaults, allow database overrides.

```php
// config/payment_gateway_flags.php
<?php
return [
    // Default states - shipped with plugin
    'stripe'                        => true,   // Stable, enabled by default
    'stripe_sepa'                   => true,
    'authorize_net_cim_credit_card' => true,   // Stable, enabled by default
    'braintree_credit_card'         => false,  // In development
    'braintree_paypal'              => false,
    'square_credit_card'            => false,  // In development
    'square_cash_app_pay'           => false,
    'paypal'                        => false,
    'ppcp-gateway'                  => false,
    'nmi_gateway_woocommerce'       => false,
    'cybersource_credit_card'       => false,
    'trustcommerce'                 => false,
    'sagepaymentsusaapi'            => false,
    'checkoutcom_card_payment'      => false,
    'opayo_direct'                  => false,
    'paya_gateway_credit_card'      => false,
];
```

**Implementation:**

```php
// app/Modules/Payments/Services/PaymentGatewayFeatureFlags.php
namespace Autoship\Modules\Payments\Services;

class PaymentGatewayFeatureFlags {

    private const OPTION_KEY = 'autoship_payment_gateway_overrides';
    private static ?array $config_defaults = null;

    private static function load_config_defaults(): array {
        if ( self::$config_defaults === null ) {
            $config_file = AUTOSHIP_PLUGIN_PATH . 'config/payment_gateway_flags.php';
            self::$config_defaults = file_exists( $config_file )
                ? include $config_file
                : [];
        }
        return self::$config_defaults;
    }

    public static function is_gateway_enabled( string $gateway_id ): bool {
        // Priority 1: Environment constant (highest priority)
        if ( defined( 'AUTOSHIP_GATEWAY_' . strtoupper( $gateway_id ) . '_ENABLED' ) ) {
            return (bool) constant( 'AUTOSHIP_GATEWAY_' . strtoupper( $gateway_id ) . '_ENABLED' );
        }

        // Priority 2: Database override
        $overrides = get_option( self::OPTION_KEY, [] );
        if ( isset( $overrides[ $gateway_id ] ) ) {
            return (bool) $overrides[ $gateway_id ];
        }

        // Priority 3: Config file defaults
        $defaults = self::load_config_defaults();
        return $defaults[ $gateway_id ] ?? false;
    }

    public static function set_gateway_override( string $gateway_id, ?bool $enabled ): bool {
        $overrides = get_option( self::OPTION_KEY, [] );

        if ( $enabled === null ) {
            // Remove override, revert to config default
            unset( $overrides[ $gateway_id ] );
        } else {
            $overrides[ $gateway_id ] = $enabled;
        }

        return update_option( self::OPTION_KEY, $overrides );
    }

    public static function get_effective_flags(): array {
        $defaults = self::load_config_defaults();
        $overrides = get_option( self::OPTION_KEY, [] );

        $result = [];
        foreach ( $defaults as $gateway_id => $default ) {
            $result[ $gateway_id ] = [
                'default'   => $default,
                'override'  => $overrides[ $gateway_id ] ?? null,
                'effective' => self::is_gateway_enabled( $gateway_id ),
                'source'    => isset( $overrides[ $gateway_id ] ) ? 'database' : 'config',
            ];
        }

        return $result;
    }

    public static function clear_overrides(): bool {
        return delete_option( self::OPTION_KEY );
    }
}
```

| Pros | Cons |
|------|------|
| Ship safe defaults with plugin | More complex implementation |
| Per-site overrides via database | Two sources of truth |
| Environment constants for emergencies | Debugging requires checking multiple sources |
| Best flexibility | Documentation overhead |

**Priority Chain:** Constants → Database → Config File → Hardcoded Default (false)

---

### Option 4: Extend Existing FeatureManager

**Concept:** Extend `FeatureManager` to support per-gateway payment flags with naming convention.

```php
// Add to FeatureManager::$features array
'payments'                            => true,   // Main module flag
'payments_gateway_stripe'             => true,   // Per-gateway flags
'payments_gateway_stripe_sepa'        => true,
'payments_gateway_authorize_net'      => true,
'payments_gateway_braintree'          => false,
'payments_gateway_square'             => false,
'payments_gateway_paypal'             => false,
// ... more gateways
```

**Implementation:**

```php
// Modify FeatureManager to support dynamic gateway flags
class FeatureManager {

    // Existing code...

    public static function is_payment_gateway_enabled( string $gateway_id ): bool {
        // First check if payments module is enabled
        if ( ! self::is_enabled( 'payments' ) ) {
            return false;
        }

        // Normalize gateway ID to feature key
        $feature_key = 'payments_gateway_' . self::normalize_gateway_id( $gateway_id );

        return self::is_enabled( $feature_key );
    }

    private static function normalize_gateway_id( string $gateway_id ): string {
        // Map various gateway IDs to feature keys
        $mappings = [
            'stripe'                        => 'stripe',
            'stripe_sepa'                   => 'stripe_sepa',
            'authorize_net_cim_credit_card' => 'authorize_net',
            'braintree_credit_card'         => 'braintree',
            'braintree_paypal'              => 'braintree',
            'square_credit_card'            => 'square',
            'square_cash_app_pay'           => 'square',
            // ... more mappings
        ];

        return $mappings[ $gateway_id ] ?? str_replace( [ '-', ' ' ], '_', $gateway_id );
    }
}
```

**Integration:**

```php
// In PaymentsCompatibilityManager
if ( ! FeatureManager::is_payment_gateway_enabled( $gateway_id ) ) {
    continue; // Skip registration
}
```

| Pros | Cons |
|------|------|
| Consistent with existing pattern | FeatureManager becomes larger |
| Single source of truth for features | Still hardcoded in class |
| No new classes needed | Less flexible than database |

---

### Option 5: WP-CLI Command for Operations

**Concept:** Provide WP-CLI commands for enabling/disabling gateways without admin UI.

```bash
# List all gateway flags and their status
wp autoship gateway-flags list

# Enable a gateway
wp autoship gateway-flags enable stripe

# Disable a gateway
wp autoship gateway-flags disable braintree_credit_card

# Enable multiple gateways
wp autoship gateway-flags enable stripe authorize_net_cim_credit_card

# Reset all to defaults
wp autoship gateway-flags reset

# Show effective status with source
wp autoship gateway-flags status stripe
```

**Implementation:**

```php
// app/CLI/PaymentGatewayFlagsCommand.php
namespace Autoship\CLI;

use Autoship\Modules\Payments\Services\PaymentGatewayFeatureFlags;
use WP_CLI;

class PaymentGatewayFlagsCommand {

    /**
     * List all payment gateway feature flags.
     *
     * ## EXAMPLES
     *     wp autoship gateway-flags list
     *
     * @subcommand list
     */
    public function list_flags( $args, $assoc_args ) {
        $flags = PaymentGatewayFeatureFlags::get_effective_flags();

        $rows = [];
        foreach ( $flags as $gateway_id => $data ) {
            $rows[] = [
                'gateway'   => $gateway_id,
                'enabled'   => $data['effective'] ? 'yes' : 'no',
                'source'    => $data['source'],
                'default'   => $data['default'] ? 'yes' : 'no',
            ];
        }

        WP_CLI\Utils\format_items( 'table', $rows, [ 'gateway', 'enabled', 'source', 'default' ] );
    }

    /**
     * Enable a payment gateway.
     *
     * ## OPTIONS
     * <gateway_id>...
     * : One or more gateway IDs to enable.
     *
     * ## EXAMPLES
     *     wp autoship gateway-flags enable stripe
     *     wp autoship gateway-flags enable stripe braintree_credit_card
     *
     * @subcommand enable
     */
    public function enable( $args, $assoc_args ) {
        foreach ( $args as $gateway_id ) {
            if ( PaymentGatewayFeatureFlags::set_gateway_override( $gateway_id, true ) ) {
                WP_CLI::success( "Enabled gateway: {$gateway_id}" );
            } else {
                WP_CLI::error( "Failed to enable gateway: {$gateway_id}" );
            }
        }
    }

    /**
     * Disable a payment gateway.
     *
     * ## OPTIONS
     * <gateway_id>...
     * : One or more gateway IDs to disable.
     *
     * ## EXAMPLES
     *     wp autoship gateway-flags disable braintree_credit_card
     *
     * @subcommand disable
     */
    public function disable( $args, $assoc_args ) {
        foreach ( $args as $gateway_id ) {
            if ( PaymentGatewayFeatureFlags::set_gateway_override( $gateway_id, false ) ) {
                WP_CLI::success( "Disabled gateway: {$gateway_id}" );
            } else {
                WP_CLI::error( "Failed to disable gateway: {$gateway_id}" );
            }
        }
    }

    /**
     * Reset all gateway flags to defaults.
     *
     * ## EXAMPLES
     *     wp autoship gateway-flags reset
     *
     * @subcommand reset
     */
    public function reset( $args, $assoc_args ) {
        if ( PaymentGatewayFeatureFlags::clear_overrides() ) {
            WP_CLI::success( 'All gateway flags reset to defaults.' );
        } else {
            WP_CLI::warning( 'No overrides to clear.' );
        }
    }
}

// Register command
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'autoship gateway-flags', PaymentGatewayFlagsCommand::class );
}
```

---

## Recommended Approach: Option 3 (Hybrid) + Option 5 (WP-CLI)

### Why This Combination?

1. **Config file for defaults** - Ship stable gateways enabled, new ones disabled
2. **Database overrides** - Per-site enablement without code changes
3. **WP-CLI for operations** - Enable gateways on production without admin UI
4. **Constants for emergencies** - Force enable/disable without database access

### Release Workflow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         GATEWAY RELEASE WORKFLOW                        │
└─────────────────────────────────────────────────────────────────────────┘

1. DEVELOPMENT
   ├─ New gateway code in app/Domain/PaymentIntegrations/
   ├─ New compatibility class in app/Modules/Payments/Compatibility/
   ├─ Add to config/payment_gateway_flags.php as FALSE (disabled)
   └─ Write tests

2. SILENT RELEASE (Plugin Update)
   ├─ Code ships to all sites
   ├─ Gateway is DISABLED by default (config file)
   └─ No impact on existing sites

3. BETA TESTING
   ├─ Enable on specific sites via WP-CLI:
   │   wp autoship gateway-flags enable square_credit_card
   ├─ Or via database directly (admin UI if built)
   └─ Monitor logs and behavior

4. GRADUAL ROLLOUT
   ├─ Enable for more sites as confidence grows
   ├─ Update config file default to TRUE
   └─ Release plugin update (now enabled by default)

5. EMERGENCY DISABLE
   ├─ Sites can override back to disabled:
   │   wp autoship gateway-flags disable square_credit_card
   └─ Or via wp-config.php constant:
       define( 'AUTOSHIP_GATEWAY_SQUARE_ENABLED', false );
```

---

## Implementation Plan

### Phase 1: Core Feature Flag Service (Estimated: 2-3 hours)

1. Create `PaymentGatewayFeatureFlags` service class
2. Create `config/payment_gateway_flags.php` config file
3. Add integration to `PaymentsCompatibilityManager`

### Phase 2: WP-CLI Commands (Estimated: 1-2 hours)

1. Create `PaymentGatewayFlagsCommand` class
2. Register WP-CLI command
3. Test commands

### Phase 3: Testing & Documentation (Estimated: 1-2 hours)

1. Unit tests for `PaymentGatewayFeatureFlags`
2. Integration tests for compatibility layer with flags
3. Update documentation

### Files to Create/Modify

**New Files:**
- `app/Modules/Payments/Services/PaymentGatewayFeatureFlags.php`
- `app/CLI/PaymentGatewayFlagsCommand.php`
- `config/payment_gateway_flags.php`
- `tests/Modules/Payments/Services/PaymentGatewayFeatureFlagsTest.php`

**Modified Files:**
- `app/Modules/Payments/Compatibility/PaymentsCompatibilityManager.php`
- `app/Modules/Payments/PaymentsModule.php`

---

## Comparison Matrix

| Feature | Option 1 (DB) | Option 2 (Constants) | Option 3 (Hybrid) | Option 4 (FeatureManager) |
|---------|---------------|---------------------|-------------------|--------------------------|
| Dynamic at runtime | ✅ | ❌ | ✅ | ❌ |
| No DB dependency | ❌ | ✅ | Partial | ✅ |
| Environment-specific | ❌ | ✅ | ✅ | ❌ |
| Admin UI possible | ✅ | ❌ | ✅ | ✅ |
| Ship safe defaults | ❌ | ❌ | ✅ | ❌ |
| Emergency override | ❌ | ✅ | ✅ | ❌ |
| Complexity | Low | Low | Medium | Low |
| Best for rapid releases | Good | Poor | Best | Fair |

---

## Summary

The **Hybrid approach (Option 3)** combined with **WP-CLI commands (Option 5)** provides:

1. **Safe defaults** - New gateways ship disabled
2. **Per-site control** - Enable via database without code changes
3. **Operations friendly** - WP-CLI for server access scenarios
4. **Emergency escape hatch** - Constants override everything
5. **Compatibility layer integration** - Hooks into existing architecture

This enables the release workflow where:
- Code ships silently with the plugin
- Gateways are enabled per-site or via config update
- Rollback is instant via flag toggle
- No major refactoring needed - just hook into compatibility layer initialization
