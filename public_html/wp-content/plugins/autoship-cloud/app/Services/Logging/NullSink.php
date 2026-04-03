<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * A no-op sink that discards all log messages.
 *
 * @package  Autoship
 * @since    2.11.0
 */

namespace Autoship\Services\Logging;

/**
 * A no-op sink used when the logging subsystem has not been initialized.
 *
 * @package  Autoship
 * @since    2.11.0
 */
class NullSink implements SinkInterface {

	/**
	 * Log a message (no-op).
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @param string $type    The type of log entry.
	 * @return bool Always false.
	 */
	public function log( string $message, string $context = '', string $type = '' ): bool {
		return false;
	}

	/**
	 * Log an info message (no-op).
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Always false.
	 */
	public function info( string $message, string $context = '' ): bool {
		return false;
	}

	/**
	 * Log a trace message (no-op).
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Always false.
	 */
	public function trace( string $message, string $context = '' ): bool {
		return false;
	}

	/**
	 * Log a debug message (no-op).
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Always false.
	 */
	public function debug( string $message, string $context = '' ): bool {
		return false;
	}

	/**
	 * Log an error message (no-op).
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Always false.
	 */
	public function error( string $message, string $context = '' ): bool {
		return false;
	}

	/**
	 * Log a warning message (no-op).
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Always false.
	 */
	public function warning( string $message, string $context = '' ): bool {
		return false;
	}

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool Always false.
	 */
	public function is_enabled(): bool {
		return false;
	}

	/**
	 * Check if tracing is enabled.
	 *
	 * @return bool Always false.
	 */
	public function is_tracing_enabled(): bool {
		return false;
	}

	/**
	 * Check if log download is supported.
	 *
	 * @return bool Always false.
	 */
	public function can_download_logs(): bool {
		return false;
	}

	/**
	 * Download a log file (no-op).
	 *
	 * @param string $log_name The name of the log.
	 * @return void
	 */
	public function download_log( string $log_name ): void {
	}

	/**
	 * Check if log listing is supported.
	 *
	 * @return bool Always false.
	 */
	public function can_list_logs(): bool {
		return false;
	}

	/**
	 * List available logs.
	 *
	 * @return array Always empty.
	 */
	public function list_logs(): array {
		return array();
	}

	/**
	 * Check if log cleanup is supported.
	 *
	 * @return bool Always false.
	 */
	public function can_cleanup_logs(): bool {
		return false;
	}

	/**
	 * Perform log cleanup (no-op).
	 *
	 * @return bool Always false.
	 */
	public function cleanup_logs(): bool {
		return false;
	}

	/**
	 * Flush buffered logs (no-op).
	 *
	 * @return bool Always true.
	 */
	public function flush_logs(): bool {
		return true;
	}
}
