<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Represents the plugin's new implementation.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core;

use Autoship\Modules\Nextime\NextimeModule;
use Autoship\Modules\Quicklaunch\QuicklaunchModule;
use Autoship\Modules\Synchronizers\Products\ProductSynchronizerModule;

/**
 * Represents the plugin.
 *
 * @package Autoship
 * @since 2.8.7
 */
class Plugin {

	/**
	 * The container for the plugin services.
	 *
	 * @var ServiceContainer
	 */
	protected ServiceContainer $container;

	/**
	 * The module manager for the plugin.
	 *
	 * @var ModuleManager
	 */
	private ModuleManager $module_manager;

	/**
	 * The current instance of the plugin.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Initializes the Autoship Core Plugin.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->container      = new ServiceContainer();
		$this->module_manager = new ModuleManager( $this->container );
	}

	/**
	 * Run the configuration of the different modules.
	 *
	 * @return void
	 */
	public function boot() {
		$this->register_core_services();
		$this->register_modules();
		$this->boot_modules();
	}

	/**
	 * Register the core services into the plugin.
	 *
	 * @return void
	 */
	private function register_core_services() {
		$this->container->register(
			Environment::class,
			function () {
				return new Environment();
			}
		);

		$this->container->register(
			Installer::class,
			function () {
				return new Installer();
			}
		);
	}

	/**
	 * Register the enabled modules.
	 *
	 * @return void
	 */
	protected function register_modules() {
		if ( FeatureManager::is_enabled( 'quicklaunch' ) ) {
			$this->module_manager->register_module( new QuicklaunchModule() );
		}

		if ( FeatureManager::is_enabled( 'product_sync' ) ) {
			$this->module_manager->register_module( new ProductSynchronizerModule() );
		}

		if ( FeatureManager::is_enabled( 'nextime' ) ) {
			$this->module_manager->register_module( new NextimeModule() );
		}
	}

	/**
	 * Boots the modules to activate them.
	 *
	 * @return void
	 */
	protected function boot_modules() {
		$this->module_manager->boot();
	}

	/**
	 * Deactivates the modules.
	 *
	 * @return void
	 */
	protected function deactivate_modules(): void {
		$this->module_manager->deactivate();
	}

	/**
	 * Uninstalls the modules.
	 *
	 * @return void
	 */
	protected function uninstall_modules(): void {
		$this->module_manager->uninstall();
	}

	/**
	 * Gets the service container for the plugin.
	 *
	 * @return ServiceContainer
	 */
	public function get_container(): ServiceContainer {
		return $this->container;
	}

	/**
	 * Activates the plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
	}

	/**
	 * Deactivates each module.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		$plugin = self::get_instance();

		if ( null === $plugin ) {
			return;
		}

		$plugin->deactivate_modules();
	}

	/**
	 * Uninstalls each module.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		$plugin = self::get_instance();

		if ( null === $plugin ) {
			return;
		}

		$plugin->uninstall_modules();
	}

	/**
	 * Gets the current instance of the plugin.
	 *
	 * @return Plugin|null
	 */
	public static function get_instance(): ?Plugin {
		return self::$instance;
	}

	/**
	 * Gets the service container of the plugin.
	 *
	 * @return ServiceContainer
	 */
	public static function get_service_container(): ServiceContainer {
		return self::$instance->get_container();
	}

	/**
	 * Starts the plugin.
	 *
	 * @return Plugin
	 */
	public static function run(): Plugin {
		self::$instance = new self();
		self::$instance->boot();

		return self::$instance;
	}
}
