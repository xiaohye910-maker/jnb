<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Options
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Domain\Nextime;

/**
 * Defines the Nextime Shipping Options.
 */
class ShippingOptions {

	/**
	 * The mode.
	 *
	 * @var string
	 */
	private string $mode;

	/**
	 * The recommended delivery date.
	 *
	 * @var DeliveryDate|null
	 */
	private ?DeliveryDate $recommended_delivery_date;

	/**
	 * The delivery dates.
	 *
	 * @var array<DeliveryDate>
	 */
	private array $delivery_dates;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->delivery_dates = array();
	}

	/**
	 * Sets the mode.
	 *
	 * @param string $mode The mode.
	 *
	 * @return void
	 */
	public function set_mode( string $mode ): void {
		$this->mode = $mode;
	}

	/**
	 * Gets the mode.
	 *
	 * @return string
	 */
	public function get_mode(): string {
		return $this->mode;
	}

	/**
	 * Sets the recommended delivery date.
	 *
	 * @param DeliveryDate $delivery_date The recommended delivery date.
	 *
	 * @return void
	 */
	public function set_recommended_delivery_date( DeliveryDate $delivery_date ): void {
		$this->recommended_delivery_date = $delivery_date;
	}

	/**
	 * Gets the recommended delivery date.
	 *
	 * @return DeliveryDate|null
	 */
	public function get_recommended_delivery_date(): ?DeliveryDate {
		return $this->recommended_delivery_date;
	}

	/**
	 * Gets the delivery dates.
	 *
	 * @return array<DeliveryDate>
	 */
	public function get_delivery_dates(): array {
		return $this->delivery_dates;
	}

	/**
	 * Adds a delivery date.
	 *
	 * @param DeliveryDate $delivery_date The delivery date.
	 *
	 * @return void
	 */
	public function add_delivery_date( DeliveryDate $delivery_date ): void {
		$this->delivery_dates[] = $delivery_date;
	}
}
