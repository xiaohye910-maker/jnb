<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CouponResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Coupons;

use stdClass;

/**
 * Response object for coupon data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CouponResponse {
	/**
	 * The coupon ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * The coupon code.
	 *
	 * @var string
	 */
	private string $code;

	/**
	 * The coupon name.
	 *
	 * @var string|null
	 */
	private ?string $name = null;

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
	 * The raw response data.
	 *
	 * @var stdClass
	 */
	private stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param stdClass $data The response data.
	 */
	public function __construct( stdClass $data ) {
		$this->raw_data        = $data;
		$this->id              = $data->id;
		$this->code            = $data->code;
		$this->name            = $data->name ?? null;
		$this->discount_type   = $data->discountType ?? null; // phpcs:ignore
		$this->amount          = $data->amount ?? null;
		$this->active          = $data->active ?? null;
		$this->is_stackable    = $data->isStackable ?? null; // phpcs:ignore
		$this->expiration_date = $data->expirationDate ?? null; // phpcs:ignore
	}

	/**
	 * Get the coupon ID.
	 *
	 * @return int The coupon ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the coupon code.
	 *
	 * @return string The coupon code.
	 */
	public function get_code(): string {
		return $this->code;
	}

	/**
	 * Get the coupon name.
	 *
	 * @return string|null The coupon name.
	 */
	public function get_name(): ?string {
		return $this->name;
	}

	/**
	 * Get the discount type.
	 *
	 * @return string|null The discount type.
	 */
	public function get_discount_type(): ?string {
		return $this->discount_type;
	}

	/**
	 * Get the discount amount.
	 *
	 * @return float|null The discount amount.
	 */
	public function get_amount(): ?float {
		return $this->amount;
	}

	/**
	 * Get whether the coupon is active.
	 *
	 * @return bool|null Whether the coupon is active.
	 */
	public function get_active(): ?bool {
		return $this->active;
	}

	/**
	 * Get whether the coupon is stackable.
	 *
	 * @return bool|null Whether the coupon is stackable.
	 */
	public function get_is_stackable(): ?bool {
		return $this->is_stackable;
	}

	/**
	 * Get the expiration date.
	 *
	 * @return string|null The expiration date.
	 */
	public function get_expiration_date(): ?string {
		return $this->expiration_date;
	}

	/**
	 * Get the raw response data.
	 *
	 * @return stdClass The raw response data.
	 */
	public function get_raw_data(): stdClass {
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
