# Conditional QMC Script Loading

## Summary
Implement conditional loading of QMC (QPilot Merchant Console) web component scripts to only load on specific Autoship admin pages instead of all WordPress admin pages.

## Problem
Currently, `autoship_enqueue_admin_scripts()` in `src/scripts.php` loads QMC scripts (runtime.js, polyfills.js, vendor.js, main.js) on **all WordPress admin pages** when the `qmc_components` feature flag is enabled. This causes:
- Unnecessary bandwidth usage on non-Autoship pages
- Potential script conflicts with other plugins
- Slower page loads across the entire admin area

## Solution
Modify `autoship_enqueue_admin_scripts()` to accept the `$hook_suffix` parameter and conditionally load QMC scripts only on pages that use QMC components.

## Files to Modify
- `src/scripts.php` - Update `autoship_enqueue_admin_scripts()` function

## Implementation

### Target Pages
Pages that require QMC component scripts:

| Page | Menu Slug | Hook Suffix |
|------|-----------|-------------|
| Dashboard | `dashboard` | `autoship_page_dashboard` |
| Scheduled Orders | `scheduled-orders` | `autoship_page_scheduled-orders` |
| Customers | `customers` | `autoship_page_customers` |
| Products | `ac-products` | `autoship_page_ac-products` |
| Quick Actions | `quick-actions` | `autoship_page_quick-actions` |
| Retain & Grow | `retain-and-grow` | `autoship_page_retain-and-grow` |

### Code Changes

#### Before (Current Implementation)
```php
function autoship_enqueue_admin_scripts() {
    // ... existing code ...

    $use_component_view = FeatureManager::is_enabled('qmc_components');

    if ( $use_component_view ) {
        $base_url = 'https://portal-staging.qpilot.cloud';

        // Enqueue Styles
        wp_enqueue_style( 'autoship-dashboard-qmc-style', ... );
        wp_enqueue_style( 'autoship-dashboard-qmc-style2', ... );

        // Enqueue Scripts
        wp_enqueue_script( 'autoship-dashboard-runtime', ... );
        wp_enqueue_script( 'autoship-dashboard-polyfills', ... );
        wp_enqueue_script( 'autoship-dashboard-vendor', ... );
        wp_enqueue_script( 'autoship-dashboard-main', ... );

        add_filter( 'script_loader_tag', 'autoship_add_module_type_to_scripts', 10, 3 );
    }
}

add_action( 'admin_enqueue_scripts', 'autoship_enqueue_admin_scripts' );
```

#### After (Proposed Implementation)
```php
function autoship_enqueue_admin_scripts( $hook_suffix ) {
    // ... existing code (jQuery, admin styles, etc.) ...

    $use_component_view = FeatureManager::is_enabled('qmc_components');

    // Define pages that require QMC component scripts
    $qmc_pages = array(
        'autoship_page_dashboard',
        'autoship_page_scheduled-orders',
        'autoship_page_customers',
        'autoship_page_ac-products',
        'autoship_page_quick-actions',
        'autoship_page_retain-and-grow',
    );

    // Only load QMC scripts on specific Autoship pages
    if ( $use_component_view && in_array( $hook_suffix, $qmc_pages, true ) ) {
        $base_url = 'https://portal-staging.qpilot.cloud';

        // Enqueue Styles
        wp_enqueue_style( 'autoship-dashboard-qmc-style', plugin_dir_url( Autoship_Plugin_File ) . '/styles/styles-woo.css', array(), Autoship_Version );
        wp_enqueue_style( 'autoship-dashboard-qmc-style2', plugin_dir_url( Autoship_Plugin_File ) . '/styles/qmc.css', array(), Autoship_Version );

        // Enqueue Scripts with dependencies to ensure correct order
        wp_enqueue_script( 'autoship-dashboard-runtime', $base_url . '/runtime.js', array(), Autoship_Version, true );
        wp_enqueue_script( 'autoship-dashboard-polyfills', $base_url . '/polyfills.js', array( 'autoship-dashboard-runtime' ), Autoship_Version, true );
        wp_enqueue_script( 'autoship-dashboard-vendor', $base_url . '/vendor.js', array( 'autoship-dashboard-polyfills' ), Autoship_Version, true );
        wp_enqueue_script( 'autoship-dashboard-main', $base_url . '/main.js', array( 'autoship-dashboard-vendor' ), Autoship_Version, true );

        // Add filter to convert scripts to ES modules
        add_filter( 'script_loader_tag', 'autoship_add_module_type_to_scripts', 10, 3 );
    }
}

add_action( 'admin_enqueue_scripts', 'autoship_enqueue_admin_scripts' );
```

## Filter for Extensibility (Optional)
Add a filter to allow other plugins/themes to modify the list of pages:

```php
$qmc_pages = apply_filters( 'autoship_qmc_component_pages', array(
    'autoship_page_dashboard',
    'autoship_page_scheduled-orders',
    'autoship_page_customers',
    'autoship_page_ac-products',
    'autoship_page_quick-actions',
    'autoship_page_retain-and-grow',
) );
```

## Testing

### Verification Steps
1. Enable `qmc_components` feature flag in FeatureManager
2. Navigate to each QMC page and verify:
   - Scripts load correctly (check Network tab in DevTools)
   - Web components render properly
   - Navigation between views works
3. Navigate to non-QMC admin pages (e.g., WooCommerce, Posts) and verify:
   - QMC scripts are NOT loaded (check Network tab)
   - No console errors related to QMC

### Test Pages
**Should load QMC scripts:**
- `/wp-admin/admin.php?page=dashboard`
- `/wp-admin/admin.php?page=scheduled-orders`
- `/wp-admin/admin.php?page=customers`
- `/wp-admin/admin.php?page=ac-products`
- `/wp-admin/admin.php?page=quick-actions`
- `/wp-admin/admin.php?page=retain-and-grow`

**Should NOT load QMC scripts:**
- `/wp-admin/index.php` (WordPress Dashboard)
- `/wp-admin/edit.php` (Posts)
- `/wp-admin/admin.php?page=wc-admin` (WooCommerce)
- `/wp-admin/admin.php?page=autoship` (Autoship Settings)
- `/wp-admin/admin.php?page=reports` (Autoship Reports)

## Notes
- The `$hook_suffix` parameter is automatically passed by WordPress to the `admin_enqueue_scripts` action
- Hook suffix format for submenu pages: `{parent_slug}_page_{menu_slug}`
- The parent slug for Autoship submenus is `autoship`
