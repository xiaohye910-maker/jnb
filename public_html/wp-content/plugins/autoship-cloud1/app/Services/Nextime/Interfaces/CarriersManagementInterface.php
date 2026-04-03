<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Sites Management Interface.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Interfaces;

use Autoship\Services\Nextime\Carriers\ShippingOptionsRequest;
use Autoship\Services\Nextime\Carriers\ShippingOptionsResponse;

interface CarriersManagementInterface {

	/**
	 * Gets the shipping options for the given request.
	 *
	 * @param ShippingOptionsRequest $request The shipping options request.
	 *
	 * @return ShippingOptionsResponse The shipping options response.
	 */
	public function get_shipping_options( ShippingOptionsRequest $request ): ShippingOptionsResponse;
}
