<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents the module's interfaces.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core;

/**
 * Represents the module interface.
 *
 * @package Autoship
 * @since 2.8.7
 */
interface ModuleInterface {

	/**
	 * Registers the module's services with the service container.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return mixed
	 */
	public function register( ServiceContainer $container );

	/**
	 * Boots the module.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return mixed
	 */
	public function boot( ServiceContainer $container );

	/**
	 * Performs this operation upon deactivation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void;

	/**
	 * Performs this operation upon uninstallation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void;
}
