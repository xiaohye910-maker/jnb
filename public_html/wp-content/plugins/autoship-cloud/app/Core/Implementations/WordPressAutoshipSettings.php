<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of AutoshipSettingsInterface.
 *
 * @package Autoship\Core\Implementations
 * @since 2.12.1
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\AutoshipSettingsInterface;

/**
 * WordPress implementation of AutoshipSettingsInterface.
 *
 * @package Autoship\Core\Implementations
 * @since 2.12.1
 */
class WordPressAutoshipSettings implements AutoshipSettingsInterface {

	/**
	 * Check if global product sync is enabled.
	 *
	 * Deletes the options cache first to ensure a fresh read,
	 * matching the legacy autoship_global_sync_active_enabled() behavior.
	 *
	 * @return bool
	 */
	public function is_global_sync_active_enabled(): bool {
		wp_cache_delete( '_autoship_sync_all_products_enabled', 'options' );

		return 'yes' === get_option( '_autoship_sync_all_products_enabled' );
	}

	/**
	 * Get the maximum number of frequency option slots per product.
	 *
	 * @return int
	 */
	public function get_max_frequency_options_count(): int {
		return defined( 'Autoship_Options_Count' ) ? Autoship_Options_Count : 5; // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
	}

	/**
	 * Get the available frequency types with their labels.
	 *
	 * Preserves the 'autoship_relative_next_occurrence_types' filter
	 * for backward compatibility.
	 *
	 * @return array<string, string>
	 */
	public function get_frequency_types(): array {
		return apply_filters(
			'autoship_relative_next_occurrence_types',
			array(
				'Days'          => __( 'Days', 'autoship' ),
				'Weeks'         => __( 'Weeks', 'autoship' ),
				'Months'        => __( 'Months', 'autoship' ),
				'DayOfTheWeek'  => __( 'Day of the week', 'autoship' ),
				'DayOfTheMonth' => __( 'Day of the month', 'autoship' ),
			)
		);
	}
}
