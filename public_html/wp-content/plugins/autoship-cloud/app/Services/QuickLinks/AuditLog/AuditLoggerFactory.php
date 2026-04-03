<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Audit Logger Factory.
 *
 * Creates the appropriate audit logger based on configuration.
 *
 * @package Autoship\Services\QuickLinks\AuditLog
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog;

use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditLoggerInterface;
use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;
use Autoship\Services\QuickLinks\AuditLog\Strategies\DatabaseAuditLogger;
use Autoship\Services\QuickLinks\AuditLog\Strategies\FileAuditLogger;
use Autoship\Services\Logging\LoggerInterface;

/**
 * Audit Logger Factory.
 *
 * Factory class for creating audit logger instances based on configuration.
 */
class AuditLoggerFactory {

	/**
	 * WordPress functions interface.
	 *
	 * @var AuditFunctionInterface
	 */
	private $wp_functions;

	/**
	 * Logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param AuditFunctionInterface $wp_functions WordPress functions interface.
	 * @param LoggerInterface        $logger       The logger instance.
	 */
	public function __construct( AuditFunctionInterface $wp_functions, LoggerInterface $logger ) {
		$this->wp_functions = $wp_functions;
		$this->logger       = $logger;
	}

	/**
	 * Create an audit logger based on configuration.
	 *
	 * @param array $config Configuration array.
	 *
	 * @return AuditLoggerInterface
	 */
	public function create( array $config ): AuditLoggerInterface {
		$strategy = isset( $config['strategy'] ) ? $config['strategy'] : 'database';
		$settings = isset( $config['settings'][ $strategy ] )
			? $config['settings'][ $strategy ]
			: array();

		return $this->create_strategy( $strategy, $settings );
	}

	/**
	 * Create a specific logger strategy.
	 *
	 * @param string $strategy Strategy name.
	 * @param array  $settings Strategy-specific settings.
	 *
	 * @return AuditLoggerInterface
	 */
	public function create_strategy( string $strategy, array $settings = array() ): AuditLoggerInterface {
		switch ( $strategy ) {
			case 'file':
				return new FileAuditLogger( $this->wp_functions, $settings );

			case 'database':
			default:
				return new DatabaseAuditLogger( $this->wp_functions, $this->logger, $settings );
		}
	}

	/**
	 * Get available strategies.
	 *
	 * @return array
	 */
	public function get_available_strategies(): array {
		return array( 'database', 'file' );
	}

	/**
	 * Check if a strategy is available.
	 *
	 * @param string $strategy Strategy name.
	 * @param array  $settings Strategy settings.
	 *
	 * @return bool
	 */
	public function is_strategy_available( string $strategy, array $settings = array() ): bool {
		$logger = $this->create_strategy( $strategy, $settings );
		return $logger->is_available();
	}
}
