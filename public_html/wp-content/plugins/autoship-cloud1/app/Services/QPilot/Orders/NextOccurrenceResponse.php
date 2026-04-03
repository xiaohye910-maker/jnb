<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * Next occurrence response class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Orders;

/**
 * Response object for next occurrence date information.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class NextOccurrenceResponse {
	/**
	 * The next occurrence date in UTC.
	 *
	 * @var string
	 */
	private string $next_occurrence_utc;

	/**
	 * The next occurrence date with offset.
	 *
	 * @var string|null
	 */
	private ?string $next_occurrence_offset = null;

	/**
	 * The UTC offset.
	 *
	 * @var int|null
	 */
	private ?int $utc_offset = null;

	/**
	 * The raw response data.
	 *
	 * @var \stdClass
	 */
	private \stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param \stdClass $data The response data.
	 */
	public function __construct( \stdClass $data ) {
		$this->raw_data               = $data;
		$this->next_occurrence_utc    = $data->nextOccurrenceUtc; // phpcs:ignore
		$this->next_occurrence_offset = $data->nextOccurrenceOffset ?? null; // phpcs:ignore
		$this->utc_offset             = $data->utcOffset ?? null; // phpcs:ignore
	}

	/**
	 * Get the next occurrence date in UTC.
	 *
	 * @return string The next occurrence date in UTC.
	 */
	public function get_next_occurrence_utc(): string {
		return $this->next_occurrence_utc;
	}

	/**
	 * Get the next occurrence date with offset.
	 *
	 * @return string|null The next occurrence date with offset.
	 */
	public function get_next_occurrence_offset(): ?string {
		return $this->next_occurrence_offset;
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
	 * Get the raw response data.
	 *
	 * @return \stdClass The raw response data.
	 */
	public function get_raw_data(): \stdClass {
		return $this->raw_data;
	}

	/**
	 * Convert the response to an array.
	 *
	 * @return array The response as an array.
	 */
	public function to_array(): array {
		return (array) $this->raw_data;
	}
}
