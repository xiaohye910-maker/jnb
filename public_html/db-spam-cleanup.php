<?php
/**
 * One-time spam/malware database cleanup script.
 * Access: https://your-site.com/db-spam-cleanup.php?token=YOUR_TOKEN
 * DELETE THIS FILE after running it.
 */

// ── CONFIG ───────────────────────────────────────────────────────────────────
// Change this token before uploading, then pass it in the URL as ?token=...
define( 'CLEANUP_TOKEN', 'change-me-before-uploading' );

// Spam patterns to search for (case-insensitive substring matches)
$SPAM_PATTERNS = [
    'rougecasinos.com',
    'rouge casino',
    'rougecasino',
    'experienced players choose to spend their gaming time',
    'diverse game library, transparent conditions',
    'spinning the reels on video slots',
    'testing your skills at the blackjack table',
    'respected developers in the industry',
];
// ── END CONFIG ───────────────────────────────────────────────────────────────

// Validate token
$token = isset( $_GET['token'] ) ? $_GET['token'] : '';
if ( $token !== CLEANUP_TOKEN || CLEANUP_TOKEN === 'change-me-before-uploading' ) {
    http_response_code( 403 );
    exit( 'Forbidden. Set CLEANUP_TOKEN in the script and pass ?token=YOUR_TOKEN in the URL.' );
}

$do_clean = isset( $_GET['action'] ) && $_GET['action'] === 'clean';

// Bootstrap WordPress (read-only, no output buffering issues)
define( 'SHORTINIT', false );
require_once __DIR__ . '/wp-load.php';

