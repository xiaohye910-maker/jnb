<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Health check Module for Autoship Cloud.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Modules\Healthcheck;

use Autoship\Core\ClockInterface;
use Autoship\Core\CredentialsInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Core\ModuleInterface;
use Autoship\Core\Plugin;
use Autoship\Core\ServiceContainer;
use Autoship\Modules\Healthcheck\Compatibility\HealthcheckCompatibility;
use Autoship\Modules\Healthcheck\Controllers\HealthcheckAjaxController;
use Autoship\Modules\Healthcheck\Controllers\HealthcheckRestController;
use Autoship\Services\Healthcheck\HealthcheckSettingsInterface;
use Autoship\Services\Healthcheck\Implementations\WordPressHealthcheckSettings;
use Autoship\Services\Labels\LabelServiceInterface;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Services\QPilot\QPilotServiceFactory;
use Autoship\Services\QPilot\QPilotServiceInterface;
use Exception;

/**
 * Module for the Health check system.
 *
 * Registers all health check services in the service container,
 * boots controllers and compatibility hooks, and handles cleanup
 * on plugin deactivation/uninstall.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class HealthcheckModule implements ModuleInterface {

	/**
	 * Register the module services with the service container.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function register( ServiceContainer $container ) {

		// Register the Healthcheck settings.
		$container->register(
			HealthcheckSettingsInterface::class,
			function () {
				return new WordPressHealthcheckSettings();
			}
		);

		// Register the QPilot service for health check use.
		$container->register(
			QPilotServiceInterface::class,
			function () {
				$container   = Plugin::get_service_container();
				$credentials = $container->get( CredentialsInterface::class );

				$token   = $credentials->get_auth_token();
				$site_id = $credentials->get_site_id();

				if ( empty( $token ) || null === $site_id ) {
					return null;
				}

				$environment = $container->get( EnvironmentInterface::class );

				return QPilotServiceFactory::create( $environment->get_api_url(), $token, $site_id );
			}
		);

		// Register the HealthcheckService.
		$container->register(
			HealthcheckService::class,
			function () {
				$container = Plugin::get_service_container();
				$settings  = $container->get( HealthcheckSettingsInterface::class );
				$clock     = $container->get( ClockInterface::class );
				$logger    = $container->get( LoggerInterface::class );

				// QPilot client may be null if no credentials are configured.
				$qpilot = null;
				try {
					$qpilot = $container->get( QPilotServiceInterface::class );
				} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// No credentials available — QPilot client stays null.
				}

				return new HealthcheckService( $settings, $qpilot, $clock, $logger );
			}
		);

		// Register the AJAX controller.
		$container->register(
			HealthcheckAjaxController::class,
			function () {
				$container = Plugin::get_service_container();
				$service   = $container->get( HealthcheckService::class );

				return new HealthcheckAjaxController( $service );
			}
		);

		// Register the REST controller.
		$container->register(
			HealthcheckRestController::class,
			function () {
				$container = Plugin::get_service_container();
				$settings  = $container->get( HealthcheckSettingsInterface::class );
				$clock     = $container->get( ClockInterface::class );

				return new HealthcheckRestController( $settings, $clock );
			}
		);

		// Register the Compatibility layer.
		$container->register(
			HealthcheckCompatibility::class,
			function () {
				$container   = Plugin::get_service_container();
				$service     = $container->get( HealthcheckService::class );
				$settings    = $container->get( HealthcheckSettingsInterface::class );
				$clock       = $container->get( ClockInterface::class );
				$logger      = $container->get( LoggerInterface::class );
				$labels      = $container->get( LabelServiceInterface::class );
				$credentials = $container->get( CredentialsInterface::class );

				return new HealthcheckCompatibility( $service, $settings, $clock, $logger, $labels, $credentials );
			}
		);
	}

	/**
	 * Boot the module services.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function boot( ServiceContainer $container ) {
		try {
			$logger = $container->get( LoggerInterface::class );
		} catch ( Exception $e ) {
			$logger = null;
		}

		try {
			// Boot the AJAX controller.
			$ajax_controller = $container->get( HealthcheckAjaxController::class );
			$ajax_controller->register_hooks();

			// Boot the REST controller.
			$rest_controller = $container->get( HealthcheckRestController::class );
			$rest_controller->register_hooks();

			// Boot the compatibility layer.
			$compatibility = $container->get( HealthcheckCompatibility::class );
			$compatibility->register_hooks();
		} catch ( Exception $exception ) {
			if ( null !== $logger ) {
				$logger->log( 'Autoship Healthcheck', 'Unable to boot Healthcheck module: ' . $exception->getMessage() );
			}
		}
	}

	/**
	 * Performs cleanup on plugin deactivation.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
	}

	/**
	 * Performs cleanup on plugin uninstallation.
	 *
	 * Deletes all health check options from the database.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void {
		try {
			$settings = $container->get( HealthcheckSettingsInterface::class );
			$settings->delete_all();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Settings unavailable during uninstall — safe to ignore.
		}
	}
}
