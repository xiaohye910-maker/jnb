<?php
/**
 * Autoship Scripts and Styles
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;

/**
 * Enqueues Autoship Scripts and Styles For the Backend
 *
 * @param string $hook_suffix The current admin page hook suffix.
 */
function autoship_enqueue_admin_scripts( $hook_suffix ) {

	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	// Main Autoship Admin Style sheet.
	wp_enqueue_style( 'autoship-admin', plugin_dir_url( Autoship_Plugin_File ) . 'styles/admin-style.css', array(), Autoship_Version );

	// Main Autoship Script.
	wp_enqueue_script( 'autoship-admin', plugin_dir_url( Autoship_Plugin_File ) . 'js/admin.js', array(), Autoship_Version, true );

	// Bulk Utilities Script.
	wp_enqueue_script( 'autoship-batch', plugin_dir_url( Autoship_Plugin_File ) . 'js/batch.js', array( 'jquery' ), Autoship_Version, true );

	$features           = Plugin::get_service_container()->get( FeatureManagerInterface::class );
	$use_component_view = $features->is_enabled( 'qmc_components' );

    $token_auth = autoship_get_token_auth();
    $has_token  = ! empty( $token_auth );

    // Only load QMC scripts on specific Autoship pages.
    if ( $hook_suffix == 'toplevel_page_autoship' || ( $use_component_view && stristr($hook_suffix, 'autoship' )  && !stristr($hook_suffix, 'quicklaunch' ) && $has_token )) {
        $base_url  = autoship_get_qmc_components_url();
        $scripts   = autoship_get_qmc_component_scripts();
        $style_url = autoship_get_qmc_component_style_url( $base_url );

        wp_enqueue_style( 'autoship-dashboard-styles-qmc-base', $style_url, array(), Autoship_Version );
        wp_enqueue_style( 'autoship-dashboard-styles-qmc-woo', plugin_dir_url( Autoship_Plugin_File ) . '/styles/qmc.css', array( 'autoship-dashboard-styles-qmc-base' ), Autoship_Version );

        // Enqueue Scripts dynamically based on the filtered list.
        $prev_handle = array();
        foreach ( $scripts as $script ) {
            $handle = 'autoship-dashboard-' . str_replace( '.js', '', $script );
            wp_enqueue_script( $handle, $base_url . '/' . $script, $prev_handle, Autoship_Version, true );
            $prev_handle = array( $handle );
        }

        // Add the filter to convert scripts to ES modules.
        add_filter( 'script_loader_tag', 'autoship_add_module_type_to_scripts', 10, 3 );

        // Fix PrimeNG overlay positioning in WP admin.
        wp_enqueue_script( 'autoship-qmc-overlay-fix', plugin_dir_url( Autoship_Plugin_File ) . 'js/admin/qmc-overlay-fix.js', array(), Autoship_Version, true );
    }
}

add_action( 'admin_enqueue_scripts', 'autoship_enqueue_admin_scripts' );

/**
 * Adds type="module" to QMC dashboard scripts for ES module support.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source.
 * @return string Modified script tag.
 */
function autoship_add_module_type_to_scripts( string $tag, string $handle, string $src ): string {
	// Check if handle starts with 'autoship-dashboard-' prefix.
	if ( strpos( $handle, 'autoship-dashboard-' ) === 0 ) {
		$tag = str_replace( '<script ', '<script type="module" ', $tag );
	}

	return $tag;
}

/**
 * Enqueues Autoship Scripts and Styles For the Frontend
 */
