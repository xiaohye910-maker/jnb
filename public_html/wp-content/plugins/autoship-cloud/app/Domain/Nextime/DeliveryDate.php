<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Delivery Date
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Domain\Nextime;

use DateTimeImmutable;
use Exception;

/**
 * Defines the Nextime Shipping Delivery Date.
 */
class DeliveryDate {

	/**
	 * The external id.
	 *
	 * @var string
	 */
	private string $external_id;

	/**
	 * The delivery date.
	 *
	 * @var string
	 */
	private string $delivery_date;

	/**
	 * The shipping cutoff date.
	 *
	 * @var string
	 */
	private string $shipping_cutoff_date;

	/**
	 * The shipping lines.
	 *
	 * @var array<ShippingLine>
	 */
	private array $shipping_lines;

	/**
	 * Indicates if the delivery date is considered as a secondary cutoff.
	 *
	 * @var bool
	 */
	private bool $considered_secondary_cutoff;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->external_id                 = '';
		$this->delivery_date               = '';
		$this->shipping_cutoff_date        = '';
		$this->considered_secondary_cutoff = false;
		$this->shipping_lines              = array();
	}

	/**
	 * Gets the external id.
	 *
	 * @return string
	 */
	public function get_external_id(): string {
		return $this->external_id;
	}

	/**
	 * Sets the external id.
	 *
	 * @param string $external_id The external id.
	 *
	 * @return void
	 */
	public function set_external_id( string $external_id ): void {
		$this->external_id = $external_id;
	}

	/**
	 * Gets the delivery date.
	 *
	 * @return string
	 */
	public function get_delivery_date(): string {
		return $this->delivery_date;
	}

	/**
	 * Gets the delivery date formatted.
	 *
	 * @param string $format The format of the delivery date.
	 *
	 * @return string
	 */
	public function get_formatted_delivery_date( string $format = 'l, F dS Y' ): string {
		if ( empty( $this->delivery_date ) ) {
			return '';
		}

		try {
			$dt = new DateTimeImmutable( $this->delivery_date );
			return $dt->format( $format );
		} catch ( Exception $e ) {
			return '';
		}
	}

	/**
	 * Sets the delivery date.
	 *
	 * @param string $delivery_date The delivery date.
	 *
	 * @return void
	 */
	public function set_delivery_date( string $delivery_date ): void {
		$this->delivery_date = $delivery_date;
	}

	/**
	 * Gets the shipping cutoff date.
	 *
	 * @return string
	 */
	public function get_shipping_cutoff_date(): string {
		return $this->shipping_cutoff_date;
	}

	/**
	 * Sets the shipping cutoff date.
	 *
	 * @param string $shipping_cutoff_date The shipping cutoff date.
	 *
	 * @return void
	 */
	public function set_shipping_cutoff_date( string $shipping_cutoff_date ): void {
		$this->shipping_cutoff_date = $shipping_cutoff_date;
	}

	/**
	 * Adds a shipping line.
	 *
	 * @param ShippingLine $shipping_line The shipping line.
	 *
	 * @return void
	 */
	public function add_shipping_line( ShippingLine $shipping_line ): void {
		$this->shipping_lines[] = $shipping_line;
	}

	/**
	 * Gets the shipping lines.
	 *
	 * @return array|ShippingLine[]
	 */
	public function get_shipping_lines(): array {
		return $this->shipping_lines;
	}

	/**
	 * Gets the considered secondary cutoff.
	 *
	 * @return bool
	 */
	public function get_considered_secondary_cutoff(): bool {
		return $this->considered_secondary_cutoff;
	}

	/**
	 * Sets the considered secondary cutoff.
	 *
	 * @param bool $considered_secondary_cutoff The considered secondary cutoff.
	 *
	 * @return void
	 */
	public function set_considered_secondary_cutoff( bool $considered_secondary_cutoff ): void {
		$this->considered_secondary_cutoff = $considered_secondary_cutoff;
	}
}
