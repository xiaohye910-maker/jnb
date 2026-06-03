<?php
/**
 * Read-only security audit for recurring spam/malware investigation.
 * Access: https://your-site.com/security-audit.php?token=jnb-fix-2026
 * DELETE THIS FILE after reviewing the report.
 *
 * STRICTLY READ-ONLY: this script only runs SELECT queries and reads files.
 * It never writes to the database or filesystem.
 */

define( 'AUDIT_TOKEN', 'jnb-fix-2026' );

$token = isset( $_GET['token'] ) ? $_GET['token'] : '';
if ( ! hash_equals( AUDIT_TOKEN, (string) $token ) ) {
    http_response_code( 403 );
    exit( 'Forbidden. Append ?token=YOUR_TOKEN to the URL.' );
}

require_once __DIR__ . '/wp-load.php';

// Defence in depth: the report exposes user emails, so also require that the
// viewer is a logged-in administrator in this browser session.
if ( ! current_user_can( 'manage_options' ) ) {
    http_response_code( 403 );
    exit( 'Forbidden. Log in to WordPress as an administrator first, then reload this URL (with the token).' );
}

global $wpdb;
nocache_headers();
header( 'Content-Type: text/plain; charset=utf-8' );

function h( $t ) { echo "\n========== $t ==========\n"; }

echo "SECURITY AUDIT — " . get_bloginfo( 'name' ) . " (" . home_url() . ")\n";
echo "generated: " . gmdate( 'c' ) . "\n";

// ── 1) Administrator accounts ────────────────────────────────────────────────
h( '1) ADMINISTRATOR ACCOUNTS (look for any you do not recognise)' );
$admins = get_users( [ 'role' => 'administrator', 'orderby' => 'registered', 'order' => 'ASC' ] );
echo "admin_count: " . count( $admins ) . "\n\n";
foreach ( $admins as $u ) {
    printf(
        "ID=%-5d login=%-22s email=%-32s registered=%s\n",
        $u->ID, $u->user_login, $u->user_email, $u->user_registered
    );
}

// ── 2) Recently registered users (last 60 days, any role) ───────────────────
h( '2) USERS REGISTERED IN LAST 60 DAYS (any role)' );
$recent = $wpdb->get_results( $wpdb->prepare(
    "SELECT ID, user_login, user_email, user_registered
       FROM {$wpdb->users}
      WHERE user_registered >= %s
   ORDER BY user_registered DESC
      LIMIT 100",
    gmdate( 'Y-m-d H:i:s', time() - 60 * DAY_IN_SECONDS )
) );
if ( $recent ) {
    foreach ( $recent as $u ) {
        $roles = get_userdata( $u->ID )->roles;
        printf( "ID=%-5d login=%-22s email=%-32s registered=%s roles=%s\n",
            $u->ID, $u->user_login, $u->user_email, $u->user_registered, implode( ',', (array) $roles ) );
    }
} else {
    echo "(none)\n";
}

// ── 3) Scheduled cron events (a hidden recurring task can re-inject spam) ────
h( '3) SCHEDULED CRON EVENTS (unknown hooks = suspicious)' );
$cron = _get_cron_array();
$known_prefixes = [ 'wp_', 'action_scheduler', 'woocommerce', 'jetpack', 'wpseo', 'rank_math',
    'aioseo', 'monsterinsights', 'updraft', 'elementor', 'astra', 'flatsome', 'wc_', 'gravityview',
    'do_pings', 'publish_future', 'delete_', 'recovery_mode', 'wp', 'mailpoet', 'wpforms' ];
if ( is_array( $cron ) ) {
    foreach ( $cron as $ts => $hooks ) {
        if ( ! is_array( $hooks ) ) { continue; }
        foreach ( $hooks as $hook => $events ) {
            $known = false;
            foreach ( $known_prefixes as $p ) {
                if ( stripos( $hook, $p ) === 0 ) { $known = true; break; }
            }
            $flag = $known ? '   ' : '!! ';
            printf( "%snext=%s  hook=%s\n", $flag, gmdate( 'Y-m-d H:i', $ts ), $hook );
        }
    }
    echo "\n(lines starting with !! are hooks not matching common-plugin prefixes — inspect these)\n";
} else {
    echo "(no cron array)\n";
}

// ── 4) Autoloaded options that contain executable-looking code ──────────────
h( '4) OPTIONS CONTAINING CODE-LIKE STRINGS (injected payloads hide here)' );
$needles = [ '<?php', 'eval(', 'base64_decode', 'gzinflate', 'gzuncompress', 'str_rot13', 'create_function', 'system(' ];
$where = [];
foreach ( $needles as $n ) {
    $where[] = $wpdb->prepare( 'option_value LIKE %s', '%' . $wpdb->esc_like( $n ) . '%' );
}
$rows = $wpdb->get_results(
    "SELECT option_id, option_name, autoload, LENGTH(option_value) AS len
       FROM {$wpdb->options}
      WHERE " . implode( ' OR ', $where ) . "
   ORDER BY len DESC
      LIMIT 60"
);
if ( $rows ) {
    foreach ( $rows as $o ) {
        printf( "option=%-40s autoload=%-4s len=%d\n", $o->option_name, $o->autoload, $o->len );
    }
    echo "\n(many of these are legitimate — e.g. theme/widget HTML. Inspect any with an odd name.)\n";
} else {
    echo "(none)\n";
}

