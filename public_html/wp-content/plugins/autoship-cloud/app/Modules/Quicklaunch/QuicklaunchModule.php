<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class implements the Autoship Quicklaunch module.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Automattic\WooCommerce\Utilities\OrderUtil;
use Autoship\Core\AccountManagerInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\InstallerInterface;
use Autoship\Core\ModuleInterface;
use Autoship\Core\Plugin;
use Autoship\Core\ServiceContainer;
use Autoship\Core\SitesManagerInterface;
use Autoship\Services\Logging\LoggerInterface;
use Exception;

/**
 * Registers the quicklaunch module services and boots it if the quicklaunch is enabled.
 *
 * @package Autoship
 * @since 2.8.7
 */
class QuicklaunchModule implements ModuleInterface {

	/**
	 * The feature manager instance.
	 *
	 * @var FeatureManagerInterface
	 */
	private FeatureManagerInterface $feature_manager;

	/**
	 * Constructor.
	 *
	 * @param FeatureManagerInterface $feature_manager The feature manager.
	 */
	public function __construct( FeatureManagerInterface $feature_manager ) {
		$this->feature_manager = $feature_manager;
	}

	/**
	 * Register the module handlers.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function register( ServiceContainer $container ) {
		$feature_manager = $this->feature_manager;

		$container->register(
			'quicklaunch_setup_handler',
			function () use ( $feature_manager ) {
				$c = Plugin::get_service_container();
				return new SetupStepHandler( $feature_manager, $c->get( EnvironmentInterface::class ) );
			}
		);

		$container->register(
			'quicklaunch_lead_handler',
			function () use ( $feature_manager ) {
				$c = Plugin::get_service_container();
				return new LeadStepHandler(
					$feature_manager,
					$c->get( EnvironmentInterface::class ),
					$c->get( InstallerInterface::class )
				);
			}
		);

		$container->register(
			'quicklaunch_login_handler',
			function () use ( $feature_manager ) {
				$c = Plugin::get_service_container();
				return new LoginStepHandler(
					$feature_manager,
					$c->get( AccountManagerInterface::class ),
					$c->get( SitesManagerInterface::class ),
					$c->get( LoggerInterface::class )
				);
			}
		);

		$container->register(
			'quicklaunch_logout_handler',
			function () use ( $feature_manager ) {
				return new LogoutStepHandler( $feature_manager );
			}
		);

		$container->register(
			'quicklaunch_product_handler',
			function () use ( $feature_manager ) {
				$c = Plugin::get_service_container();
				return new ProductStepHandler(
					$feature_manager,
					$c->get( LoggerInterface::class )
				);
			}
		);

		$container->register(
			'quicklaunch_registration_handler',
			function () use ( $feature_manager ) {
				$c = Plugin::get_service_container();
				return new RegistrationStepHandler(
					$feature_manager,
					$c->get( AccountManagerInterface::class ),
					$c->get( SitesManagerInterface::class ),
					$c->get( LoggerInterface::class )
				);
			}
		);

		$container->register(
			'quicklaunch_reset_handler',
			function () {
				return new ResetStepHandler();
			}
		);

		$container->register(
			'quicklaunch_payment_method_handler',
			function () use ( $feature_manager ) {
				return new PaymentMethodStepHandler( $feature_manager );
			}
		);

		$container->register(
			'quicklaunch_widget_handler',
			function () {
				return new WidgetHandler();
			}
		);

		// Register more Quicklaunch handlers here if needed.
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
			// Wire the handlers to continue.
			$container->get( 'quicklaunch_lead_handler' );
			$container->get( 'quicklaunch_login_handler' );
			$container->get( 'quicklaunch_logout_handler' );
			$container->get( 'quicklaunch_product_handler' );
			$container->get( 'quicklaunch_registration_handler' );
			$container->get( 'quicklaunch_reset_handler' );
			$container->get( 'quicklaunch_payment_method_handler' );

			// Once they are wired. Initialize the module.
			$this->initialize( $container );

		} catch ( Exception $exception ) {
			if ( null !== $logger ) {
				$logger->log(
					__( 'Autoship Quicklaunch Exception', 'autoship' ),
					// translators: %s is the exception message.
					sprintf( 'An exception occurred when attempting to boot the Quicklaunch Module. Details: %s', $exception->getMessage() )
				);
			}
		}
	}

	/**
	 * Initializes the quicklaunch module.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 * @throws Exception If the quicklaunch could not be enabled.
	 */
	private function initialize( ServiceContainer $container ): void {

		if ( ! $this->feature_manager->is_enabled( 'quicklaunch' ) ) {
			return;
		}

		$display       = false;
		$is_connected  = autoship_has_credentials() && autoship_has_auth_token();
		$last_step     = get_option( 'autoship_quicklaunch_last_step' );
		$has_last_step = ! empty( $last_step );
		$orders        = self::count_autoship_scheduled_orders();
		$has_orders    = $orders > 0;

		if ( $has_orders ) {
			// If the site has orders, disable the quicklaunch and set it as completed if it is not already.
			if ( ! $has_last_step ) {
				update_option( 'autoship_quicklaunch_completed', true );
				update_option( 'autoship_quicklaunch_last_step', 'completed' );
			}
		} else {
			// If the site is connected, and it doesn't have a last step.
			if ( $is_connected && ! $has_last_step ) {
				// Set the last step as the product step.
				update_option( 'autoship_quicklaunch_completed', false );
				update_option( 'autoship_quicklaunch_last_step', 'product' );
			}

			$display = true;
		}

		// Verify that the quicklaunch must be shown or not.
		if ( $display ) {
			// Activate the setup using the setup handler.
			$container->get( 'quicklaunch_setup_handler' );
		}
	}

