<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLinks Module.
 *
 * Provides one-click subscription management via secure URLs.
 *
 * @package Autoship\Modules\QuickLinks
 * @since   3.2.0
 */

namespace Autoship\Modules\QuickLinks;

use Autoship\Core\ModuleInterface;
use Autoship\Core\Plugin;
use Autoship\Core\ServiceContainer;
use Autoship\Services\QuickLinks\Implementations\QuickLinkRepository;
use Autoship\Services\QuickLinks\Implementations\QuickLinkService;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Services\QuickLinks\QuickLinkActionFactory;
use Autoship\Services\QuickLinks\RateLimiter\RateLimiter;
use Autoship\Services\QuickLinks\RateLimiter\RateLimiterStorageFactory;
use Autoship\Services\QuickLinks\EmailScanner\EmailScannerDetector;
use Autoship\Services\QuickLinks\AuditLog\AuditLoggerFactory;
use Autoship\Services\QuickLinks\AuditLog\QuickLinkAuditService;
use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;
use Autoship\Services\QuickLinks\AuditLog\Implementations\WordPressAuditFunctions;
use Autoship\Services\QuickLinks\AuditLog\DTOs\AuditEntry;
use Autoship\Services\QuickLinks\Confirmation\ConfirmationTableMigration;
use Autoship\Services\QuickLinks\Confirmation\ConfirmationCleanupScheduler;
use Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService;
use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationFunctionInterface;
use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationRepositoryInterface;
use Autoship\Services\QuickLinks\Confirmation\Implementations\WordPressConfirmationFunctions;
use Autoship\Services\QuickLinks\Confirmation\Implementations\ConfirmationRepository;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Modules\QuickLinks\Controllers\QuickLinksSettingsController;
use Exception;

/**
 * QuickLinks Module.
 *
 * Registers and boots QuickLinks functionality for one-click
 * subscription management.
 */
class QuickLinksModule implements ModuleInterface {

	/**
	 * Rewrite rules version. Increment this when rewrite rules change.
	 *
	 * @var string
	 */
	const REWRITE_RULES_VERSION = '1.0.0';

	/**
	 * Option name for storing rewrite rules version.
	 *
	 * @var string
	 */
	const REWRITE_RULES_VERSION_OPTION = 'autoship_quicklinks_rewrite_version';

