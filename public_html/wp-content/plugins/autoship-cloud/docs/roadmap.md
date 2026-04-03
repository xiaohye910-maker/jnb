# Legacy `autoship_*()` Function Calls in `app/`

Remaining procedural `autoship_*()` functions from `src/` still used in the modern `app/` directory. These are candidates for wrapping behind interfaces to complete the DI migration.

## Credential / Auth Checks (10 calls, 5 files)

| Function | File | Line |
|---|---|---|
| `autoship_has_credentials()` | `Core/Implementations/SitesManager.php` | 135 |
| `autoship_has_auth_token()` | `Core/Implementations/SitesManager.php` | 135 |
| `autoship_has_credentials()` | `Modules/Synchronizers/Products/ProductSynchronizer.php` | 404 |
| `autoship_has_auth_token()` | `Modules/Synchronizers/Products/ProductSynchronizer.php` | 404 |
| `autoship_has_credentials()` | `Modules/Nextime/NextimeService.php` | 354 |
| `autoship_has_auth_token()` | `Modules/Nextime/NextimeService.php` | 354 |
| `autoship_has_credentials()` + `autoship_has_auth_token()` | `Modules/Quicklaunch/ProductStepHandler.php` | 66 |
| `autoship_has_credentials()` + `autoship_has_auth_token()` | `Modules/Quicklaunch/QuicklaunchModule.php` | 196, 295 |

## OAuth / Site Connection (1 call)

| Function | File | Line |
|---|---|---|
| `autoship_oauth2_connect_site()` | `Core/Implementations/SitesManager.php` | 140 |

## Configuration Getters (6 calls, 3 files)

| Function | File | Line |
|---|---|---|
| `autoship_get_api_url()` | `Core/Implementations/Environment.php` | 143 |
| `autoship_get_merchants_url()` | `Core/Implementations/Environment.php` | 144 |
| `autoship_get_site_id()` | `Services/Nextime/Implementations/WordPressNextimeSettings.php` | 111 |
| `autoship_get_site_id()` | `Services/Nextime/Implementations/NextimeSitesManagement.php` | 48 |
| `autoship_get_token_auth()` | `Services/Nextime/Implementations/NextimeSitesManagement.php` | 49 |
| `autoship_get_scheduled_orders_url()` | `Modules/QuickLinks/Controllers/QuickLinkController.php` | 481 |

## Template Rendering (11 calls, 2 files)

| Function | File | Line |
|---|---|---|
| `autoship_include_template()` | `Modules/Quicklaunch/SetupStepHandler.php` | 129, 140, 151, 162, 173, 184, 195, 206, 219, 229 |
| `autoship_include_template()` | `Modules/Quicklaunch/WidgetHandler.php` | 58 |

## Product Operations (11 calls, 2 files)

| Function | File | Line |
|---|---|---|
| `autoship_push_product()` | `Modules/Synchronizers/Products/ProductSynchronizer.php` | 423 |
| `autoship_push_product()` | `Modules/Quicklaunch/ProductStepHandler.php` | 163 |
| `autoship_set_product_sync_active_enabled()` | `Modules/Quicklaunch/ProductStepHandler.php` | 116 |
| `autoship_update_product_variants_sync_active_flag()` | `Modules/Quicklaunch/ProductStepHandler.php` | 117 |
| `autoship_set_product_autoship_enabled()` | `Modules/Quicklaunch/ProductStepHandler.php` | 118 |
| `autoship_set_product_add_to_scheduled_order()` | `Modules/Quicklaunch/ProductStepHandler.php` | 119 |
| `autoship_set_product_process_on_scheduled_order()` | `Modules/Quicklaunch/ProductStepHandler.php` | 120 |
| `autoship_update_product_add_to_scheduled_order_flag()` | `Modules/Quicklaunch/ProductStepHandler.php` | 121 |
| `autoship_override_frequency_options_enabled()` | `Modules/Quicklaunch/ProductStepHandler.php` | 185 |
| `autoship_set_product_checkout_price()` | `Modules/Quicklaunch/ProductStepHandler.php` | 236 |
| `autoship_set_product_recurring_price()` | `Modules/Quicklaunch/ProductStepHandler.php` | 245 |

## Summary

| Category | Calls | Unique Functions |
|---|---|---|
| Credential/auth checks | 10 | 2 |
| OAuth connection | 1 | 1 |
| Config getters | 6 | 5 |
| Template rendering | 11 | 1 |
| Product operations | 11 | 10 |
| **Total** | **39** | **19** |
