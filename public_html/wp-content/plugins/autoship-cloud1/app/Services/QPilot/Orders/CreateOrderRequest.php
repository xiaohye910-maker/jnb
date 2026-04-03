<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Create order request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Request object for creating a new scheduled order.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CreateOrderRequest {
	/**
	 * The customer ID.
	 *
	 * @var int
	 */
	private int $customer_id;

	/**
	 * The order name.
	 *
	 * @var string|null
	 */
	private ?string $name = null;

	/**
	 * The next occurrence date in UTC.
	 *
	 * @var string|null
	 */
	private ?string $next_occurrence_utc = null;

	/**
	 * The next occurrence offset.
	 *
	 * @var string|null
	 */
	private ?string $next_occurrence_offset = null;

	/**
	 * The UTC offset.
	 *
	 * @var int|null
	 */
	private ?int $utc_offset = null;

	/**
	 * The order status.
	 *
	 * @var string|null
	 */
	private ?string $status = null;

	/**
	 * The frequency type.
	 *
	 * @var string|null
	 */
	private ?string $frequency_type = null;

	/**
	 * The frequency display name.
	 *
	 * @var string|null
	 */
	private ?string $frequency_display_name = null;

	/**
	 * The frequency value.
	 *
	 * @var int|null
	 */
	private ?int $frequency = null;

	/**
	 * The payment method ID.
	 *
	 * @var int|null
	 */
	private ?int $payment_method_id = null;

	/**
	 * Whether to authorize only.
	 *
	 * @var bool|null
	 */
	private ?bool $authorize_only = null;

	/**
	 * The currency ISO code.
	 *
	 * @var string|null
	 */
	private ?string $currency_iso = null;

	/**
	 * The order subtotal.
	 *
	 * @var float|null
	 */
	private ?float $subtotal = null;

	/**
	 * The shipping total.
	 *
	 * @var float|null
	 */
	private ?float $shipping_total = null;

	/**
	 * The tax total.
	 *
	 * @var float|null
	 */
	private ?float $tax_total = null;

	/**
	 * The order total.
	 *
	 * @var float|null
	 */
	private ?float $total = null;

	/**
	 * The shipping rate name.
	 *
	 * @var string|null
	 */
	private ?string $shipping_rate_name = null;

	/**
	 * The shipping first name.
	 *
	 * @var string|null
	 */
	private ?string $shipping_first_name = null;

	/**
	 * The shipping last name.
	 *
	 * @var string|null
	 */
	private ?string $shipping_last_name = null;

	/**
	 * The shipping street address line 1.
	 *
	 * @var string|null
	 */
	private ?string $shipping_street1 = null;

	/**
	 * The shipping street address line 2.
	 *
	 * @var string|null
	 */
	private ?string $shipping_street2 = null;

	/**
	 * The shipping city.
	 *
	 * @var string|null
	 */
	private ?string $shipping_city = null;

	/**
	 * The shipping state.
	 *
	 * @var string|null
	 */
	private ?string $shipping_state = null;

	/**
	 * The shipping postcode.
	 *
	 * @var string|null
	 */
	private ?string $shipping_postcode = null;

	/**
	 * The shipping country.
	 *
	 * @var string|null
	 */
	private ?string $shipping_country = null;

	/**
	 * The phone number.
	 *
	 * @var string|null
	 */
	private ?string $phone_number = null;

	/**
	 * The company name.
	 *
	 * @var string|null
	 */
	private ?string $company = null;

	/**
	 * The original external ID.
	 *
	 * @var string|null
	 */
	private ?string $original_external_id = null;

	/**
	 * The order note.
	 *
	 * @var string|null
	 */
	private ?string $note = null;

	/**
	 * The scheduled order items.
	 *
	 * @var array|null
	 */
	private ?array $scheduled_order_items = null;

	/**
	 * The estimated delivery date.
	 *
	 * @var string|null
	 */
	private ?string $estimated_delivery_date = null;

	/**
	 * Constructor.
	 *
	 * @param int $customer_id The customer ID.
	 */
	public function __construct( int $customer_id ) {
		$this->customer_id = $customer_id;
	}

	/**
	 * Set the order name.
	 *
	 * @param string $name The order name.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * Set the next occurrence date in UTC.
	 *
	 * @param string $next_occurrence_utc The next occurrence date in UTC.
	 *
	 * @return self
	 */
	public function set_next_occurrence_utc( string $next_occurrence_utc ): self {
		$this->next_occurrence_utc = $next_occurrence_utc;

		return $this;
	}

	/**
	 * Set the next occurrence offset.
	 *
	 * @param string $next_occurrence_offset The next occurrence offset.
	 *
	 * @return self
	 */
	public function set_next_occurrence_offset( string $next_occurrence_offset ): self {
		$this->next_occurrence_offset = $next_occurrence_offset;

		return $this;
	}

	/**
	 * Set the UTC offset.
	 *
	 * @param int $utc_offset The UTC offset.
	 *
	 * @return self
	 */
	public function set_utc_offset( int $utc_offset ): self {
		$this->utc_offset = $utc_offset;

		return $this;
	}

	/**
	 * Set the order status.
	 *
	 * @param string $status The order status.
	 *
	 * @return self
	 */
	public function set_status( string $status ): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * Set the frequency type.
	 *
	 * @param string $frequency_type The frequency type.
	 *
	 * @return self
	 */
	public function set_frequency_type( string $frequency_type ): self {
		$this->frequency_type = $frequency_type;

		return $this;
	}

	/**
	 * Set the frequency display name.
	 *
	 * @param string $frequency_display_name The frequency display name.
	 *
	 * @return self
	 */
	public function set_frequency_display_name( string $frequency_display_name ): self {
		$this->frequency_display_name = $frequency_display_name;

		return $this;
	}

	/**
	 * Set the frequency value.
	 *
	 * @param int $frequency The frequency value.
	 *
	 * @return self
	 */
	public function set_frequency( int $frequency ): self {
		$this->frequency = $frequency;

		return $this;
	}

	/**
	 * Set the payment method ID.
	 *
	 * @param int $payment_method_id The payment method ID.
	 *
	 * @return self
	 */
	public function set_payment_method_id( int $payment_method_id ): self {
		$this->payment_method_id = $payment_method_id;

		return $this;
	}

	/**
	 * Set whether to authorize only.
	 *
	 * @param bool $authorize_only Whether to authorize only.
	 *
	 * @return self
	 */
	public function set_authorize_only( bool $authorize_only ): self {
		$this->authorize_only = $authorize_only;

		return $this;
	}

	/**
	 * Set the currency ISO code.
	 *
	 * @param string $currency_iso The currency ISO code.
	 *
	 * @return self
	 */
	public function set_currency_iso( string $currency_iso ): self {
		$this->currency_iso = $currency_iso;

		return $this;
	}

	/**
	 * Set the order subtotal.
	 *
	 * @param float $subtotal The order subtotal.
	 *
	 * @return self
	 */
	public function set_subtotal( float $subtotal ): self {
		$this->subtotal = $subtotal;

		return $this;
	}

	/**
	 * Set the shipping total.
	 *
	 * @param float $shipping_total The shipping total.
	 *
	 * @return self
	 */
	public function set_shipping_total( float $shipping_total ): self {
		$this->shipping_total = $shipping_total;

		return $this;
	}

	/**
	 * Set the tax total.
	 *
	 * @param float $tax_total The tax total.
	 *
	 * @return self
	 */
	public function set_tax_total( float $tax_total ): self {
		$this->tax_total = $tax_total;

		return $this;
	}

	/**
	 * Set the order total.
	 *
	 * @param float $total The order total.
	 *
	 * @return self
	 */
	public function set_total( float $total ): self {
		$this->total = $total;

		return $this;
	}

	/**
	 * Set the shipping address.
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
	public function set_shipping_address( string $first_name, string $last_name, string $street1, string $city, string $state, string $postcode, string $country, string $street2 = '' ): self {
		$this->shipping_first_name = $first_name;
		$this->shipping_last_name  = $last_name;
		$this->shipping_street1    = $street1;
		$this->shipping_street2    = $street2;
		$this->shipping_city       = $city;
		$this->shipping_state      = $state;
		$this->shipping_postcode   = $postcode;
		$this->shipping_country    = $country;

		return $this;
	}

	/**
	 * Set the scheduled order items.
	 *
	 * @param array $scheduled_order_items The scheduled order items.
	 *
	 * @return self
	 */
	public function set_scheduled_order_items( array $scheduled_order_items ): self {
		$this->scheduled_order_items = $scheduled_order_items;

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
		);

		if ( null !== $this->name ) {
			$data['name'] = $this->name;
		}

		if ( null !== $this->next_occurrence_utc ) {
			$data['nextOccurrenceUtc'] = $this->next_occurrence_utc;
		}

		if ( null !== $this->next_occurrence_offset ) {
			$data['nextOccurrenceOffset'] = $this->next_occurrence_offset;
		}

		if ( null !== $this->utc_offset ) {
			$data['utcOffset'] = $this->utc_offset;
		}

		if ( null !== $this->status ) {
			$data['status'] = $this->status;
		}

		if ( null !== $this->frequency_type ) {
			$data['frequencyType'] = $this->frequency_type;
		}

		if ( null !== $this->frequency_display_name ) {
			$data['frequencyDisplayName'] = $this->frequency_display_name;
		}

		if ( null !== $this->frequency ) {
			$data['frequency'] = $this->frequency;
		}

		if ( null !== $this->payment_method_id ) {
			$data['paymentMethodId'] = $this->payment_method_id;
		}

		if ( null !== $this->authorize_only ) {
			$data['authorizeOnly'] = $this->authorize_only;
		}

		if ( null !== $this->currency_iso ) {
			$data['currencyIso'] = $this->currency_iso;
		}

		if ( null !== $this->subtotal ) {
			$data['subtotal'] = $this->subtotal;
		}

		if ( null !== $this->shipping_total ) {
			$data['shippingTotal'] = $this->shipping_total;
		}

		if ( null !== $this->tax_total ) {
			$data['taxTotal'] = $this->tax_total;
		}

		if ( null !== $this->total ) {
			$data['total'] = $this->total;
		}

		if ( null !== $this->shipping_rate_name ) {
			$data['shippingRateName'] = $this->shipping_rate_name;
		}

		if ( null !== $this->shipping_first_name ) {
			$data['shippingFirstName'] = $this->shipping_first_name;
		}

		if ( null !== $this->shipping_last_name ) {
			$data['shippingLastName'] = $this->shipping_last_name;
		}

		if ( null !== $this->shipping_street1 ) {
			$data['shippingStreet1'] = $this->shipping_street1;
		}

		if ( null !== $this->shipping_street2 ) {
			$data['shippingStreet2'] = $this->shipping_street2;
		}

		if ( null !== $this->shipping_city ) {
			$data['shippingCity'] = $this->shipping_city;
		}

		if ( null !== $this->shipping_state ) {
			$data['shippingState'] = $this->shipping_state;
		}

		if ( null !== $this->shipping_postcode ) {
			$data['shippingPostcode'] = $this->shipping_postcode;
		}

		if ( null !== $this->shipping_country ) {
			$data['shippingCountry'] = $this->shipping_country;
		}

		if ( null !== $this->phone_number ) {
			$data['phoneNumber'] = $this->phone_number;
		}

		if ( null !== $this->company ) {
			$data['company'] = $this->company;
		}

		if ( null !== $this->original_external_id ) {
			$data['originalExternalId'] = $this->original_external_id;
		}

		if ( null !== $this->note ) {
			$data['note'] = $this->note;
		}

		if ( null !== $this->scheduled_order_items ) {
			$data['scheduledOrderItems'] = $this->scheduled_order_items;
		}

		if ( null !== $this->estimated_delivery_date ) {
			$data['estimatedDeliveryDate'] = $this->estimated_delivery_date;
		}

		return $data;
	}
}
