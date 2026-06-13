<?php
/**
 * One-time casino-spam database cleanup.
 * Runs automatically on the next WordPress page load, then deletes itself.
 * Deployed by Claude Code — safe to remove if already executed.
 *
 * To clean a NEW spam wave in the future: add its distinctive phrases to
 * $patterns below and bump JNB_SPAM_CLEANUP_VERSION so the guard re-runs.
 */

// Bump this whenever the pattern list changes so the cleanup runs again
// (the previous run set an option keyed to the old version).
define( 'JNB_SPAM_CLEANUP_VERSION', '2026-06-fanobet' );

add_action( 'init', 'jnb_spam_cleanup_run', 1 );

function jnb_spam_cleanup_run() {
    $done_option = 'jnb_spam_cleanup_done_' . JNB_SPAM_CLEANUP_VERSION;

    // Only run once per version; guard against concurrent requests.
    if ( get_option( $done_option ) ) {
        // Already ran — delete this file so it stops loading.
        @unlink( __FILE__ );
        return;
    }
    // Atomic lock: add_option returns false if the option already exists.
    if ( ! add_option( $done_option, '1', '', 'no' ) ) {
        return; // Another request is already running it.
    }

    global $wpdb;

    $patterns = [
        // ── Current wave: "fanobet / golden euro / tower rush" ───────────
        'fanobet',                 // fanobetcasino.net.nl, fanobet casino
        'golden euro casino',
        'goldeneurocasino',
        'golden-euro-casino',
        'tower rush app',
        'towerrush',
        'Authentic products, better prices',
        'For those seeking additional entertainment options',
        'offers a user-friendly gaming platform',
        'For those seeking premium-quality wellness products',
        'offers a trusted selection of supplements',
        // ── Previous wave: "1red casino" (kept for residual cleanup) ─────
        '1red casino',
        '1redcasino',
        '1red-casino',
        'seamless and enjoyable gaming experience from the very first click',
        'all the way to the final withdrawal',
        'deposits are processed instantly through trusted channels',
        'game selection covers all major categories without exception',
        'knowledgeable agents is available around the clock',
        'registration process is refreshingly quick',
        // ── Previous wave: "rouge casino" (kept for residual cleanup) ─────
        'rougecasinos.com',
        'rouge casino',
        'rougecasino',
        'experienced players choose to spend their gaming time',
        'diverse game library, transparent conditions',
        'spinning the reels on video slots',
        'testing your skills at the blackjack table',
        'respected developers in the industry',
        'presents them in a clean, intuitive layout',
        'Whether you prefer spinning the reels',
    ];

    // ── wp_posts ──────────────────────────────────────────────────────────────
    $post_ids = [];
    foreach ( $patterns as $p ) {
        $rows = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s OR post_excerpt LIKE %s OR post_title LIKE %s",
                '%' . $wpdb->esc_like( $p ) . '%',
                '%' . $wpdb->esc_like( $p ) . '%',
                '%' . $wpdb->esc_like( $p ) . '%'
            )
        );
        $post_ids = array_merge( $post_ids, $rows );
    }
    foreach ( array_unique( $post_ids ) as $id ) {
        $post = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_content, post_excerpt FROM {$wpdb->posts} WHERE ID = %d", $id ) );
        if ( $post ) {
            $wpdb->update(
                $wpdb->posts,
                [
                    'post_content' => jnb_strip_spam( $post->post_content, $patterns ),
                    'post_excerpt' => jnb_strip_spam( $post->post_excerpt, $patterns ),
                ],
                [ 'ID' => $id ]
            );
            clean_post_cache( $id );
        }
    }

    // ── wp_options ────────────────────────────────────────────────────────────
    foreach ( $patterns as $p ) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_value LIKE %s",
                '%' . $wpdb->esc_like( $p ) . '%'
            )
        );
        foreach ( $rows as $row ) {
            $wpdb->update(
                $wpdb->options,
                [ 'option_value' => jnb_strip_spam( $row->option_value, $patterns ) ],
                [ 'option_id' => $row->option_id ]
            );
            wp_cache_delete( $row->option_name, 'options' );
        }
    }

    // ── wp_postmeta ───────────────────────────────────────────────────────────
    foreach ( $patterns as $p ) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
                '%' . $wpdb->esc_like( $p ) . '%'
            )
        );
        foreach ( $rows as $row ) {
            $wpdb->update(
                $wpdb->postmeta,
                [ 'meta_value' => jnb_strip_spam( $row->meta_value, $patterns ) ],
                [ 'meta_id' => $row->meta_id ]
            );
        }
    }

    // ── wp_usermeta ───────────────────────────────────────────────────────────
    foreach ( $patterns as $p ) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_value LIKE %s",
                '%' . $wpdb->esc_like( $p ) . '%'
            )
        );
        foreach ( $rows as $row ) {
            $wpdb->update(
                $wpdb->usermeta,
                [ 'meta_value' => jnb_strip_spam( $row->meta_value, $patterns ) ],
                [ 'umeta_id' => $row->umeta_id ]
            );
        }
    }

    // Self-destruct
    @unlink( __FILE__ );
}

function jnb_strip_spam( $text, $patterns ) {
    if ( empty( $text ) ) {
        return $text;
    }
    foreach ( $patterns as $pattern ) {
        // Remove full HTML <p> blocks containing the pattern
        $text = preg_replace(
            '/<p[^>]*>(?:(?!<\/p>).)*' . preg_quote( $pattern, '/' ) . '(?:(?!<\/p>).)*<\/p>/is',
            '',
            $text
        );
        // Remove plain-text double-newline-separated paragraphs
        $paras = preg_split( '/(\n{2,})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE );
        $out   = [];
        for ( $i = 0; $i < count( $paras ); $i++ ) {
            if ( stripos( $paras[ $i ], $pattern ) === false ) {
                $out[] = $paras[ $i ];
            }
        }
        $text = implode( '', $out );
    }
    return trim( $text );
}
