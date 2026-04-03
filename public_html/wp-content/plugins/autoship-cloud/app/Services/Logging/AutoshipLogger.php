<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * AutoshipLogger implementation that wraps the existing Logger class.
 *
 * This class provides a dependency-injectable logger service that delegates
 * to the existing static Logger class, enabling better testability while
 * maintaining compatibility with the current logging infrastructure.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.8.9
 */

namespace Autoship\Services\Logging;

/**
 * AutoshipLogger implementation that wraps the existing Logger class.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.8.9
 */
class AutoshipLogger implements LoggerInterface {

	/**
	 * Log a message with a specific type.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 * @param string $type The type of log entry.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function log( string $context, string $message, string $type = '' ): bool {
		return Logger::log( $context, $message, $type );
	}

	/**
	 * Log an informational message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function info( string $context, string $message ): bool {
		return Logger::info( $context, $message );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function debug( string $context, string $message ): bool {
		return Logger::debug( $context, $message );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function error( string $context, string $message ): bool {
		return Logger::error( $context, $message );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function warning( string $context, string $message ): bool {
		return Logger::warning( $context, $message );
	}

	/**
	 * Log a trace message for performance tracking.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function trace( string $context, string $message ): bool {
		return Logger::trace( $context, $message );
	}

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool Whether logging is enabled.
	 */
	public function is_enabled(): bool {
		return Logger::is_logging_enabled();
	}
}
