<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Registers the product synchronization module services and boots it.
 *
 * @package Autoship
 * @since 2.8.6
 */

namespace Autoship\Modules\Synchronizers\Products;

use Autoship\Core\ModuleInterface;
use Autoship\Core\ServiceContainer;
use Exception;

/**
 * Represents the product synchronization module.
 *
 * @package Autoship
 * @since 2.8.6
 */
class ProductSynchronizerModule implements ModuleInterface {

	/**
	 * Register the module handlers.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function register( ServiceContainer $container ) {
		$container->register(
			ProductSynchronizer::class,
			function () {
				return new ProductSynchronizer();
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
			$synchronizer = $container->get( ProductSynchronizer::class );

			$synchronizer->init();
		} catch ( Exception $exception ) {
			autoship_log_entry(
				__( 'Autoship Product Sync Exception', 'autoship' ),
				sprintf( 'An exception occurred when attempting to boot the Product Synchronization Module. Details: %s', $exception->getMessage() )
			);
		}
	}

	/**
	 * Performs this operation upon deactivation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
		try {
			$synchronizer = $container->get( ProductSynchronizer::class );
			$synchronizer->disable_sync();
		} catch ( Exception $e ) {
			autoship_log_entry( 'Autoship Deactivator', $e->getMessage() );
		}
	}

	/**
	 * Performs this operation upon uninstallation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void {
	}
}
