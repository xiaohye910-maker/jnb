<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The free shipping method for Autoship orders.
 *
 * @package Autoship
 * @since 1.0.0
 */

// Works with WooCommerce 3.2.6.
add_action( 'woocommerce_shipping_init', 'autoship_shipping_method_init' );

/**
 * Initializes the Autoship Free Shipping method.
 *
 * This function registers the Autoship Free Shipping method with WooCommerce.
 * It defines the class that handles the shipping method and adds it to the list of available shipping methods.
 */
function autoship_shipping_method_init() {

	/**
	 * Class Autoship_FreeShipping_Method. Handles the Autoship Free Shipping method.
	 *
	 * @package Autoship
	 */
	class Autoship_FreeShipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor
		 *
		 * @param int $instance_id Optional. The instance ID of the shipping method.
		 * @access public
		 * @return void
		 */
		public function __construct( $instance_id = 0 ) {
			$this->instance_id        = absint( $instance_id );
			$this->id                 = 'autoship_free_shipping';
			$this->method_title       = __( 'Autoship Free Shipping (Not editable)', 'autoship' );
			$this->method_description = __( 'Free shipping for Autoship orders.', 'autoship' );

			// Add to shipping zones list.
			$this->supports = array(
				'shipping-zones',
			);

			$this->init();

			$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
			$this->title   = $this->method_title;
		}

		/**
		 * Init settings
		 *
		 * @access public
		 * @return void
		 */
		public function init() {

			// Load the settings API.
			$this->init_form_fields();
			$this->init_settings();

			// Save settings in admin if any are defined.
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Define settings field for this shipping
		 *
		 * @return void
		 */
		public function init_form_fields() {
		}

		/**
		 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
		 *
		 * @access public
		 *
		 * @param array $package Optional. An Optional array of package data. This is not used in this method but can be used to determine shipping costs based on package contents.
		 *
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			// Register the rate.
			$this->add_rate(
				array(
					'id'      => $this->id,
					'label'   => __( 'Free Shipping', 'autoship' ),
					'cost'    => 0.0,
					'package' => $package,
					'taxes'   => false,
				)
			);
		}
	}

	/**
	 * Add the Autoship Free Shipping method to the list of available shipping methods.
	 *
	 * @param array $methods The current list of shipping methods.
	 */
	function add_autoship_shipping_freeshipping_method( $methods ) {
		// Only add the new class if the functionality is enabled.
		$free_shipping_option = get_option( 'autoship_free_shipping' );
		if ( ( 'checkout+autoship' === $free_shipping_option ) ) {
			$methods['autoship_free_shipping'] = 'Autoship_FreeShipping_Method';
		}

		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'add_autoship_shipping_freeshipping_method' );
}
