<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Value object for a product frequency option.
 *
 * @package Autoship\Domain
 * @since 2.12.1
 */

namespace Autoship\Domain;

/**
 * Represents a single frequency option configured on a product.
 *
 * Immutable value object.
 *
 * @package Autoship\Domain
 * @since 2.12.1
 */
class FrequencyOption {

	/**
	 * The frequency type (e.g., 'Days', 'Weeks').
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * The frequency number.
	 *
	 * @var string
	 */
	private string $number;

	/**
	 * The display name.
	 *
	 * @var string
	 */
	private string $display_name;

	/**
	 * Constructor.
	 *
	 * @param string $type         The frequency type.
	 * @param string $number       The frequency number.
	 * @param string $display_name The display name.
	 */
	public function __construct( string $type, string $number, string $display_name ) {
		$this->type         = $type;
		$this->number       = $number;
		$this->display_name = $display_name;
	}

	/**
	 * Get the frequency type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get the frequency number.
	 *
	 * @return string
	 */
	public function get_number(): string {
		return $this->number;
	}

	/**
	 * Get the display name.
	 *
	 * @return string
	 */
	public function get_display_name(): string {
		return $this->display_name;
	}
}
