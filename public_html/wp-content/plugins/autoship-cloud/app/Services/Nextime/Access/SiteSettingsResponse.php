<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Site Settings Response.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Access;

/**
 * Represents the response from the Nextime site settings API.
 */
class SiteSettingsResponse {

	/**
	 * Represents the Nextime site ID.
	 *
	 * @var int
	 */
	private int $site_id;

	/**
	 * Represents the Nextime site token.
	 *
	 * @var string
	 */
	private string $site_token;

	/**
	 * Represents the Nextime integration ID.
	 *
	 * @var int
	 */
	private int $integration_id;

	/**
	 * Represents whether the delivery date should be displayed.
	 *
	 * @var bool
	 */
	private bool $should_display_delivery_date = true;

	/**
	 * Represents whether the next occurrence date should be aligned with the delivery date.
	 *
	 * @var bool
	 */
	private bool $should_align_next_occurrence_date = true;

	/**
	 * Represents the status of the site.
	 *
	 * @var bool
	 */
	private bool $is_enabled = false;

	/**
	 * Constructor.
	 *
	 * @param int    $site_id The Nextime site ID.
	 * @param string $site_token The Nextime site token.
	 * @param int    $integration_id The Nextime integration ID.
	 * @param bool   $should_display_delivery_date Indicates whether the delivery date should be displayed.
	 * @param bool   $should_align_next_occurrence_date Indicates whether the next occurrence date should be aligned with the delivery date.
	 * @param bool   $is_enabled The status of the site.
	 */
	public function __construct( int $site_id, string $site_token, int $integration_id, bool $should_display_delivery_date, bool $should_align_next_occurrence_date, bool $is_enabled ) {
		$this->site_id                           = $site_id;
		$this->site_token                        = $site_token;
		$this->integration_id                    = $integration_id;
		$this->should_display_delivery_date      = $should_display_delivery_date;
		$this->should_align_next_occurrence_date = $should_align_next_occurrence_date;
		$this->is_enabled                        = $is_enabled;
	}

	/**
	 * Get the site ID.
	 *
	 * @return int
	 */
	public function get_site_id(): int {
		return $this->site_id;
	}

	/**
	 * Get the site token.
	 *
	 * @return string
	 */
	public function get_site_token(): string {
		return $this->site_token;
	}

	/**
	 * Get the integration ID.
	 *
	 * @return int
	 */
	public function get_integration_id(): int {
		return $this->integration_id;
	}

	/**
	 * Get the delivery date display status.
	 *
	 * @return bool
	 */
	public function should_display_delivery_date(): bool {
		return $this->should_display_delivery_date;
	}

	/**
	 * Get the next occurrence date alignment status.
	 *
	 * @return bool
	 */
	public function should_align_next_occurrence_date(): bool {
		return $this->should_align_next_occurrence_date;
	}

	/**
	 * Get the status of the site.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return $this->is_enabled;
	}

	/**
	 * Convert the response to an array.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'site_id'                           => $this->site_id,
			'site_token'                        => $this->site_token,
			'integration_id'                    => $this->integration_id,
			'should_display_delivery_date'      => $this->should_display_delivery_date,
			'should_align_next_occurrence_date' => $this->should_align_next_occurrence_date,
			'is_enabled'                        => $this->is_enabled,
		);
	}
}
