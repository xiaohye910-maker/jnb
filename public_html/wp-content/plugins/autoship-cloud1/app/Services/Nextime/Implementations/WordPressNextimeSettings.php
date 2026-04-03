<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The WordPress Nextime Settings implementation.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Implementations;

use Autoship\Core\Environment;
use Autoship\Services\Nextime\NextimeSettingsInterface;

/**
 * The Nextime Settings implementation.
 */
class WordPressNextimeSettings implements NextimeSettingsInterface {

	/**
	 * The source of the API call.
	 *
	 * @var string
	 */
	private string $source;

	/**
	 * Constructor.
	 *
	 * @param Environment $environment The environment object.
	 */
	public function __construct( Environment $environment ) {
		$this->source = "AC-WC-{$environment->get_autoship_version()}";
	}

	/**
	 * Get the API URL.
	 *
	 * @return string
	 */
	public function get_api_url(): string {
		return apply_filters( 'autoship_nextime_api_url', 'https://api.nextime.ai' );
	}

	/**
	 * Get the API key.
	 *
	 * @return string The API key.
	 */
	public function get_site_token(): string {
		return get_option( 'autoship_nextime_site_token', '' );
	}

	/**
	 * Set the API key.
	 *
	 * @param string $site_token The API key.
	 *
	 * @return void
	 */
	public function set_site_token( string $site_token ): void {
		update_option( 'autoship_nextime_site_token', $site_token );
	}

	/**
	 * Get the site ID.
	 *
	 * @return int The site ID.
	 */
	public function get_site_id(): int {
		return intval( get_option( 'autoship_nextime_site_id', 0 ) );
	}

	/**
	 * Set the site ID.
	 *
	 * @param int $site_id The site ID.
	 *
	 * @return void
	 */
	public function set_site_id( int $site_id ): void {
		update_option( 'autoship_nextime_site_id', $site_id );
	}

	/**
	 * Set the source of the API call.
	 *
	 * @param string $source The source of the call.
	 *
	 * @return void
	 */
	public function set_source( string $source ): void {
		$this->source = $source;
	}

	/**
	 * Get the source of the API call.
	 *
	 * @return string
	 */
	public function get_source(): string {
		return $this->source;
	}

	/**
	 * The QPilot site ID.
	 *
	 * @return int
	 */
	public function get_qpilot_site_id(): int {
		$qpilot_site_id = autoship_get_site_id();

		if ( empty( $qpilot_site_id ) ) {
			return 0;
		}

		return $qpilot_site_id;
	}

	/**
	 * Gets the timeout for the API call.
	 *
	 * @return int
	 */
	public function get_timeout(): int {
		return apply_filters( 'autoship_nextime_api_timeout', 10 );
	}

	/**
	 * Gets the integration ID.
	 *
	 * @return int
	 */
	public function get_integration_id(): int {
		return intval( get_option( 'autoship_nextime_integration_id', 0 ) );
	}

	/**
	 * Set the integration ID.
	 *
	 * @param int $integration_id The integration ID.
	 *
	 * @return void
	 */
	public function set_integration_id( int $integration_id ): void {
		update_option( 'autoship_nextime_integration_id', $integration_id );
	}

	/**
	 * Gets the display delivery date status.
	 *
	 * @return bool
	 */
	public function get_display_delivery_date(): bool {
		$display = get_option( 'autoship_nextime_display_delivery_date', 'yes' );

		return 'yes' === $display;
	}

	/**
	 * Set the display delivery date status.
	 *
	 * @param bool $display The display delivery date status.
	 *
	 * @return void
	 */
	public function set_display_delivery_date( bool $display ): void {
		update_option( 'autoship_nextime_display_delivery_date', $display ? 'yes' : 'no' );
	}

	/**
	 * Get the next occurrence date alignment status.
	 *
	 * @return bool
	 */
	public function get_align_next_occurrence_date(): bool {
		$align = get_option( 'autoship_nextime_align_next_occurrence_date', 'yes' );

		return 'yes' === $align;
	}

	/**
	 * Set the next occurrence date alignment status.
	 *
	 * @param bool $align The next occurrence date alignment status.
	 *
	 * @return void
	 */
	public function set_align_next_occurrence_date( bool $align ): void {
		update_option( 'autoship_nextime_align_next_occurrence_date', $align ? 'yes' : 'no' );
	}

	/**
	 * Check if the integration is enabled.
	 *
	 * @return bool
	 */
	public function get_is_enabled(): bool {
		$enabled = get_option( 'autoship_nextime_enabled', 'yes' );

		return 'yes' === $enabled;
	}

	/**
	 * Set the integration status.
	 *
	 * @param bool $is_enabled The integration status.
	 *
	 * @return void
	 */
	public function set_is_enabled( bool $is_enabled ): void {
		update_option( 'autoship_nextime_enabled', $is_enabled ? 'yes' : 'no' );
	}

	/**
	 * Get the last check timestamp.
	 *
	 * @return int
	 */
	public function get_last_check_timestamp(): int {
		$last_check = get_option( 'autoship_nextime_last_check_timestamp', 0 );

		return intval( $last_check );
	}

	/**
	 * Set the last check timestamp.
	 *
	 * @param int $timestamp The last check timestamp.
	 *
	 * @return void
	 */
	public function set_last_check_timestamp( int $timestamp ): void {
		update_option( 'autoship_nextime_last_check_timestamp', $timestamp );
	}

	/**
	 * Gets the last check status.
	 *
	 * @return string The last check status.
	 */
	public function get_last_check_status(): string {
		return get_option( 'autoship_nextime_last_check_status', 'unknown' );
	}

	/**
	 * Set the last check status.
	 *
	 * @param string $status The last check status.
	 *
	 * @return void
	 */
	public function set_last_check_status( string $status ): void {
		update_option( 'autoship_nextime_last_check_status', $status );
	}
}