// ── 5) Recently modified posts/pages (the spam injection footprint) ─────────
h( '5) POSTS/PAGES MODIFIED IN LAST 45 DAYS' );
$posts = $wpdb->get_results( $wpdb->prepare(
    "SELECT ID, post_type, post_status, post_author, post_modified, post_title
       FROM {$wpdb->posts}
      WHERE post_type IN ('post','page')
        AND post_status IN ('publish','draft','private','pending')
        AND post_modified >= %s
   ORDER BY post_modified DESC
      LIMIT 60",
    gmdate( 'Y-m-d H:i:s', time() - 45 * DAY_IN_SECONDS )
) );
if ( $posts ) {
    foreach ( $posts as $p ) {
        $author = get_the_author_meta( 'user_login', $p->post_author );
        printf( "%s  ID=%-6d %-7s %-9s author=%-15s  %s\n",
            $p->post_modified, $p->ID, $p->post_type, $p->post_status, $author, $p->post_title );
    }
} else {
    echo "(none)\n";
}

// ── 6) Live filesystem: PHP in uploads + signature scan ─────────────────────
// The repo deploy EXCLUDES uploads/, so malware dropped there only shows here.
h( '6) PHP FILES UNDER wp-content/uploads (should be only index.php stubs)' );
$uploads = wp_get_upload_dir();
$base    = isset( $uploads['basedir'] ) ? $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';
$php_in_uploads = [];
if ( is_dir( $base ) ) {
    $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $it as $file ) {
        if ( preg_match( '/\.(php|phtml|php5|php7|pht|phar)$/i', $file->getFilename() ) ) {
            $php_in_uploads[] = $file->getPathname();
        }
    }
}
if ( $php_in_uploads ) {
    foreach ( $php_in_uploads as $f ) {
        $sz = @filesize( $f );
        $first = trim( (string) @file_get_contents( $f, false, null, 0, 60 ) );
        $stub  = ( stripos( $first, 'Silence is golden' ) !== false );
        printf( "%s  %s (%d bytes)%s\n", $stub ? '   ' : '!! ', $f, $sz, $stub ? '  [silence stub - ok]' : '  [INSPECT]' );
    }
    echo "\n(lines with !! are PHP files in uploads that are NOT the silence stub — almost always malware)\n";
} else {
    echo "(no PHP files in uploads — good)\n";
}

h( '7) SIGNATURE SCAN OF LIVE wp-content (mu-plugins, themes, uploads roots)' );
$scan_dirs = [ WPMU_PLUGIN_DIR ?? ( WP_CONTENT_DIR . '/mu-plugins' ), $base ];
$sig = '/eval\s*\(\s*(base64_decode|gzinflate|gzuncompress|str_rot13)|assert\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)|\$_(GET|POST|REQUEST|COOKIE)\s*\[[^\]]+\]\s*\(|preg_replace\s*\(\s*([\'"]).*\/e\2|gzinflate\s*\(\s*base64_decode|create_function\s*\(/i';
$hits = 0;
foreach ( array_unique( $scan_dirs ) as $dir ) {
    if ( ! is_dir( $dir ) ) { continue; }
    $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $it as $file ) {
        if ( ! preg_match( '/\.php$/i', $file->getFilename() ) ) { continue; }
        $c = @file_get_contents( $file->getPathname() );
        if ( $c && preg_match( $sig, $c ) ) {
            echo "!! " . $file->getPathname() . "\n";
            $hits++;
        }
    }
}
echo $hits ? "\n($hits file(s) matched shell signatures — inspect)\n" : "(no shell signatures in scanned live dirs — good)\n";

// ── 8) Residual casino-spam check on the live DB ────────────────────────────
h( '8) RESIDUAL CASINO-SPAM CHECK (current + previous waves)' );
$spam = [ '1red casino', '1redcasino', 'rouge casino', 'rougecasino',
    'deposits are processed instantly through trusted channels',
    'experienced players choose to spend their gaming time' ];
$found = 0;
foreach ( $spam as $s ) {
    $like = '%' . $wpdb->esc_like( $s ) . '%';
    $c  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts}    WHERE post_content LIKE %s OR post_title LIKE %s", $like, $like ) );
    $c += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options}  WHERE option_value LIKE %s", $like ) );
    $c += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) );
    if ( $c > 0 ) { printf( "!! '%s' still present in %d row(s)\n", $s, $c ); $found += $c; }
}
echo $found ? "\n($found total matches still in DB)\n" : "(clean — no casino spam in posts/options/postmeta)\n";

echo "\n\nDONE. Delete security-audit.php from the server after reviewing.\n";
