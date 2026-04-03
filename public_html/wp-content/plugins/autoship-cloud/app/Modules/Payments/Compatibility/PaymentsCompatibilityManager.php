<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Compatibility Manager
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Compatibility;

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;
use Autoship\Modules\Payments\Services\PaymentGatewayRegistry;
use Autoship\Services\Logging\Logger;
use Exception;

/**
 * Manager for all payment gateway backward compatibility classes.
 *
 * @package Autoship\Modules\Payments\Compatibility
 * @since 3.0.0
 */
class PaymentsCompatibilityManager {

	/**
	 * Registered backward compatibility instances.
	 *
	 * @var array<string, AbstractPaymentCompatibility>
	 */
	private static array $compatibility_instances = array();

	/**
	 * Whether the manager has been initialized.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;

	/**
	 * Available compatibility classes.
	 *
	 * @var array<string, string>
	 */
	private static array $available_compatibility_classes = array(
		'stripe'                        => 'Autoship\\Modules\\Payments\\Compatibility\\StripePaymentCompatibility',
		'stripe_sepa'                   => 'Autoship\\Modules\\Payments\\Compatibility\\StripePaymentCompatibility',
		'authorize_net_cim_credit_card' => 'Autoship\\Modules\\Payments\\Compatibility\\AuthorizeNetPaymentCompatibility',
		'ppec_paypal'                   => 'Autoship\\Modules\\Payments\\Compatibility\\PayPalPaymentCompatibility',
		'square_credit_card'            => 'Autoship\\Modules\\Payments\\Compatibility\\SquarePaymentCompatibility',
		'square_cash_app_pay'           => 'Autoship\\Modules\\Payments\\Compatibility\\SquarePaymentCompatibility',
		'braintree_credit_card'               => 'Autoship\\Modules\\Payments\\Compatibility\\BraintreePaymentCompatibility',
		'braintree_paypal'                    => 'Autoship\\Modules\\Payments\\Compatibility\\BraintreePaymentCompatibility',
		'cybersource_credit_card'             => 'Autoship\\Modules\\Payments\\Compatibility\\CyberSourceV2PaymentCompatibility',
		'nmi_gateway_woocommerce_credit_card' => 'Autoship\\Modules\\Payments\\Compatibility\\NmiPaymentCompatibility',
		'nmi'                                 => 'Autoship\\Modules\\Payments\\Compatibility\\NmiPaymentCompatibility',
		'sagepaymentsusaapi'                  => 'Autoship\\Modules\\Payments\\Compatibility\\PayaV1PaymentCompatibility',
		'trustcommerce'                       => 'Autoship\\Modules\\Payments\\Compatibility\\TrustCommercePaymentCompatibility',
		'sagepaydirect'                       => 'Autoship\\Modules\\Payments\\Compatibility\\SagePaymentCompatibility',
		'wc_checkout_com_cards'               => 'Autoship\\Modules\\Payments\\Compatibility\\CheckoutPaymentCompatibility',
		'airwallex_card'                      => 'Autoship\\Modules\\Payments\\Compatibility\\AirwallexPaymentCompatibility',
	);

	/**
	 * Initialize all backward compatibility layers.
	 *
	 * @param ?PaymentGatewayRegistry $registry The payment gateway registry.
	 *
	 * @return void
	 */
	public static function init( ?PaymentGatewayRegistry $registry = null ): void {
		if ( self::$initialized ) {
			return;
		}

		// Register all backward compatibility classes.
		self::register_compatibility_classes( $registry );

		// Initialize all registered instances.
		self::initialize_all();

		self::$initialized = true;
	}

