<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLink Audit Service.
 *
 * Main service orchestrating audit logging with fallback logic.
 *
 * @package Autoship\Services\QuickLinks\AuditLog
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog;

use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditLoggerInterface;
use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;
use Autoship\Services\QuickLinks\AuditLog\DTOs\AuditEntry;
use Autoship\Services\Logging\LoggerInterface;

/**
 * QuickLink Audit Service.
 *
 * Orchestrates audit logging operations with automatic fallback
 * from database to file logging on failures.
 */
class QuickLinkAuditService {

	/**
	 * Option name for configuration.
	 */
	const CONFIG_OPTION = 'autoship_quicklinks_audit_config';

	/**
	 * Option name for last cleanup timestamp.
	 */
	const LAST_CLEANUP_OPTION = 'autoship_quicklinks_audit_last_cleanup';

	/**
	 * Primary audit logger.
	 *
	 * @var AuditLoggerInterface
	 */
	private $primary_logger;

	/**
	 * Fallback audit logger.
	 *
	 * @var AuditLoggerInterface|null
	 */
	private $fallback_logger;

	/**
	 * Service configuration.
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Logger factory.
	 *
	 * @var AuditLoggerFactory
	 */
	private $factory;

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
	 * @param AuditLoggerFactory     $factory      The logger factory.
	 * @param AuditFunctionInterface $wp_functions WordPress functions interface.
	 * @param LoggerInterface        $logger       The logger instance.
	 */
	public function __construct( AuditLoggerFactory $factory, AuditFunctionInterface $wp_functions, LoggerInterface $logger ) {
		$this->factory      = $factory;
		$this->wp_functions = $wp_functions;
		$this->logger       = $logger;
		$this->config       = $this->load_config();
		$this->initialize_loggers();
	}

	/**
	 * Log a QuickLink audit entry.
	 *
	 * @param AuditEntry $entry The entry to log.
	 *
	 * @return bool True if logged successfully.
	 */
	public function log( AuditEntry $entry ): bool {
		if ( ! $this->is_enabled() ) {
			$this->logger->info(
				'QuickLink Audit',
				'Audit logging is disabled, skipping entry'
			);
			return false;
		}

		$this->logger->info(
			'QuickLink Audit',
			sprintf(
				'Logging audit entry: action=%d, order=%d, success=%s, error=%s',
				$entry->get_action_type(),
				$entry->get_scheduled_order_id(),
				$entry->is_success() ? 'true' : 'false',
				$entry->get_error_code() ?? 'none'
			)
		);

		$success = $this->primary_logger->log( $entry );

		if ( $success ) {
			$this->logger->info(
				'QuickLink Audit',
				sprintf(
					'Audit entry logged successfully via %s',
					$this->primary_logger->get_strategy_name()
				)
			);
			$this->maybe_cleanup();
			return true;
		}

		$this->logger->error(
			'QuickLink Audit',
			sprintf(
				'Primary logger (%s) failed to log audit entry for order %d',
				$this->primary_logger->get_strategy_name(),
				$entry->get_scheduled_order_id()
			)
		);

		if ( $this->should_fallback() && null !== $this->fallback_logger ) {
			$this->logger->warning(
				'QuickLink Audit',
				sprintf(
					'Primary logger (%s) failed, falling back to %s',
					$this->primary_logger->get_strategy_name(),
					$this->fallback_logger->get_strategy_name()
				)
			);

			$success = $this->fallback_logger->log( $entry );

			if ( $success ) {
				$this->logger->info(
					'QuickLink Audit',
					sprintf(
						'Audit entry logged via fallback (%s)',
						$this->fallback_logger->get_strategy_name()
					)
				);
			} else {
				$this->logger->error(
					'QuickLink Audit',
					sprintf(
						'Fallback logger (%s) also failed for order %d',
						$this->fallback_logger->get_strategy_name(),
						$entry->get_scheduled_order_id()
					)
				);
			}
		}

		$this->maybe_cleanup();
		return $success;
	}

