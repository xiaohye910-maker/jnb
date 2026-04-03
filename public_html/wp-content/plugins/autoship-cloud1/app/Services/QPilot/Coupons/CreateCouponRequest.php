<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CreateCouponRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Coupons;

/**
 * Request object for creating a coupon.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CreateCouponRequest {
	/**
	 * The coupon code.
	 *
	 * @var string
	 */
	private string $code;

	/**
	 * The coupon name.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * The discount type.
	 *
	 * @var string|null
	 */
	private ?string $discount_type = null;

	/**
	 * The discount amount.
	 *
	 * @var float|null
	 */
	private ?float $amount = null;

	/**
	 * Whether the coupon is active.
	 *
	 * @var bool|null
	 */
	private ?bool $active = null;

	/**
	 * Whether the coupon is stackable.
	 *
	 * @var bool|null
	 */
	private ?bool $is_stackable = null;

	/**
	 * The expiration date.
	 *
	 * @var string|null
	 */
	private ?string $expiration_date = null;

	/**
	 * Required shipping country for this coupon to be valid.
	 *
	 * @var string|null
	 */
	private ?string $country = null;

	/**
	 * Required shipping postcode for this coupon to be valid.
	 *
	 * @var string|null
	 */
	private ?string $postcode = null;

	/**
	 * Required shipping state for this coupon to be valid.
	 *
	 * @var string|null
	 */
	private ?string $state = null;

	/**
	 * Required shipping city for this coupon to be valid.
	 *
	 * @var string|null
	 */
	private ?string $city = null;

	/**
	 * Min # units required for this coupon to be valid.
	 *
	 * @var int|null
	 */
	private ?int $min_units = null;

	/**
	 * Max # units required for this coupon to be valid.
	 *
	 * @var int|null
	 */
	private ?int $max_units = null;

	/**
	 * Min weight required for this coupon to be valid.
	 *
	 * @var float|null
	 */
	private ?float $min_weight = null;

	/**
	 * Max weight required for this coupon to be valid.
	 *
	 * @var float|null
	 */
	private ?float $max_weight = null;

	/**
	 * The required weight unit type for this coupon to be valid.
	 *
	 * @var string|null
	 */
	private ?string $weight_unit_type = null;

	/**
	 * Total cycles this coupon will apply.
	 *
	 * @var int|null
	 */
	private ?int $cycles = null;

	/**
	 * Max scheduled order cycles this coupon can be used across a site.
	 *
	 * @var int|null
	 */
	private ?int $max_cycles_per_site = null;

	/**
	 * Min subtotal required for this coupon.
	 *
	 * @var float|null
	 */
	private ?float $min_subtotal = null;

	/**
	 * Max subtotal required for this coupon.
	 *
	 * @var float|null
	 */
	private ?float $max_subtotal = null;

	/**
	 * Max scheduled order cycles this coupon can be used by a customer.
	 *
	 * @var int|null
	 */
	private ?int $max_cycles_per_customer = null;

	/**
	 * Max number of times this coupon can be used by a customer.
	 *
	 * @var int|null
	 */
	private ?int $max_assignments_per_customer = null;

	/**
	 * Max number of times this coupon can be used across a site.
	 *
	 * @var int|null
	 */
	private ?int $max_assignments_per_site = null;

	/**
	 * Required min cycles for a scheduled order for coupon to be valid.
	 *
	 * @var int|null
	 */
	private ?int $min_scheduled_order_cycles = null;

	/**
	 * Required max cycles for a scheduled order for coupon to be valid.
	 *
	 * @var int|null
	 */
	private ?int $max_scheduled_order_cycles = null;

	/**
	 * Max scheduled order cycles a coupon will be valid.
	 *
	 * @var int|null
	 */
	private ?int $max_cycles_per_scheduled_order = null;

	/**
	 * Max discount $ allowed for a single customer.
	 *
	 * @var float|null
	 */
	private ?float $max_discount_per_customer = null;

	/**
	 * Sets the maximum amount that can be discounted by the percentage discount.
	 *
	 * @var float|null
	 */
	private ?float $max_percentage_discount = null;

	/**
	 * Constructor.
	 *
	 * @param string $code The coupon code.
	 * @param string $name The coupon name.
	 */
	public function __construct( string $code, string $name ) {
		$this->code = $code;
		$this->name = $name;
	}

	/**
	 * Set the discount type.
	 *
	 * @param string $discount_type The discount type.
	 * @return self
	 */
	public function set_discount_type( string $discount_type ): self {
		$this->discount_type = $discount_type;
		return $this;
	}

	/**
	 * Set the discount amount.
	 *
	 * @param float $amount The discount amount.
	 * @return self
	 */
	public function set_amount( float $amount ): self {
		$this->amount = $amount;
		return $this;
	}

	/**
	 * Set whether the coupon is active.
	 *
	 * @param bool $active Whether the coupon is active.
	 * @return self
	 */
	public function set_active( bool $active ): self {
		$this->active = $active;
		return $this;
	}

	/**
	 * Set whether the coupon is stackable.
	 *
	 * @param bool $is_stackable Whether the coupon is stackable.
	 * @return self
	 */
	public function set_is_stackable( bool $is_stackable ): self {
		$this->is_stackable = $is_stackable;
		return $this;
	}

	/**
	 * Set the expiration date.
	 *
	 * @param string $expiration_date The expiration date.
	 * @return self
	 */
	public function set_expiration_date( string $expiration_date ): self {
		$this->expiration_date = $expiration_date;
		return $this;
	}

	/**
	 * Set the shipping requirements.
	 *
	 * @param string|null $country The required shipping country.
	 * @param string|null $postcode The required shipping postcode.
	 * @param string|null $state The required shipping state.
	 * @param string|null $city The required shipping city.
	 * @return self
	 */
	public function set_shipping_requirements( ?string $country = null, ?string $postcode = null, ?string $state = null, ?string $city = null ): self {
		$this->country  = $country;
		$this->postcode = $postcode;
		$this->state    = $state;
		$this->city     = $city;
		return $this;
	}

	/**
	 * Set the unit requirements.
	 *
	 * @param int|null $min_units Min # units required.
	 * @param int|null $max_units Max # units required.
	 * @return self
	 */
	public function set_unit_requirements( ?int $min_units = null, ?int $max_units = null ): self {
		$this->min_units = $min_units;
		$this->max_units = $max_units;
		return $this;
	}

	/**
	 * Set the weight requirements.
	 *
	 * @param float|null  $min_weight Min weight required.
	 * @param float|null  $max_weight Max weight required.
	 * @param string|null $weight_unit_type The required weight unit type.
	 * @return self
	 */
	public function set_weight_requirements( ?float $min_weight = null, ?float $max_weight = null, ?string $weight_unit_type = null ): self {
		$this->min_weight       = $min_weight;
		$this->max_weight       = $max_weight;
		$this->weight_unit_type = $weight_unit_type;
		return $this;
	}

	/**
	 * Set the cycle requirements.
	 *
	 * @param int|null $cycles Total cycles this coupon will apply.
	 * @param int|null $max_cycles_per_site Max cycles across a site.
	 * @param int|null $max_cycles_per_customer Max cycles per customer.
	 * @param int|null $max_cycles_per_scheduled_order Max cycles per scheduled order.
	 * @return self
	 */
	public function set_cycle_requirements( ?int $cycles = null, ?int $max_cycles_per_site = null, ?int $max_cycles_per_customer = null, ?int $max_cycles_per_scheduled_order = null ): self {
		$this->cycles                         = $cycles;
		$this->max_cycles_per_site            = $max_cycles_per_site;
		$this->max_cycles_per_customer        = $max_cycles_per_customer;
		$this->max_cycles_per_scheduled_order = $max_cycles_per_scheduled_order;
		return $this;
	}

	/**
	 * Set the subtotal requirements.
	 *
	 * @param float|null $min_subtotal Min subtotal required.
	 * @param float|null $max_subtotal Max subtotal required.
	 * @return self
	 */
	public function set_subtotal_requirements( ?float $min_subtotal = null, ?float $max_subtotal = null ): self {
		$this->min_subtotal = $min_subtotal;
		$this->max_subtotal = $max_subtotal;
		return $this;
	}

	/**
	 * Set the assignment limits.
	 *
	 * @param int|null $max_assignments_per_customer Max uses per customer.
	 * @param int|null $max_assignments_per_site Max uses across a site.
	 * @return self
	 */
	public function set_assignment_limits( ?int $max_assignments_per_customer = null, ?int $max_assignments_per_site = null ): self {
		$this->max_assignments_per_customer = $max_assignments_per_customer;
		$this->max_assignments_per_site     = $max_assignments_per_site;
		return $this;
	}

	/**
	 * Set the scheduled order cycle requirements.
	 *
	 * @param int|null $min_scheduled_order_cycles Min cycles required.
	 * @param int|null $max_scheduled_order_cycles Max cycles required.
	 * @return self
	 */
	public function set_scheduled_order_cycle_requirements( ?int $min_scheduled_order_cycles = null, ?int $max_scheduled_order_cycles = null ): self {
		$this->min_scheduled_order_cycles = $min_scheduled_order_cycles;
		$this->max_scheduled_order_cycles = $max_scheduled_order_cycles;
		return $this;
	}

	/**
	 * Set the maximum discount per customer.
	 *
	 * @param float $max_discount_per_customer Max discount $ allowed for a single customer.
	 * @return self
	 */
	public function set_max_discount_per_customer( float $max_discount_per_customer ): self {
		$this->max_discount_per_customer = $max_discount_per_customer;
		return $this;
	}

	/**
	 * Set the maximum percentage discount.
	 *
	 * @param float $max_percentage_discount Max amount that can be discounted by percentage.
	 * @return self
	 */
	public function set_max_percentage_discount( float $max_percentage_discount ): self {
		$this->max_percentage_discount = $max_percentage_discount;
		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'code' => $this->code,
			'name' => $this->name,
		);

		if ( null !== $this->discount_type ) {
			$data['discountType'] = $this->discount_type;
		}

		if ( null !== $this->amount ) {
			$data['amount'] = $this->amount;
		}

		if ( null !== $this->active ) {
			$data['active'] = $this->active;
		}

		if ( null !== $this->is_stackable ) {
			$data['isStackable'] = $this->is_stackable;
		}

		if ( null !== $this->expiration_date ) {
			$data['expirationDate'] = $this->expiration_date;
		}

		if ( null !== $this->country ) {
			$data['country'] = $this->country;
		}

		if ( null !== $this->postcode ) {
			$data['postcode'] = $this->postcode;
		}

		if ( null !== $this->state ) {
			$data['state'] = $this->state;
		}

		if ( null !== $this->city ) {
			$data['city'] = $this->city;
		}

		if ( null !== $this->min_units ) {
			$data['minUnits'] = $this->min_units;
		}

		if ( null !== $this->max_units ) {
			$data['maxUnits'] = $this->max_units;
		}

		if ( null !== $this->min_weight ) {
			$data['minWeight'] = $this->min_weight;
		}

		if ( null !== $this->max_weight ) {
			$data['maxWeight'] = $this->max_weight;
		}

		if ( null !== $this->weight_unit_type ) {
			$data['weightUnitType'] = $this->weight_unit_type;
		}

		if ( null !== $this->cycles ) {
			$data['cycles'] = $this->cycles;
		}

		if ( null !== $this->max_cycles_per_site ) {
			$data['maxCyclesPerSite'] = $this->max_cycles_per_site;
		}

		if ( null !== $this->min_subtotal ) {
			$data['minSubtotal'] = $this->min_subtotal;
		}

		if ( null !== $this->max_subtotal ) {
			$data['maxSubtotal'] = $this->max_subtotal;
		}

		if ( null !== $this->max_cycles_per_customer ) {
			$data['maxCyclesPerCustomer'] = $this->max_cycles_per_customer;
		}

		if ( null !== $this->max_assignments_per_customer ) {
			$data['maxAssignmentsPerCustomer'] = $this->max_assignments_per_customer;
		}

		if ( null !== $this->max_assignments_per_site ) {
			$data['maxAssignmentsPerSite'] = $this->max_assignments_per_site;
		}

		if ( null !== $this->min_scheduled_order_cycles ) {
			$data['minScheduledOrderCycles'] = $this->min_scheduled_order_cycles;
		}

		if ( null !== $this->max_scheduled_order_cycles ) {
			$data['maxScheduledOrderCycles'] = $this->max_scheduled_order_cycles;
		}

		if ( null !== $this->max_cycles_per_scheduled_order ) {
			$data['maxCyclesPerScheduledOrder'] = $this->max_cycles_per_scheduled_order;
		}

		if ( null !== $this->max_discount_per_customer ) {
			$data['maxDiscountPerCustomer'] = $this->max_discount_per_customer;
		}

		if ( null !== $this->max_percentage_discount ) {
			$data['maxPercentageDiscount'] = $this->max_percentage_discount;
		}

		return $data;
	}
}
