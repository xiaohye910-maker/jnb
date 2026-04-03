<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for Autoship-specific settings.
 *
 * @package Autoship\Core
 * @since 2.12.1
 */

namespace Autoship\Core;

/**
 * Provides Autoship domain-specific settings.
 *
 * Unlike SettingsInterface (generic key-value store), this interface
 * exposes typed, semantically named methods for Autoship-specific
 * configuration values.
 *
 * @package Autoship\Core
 * @since 2.12.1
 */
interface AutoshipSettingsInterface {

	/**
	 * Check if global product sync is enabled.
	 *
	 * When enabled, all products are considered active for bulk operations.
	 *
	 * @return bool True if all products should be synced.
	 */
	public function is_global_sync_active_enabled(): bool;

	/**
	 * Get the maximum number of frequency option slots per product.
	 *
	 * @return int The max frequency options count.
	 */
	public function get_max_frequency_options_count(): int;

	/**
	 * Get the available frequency types with their labels.
	 *
	 * @return array<string, string> Map of type key => localized label.
	 */
	public function get_frequency_types(): array;
}
