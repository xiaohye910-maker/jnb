<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the Healthcheck Settings.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Services\Healthcheck\Implementations;

use Autoship\Services\Healthcheck\HealthcheckSettingsInterface;

/**
 * WordPress implementation of the Healthcheck Settings.
 *
 * Uses WordPress options to persist integration health status and
 * endpoint timestamps. Preserves wp_cache_delete() calls before reads
 * to ensure fresh data (matching the legacy behavior).
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class WordPressHealthcheckSettings implements HealthcheckSettingsInterface {

	/**
	 * The status fields mapping option keys to REST action identifiers.
	 *
	 * @var array
	 */
	const STATUS_FIELDS = array(
		'autoship_wc_get_checked_utc' => 'wc_get',
		'autoship_put_checked_utc'    => 'put',
		'autoship_post_checked_utc'   => 'post',
	);

	/**
	 * Get the overall health status.
	 *
	 * @return string The health status ('healthy', 'unhealthy', or empty).
	 */
	public function get_health_status(): string {
		wp_cache_delete( 'autoship_health', 'options' );

		$value = get_option( 'autoship_health' );

		return false === $value ? '' : (string) $value;
	}

	/**
	 * Set the overall health status.
	 *
	 * @param string $status The health status to set.
	 *
	 * @return void
	 */
	public function set_health_status( string $status ): void {
		update_option( 'autoship_health', $status, false );
	}

	/**
	 * Get the health details notice.
	 *
	 * @return string The health details string.
	 */
	public function get_health_details(): string {
		$value = get_option( 'autoship_health_details' );

		return false === $value ? '' : (string) $value;
	}

	/**
	 * Set the health details notice.
	 *
	 * @param string $details The details string to set.
	 *
	 * @return void
	 */
	public function set_health_details( string $details ): void {
		update_option( 'autoship_health_details', $details, false );
	}

	/**
	 * Get the integration point status timestamp for a given type.
	 *
	 * @param string $type The integration type (e.g. 'post', 'wc_get', 'put').
	 *
	 * @return int The UTC Unix timestamp, or 0 if not set.
	 */
	public function get_point_status( string $type ): int {
		$type   = strtolower( $type );
		$option = "autoship_{$type}_checked_utc";

		wp_cache_delete( $option, 'options' );

		$val = get_option( $option );

		return empty( $val ) ? 0 : (int) $val;
	}

	/**
	 * Set the integration point status timestamp for a given type.
	 *
	 * @param string $type      The integration type (e.g. 'post', 'wc_get', 'put').
	 * @param int    $timestamp The UTC Unix timestamp.
	 *
	 * @return bool True if the option was updated, false otherwise.
	 */
	public function set_point_status( string $type, int $timestamp ): bool {
		$type = strtolower( $type );

		return update_option( "autoship_{$type}_checked_utc", $timestamp, false );
	}

	/**
	 * Clear all integration point status timestamps (sets to empty).
	 *
	 * @return void
	 */
	public function clear_point_statuses(): void {
		foreach ( self::STATUS_FIELDS as $field => $action ) {
			update_option( $field, '', false );
		}
	}

	/**
	 * Delete all healthcheck options (timestamps + health status).
	 *
	 * @return void
	 */
	public function delete_all(): void {
		$fields                    = self::STATUS_FIELDS;
		$fields['autoship_health'] = 'true';

		foreach ( $fields as $field => $action ) {
			delete_option( $field );
		}
	}

	/**
	 * Get the last integration check UTC timestamp.
	 *
	 * @return int The UTC Unix timestamp, or 0 if not set.
	 */
	public function get_last_check_timestamp(): int {
		$value = get_option( '_autoship_last_integration_check_utc', 0 );

		return intval( $value );
	}

	/**
	 * Set the last integration check UTC timestamp.
	 *
	 * @param int $timestamp The UTC Unix timestamp.
	 *
	 * @return void
	 */
	public function set_last_check_timestamp( int $timestamp ): void {
		update_option( '_autoship_last_integration_check_utc', $timestamp, false );
	}

	/**
	 * Get the status fields to action map.
	 *
	 * @return array The field-to-action map.
	 */
	public function get_status_fields(): array {
		return self::STATUS_FIELDS;
	}
}
