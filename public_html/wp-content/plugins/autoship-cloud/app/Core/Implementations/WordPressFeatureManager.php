<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the feature manager.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\FeatureManagerInterface;
use Autoship\Services\Logging\Logger;

/**
 * WordPress implementation of the feature manager.
 *
 * For payment gateway flags (payments_gateway_*), this implementation supports
 * a two-tier priority chain:
 *
 * 1. QPilot remote flags (authoritative when available)
 * 2. Hardcoded defaults (fallback) - the $features array below
 *
 * @package Autoship
 * @since 2.11.0
 */
class WordPressFeatureManager implements FeatureManagerInterface {

	/**
	 * Prefix identifying payment gateway feature flags.
	 *
	 * @var string
	 */
	const PAYMENT_GATEWAY_PREFIX = 'payments_gateway_';

	/**
	 * The features enabled.
	 *
	 * @var array|string[]
	 */
	protected array $features = array(
		'development_mode'                     => false,
		'quicklaunch'                          => true,
		'quicklaunch_lead_registration'        => true,
		'quicklaunch_account_login'            => true,
		'quicklaunch_account_logout'           => true,
		'quicklaunch_account_register'         => true,
		'quicklaunch_product_setup'            => true,
		'quicklaunch_payment_method_installer' => true,
		'quicklaunch_reset'                    => false,
		'quicklaunch_display_beacon'           => true,
		'product_sync'                         => true,
		'nextime'                              => true,
		'quicklaunch_phone_required'           => true,
		'quicklinks'                           => true,
		'qmc_components'                       => true,
		'payments'                             => true,
		'healthcheck'                          => true,
		'utilities'                            => true,

		// Per-gateway payment feature flags.
		'payments_gateway_stripe'              => true,  // Stripe enabled.
		'payments_gateway_stripe_sepa'         => true,  // Stripe SEPA enabled.
		'payments_gateway_authorize_net'       => true,  // Authorize.Net enabled.
		'payments_gateway_braintree'           => false, // Braintree disabled by default.
		'payments_gateway_square'              => false, // Square enabled.
		'payments_gateway_paypal'              => false, // PayPal enabled.
		'payments_gateway_nmi'                 => false, // NMI disabled by default.
		'payments_gateway_cybersource'         => false, // CyberSource disabled by default.
		'payments_gateway_trustcommerce'       => false, // TrustCommerce disabled by default.
		'payments_gateway_sage'                => false, // Sage disabled by default.
		'payments_gateway_checkoutcom'         => false, // Checkout.com disabled by default.
		'payments_gateway_opayo'               => false, // Opayo disabled by default.
		'payments_gateway_paya'                => false, // Paya disabled by default.
		'payments_gateway_airwallex'           => false, // Airwallex disabled by default.
	);

	/**
	 * Mapping of gateway IDs to feature keys.
	 *
	 * Multiple WooCommerce gateway IDs can map to a single feature flag.
	 *
	 * @var array<string, string>
	 */
	protected array $gateway_feature_map = array(
		'stripe'                              => 'stripe',
		'stripe_sepa'                         => 'stripe_sepa',
		'authorize_net_cim_credit_card'       => 'authorize_net',
		'braintree_credit_card'               => 'braintree',
		'braintree_paypal'                    => 'braintree',
		'square_credit_card'                  => 'square',
		'square_cash_app_pay'                 => 'square',
		'paypal'                              => 'paypal',
		'ppcp-gateway'                        => 'paypal',
		'nmi_gateway_woocommerce'             => 'nmi',
		'nmi_gateway_woocommerce_credit_card' => 'nmi',
		'nmi'                                 => 'nmi',
		'cybersource_credit_card'             => 'cybersource',
		'trustcommerce'                       => 'trustcommerce',
		'sagepaymentsusaapi'                  => 'sage',
		'sagepaydirect'                       => 'sage',
		'checkoutcom_card_payment'            => 'checkoutcom',
		'wc_checkout_com_cards'               => 'checkoutcom',
		'opayo_direct'                        => 'opayo',
		'paya_gateway_credit_card'            => 'paya',
		'airwallex_card'                      => 'airwallex',
	);

	/**
	 * A callable that returns an array of remotely enabled feature flag names,
	 * or null if remote flags are unavailable.
	 *
	 * @var callable|null
	 */
	private $remote_flags_provider = null;

