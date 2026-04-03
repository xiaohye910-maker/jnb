<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CouponValidationResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Coupons;

use stdClass;

/**
 * Response object for coupon validation.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CouponValidationResponse {
	/**
	 * The valid coupons.
	 *
	 * @var array<CouponResponse>
	 */
	private array $valid_coupons;

	/**
	 * The invalid coupon codes.
	 *
	 * @var array<string>
	 */
	private array $invalid_coupon_codes;

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
		$this->raw_data             = $data;
		$this->valid_coupons        = array();
		$this->invalid_coupon_codes = array();

		// Process the response data to extract valid and invalid coupons.
		if ( isset( $data->items ) && is_array( $data->items ) ) {
			foreach ( $data->items as $item ) {
				$this->valid_coupons[] = new CouponResponse( $item );
			}
		}

		// If there's an invalidCoupons property, extract the invalid coupon codes.
		if ( isset( $data->invalidCoupons ) && is_array( $data->invalidCoupons ) ) { // phpcs:ignore
			$this->invalid_coupon_codes = $data->invalidCoupons; // phpcs:ignore
		}
	}

	/**
	 * Get the valid coupons.
	 *
	 * @return array<CouponResponse> The valid coupons.
	 */
	public function get_valid_coupons(): array {
		return $this->valid_coupons;
	}

	/**
	 * Get the invalid coupon codes.
	 *
	 * @return array<string> The invalid coupon codes.
	 */
	public function get_invalid_coupon_codes(): array {
		return $this->invalid_coupon_codes;
	}

	/**
	 * Check if a specific coupon code is valid.
	 *
	 * @param string $code The coupon code to check.
	 * @return bool True if the coupon code is valid, false otherwise.
	 */
	public function is_coupon_valid( string $code ): bool {
		foreach ( $this->valid_coupons as $coupon ) {
			if ( $coupon->get_code() === $code ) {
				return true;
			}
		}
		return false;
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
