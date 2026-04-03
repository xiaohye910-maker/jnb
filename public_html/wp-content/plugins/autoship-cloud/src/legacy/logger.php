<?php
/**
 * The legacy logging functions.
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Services\Logging\Logger;

/**
 * Gets the given log name as a link.
 *
 * @param string $file The log name.
 *
 * @return string
 * @deprecated 2.13
 */
function autoship_get_log_file_download_link( string $file ): string {
	$path = autoship_admin_settings_tab_url( 'autoship-logs' );

	return $path . '&autoship_download_log_file=' . $file;
}

/**
 * Gets the logs of the plugin.
 *
 * @return array
 * @deprecated 2.13
 */
function autoship_get_log_files(): array {
	return Logger::get_logs();
}

/**
 * Logs an entry into the plugin log.
 *
 * @param string $context The context of the message.
 * @param string $message The actual message to log.
 *
 * @return bool
 */
function autoship_log_entry( string $context, string $message ): bool {
	// Allow logging to be performed separately when Autoship Logging turned off.
	do_action( 'autoship_before_log_entry', $context, $message );

	if ( ! apply_filters( 'autoship_log_entry', true, $context, $message ) ) {
		return true;
	}

	return Logger::log( $context, $message );

	// allows for email and other actions after a log entry has been added.
	// do_action('autoship_after_log_entry', $time, $entry, $context, $message ).
}

/**
 * Checks for the download filename as an action and perform the read. This needs to be a handled action.
 *
 * @return void
 * @deprecated 2.13
 */
function autoship_download_log_file(): void {
	if ( empty( $_GET['autoship_download_log_file'] ) || ! current_user_can( 'export' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$file = sanitize_text_field( wp_unslash( $_GET['autoship_download_log_file'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$logger = Logger::get_instance();
	$sink   = $logger->get_sink();

	if ( ! $sink->can_download_logs() ) {
		return;
	}

	$sink->download_log( $file );
}

add_action( 'admin_init', 'autoship_download_log_file' );
