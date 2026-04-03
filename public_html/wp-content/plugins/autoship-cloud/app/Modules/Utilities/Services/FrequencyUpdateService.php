<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Frequency Update Service for the Utilities module.
 *
 * @package Autoship
 * @since 2.12.1
 */

namespace Autoship\Modules\Utilities\Services;

use Autoship\Core\AutoshipSettingsInterface;
use Autoship\Domain\AutoshipProduct;
use WP_Error;

/**
 * Service for updating product frequency options and validating frequency data.
 *
 * Extracted from:
 * - autoship_products_update_frequency_options() (src/bulk.php)
 * - autoship_ajax_validate_bulk_update_frequency_options_frequencies() (src/bulk.php)
 *
 * @package Autoship\Modules\Utilities\Services
 * @since 2.12.1
 */
class FrequencyUpdateService {

	/**
	 * Allowed frequency types with their valid ranges.
	 *
	 * @var array<string, array{min: int, max: int}>
	 */
	const ALLOWED_TYPES = array(
		'Days'          => array(
			'min' => 1,
			'max' => 365,
		),
		'Weeks'         => array(
			'min' => 1,
			'max' => 52,
		),
		'Months'        => array(
			'min' => 1,
			'max' => 12,
		),
		'DayOfTheWeek'  => array(
			'min' => 1,
			'max' => 7,
		),
		'DayOfTheMonth' => array(
			'min' => 1,
			'max' => 31,
		),
	);

	/**
	 * The Autoship settings.
	 *
	 * @var AutoshipSettingsInterface
	 */
	private AutoshipSettingsInterface $settings;

	/**
	 * Constructor.
	 *
	 * @param AutoshipSettingsInterface $settings The Autoship settings.
	 */
	public function __construct( AutoshipSettingsInterface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Update the frequency options for a product.
	 *
	 * Skips products that have overridden frequency options and are
	 * not marked as bulk-updatable. After updating meta, invalidates
	 * the post-cache to prevent stale PDP data.
	 *
	 * @param AutoshipProduct $product     The product to update.
	 * @param array           $frequencies The validated frequency options.
	 *
	 * @return bool True if the update was performed or skipped, false on invalid input.
	 */
	public function update_product_frequencies( AutoshipProduct $product, array $frequencies ): bool {
		if ( ! $product->get_id() ) {
			return false;
		}

		if ( $product->should_skip_bulk_frequency_update() ) {
			return true;
		}

		$product->enable_frequency_override();
		$product->enable_bulk_frequency_update();
		$product->set_frequency_options( $frequencies, $this->settings->get_max_frequency_options_count() );
		$product->invalidate_cache();

		return true;
	}

	/**
	 * Validate and sanitize frequency options from user input.
	 *
	 * @param array $frequencies The raw frequency options.
	 *
	 * @return array|WP_Error The validated frequencies, or WP_Error on failure.
	 */
	public function validate_frequencies( array $frequencies ) {
		if ( empty( $frequencies ) ) {
			return new WP_Error( 'invalid_data', __( 'Frequencies data is missing or invalid.', 'autoship' ) );
		}

		$validated_frequencies = array();

		foreach ( $frequencies as $index => $frequency ) {
			if ( ! isset( $frequency['id'], $frequency['frequency_type'], $frequency['frequency_number'], $frequency['display_name'] ) ) {
				// translators: %d is the frequency option index.
				return new WP_Error( 'missing_data', sprintf( __( 'Missing data in frequency option %d.', 'autoship' ), $index + 1 ) );
			}

			$frequency_id     = absint( $frequency['id'] );
			$frequency_type   = sanitize_text_field( $frequency['frequency_type'] );
			$frequency_number = absint( $frequency['frequency_number'] );
			$frequency_name   = sanitize_text_field( $frequency['display_name'] );

			if ( ! isset( self::ALLOWED_TYPES[ $frequency_type ] ) ) {
				// translators: %d is the frequency option index.
				return new WP_Error( 'invalid_type', sprintf( __( 'Invalid data in frequency option %d.', 'autoship' ), $index + 1 ) );
			}

			$range = self::ALLOWED_TYPES[ $frequency_type ];
			if ( $frequency_number < $range['min'] || $frequency_number > $range['max'] ) {
				// translators: %d is the frequency number.
				return new WP_Error( 'invalid_number', sprintf( __( 'Invalid frequency number in option %d.', 'autoship' ), $index + 1 ) );
			}

			$validated_frequencies[] = array(
				'id'               => $frequency_id,
				'frequency_type'   => $frequency_type,
				'frequency_number' => $frequency_number,
				'display_name'     => $frequency_name,
			);
		}

		return $validated_frequencies;
	}

	/**
	 * Get the allowed frequency types.
	 *
	 * @return array<string, array{min: int, max: int}>
	 */
	public function get_allowed_frequency_types(): array {
		return self::ALLOWED_TYPES;
	}

	/**
	 * Get the maximum number of frequency options per product.
	 *
	 * @return int
	 */
	public function get_max_options_count(): int {
		return $this->settings->get_max_frequency_options_count();
	}
}
