# Filterable QMC Components URL, Scripts, and Styles

**Status**: COMPLETED (2026-01-21)

## Summary
Added filters to customize the QMC (QPilot Merchant Console) components base URL, scripts list, and style URL. This enables different configurations for production, staging, and local development environments.

## Implementation

### Functions Added to `src/scripts.php`

#### autoship_get_qmc_components_url()
```php
function autoship_get_qmc_components_url(): string {
    return apply_filters( 'autoship_qmc_components_url', 'https://portal.qpilot.cloud' );
}
```

#### autoship_get_qmc_component_scripts()
```php
function autoship_get_qmc_component_scripts(): array {
    $scripts = array(
        'runtime.js',
        'polyfills.js',
        'main.js',
    );

    return apply_filters( 'autoship_qmc_component_scripts', $scripts );
}
```

#### autoship_get_qmc_component_style_url()
```php
function autoship_get_qmc_component_style_url( string $base_url ): string {
    $styles_url = $base_url . '/' . 'styles.css';

    return apply_filters( 'autoship_qmc_component_style_url', $styles_url, $base_url );
}
```

### Updated autoship_enqueue_admin_scripts()
```php
if ( $use_component_view ) {
    $base_url  = autoship_get_qmc_components_url();
    $scripts   = autoship_get_qmc_component_scripts();
    $style_url = autoship_get_qmc_component_style_url( $base_url );

    // Base style from remote URL, local override depends on it
    wp_enqueue_style( 'autoship-dashboard-styles-qmc-base', $style_url, array(), Autoship_Version );
    wp_enqueue_style( 'autoship-dashboard-styles-qmc-woo', plugin_dir_url( Autoship_Plugin_File ) . '/styles/qmc.css', array( 'autoship-dashboard-styles-qmc-base' ), Autoship_Version );

    // Enqueue Scripts dynamically based on the filtered list
    $prev_handle = array();
    foreach ( $scripts as $script ) {
        $handle = 'autoship-dashboard-' . str_replace( '.js', '', $script );
        wp_enqueue_script( $handle, $base_url . '/' . $script, $prev_handle, Autoship_Version, true );
        $prev_handle = array( $handle );
    }

    add_filter( 'script_loader_tag', 'autoship_add_module_type_to_scripts', 10, 3 );
}
```

## Filter Reference

| Filter | Default | Parameters | Purpose |
|--------|---------|------------|---------|
| `autoship_qmc_components_url` | `https://portal.qpilot.cloud` | `$url` | Base URL for scripts |
| `autoship_qmc_component_scripts` | `['runtime.js', 'polyfills.js', 'main.js']` | `$scripts` | Scripts list |
| `autoship_qmc_component_style_url` | `{base_url}/styles.css` | `$styles_url`, `$base_url` | Style URL |

## Configuration Examples

### Production (Default)
No filters needed - uses default values:
- URL: `https://portal.qpilot.cloud`
- Scripts: `runtime.js`, `polyfills.js`, `main.js`
- Style: `https://portal.qpilot.cloud/styles.css`

### Staging Environment
Add filters in a separate plugin (e.g., `autoship-staging`):

```php
// Override base URL for staging
function autoship_staging_qmc_components_url( $url ): string {
    return 'https://portal-staging.qpilot.cloud';
}
add_filter( 'autoship_qmc_components_url', 'autoship_staging_qmc_components_url', 10, 1 );

// Add vendor.js for staging builds
function autoship_staging_qmc_components_scripts( $scripts ): array {
    return array(
        'runtime.js',
        'polyfills.js',
        'vendor.js',
        'main.js',
    );
}
add_filter( 'autoship_qmc_component_scripts', 'autoship_staging_qmc_components_scripts', 10, 1 );

// Use local style for staging
function autoship_staging_qmc_components_style_url( $styles_url, $base_url ): string {
    return plugin_dir_url( Autoship_Plugin_File ) . '/styles/styles-woo.css';
}
add_filter( 'autoship_qmc_component_style_url', 'autoship_staging_qmc_components_style_url', 10, 2 );
```

### Local Development
```php
add_filter( 'autoship_qmc_components_url', function( $url ) {
    return 'http://localhost:4200';
});

add_filter( 'autoship_qmc_component_scripts', function( $scripts ) {
    return array(
        'runtime.js',
        'polyfills.js',
        'vendor.js',
        'main.js',
    );
});
```

## Style Loading Architecture

1. **Base style** (`autoship-dashboard-styles-qmc-base`): Loaded from remote URL via `autoship_get_qmc_component_style_url()`
2. **Local override** (`autoship-dashboard-styles-qmc-woo`): Always loads `qmc.css` from plugin directory, depends on base style

This allows:
- Production: Remote `styles.css` + local `qmc.css` overrides
- Staging: Local `styles-woo.css` (via filter) + local `qmc.css` overrides

## Verification

1. **Production**: Verify scripts/styles load from `portal.qpilot.cloud`
2. **Staging**: Add filters, verify loads from `portal-staging.qpilot.cloud` with `vendor.js`
3. **Local Dev**: Add localhost filter, verify loads from local server
4. **Components**: Navigate to QMC pages, verify components render correctly

## Debug
```php
add_action( 'admin_footer', function() {
    $base_url = autoship_get_qmc_components_url();
    echo '<!-- QMC URL: ' . esc_html( $base_url ) . ' -->';
    echo '<!-- QMC Scripts: ' . esc_html( implode( ', ', autoship_get_qmc_component_scripts() ) ) . ' -->';
    echo '<!-- QMC Style URL: ' . esc_html( autoship_get_qmc_component_style_url( $base_url ) ) . ' -->';
});
```

## Notes
- Scripts are loaded with dependencies chained in order (each depends on the previous)
- The `type="module"` attribute is added to all scripts with `autoship-dashboard-` prefix
- Local `qmc.css` always loads as an override layer on top of the base style
