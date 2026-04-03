<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Line
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Domain\Nextime;

/**
 * Represents the shipping line.
 */
class ShippingLine {

	/**
	 * The name of the shipping line.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * The total of the shipping lines.
	 *
	 * @var float
	 */
	private float $total;

	/**
	 * The integration provider.
	 *
	 * @var string
	 */
	private string $integration_provider;

	/**
	 * The shipping method.
	 *
	 * @var string
	 */
	private string $shipping_method;

	/**
	 * The next order date.
	 *
	 * @var string
	 */
	private string $next_order_date;

	/**
	 * The next shipping date.
	 *
	 * @var string
	 */
	private string $next_shipping_date;

	/**
	 * The lead time in hours.
	 *
	 * @var int
	 */
	private int $lead_time_in_hours;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name                 = '';
		$this->total                = 0.0;
		$this->integration_provider = '';
		$this->shipping_method      = '';
		$this->next_order_date      = '';
		$this->next_shipping_date   = '';
		$this->lead_time_in_hours   = 0;
	}

	/**
	 * Gets the name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Sets the name.
	 *
	 * @param string $name The name.
	 *
	 * @return void
	 */
	public function set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Gets the total.
	 *
	 * @return float
	 */
	public function get_total(): float {
		return $this->total;
	}

	/**
	 * Sets the total.
	 *
	 * @param float $total The total.
	 *
	 * @return void
	 */
	public function set_total( float $total ): void {
		$this->total = $total;
	}

	/**
	 * Gets the integration provider.
	 *
	 * @return string
	 */
	public function get_integration_provider(): string {
		return $this->integration_provider;
	}

	/**
	 * Sets the integration provider.
	 *
	 * @param string $integration_provider The integration provider.
	 *
	 * @return void
	 */
	public function set_integration_provider( string $integration_provider ): void {
		$this->integration_provider = $integration_provider;
	}

	/**
	 * Gets the shipping method.
	 *
	 * @return string
	 */
	public function get_shipping_method(): string {
		return $this->shipping_method;
	}

	/**
	 * Sets the shipping method.
	 *
	 * @param string $shipping_method The shipping method.
	 *
	 * @return void
	 */
	public function set_shipping_method( string $shipping_method ): void {
		$this->shipping_method = $shipping_method;
	}

	/**
	 * Gets the next order date.
	 *
	 * @return string
	 */
	public function get_next_order_date(): string {
		return $this->next_order_date;
	}

	/**
	 * Sets the next order date.
	 *
	 * @param string $next_order_date The next order date.
	 *
	 * @return void
	 */
	public function set_next_order_date( string $next_order_date ): void {
		$this->next_order_date = $next_order_date;
	}

	/**
	 * Gets the next shipping date.
	 *
	 * @return string
	 */
	public function get_next_shipping_date(): string {
		return $this->next_shipping_date;
	}

	/**
	 * Sets the next shipping date.
	 *
	 * @param string $next_shipping_date The next shipping date.
	 *
	 * @return void
	 */
	public function set_next_shipping_date( string $next_shipping_date ): void {
		$this->next_shipping_date = $next_shipping_date;
	}

	/**
	 * Gets the lead time in number of hours.
	 *
	 * @return int
	 */
	public function get_lead_time_in_hours(): int {
		return $this->lead_time_in_hours;
	}

	/**
	 * Sets the lead time in number of hours.
	 *
	 * @param int $lead_time_in_hours The lead time in hours.
	 *
	 * @return void
	 */
	public function set_lead_time_in_hours( int $lead_time_in_hours ): void {
		$this->lead_time_in_hours = $lead_time_in_hours;
	}
}
