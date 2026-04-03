<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Gateway Registry
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Services;

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;
use Autoship\Domain\AbstractPaymentGateway;
use Autoship\Domain\PaymentIntegrationFactory;
use Autoship\Services\Logging\Logger;
use Exception;

/**
 * Registry service for managing payment gateways.
 *
 * @package Autoship\Modules\PaymentGateways\Services
 * @since 3.0.0
 */
class PaymentGatewayRegistry {

	/**
	 * Registered gateways.
	 *
	 * @var array
	 */
	private array $gateways = array();

	/**
	 * Initialize the registry with available gateways.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$supported_methods  = autoship_get_valid_payment_methods();

		// Get the feature manager to check gateway feature flags.
		$feature_manager = self::get_feature_manager();

		foreach ( $available_gateways as $gateway_id => $gateway ) {
			// Check if gateway is enabled via feature flag.
			if ( null !== $feature_manager && ! $feature_manager->is_payment_gateway_enabled( $gateway_id ) ) {
				Logger::log( 'Autoship Payments Compatibility Manager', "Gateway {$gateway_id} disabled by feature flag, skipping registration" );
				continue;
			}

			if ( array_key_exists( $gateway_id, $supported_methods ) ) {
				try {
					$integration = PaymentIntegrationFactory::create( $gateway_id, $gateway->settings );
					if ( $integration instanceof AbstractPaymentGateway ) {
						$integration->initialize();
						$this->register_gateway( $integration );
					}
				} catch ( Exception $e ) {
					Logger::error( 'Autoship Payments Registry', "Failed to register payment gateway {$gateway_id}: " . $e->getMessage() );
				}
			}
		}
	}

	/**
	 * Get the feature manager instance.
	 *
	 * @return FeatureManagerInterface|null The feature manager or null if not available.
	 */
	private static function get_feature_manager(): ?FeatureManagerInterface {
		try {
			$container = Plugin::get_service_container();
			if ( null !== $container ) {
				return $container->get( FeatureManagerInterface::class );
			}
		} catch ( Exception $exception ) {
			Logger::log( 'Autoship Payments Compatibility Manager', 'Failed to get feature manager: ' . $exception->getMessage() );
		}

		return null;
	}

	/**
	 * Register a payment gateway.
	 *
	 * @param AbstractPaymentGateway $gateway The gateway to register.
	 * @return void
	 */
	public function register_gateway( AbstractPaymentGateway $gateway ): void {
		$this->gateways[ $gateway->get_method_id() ] = $gateway;
	}

	/**
	 * Get a gateway by ID.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @return AbstractPaymentGateway|null
	 */
	public function get_gateway( string $gateway_id ): ?AbstractPaymentGateway {
		return $this->gateways[ $gateway_id ] ?? null;
	}

	/**
	 * Get all registered gateways.
	 *
	 * @return array
	 */
	public function get_all_gateways(): array {
		return $this->gateways;
	}
}
