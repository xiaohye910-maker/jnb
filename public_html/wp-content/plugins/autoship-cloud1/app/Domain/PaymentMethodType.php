<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Represents the payment method types supported by Autoship Cloud.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain;

/**
 * Represents the payment method types supported by Autoship Cloud.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PaymentMethodType {
	/**
	 * Represents the Stripe payment method code.
	 *
	 * @var int
	 */
	public const STRIPE = 0;

	/**
	 * Represents the AuthorizeNet payment method code.
	 *
	 * @var int
	 */
	public const AUTHORIZE_NET = 1;

	/**
	 * Represents the Test payment method code.
	 *
	 * @var int
	 */
	public const TEST = 2;

	/**
	 * Represents the PayPal payment method code.
	 *
	 * @var int
	 */
	public const PAYPAL = 3;

	/**
	 * Represents the BrainTree payment method code.
	 *
	 * @var int
	 */
	public const BRAINTREE = 4;

	/**
	 * Represents the CyberSource payment method code.
	 *
	 * @var int
	 */
	public const CYBER_SOURCE = 5;

	/**
	 * Represents the Undefined payment method code.
	 *
	 * @var int
	 */
	public const UNDEFINED = 6;

	/**
	 * Represents the Other payment method code.
	 *
	 * @var int
	 */
	public const OTHER = 7;

	/**
	 * Represents the Nmi payment method code.
	 *
	 * @var int
	 */
	public const NMI = 8;

	/**
	 * Represents the PayaV1 payment method code.
	 *
	 * @var int
	 */
	public const PAYA_V1 = 9;

	/**
	 * Represents the TrustCommerce payment method code.
	 *
	 * @var int
	 */
	public const TRUST_COMMERCE = 10;

	/**
	 * Represents the Square payment method code.
	 *
	 * @var int
	 */
	public const SQUARE = 12;

	/**
	 * Represents the Sage payment method code.
	 *
	 * @var int
	 */
	public const SAGE = 13;

	/**
	 * Represents the CyberSourceV2 payment method code.
	 *
	 * @var int
	 */
	public const CYBER_SOURCE_V2 = 14;

	/**
	 * Represents the Checkout payment method code.
	 *
	 * @var int
	 */
	public const CHECKOUT = 15;

	/**
	 * Represents the PayPalV2 payment method code.
	 *
	 * @var int
	 */
	public const PAYPAL_V2 = 16;

	/**
	 * Represents the Shopify payment method code.
	 *
	 * @var int
	 */
	public const SHOPIFY = 17;

	/**
	 * Represents the PayPalV3 payment method code.
	 *
	 * @var int
	 */
	public const PAYPAL_V3 = 18;

	/**
	 * Represents the Airwallex payment method code.
	 *
	 * @var int
	 */
	public const AIRWALLEX = 19;

	/**
	 * Gets the name of the payment method type.
	 *
	 * @param int $code The code of the payment method type.
	 *
	 * @return string
	 */
	public static function get_method_name( int $code ): string {
		$method_names = array(
			self::STRIPE          => 'Stripe',
			self::AUTHORIZE_NET   => 'Authorize.Net',
			self::TEST            => 'Test Payment',
			self::PAYPAL          => 'PayPal',
			self::BRAINTREE       => 'BrainTree',
			self::CYBER_SOURCE    => 'CyberSource',
			self::UNDEFINED       => 'Undefined',
			self::OTHER           => 'Other',
			self::NMI             => 'Credit Card via NMI',
			self::PAYA_V1         => 'Credit Card via Paya',
			self::TRUST_COMMERCE  => 'Trust Commerce',
			self::SQUARE          => 'Credit Card via Square',
			self::SAGE            => 'Sage',
			self::CYBER_SOURCE_V2 => 'Credit Card via CyberSource',
			self::CHECKOUT        => 'Pay by Card with Checkout.com',
			self::PAYPAL_V2       => 'PayPal V2',
			self::SHOPIFY         => 'Shopify',
			self::PAYPAL_V3       => 'PayPal V3',
			self::AIRWALLEX       => 'Airwallex',
		);

		return $method_names[ $code ] ?? '';
	}

	/**
	 * Gets the enum name of the payment method type.
	 *
	 * @param int $code The code of the payment method type.
	 *
	 * @return string
	 */
	public static function get_enum_name( int $code ): string {
		$method_names = array(
			self::STRIPE          => 'Stripe',
			self::AUTHORIZE_NET   => 'AuthorizeNet',
			self::TEST            => 'Test',
			self::PAYPAL          => 'PayPal',
			self::BRAINTREE       => 'Braintree',
			self::CYBER_SOURCE    => 'CyberSource',
			self::UNDEFINED       => 'Undefined',
			self::OTHER           => 'Other',
			self::NMI             => 'Nmi',
			self::PAYA_V1         => 'PayaV1',
			self::TRUST_COMMERCE  => 'TrustCommerce',
			self::SQUARE          => 'Square',
			self::SAGE            => 'Sage',
			self::CYBER_SOURCE_V2 => 'CyberSourceV2',
			self::CHECKOUT        => 'Checkout',
			self::PAYPAL_V2       => 'PayPalV2',
			self::SHOPIFY         => 'Shopify',
			self::PAYPAL_V3       => 'PayPalV3',
			self::AIRWALLEX       => 'Airwallex',
		);

		return $method_names[ $code ] ?? '';
	}

	/**
	 * Gets the code of the payment method type.
	 *
	 * @param string $enum_name The name of the payment method type.
	 * @return int
	 */
	public static function get_enum_code( string $enum_name ): int {
		$method_codes = array(
			'Stripe'        => self::STRIPE,
			'AuthorizeNet'  => self::AUTHORIZE_NET,
			'Test'          => self::TEST,
			'PayPal'        => self::PAYPAL,
			'Braintree'     => self::BRAINTREE,
			'CyberSource'   => self::CYBER_SOURCE,
			'Undefined'     => self::UNDEFINED,
			'Other'         => self::OTHER,
			'Nmi'           => self::NMI,
			'PayaV1'        => self::PAYA_V1,
			'TrustCommerce' => self::TRUST_COMMERCE,
			'Square'        => self::SQUARE,
			'Sage'          => self::SAGE,
			'CyberSourceV2' => self::CYBER_SOURCE_V2,
			'Checkout'      => self::CHECKOUT,
			'PayPalV2'      => self::PAYPAL_V2,
			'Shopify'       => self::SHOPIFY,
			'PayPalV3'      => self::PAYPAL_V3,
			'Airwallex'     => self::AIRWALLEX,
		);

		return $method_codes[ $enum_name ] ?? self::UNDEFINED;
	}
}
