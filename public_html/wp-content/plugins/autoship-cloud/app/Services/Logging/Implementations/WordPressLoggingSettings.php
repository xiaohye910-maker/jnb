<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the logging settings.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.11.0
 */

namespace Autoship\Services\Logging\Implementations;

use Autoship\Services\Logging\LoggingSettingsInterface;

/**
 * WordPress implementation of the logging settings.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.11.0
 */
class WordPressLoggingSettings implements LoggingSettingsInterface {

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool
	 */
	public function is_logging_enabled(): bool {
		return 'active' === get_option( 'autoship_logging_state', 'active' );
	}

	/**
	 * Check if tracing/debug logging is enabled.
	 *
	 * @return bool
	 */
	public function is_tracing_enabled(): bool {
		return 'active' === get_option( 'autoship_debug_state', '' );
	}

	/**
	 * Get the last cleanup timestamp.
	 *
	 * @return int
	 */
	public function get_last_cleanup_time(): int {
		$last = get_option( 'autoship_logs_last_cleanup', 0 );
		if ( is_numeric( $last ) && $last > 0 && $last <= time() ) {
			return (int) $last;
		}
		return 0;
	}

	/**
	 * Set the last cleanup timestamp.
	 *
	 * @param int $timestamp The cleanup timestamp.
	 *
	 * @return void
	 */
	public function set_last_cleanup_time( int $timestamp ): void {
		update_option( 'autoship_logs_last_cleanup', $timestamp );
	}

	/**
	 * Check if log cleanup is allowed.
	 *
	 * @return bool
	 */
	public function can_cleanup_logs(): bool {
		$can = apply_filters( 'autoship_clean_logger_files', true );
		return ! is_bool( $can ) ? true : $can;
	}

	/**
	 * Get the number of log files to keep.
	 *
	 * @return int
	 */
	public function get_keep_count(): int {
		$count = apply_filters( 'autoship_keep_logger_files_count', 30 );
		if ( ! is_int( $count ) || $count < 1 ) {
			$count = 30;
		}
		return $count;
	}

	/**
	 * Get the cleanup interval in seconds.
	 *
	 * @return int
	 */
	public function get_cleanup_interval(): int {
		$interval = apply_filters( 'autoship_log_cleanup_interval', 3600 );
		if ( is_int( $interval ) && $interval > 300 ) {
			return $interval;
		}
		return 3600;
	}

	/**
	 * Get the fully resolved log directory path with trailing slash.
	 *
	 * @return string
	 */
	public function get_log_directory(): string {
		$paths = wp_upload_dir( null, false );
		$dir   = apply_filters(
			'autoship_logs_directory_path',
			$paths['basedir'] . DIRECTORY_SEPARATOR . 'autoship-logs' . DIRECTORY_SEPARATOR
		);
		return trailingslashit( $dir );
	}

	/**
	 * Get the computed log filename.
	 *
	 * @return string
	 */
	public function get_log_filename(): string {
		$handle      = apply_filters( 'autoship_file_name_handle', strtolower( get_bloginfo( 'title' ) ) );
		$date_suffix = gmdate( 'Y-m-d' );
		$hash_suffix = substr( wp_hash( $handle ), 0, 10 );

		return sanitize_file_name(
			implode(
				'-',
				array(
					preg_replace( '/[^a-z0-9\-]/', '', strtolower( $handle ) ),
					$date_suffix,
					$hash_suffix,
					'autoship',
				)
			) . '.log'
		);
	}
}
