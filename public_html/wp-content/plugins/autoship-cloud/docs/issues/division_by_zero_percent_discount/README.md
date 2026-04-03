# PHP Fatal Error: Division by Zero in autoship_percent_recurring_discount()

## Error Message

```
Uncaught DivisionByZeroError: Division by zero in /wp-content/plugins/autoship-cloud/src/products.php:996
```

## Location

- **File:** `src/products.php`
- **Line:** 996
- **Function:** `autoship_percent_recurring_discount()`

## Compatibility Requirements

- Plugin must maintain compatibility with **PHP 7.4+**
- This error manifests on **PHP 8.0+** due to stricter error handling (division by zero throws `DivisionByZeroError` instead of warning)
- **WordPress:** 6.9
- **WooCommerce:** 10.4.2

## Stack Trace

```
#0  src/products.php(726): autoship_percent_recurring_discount(Object(WC_Product_Variation), Array)
#1  src/products.php(1189): autoship_get_product_prices(10081)
#2  src/cart.php(256): autoship_product_discount_data(Object(WC_Product_Variation))
#3  src/products.php(1165): autoship_get_all_variation_cart_options(Object(WC_Product_Variable))
#4  templates/product/schedule-options-variable.php(30): autoship_product_variations_discount_data(Object(WC_Product_Variable))
#5  src/utilities.php(697): include(...)
#6  src/cart.php(765): autoship_include_template('product/schedul...', Array)
#7  wp-includes/class-wp-hook.php(341): autoship_print_cart_autoship_options_variable(Object(WC_Product_Variable))
#8  wp-includes/class-wp-hook.php(365): WP_Hook->apply_filters('', Array)
#9  wp-includes/plugin.php(522): WP_Hook->do_action(Array)
#10 woocommerce/templates/single-product/add-to-cart/variable.php(70): do_action('woocommerce_bef...')
#11 woocommerce/includes/wc-core-functions.php(346): include(...)
#12 woocommerce/includes/wc-template-functions.php(1981): wc_get_template('single-product/...', Array)
#13 wp-includes/class-wp-hook.php(341): woocommerce_variable_add_to_cart('')
...
#17 elementor-pro/modules/woocommerce/widgets/product-add-to-cart.php(70): woocommerce_template_single_add_to_cart()
...
```

## Call Path

```
ElementorPro Product_Add_To_Cart widget
  → woocommerce_template_single_add_to_cart()
    → do_action('woocommerce_before_variations_form')
      → autoship_print_cart_autoship_options_variable()
        → autoship_include_template('product/schedule-options-variable.php')
          → autoship_product_variations_discount_data()
            → autoship_get_all_variation_cart_options()
              → autoship_product_discount_data()
                → autoship_get_product_prices()
                  → autoship_percent_recurring_discount()  ← ERROR HERE (line 996)
```

## Cause

The `autoship_percent_recurring_discount()` function calculates the percent discount using division:

```php
$percent_discount = round( 100 * ( 1 - $prices['discount'] / $prices['price'] ) );
```

The function checks if `$prices['discount']` is empty (line 992-994), but does **not** check if `$prices['price']` is empty or zero before performing the division.

When a product (or variation) has no regular price set, `$product->get_regular_price()` returns an empty string or `0`, causing the division by zero error.

### Common Scenarios That Trigger This Error

- Variable products with variations missing regular prices
- Sale-only pricing configurations
- Role-based pricing (B2B/wholesale pricing plugins)
- Products with incomplete pricing data

### Current Code (lines 978-999)

```php
function autoship_percent_recurring_discount( $product, $prices = array() ) {

    if ( is_numeric( $product ) ) {
        $product = wc_get_product( $product );
    }

    $prices = wp_parse_args(
        $prices,
        array(
            'price'    => ! isset( $prices['price'] ) ? $product->get_regular_price() : $prices['price'],
            'discount' => ! isset( $prices['discount'] ) ? autoship_get_product_recurring_price( $product->get_id() ) : $prices['discount'],
        )
    );

    if ( empty( $prices['discount'] ) ) {
        return 0;
    }

    $percent_discount = round( 100 * ( 1 - $prices['discount'] / $prices['price'] ) );

    return apply_filters( 'autoship_recurring_percent_discount', $percent_discount, $product, $prices );
}
```

## Required Changes

### File
`src/products.php`

### Function
`autoship_percent_recurring_discount()` (lines 978-999)

### Action
Add a guard check before line 996 to prevent division by zero.

### Code to Add
Insert the following code **after** line 994 (after the `if ( empty( $prices['discount'] ) )` check) and **before** line 996 (the division operation):

```php
// Prevent division by zero (PHP 8+ throws DivisionByZeroError)
if ( empty( $prices['price'] ) || floatval( $prices['price'] ) == 0 ) {
    return 0;
}
```