	/**
	 * Register all backward compatibility classes.
	 *
	 * @param ?PaymentGatewayRegistry $registry The payment gateway registry.
	 *
	 * @return void
	 */
	private static function register_compatibility_classes( ?PaymentGatewayRegistry $registry = null ): void {
		// Get available WooCommerce payment gateways.
		$registered_gateways = $registry->get_all_gateways();
		$available_gateways  = array();

		foreach ( $registered_gateways as $gateway ) {
			$available_gateways[] = $gateway->get_method_id();
		}

		// Get the feature manager to check gateway feature flags.
		$feature_manager = self::get_feature_manager();

		foreach ( self::$available_compatibility_classes as $gateway_id => $class_name ) {
			// Check if gateway is enabled via feature flag.
			if ( null !== $feature_manager && ! $feature_manager->is_payment_gateway_enabled( $gateway_id ) ) {

				if ( Logger::is_tracing_enabled() ) {
					Logger::log( 'Autoship Payments Compatibility Manager', "Gateway {$gateway_id} disabled by feature flag, skipping registration" );
				}

				continue;
			}

			// Only register if the gateway is available or if we're in admin/testing context.
			if ( in_array( $gateway_id, $available_gateways, true ) || self::should_register_all() ) {
				try {
					if ( class_exists( $class_name ) ) {
						$instance = new $class_name();
						if ( $instance instanceof AbstractPaymentCompatibility ) {
							self::register( $instance );
						}
					}
				} catch ( Exception $exception ) {
					Logger::log( 'Autoship Payments Compatibility Manager', "Failed to register payment compatibility for {$gateway_id}: " . $exception->getMessage() );
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
	 * Check if we should register all compatibility classes regardless of availability.
	 *
	 * @return bool
	 */
	private static function should_register_all(): bool {
		return is_admin() || ( defined( 'WP_CLI' ) && true === constant( 'WP_CLI' ) ) || ( defined( 'DOING_CRON' ) && DOING_CRON );
	}

	/**
	 * Register a backward compatibility instance.
	 *
	 * @param AbstractPaymentCompatibility $instance The compatibility instance.
	 *
	 * @return void
	 */
	public static function register( AbstractPaymentCompatibility $instance ): void {
		self::$compatibility_instances[ $instance->get_gateway_id() ] = $instance;
	}

	/**
	 * Initialize all registered backward compatibility instances.
	 *
	 * @return void
	 */
	private static function initialize_all(): void {
		foreach ( self::$compatibility_instances as $instance ) {
			if ( $instance->is_enabled() ) {
				try {
					$instance->init();
				} catch ( Exception $exception ) {
					Logger::log( 'Autoship Payments Compatibility Manager', "Failed to initialize payment compatibility for {$instance->get_gateway_id()}: " . $exception->getMessage() );
				}
			}
		}
	}

	/**
	 * Get a specific backward compatibility instance.
	 *
	 * @param string $gateway_id The gateway ID.
	 *
	 * @return AbstractPaymentCompatibility|null
	 */
	public static function get_compatibility_instance( string $gateway_id ): ?AbstractPaymentCompatibility {
		return self::$compatibility_instances[ $gateway_id ] ?? null;
	}

	/**
	 * Get all registered backward compatibility instances.
	 *
	 * @return array<string, AbstractPaymentCompatibility>
	 */
	public static function get_all_instances(): array {
		return self::$compatibility_instances;
	}

	/**
	 * Enable or disable a specific compatibility layer.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param bool   $enabled Whether to enable the compatibility layer.
	 *
	 * @return bool Whether the operation was successful.
	 */
	public static function set_compatibility_enabled( string $gateway_id, bool $enabled ): bool {
		$instance = self::get_compatibility_instance( $gateway_id );
		if ( $instance ) {
			$instance->set_enabled( $enabled );

			return true;
		}

		return false;
	}

	/**
	 * Get the status of all compatibility layers.
	 *
	 * @return array<string, array>
	 */
	public static function get_compatibility_status(): array {
		$status = array();
		foreach ( self::$compatibility_instances as $gateway_id => $instance ) {
			$status[ $gateway_id ] = array(
				'name'    => $instance->get_gateway_name(),
				'enabled' => $instance->is_enabled(),
				'class'   => get_class( $instance ),
			);
		}

		return $status;
	}

	/**
	 * Unregister a compatibility instance.
	 *
	 * @param string $gateway_id The gateway ID.
	 *
	 * @return bool Whether the instance was unregistered.
	 */
	public static function unregister( string $gateway_id ): bool {
		if ( isset( self::$compatibility_instances[ $gateway_id ] ) ) {
			unset( self::$compatibility_instances[ $gateway_id ] );

			return true;
		}

		return false;
	}

	/**
	 * Clear all registered compatibility instances.
	 *
	 * @return void
	 */
	public static function clear_all(): void {
		self::$compatibility_instances = array();
		self::$initialized             = false;
	}

	/**
	 * Check if the manager has been initialized.
	 *
	 * @return bool
	 */
	public static function is_initialized(): bool {
		return self::$initialized;
	}

	/**
	 * Get the count of registered compatibility instances.
	 *
	 * @return int
	 */
	public static function get_count(): int {
		return count( self::$compatibility_instances );
	}

	/**
	 * Register a custom compatibility class.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @param string $class_name The fully qualified class name.
	 *
	 * @return bool Whether the class was registered.
	 */
	public static function register_custom_compatibility( string $gateway_id, string $class_name ): bool {
		if ( class_exists( $class_name ) ) {
			self::$available_compatibility_classes[ $gateway_id ] = $class_name;

			// If already initialized, register and initialize immediately.
			if ( self::$initialized ) {
				try {
					$instance = new $class_name();
					if ( $instance instanceof AbstractPaymentCompatibility ) {
						self::register( $instance );
						if ( $instance->is_enabled() ) {
							$instance->init();
						}

						return true;
					}
				} catch ( Exception $exception ) {
					Logger::log( 'Autoship Payments Compatibility Manager', "Failed to register custom payment compatibility for {$gateway_id}: " . $exception->getMessage() );
				}
			}

			return true;
		}

		return false;
	}
}
