<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents a factory method for creating payment integrations.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain;

use Autoship\Domain\PaymentIntegrations\AirwallexPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\AuthorizeNetPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\BraintreePaymentIntegration;
use Autoship\Domain\PaymentIntegrations\CheckoutPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\CyberSourcePaymentIntegration;
use Autoship\Domain\PaymentIntegrations\CyberSourceV2PaymentIntegration;
use Autoship\Domain\PaymentIntegrations\NmiPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\PayaV1PaymentIntegration;
use Autoship\Domain\PaymentIntegrations\SagePaymentIntegration;
use Autoship\Domain\PaymentIntegrations\SquarePaymentIntegration;
use Autoship\Domain\PaymentIntegrations\StripePaymentIntegration;
use Autoship\Domain\PaymentIntegrations\TestPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\TrustCommercePaymentIntegration;
use Autoship\Domain\PaymentIntegrations\PayPalPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\UnknownPaymentIntegration;
use Autoship\Domain\PaymentIntegrations\FunnelKitPaymentIntegration;
use Exception;

/**
 * Represents a factory method for creating payment integrations.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PaymentIntegrationFactory {

	/**
	 * Create a payment integration based on the gateway id.
	 *
	 * @param string $gateway_id The payment gateway id.
	 * @param array  $settings The settings for the payment gateway.
	 *
	 * @return PaymentIntegration
	 */
	public static function create( string $gateway_id, array $settings ): PaymentIntegration {

		try {
			switch ( $gateway_id ) {
				case 'authorize_net_cim_credit_card':
					return AuthorizeNetPaymentIntegration::build( $gateway_id, $settings );
				case 'stripe':
				case 'stripe_sepa':
					return StripePaymentIntegration::build( $gateway_id, $settings );
				case 'nmi_gateway_woocommerce_credit_card':
				case 'nmi':
					return NmiPaymentIntegration::build( $gateway_id, $settings );
				case 'sagepaymentsusaapi':
					return PayaV1PaymentIntegration::build( $gateway_id, $settings );
				case 'trustcommerce':
					return TrustCommercePaymentIntegration::build( $gateway_id, $settings );
				case 'braintree_credit_card':
				case 'braintree_paypal':
					return BraintreePaymentIntegration::build( $gateway_id, $settings );
				case 'cybersource_credit_card':
					return CyberSourceV2PaymentIntegration::build( $gateway_id, $settings );
				case 'square_credit_card':
					return SquarePaymentIntegration::build( $gateway_id, $settings );
				case 'ppec_paypal':
					return PayPalPaymentIntegration::build( $gateway_id, $settings );
				case 'autoship-test-gateway':
					return TestPaymentIntegration::build( $gateway_id, $settings );
				case 'wc_checkout_com_cards':
					return CheckoutPaymentIntegration::build( $gateway_id, $settings );
				case 'cybersource':
					return CyberSourcePaymentIntegration::build( $gateway_id, $settings );
				case 'sagepaydirect':
					return SagePaymentIntegration::build( $gateway_id, $settings );
				case 'fkwcs_stripe':
				case 'fkwcs_stripe_ach':
					return FunnelKitPaymentIntegration::build( $gateway_id, $settings );
				case 'airwallex_card':
					return AirwallexPaymentIntegration::build( $gateway_id, $settings );
				default:
					return UnknownPaymentIntegration::build( $gateway_id, $settings );
			}
		} catch ( Exception $e ) {
			return UnknownPaymentIntegration::build( $gateway_id, $settings );
		}
	}
}
