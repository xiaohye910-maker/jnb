<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Rate Service Code.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Domain\Nextime;

/**
 * Represents the shipping rate service code.
 */
class ShippingRateServiceCode {

	/**
	 * The delivery date.
	 *
	 * @var string|null
	 */
	private ?string $delivery_date;

	/**
	 * The description.
	 *
	 * @var string|null
	 */
	private ?string $description;

	/**
	 * The charge date.
	 *
	 * @var string|null
	 */
	private ?string $charge_date;

	/**
	 * The ship date.
	 *
	 * @var string|null
	 */
	private ?string $ship_date;

	/**
	 * The total.
	 *
	 * @var float|null
	 */
	private ?float $total;

	/**
	 * The name
	 *
	 * @var string|null
	 */
	private ?string $name;

	/**
	 * The shipping method
	 *
	 * @var string|null
	 */
	private ?string $shipping_method;

	/**
	 * The position.
	 *
	 * @var int|null
	 */
	private ?int $position;

	/**
	 * Gets the name.
	 *
	 * @return string|null
	 */
	public function get_name(): ?string {
		return $this->name;
	}

	/**
	 * Sets the name.
	 *
	 * @param string $name The name of the service code.
	 *
	 * @return void
	 */
	public function set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Gets the total.
	 *
	 * @return float|null
	 */
	public function get_total(): ?float {
		return $this->total;
	}

	/**
	 * Sets the total.
	 *
	 * @param float $total The total of the service code.
	 *
	 * @return void
	 */
	public function set_total( float $total ): void {
		$this->total = $total;
	}

	/**
	 * Gets the shipping method.
	 *
	 * @return string|null
	 */
	public function get_shipping_method(): ?string {
		return $this->shipping_method;
	}

	/**
	 * Sets the shipping method.
	 *
	 * @param string $shipping_method The shipping method of the service code.
	 *
	 * @return void
	 */
	public function set_shipping_method( string $shipping_method ): void {
		$this->shipping_method = $shipping_method;
	}

	/**
	 * Gets the description.
	 *
	 * @return string|null
	 */
	public function get_description(): ?string {
		return $this->description;
	}

	/**
	 * Sets the description.
	 *
	 * @param string $description The description of the service code.
	 *
	 * @return void
	 */
	public function set_description( string $description ): void {
		$this->description = $description;
	}

	/**
	 * Gets the position.
	 *
	 * @return int|null
	 */
	public function get_position(): ?int {
		return $this->position;
	}

	/**
	 * Sets the position.
	 *
	 * @param int $position The position of the service code.
	 *
	 * @return void
	 */
	public function set_position( int $position ): void {
		$this->position = $position;
	}

	/**
	 * Gets the charge date.
	 *
	 * @return string|null
	 */
	public function get_charge_date(): ?string {
		return $this->charge_date;
	}

	/**
	 * Sets the charge date.
	 *
	 * @param string $charge_date The charge date of the service code.
	 *
	 * @return void
	 */
	public function set_charge_date( string $charge_date ): void {
		$this->charge_date = $charge_date;
	}

	/**
	 * Gets the ship date.
	 *
	 * @return string|null
	 */
	public function get_ship_date(): ?string {
		return $this->ship_date;
	}

	/**
	 * Sets the ship date.
	 *
	 * @param string $ship_date The ship date of the service code.
	 *
	 * @return void
	 */
	public function set_ship_date( string $ship_date ): void {
		$this->ship_date = $ship_date;
	}

	/**
	 * Gets the delivery date.
	 *
	 * @return string|null
	 */
	public function get_delivery_date(): ?string {
		return $this->delivery_date;
	}

	/**
	 * Sets the delivery date.
	 *
	 * @param string $delivery_date The delivery date of the service code.
	 *
	 * @return void
	 */
	public function set_delivery_date( string $delivery_date ): void {
		$this->delivery_date = $delivery_date;
	}
}
