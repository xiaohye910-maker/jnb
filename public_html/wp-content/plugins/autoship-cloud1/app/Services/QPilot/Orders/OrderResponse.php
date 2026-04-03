<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * OrderResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Response object for order data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class OrderResponse {
	/**
	 * The order ID.
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
	 * The origin of the order.
	 *
	 * @var string|null
	 */
	private ?string $origin = null;

	/**
	 * The applied coupon codes.
	 *
	 * @var array|null
	 */
	private ?array $coupons = null;

	/**
	 * Array of key-value pairs of metadata.
	 *
	 * @var array|null
	 */
	private ?array $metadata = null;

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
		$this->raw_data                = $data;
		$this->id                      = $data->id;
		$this->customer_id             = $data->customerId; // phpcs:ignore
		$this->name                    = $data->name ?? null;
		$this->next_occurrence_utc     = $data->nextOccurrenceUtc ?? null; // phpcs:ignore
		$this->next_occurrence_offset  = $data->nextOccurrenceOffset ?? null; // phpcs:ignore
		$this->utc_offset              = $data->utcOffset ?? null; // phpcs:ignore
		$this->status                  = $data->status ?? null;
		$this->frequency_type          = $data->frequencyType ?? null; // phpcs:ignore
		$this->frequency_display_name  = $data->frequencyDisplayName ?? null; // phpcs:ignore
		$this->frequency               = $data->frequency ?? null;
		$this->payment_method_id       = $data->paymentMethodId ?? null; // phpcs:ignore
		$this->authorize_only          = $data->authorizeOnly ?? null; // phpcs:ignore
		$this->currency_iso            = $data->currencyIso ?? null; // phpcs:ignore
		$this->subtotal                = $data->subtotal ?? null;
		$this->shipping_total          = $data->shippingTotal ?? null; // phpcs:ignore
		$this->tax_total               = $data->taxTotal ?? null; // phpcs:ignore
		$this->total                   = $data->total ?? null;
		$this->shipping_rate_name      = $data->shippingRateName ?? null; // phpcs:ignore
		$this->shipping_first_name     = $data->shippingFirstName ?? null; // phpcs:ignore
		$this->shipping_last_name      = $data->shippingLastName ?? null; // phpcs:ignore
		$this->shipping_street1        = $data->shippingStreet1 ?? null; // phpcs:ignore
		$this->shipping_street2        = $data->shippingStreet2 ?? null; // phpcs:ignore
		$this->shipping_city           = $data->shippingCity ?? null; // phpcs:ignore
		$this->shipping_state          = $data->shippingState ?? null; // phpcs:ignore
		$this->shipping_postcode       = $data->shippingPostcode ?? null; // phpcs:ignore
		$this->shipping_country        = $data->shippingCountry ?? null; // phpcs:ignore
		$this->phone_number            = $data->phoneNumber ?? null; // phpcs:ignore
		$this->company                 = $data->company ?? null;
		$this->original_external_id    = $data->originalExternalId ?? null; // phpcs:ignore
		$this->note                    = $data->note ?? null;
		$this->scheduled_order_items   = $data->scheduledOrderItems ?? null; // phpcs:ignore
		$this->estimated_delivery_date = $data->estimatedDeliveryDate ?? null; // phpcs:ignore
		$this->origin                  = $data->origin ?? null;
		$this->coupons                 = $data->coupons ?? null;
		$this->metadata                = $data->metadata ?? null;
	}

	/**
	 * Get the order ID.
	 *
	 * @return int The order ID.
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
	 * Get the order name.
	 *
	 * @return string|null The order name.
	 */
	public function get_name(): ?string {
		return $this->name;
	}

	/**
	 * Get the next occurrence date in UTC.
	 *
	 * @return string|null The next occurrence date in UTC.
	 */
	public function get_next_occurrence_utc(): ?string {
		return $this->next_occurrence_utc;
	}

	/**
	 * Get the next occurrence offset.
	 *
	 * @return string|null The next occurrence offset.
	 */
	public function get_next_occurrence_offset(): ?string {
		return $this->next_occurrence_offset;
	}

	/**
	 * Get the UTC offset.
	 *
	 * @return int|null The UTC offset.
	 */
	public function get_utc_offset(): ?int {
		return $this->utc_offset;
	}

	/**
	 * Get the order status.
	 *
	 * @return string|null The order status.
	 */
	public function get_status(): ?string {
		return $this->status;
	}

	/**
	 * Get the frequency type.
	 *
	 * @return string|null The frequency type.
	 */
	public function get_frequency_type(): ?string {
		return $this->frequency_type;
	}

	/**
	 * Get the frequency display name.
	 *
	 * @return string|null The frequency display name.
	 */
	public function get_frequency_display_name(): ?string {
		return $this->frequency_display_name;
	}

	/**
	 * Get the frequency value.
	 *
	 * @return int|null The frequency value.
	 */
	public function get_frequency(): ?int {
		return $this->frequency;
	}

	/**
	 * Get the payment method ID.
	 *
	 * @return int|null The payment method ID.
	 */
	public function get_payment_method_id(): ?int {
		return $this->payment_method_id;
	}

	/**
	 * Get whether to authorize only.
	 *
	 * @return bool|null Whether to authorize only.
	 */
	public function get_authorize_only(): ?bool {
		return $this->authorize_only;
	}

	/**
	 * Get the currency ISO code.
	 *
	 * @return string|null The currency ISO code.
	 */
	public function get_currency_iso(): ?string {
		return $this->currency_iso;
	}

	/**
	 * Get the order subtotal.
	 *
	 * @return float|null The order subtotal.
	 */
	public function get_subtotal(): ?float {
		return $this->subtotal;
	}

	/**
	 * Get the shipping total.
	 *
	 * @return float|null The shipping total.
	 */
	public function get_shipping_total(): ?float {
		return $this->shipping_total;
	}

	/**
	 * Get the tax total.
	 *
	 * @return float|null The tax total.
	 */
	public function get_tax_total(): ?float {
		return $this->tax_total;
	}

	/**
	 * Get the order total.
	 *
	 * @return float|null The order total.
	 */
	public function get_total(): ?float {
		return $this->total;
	}

	/**
	 * Get the shipping rate name.
	 *
	 * @return string|null The shipping rate name.
	 */
	public function get_shipping_rate_name(): ?string {
		return $this->shipping_rate_name;
	}

	/**
	 * Get the shipping first name.
	 *
	 * @return string|null The shipping first name.
	 */
	public function get_shipping_first_name(): ?string {
		return $this->shipping_first_name;
	}

	/**
	 * Get the shipping last name.
	 *
	 * @return string|null The shipping last name.
	 */
	public function get_shipping_last_name(): ?string {
		return $this->shipping_last_name;
	}

	/**
	 * Get the shipping street address line 1.
	 *
	 * @return string|null The shipping street address line 1.
	 */
	public function get_shipping_street1(): ?string {
		return $this->shipping_street1;
	}

	/**
	 * Get the shipping street address line 2.
	 *
	 * @return string|null The shipping street address line 2.
	 */
	public function get_shipping_street2(): ?string {
		return $this->shipping_street2;
	}

	/**
	 * Get the shipping city.
	 *
	 * @return string|null The shipping city.
	 */
	public function get_shipping_city(): ?string {
		return $this->shipping_city;
	}

	/**
	 * Get the shipping state.
	 *
	 * @return string|null The shipping state.
	 */
	public function get_shipping_state(): ?string {
		return $this->shipping_state;
	}

	/**
	 * Get the shipping postcode.
	 *
	 * @return string|null The shipping postcode.
	 */
	public function get_shipping_postcode(): ?string {
		return $this->shipping_postcode;
	}

	/**
	 * Get the shipping country.
	 *
	 * @return string|null The shipping country.
	 */
	public function get_shipping_country(): ?string {
		return $this->shipping_country;
	}

	/**
	 * Get the phone number.
	 *
	 * @return string|null The phone number.
	 */
	public function get_phone_number(): ?string {
		return $this->phone_number;
	}

	/**
	 * Get the company name.
	 *
	 * @return string|null The company name.
	 */
	public function get_company(): ?string {
		return $this->company;
	}

	/**
	 * Get the original external ID.
	 *
	 * @return string|null The original external ID.
	 */
	public function get_original_external_id(): ?string {
		return $this->original_external_id;
	}

	/**
	 * Get the order note.
	 *
	 * @return string|null The order note.
	 */
	public function get_note(): ?string {
		return $this->note;
	}

	/**
	 * Get the scheduled order items.
	 *
	 * @return array|null The scheduled order items.
	 */
	public function get_scheduled_order_items(): ?array {
		return $this->scheduled_order_items;
	}

	/**
	 * Get the estimated delivery date.
	 *
	 * @return string|null The estimated delivery date.
	 */
	public function get_estimated_delivery_date(): ?string {
		return $this->estimated_delivery_date;
	}

	/**
	 * Get the origin of the order.
	 *
	 * @return string|null The origin of the order.
	 */
	public function get_origin(): ?string {
		return $this->origin;
	}

	/**
	 * Get the applied coupon codes.
	 *
	 * @return array|null The applied coupon codes.
	 */
	public function get_coupons(): ?array {
		return $this->coupons;
	}

	/**
	 * Get the metadata for the order.
	 *
	 * @return array|null The metadata for the order.
	 */
	public function get_metadata(): ?array {
		return $this->metadata;
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
