<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Healthcheck Settings Interface.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Services\Healthcheck;

/**
 * Settings contract for the Healthcheck system.
 *
 * Wraps the WordPress options used to store integration health
 * status, endpoint timestamps, and last-check metadata.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
interface HealthcheckSettingsInterface {

	/**
	 * Get the overall health status.
	 *
	 * @return string The health status ('healthy', 'unhealthy', or empty).
	 */
	public function get_health_status(): string;

	/**
	 * Set the overall health status.
	 *
	 * @param string $status The health status to set.
	 *
	 * @return void
	 */
	public function set_health_status( string $status ): void;

	/**
	 * Get the health details notice.
	 *
	 * @return string The health details string.
	 */
	public function get_health_details(): string;

	/**
	 * Set the health details notice.
	 *
	 * @param string $details The details string to set.
	 *
	 * @return void
	 */
	public function set_health_details( string $details ): void;

	/**
	 * Get the integration point status timestamp for a given type.
	 *
	 * @param string $type The integration type (e.g. 'post', 'wc_get', 'put').
	 *
	 * @return int The UTC Unix timestamp, or 0 if not set.
	 */
	public function get_point_status( string $type ): int;

	/**
	 * Set the integration point status timestamp for a given type.
	 *
	 * @param string $type      The integration type (e.g. 'post', 'wc_get', 'put').
	 * @param int    $timestamp The UTC Unix timestamp.
	 *
	 * @return bool True if the option was updated, false otherwise.
	 */
	public function set_point_status( string $type, int $timestamp ): bool;

	/**
	 * Clear all integration point status timestamps (sets to empty).
	 *
	 * @return void
	 */
	public function clear_point_statuses(): void;

	/**
	 * Delete all healthcheck options (timestamps + health status).
	 *
	 * @return void
	 */
	public function delete_all(): void;

	/**
	 * Get the last integration check UTC timestamp.
	 *
	 * @return int The UTC Unix timestamp, or 0 if not set.
	 */
	public function get_last_check_timestamp(): int;

	/**
	 * Set the last integration check UTC timestamp.
	 *
	 * @param int $timestamp The UTC Unix timestamp.
	 *
	 * @return void
	 */
	public function set_last_check_timestamp( int $timestamp ): void;

	/**
	 * Get the status fields to action map.
	 *
	 * Returns the mapping of option keys to their corresponding
	 * REST API action identifiers.
	 *
	 * @return array The field-to-action map.
	 */
	public function get_status_fields(): array;
}