function autoship_enqueue_scripts() {

	// Main Autoship Style sheet.
	wp_enqueue_style( 'autoship', plugin_dir_url( Autoship_Plugin_File ) . 'styles/style.css', array(), Autoship_Version );

	// Enqueue Dashicons if not loaded.
	wp_enqueue_style( 'dashicons' );

	wp_enqueue_script( 'autoship-product-schedule-options', plugin_dir_url( Autoship_Plugin_File ) . 'js/product-schedule-options.js', array(), Autoship_Version, true );
	wp_enqueue_script( 'autoship-schedule-options', plugin_dir_url( Autoship_Plugin_File ) . 'js/schedule-options.js', array( 'jquery' ), Autoship_Version, true );
	wp_enqueue_script( 'autoship-scheduled-orders', plugin_dir_url( Autoship_Plugin_File ) . 'js/scheduled-orders.js', array( 'jquery', 'jquery-tiptip' ), Autoship_Version, true );

	// Only Load if Dynamic Cart is Enabled.
	if ( ! empty( autoship_get_settings_fields( 'autoship_dynamic_cart', true ) ) ) {
		wp_enqueue_script( 'autoship-schedule-cart', plugin_dir_url( Autoship_Plugin_File ) . 'js/schedule-cart.js', array(), Autoship_Version, true );
		wp_enqueue_script( 'autoship-select-frequency-dialog', plugin_dir_url( Autoship_Plugin_File ) . 'js/select-frequency-dialog.js', array(), Autoship_Version, true );
		wp_enqueue_script( 'autoship-select-next-occurrence-dialog', plugin_dir_url( Autoship_Plugin_File ) . 'js/select-next-occurrence-dialog.js', array(), Autoship_Version, true );
	}

	// WooCommerce Script Used for Frontend.
	wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );

	if ( 'v2_portal' === autoship_get_scheduled_orders_display_version() ) {
		wp_enqueue_script_module( 'autoship-v2-portal-main-script', trailingslashit( autoship_get_v2_portal_scripts_url() ) . 'main.js', array(), Autoship_Version, array() );
		wp_enqueue_script( 'autoship-v2-portal-polyfills-script', trailingslashit( autoship_get_v2_portal_scripts_url() ) . 'polyfills.js', array(), Autoship_Version, false );
		wp_enqueue_style( 'autoship-v2-portal-style', trailingslashit( autoship_get_v2_portal_scripts_url() ) . 'styles.css', array(), Autoship_Version );
	}

	if ( 'yes' !== autoship_get_scheduled_order_upsell_disable_carousel_js() && 'yes' === autoship_get_scheduled_order_upsell_carousel() ) {
		wp_enqueue_script( 'autoship-scheduled-order-upsell', plugin_dir_url( Autoship_Plugin_File ) . 'js/upsell-product-carousel.js', array( 'flexslider' ), Autoship_Version, false );
	}
}

add_action( 'wp_enqueue_scripts', 'autoship_enqueue_scripts' );


/**
 * Disables the Tooltip as Modal on Mobile.
 *
 * @param int $val The current min-width.
 * @return int 0 if the setting is off.
 */
function autoship_show_tooltip_as_modal_mobile( $val ) {
	return 'yes' !== autoship_get_settings_fields( 'autoship_product_info_mobile_tooltip', true ) ? 0 : $val;
}

add_filter( 'autoship_dialog_info_tooltip_min_browser_width', 'autoship_show_tooltip_as_modal_mobile', 10, 1 );

/**
 * Outputs Autoship Script Data in the Page Header.
 *
 * @see autoship_print_scripts_data()
 */
