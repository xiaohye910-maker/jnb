<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Compatibility Service
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Services;

use Autoship\Modules\Payments\Compatibility\PaymentsCompatibilityManager;
use Autoship\Modules\Payments\Compatibility\AbstractPaymentCompatibility;
use Autoship\Services\Logging\Logger;

/**
 * Service for managing payment gateway backward compatibility.
 *
 * @package Autoship\Modules\Payments\Services
 * @since 3.0.0
 */
class PaymentsCompatibilityService {

	/**
	 * Payment gateway registry.
	 *
	 * @var PaymentGatewayRegistry
	 */
	private PaymentGatewayRegistry $registry;

	/**
	 * Whether the service has been initialized.
	 *
	 * @var bool
	 */
	private bool $initialized = false;

	/**
	 * Constructor.
	 *
	 * @param PaymentGatewayRegistry $registry The payment gateway registry.
	 */
	public function __construct( PaymentGatewayRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Initialize backward compatibility.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( $this->initialized ) {
			return;
		}

		// Initialize the compatibility manager with the registry.
		PaymentsCompatibilityManager::init( $this->registry );

		$this->initialized = true;

		// Log initialization.
		$this->log( 'Payment compatibility service initialized with ' . PaymentsCompatibilityManager::get_count() . ' compatibility layers.' );
	}

	/**
	 * Get a specific compatibility instance.
	 *
	 * @param string $gateway_id The gateway ID.
	 *
	 * @return AbstractPaymentCompatibility|null
	 */
	public function get_compatibility_instance( string $gateway_id ): ?AbstractPaymentCompatibility {
		return PaymentsCompatibilityManager::get_compatibility_instance( $gateway_id );
	}

	/**
	 * Get all compatibility instances.
	 *
	 * @return array<string, AbstractPaymentCompatibility>
	 */
	public function get_all_compatibility_instances(): array {
		return PaymentsCompatibilityManager::get_all_instances();
	}

	/**
	 * Enable or disable a specific compatibility layer.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param bool   $enabled Whether to enable the compatibility layer.
	 *
	 * @return bool Whether the operation was successful.
	 */
	public function set_compatibility_enabled( string $gateway_id, bool $enabled ): bool {
		$result = PaymentsCompatibilityManager::set_compatibility_enabled( $gateway_id, $enabled );

		if ( $result ) {
			$status = $enabled ? 'enabled' : 'disabled';
			$this->log( "Payment compatibility for {$gateway_id} has been {$status}." );
		}

		return $result;
	}

	/**
	 * Get the status of all compatibility layers.
	 *
	 * @return array<string, array>
	 */
	public function get_compatibility_status(): array {
		return PaymentsCompatibilityManager::get_compatibility_status();
	}

	/**
	 * Register a custom compatibility class.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param string $class_name The fully qualified class name.
	 *
	 * @return bool Whether the class was registered.
	 */
	public function register_custom_compatibility( string $gateway_id, string $class_name ): bool {
		$result = PaymentsCompatibilityManager::register_custom_compatibility( $gateway_id, $class_name );

		if ( $result ) {
			$this->log( "Custom payment compatibility registered for {$gateway_id}: {$class_name}" );
		}

		return $result;
	}

	/**
	 * Check if a gateway has compatibility support.
	 *
	 * @param string $gateway_id The gateway ID.
	 *
	 * @return bool
	 */
	public function has_compatibility_support( string $gateway_id ): bool {
		return PaymentsCompatibilityManager::get_compatibility_instance( $gateway_id ) !== null;
	}

	/**
	 * Get enabled compatibility layers.
	 *
	 * @return array<string, AbstractPaymentCompatibility>
	 */
	public function get_enabled_compatibility_layers(): array {
		$enabled       = array();
		$all_instances = PaymentsCompatibilityManager::get_all_instances();

		foreach ( $all_instances as $gateway_id => $instance ) {
			if ( $instance->is_enabled() ) {
				$enabled[ $gateway_id ] = $instance;
			}
		}

		return $enabled;
	}

	/**
	 * Get disabled compatibility layers.
	 *
	 * @return array<string, AbstractPaymentCompatibility>
	 */
	public function get_disabled_compatibility_layers(): array {
		$disabled      = array();
		$all_instances = PaymentsCompatibilityManager::get_all_instances();

		foreach ( $all_instances as $gateway_id => $instance ) {
			if ( ! $instance->is_enabled() ) {
				$disabled[ $gateway_id ] = $instance;
			}
		}

		return $disabled;
	}

	/**
	 * Reinitialize compatibility layers.
	 *
	 * @return void
	 */
	public function reinitialize(): void {
		PaymentsCompatibilityManager::clear_all();
		$this->initialized = false;
		$this->initialize();
	}

	/**
	 * Get compatibility statistics.
	 *
	 * @return array
	 */
	public function get_compatibility_statistics(): array {
		$all_instances  = PaymentsCompatibilityManager::get_all_instances();
		$enabled_count  = count( $this->get_enabled_compatibility_layers() );
		$disabled_count = count( $this->get_disabled_compatibility_layers() );

		return array(
			'total'       => count( $all_instances ),
			'enabled'     => $enabled_count,
			'disabled'    => $disabled_count,
			'initialized' => PaymentsCompatibilityManager::is_initialized(),
		);
	}

	/**
	 * Check if the service is initialized.
	 *
	 * @return bool
	 */
	public function is_initialized(): bool {
		return $this->initialized && PaymentsCompatibilityManager::is_initialized();
	}

	/**
	 * Get the payment gateway registry.
	 *
	 * @return PaymentGatewayRegistry
	 */
	public function get_registry(): PaymentGatewayRegistry {
		return $this->registry;
	}

	/**
	 * Validate compatibility layer configuration.
	 *
	 * @param string $gateway_id The gateway ID.
	 *
	 * @return array Validation results.
	 */
	public function validate_compatibility_layer( string $gateway_id ): array {
		$instance = $this->get_compatibility_instance( $gateway_id );

		if ( ! $instance ) {
			return array(
				'valid'  => false,
				'errors' => array( "No compatibility layer found for gateway: {$gateway_id}" ),
			);
		}

		$errors = array();

		// Check if gateway is available in WooCommerce.
		if ( ! $instance->is_gateway_available() ) {
			$errors[] = "Gateway {$gateway_id} is not available in WooCommerce";
		}

		// Check if the gateway has a corresponding payment integration.
		$gateway = $this->registry->get_gateway( $gateway_id );
		if ( ! $gateway ) {
			$errors[] = "No payment integration found for gateway: {$gateway_id}";
		}

		return array(
			'valid'        => empty( $errors ),
			'errors'       => $errors,
			'enabled'      => $instance->is_enabled(),
			'gateway_name' => $instance->get_gateway_name(),
		);
	}

	/**
	 * Log a message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private function log( string $message ): void {

		if ( Logger::is_tracing_enabled() ) {
			Logger::log( 'Autoship Payments Compatibility Service', $message );
		}
	}
}
