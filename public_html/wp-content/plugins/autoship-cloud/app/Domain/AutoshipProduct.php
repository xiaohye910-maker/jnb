<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Domain decorator for WC_Product with Autoship-specific metadata.
 *
 * @package Autoship\Domain
 * @since 2.12.1
 */

namespace Autoship\Domain;

/**
 * Wraps a WC_Product and encapsulates all Autoship-specific
 * post-meta operations (frequency options, override flags, cache).
 *
 * Immutable reference to the underlying WC_Product; mutates only
 * WordPress post-meta via get_post_meta / update_post_meta.
 *
 * @package Autoship\Domain
 * @since 2.12.1
 */
class AutoshipProduct {

	/**
	 * The underlying WooCommerce product.
	 *
	 * @var \WC_Product
	 */
	private $product;

	/**
	 * Constructor.
	 *
	 * @param \WC_Product $product The WooCommerce product.
	 */
	public function __construct( $product ) {
		$this->product = $product;
	}

	/**
	 * Get the product ID.
	 *
	 * @return int
	 */
	public function get_id(): int {
		return (int) $this->product->get_id();
	}

	/**
	 * Get the underlying WooCommerce product.
	 *
	 * @return \WC_Product
	 */
	public function get_wc_product() {
		return $this->product;
	}

	/**
	 * Check if frequency override is enabled for this product.
	 *
	 * Preserves the 'autoship_override_simple_frequency_options_default'
	 * filter for backward compatibility.
	 *
	 * @return bool
	 */
	public function is_frequency_override_enabled(): bool {
		$id  = $this->get_id();
		$val = apply_filters(
			'autoship_override_simple_frequency_options_default',
			get_post_meta( $id, '_autoship_override_frequency_options', true ),
			$id
		);

		return 'yes' === ( empty( $val ) ? 'no' : $val );
	}

	/**
	 * Check if this product allows bulk frequency updates.
	 *
	 * @return bool
	 */
	public function is_bulk_frequency_updatable(): bool {
		return 'yes' === get_post_meta( $this->get_id(), '_autoship_allow_frequency_options_bulk_update', true );
	}

	/**
	 * Check if this product should be skipped during the bulk frequency update.
	 *
	 * A product is skipped when it has frequency override enabled
	 * but is NOT marked as bulk-updatable.
	 *
	 * @return bool
	 */
	public function should_skip_bulk_frequency_update(): bool {
		return $this->is_frequency_override_enabled() && ! $this->is_bulk_frequency_updatable();
	}

	/**
	 * Enable the frequency override flag on this product.
	 *
	 * @return void
	 */
	public function enable_frequency_override(): void {
		update_post_meta( $this->get_id(), '_autoship_override_frequency_options', 'yes' );
	}

	/**
	 * Enable the bulk frequency update flag on this product.
	 *
	 * @return void
	 */
	public function enable_bulk_frequency_update(): void {
		update_post_meta( $this->get_id(), '_autoship_allow_frequency_options_bulk_update', 'yes' );
	}

	/**
	 * Get the current frequency options from post-meta.
	 *
	 * @param int $max_slots Maximum number of frequency slots to read.
	 *
	 * @return FrequencyOption[]
	 */
	public function get_frequency_options( int $max_slots ): array {
		$id      = $this->get_id();
		$options = array();

		for ( $i = 0; $i < $max_slots; ++$i ) {
			$type         = get_post_meta( $id, "_autoship_frequency_type_{$i}", true );
			$number       = get_post_meta( $id, "_autoship_frequency_{$i}", true );
			$display_name = get_post_meta( $id, "_autoship_frequency_display_name_{$i}", true );

			if ( '' !== $type || '' !== $number || '' !== $display_name ) {
				$options[] = new FrequencyOption(
					is_string( $type ) ? $type : '',
					is_string( $number ) ? $number : '',
					is_string( $display_name ) ? $display_name : ''
				);
			}
		}

		return $options;
	}

	/**
	 * Set the frequency options on this product's post-meta.
	 *
	 * Performs dirty-checking: only writes meta that has changed.
	 *
	 * @param array $frequencies Array of frequency data arrays with keys:
	 *                           'frequency_type', 'frequency_number', 'display_name'.
	 * @param int   $max_slots   Maximum number of frequency slots.
	 *
	 * @return void
	 */
	public function set_frequency_options( array $frequencies, int $max_slots ): void {
		$id           = $this->get_id();
		$current_meta = get_post_meta( $id );

		for ( $i = 0; $i < $max_slots; ++$i ) {
			$type   = isset( $frequencies[ $i ]['frequency_type'] ) ? sanitize_text_field( $frequencies[ $i ]['frequency_type'] ) : '';
			$name   = isset( $frequencies[ $i ]['display_name'] ) ? sanitize_text_field( $frequencies[ $i ]['display_name'] ) : '';
			$number = isset( $frequencies[ $i ]['frequency_number'] ) ? sanitize_text_field( $frequencies[ $i ]['frequency_number'] ) : '';

			if ( ! isset( $current_meta[ "_autoship_frequency_type_$i" ][0] ) || $current_meta[ "_autoship_frequency_type_$i" ][0] !== $type ) {
				update_post_meta( $id, "_autoship_frequency_type_$i", $type );
			}

			if ( ! isset( $current_meta[ "_autoship_frequency_$i" ][0] ) || $current_meta[ "_autoship_frequency_$i" ][0] !== $number ) {
				update_post_meta( $id, "_autoship_frequency_$i", $number );
			}

			if ( ! isset( $current_meta[ "_autoship_frequency_display_name_$i" ][0] ) || $current_meta[ "_autoship_frequency_display_name_$i" ][0] !== $name ) {
				update_post_meta( $id, "_autoship_frequency_display_name_$i", $name );
			}
		}
	}

	/**
	 * Invalidate the WordPress post-cache for this product.
	 *
	 * @return void
	 */
	public function invalidate_cache(): void {
		clean_post_cache( $this->get_id() );
	}
}