function autoship_head_scripts() {

	$data = array(
		'AUTOSHIP_SITE_URL'                 => site_url( '/' ),
		'AUTOSHIP_AJAX_URL'                 => admin_url( '/admin-ajax.php' ),
		'AUTOSHIP_MERCHANTS_URL'            => autoship_get_merchants_url(),
		'AUTOSHIP_API_URL'                  => autoship_get_api_url(),
		'AUTOSHIP_DIALOG_TYPE'              => autoship_get_settings_fields( 'autoship_product_info_display', true ),
		'AUTOSHIP_DIALOG_TOOLTIP_MIN_WIDTH' => apply_filters( 'autoship_dialog_info_tooltip_min_browser_width', 1024 ),
		'AUTOSHIP_DIALOG_SIZE'              => autoship_get_settings_fields( 'autoship_product_info_modal_size', true ),
		'AUTOSHIP_DIALOG_SIZES'             => autoship_get_info_modal_sizes(),
	);
	autoship_print_scripts_data( $data );

	$default_autoship_template_data = apply_filters(
		'autoship_default_template_data',
		array(
			'cartBtn'             => '.add_to_cart_button',
			'yesBtn'              => '.autoship-yes-radio',
			'noBtn'               => '.autoship-no-radio',
			'optionsCls'          => '.autoship-schedule-options',
			'discountPriceCls'    => '.autoship-percent-discount',
			'checkoutPriceCls'    => '.autoship-checkout-price',
			'discountStringCls'   => '.autoship-custom-percent-discount-str',
			'frequencyCls'        => '.autoship-frequency',
			'frequencySelectCls'  => '.autoship-frequency-select',
			'frequencyTypeValCls' => '.autoship-frequency-type-value',
			'frequencyValCls'     => '.autoship-frequency-value',
			'productCls'          => '.product',  // The class of the Main Product HTML Element on the shop page.
			'cartItemCls'         => '.cart_item',
			'variationFormCls'    => '.variations_form',
			'variationIdCls'      => '.variation_id',
			'findProductFn'       => null, // Pluggable Function.
			'findAutoshipOptions' => null, // Pluggable Function.
			'retrieveProductIdFn' => null, // Pluggable Function.
			'setVariationIdFn'    => null, // Pluggable Function.
			'getVariationIdFn'    => null, // Pluggable Function.
			'isSimpleProductFn'   => null, // Pluggable Function.
			'isCartPageFn'        => null, // Pluggable Function.
			'isCartPage'          => false,
		)
	);

	ob_start();
	?>
	<!-- Autoship Cloud Data Container -->
	<script>window['autoshipTemplateData']=window['autoshipTemplateData']||<?php echo wp_json_encode( $default_autoship_template_data ); ?>;</script>
	<!-- End Autoship Cloud Data Container -->
	<?php
	echo apply_filters( 'autoship_header_script_data', ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'wp_head', 'autoship_head_scripts' );

/**
 * Outputs JS Directly to Page.
 *
 * @param array $data A Set of Key value pairs to add/output.
 */
function autoship_print_scripts_data( $data ) {
	echo "<script>\r\n// <![CDATA[\r\n";
	foreach ( $data as $name => $value ) {
		printf( "var %s = %s;\r\n", $name, wp_json_encode( $value ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo "// ]]>\r\n</script>\r\n";
}

/**
 * Returns URL which hosts scripts for V2 portal
 *
 * @return string URL for V2 portal scripts
 */
function autoship_get_v2_portal_scripts_url() {
	return apply_filters( 'autoship_v2_portal_scripts_url', 'https://subscriber-portal.qpilot.cloud' );
}


function autoship_get_qmc_portal_url(): string {
	return apply_filters( 'autoship_qmc_portal_url', 'https://portal.qpilot.cloud' );
}

/**
 * Returns URL which hosts scripts for QMC components.
 *
 * @return string URL for QMC component scripts
 */
function autoship_get_qmc_components_url(): string {
	return apply_filters( 'autoship_qmc_components_url', 'https://portal.qpilot.cloud' );
}

/**
 * Returns the list of QMC component scripts to load.
 *
 * @return array List of script filenames to load
 */
function autoship_get_qmc_component_scripts(): array {
	$scripts = array(
		'runtime.js',
		'polyfills.js',
		'main.js',
	);

	return apply_filters( 'autoship_qmc_component_scripts', $scripts );
}

/**
 * Returns the list of QMC component styles to load.
 *
 * @return string The file URL of the style sheet.
 */
function autoship_get_qmc_component_style_url( string $base_url ): string {
    $styles_url = $base_url . '/' . 'styles.css';

	return apply_filters( 'autoship_qmc_component_style_url', $styles_url, $base_url );
}
