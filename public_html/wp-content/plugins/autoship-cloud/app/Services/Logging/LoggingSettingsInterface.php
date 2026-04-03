<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Logging Settings Interface.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.11.0
 */

namespace Autoship\Services\Logging;

/**
 * Interface for logging configuration settings.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.11.0
 */
interface LoggingSettingsInterface {

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool
	 */
	public function is_logging_enabled(): bool;

	/**
	 * Check if tracing/debug logging is enabled.
	 *
	 * @return bool
	 */
	public function is_tracing_enabled(): bool;

	/**
	 * Get the last cleanup timestamp.
	 *
	 * @return int
	 */
	public function get_last_cleanup_time(): int;

	/**
	 * Set the last cleanup timestamp.
	 *
	 * @param int $timestamp The cleanup timestamp.
	 *
	 * @return void
	 */
	public function set_last_cleanup_time( int $timestamp ): void;

	/**
	 * Check if log cleanup is allowed.
	 *
	 * @return bool
	 */
	public function can_cleanup_logs(): bool;

	/**
	 * Get the number of log files to keep.
	 *
	 * @return int
	 */
	public function get_keep_count(): int;

	/**
	 * Get the cleanup interval in seconds.
	 *
	 * @return int
	 */
	public function get_cleanup_interval(): int;

	/**
	 * Get the fully resolved log directory path with trailing slash.
	 *
	 * @return string
	 */
	public function get_log_directory(): string;

	/**
	 * Get the computed log filename.
	 *
	 * @return string
	 */
	public function get_log_filename(): string;
}