	/**
	 * The QuickLinks service instance.
	 *
	 * @var QuickLinksService|null
	 */
	protected ?QuickLinksService $service = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Register the module services in the container.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function register( ServiceContainer $container ): void {
		// Register QPilot HTTP Client.
		$container->register(
			QPilotHttpClient::class,
			function () {
				$container   = Plugin::get_service_container();
				$environment = $container->get( EnvironmentInterface::class );
				$client      = new QPilotHttpClient( $environment->get_api_url() );

				// Set the authentication token and site ID from WordPress options.
				$token_auth = get_option( 'autoship_token_auth', '' );
				$site_id    = get_option( 'autoship_site_id', 0 );

				if ( ! empty( $token_auth ) ) {
					$client->set_token_auth( $token_auth );
				}

				if ( ! empty( $site_id ) ) {
					$client->set_site_id( (int) $site_id );
				}

				// Set the source for API tracking.
				$client->set_source( 'AC-WC-' . $environment->get_autoship_version() );

				return $client;
			}
		);

		// Register QuickLink Repository.
		$container->register(
			QuickLinkRepositoryInterface::class,
			function () {
				$container = Plugin::get_service_container();
				$client    = $container->get( QPilotHttpClient::class );
				$logger    = $container->get( LoggerInterface::class );

				return new QuickLinkRepository( $client, $logger );
			}
		);

		// Register QuickLink Action Factory.
		$container->register(
			QuickLinkActionFactory::class,
			function () {
				$container  = Plugin::get_service_container();
				$repository = $container->get( QuickLinkRepositoryInterface::class );
				$logger     = $container->get( LoggerInterface::class );

				return new QuickLinkActionFactory( $repository, $logger );
			}
		);

		// Register QuickLink Service.
		$container->register(
			QuickLinkServiceInterface::class,
			function () {
				$container  = Plugin::get_service_container();
				$repository = $container->get( QuickLinkRepositoryInterface::class );
				$factory    = $container->get( QuickLinkActionFactory::class );

				return new QuickLinkService( $repository, $factory );
			}
		);

		// Register Email Scanner Detector.
		$container->register(
			EmailScannerDetector::class,
			function () {
				return new EmailScannerDetector(
					$this->get_scanner_config()
				);
			}
		);

		// Register QuickLinks Module Service (facade).
		$container->register(
			QuickLinksService::class,
			function () {
				$container            = Plugin::get_service_container();
				$quicklink_service    = $container->get( QuickLinkServiceInterface::class );
				$rate_limiter         = $container->get( RateLimiter::class );
				$scanner_detector     = $container->get( EmailScannerDetector::class );
				$audit_service        = $container->get( QuickLinkAuditService::class );
				$confirmation_service = $container->get( QuickLinkConfirmationService::class );

				return new QuickLinksService(
					$quicklink_service,
					$rate_limiter,
					$scanner_detector,
					$audit_service,
					$confirmation_service
				);
			}
		);

		// Register Rate Limiter Storage Factory.
		$container->register(
			RateLimiterStorageFactory::class,
			function () {
				return new RateLimiterStorageFactory();
			}
		);

		// Register Rate Limiter.
		$container->register(
			RateLimiter::class,
			function () {
				$container = Plugin::get_service_container();
				$config    = $this->get_rate_limiter_config();
				$factory   = $container->get( RateLimiterStorageFactory::class );
				$storage   = $factory->create( $config );

				return new RateLimiter(
					$storage,
					isset( $config['max_attempts'] ) ? (int) $config['max_attempts'] : 5,
					isset( $config['window_seconds'] ) ? (int) $config['window_seconds'] : 60,
					isset( $config['enabled'] ) ? (bool) $config['enabled'] : true
				);
			}
		);

		// Register Audit Function Interface (WordPress implementation).
		$container->register(
			AuditFunctionInterface::class,
			function () {
				return new WordPressAuditFunctions();
			}
		);

		// Register Audit Logger Factory.
		$container->register(
			AuditLoggerFactory::class,
			function () {
				$container    = Plugin::get_service_container();
				$wp_functions = $container->get( AuditFunctionInterface::class );
				$logger       = $container->get( LoggerInterface::class );

				return new AuditLoggerFactory( $wp_functions, $logger );
			}
		);

		// Register QuickLink Audit Service.
		$container->register(
			QuickLinkAuditService::class,
			function () {
				$container    = Plugin::get_service_container();
				$factory      = $container->get( AuditLoggerFactory::class );
				$wp_functions = $container->get( AuditFunctionInterface::class );
				$logger       = $container->get( LoggerInterface::class );

				// Set wp_functions on AuditEntry for static usage.
				AuditEntry::set_wp_functions( $wp_functions );

				return new QuickLinkAuditService( $factory, $wp_functions, $logger );
			}
		);

		// Register Confirmation Function Interface (WordPress implementation).
		$container->register(
			ConfirmationFunctionInterface::class,
			function () {
				return new WordPressConfirmationFunctions();
			}
		);

		// Register Confirmation Repository.
		$container->register(
			ConfirmationRepositoryInterface::class,
			function () {
				$container    = Plugin::get_service_container();
				$wp_functions = $container->get( ConfirmationFunctionInterface::class );

				return new ConfirmationRepository( $wp_functions );
			}
		);

		// Register Confirmation Table Migration.
		$container->register(
			ConfirmationTableMigration::class,
			function () {
				$container    = Plugin::get_service_container();
				$wp_functions = $container->get( ConfirmationFunctionInterface::class );

				return new ConfirmationTableMigration( $wp_functions );
			}
		);

		// Register QuickLink Confirmation Service.
		$container->register(
			QuickLinkConfirmationService::class,
			function () {
				$container    = Plugin::get_service_container();
				$repository   = $container->get( ConfirmationRepositoryInterface::class );
				$wp_functions = $container->get( ConfirmationFunctionInterface::class );

				return new QuickLinkConfirmationService( $repository, $wp_functions );
			}
		);

		// Register Confirmation Cleanup Scheduler.
		$container->register(
			ConfirmationCleanupScheduler::class,
			function () {
				$container            = Plugin::get_service_container();
				$confirmation_service = $container->get( QuickLinkConfirmationService::class );
				$logger               = $container->get( LoggerInterface::class );

				return new ConfirmationCleanupScheduler( $confirmation_service, $logger );
			}
		);

		// Register Settings Controller.
		$container->register(
			QuickLinksSettingsController::class,
			function () {
				return new QuickLinksSettingsController();
			}
		);
	}

