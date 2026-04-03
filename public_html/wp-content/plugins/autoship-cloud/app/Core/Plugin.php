<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Represents the plugin's new implementation.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core;

use Autoship\Core\Implementations\AccountManager;
use Autoship\Core\Implementations\Environment;
use Autoship\Core\Implementations\Installer;
use Autoship\Core\Implementations\SitesManager;
use Autoship\Core\Implementations\SystemClock;
use Autoship\Core\Implementations\WordPressCredentials;
use Autoship\Core\Implementations\WordPressFeatureManager;
use Autoship\Core\Implementations\WordPressOAuthService;
use Autoship\Core\Implementations\WordPressAutoshipSettings;
use Autoship\Core\Implementations\WordPressSettings;
use Autoship\Services\QPilot\QPilotServiceFactory;
use Autoship\Modules\Healthcheck\HealthcheckModule;
use Autoship\Modules\Nextime\NextimeModule;
use Autoship\Modules\Payments\PaymentsModule;
use Autoship\Modules\Quicklaunch\QuicklaunchModule;
use Autoship\Modules\QuickLinks\QuickLinksModule;
use Autoship\Modules\Synchronizers\Products\ProductSynchronizerModule;
use Autoship\Services\Labels\Implementations\WordPressLabelService;
use Autoship\Services\Labels\LabelServiceInterface;
use Autoship\Modules\Utilities\UtilitiesModule;
use Autoship\Repositories\ProductRepositoryInterface;
use Autoship\Repositories\Implementations\WordPressProductRepository;
use Autoship\Services\Logging\AutoshipLogger;
use Autoship\Services\Logging\Implementations\WordPressLoggingSettings;
use Autoship\Services\Logging\Logger;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Services\Logging\LoggingSettingsInterface;
use Autoship\Services\Logging\SinkFactory;
use Exception;

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
	 * @throws Exception If the boot process fails.
	 */
	public function boot(): void {
		$this->register_core_services();
		$this->initialize_logging();
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
			FeatureManagerInterface::class,
			function () {
				$manager = new WordPressFeatureManager();
				$manager->set_remote_flags_provider( array( $this, 'fetch_qpilot_feature_flags' ) );

				return $manager;
			}
		);

		$this->container->register(
			EnvironmentInterface::class,
			function () {
				return new Environment();
			}
		);

		$this->container->register(
			SettingsInterface::class,
			function () {
				return new WordPressSettings();
			}
		);

		$this->container->register(
			AutoshipSettingsInterface::class,
			function () {
				return new WordPressAutoshipSettings();
			}
		);

		$this->container->register(
			LabelServiceInterface::class,
			function () {
				$c = Plugin::get_service_container();
				return new WordPressLabelService(
					$c->get( SettingsInterface::class )
				);
			}
		);

		$this->container->register(
			ClockInterface::class,
			function () {
				return new SystemClock();
			}
		);

		$this->container->register(
			InstallerInterface::class,
			function () {
				$c = Plugin::get_service_container();
				return new Installer(
					$c->get( EnvironmentInterface::class ),
					$c->get( SettingsInterface::class )
				);
			}
		);

		$this->container->register(
			AccountManagerInterface::class,
			function () {
				$c = Plugin::get_service_container();
				return new AccountManager(
					$c->get( EnvironmentInterface::class ),
					$c->get( InstallerInterface::class ),
					$c->get( SettingsInterface::class )
				);
			}
		);

		$this->container->register(
			CredentialsInterface::class,
			function () {
				return new WordPressCredentials();
			}
		);

		$this->container->register(
			OAuthServiceInterface::class,
			function () {
				$c = Plugin::get_service_container();
				return new WordPressOAuthService(
					$c->get( CredentialsInterface::class ),
					$c->get( EnvironmentInterface::class )
				);
			}
		);

		$this->container->register(
			SitesManagerInterface::class,
			function () {
				$c = Plugin::get_service_container();
				return new SitesManager(
					$c->get( EnvironmentInterface::class ),
					$c->get( CredentialsInterface::class ),
					$c->get( OAuthServiceInterface::class )
				);
			}
		);

		$this->container->register(
			LoggingSettingsInterface::class,
			function () {
				return new WordPressLoggingSettings();
			}
		);

		$this->container->register(
			LoggerInterface::class,
			function () {
				return new AutoshipLogger();
			}
		);

		$this->container->register(
			ProductRepositoryInterface::class,
			function () {
				return new WordPressProductRepository();
			}
		);
	}

	/**
	 * Initializes the logging subsystem with injected dependencies.
	 *
	 * @return void
	 * @throws Exception If the logging initialization fails.
	 */
	private function initialize_logging(): void {
		SinkFactory::initialize(
			$this->container->get( LoggingSettingsInterface::class ),
			$this->container->get( ClockInterface::class )
		);
	}

	/**
	 * Register the enabled modules.
	 *
	 * @return void
	 * @throws Exception If module registration fails.
	 */
	protected function register_modules(): void {
		$features = $this->container->get( FeatureManagerInterface::class );

		if ( $features->is_enabled( 'quicklaunch' ) ) {
			$this->module_manager->register_module( new QuicklaunchModule( $features ) );
		}

		if ( $features->is_enabled( 'product_sync' ) ) {
			$this->module_manager->register_module( new ProductSynchronizerModule() );
		}

		if ( $features->is_enabled( 'nextime' ) ) {
			$this->module_manager->register_module( new NextimeModule() );
		}

		if ( $features->is_enabled( 'quicklinks' ) ) {
			$this->module_manager->register_module( new QuickLinksModule() );
		}

		if ( $features->is_enabled( 'payments' ) ) {
			$this->module_manager->register_module( new PaymentsModule() );
		}

		if ( $features->is_enabled( 'utilities' ) ) {
			$this->module_manager->register_module( new UtilitiesModule() );
		}

		if ( $features->is_enabled( 'healthcheck' ) ) {
			$this->module_manager->register_module( new HealthcheckModule() );
		}
	}

	/**
	 * Boots the modules to activate them.
	 *
	 * @return void
	 */
	protected function boot_modules(): void {
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
	 * Fetch feature flags from the QPilot API.
	 *
	 * Retrieves both global and site-specific flags, merges them,
	 * and caches the result in a WordPress transient for 1 hour.
	 *
	 * @return array<string>|null List of enabled flag names, or null on failure.
	 */
	public function fetch_qpilot_feature_flags(): ?array {

		$cache_key = 'autoship_qpilot_feature_flags';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			Logger::debug( 'Feature Flags', 'Returning cached remote flags.' );
			return $cached;
		}

		$token   = get_option( 'autoship_token_auth', '' );
		$site_id = (int) get_option( 'autoship_site_id', 0 );

		if ( empty( $token ) || $site_id <= 0 ) {
			Logger::debug( 'Feature Flags', 'No QPilot credentials configured. Using hardcoded defaults.' );
			return null;
		}

		try {
			$environment = $this->container->get( EnvironmentInterface::class );
			$client      = QPilotServiceFactory::create(
				$environment->get_api_url(),
				$token,
				$site_id
			);
		} catch ( \Exception $e ) {
			Logger::error( 'Feature Flags', 'Failed to initialize QPilot client: ' . $e->getMessage() );
			return null;
		}

		// Fetch global and site-specific flags independently so one
		// failure does not prevent the other from being used.
		$global_flags   = array();
		$global_success = false;
		$site_flags     = array();
		$site_success   = false;

		try {
			$global_flags   = $client->get_feature_flags();
			$global_success = true;
		} catch ( \Exception $e ) {
			Logger::error( 'Feature Flags', 'Failed to fetch global feature flags: ' . $e->getMessage() );
		}

		try {
			$site_flags   = $client->get_site_feature_flags();
			$site_success = true;
		} catch ( \Exception $e ) {
			Logger::error( 'Feature Flags', 'Failed to fetch site feature flags: ' . $e->getMessage() );
		}

		// If both calls failed, return null to fall back to hardcoded defaults.
		if ( ! $global_success && ! $site_success ) {
			Logger::error( 'Feature Flags', 'Both QPilot feature flag endpoints failed. Using hardcoded defaults.' );
			return null;
		}

		$all_flags = array_values( array_unique( array_merge( $global_flags, $site_flags ) ) );

		set_transient( $cache_key, $all_flags, 5 * MINUTE_IN_SECONDS );

		Logger::debug( 'Feature Flags', 'Resolved remote flags: ' . implode( ', ', $all_flags ) );

		return $all_flags;
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
	 * @throws Exception If the boot process fails.
	 */
	public static function run(): Plugin {
		self::$instance = new self();
		self::$instance->boot();

		return self::$instance;
	}
}
