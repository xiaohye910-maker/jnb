<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Create payment method request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Payments;

/**
 * Request object for creating a payment method.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CreatePaymentMethodRequest {
	/**
	 * The customer ID.
	 *
	 * @var int
	 */
	private int $customer_id;

	/**
	 * The payment gateway type name.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * The last four digits of the card.
	 *
	 * @var string|null
	 */
	private ?string $last_four_digits = null;

	/**
	 * The expiration date.
	 *
	 * @var string|null
	 */
	private ?string $expiration = null;

	/**
	 * The payment gateway customer ID.
	 *
	 * @var string|null
	 */
	private ?string $gateway_customer_id = null;

	/**
	 * The payment gateway token.
	 *
	 * @var string|null
	 */
	private ?string $gateway_payment_id = null;

	/**
	 * The payment description.
	 *
	 * @var string|null
	 */
	private ?string $description = null;

	/**
	 * The customer's billing first name.
	 *
	 * @var string|null
	 */
	private ?string $billing_first_name = null;

	/**
	 * The customer's billing last name.
	 *
	 * @var string|null
	 */
	private ?string $billing_last_name = null;

	/**
	 * The customer's billing street address line 1.
	 *
	 * @var string|null
	 */
	private ?string $billing_street1 = null;

	/**
	 * The customer's billing street address line 2.
	 *
	 * @var string|null
	 */
	private ?string $billing_street2 = null;

	/**
	 * The customer's billing city.
	 *
	 * @var string|null
	 */
	private ?string $billing_city = null;

	/**
	 * The customer's billing state.
	 *
	 * @var string|null
	 */
	private ?string $billing_state = null;

	/**
	 * The customer's billing postcode.
	 *
	 * @var string|null
	 */
	private ?string $billing_postcode = null;

	/**
	 * The customer's billing country.
	 *
	 * @var string|null
	 */
	private ?string $billing_country = null;

	/**
	 * Whether this payment method is the default.
	 *
	 * @var bool|null
	 */
	private ?bool $is_default = null;

	/**
	 * Constructor.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $type The payment gateway type name.
	 */
	public function __construct( int $customer_id, string $type ) {
		$this->customer_id = $customer_id;
		$this->type        = $type;
	}

	/**
	 * Set the last four digits of the card.
	 *
	 * @param string $last_four_digits The last four digits of the card.
	 *
	 * @return self
	 */
	public function set_last_four_digits( string $last_four_digits ): self {
		$this->last_four_digits = $last_four_digits;

		return $this;
	}

	/**
	 * Set the expiration date.
	 *
	 * @param string $expiration The expiration date.
	 *
	 * @return self
	 */
	public function set_expiration( string $expiration ): self {
		$this->expiration = $expiration;

		return $this;
	}

	/**
	 * Set the payment gateway customer ID.
	 *
	 * @param string $gateway_customer_id The payment gateway customer ID.
	 *
	 * @return self
	 */
	public function set_gateway_customer_id( string $gateway_customer_id ): self {
		$this->gateway_customer_id = $gateway_customer_id;

		return $this;
	}

	/**
	 * Set the payment gateway token.
	 *
	 * @param string $gateway_payment_id The payment gateway token.
	 *
	 * @return self
	 */
	public function set_gateway_payment_id( string $gateway_payment_id ): self {
		$this->gateway_payment_id = $gateway_payment_id;

		return $this;
	}

	/**
	 * Set the payment description.
	 *
	 * @param string $description The payment description.
	 *
	 * @return self
	 */
	public function set_description( string $description ): self {
		$this->description = $description;

		return $this;
	}

	/**
	 * Set the billing address.
	 *
	 * @param string $first_name The first name.
	 * @param string $last_name The last name.
	 * @param string $street1 The street address line 1.
	 * @param string $city The city.
	 * @param string $state The state.
	 * @param string $postcode The postcode.
	 * @param string $country The country.
	 * @param string $street2 The street address line 2.
	 *
	 * @return self
	 */
	public function set_billing_address( string $first_name, string $last_name, string $street1, string $city, string $state, string $postcode, string $country, string $street2 = '' ): self {
		$this->billing_first_name = $first_name;
		$this->billing_last_name  = $last_name;
		$this->billing_street1    = $street1;
		$this->billing_street2    = $street2;
		$this->billing_city       = $city;
		$this->billing_state      = $state;
		$this->billing_postcode   = $postcode;
		$this->billing_country    = $country;

		return $this;
	}

	/**
	 * Set whether this payment method is the default.
	 *
	 * @param bool $is_default Whether this payment method is the default.
	 *
	 * @return self
	 */
	public function set_is_default( bool $is_default ): self {
		$this->is_default = $is_default;

		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'customerId' => $this->customer_id,
			'type'       => $this->type,
		);

		if ( null !== $this->last_four_digits ) {
			$data['lastFourDigits'] = $this->last_four_digits;
		}

		if ( null !== $this->expiration ) {
			$data['expiration'] = $this->expiration;
		}

		if ( null !== $this->gateway_customer_id ) {
			$data['gatewayCustomerId'] = $this->gateway_customer_id;
		}

		if ( null !== $this->gateway_payment_id ) {
			$data['gatewayPaymentId'] = $this->gateway_payment_id;
		}

		if ( null !== $this->description ) {
			$data['description'] = $this->description;
		}

		if ( null !== $this->billing_first_name ) {
			$data['billingFirstName'] = $this->billing_first_name;
		}

		if ( null !== $this->billing_last_name ) {
			$data['billingLastName'] = $this->billing_last_name;
		}

		if ( null !== $this->billing_street1 ) {
			$data['billingStreet1'] = $this->billing_street1;
		}

		if ( null !== $this->billing_street2 ) {
			$data['billingStreet2'] = $this->billing_street2;
		}

		if ( null !== $this->billing_city ) {
			$data['billingCity'] = $this->billing_city;
		}

		if ( null !== $this->billing_state ) {
			$data['billingState'] = $this->billing_state;
		}

		if ( null !== $this->billing_postcode ) {
			$data['billingPostcode'] = $this->billing_postcode;
		}

		if ( null !== $this->billing_country ) {
			$data['billingCountry'] = $this->billing_country;
		}

		if ( null !== $this->is_default ) {
			$data['isDefault'] = $this->is_default;
		}

		return $data;
	}
}
