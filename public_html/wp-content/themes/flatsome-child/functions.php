<?php
// Add custom Theme Functions here

// Force Flatsome AJAX add-to-cart on.
// The theme setting was disabled, causing standard form POST which gets served
// from SiteGround's page cache showing an empty cart after redirect.
// AJAX routes through admin-ajax.php (never cached) so cart updates correctly.
add_filter( 'theme_mod_ajax_add_to_cart', '__return_true' );

// Bust browser/CDN cache for the patched credit-cards.js (fix: field state
// key mismatch caused "credit card form incomplete" error on every submission).
add_filter( 'script_loader_src', function( $src, $handle ) {
	$patched_handles = array( 'wc-ppcp-card-gateway', 'ppcp-smart-button' );
	if ( in_array( $handle, $patched_handles, true ) ) {
		$src = add_query_arg( 'patch', '20260524d', $src );
	}
	return $src;
}, 10, 2 );

// Hide the third-party pymntpl-paypal-woocommerce card gateway (ppcp_card) from checkout UI.
add_filter( 'woocommerce_available_payment_gateways', function( $gateways ) {
	unset( $gateways['ppcp_card'] );
	return $gateways;
} );

// Prevent the third-party plugin's checkout scripts from loading.
// woocommerce_available_payment_gateways hides ppcp_card from the UI but does NOT
// stop the plugin from enqueuing scripts (it checks gateway::is_available(), not the
// WC filter). With its scripts active, the third-party plugin:
//   1. Loads the PayPal SDK at a different URL (different components) than the official
//      plugin — PayPal blocks a second SDK load with different params on the same page.
//   2. Instantiates paypal.CardFields() independently, conflicting with the official
//      plugin's instance and causing "credit card details are not valid" on submit.
// Removing these handles via the plugin's own filter eliminates both conflicts so
// the official ppcp-credit-card-gateway can handle direct card entry cleanly.
add_filter( 'wc_ppcp_script_dependencies', function( $handles ) {
	return array_diff( $handles, [
		'wc-ppcp-card-gateway',
		'wc-ppcp-checkout-gateway',
		'wc-ppcp-checkout-express',
	] );
} );