	/**
	 * Get the rate limiter configuration.
	 *
	 * Checks the new consolidated settings option first, then falls back
	 * to the legacy option for backward compatibility.
	 *
	 * @return array The configuration array.
	 */
	private function get_rate_limiter_config(): array {
		// Try new consolidated settings first.
		$settings = get_option( 'autoship_quicklinks_settings', null );

		if ( null !== $settings && is_array( $settings ) && isset( $settings['rate_limiter'] ) ) {
			$rl = $settings['rate_limiter'];
			return array(
				'enabled'        => isset( $rl['enabled'] ) ? $rl['enabled'] : true,
				'strategy'       => isset( $rl['strategy'] ) ? $rl['strategy'] : 'transient',
				'max_attempts'   => isset( $rl['max_attempts'] ) ? $rl['max_attempts'] : 5,
				'window_seconds' => isset( $rl['window_seconds'] ) ? $rl['window_seconds'] : 60,
				'settings'       => array(
					'transient' => array(),
					'database'  => array( 'table_name' => 'autoship_rate_limits' ),
					'wp_cache'  => array( 'group' => 'autoship_ratelimit' ),
					'file'      => array( 'directory' => '' ),
				),
			);
		}

		// Fall back to legacy option.
		$config = get_option( 'autoship_quicklinks_rate_limiter', null );

		if ( null !== $config && is_array( $config ) ) {
			return $config;
		}

		// Return default configuration.
		return array(
			'enabled'        => true,
			'strategy'       => 'transient',
			'max_attempts'   => 5,
			'window_seconds' => 60,
			'settings'       => array(
				'transient' => array(),
				'database'  => array( 'table_name' => 'autoship_rate_limits' ),
				'wp_cache'  => array( 'group' => 'autoship_ratelimit' ),
				'file'      => array( 'directory' => '' ),
			),
		);
	}

	/**
	 * Get the email scanner detector configuration.
	 *
	 * Checks the new consolidated settings option first, then falls back
	 * to the legacy option for backward compatibility.
	 *
	 * @return array The configuration array.
	 */
	private function get_scanner_config(): array {
		// Try new consolidated settings first.
		$settings = get_option( 'autoship_quicklinks_settings', null );

		if ( null !== $settings && is_array( $settings ) && isset( $settings['scanner'] ) ) {
			$sc = $settings['scanner'];
			return array(
				'enabled'              => isset( $sc['enabled'] ) ? $sc['enabled'] : true,
				'block_actions'        => array( 2 ), // Only Process Now (action_type = 2).
				'behavioral_detection' => array(
					'enabled'                 => isset( $sc['behavioral_detection'] ) ? $sc['behavioral_detection'] : true,
					'require_accept_language' => isset( $sc['require_accept_language'] ) ? $sc['require_accept_language'] : true,
					'require_html_accept'     => isset( $sc['require_html_accept'] ) ? $sc['require_html_accept'] : true,
					'min_suspicious_count'    => isset( $sc['min_suspicious_count'] ) ? $sc['min_suspicious_count'] : 2,
				),
			);
		}

		// Fall back to legacy option.
		$config = get_option( 'autoship_quicklinks_scanner_config', null );

		if ( null !== $config && is_array( $config ) ) {
			return $config;
		}

		// Return default configuration.
		return array(
			'enabled'              => true,
			'block_actions'        => array( 2 ), // Only Process Now (action_type = 2).
			'behavioral_detection' => array(
				'enabled'                 => true,
				'require_accept_language' => true,
				'require_html_accept'     => true,
				'min_suspicious_count'    => 2,
			),
		);
	}

