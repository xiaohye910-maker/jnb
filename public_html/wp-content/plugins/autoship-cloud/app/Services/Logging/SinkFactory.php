<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The sink factory to build instances.
 *
 * @package  Autoship
 * @since    2.8.9
 */

namespace Autoship\Services\Logging;

use Autoship\Core\ClockInterface;
use RuntimeException;

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
	 * Constructor. Registers the default file logger.
	 *
	 * @param LoggingSettingsInterface $settings The logging settings.
	 * @param ClockInterface           $clock    The clock instance.
	 */
	public function __construct( LoggingSettingsInterface $settings, ClockInterface $clock ) {
		$this->register_sink( 'file', new FileSink( $settings, $clock ) );
	}

	/**
	 * Initializes the singleton instance with dependencies.
	 *
	 * @param LoggingSettingsInterface $settings The logging settings.
	 * @param ClockInterface           $clock    The clock instance.
	 *
	 * @return SinkFactory The initialized factory instance.
	 */
	public static function initialize( LoggingSettingsInterface $settings, ClockInterface $clock ): SinkFactory {
		self::$instance = new self( $settings, $clock );
		return self::$instance;
	}

	/**
	 * Returns the current factory instance.
	 *
	 * @return SinkFactory The current factory instance.
	 * @throws RuntimeException If the factory has not been initialized.
	 */
	public static function get_instance(): SinkFactory {
		if ( null === self::$instance ) {
			throw new RuntimeException( 'SinkFactory has not been initialized. Call SinkFactory::initialize() first.' );
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