	/**
	 * Gets the current count of scheduled orders.
	 *
	 * @return int
	 */
	public static function count_autoship_scheduled_orders(): int {
		$cache_key = 'autoship_quicklaunch_orders_checker';
		$orders    = wp_cache_get( $cache_key );

		if ( empty( $orders ) ) {
			global $wpdb;

			try {
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$query = $wpdb->prepare(
						"SELECT COUNT(order_id) AS Total FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s AND meta_value <> ''",
						'_autoship_created_scheduled_orders_key'
					);
				} else {
					$query = $wpdb->prepare(
						"SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value <> ''",
						'_autoship_created_scheduled_orders_key'
					);
				}

				$orders = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
				if ( empty( $orders ) || ! is_numeric( $orders ) ) {
					$orders = 0;
				}

				wp_cache_set( $cache_key, $orders );

			} catch ( Exception $exception ) {
				try {
					$log = Plugin::get_service_container()->get( LoggerInterface::class );
					$log->log(
						__( 'Autoship Quicklaunch Orders Count Exception', 'autoship' ),
						sprintf( 'An exception occurred when attempting to count Autoship scheduled orders. Details: %s', $exception->getMessage() )
					);
				} catch ( Exception $e ) {
					// Logger unavailable.
				}

				$orders = 0;
			}
		}

		// Ensure the orders count is an integer.
		if ( ! is_numeric( $orders ) ) {
			$orders = 0;
		}

		return $orders;
	}

	/**
	 * Returns true if on activation the process should be redirected.
	 *
	 * @return bool
	 */
	public static function must_redirect_on_activation(): bool {

		$features = Plugin::get_service_container()->get( FeatureManagerInterface::class );
		if ( ! $features->is_enabled( 'quicklaunch' ) ) {
			return false;
		}

		// Enable redirection to the quicklaunch if not installed or does not have a valid connection.
		$redirect     = false;
		$is_connected = autoship_has_credentials() && autoship_has_auth_token();
		$orders       = self::count_autoship_scheduled_orders();
		$has_orders   = $orders > 0;

		if ( ! $has_orders && ! $is_connected ) {
			$redirect = true;
		}

		return $redirect;
	}

	/**
	 * Performs this operation upon deactivation of the plugin.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
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
