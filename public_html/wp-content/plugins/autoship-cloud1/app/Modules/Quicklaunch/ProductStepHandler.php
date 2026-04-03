<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The product step handler class.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\FeatureManager;

/**
 * Implements the product step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class ProductStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_autoship_quicklaunch_product_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles a product update request.
	 *
	 * @return void
	 */
	public function handle() {
		// Check if the quick launch is enabled.
		if ( ! FeatureManager::is_enabled( 'quicklaunch' ) ) {
			wp_send_json_error( array( 'message' => __( 'The Autoship Quick Launch is not enabled.', 'autoship' ) ) );
		}

		// If the register is not enabled, bypass it and continue using mock data.
		if ( ! FeatureManager::is_enabled( 'quicklaunch_product_setup' ) ) {
			wp_send_json_success( array( 'message' => 'ok' ) );
		}

		// Verifies that the site is connected.
		if ( ! autoship_has_credentials() || ! autoship_has_auth_token() ) {
			wp_send_json_error( array( 'message' => __( 'The site is not connected.', 'autoship' ) ) );
		}

		// Get the product information from the request.
		$product_id          = $this->get_post_int_value( 'product_id' );
		$discount_enabled    = $this->get_post_bool_value( 'discount_enabled' );
		$discount_percent    = $this->get_post_int_value( 'discount_percent' );
		$frequency_1_enabled = $this->get_post_bool_value( 'frequency_1_enabled' );
		$frequency_1_type    = $this->get_post_string_value( 'frequency_1_type' );
		$frequency_1_number  = $this->get_post_int_value( 'frequency_1_number' );
		$frequency_2_enabled = $this->get_post_bool_value( 'frequency_2_enabled' );
		$frequency_2_type    = $this->get_post_string_value( 'frequency_2_type' );
		$frequency_2_number  = $this->get_post_int_value( 'frequency_2_number' );
		$frequency_3_enabled = $this->get_post_bool_value( 'frequency_3_enabled' );
		$frequency_3_type    = $this->get_post_string_value( 'frequency_3_type' );
		$frequency_3_number  = $this->get_post_int_value( 'frequency_3_number' );

		// Validate the initial information.
		$errors_product     = $this->validate_product( $product_id );
		$errors_discount    = $this->validate_discount( $discount_enabled, $discount_percent );
		$errors_frequency_1 = $this->validate_frequency( $frequency_1_enabled, $frequency_1_type, $frequency_1_number );
		$errors_frequency_2 = $this->validate_frequency( $frequency_2_enabled, $frequency_2_type, $frequency_2_number );
		$errors_frequency_3 = $this->validate_frequency( $frequency_3_enabled, $frequency_3_type, $frequency_3_number );

		$errors = array_merge( $errors_product, $errors_discount, $errors_frequency_1, $errors_frequency_2, $errors_frequency_3 );
		if ( count( $errors ) > 0 ) {
			$error_message = $errors[0];

			// translators: %s is the validation error message.
			wp_send_json_error( array( 'message' => sprintf( __( 'Please correct the following error: %s.', 'autoship' ), $error_message ) ) );
		}

		// Gets the product.
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'The product could not be found.', 'autoship' ) ) );
		}

		$type = $product->get_type();
		$id   = $product->get_id();

		// Updating the product id for the last page.
		update_option( 'autoship_quicklaunch_product', $id );

		if ( 'variation' === $type ) {
			// Only update the parent level for variations and simple products.
			$id = $product->get_parent_id();
		}

		$result = autoship_set_product_sync_active_enabled( $id );
		autoship_update_product_variants_sync_active_flag( $id, 'yes' );
		autoship_set_product_autoship_enabled( $id );
		autoship_set_product_add_to_scheduled_order( $id );
		autoship_set_product_process_on_scheduled_order( $id );
		autoship_update_product_add_to_scheduled_order_flag( $id, 'yes' );

		$frequencies = array();
		if ( $frequency_1_enabled ) {
			$frequencies[] = array(
				'frequency_type'   => $frequency_1_type,
				'frequency_number' => $frequency_1_number,
				'display_name'     => '',
			);
		}

		if ( $frequency_2_enabled ) {
			$frequencies[] = array(
				'frequency_type'   => $frequency_2_type,
				'frequency_number' => $frequency_2_number,
				'display_name'     => '',
			);
		}

		if ( $frequency_3_enabled ) {
			$frequencies[] = array(
				'frequency_type'   => $frequency_3_type,
				'frequency_number' => $frequency_3_number,
				'display_name'     => '',
			);
		}

		$updated = $this->update_frequencies( $product, $frequencies );
		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => __( 'The product could not be updated.', 'autoship' ) ) );
			wp_die();
		}

		if ( $discount_enabled ) {
			$updated_discount = $this->update_discount( $product, $discount_percent );
			if ( ! $updated_discount ) {
				wp_send_json_error( array( 'message' => __( 'The product discount could not be set.', 'autoship' ) ) );
				wp_die();
			}
		}

		// Push it to Qpilot.
		$pushed = autoship_push_product( $product );
		if ( is_wp_error( $pushed ) || ! $pushed ) {
			wp_send_json_error( array( 'message' => __( 'The product could not be synchronized with QPilot.', 'autoship' ) ) );
		}

		wp_send_json_success( array( 'message' => 'ok' ) );
		wp_die();
	}

	/**
	 * Updates the frequencies of the product.
	 *
	 * @param object $product The product to update.
	 * @param array  $frequencies The frequencies to update.
	 * @return bool
	 */
	private function update_frequencies( object $product, array $frequencies ): bool {
		if ( ! is_a( $product, 'WC_Product' ) || ! $product->get_id() ) {
			return false;
		}

		$product_id    = $product->get_id();
		$overridden    = autoship_override_frequency_options_enabled( $product );
		$updatable     = get_post_meta( $product_id, '_autoship_allow_frequency_options_bulk_update', true );
		$updatable     = ( 'yes' === $updatable ) ? 'yes' : 'no';
		$options_count = defined( 'Autoship_Options_Count' ) ? Autoship_Options_Count : 5;

		if ( 'yes' === $overridden && 'no' === $updatable ) {
			return true;
		}

		update_post_meta( $product_id, '_autoship_override_frequency_options', 'yes' );
		update_post_meta( $product_id, '_autoship_allow_frequency_options_bulk_update', 'yes' );

		$current_meta = get_post_meta( $product_id );

		for ( $i = 0; $i < $options_count; $i++ ) {
			$type   = isset( $frequencies[ $i ]['frequency_type'] ) ? sanitize_text_field( $frequencies[ $i ]['frequency_type'] ) : '';
			$name   = isset( $frequencies[ $i ]['display_name'] ) ? sanitize_text_field( $frequencies[ $i ]['display_name'] ) : '';
			$number = isset( $frequencies[ $i ]['frequency_number'] ) ? sanitize_text_field( $frequencies[ $i ]['frequency_number'] ) : '';

			if ( ! isset( $current_meta[ "_autoship_frequency_type_$i" ][0] ) || $current_meta[ "_autoship_frequency_type_$i" ][0] !== $type ) {
				update_post_meta( $product_id, "_autoship_frequency_type_$i", $type );
			}

			if ( ! isset( $current_meta[ "_autoship_frequency_$i" ][0] ) || $current_meta[ "_autoship_frequency_$i" ][0] !== $number ) {
				update_post_meta( $product_id, "_autoship_frequency_$i", $number );
			}

			if ( ! isset( $current_meta[ "_autoship_frequency_display_name_$i" ][0] ) || $current_meta[ "_autoship_frequency_display_name_$i" ][0] !== $name ) {
				update_post_meta( $product_id, "_autoship_frequency_display_name_$i", $name );
			}
		}

		return true;
	}

	/**
	 * Updates the product discount percent for autoship and save. Sets the checkout price.
	 *
	 * @param object $product The product to update.
	 * @param int    $discount_percent The discount to set (allowed values from 1 to 99).
	 * @return bool
	 */
	private function update_discount( object $product, int $discount_percent ): bool {
		$product_id = $product->get_id();

		// The discount will be based on the regular price.
		$discount_factor = $discount_percent / 100.0;
		$regular_price   = floatval( $product->get_regular_price() );
		$checkout_price  = round( $regular_price - ( $regular_price * $discount_factor ), 2 );

		// Set the checkout price for autoship.
		$updated = autoship_set_product_checkout_price( $product_id, $checkout_price );
		if ( ! $updated ) {
			// translators: %1$d is the product id and %2$f is the checkout price.
			autoship_log_entry( __( 'Autoship Quicklaunch', 'autoship' ), sprintf( __( 'There was an issue while attempting to update the checkout price for product: %1$d to the checkout price: %2$f.', 'autoship' ), $product_id, $checkout_price ) );

			return false;
		}

		// Set the recurring price for autoship.
		$recurring = autoship_set_product_recurring_price( $product_id, $checkout_price );
		if ( ! $recurring ) {
			// translators: %1$d is the product id and %2$f is the recurring price.
			autoship_log_entry( __( 'Autoship Quicklaunch', 'autoship' ), sprintf( __( 'There was an issue while attempting to update the recurring price for product: %1$d to the recurring price: %2$f.', 'autoship' ), $product_id, $checkout_price ) );

			return false;
		}

		return true;
	}

	/**
	 * Validates the product ID.
	 *
	 * @param int $product_id The product ID.
	 * @return array
	 */
	private function validate_product( int $product_id ): array {
		$errors = array();
		if ( $product_id < 1 ) {
			$errors[] = __( 'The product ID is not valid.', 'autoship' );
		}

		return $errors;
	}

	/**
	 * Validates the discount option.
	 *
	 * @param bool $enabled The enabled state of the discount.
	 * @param int  $percent The percentage of the discount.
	 * @return array
	 */
	private function validate_discount( bool $enabled, int $percent ): array {
		$errors = array();
		if ( true === $enabled && ( $percent < 1 || $percent > 99 ) ) {
			$errors[] = __( 'The discount must be between 1 and 99.', 'autoship' );
		}

		return $errors;
	}

	/**
	 * Validates the frequency option.
	 *
	 * @param bool   $enabled The enabled state of the frequency.
	 * @param string $type The type of the frequency.
	 * @param int    $number The number value of the frequency.
	 * @return array
	 */
	private function validate_frequency( bool $enabled, string $type, int $number ): array {
		$allowed = array(
			'Days'          => array(
				'min' => 1,
				'max' => 366,
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

		$errors = array();
		if ( true === $enabled ) {
			if ( array_key_exists( $type, $allowed ) ) {
				$min = $allowed[ $type ]['min'];
				$max = $allowed[ $type ]['max'];

				if ( $number < $min || $number > $max ) {
					// translators: %1$d is the minimum value, %2$d is the maximum value.
					$errors[] = sprintf( __( 'The frequency must be between %1$d and %2$d.', 'autoship' ), $min, $max );
				}
			} else {
				$errors[] = __( 'The frequency type is not valid.', 'autoship' );
			}
		}

		return $errors;
	}
}
