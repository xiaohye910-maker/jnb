<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The base sink interface.
 *
 * @package  Autoship
 * @since    2.8.9
 */

namespace Autoship\Services\Logging;

/**
 * The logger interface for tge base sinks.
 *
 * @package  Autoship
 * @since    2.8.9
 */
interface SinkInterface {
	/**
	 * Log a message with a specific type
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @param string $type The type of log entry.
	 * @return bool Whether the logging was successful
	 */
	public function log( string $message, string $context = '', string $type = '' ): bool;

	/**
	 * Log an informational message.
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function info( string $message, string $context = '' ): bool;

	/**
	 * Log a trace message for performance tracking.
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function trace( string $message, string $context = '' ): bool;

	/**
	 * Log a debug message
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function debug( string $message, string $context = '' ): bool;

	/**
	 * Log an error message
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function error( string $message, string $context = '' ): bool;

	/**
	 * Log a warning message
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function warning( string $message, string $context = '' ): bool;

	/**
	 * Check if logging is enabled
	 *
	 * @return bool Whether logging is enabled
	 */
	public function is_enabled(): bool;

	/**
	 * Check if tracing is enabled.
	 *
	 * @return bool
	 */
	public function is_tracing_enabled(): bool;

	/**
	 * Returns the value that indicates if the logger has download capabilities.
	 *
	 * @return bool
	 */
	public function can_download_logs(): bool;

	/**
	 * Streams a log file for download.
	 *
	 * @param string $log_name The name of the log.
	 * @return void
	 */
	public function download_log( string $log_name ): void;

	/**
	 * Returns the value that indicates if the logger has log listing capabilities.
	 *
	 * @return bool
	 */
	public function can_list_logs(): bool;

	/**
	 * Returns an array of log names if they can be listed.
	 *
	 * @return array An array of filenames.
	 */
	public function list_logs(): array;

	/**
	 * Returns the value that indicates if the logger has log cleanup capabilities.
	 *
	 * @return bool
	 */
	public function can_cleanup_logs(): bool;

	/**
	 * Performs a log cleanup if the logger allows it.
	 *
	 * @return bool
	 */
	public function cleanup_logs(): bool;

	/**
	 * Flushes the buffer logs into the file.
	 *
	 * @return bool
	 */
	public function flush_logs(): bool;
}
