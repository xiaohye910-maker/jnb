# Integrate Quick Actions into Retain & Grow

**Status**: COMPLETED (2026-01-21)

## Summary
Moved the Quick Actions page from a standalone menu item to a tab within the Retain & Grow page, following the same pattern as Coupons, Retention Workflows, and Dunning.

## Changes Made

### Files Modified

| File | Change |
|------|--------|
| `src/admin.php` | Added `quick-actions` tab to `autoship_retain_and_grow_tabs()` |
| `src/admin.php` | Removed standalone menu item from submenus array |
| `src/admin.php` | Added iframe fallback URL to `autoship_admin_retain_and_grow_tabs_content()` |
| `src/admin.php` | Removed `autoship_quick_links_page()` function |
| `templates/admin/quicklinks.php` | Deleted |
| `templates/admin/retain-and-grow/quick-actions.php` | Created (without breadcrumb header) |

### Implementation Details

#### 1. Added Tab to autoship_retain_and_grow_tabs()
**Location**: `src/admin.php` line 1168-1172

```php
'quick-actions'       => array(
    'label'      => __( 'Quick Actions', 'autoship' ),
    'callback'   => 'autoship_admin_retain_and_grow_tabs_content',
    'link_class' => '',
),
```

#### 2. Removed Standalone Menu Item
**Location**: `src/admin.php` (formerly lines 965-972)

Removed the `'quicklinks'` entry from the submenus array.

#### 3. Added Iframe Fallback URL
**Location**: `src/admin.php` line 1631

```php
'quick-actions' => rawurlencode( $site_id ) . '/quick-actions?tokenBearerAuth=' . rawurlencode( $token_auth ),
```

#### 4. Moved Template File
- **Deleted**: `templates/admin/quicklinks.php`
- **Created**: `templates/admin/retain-and-grow/quick-actions.php`
- Removed breadcrumb header (provided by Retain & Grow parent page)

#### 5. Removed Orphaned Page Function
**Location**: `src/admin.php` (formerly lines 2649-2663)

Removed `autoship_quick_links_page()` function.

## Current Structure

### Retain & Grow Tabs
Located in `autoship_retain_and_grow_tabs()`:
```php
$tabs = array(
    'coupons'             => array( ... ),
    'retention-workflows' => array( ... ),
    'dunning'             => array( ... ),
    'quick-actions'       => array( ... ),  // NEW
);
```

### Template Location
`templates/admin/retain-and-grow/quick-actions.php`

### Components Used
- `qmc-quick-links-list` - Main list view
- `qmc-quick-link-form` - Create/Edit form

### Event Handlers
- `qmc-navigate` - Handles create and edit actions
- `qmc-quick-link-saved` - Returns to list after save
- `qmc-navigate-back` - Returns to list

## CSS Updates
`#qmc-quick-links-app` was already added to `styles/qmc.css` in previous updates.

## Verification

1. Navigate to `/wp-admin/admin.php?page=retain-and-grow`
2. Verify Quick Actions tab appears alongside Coupons, Retention Workflows, Dunning
3. Click tab and verify `qmc-quick-links-list` loads
4. Test create/edit/save flows
5. Confirm standalone "Quick Actions" menu item is removed
6. Test iframe fallback (disable `qmc_components` feature flag)

## Migration Notes
- Old URL `/wp-admin/admin.php?page=quick-actions` no longer works
- Access Quick Actions via Retain & Grow tab: `/wp-admin/admin.php?page=retain-and-grow&tab=quick-actions`
