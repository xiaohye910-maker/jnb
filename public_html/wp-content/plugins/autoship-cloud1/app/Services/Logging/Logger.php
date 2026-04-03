<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Logger class file.
 *
 * @package Autoship
 * @subpackage Logging
 */

namespace Autoship\Services\Logging;

/**
 * Logger class for handling logging operations.
 *
 * This class provides a standardized way to log messages with different severity levels.
 * It follows WordPress coding standards and implements the LoggerInterface.
 *
 * @since 1.0.0
 */
class Logger {
	/**
	 * The singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var ?Logger
	 */
	private static ?Logger $instance = null;

	/**
	 * The logger instance.
	 *
	 * @since 1.0.0
	 * @var SinkInterface
	 */
	private SinkInterface $sink;

	/**
	 * Contains the timers used for tracing.
	 *
	 * @var array
	 */
	private array $timers = array();

	/**
	 * The constructor is private to prevent direct instantiation.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->sink = SinkFactory::get_instance()->get_default_sink();
	}

	/**
	 * Log a trace message
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function trace( string $context, string $message ): bool {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return false;
		}

		return $logger->get_sink()->trace( $message, $context );
	}

	/**
	 * Log an info message
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function info( string $context, string $message ): bool {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return false;
		}

		return $logger->get_sink()->info( $message, $context );
	}

	/**
	 * Log a debug message
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function debug( string $context, string $message ): bool {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return false;
		}

		return $logger->get_sink()->debug( $message, $context );
	}

	/**
	 * Log an error message
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function error( string $context, string $message ): bool {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return false;
		}

		return $logger->get_sink()->error( $message, $context );
	}

	/**
	 * Log a warning message
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function warning( string $context, string $message ): bool {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return false;
		}

		return $logger->get_sink()->warning( $message, $context );
	}

	/**
	 * Check if logging is enabled
	 *
	 * @return bool Whether logging is enabled.
	 * @since 1.0.0
	 */
	public static function is_logging_enabled(): bool {
		$sink = SinkFactory::get_instance()->get_sink();
		return $sink->is_enabled();
	}

	/**
	 * Backward compatibility method for is_tracing_enabled
	 *
	 * @return bool Whether logging is enabled.
	 * @since 1.0.0
	 */
	public static function is_tracing_enabled(): bool {
		$sink = SinkFactory::get_instance()->get_sink();
		return $sink->is_tracing_enabled();
	}

	/**
	 * Check if logging is enabled
	 *
	 * @return bool Whether logging is enabled.
	 * @since 1.0.0
	 */
	public function is_enabled(): bool {
		return $this->sink->is_enabled();
	}

	/**
	 * Get the logger instance
	 *
	 * @return SinkInterface The logger instance.
	 * @since 1.0.0
	 */
	public function get_sink(): SinkInterface {
		return $this->sink;
	}

	/**
	 * Set the logger instance
	 *
	 * @param SinkInterface $sink The logger instance.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function set_sink( SinkInterface $sink ): void {
		$this->sink = $sink;
	}

	/**
	 * Returns the current logger instance.
	 *
	 * @return Logger The current logger instance.
	 * @since 1.0.0
	 */
	public static function get_instance(): Logger {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Helper function to log a message with a specific type
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @param string $type The type of log entry.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function log( string $message, string $context = '', string $type = '' ): bool {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return false;
		}

		return $logger->get_sink()->log( $message, $context, $type );
	}

	/**
	 * Helper function to log a message with timing information
	 *
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 * @param float  $start_time The start time in milliseconds.
	 *
	 * @return bool Whether the logging was successful.
	 * @since 1.0.0
	 */
	public static function log_with_timing( string $context, string $message, float $start_time ): bool {
		$end_time = floor( microtime( true ) * 1000 );
		$elapsed  = $end_time - $start_time;

		return self::trace( $context, sprintf( '%s. Elapsed time: %d milliseconds', $message, $elapsed ) );
	}

	/**
	 * Start a timer with a specific ID
	 *
	 * @param string $timer_id A unique identifier for this timer.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function start_timer( string $timer_id ): void {
		$timers                      = self::get_instance()->timers ?? array();
		$timers[ $timer_id ]         = floor( microtime( true ) * 1000 );
		self::get_instance()->timers = $timers;
	}

	/**
	 * Stop a timer and log the elapsed time
	 *
	 * @param string $timer_id The timer identifier.
	 * @param string $context The context of the message.
	 * @param string $message The message to log.
	 *
	 * @return bool Whether the logging was successful
	 * @since 1.0.0
	 */
	public static function stop_timer( string $timer_id, string $context, string $message ): bool {
		$timers = self::get_instance()->timers ?? array();

		if ( ! isset( $timers[ $timer_id ] ) ) {
			return false;
		}

		$start_time = $timers[ $timer_id ];
		$end_time   = floor( microtime( true ) * 1000 );
		$elapsed    = $end_time - $start_time;

		// Remove the timer.
		unset( $timers[ $timer_id ] );
		self::get_instance()->timers = $timers;

		return self::trace( $context, sprintf( '%s. Elapsed time: %d milliseconds', $message, $elapsed ) );
	}

	/**
	 * Gets a list of the logs if supported by the sink.
	 *
	 * @return array
	 */
	public static function get_logs(): array {
		$logger = self::get_instance();

		if ( ! $logger->is_enabled() ) {
			return array();
		}

		$sink = $logger->get_sink();
		if ( ! $sink->can_list_logs() ) {
			return array();
		}

		return $sink->list_logs();
	}
}
