<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * PaymentMethodResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Payments;

/**
 * Response object for payment method data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class PaymentMethodResponse {
	/**
	 * The payment method ID.
	 *
	 * @var int
	 */
	private int $id;

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
	 * The raw response data.
	 *
	 * @var \stdClass
	 */
	private \stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param \stdClass $data The response data.
	 */
	public function __construct( \stdClass $data ) {
		$this->raw_data            = $data;
		$this->id                  = $data->id;
		$this->customer_id         = $data->customerId; // phpcs:ignore
		$this->type                = $data->type;
		$this->last_four_digits    = $data->lastFourDigits ?? null; // phpcs:ignore
		$this->expiration          = $data->expiration ?? null;
		$this->gateway_customer_id = $data->gatewayCustomerId ?? null; // phpcs:ignore
		$this->gateway_payment_id  = $data->gatewayPaymentId ?? null; // phpcs:ignore
		$this->description         = $data->description ?? null;
		$this->billing_first_name  = $data->billingFirstName ?? null; // phpcs:ignore
		$this->billing_last_name   = $data->billingLastName ?? null; // phpcs:ignore
		$this->billing_street1     = $data->billingStreet1 ?? null; // phpcs:ignore
		$this->billing_street2     = $data->billingStreet2 ?? null; // phpcs:ignore
		$this->billing_city        = $data->billingCity ?? null; // phpcs:ignore
		$this->billing_state       = $data->billingState ?? null; // phpcs:ignore
		$this->billing_postcode    = $data->billingPostcode ?? null; // phpcs:ignore
		$this->billing_country     = $data->billingCountry ?? null; // phpcs:ignore
		$this->is_default          = $data->isDefault ?? null; // phpcs:ignore
	}

	/**
	 * Get the payment method ID.
	 *
	 * @return int The payment method ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the customer ID.
	 *
	 * @return int The customer ID.
	 */
	public function get_customer_id(): int {
		return $this->customer_id;
	}

	/**
	 * Get the payment gateway type name.
	 *
	 * @return string The payment gateway type name.
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get the last four digits of the card.
	 *
	 * @return string|null The last four digits of the card.
	 */
	public function get_last_four_digits(): ?string {
		return $this->last_four_digits;
	}

	/**
	 * Get the expiration date.
	 *
	 * @return string|null The expiration date.
	 */
	public function get_expiration(): ?string {
		return $this->expiration;
	}

	/**
	 * Get the payment gateway customer ID.
	 *
	 * @return string|null The payment gateway customer ID.
	 */
	public function get_gateway_customer_id(): ?string {
		return $this->gateway_customer_id;
	}

	/**
	 * Get the payment gateway token.
	 *
	 * @return string|null The payment gateway token.
	 */
	public function get_gateway_payment_id(): ?string {
		return $this->gateway_payment_id;
	}

	/**
	 * Get the payment description.
	 *
	 * @return string|null The payment description.
	 */
	public function get_description(): ?string {
		return $this->description;
	}

	/**
	 * Get the customer's billing first name.
	 *
	 * @return string|null The customer's billing first name.
	 */
	public function get_billing_first_name(): ?string {
		return $this->billing_first_name;
	}

	/**
	 * Get the customer's billing last name.
	 *
	 * @return string|null The customer's billing last name.
	 */
	public function get_billing_last_name(): ?string {
		return $this->billing_last_name;
	}

	/**
	 * Get the customer's billing street address line 1.
	 *
	 * @return string|null The customer's billing street address line 1.
	 */
	public function get_billing_street1(): ?string {
		return $this->billing_street1;
	}

	/**
	 * Get the customer's billing street address line 2.
	 *
	 * @return string|null The customer's billing street address line 2.
	 */
	public function get_billing_street2(): ?string {
		return $this->billing_street2;
	}

	/**
	 * Get the customer's billing city.
	 *
	 * @return string|null The customer's billing city.
	 */
	public function get_billing_city(): ?string {
		return $this->billing_city;
	}

	/**
	 * Get the customer's billing state.
	 *
	 * @return string|null The customer's billing state.
	 */
	public function get_billing_state(): ?string {
		return $this->billing_state;
	}

	/**
	 * Get the customer's billing postcode.
	 *
	 * @return string|null The customer's billing postcode.
	 */
	public function get_billing_postcode(): ?string {
		return $this->billing_postcode;
	}

	/**
	 * Get the customer's billing country.
	 *
	 * @return string|null The customer's billing country.
	 */
	public function get_billing_country(): ?string {
		return $this->billing_country;
	}

	/**
	 * Get whether this payment method is the default.
	 *
	 * @return bool|null Whether this payment method is the default.
	 */
	public function get_is_default(): ?bool {
		return $this->is_default;
	}

	/**
	 * Get the raw response data.
	 *
	 * @return \stdClass The raw response data.
	 */
	public function get_raw_data(): \stdClass {
		return $this->raw_data;
	}

	/**
	 * Convert the response to an array.
	 *
	 * @return array The response as an array.
	 */
	public function to_array(): array {
		return (array) $this->raw_data;
	}
}