	/**
	 * Create an audit entry from controller context.
	 *
	 * @param int         $action_type        The action type.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param string      $ip_address         Client IP address.
	 * @param bool        $success            Whether action succeeded.
	 * @param int|null    $quicklink_id       QuickLink ID.
	 * @param string|null $slug               QuickLink URL slug.
	 * @param int|null    $customer_id        Customer ID.
	 * @param string|null $user_agent         User agent.
	 * @param string|null $token              Security token (will be hashed).
	 * @param string|null $error_code         Error code if failed.
	 * @param string|null $error_message      Error message if failed.
	 * @param int|null    $execution_time_ms  Execution time in ms.
	 *
	 * @return AuditEntry
	 */
	public function create_entry(
		int $action_type,
		int $scheduled_order_id,
		string $ip_address,
		bool $success,
		$quicklink_id = null,
		$slug = null,
		$customer_id = null,
		$user_agent = null,
		$token = null,
		$error_code = null,
		$error_message = null,
		$execution_time_ms = null
	): AuditEntry {
		$entry = new AuditEntry(
			$action_type,
			$scheduled_order_id,
			$ip_address,
			$success
		);

		if ( null !== $quicklink_id ) {
			$entry->set_quicklink_id( (int) $quicklink_id );
		}

		if ( null !== $slug ) {
			$entry->set_slug( substr( $slug, 0, 100 ) );
		}

		if ( null !== $customer_id ) {
			$entry->set_customer_id( (int) $customer_id );
		}

		if ( null !== $user_agent ) {
			$entry->set_user_agent( substr( $user_agent, 0, 500 ) );
		}

		if ( null !== $token ) {
			$entry->set_token_hash( hash( 'sha256', $token ) );
		}

		if ( null !== $error_code ) {
			$entry->set_error_code( substr( $error_code, 0, 50 ) );
		}

		if ( null !== $error_message ) {
			$entry->set_error_message( substr( $error_message, 0, 500 ) );
		}

		if ( null !== $execution_time_ms ) {
			$entry->set_execution_time_ms( (int) $execution_time_ms );
		}

		if ( $this->wp_functions->is_user_logged_in() ) {
			$entry->set_wp_user_id( $this->wp_functions->get_current_user_id() );
		}

		return $entry;
	}

	/**
	 * Check if audit logging is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return isset( $this->config['enabled'] ) ? (bool) $this->config['enabled'] : true;
	}

	/**
	 * Run cleanup on both loggers.
	 *
	 * @return array Cleanup results.
	 */
	public function run_cleanup(): array {
		$retention_days = $this->get_retention_days();
		$results        = array(
			'primary'  => 0,
			'fallback' => 0,
		);

		$results['primary'] = $this->primary_logger->cleanup( $retention_days );

		if ( null !== $this->fallback_logger ) {
			$results['fallback'] = $this->fallback_logger->cleanup( $retention_days );
		}

		$this->wp_functions->update_option( self::LAST_CLEANUP_OPTION, time() );

		return $results;
	}

	/**
	 * Load configuration from WordPress options.
	 *
	 * @return array
	 */
	private function load_config(): array {
		$config = $this->wp_functions->get_option( self::CONFIG_OPTION, null );

		if ( null !== $config && is_array( $config ) ) {
			return $config;
		}

		return array(
			'enabled'          => true,
			'strategy'         => 'database',
			'fallback_to_file' => true,
			'retention_days'   => 30,
			'cleanup_interval' => 86400,
			'settings'         => array(
				'database' => array(
					'table_name' => 'autoship_quicklink_audit_log',
				),
				'file'     => array(
					'directory' => '',
				),
			),
		);
	}

	/**
	 * Initialize loggers based on configuration.
	 *
	 * @return void
	 */
	private function initialize_loggers(): void {
		$this->primary_logger = $this->factory->create( $this->config );

		if ( $this->should_fallback() ) {
			$fallback_strategy = 'database' === $this->config['strategy'] ? 'file' : 'database';
			$fallback_settings = isset( $this->config['settings'][ $fallback_strategy ] )
				? $this->config['settings'][ $fallback_strategy ]
				: array();

			$this->fallback_logger = $this->factory->create_strategy(
				$fallback_strategy,
				$fallback_settings
			);
		}
	}

	/**
	 * Check if fallback is enabled.
	 *
	 * @return bool
	 */
	private function should_fallback(): bool {
		return isset( $this->config['fallback_to_file'] )
			? (bool) $this->config['fallback_to_file']
			: true;
	}

	/**
	 * Get retention days from config.
	 *
	 * @return int
	 */
	private function get_retention_days(): int {
		return isset( $this->config['retention_days'] )
			? (int) $this->config['retention_days']
			: 30;
	}

	/**
	 * Maybe run cleanup based on interval.
	 *
	 * @return void
	 */
	private function maybe_cleanup(): void {
		$last_cleanup = (int) $this->wp_functions->get_option( self::LAST_CLEANUP_OPTION, 0 );
		$interval     = isset( $this->config['cleanup_interval'] )
			? (int) $this->config['cleanup_interval']
			: 86400;

		if ( time() - $last_cleanup >= $interval ) {
			$this->run_cleanup();
		}
	}

	/**
	 * Get the primary logger instance.
	 *
	 * @return AuditLoggerInterface
	 */
	public function get_primary_logger(): AuditLoggerInterface {
		return $this->primary_logger;
	}

	/**
	 * Get the fallback logger instance.
	 *
	 * @return AuditLoggerInterface|null
	 */
	public function get_fallback_logger() {
		return $this->fallback_logger;
	}

	/**
	 * Get the current configuration.
	 *
	 * @return array
	 */
	public function get_config(): array {
		return $this->config;
	}
}
