<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Extension Plugin
 *
 * @package Autoship Cloud Nextime Extension
 * @since 1.0.0
 */

namespace Autoship\Modules\Nextime;

use Autoship\Core\EnvironmentInterface;
use Autoship\Core\ModuleInterface;
use Autoship\Core\Plugin;
use Autoship\Core\ServiceContainer;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Services\Nextime\Implementations\NextimeCarriersManagement;
use Autoship\Services\Nextime\Implementations\NextimeSitesManagement;
use Autoship\Services\Nextime\Implementations\WordPressNextimeHttpClient;
use Autoship\Services\Nextime\Implementations\WordPressNextimeSettings;
use Autoship\Services\Nextime\Interfaces\CarriersManagementInterface;
use Autoship\Services\Nextime\Interfaces\SitesManagementInterface;
use Autoship\Services\Nextime\NextimeHttpClientInterface;
use Autoship\Services\Nextime\NextimeServiceClient;
use Autoship\Services\Nextime\NextimeServiceInterface;
use Autoship\Services\Nextime\NextimeSettingsInterface;
use Exception;

/**
 * The autoship extension method.
 */
class NextimeModule implements ModuleInterface {

	/**
	 * The nextime service loader.
	 *
	 * @var ?NextimeService
	 */
	protected ?NextimeService $service;

	/**
	 * Initialize the plugin by setting up dependencies, locale, admin, and public hooks.
	 */
	public function __construct() {
	}

	/**
	 * Register the module handlers.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function register( ServiceContainer $container ) {

		// Register the Nextime settings.
		$container->register(
			NextimeSettingsInterface::class,
			function () {
				$environment = Plugin::get_service_container()->get( EnvironmentInterface::class );

				return new WordPressNextimeSettings( $environment );
			}
		);

		// Register the Nextime HTTP client.
		$container->register(
			NextimeHttpClientInterface::class,
			function () {
				$container = Plugin::get_service_container();
				$settings  = $container->get( NextimeSettingsInterface::class );

				return new WordPressNextimeHttpClient( $settings );
			}
		);

		// Register the Nextime sites management service.
		$container->register(
			SitesManagementInterface::class,
			function () {
				$environment = Plugin::get_service_container()->get( EnvironmentInterface::class );
				return new NextimeSitesManagement( $environment );
			}
		);

		// Register the Nextime carriers management service.
		$container->register(
			CarriersManagementInterface::class,
			function () {
				$container = Plugin::get_service_container();
				$client    = $container->get( NextimeHttpClientInterface::class );
				$logger    = $container->get( LoggerInterface::class );

				return new NextimeCarriersManagement( $client, $logger );
			}
		);

		// Register the Nextime API Service client.
		$container->register(
			NextimeServiceInterface::class,
			function () {
				$container = Plugin::get_service_container();
				$settings  = $container->get( NextimeSettingsInterface::class );
				$sites     = $container->get( SitesManagementInterface::class );
				$carriers  = $container->get( CarriersManagementInterface::class );

				return new NextimeServiceClient( $settings, $sites, $carriers );
			}
		);

		$container->register(
			NextimeService::class,
			function () {
				$container   = Plugin::get_service_container();
				$settings    = $container->get( NextimeSettingsInterface::class );
				$nextime     = $container->get( NextimeServiceInterface::class );
				$environment = $container->get( EnvironmentInterface::class );
				$logger      = $container->get( LoggerInterface::class );

				return new NextimeService( $settings, $nextime, $environment, $logger );
			}
		);
	}

	/**
	 * Boots the module services.
	 *
	 * @param ServiceContainer $container The container.
	 * @return void
	 **/
	public function boot( ServiceContainer $container ) {
		try {
			$logger = $container->get( LoggerInterface::class );
		} catch ( Exception $e ) {
			$logger = null;
		}

		try {
			$this->service = $container->get( NextimeService::class );

			$enabled = $this->service->is_nextime_enabled();
			if ( $enabled ) {
				$this->service->initialize();
			}
		} catch ( Exception $exception ) {
			if ( null !== $logger ) {
				$logger->log( 'Autoship Nextime', 'Unable to retrieve Nextime service.' );
			}
		}
	}

	/**
	 * Performs this operation upon uninstallation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void {
		flush_rewrite_rules();
	}

	/**
	 * Performs this operation upon deactivation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
	}
}
