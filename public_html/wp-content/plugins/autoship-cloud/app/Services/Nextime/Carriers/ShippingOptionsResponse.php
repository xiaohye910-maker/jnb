<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Options Response.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Carriers;

use Autoship\Domain\Nextime\ShippingRate;

/**
 * Represents the response from the Nextime shipping options API.
 */
class ShippingOptionsResponse {

	/**
	 * The shipping rate.
	 *
	 * @var ?ShippingRate
	 */
	private ?ShippingRate $shipping_rate;

	/**
	 * Add a shipping rate.
	 *
	 * @param ShippingRate $shipping_rate The shipping rate to add.
	 *
	 * @return void
	 */
	public function set_shipping_rate( ShippingRate $shipping_rate ) {
		$this->shipping_rate = $shipping_rate;
	}

	/**
	 * Get the shipping rate of the response.
	 *
	 * @return ?ShippingRate
	 */
	public function get_shipping_rate(): ?ShippingRate {
		return $this->shipping_rate;
	}
}
