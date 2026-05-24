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

// Hide the third-party pymntpl-paypal-woocommerce card gateway (ppcp_card) from checkout.
// Both plugins instantiate paypal.CardFields() on the same page; their SDK instances
// interfere and every submission shows "credit card details are not valid."
// The official woocommerce-paypal-payments ppcp-credit-card-gateway handles direct
// card entry (no PayPal account required) and is the one users need.
// The PayPal button gateway (ppcp-gateway) is unaffected and keeps working.
add_filter( 'woocommerce_available_payment_gateways', function( $gateways ) {
	unset( $gateways['ppcp_card'] );
	return $gateways;
} );