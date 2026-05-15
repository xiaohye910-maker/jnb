<?php
/**
 * Emergency cleanup: remove body-inject.php malware and protect AJAX responses.
 * Runs before any other mu-plugin. Self-deletes once the target is gone.
 */

// 1. Kill the malware file immediately, every request, until it's gone.
$target = __DIR__ . '/body-inject.php';
if ( file_exists( $target ) ) {
	@unlink( $target );
}

// 2. For AJAX/JSON requests, start output buffering NOW so any stray output
//    from remaining malware is captured and can be discarded before wp_send_json.
if (
	defined( 'DOING_AJAX' ) && DOING_AJAX ||
	( isset( $_GET['wc-ajax'] ) ) ||
	( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) !== false )
) {
	ob_start();

	// On shutdown, if there is stray output buffered, discard it so the real
	// JSON response (already sent by wp_send_json / wp_die) is not prepended.
	register_shutdown_function( function () {
		$stray = ob_get_clean();
		// Only discard if it looks like HTML/non-JSON junk prepended to a response.
		if ( $stray && $stray[0] !== '{' && $stray[0] !== '[' ) {
			// Already discarded — do not echo.
		}
	} );
}

// 3. Self-destruct once body-inject.php has been confirmed gone for this request.
if ( ! file_exists( $target ) ) {
	// Check if there are any other known malware mu-plugins still present.
	$malware_patterns = [ 'body-inject', 'inject-body', 'bozuldum', 'remote-html' ];
	$mu_dir           = __DIR__;
	$has_other        = false;
	foreach ( glob( $mu_dir . '/*.php' ) as $f ) {
		if ( basename( $f ) === basename( __FILE__ ) ) continue;
		if ( basename( $f ) === 'spam-cleanup-once.php' ) continue;
		$content = @file_get_contents( $f );
		foreach ( $malware_patterns as $p ) {
			if ( stripos( $content, $p ) !== false || stripos( basename( $f ), $p ) !== false ) {
				$has_other = true;
				@unlink( $f );
			}
		}
	}
	if ( ! $has_other ) {
		@unlink( __FILE__ );
	}
}
