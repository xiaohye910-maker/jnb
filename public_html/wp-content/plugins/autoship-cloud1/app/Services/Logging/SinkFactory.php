<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The sink factory to build instances.
 *
 * @package  Autoship
 * @since    2.8.9
 */

namespace Autoship\Services\Logging;

/**
 * The core sink factory.
 *
 * @package  Autoship
 * @since    2.8.9
 */
class SinkFactory {
	/**
	 * The singleton instance of the class.
	 *
	 * @var ?SinkFactory
	 */
	private static ?SinkFactory $instance = null;

	/**
	 * The registered sinks.
	 *
	 * @var array
	 */
	private array $loggers = array();

	/**
	 * The constructor is private to prevent direct instantiation.
	 */
	private function __construct() {
		// Register the default file logger.
		$this->register_sink( 'file', new FileSink() );
	}

	/**
	 * Returns the current factory instance.
	 *
	 * @return SinkFactory The current factory instance.
	 */
	public static function get_instance(): SinkFactory {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register a sink with the factory.
	 *
	 * @param string        $name The name of the sink.
	 * @param SinkInterface $logger The logger instance.
	 *
	 * @return void
	 */
	public function register_sink( string $name, SinkInterface $logger ): void {
		$this->loggers[ $name ] = $logger;
	}

	/**
	 * Get a sink by name.
	 *
	 * @param string $name The name of the sink.
	 *
	 * @return SinkInterface|null The sink instance or null if not found
	 */
	public function get_sink( string $name = 'file' ): ?SinkInterface {
		return $this->loggers[ $name ] ?? null;
	}

	/**
	 * Get the default sink
	 *
	 * @return SinkInterface The default sink.
	 */
	public function get_default_sink(): SinkInterface {
		return $this->get_sink();
	}
}