### Complete Fixed Function

```php
function autoship_percent_recurring_discount( $product, $prices = array() ) {

    if ( is_numeric( $product ) ) {
        $product = wc_get_product( $product );
    }

    $prices = wp_parse_args(
        $prices,
        array(
            'price'    => ! isset( $prices['price'] ) ? $product->get_regular_price() : $prices['price'],
            'discount' => ! isset( $prices['discount'] ) ? autoship_get_product_recurring_price( $product->get_id() ) : $prices['discount'],
        )
    );

    if ( empty( $prices['discount'] ) ) {
        return 0;
    }

    // Prevent division by zero (PHP 8+ throws DivisionByZeroError)
    if ( empty( $prices['price'] ) || floatval( $prices['price'] ) == 0 ) {
        return 0;
    }

    $percent_discount = round( 100 * ( 1 - $prices['discount'] / $prices['price'] ) );

    return apply_filters( 'autoship_recurring_percent_discount', $percent_discount, $product, $prices );
}
```

### Why This Fix Works

1. **`empty( $prices['price'] )`** - Catches empty strings, `null`, and `false` values returned by `get_regular_price()` when no price is set
2. **`floatval( $prices['price'] ) == 0`** - Catches explicit zero values and string "0"
3. **Returns `0`** - Consistent with the existing behavior when `$prices['discount']` is empty (no discount percentage when price data is invalid)

## Environment

- Triggered when a WooCommerce product or variation has no regular price set
- Occurs when viewing products with Autoship schedule options enabled
- Call chain originates from `schedule-options-variable.php` template
- Fatal error on variable product pages
- Autoship pricing fails to render
- **Elementor Pro** involved in call chain (Product_Add_To_Cart widget triggers WooCommerce template rendering)

## Impact

- **Severity:** Critical (Fatal Error)
- **User Impact:** Site crashes when viewing variable products with variations that have no regular price set
- **Affected Environments:** All sites using this plugin version with PHP 8.0+ and products with missing/zero prices

## Support Team Notes

### Findings

- Switching themes and deactivating plugins revealed that deactivating **Yotpo** prevented the critical error from appearing, but this is an **interaction, not a root cause**
- Yotpo forces product pricing evaluation and variation rendering, which exposes Autoship's PHP 8 incompatibilities
- The underlying issue remains in the plugin code regardless of Yotpo

### Root Cause Analysis

This is a **PHP 8+ compatibility issue**:
- PHP 8+ throws a fatal `DivisionByZeroError` for division by zero (PHP 7.x only issued a warning)
- The plugin assumption (prices will always be non-zero) no longer holds safely in PHP 8+
- Likely to affect many merchants with similar pricing setups

### Testing Recommendations

After applying the fix, test against:
- PHP 8.1 / 8.2
- Variable products
- Zero / missing regular prices
- Sale-only variations
- Wholesale / role-based pricing enabled

## QA Testing Guide

### Prerequisites

1. **Autoship Cloud plugin** is active
2. **Product has Autoship enabled** (schedule options are displayed)
3. **Product is a Variable product** with variations
4. **PHP 8.0+** environment (error is fatal on PHP 8+, only warning on PHP 7.x)

### Trigger Scenario

The error occurs when **any variation** of a variable product has:
- **No regular price set** (empty field)
- **Regular price is `0`**
- **Regular price is `null`** (can happen with programmatic product creation or imports)

### How to Reproduce

1. Create a **Variable product** in WooCommerce
2. Add at least one variation
3. **Leave the Regular Price field empty** (or set to 0) on one or more variations
4. Enable Autoship for the product (so schedule options display)
5. View the product on the **frontend** (single product page)

### Entry Points That Trigger the Error

| Entry Point | How to Test |
|-------------|-------------|
| Single product page | Visit any variable product page with missing variation prices |
| Elementor Pro Product widget | Use the Product Add-to-Cart widget on a page |
| Cart page (if Dynamic Cart enabled) | Add a variable product to cart and view cart |

### Additional Scenarios to Test

- **Sale-only variations** - Variations with only a sale price, no regular price
- **B2B/Wholesale pricing plugins** - May hide or zero out regular prices for certain roles
- **Product imports** - CSV imports that leave regular price empty
- **Programmatic product creation** - API-created products without full pricing data

### Expected Behavior After Fix

When a variation has no valid regular price:
- Function should return `0` (no discount percentage)
- No fatal error
- Page renders normally
- Autoship options still display (just no discount shown for that variation)

### Related Issue

This issue is part of a set of PHP 8 compatibility problems. See also:
- [wp_enqueue_script_module() Type Error](../wp_enqueue_script_module_type_error/README.md)
