<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Abstract Payment Compatibility
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\Plugin;
use Autoship\Domain\AbstractPaymentGateway;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;

/**
 * Abstract base class for payment gateway backward compatibility.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 2.11.0
 */
abstract class AbstractPaymentCompatibility {

	/**
	 * The gateway ID this compatibility class handles.
	 *
	 * @var string
	 */
	protected string $gateway_id;

	/**
	 * The gateway name for display purposes.
	 *
	 * @var string
	 */
	protected string $gateway_name;

	/**
	 * Whether this compatibility layer is enabled.
	 *
	 * @var bool
	 */
	protected bool $enabled = true;

	/**
	 * Initialize backward compatibility for this gateway.
	 *
	 * @return void
	 */
	abstract public function init(): void;

	/**
	 * Register filter hooks for this gateway.
	 *
	 * @return void
	 */
	abstract protected function register_filter_hooks(): void;

	/**
	 * Register action hooks for this gateway.
	 *
	 * @return void
	 */
	abstract protected function register_action_hooks(): void;

	/**
	 * Get the gateway ID.
	 *
	 * @return string
	 */
	public function get_gateway_id(): string {
		return $this->gateway_id;
	}

	/**
	 * Get the gateway name.
	 *
	 * @return string
	 */
	public function get_gateway_name(): string {
		return $this->gateway_name;
	}

	/**
	 * Check if this compatibility layer is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return $this->enabled;
	}

	/**
	 * Enable or disable this compatibility layer.
	 *
	 * @param bool $enabled Whether to enable the compatibility layer.
	 *
	 * @return void
	 */
	public function set_enabled( bool $enabled ): void {
		$this->enabled = $enabled;
	}

	/**
	 * Check if the gateway is available in WooCommerce.
	 *
	 * @return bool
	 */
	public function is_gateway_available(): bool {
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

		return isset( $available_gateways[ $this->gateway_id ] );
	}

	/**
	 * Log a compatibility message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	protected function log( string $message ): void {
		Logger::log( 'Autoship Payment Compatibility', "[Autoship Payment Compatibility - {$this->gateway_name}] {$message}" );
	}

	/**
	 * Get the payment gateway registry instance.
	 *
	 * @return ?PaymentGatewayRegistry
	 */
	protected function get_gateway_registry(): ?PaymentGatewayRegistry {
		if ( function_exists( 'autoship_get_service_container' ) ) {
			$container = Plugin::get_service_container();

			return $container->get( PaymentGatewayRegistry::class );
		}

		return null;
	}

	/**
	 * Get the payment gateway instance from the registry.
	 *
	 * @return ?AbstractPaymentGateway
	 */
	protected function get_gateway_instance(): ?AbstractPaymentGateway {
		$registry = $this->get_gateway_registry();

		return $registry ? $registry->get_gateway( $this->gateway_id ) : null;
	}

	/**
	 * Check if a function already exists before registering it.
	 *
	 * @param string $function_name The function name to check.
	 *
	 * @return bool
	 */
	protected function function_exists( string $function_name ): bool {
		return function_exists( $function_name );
	}

	/**
	 * Safely register a function wrapper.
	 *
	 * @param string $function_name The function name.
	 *
	 * @return bool Whether the function was registered.
	 */
	protected function register_function( string $function_name ): bool {
		if ( ! $this->function_exists( $function_name ) ) {
			// TODO: Imeplement here.

			return true;
		}

		return false;
	}
}
