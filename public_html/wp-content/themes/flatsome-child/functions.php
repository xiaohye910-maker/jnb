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
	if ( 'wc-ppcp-card-gateway' === $handle ) {
		$src = add_query_arg( 'patch', '20260524b', $src );
	}
	return $src;
}, 10, 2 );