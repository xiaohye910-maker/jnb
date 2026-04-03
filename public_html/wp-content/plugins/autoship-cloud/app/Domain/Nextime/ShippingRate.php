<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Rate
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Domain\Nextime;

/**
 * Defines the Nextime Shipping Rate.
 */
class ShippingRate {
	/**
	 * The value that indicates whether the shipping rate was successful.
	 *
	 * @var bool
	 */
	private bool $succeeded;

	/**
	 * The shipping options.
	 *
	 * @var ShippingOptions|null
	 */
	private ?ShippingOptions $shipping_options;

	/**
	 * The errors of the shipping rate.
	 *
	 * @var array
	 */
	private array $errors;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->succeeded        = false;
		$this->shipping_options = null;
	}

	/**
	 * Gets the shipping options.
	 *
	 * @return ShippingOptions
	 */
	public function get_shipping_options(): ?ShippingOptions {
		return $this->shipping_options;
	}

	/**
	 * Sets the shipping options.
	 *
	 * @param ShippingOptions $shipping_options The shipping options.
	 *
	 * @return void
	 */
	public function set_shipping_options( ShippingOptions $shipping_options ): void {
		$this->shipping_options = $shipping_options;
	}

	/**
	 * Gets the value that indicates whether the shipping rate was successful.
	 *
	 * @return bool
	 */
	public function get_succeeded(): bool {
		return $this->succeeded;
	}

	/**
	 * Sets the value that indicates whether the shipping rate was successful.
	 *
	 * @param bool $succeeded The value that indicates whether the shipping rate was successful.
	 *
	 * @return void
	 */
	public function set_succeeded( bool $succeeded ) {
		$this->succeeded = $succeeded;
	}

	/**
	 * Gets the errors of the shipping rate.
	 *
	 * @return array
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Sets the errors of the shipping rate.
	 *
	 * @param array $errors The errors of the shipping rate.
	 *
	 * @return void
	 */
	public function set_errors( array $errors ) {
		$this->errors = $errors;
	}
}
