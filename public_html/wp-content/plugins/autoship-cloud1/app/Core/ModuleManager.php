<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Represents the module manager.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core;

/**
 * Represents the module manager class.
 *
 * @package Autoship
 * @since 2.8.7
 */
class ModuleManager {

	/**
	 * The service provider container.
	 *
	 * @var ServiceContainer
	 */
	private ServiceContainer $container;

	/**
	 * The registered modules array.
	 *
	 * @var array
	 */
	private array $modules = array();

	/**
	 * Constructor
	 *
	 * @param ServiceContainer $container The service container.
	 */
	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	/**
	 * Register a module into the module manager.
	 *
	 * @param ModuleInterface $module The module to register.
	 * @return void
	 */
	public function register_module( ModuleInterface $module ) {
		$this->modules[] = $module;

		$module->register( $this->container );
	}

	/**
	 * Boot all registered modules.
	 *
	 * @return void
	 */
	public function boot() {
		foreach ( $this->modules as $module ) {
			$module->boot( $this->container );
		}
	}

	/**
	 * Deactivate registered modules.
	 *
	 * @return void
	 */
	public function deactivate() {
		foreach ( $this->modules as $module ) {
			$module->deactivate( $this->container );
		}
	}

	/**
	 * Uninstall registered modules.
	 *
	 * @return void
	 */
	public function uninstall() {
		foreach ( $this->modules as $module ) {
			$module->uninstall( $this->container );
		}
	}
}
