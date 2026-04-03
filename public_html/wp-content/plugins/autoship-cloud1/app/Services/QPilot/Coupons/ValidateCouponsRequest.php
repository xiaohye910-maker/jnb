<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * ValidateCouponsRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Coupons;

/**
 * Request object for validating coupons.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class ValidateCouponsRequest {
	/**
	 * The coupon codes to validate.
	 *
	 * @var array<string>
	 */
	private array $coupon_codes;

	/**
	 * Constructor.
	 *
	 * @param array<string> $coupon_codes The coupon codes to validate.
	 */
	public function __construct( array $coupon_codes = array() ) {
		$this->coupon_codes = $coupon_codes;
	}

	/**
	 * Add a coupon code to validate.
	 *
	 * @param string $coupon_code The coupon code to add.
	 * @return self
	 */
	public function add_coupon_code( string $coupon_code ): self {
		$this->coupon_codes[] = $coupon_code;
		return $this;
	}

	/**
	 * Set the coupon codes to validate.
	 *
	 * @param array<string> $coupon_codes The coupon codes to validate.
	 * @return self
	 */
	public function set_coupon_codes( array $coupon_codes ): self {
		$this->coupon_codes = $coupon_codes;
		return $this;
	}

	/**
	 * Get the coupon codes to validate.
	 *
	 * @return array<string> The coupon codes to validate.
	 */
	public function get_coupon_codes(): array {
		return $this->coupon_codes;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		return array(
			'coupons' => implode( ',', $this->coupon_codes ),
		);
	}
}
