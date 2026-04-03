<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Settings Interface.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime;

interface NextimeSettingsInterface {

	/**
	 * Get the API URL.
	 *
	 * @return string
	 */
	public function get_api_url(): string;

	/**
	 * Get the API key.
	 *
	 * @return string The API key.
	 */
	public function get_site_token(): string;

	/**
	 * Set the API key.
	 *
	 * @param string $site_token The API key.
	 * @return void
	 */
	public function set_site_token( string $site_token ): void;

	/**
	 * Get the site ID.
	 *
	 * @return int The site ID.
	 */
	public function get_site_id(): int;

	/**
	 * Set the site ID.
	 *
	 * @param int $site_id The site ID.
	 * @return void
	 */
	public function set_site_id( int $site_id ): void;

	/**
	 * Gets the integration ID.
	 *
	 * @return int
	 */
	public function get_integration_id(): int;

	/**
	 * Set the integration ID.
	 *
	 * @param int $integration_id The integration ID.
	 *
	 * @return void
	 */
	public function set_integration_id( int $integration_id ): void;

	/**
	 * Gets the display delivery date status.
	 *
	 * @return bool
	 */
	public function get_display_delivery_date(): bool;

	/**
	 * Set the display delivery date status.
	 *
	 * @param bool $display The display delivery date status.
	 *
	 * @return void
	 */
	public function set_display_delivery_date( bool $display ): void;

	/**
	 * Get the next occurrence date alignment status.
	 *
	 * @return bool
	 */
	public function get_align_next_occurrence_date(): bool;

	/**
	 * Set the next occurrence date alignment status.
	 *
	 * @param bool $align The next occurrence date alignment status.
	 *
	 * @return void
	 */
	public function set_align_next_occurrence_date( bool $align ): void;

	/**
	 * Set the source of the API call.
	 *
	 * @param string $source The source of the call.
	 * @return void
	 */
	public function set_source( string $source ): void;

	/**
	 * Get the source of the API call.
	 *
	 * @return string
	 */
	public function get_source(): string;

	/**
	 * The QPilot site ID.
	 *
	 * @return int
	 */
	public function get_qpilot_site_id(): int;

	/**
	 * Gets the timeout for the API call.
	 *
	 * @return int
	 */
	public function get_timeout(): int;

	/**
	 * Check if the integration is enabled.
	 *
	 * @return bool
	 */
	public function get_is_enabled(): bool;

	/**
	 * Set the integration status.
	 *
	 * @param bool $is_enabled The integration status.
	 *
	 * @return void
	 */
	public function set_is_enabled( bool $is_enabled ): void;

	/**
	 * Get the last check timestamp.
	 *
	 * @return int
	 */
	public function get_last_check_timestamp(): int;

	/**
	 * Set the last check timestamp.
	 *
	 * @param int $timestamp The last check timestamp.
	 *
	 * @return void
	 */
	public function set_last_check_timestamp( int $timestamp ): void;

	/**
	 * Gets the last check status.
	 *
	 * @return string The last check status.
	 */
	public function get_last_check_status(): string;

	/**
	 * Set the last check status.
	 *
	 * @param string $status The last check status.
	 *
	 * @return void
	 */
	public function set_last_check_status( string $status ): void;
}
