<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Next occurrence request class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Request object for generating the next occurrence date.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class NextOccurrenceRequest {
	/**
	 * The frequency type.
	 *
	 * @var string
	 */
	private string $frequency_type;

	/**
	 * The frequency value.
	 *
	 * @var int
	 */
	private int $frequency;

	/**
	 * The reference date.
	 *
	 * @var string|null
	 */
	private ?string $reference_date = null;

	/**
	 * The UTC offset.
	 *
	 * @var int|null
	 */
	private ?int $utc_offset = null;

	/**
	 * Constructor.
	 *
	 * @param string $frequency_type The frequency type.
	 * @param int    $frequency The frequency value.
	 */
	public function __construct( string $frequency_type, int $frequency ) {
		$this->frequency_type = $frequency_type;
		$this->frequency      = $frequency;
	}

	/**
	 * Set the reference date.
	 *
	 * @param string $reference_date The reference date.
	 *
	 * @return self
	 */
	public function set_reference_date( string $reference_date ): self {
		$this->reference_date = $reference_date;

		return $this;
	}

	/**
	 * Set the UTC offset.
	 *
	 * @param int $utc_offset The UTC offset.
	 *
	 * @return self
	 */
	public function set_utc_offset( int $utc_offset ): self {
		$this->utc_offset = $utc_offset;

		return $this;
	}

	/**
	 * Get the frequency type.
	 *
	 * @return string The frequency type.
	 */
	public function get_frequency_type(): string {
		return $this->frequency_type;
	}

	/**
	 * Get the frequency value.
	 *
	 * @return int The frequency value.
	 */
	public function get_frequency(): int {
		return $this->frequency;
	}

	/**
	 * Get the reference date.
	 *
	 * @return string|null The reference date.
	 */
	public function get_reference_date(): ?string {
		return $this->reference_date;
	}

	/**
	 * Get the UTC offset.
	 *
	 * @return int|null The UTC offset.
	 */
	public function get_utc_offset(): ?int {
		return $this->utc_offset;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'frequencyType' => $this->frequency_type,
			'frequency'     => $this->frequency,
		);

		if ( null !== $this->reference_date ) {
			$data['referenceDate'] = $this->reference_date;
		}

		if ( null !== $this->utc_offset ) {
			$data['utcOffset'] = $this->utc_offset;
		}

		return $data;
	}
}
