<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Integrations Module Plugin
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments;

use Autoship\Core\ModuleInterface;
use Autoship\Core\Plugin;
use Autoship\Core\ServiceContainer;
use Autoship\Modules\Payments\Services\PaymentGatewayService;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Modules\Payments\Services\PaymentMethodDataBuilder;
use Autoship\Modules\Payments\Services\PaymentMethodService;
use Autoship\Modules\Payments\Services\PaymentsCompatibilityService;
use Exception;

/**
 * Payment Gateway Module for managing all payment gateway integrations.
 *
 * @package Autoship\Modules\PaymentGateways
 * @since 2.11.0
 */
class PaymentsModule implements ModuleInterface {

	/**
	 * Register the module's services with the service container.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function register( ServiceContainer $container ): void {
		// Register the payment gateway registry.
		$container->register(
			PaymentGatewayRegistry::class,
			function () {
				return new PaymentGatewayRegistry();
			}
		);

		// Register the payment gateway service.
		$container->register(
			PaymentGatewayService::class,
			function () {
				$container = Plugin::get_service_container();

				return new PaymentGatewayService( $container->get( PaymentGatewayRegistry::class ) );
			}
		);

		// Register the payment method data builder.
		$container->register(
			PaymentMethodDataBuilder::class,
			function () {
				return new PaymentMethodDataBuilder();
			}
		);

		// Register the payment method service.
		$container->register(
			PaymentMethodService::class,
			function () {
				$container = Plugin::get_service_container();

				return new PaymentMethodService( $container->get( PaymentMethodDataBuilder::class ) );
			}
		);

		// Register backward compatibility service.
		$container->register(
			PaymentsCompatibilityService::class,
			function () {
				$container = Plugin::get_service_container();

				return new PaymentsCompatibilityService( $container->get( PaymentGatewayRegistry::class ) );
			}
		);
	}

	/**
	 * Boots the module.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 * @throws Exception Thrown when the services are not found.
	 */
	public function boot( ServiceContainer $container ): void {
		// Register WordPress hooks.
		$this->register_wordpress_hooks( $container );

		// Using woocommerce_init is safer than plugins_loaded for WC context.
		add_action(
			'woocommerce_init',
			function () use ( $container ) {
				// Initialize the registry first.
				$registry = $container->get( PaymentGatewayRegistry::class );
				$registry->initialize();

				// Initialize compatibility.
				$compatibility = $container->get( PaymentsCompatibilityService::class );
				$compatibility->initialize();
			},
			99
		);
	}

	/**
	 * Register WordPress hooks and filters.
	 *
	 * @param ServiceContainer $container The service container.
	 *
	 * @return void
	 * @throws Exception Thrown when the services are not found.
	 */
	private function register_wordpress_hooks( ServiceContainer $container ): void {
		$service = $container->get( PaymentGatewayService::class );

		// Hook into WooCommerce payment token creation.
		add_action( 'woocommerce_new_payment_token', array( $service, 'handle_new_payment_token' ) );

		// Hook into payment method updates.
		add_action( 'autoship_update_payment_method_on_all_scheduled_orders', array( $service, 'update_payment_method_on_orders' ) );

		// Register payment gateway filters.
		add_filter( 'autoship_get_valid_payment_methods', array( $service, 'get_valid_payment_methods' ) );
	}

	/**
	 * Deactivate the module.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function deactivate( ServiceContainer $container ): void {
	}

	/**
	 * Uninstall the module.
	 *
	 * @param ServiceContainer $container The service container.
	 * @return void
	 */
	public function uninstall( ServiceContainer $container ): void {
	}
}
