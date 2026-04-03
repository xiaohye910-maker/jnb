<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Shipping Options Request.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Carriers;

/**
 * The Nextime Shipping Options Request.
 */
class ShippingOptionsRequest {

	/**
	 * The order id for the request.
	 *
	 * @var string
	 */
	private string $order_id;

	/**
	 * Contains the order items.
	 *
	 * @var array<ShippingOptionsRequestItem>
	 */
	private array $items;

	/**
	 * The street address.
	 *
	 * @var string
	 */
	private string $street;

	/**
	 * The second street address.
	 *
	 * @var string
	 */
	private string $street2;

	/**
	 * The city.
	 *
	 * @var string
	 */
	private string $city;

	/**
	 * The state.
	 *
	 * @var string
	 */
	private string $state;

	/**
	 * The country.
	 *
	 * @var string
	 */
	private string $country;

	/**
	 * The postal code.
	 *
	 * @var string
	 */
	private string $postal;

	/**
	 * The default cost.
	 *
	 * @var float
	 */
	private float $default_cost;

	/**
	 * Constructor.
	 *
	 * @param string $order_id The order id.
	 * @param string $postal The postal code.
	 * @param string $country The country.
	 * @param float  $default_cost The default cost.
	 */
	public function __construct( string $order_id, string $postal = '', string $country = '', float $default_cost = 0.0 ) {
		$this->order_id     = $order_id;
		$this->postal       = $postal;
		$this->country      = $country;
		$this->default_cost = $default_cost;
		$this->street       = '';
		$this->street2      = '';
		$this->city         = '';
		$this->state        = '';
		$this->items        = array();
	}

	/**
	 * Gets the order id.
	 *
	 * @return string
	 */
	public function get_order_id(): string {
		return $this->order_id;
	}

	/**
	 * Set the street address.
	 *
	 * @param string $street The street address.
	 */
	public function set_street( string $street ): void {
		$this->street = $street;
	}

	/**
	 * Sets street address 2.
	 *
	 * @param string $street2 Street address 2.
	 */
	public function set_street2( string $street2 ): void {
		$this->street2 = $street2;
	}

	/**
	 * Sets the city.
	 *
	 * @param string $city The city.
	 */
	public function set_city( string $city ): void {
		$this->city = $city;
	}

	/**
	 * Sets the state.
	 *
	 * @param string $state The state.
	 */
	public function set_state( string $state ): void {
		$this->state = $state;
	}

	/**
	 * Gets the default cost.
	 *
	 * @return float
	 */
	public function get_default_cost(): float {
		return $this->default_cost;
	}

	/**
	 * Gets the street address.
	 *
	 * @return string
	 */
	public function get_street(): string {
		return $this->street;
	}

	/**
	 * Gets the second street address.
	 *
	 * @return string
	 */
	public function get_street_2(): string {
		return $this->street2;
	}

	/**
	 * Gets the country.
	 *
	 * @return string
	 */
	public function get_country(): string {
		return $this->country;
	}

	/**
	 * Gets the state.
	 *
	 * @return string
	 */
	public function get_state(): string {
		return $this->state;
	}

	/**
	 * Gets the city.
	 *
	 * @return string
	 */
	public function get_city(): string {
		return $this->city;
	}

	/**
	 * Gets the postal code.
	 *
	 * @return string
	 */
	public function get_postal_code(): string {
		return $this->postal;
	}

	/**
	 * Adds an item.
	 *
	 * @param ShippingOptionsRequestItem $item The item to add.
	 *
	 * @return void
	 */
	public function add_item( ShippingOptionsRequestItem $item ): void {
		$this->items[] = $item;
	}

	/**
	 * Gets the items.
	 *
	 * @return array|ShippingOptionsRequestItem[]
	 */
	public function get_items(): array {
		return $this->items;
	}
}