	/**
	 * Cached result from the remote flags provider.
	 *
	 * @var array<string>|null
	 */
	private ?array $remote_flags = null;

	/**
	 * Whether remote flags have already been resolved.
	 *
	 * @var bool
	 */
	private bool $remote_flags_resolved = false;

	/**
	 * Set the remote flags provider.
	 *
	 * The provider is a callable that returns an array of enabled feature flag
	 * names from QPilot, or null if remote flags are unavailable.
	 *
	 * @param callable $provider A callable returning array<string>|null.
	 *
	 * @return void
	 */
	public function set_remote_flags_provider( callable $provider ): void {
		$this->remote_flags_provider = $provider;
		$this->remote_flags          = null;
		$this->remote_flags_resolved = false;
	}

	/**
	 * Gets the value indicating if the feature is enabled or not.
	 *
	 * For payment gateway flags (payments_gateway_*), the priority chain is:
	 * 1. QPilot remote flags (authoritative when available)
	 * 2. Hardcoded default in $features array
	 *
	 * For all other flags, returns the hardcoded default.
	 *
	 * @param string $feature The name of the feature.
	 *
	 * @return bool
	 */
	public function is_enabled( string $feature ): bool {
		// For payment gateway flags, check QPilot remote flags first.
		if ( $this->is_payment_gateway_feature( $feature ) ) {
			$remote_flags = $this->resolve_remote_flags();

			if ( Logger::is_tracing_enabled() ) {
				Logger::log( 'Feature Manager', "Checking feature '{$feature}' - Remote flags: " . ( is_array( $remote_flags ) ? implode( ', ', $remote_flags ) : 'null' ) );
			}

			if ( null !== $remote_flags ) {
				return in_array( $feature, $remote_flags, true );
			}
		}

		// Hardcoded default (for all flags, and fallback for payment flags).
		return ! empty( $this->features[ $feature ] );
	}

	/**
	 * Gets the value indicating if a payment gateway feature is enabled.
	 *
	 * This method checks both the main 'payments' feature flag and the
	 * individual gateway feature flag.
	 *
	 * @param string $gateway_id The payment gateway ID (e.g., 'stripe', 'braintree_credit_card').
	 *
	 * @return bool
	 */
	public function is_payment_gateway_enabled( string $gateway_id ): bool {
		// First check if the main payments module is enabled.
		if ( ! $this->is_enabled( 'payments' ) ) {
			return false;
		}

		// Normalize gateway ID to feature key and check.
		$feature_key = self::PAYMENT_GATEWAY_PREFIX . $this->normalize_gateway_id( $gateway_id );

		return $this->is_enabled( $feature_key );
	}

	/**
	 * Normalize a gateway ID to its feature key.
	 *
	 * Maps various WooCommerce gateway IDs to their corresponding feature keys.
	 * For example, 'braintree_credit_card' and 'braintree_paypal' both map to 'braintree'.
	 *
	 * @param string $gateway_id The gateway ID.
	 *
	 * @return string The normalized feature key.
	 */
	protected function normalize_gateway_id( string $gateway_id ): string {
		// Check if we have a specific mapping.
		if ( isset( $this->gateway_feature_map[ $gateway_id ] ) ) {
			return $this->gateway_feature_map[ $gateway_id ];
		}

		// Fall back to sanitizing the gateway ID.
		return str_replace( array( '-', ' ' ), '_', strtolower( $gateway_id ) );
	}

	/**
	 * Check if a feature name is a payment gateway feature.
	 *
	 * @param string $feature The feature name.
	 *
	 * @return bool True if the feature is a payment gateway flag.
	 */
	private function is_payment_gateway_feature( string $feature ): bool {
		return 0 === strpos( $feature, self::PAYMENT_GATEWAY_PREFIX );
	}

	/**
	 * Lazily resolve remote feature flags from the provider.
	 *
	 * The provider is only called once per request. Subsequent calls
	 * return the cached result.
	 *
	 * @return array<string>|null The list of enabled flag names, or null if unavailable.
	 */
	private function resolve_remote_flags(): ?array {
		if ( $this->remote_flags_resolved ) {
			return $this->remote_flags;
		}

		$this->remote_flags_resolved = true;

		if ( null === $this->remote_flags_provider ) {
			return null;
		}

		$this->remote_flags = call_user_func( $this->remote_flags_provider );

		return $this->remote_flags;
	}
}