global $wpdb;

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Spam Cleanup</title>
<style>
  body { font-family: monospace; background:#111; color:#eee; padding:20px; }
  h2 { color:#f90; }
  .found { color:#f66; }
  .clean { color:#6f6; }
  .section { border:1px solid #444; margin:12px 0; padding:12px; border-radius:4px; }
  table { border-collapse:collapse; width:100%; }
  td,th { border:1px solid #555; padding:6px 10px; font-size:13px; }
  th { background:#222; }
  .snippet { max-width:600px; word-break:break-all; color:#fc9; }
  a.btn { display:inline-block; margin-top:14px; padding:8px 18px;
          background:#c00; color:#fff; text-decoration:none; border-radius:4px; }
</style>
</head>
<body>
<h2>Casino Spam Cleanup &mdash; <?php echo esc_html( get_bloginfo('name') ); ?></h2>
<p><strong>Mode:</strong> <?php echo $do_clean ? '<span style="color:#f66">CLEAN (removing spam)</span>' : '<span style="color:#6cf">SCAN ONLY (no changes made)</span>'; ?></p>

<?php

$found_anything = false;
$results        = [];

// Build a single SQL LIKE condition for all patterns
function build_like_clauses( $col, $patterns ) {
    global $wpdb;
    $parts = [];
    foreach ( $patterns as $p ) {
        $parts[] = $wpdb->prepare( "$col LIKE %s", '%' . $wpdb->esc_like( $p ) . '%' );
    }
    return '(' . implode( ' OR ', $parts ) . ')';
}

// ── 1. wp_posts (page/post content, excerpt, title) ─────────────────────────
echo '<div class="section"><h3>1. wp_posts (page/post content)</h3>';

$cols = [ 'post_content', 'post_excerpt', 'post_title' ];
$col_checks = [];
foreach ( $cols as $c ) {
    $col_checks[] = build_like_clauses( $c, $SPAM_PATTERNS );
}
$where = implode( ' OR ', $col_checks );

$posts = $wpdb->get_results(
    "SELECT ID, post_type, post_status, post_title, post_content, post_excerpt FROM {$wpdb->posts} WHERE $where"
);

if ( $posts ) {
    $found_anything = true;
    echo '<table><tr><th>ID</th><th>Type</th><th>Status</th><th>Title</th><th>Snippet</th></tr>';
    foreach ( $posts as $p ) {
        // Highlight the spam
        $excerpt = esc_html( mb_substr( $p->post_content, 0, 400 ) );
        echo "<tr class='found'>";
        echo "<td>{$p->ID}</td><td>" . esc_html($p->post_type) . "</td>";
        echo "<td>" . esc_html($p->post_status) . "</td>";
        echo "<td>" . esc_html($p->post_title) . "</td>";
        echo "<td class='snippet'>$excerpt&hellip;</td>";
        echo "</tr>";

        if ( $do_clean ) {
            // Remove only the spam paragraphs; keep the rest of the content
            $clean_content  = remove_spam_blocks( $p->post_content, $SPAM_PATTERNS );
            $clean_excerpt  = remove_spam_blocks( $p->post_excerpt, $SPAM_PATTERNS );
            $wpdb->update(
                $wpdb->posts,
                [ 'post_content' => $clean_content, 'post_excerpt' => $clean_excerpt ],
                [ 'ID' => $p->ID ]
            );
            clean_post_cache( $p->ID );
            echo "<tr class='clean'><td colspan='5'>&#10003; Cleaned post ID {$p->ID}</td></tr>";
        }
    }
    echo '</table>';
} else {
    echo '<p class="clean">&#10003; No spam found in posts.</p>';
}
echo '</div>';

// ── 2. wp_options (widgets, header/footer scripts, theme mods, WPCode legacy) ─
echo '<div class="section"><h3>2. wp_options (widgets, header/footer scripts, theme settings)</h3>';

// Fetch all options that could contain text (skip autoloaded serialized arrays for speed)
$option_names_of_interest = [
    'widget_%',
    'ihaf_insert_header',
    'ihaf_insert_footer',
    'ihaf_insert_body',
    'simple_banner_options',
    'theme_mods_%',
    'elementor_data',
];

$like_clauses = [];
foreach ( $option_names_of_interest as $n ) {
    $like_clauses[] = $wpdb->prepare( 'option_name LIKE %s', $n );
}
$name_where = implode( ' OR ', $like_clauses );
$options = $wpdb->get_results(
    "SELECT option_id, option_name, option_value FROM {$wpdb->options}
     WHERE ($name_where) AND (" . build_like_clauses( 'option_value', $SPAM_PATTERNS ) . ")"
);

if ( $options ) {
    $found_anything = true;
    echo '<table><tr><th>option_id</th><th>option_name</th><th>Snippet</th></tr>';
    foreach ( $options as $o ) {
        $snippet = esc_html( mb_substr( $o->option_value, 0, 400 ) );
        echo "<tr class='found'><td>{$o->option_id}</td><td>" . esc_html($o->option_name) . "</td>";
        echo "<td class='snippet'>$snippet&hellip;</td></tr>";

        if ( $do_clean ) {
            $clean_value = remove_spam_blocks( $o->option_value, $SPAM_PATTERNS );
            $wpdb->update(
                $wpdb->options,
                [ 'option_value' => $clean_value ],
                [ 'option_id' => $o->option_id ]
            );
            wp_cache_delete( $o->option_name, 'options' );
            echo "<tr class='clean'><td colspan='3'>&#10003; Cleaned option: " . esc_html($o->option_name) . "</td></tr>";
        }
    }
    echo '</table>';
} else {
    echo '<p class="clean">&#10003; No spam found in options (widgets / header-footer scripts / banners).</p>';
}
echo '</div>';

// ── 3. wp_postmeta (custom fields, Elementor page builder data) ─────────────
echo '<div class="section"><h3>3. wp_postmeta (custom fields, page-builder data)</h3>';

$metas = $wpdb->get_results(
    "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta}
     WHERE " . build_like_clauses( 'meta_value', $SPAM_PATTERNS )
);

if ( $metas ) {
    $found_anything = true;
    echo '<table><tr><th>meta_id</th><th>post_id</th><th>meta_key</th><th>Snippet</th></tr>';
    foreach ( $metas as $m ) {
        $snippet = esc_html( mb_substr( $m->meta_value, 0, 400 ) );
        echo "<tr class='found'><td>{$m->meta_id}</td><td>{$m->post_id}</td>";
        echo "<td>" . esc_html($m->meta_key) . "</td><td class='snippet'>$snippet&hellip;</td></tr>";

        if ( $do_clean ) {
            $clean_value = remove_spam_blocks( $m->meta_value, $SPAM_PATTERNS );
            $wpdb->update(
                $wpdb->postmeta,
                [ 'meta_value' => $clean_value ],
                [ 'meta_id' => $m->meta_id ]
            );
            echo "<tr class='clean'><td colspan='4'>&#10003; Cleaned meta_id {$m->meta_id} (post {$m->post_id}: " . esc_html($m->meta_key) . ")</td></tr>";
        }
    }
    echo '</table>';
} else {
    echo '<p class="clean">&#10003; No spam found in postmeta.</p>';
}
echo '</div>';

// ── 4. wp_usermeta ───────────────────────────────────────────────────────────
echo '<div class="section"><h3>4. wp_usermeta</h3>';

$usermetas = $wpdb->get_results(
    "SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta}
     WHERE " . build_like_clauses( 'meta_value', $SPAM_PATTERNS )
);

if ( $usermetas ) {
    $found_anything = true;
    echo '<table><tr><th>umeta_id</th><th>user_id</th><th>meta_key</th><th>Snippet</th></tr>';
    foreach ( $usermetas as $u ) {
        $snippet = esc_html( mb_substr( $u->meta_value, 0, 400 ) );
        echo "<tr class='found'><td>{$u->umeta_id}</td><td>{$u->user_id}</td>";
        echo "<td>" . esc_html($u->meta_key) . "</td><td class='snippet'>$snippet&hellip;</td></tr>";

        if ( $do_clean ) {
            $clean_value = remove_spam_blocks( $u->meta_value, $SPAM_PATTERNS );
            $wpdb->update(
                $wpdb->usermeta,
                [ 'meta_value' => $clean_value ],
                [ 'umeta_id' => $u->umeta_id ]
            );
            echo "<tr class='clean'><td colspan='4'>&#10003; Cleaned umeta_id {$u->umeta_id}</td></tr>";
        }
    }
    echo '</table>';
} else {
    echo '<p class="clean">&#10003; No spam found in usermeta.</p>';
}
echo '</div>';

// ── Summary & action button ──────────────────────────────────────────────────
echo '<div class="section">';
if ( ! $found_anything ) {
    echo '<p class="clean" style="font-size:1.2em">&#10003; No casino/gambling spam found in the database.</p>';
} elseif ( ! $do_clean ) {
    $clean_url = '?token=' . urlencode( CLEANUP_TOKEN ) . '&action=clean';
    echo '<p class="found" style="font-size:1.1em">&#9888; Spam found (see above). Review the results, then click below to remove it.</p>';
    echo "<a class='btn' href='" . esc_url( $clean_url ) . "'>Remove all spam now</a>";
} else {
    echo '<p class="clean" style="font-size:1.2em">&#10003; Cleanup complete. <strong>Please delete this file (db-spam-cleanup.php) from your server immediately.</strong></p>';
}
echo '</div>';

// ── Helper: strip spam paragraphs from a string ──────────────────────────────
function remove_spam_blocks( $text, $patterns ) {
    if ( empty( $text ) ) {
        return $text;
    }

    // Try to remove full <p> blocks containing spam
    foreach ( $patterns as $pattern ) {
        // Remove HTML paragraphs containing the pattern
        $text = preg_replace(
            '/<p[^>]*>[^<]*' . preg_quote( $pattern, '/' ) . '[^<]*<\/p>/is',
            '',
            $text
        );
        // Remove plain-text paragraphs (double-newline separated)
        $paragraphs = preg_split( '/\n{2,}/', $text );
        $paragraphs = array_filter( $paragraphs, function( $p ) use ( $pattern ) {
            return stripos( $p, $pattern ) === false;
        } );
        $text = implode( "\n\n", $paragraphs );
    }

    return trim( $text );
}
?>
</body>
</html>
