<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Utilities Module.
 *
 * @package Autoship
 * @since 2.12.1
 */

namespace Autoship\Modules\Utilities;

use Autoship\Core\AutoshipSettingsInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Core\ModuleInterface;
use Autoship\Core\Plugin;
use Autoship\Core\ServiceContainer;
use Autoship\Modules\Utilities\Controllers\BulkFrequenciesController;
use Autoship\Modules\Utilities\Services\FrequencyUpdateService;
use Autoship\Modules\Utilities\Services\ProductQueryService;
use Autoship\Repositories\ProductRepositoryInterface;
use Exception;

/**
 * Utilities Module for bulk product operations.
 *
 * This currently provides the bulk frequency update utility. Additional
 * utilities can be added as controllers within this module.
 *
 * @package Autoship\Modules\Utilities
 * @since 2.12.1
 */
class UtilitiesModule implements ModuleInterface {

	/**
	 * Register the module's services with the service container.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function register( ServiceContainer $container ): void {
		$container->register(
			ProductQueryService::class,
			function () {
				$c = Plugin::get_service_container();

				return new ProductQueryService(
					$c->get( ProductRepositoryInterface::class ),
					$c->get( AutoshipSettingsInterface::class )
				);
			}
		);

		$container->register(
			FrequencyUpdateService::class,
			function () {
				$c = Plugin::get_service_container();

				return new FrequencyUpdateService(
					$c->get( AutoshipSettingsInterface::class )
				);
			}
		);

		$container->register(
			BulkFrequenciesController::class,
			function () {
				$c = Plugin::get_service_container();

				return new BulkFrequenciesController(
					$c->get( FrequencyUpdateService::class ),
					$c->get( ProductQueryService::class ),
					$c->get( EnvironmentInterface::class ),
					$c->get( AutoshipSettingsInterface::class )
				);
			}
		);
	}

	/**
	 * Boot the module.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 * @throws Exception Thrown when the services are not found.
	 */
	public function boot( ServiceContainer $container ): void {
		if ( is_admin() ) {
			$controller = $container->get( BulkFrequenciesController::class );
			$controller->register();
		}
	}

	/**
	 * Deactivate the module.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
	}

	/**
	 * Uninstall the module.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void {
	}
}
