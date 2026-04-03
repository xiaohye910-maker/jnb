<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Logger interface for dependency injection and testability.
 *
 * This interface provides a contract for logging services that can be
 * injected into classes, enabling better testability and decoupling.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.8.9
 */

namespace Autoship\Services\Logging;

/**
 * Logger interface for dependency injection and testability.
 *
 * @package Autoship
 * @subpackage Logging
 * @since 2.8.9
 */
interface LoggerInterface {

	/**
	 * Log a message with a specific type.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 * @param string $type The type of log entry.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function log( string $context, string $message, string $type = '' ): bool;

	/**
	 * Log an informational message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function info( string $context, string $message ): bool;

	/**
	 * Log a debug message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function debug( string $context, string $message ): bool;

	/**
	 * Log an error message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function error( string $context, string $message ): bool;

	/**
	 * Log a warning message.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function warning( string $context, string $message ): bool;

	/**
	 * Log a trace message for performance tracking.
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 */
	public function trace( string $context, string $message ): bool;

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool Whether logging is enabled.
	 */
	public function is_enabled(): bool;
}