	/**
	 * Boot the module.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function boot( ServiceContainer $container ): void {
		try {
			$logger = $container->get( LoggerInterface::class );
		} catch ( Exception $e ) {
			$logger = null;
		}

		try {

			// Run confirmation table migration.
			$migration = $container->get( ConfirmationTableMigration::class );
			$migration->run();

			// Initialize confirmation cleanup hook handler.
			$cleanup_scheduler = $container->get( ConfirmationCleanupScheduler::class );
			$cleanup_scheduler->initialize();

			// Schedule the cleanup task on 'init' hook when Action Scheduler is available.
			add_action(
				'init',
				function () use ( $cleanup_scheduler ) {
					$cleanup_scheduler->schedule_task();
				},
				20 // Run after WooCommerce Action Scheduler is loaded.
			);

			try {
				// Get the QuickLinks service.
				$service = $container->get( QuickLinksService::class );
			} catch ( Exception $e ) {
				if ( null !== $logger ) {
					$logger->log( 'Autoship QuickLinks', 'Failed to get QuickLinks service: ' . $e->getMessage(), 'error' );
				}
				return;
			}

			$this->service = $service;

			// Initialize the service (registers rewrite rules and hooks).
			try {
				$this->service->initialize();
			} catch ( Exception $e ) {
				if ( null !== $logger ) {
					$logger->log( 'Autoship QuickLinks', 'Failed to initialize QuickLinks service: ' . $e->getMessage(), 'error' );
				}
				return;
			}

			// Flush rewrite rules once when QuickLinks is first activated or updated.
			$this->maybe_flush_rewrite_rules();

			// Initialize settings controller in admin context.
			if ( is_admin() ) {
				$settings_controller = $container->get( QuickLinksSettingsController::class );
				$settings_controller->register();
			}
		} catch ( Exception $exception ) {
			if ( null !== $logger ) {
				$logger->log(
					'Autoship QuickLinks',
					'Unable to initialize QuickLinks service: ' . $exception->getMessage()
				);
			}
		}
	}

	/**
	 * Perform operations on plugin uninstall.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void {
		global $wpdb;

		// Drop rate limits table if exists.
		$table_name = $wpdb->prefix . 'autoship_rate_limits';
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Drop audit log table if exists.
		$audit_table = $wpdb->prefix . 'autoship_quicklink_audit_log';
		$wpdb->query( "DROP TABLE IF EXISTS {$audit_table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Drop confirmation table if exists.
		$confirmation_table = $wpdb->prefix . ConfirmationTableMigration::TABLE_NAME;
		$wpdb->query( "DROP TABLE IF EXISTS {$confirmation_table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Delete confirmation table version option.
		delete_option( ConfirmationTableMigration::VERSION_OPTION );

		// Unschedule confirmation cleanup and delete option.
		$cleanup_scheduler = $container->get( ConfirmationCleanupScheduler::class );
		$cleanup_scheduler->unschedule_task();
		delete_option( ConfirmationCleanupScheduler::LAST_CLEANUP_OPTION );

		// Delete rate limiter config option.
		delete_option( 'autoship_quicklinks_rate_limiter' );

		// Delete scanner config option.
		delete_option( 'autoship_quicklinks_scanner_config' );

		// Delete audit config options.
		delete_option( QuickLinkAuditService::CONFIG_OPTION );
		delete_option( QuickLinkAuditService::LAST_CLEANUP_OPTION );

		// Clean up file storage directory.
		$upload_dir     = wp_upload_dir();
		$rate_limit_dir = trailingslashit( $upload_dir['basedir'] ) . 'autoship-ratelimits/';
		if ( is_dir( $rate_limit_dir ) ) {
			$this->recursive_rmdir( $rate_limit_dir );
		}

		// Clean up audit log file directory.
		$audit_log_dir = trailingslashit( $upload_dir['basedir'] ) . 'autoship-audit-logs/';
		if ( is_dir( $audit_log_dir ) ) {
			$this->recursive_rmdir( $audit_log_dir );
		}

		// Delete rewrite rules version option.
		delete_option( self::REWRITE_RULES_VERSION_OPTION );

		// Flush rewrite rules on uninstall.
		flush_rewrite_rules();
	}

	/**
	 * Flush rewrite rules if the version has changed or not set.
	 *
	 * This ensures rewrite rules are flushed once when QuickLinks is
	 * first activated or when the rules are updated.
	 *
	 * @return void
	 */
	private function maybe_flush_rewrite_rules(): void {
		$current_version = get_option( self::REWRITE_RULES_VERSION_OPTION, '' );

		if ( self::REWRITE_RULES_VERSION !== $current_version ) {
			flush_rewrite_rules();
			update_option( self::REWRITE_RULES_VERSION_OPTION, self::REWRITE_RULES_VERSION );
		}
	}

	/**
	 * Recursively remove a directory and its contents.
	 *
	 * @param string $dir The directory path.
	 *
	 * @return void
	 */
	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$objects = scandir( $dir );
		if ( false === $objects ) {
			return;
		}

		foreach ( $objects as $object ) {
			if ( '.' === $object || '..' === $object ) {
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $object;
			if ( is_dir( $path ) ) {
				$this->recursive_rmdir( $path );
			} else {
				unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}

		rmdir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}

	/**
	 * Perform operations on plugin deactivation.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
		// Unschedule confirmation cleanup task.
		$cleanup_scheduler = $container->get( ConfirmationCleanupScheduler::class );
		$cleanup_scheduler->unschedule_task();

		// Flush rewrite rules on deactivation.
		flush_rewrite_rules();
	}
}
