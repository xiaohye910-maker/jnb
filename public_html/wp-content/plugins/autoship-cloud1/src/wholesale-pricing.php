<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The functional code that handles the wholesale integration.
 *
 * @package Autoship
 * @since 1.0.0
 */

/**
 * The core Wholesale Pricing Autoship Cloud plugin class.
 * This class uses methods from the WooCommerce Wholesale Prices Plugin classes
 * and filters from the Autoship Cloud Plugin.
 *
 * @since      1.0.0
 * @package    Autoship_Cloud_Wholesale_Pricing
 * @subpackage Autoship_Cloud_Wholesale_Pricing/includes
 * @author     Patterns In the Cloud LLC
 */
class Autoship_Cloud_Wholesale_Pricing {

	/**
	 * Class instance.
	 *
	 * @since    1.0.0
	 * @see get_instance()
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initial Setup
	 *
	 * @since    1.0.0
	 */
	public function load() {
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Access this instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 * @since    1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// Add the opt-in option to the edit product screen.
		add_action( 'autoship_after_print_product_custom_fields', array( $this, 'schedule_order_wholesale_discount' ), 10, 1 );
		add_action( 'autoship_after_print_variable_product_custom_fields', array( $this, 'schedule_order_variation_wholesale_discount' ), 10, 3 );

		// Saves the custom option on edit update.
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_custom_options' ), 10, 1 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_custom_options' ), 10, 2 );

		// Bulk actions.
		add_action( 'admin_init', array( $this, 'download_export_file' ) );
		add_filter( 'autoship_batch_update_products_actions', array( $this, 'add_update_scheduled_orders_wholesale_pricing_action' ), 15 );

		add_action( 'autoship_after_autoship_admin_utilities', array( $this, 'autoship_wholesale_bulk_admin_utilities_html' ), 10, 2 );
		add_action( 'autoship_set_latest_export_file', array( $this, 'set_wholesale_pricing_updates_export_file' ) );
		add_filter( 'autoship_batch_update_products_autoship_bulk_update_scheduled_orders_wholesale_pricing_args', array( $this, 'autoship_batch_update_scheduled_orders_wholesale_pricing_added_args' ), 10, 1 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

			// If you want use WooCommerce functions, do that after WooCommerce is loaded.
			add_action( 'woocommerce_init', array( $this, 'define_woo_dependent_hooks' ) );
		}
	}

	/**
	 * Register the Woo Commerce Filters and Hooks.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function define_woo_dependent_hooks() {

		/* Hooks to process wholesale pricing on the Native UI */
		add_filter( 'autoship_create_scheduled_order_item_data', array( $this, 'autoship_add_schedule_items_wholesale_price' ), 10, 5 );
		add_filter( 'autoship_update_schedule_items_changes', array( $this, 'autoship_update_schedule_items_wholesale_price' ), 10, 5 );
		add_filter( 'autoship_scheduled_order_form_item_add_display_name', array( $this, 'autoship_product_dropdown_get_price_range' ), 10, 5 );
		add_filter( 'autoship_scheduled_order_form_add_item_price', array( $this, 'autoship_added_product_html_get_price' ), 10, 3 );
		add_filter( 'autoship_scheduled_order_form_item_quantity_field', array( $this, 'autoship_enable_schedule_items_wholesale_price_input' ), 10, 3 );

		add_filter( 'autoship_all_prices_array', array( $this, 'autoship_all_prices_array_w_wholesale' ), 10, 3 );
		add_filter( 'autoship_discount_checkout_and_recurring_same', array( $this, 'autoship_checkout_recurring_discount_wholesale_string' ), 10, 4 );
		add_filter( 'autoship_product_discount_data', array( $this, 'autoship_adjust_displayed_wholesale_string_price' ), 10, 2 );

		// Hooks to process wholesale pricing on the Cart & Checkout.
		add_filter( 'wwp_filter_get_custom_product_type_wholesale_price', array( $this, 'autoship_adjust_cart_item_general_wholesale_price' ), 10, 4 );
		add_filter( 'wwp_filter_wholesale_price_cart', array( $this, 'autoship_adjust_cart_item_wholesale_price' ), 10, 5 );
		add_filter( 'autoship_get_scheduled_order_data_full_item_data', array( $this, 'autoship_update_scheduled_order_full_item_data' ), 10, 3 );
	}

	/**
	 * Checks for Empty Value ( Checks empty not 0 strings and NULL )
	 *
	 * @param mixed $val The value to check.
	 * @return bool True if the value is empty or not set, else false.
	 *
	 * @since     1.0.0
	 * @static
	 * @access public
	 */
	public function NonZeroEmpty( $val ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return ! isset( $val ) || ( empty( $val ) && 0 !== $val );
	}

	// ==========================================================
	// Save Functions
	// ==========================================================

	/**
	 * Saves the Autoship Custom Field(s) for a Products and Variations
	 *
	 * @param int $id The variation id.
	 * @param int $index Optional The current variation index. Default NULL.
	 *
	 * @since 1.0.0
	 * @access public
	 * @see woocommerce_process_product_meta
	 * @see woocommerce_save_product_variation
	 */
	public function save_product_custom_options( $id, $index = null ) {

		$options = array(
			'_autoship_schedule_order_wholesale_discount' => 'floatval',
		);

		$values = array();

		// If the index is NULL then this is a product and not a variation. Variations have indexes.
		if ( null === $index ) {
			foreach ( $options as $option => $sanitization ) {
				$values[ $option ] = isset( $_POST[ $option ] ) ? $sanitization( $_POST[ $option ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
		} else {
			foreach ( $options as $option => $sanitization ) {
				$values[ $option ] = isset( $_POST[ $option ] ) && isset( $_POST[ $option ][ $index ] ) ? $sanitization( $_POST[ $option ][ $index ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
		}

		// Save all the new values.
		$this->set_product_wholesale_discount( $id, $values['_autoship_schedule_order_wholesale_discount'] );
	}

	// ==========================================================
	// Set Functions
	// ==========================================================

	/**
	 * Sets the Scheduled Order Wholesale Discount Option for a product
	 *
	 * @param int|WC_Product $product_id The current woocommerce product.
	 * @param string         $value The value to update the option to.
	 *
	 * @return bool True if successful else false.
	 * @since     1.0.0
	 *
	 * @access public
	 */
	public function set_product_wholesale_discount( $product_id, $value = 0 ) {
		return update_post_meta( $product_id, '_autoship_schedule_order_wholesale_discount', $value );
	}

	// ==========================================================
	// Get Functions
	// ==========================================================

	/**
	 * Gets the Scheduled Order Wholesale Discount Option for a product
	 *
	 * @param int|WC_Product $product_id The current woocommerce product.
	 *
	 * @return float The percentge discount
	 * @since     1.0.0
	 *
	 * @access public
	 */
	public function get_product_wholesale_discount( $product_id ) {
		return get_post_meta( $product_id, '_autoship_schedule_order_wholesale_discount', true );
	}

	/************************************
	 *         Edit Product Page
	 *************************************/

	/**
	 * Outputs the Wholesale Discount Field for variations
	 *
	 * @param int     $loop The current loop index.
	 * @param array   $variation_data The current variation data.
	 * @param WP_Post $variation The current variation post object.
	 *
	 * @return void
	 * @since  2.0.0
	 *
	 * @access public
	 */
	public function schedule_order_variation_wholesale_discount( $loop, $variation_data, $variation ) {
		?>
		<div class="autoship_scheduled_order_wholesale_extension_settings data">

			<h4><?php echo esc_html( __( 'Autoship Cloud Wholesale Settings', 'autoship' ) ); ?></h4>

			<div class="options_group autoship_scheduled_order_wholesale_extension">
				
				<?php
				woocommerce_wp_text_input(
					array(
						'id'          => '_autoship_schedule_order_wholesale_discount' . $loop,
						'name'        => "_autoship_schedule_order_wholesale_discount[{$loop}]",
						'label'       => __( 'Discount Percentage', 'autoship' ),
						'description' => __( 'Enter an additional discount percentage to apply to the checkout & recurring price for Wholesale Autoship products.', 'autoship' ),
						'placeholder' => __( '(Optional)', 'autoship' ),
						'data_type'   => 'decimal',
						'value'       => $this->get_product_wholesale_discount( $variation->ID ),
						'desc_tip'    => true,
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Outputs the Wholesale Discount Field
	 *
	 * @param WC_Product $_product The current woocommerce product.
	 *
	 * @return void
	 * @since     2.0.0
	 *
	 * @access public
	 */
	public function schedule_order_wholesale_discount( $_product ) {
		?>
		<div class="autoship_scheduled_order_wholesale_extension_settings autoship-sync-active-option-group">

			<h4><?php echo esc_html( __( 'Autoship Cloud Wholesale Settings', 'autoship' ) ); ?></h4>

			<div class="options_group autoship_scheduled_order_wholesale_extension">
				
				<?php
				// Assigned Group Ids.
				woocommerce_wp_text_input(
					array(
						'id'          => '_autoship_schedule_order_wholesale_discount',
						'label'       => __( 'Discount Percentage', 'autoship' ),
						'description' => __( 'Enter an additional discount percentage to apply to the checkout & recurring price for Wholesale Autoship products.', 'autoship' ),
						'placeholder' => __( '(Optional)', 'autoship' ),
						'data_type'   => 'decimal',
						'value'       => $this->get_product_wholesale_discount( $_product->get_id() ),
						'desc_tip'    => true,
					)
				);
				?>
			</div>

		</div>
		
		<?php
	}

	/************************************
	 *         Utilities
	 *************************************/

	/**
	 * Gets the automatic wholesale discount percentage for
	 * autoship items
	 *
	 * @param int    $product_id The current product id.
	 * @param string $user_wholesale_role The current users wholesale role.
	 *
	 * @return float The discount
	 * @since  1.0.1
	 */
	public function autoship_get_scheduled_discount_percentage( $product_id, $user_wholesale_role ) {
		$percentage = $this->get_product_wholesale_discount( $product_id );

		return apply_filters( 'autoship_wholesale_scheduled_discount_percentage', empty( $percentage ) ? 0 : $percentage / 100, $product_id, $user_wholesale_role );
	}

	/**
	 * Get the sites wholesale settings.
	 *
	 * @param int|null $user_id The user ID to get the settings for. If null, will use the current user ID.
	 *
	 * @return array The Site Settings.
	 * @since     1.0.0
	 */
	public function autoship_site_wholesale_settings( $user_id = null ) {

		if ( ! isset( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return array(
			'minimum_cart_items'                     => trim( get_option( 'wwpp_settings_minimum_order_quantity' ) ),
			'minimum_cart_price'                     => trim( get_option( 'wwpp_settings_minimum_order_price' ) ),
			'minimum_requirements_conditional_logic' => get_option( 'wwpp_settings_minimum_requirements_logic' ),
			'override_per_wholesale_role'            => get_option( 'wwpp_settings_override_order_requirement_per_role', false ),
			'per_wholesale_role_order_requirement'   => get_option( 'wwpp_option_wholesale_role_order_requirement_mapping', array() ),
			'shop_base_currency'                     => get_option( 'woocommerce_currency' ),
			'user_min_order_qty_override'            => get_user_meta( $user_id, 'wwpp_override_min_order_qty', true ),
			'user_min_order_qty'                     => get_user_meta( $user_id, 'wwpp_min_order_qty', true ),
			'user_min_order_price_override'          => get_user_meta( $user_id, 'wwpp_override_min_order_price', true ),
			'user_min_order_price'                   => get_user_meta( $user_id, 'wwpp_min_order_price', true ),
			'user_min_order_logic'                   => get_user_meta( $user_id, 'wwpp_min_order_logic', true ),
		);
	}

	/**
	 * Return product wholesale price for a given wholesale user role.
	 *
	 * @param int   $product_id Product id.
	 * @param array $user_wholesale_role Array of user wholesale roles.
	 *
	 * @see WWP_Wholesale_Prices::get_product_raw_wholesale_price()
	 *
	 * @since     1.0.0
	 */
	public function autoship_calculate_schedule_product_wholesale_price( $product_id, $user_wholesale_role ) {

		if ( ! class_exists( 'WWP_Wholesale_Prices' ) ) {
			return null;
		}

		$prices = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3( $product_id, $user_wholesale_role );

		return apply_filters( 'autoship_calculate_schedule_product_wholesale_price', trim( $prices['wholesale_price'] ), $product_id, $user_wholesale_role );
	}

	/**
	 * Return wholesale user role for a user.
	 *
	 * @param int $wp_user The WC User object.
	 *
	 * @return string The supplied user's WholeSale Role.
	 * @since  1.0.0
	 * @see WWP_Wholesale_Roles->getUserWholesaleRole()
	 */
	public function autoship_get_user_wholesale_role( $wp_user = null ) {

		if ( ! class_exists( 'WWP_Wholesale_Roles' ) ) {
			return '';
		}

		$wp_user = $wp_user && ( $wp_user instanceof WP_User ) ? $wp_user : null;

		$wwp_roles = new WWP_Wholesale_Roles();

		// Get the users Wholesale Role.
		return apply_filters( 'autoship_get_user_wholesale_role', $wwp_roles->getUserWholesaleRole( $wp_user ), $wp_user );
	}

	/**
	 * Checks and Retrieves the Wholesale Base Price if it exists and this
	 * user is a wholesale user
	 *
	 * @param int          $product_id The current product id.
	 * @param WP_User|null $wp_user The current user object. If null, will use the current user.
	 *
	 * @return float|null The wholesale price else null
	 * @since 1.0.1
	 */
	public function autoship_retrieve_wholesale_base_price( $product_id, $wp_user = null ) {

		$wholesale = null;

		// Get the User's Wholesale Role.
		$user_wholesale_role = $this->autoship_get_user_wholesale_role( $wp_user );

		// No Wholesale Role Skip.
		if ( ! empty( $user_wholesale_role ) ) {

			// Get the WholeSale Price for this user. If no Wholesale price then the standard price should be used.
			$wholesale = $this->autoship_calculate_schedule_product_wholesale_price( $product_id, $user_wholesale_role );

		}

		return $this->NonZeroEmpty( $wholesale ) ? null : $wholesale;
	}

	/**
	 * Adjusts the wholesale price for a user, if no wholesale price exists the
	 * supplied regular price will ne used.
	 *
	 * @param int          $product_id The current product id.
	 * @param float|null   $base_wholesale_price . The base wholesale price.
	 * @param float|null   $regular_price Optional. The current non-wholesale price.
	 * @param WP_User|null $wp_user The current user object. If null, will use the current user.
	 *
	 * @return float|null The wholesale price else null
	 * @since 1.0.1
	 */
	public function autoship_adjust_wholesale_price( $product_id, $base_wholesale_price = null, $regular_price = null, $wp_user = null ) {

		// Get the User's Base Wholesale Price if it exists.
		if ( is_null( $base_wholesale_price ) ) {
			$base_wholesale_price = $this->autoship_retrieve_wholesale_base_price( $product_id, $wp_user );
		}

		// No Wholesale Role Price Skip.
		if ( ! is_null( $base_wholesale_price ) ) {

			// Get the User's Wholesale Role.
			$user_wholesale_role = $this->autoship_get_user_wholesale_role( $wp_user );

			// Get the WholeSale Price for this user. If no Wholesale price then the standard price should be used.
			$regular_price = floatval( $base_wholesale_price ) - ( floatval( $base_wholesale_price ) * $this->autoship_get_scheduled_discount_percentage( $product_id, $user_wholesale_role ) );

		}

		// Check for empty string in case it was pulled from
		// metadata originally.
		return $this->NonZeroEmpty( $regular_price ) ? null : $regular_price;
	}

	/**
	 * Return Title with wholesale price range for a product drop down.
	 *
	 * @param string $dropdown The current title and price string.
	 * @param string $title The current product title to include in the dropdown.
	 * @param float  $originalprice The current regular price from QPilot.
	 * @param float  $saleprice The sale price from QPilot.
	 * @param array  $product The product data from QPilot.
	 *
	 * @return string The New Product Select Name for the drop down.
	 * @since     1.0.0
	 * @see WWP_Wholesale_Roles->getUserWholesaleRole()
	 */
	public function autoship_product_dropdown_get_price_range( $dropdown, $title, $originalprice, $saleprice, $product ) {

		if ( ! class_exists( 'WWP_Wholesale_Roles' ) || ! class_exists( 'WWP_Wholesale_Roles' ) ) {
			return $dropdown;
		}

		$uses_wc_data = autoship_filter_schedulable_products_use_wc_data();
		$product      = $uses_wc_data ? $product : wc_get_product( $product['id'] );

		// Create the new item to run through the pricing method.
		$new_item = array(
			'price'     => $originalprice,
			'salePrice' => null,
			'quantity'  => 0,
		);

		// Get the new Wholesale price with the added discount if entered.
		$prices = $this->autoship_filter_add_schedule_items_wholesale_price( $new_item, '', 1, $product->get_id(), $product->get_id() );

		// Reformat the value in the drop down.
		$new_dropdown = $dropdown;
		if ( isset( $prices['salePrice'] ) && ! empty( $prices['salePrice'] ) ) {

			$new_dropdown = sprintf( $dropdown . ' %s %s', trim( apply_filters( 'wwp_filter_wholesale_price_title_text', __( 'Wholesale Price:', 'woocommerce-wholesale-prices' ) ) ), autoship_get_formatted_price( $prices['salePrice'] ) );

		}

		return apply_filters( 'autoship_product_dropdown_get_price_range', $new_dropdown, $dropdown, $new_item, $prices, $product->get_id() );
	}

	/**
	 * Adjusts the html input for WholeSale items to allow for current value.
	 * The wholesale plugin currently overrides the value displayed in the
	 *
	 * @param string $product_quantity The input html string.
	 * @param array  $item The current Scheduled Order Item.
	 * @param array  $scheduled_item The Raw Schduled Order Item from QPilot.
	 *
	 * @return string The filtered html string
	 * @since     1.0.0
	 */
	public function autoship_enable_schedule_items_wholesale_price_input( $product_quantity, $item, $scheduled_item ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( ( $item['is_sold_individually'] ) || ( 'outofstock' === $item['stock_status'] ) ) {
			return $product_quantity;
		}

		// Need more efficient way.
		$dom = new DOMDocument();
		$dom->loadHTML( $product_quantity );

		$xpath = new DOMXPath( $dom );
		$tags  = $xpath->query( '//input[@title="Qty"]' );

		// One time purchase has no Qty input so we can skip.
		if ( $tags->length <= 0 ) {
			return $product_quantity;
		}

		$value = trim( $tags[0]->getAttribute( 'value' ) );

		if ( $value !== $item['qty'] ) {
			$product_quantity = str_replace( 'value="' . $value . '"', 'value="' . $item['qty'] . '"', $product_quantity );
		}

		return $product_quantity;
	}

	/**
	 * Filter if apply wholesale price per Scheduled Order Item level. Validate if Order Item level requirements are meet or not.
	 *
	 * @param boolean $apply_price Boolean flag that determines either to apply or not wholesale pricing.
	 * @param int     $items_total_price Total $ Amount for this item.
	 * @param array   $user_wholesale_role The users wholesale role.
	 * @param int     $item_total Total Qty for this Item.
	 * @param int     $product_id The current product id.
	 *
	 * @return array|boolean Array of error notices on if order fails wholesale requirements,
	 *                       boolean true if passed and should apply wholesale pricing.
	 * @since 1.0.0
	 */
	public function autoship_validate_wholesale_price( $apply_price, $items_total_price, $user_wholesale_role, $item_total = 1, $product_id = false ) {

		global $wc_wholesale_prices_premium;
		$notice             = array();
		$wholesale_settings = $this->autoship_site_wholesale_settings();
		extract( $wholesale_settings ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		$minimum_order_items = null;
		$minimum_order_price = null;
		$product             = wc_get_product( $product_id );
		$active_currency     = get_woocommerce_currency();

		if ( $product_id && $this->usesPremium() ) {
			if ( $minimum_cart_items && is_numeric( $minimum_cart_items ) ) {
				$moq = (int) $minimum_cart_items;
			} else {
				$moq = get_post_meta( $product_id, $user_wholesale_role[0] . '_wholesale_minimum_order_quantity', true );
			}
			$minimum_order_items = ( is_numeric( $moq ) ) ? (int) $moq : null;

			if ( $minimum_cart_price && is_numeric( $minimum_cart_price ) ) {
				$minimum_order_price = (int) $minimum_cart_price;
			}
		}

		if ( 'yes' === $override_per_wholesale_role ) {
			if ( ! is_array( $per_wholesale_role_order_requirement ) ) {
				$per_wholesale_role_order_requirement = array();
			}

			if ( array_key_exists( $user_wholesale_role[0], $per_wholesale_role_order_requirement ) ) {

				// Use minimum order quantity set for this current wholesale role.
				$minimum_order_items                    = $per_wholesale_role_order_requirement[ $user_wholesale_role[0] ]['minimum_order_quantity'];
				$minimum_order_price                    = $per_wholesale_role_order_requirement[ $user_wholesale_role[0] ]['minimum_order_subtotal'];
				$minimum_requirements_conditional_logic = $per_wholesale_role_order_requirement[ $user_wholesale_role[0] ]['minimum_order_logic'];
			}
		}

		$user_min_order_qty_applied   = false;
		$user_min_order_price_applied = false;

		// Check if min order qty is overridden per wholesale user.
		if ( 'yes' === $user_min_order_qty_override ) {
			if ( is_numeric( $user_min_order_qty ) || empty( $user_min_order_qty ) ) {
				$minimum_order_items        = $user_min_order_qty;
				$user_min_order_qty_applied = true;
			}
		}

		// Check if min order price is overridden per wholesale user.
		if ( 'yes' === $user_min_order_price_override ) {
			if ( is_numeric( $user_min_order_price ) || empty( $user_min_order_price ) ) {
				$minimum_order_price          = $user_min_order_price;
				$user_min_order_price_applied = true;
			}
		}

		// Check if min order logic is overridden per wholesale user.
		if ( $user_min_order_qty_applied && $user_min_order_price_applied ) {
			if ( in_array( $user_min_order_logic, array( 'and', 'or' ), true ) ) {
				$minimum_requirements_conditional_logic = $user_min_order_logic;
			}
		}

		/**
		 * Make min order price requirement compatible with "Aelia Currency Switcher" plugin
		 */
		if ( WWP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

			if ( $active_currency !== $shop_base_currency ) {
				$minimum_order_price = WWP_ACS_Integration_Helper::convert( $minimum_order_price, $active_currency, $shop_base_currency );
			}
		}

		if ( isset( $minimum_order_items ) || isset( $minimum_order_price ) ) {

			if ( is_numeric( $minimum_order_items ) && ( ! is_numeric( $minimum_order_price ) || 0 === strcasecmp( $minimum_order_price, '' ) || ( (float) $minimum_order_price <= 0 ) ) ) {
				$wholesale_price           = $this->autoship_adjust_wholesale_price( $product_id );
				$formatted_wholesale_price = $wc_wholesale_prices_premium->wwpp_wholesale_prices->get_product_shop_price_with_taxing_applied( $product, $wholesale_price, array( 'currency' => $active_currency ), $user_wholesale_role );

				$minimum_order_items = (int) $minimum_order_items;
				if ( $item_total < $minimum_order_items ) {
					$message = sprintf(
						// translators: %1$s and %4$s are HTML tags, %2$s is the minimum order quantity, %3$s is the product title, and %5$s is the wholesale price.
						__( '%1$sYou did not meet the minimum order quantity %2$s of the product %3$s to activate wholesale pricing %4$s. Please increase quantities to the cart to activate adjusted pricing.', 'autoship' ),
						'<span class="wwpp-notice">',
						'<b>(' . $minimum_order_items . ' items)</b>',
						'<b>' . $product->get_title() . '</b>',
						'<b>(' . $formatted_wholesale_price . ')</b>',
						'</span>'
					);
					$notice = apply_filters(
						'autoship_filter_wholesale_price_min_quantity_requirement_failure_notice',
						array(
							'type'    => 'notice',
							'message' => $message,
						),
						$item_total,
						$minimum_order_items,
						$items_total_price,
						$minimum_order_price,
						$wholesale_settings
					);
				}
			} elseif ( is_numeric( $minimum_order_price ) && ( ! is_numeric( $minimum_order_items ) || 0 === strcasecmp( $minimum_order_items, '' ) || ( (int) $minimum_order_items <= 0 ) ) ) {
				$minimum_order_price = (float) $minimum_order_price;
				if ( $items_total_price < $minimum_order_price ) {
					$message = sprintf(
						// translators: %1$s and %4$s are HTML tags, %2$s is the minimum order subtotal, %3$s is the product title, and %5$s is the wholesale price.
						__( '%1$sYou have not met the minimum order subtotal of %2$s to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is %3$s%4$s', 'woocommerce-wholesale-prices-premium' ),
						'<span class="wwpp-notice">',
						'<b>(' . wc_price( $minimum_order_price ) . ')</b>',
						'<b>(' . wc_price( $items_total_price ) . ')</b>',
						'</span>'
					);

					$notice = apply_filters(
						'autoship_filter_wholesale_price_min_subtotel_requirement_failure_notice',
						array(
							'type'    => 'notice',
							'message' => $message,
						),
						$item_total,
						$minimum_order_items,
						$items_total_price,
						$minimum_order_price,
						$wholesale_settings
					);
				}
			} elseif ( is_numeric( $minimum_order_price ) && is_numeric( $minimum_order_items ) ) {

				if ( strcasecmp( $minimum_requirements_conditional_logic, 'and' ) === 0 ) {
					if ( $item_total < $minimum_order_items || $items_total_price < $minimum_order_price ) {
						$message = sprintf(
							// translators: %1$s and %4$s are HTML tags, %2$s is the minimum order quantity, %3$s is the product title, and %5$s is the wholesale price.
							__( '%1$sYou have not met the minimum order quantity of %2$s and minimum order subtotal of %3$s to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is %4$s%5$s', 'woocommerce-wholesale-prices-premium' ),
							'<span class="wwpp-notice">',
							'<b>(' . $minimum_cart_items . ')</b>',
							'<b>(' . wc_price( $minimum_order_price ) . ')</b>',
							'<b>(' . wc_price( $items_total_price ) . ')</b>',
							'</span>'
						);

						$notice = apply_filters(
							'autoship_filter_wholesale_price_min_quantity_and_min_subtotel_requirement_failure_notice',
							array(
								'type'    => 'notice',
								'message' => $message,
							),
							$item_total,
							$minimum_order_items,
							$items_total_price,
							$minimum_order_price,
							$wholesale_settings
						);
					}
				} elseif ( $item_total < $minimum_order_items && $items_total_price < $minimum_order_price ) {
					$message = sprintf(
						// translators: %1$s and %4$s are HTML tags, %2$s is the minimum order quantity, %3$s is the product title, and %5$s is the wholesale price.
						__( '%1$sYou have not met the minimum order quantity of %2$s or minimum order subtotal of %3$s to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is %4$s%5$s', 'woocommerce-wholesale-prices-premium' ),
						'<span class="wwpp-notice">',
						'<b>(' . $minimum_order_items . ')</b>',
						'<b>(' . wc_price( $minimum_order_price ) . ')</b>',
						'<b>(' . wc_price( $items_total_price ) . ')</b>',
						'</span>'
					);

					$notice = apply_filters(
						'autoship_filter_wholesale_price_min_quantity_and_min_subtotel_requirement_failure_notice',
						array(
							'type'    => 'notice',
							'message' => $message,
						),
						$item_total,
						$minimum_order_items,
						$items_total_price,
						$minimum_order_price,
						$wholesale_settings
					);
				}
			}
		}

		$notice = apply_filters( 'autoship_filter_wholesale_price_requirement_failure_notice', $notice, $minimum_order_items, $minimum_order_price, $item_total, $items_total_price, $user_wholesale_role );

		return ! empty( $notice ) ? $notice : $apply_price;
	}

	/**
	 * Checks if Wholesale Prices Premium pugin in use.
	 *
	 * @return bool True if plugin class is defined, false otherwise.
	 */
	private function usesPremium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid,WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return class_exists( 'WooCommerceWholeSalePricesPremium' );
	}

	/************************************
	 *         Cart Adjustments
	 *************************************/

	/**
	 * Adjusts the Items if needed for the Autoship Price
	 *
	 * @param float   $wholesale_price The current wholesale price.
	 * @param array   $cart_item The current cart item data.
	 * @param array   $user_wholesale_role The current users wholesale role.
	 * @param WC_Cart $cart_object The current cart object.
	 */
	public function autoship_adjust_cart_item_general_wholesale_price( $wholesale_price, $cart_item, $user_wholesale_role, $cart_object ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Check if Autoship options are selected and if not.
		if ( ! isset( $cart_item['autoship_frequency_type'] ) || empty( $cart_item['autoship_frequency_type'] ) || ! isset( $cart_item['autoship_frequency'] ) || empty( $cart_item['autoship_frequency'] ) ) {
			return $wholesale_price;
		}

		return $this->autoship_adjust_wholesale_price( $product_id, $wholesale_price );
	}

	/**
	 * Return adjusted product wholesale price for a given wholesale user role.
	 *
	 * @param array   $values Array containing the price and type.
	 * @param int     $product_id Product id.
	 * @param array   $user_wholesale_role Array of user wholesale roles.
	 * @param array   $cart_item Cart item data.
	 * @param WC_Cart $cart_object The current cart object.
	 *
	 * @return string Filtered wholesale price.
	 */
	public function autoship_adjust_cart_item_wholesale_price( $values, $product_id, $user_wholesale_role, $cart_item, $cart_object ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Check if Autoship options are selected and if not.
		if ( ! isset( $cart_item['autoship_frequency_type'] ) || empty( $cart_item['autoship_frequency_type'] ) || ! isset( $cart_item['autoship_frequency'] ) || empty( $cart_item['autoship_frequency'] ) ) {
			return $values;
		}

		$values['wholesale_price'] = $this->autoship_adjust_wholesale_price( $product_id, $values['wholesale_price'] );

		return $values;
	}

	/************************************
	 *         Native UI Adjustments
	 *************************************/

	/**
	 * Adjust the price for the wholesale price.
	 *
	 * @param string $formatted_amount The current formatted price string.
	 * @param array  $item The current item data array.
	 * @param array  $product The product data from QPilot.
	 *
	 * @return string The New formatted amount.
	 * @since  1.0.0
	 */
	public function autoship_added_product_html_get_price( $formatted_amount, $item, $product ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Create the new item to run through the pricing method.
		$new_item = array(
			'price'     => $item['price'],
			'salePrice' => null,
			'quantity'  => $item['min_input'],
		);

		// Get the new Wholesale price with the added discount if entered.
		$prices = $this->autoship_add_schedule_items_wholesale_price( $new_item, '', 1, $item['wc_product_id'], $item['wc_product_id'] );

		if ( isset( $prices['salePrice'] ) && ! empty( $prices['salePrice'] ) ) {
			return autoship_get_formatted_price( $prices['salePrice'] );
		}

		return $formatted_amount;
	}

	/**
	 * Adjusts the price for new order items added in the Autoship Scheduled Order UI.
	 *
	 * @param array  $new_item An array of the new scheduled order items being added.
	 * @param string $frequency_type The current frequency type.
	 * @param int    $frequency The current frequency.
	 * @param int    $product_id The current autoship product id.
	 * @param int    $external_id The current wc product id.
	 *
	 * @return array The new items after adjustment
	 * @since     1.0.0
	 *
	 * The new items array should look like this.
	 * {
	 *      'quantity'          => $qty,
	 *      "ScheduledOrderId"  => $order_id,
	 *      "ProductId"         => $product_id,
	 *      "Price"             => $item_price,
	 *      'salePrice'         => $item_sale_price,
	 *      'quantity'          => $qty
	 * }
	 */
	public function autoship_add_schedule_items_wholesale_price( $new_item, $frequency_type, $frequency, $product_id, $external_id ) {
		// Skip when product is "added" to SO, but Update items button is not pressed yet
		// Prevents "fake" notice to be shown or duplicated notice.
		if ( $new_item['quantity'] <= 0 ) {
			return $new_item;
		}
		// If you need a Product object for the above.
		$product = wc_get_product( $external_id );

		// Itterate throught the current scheduled items.
		$autoship            = array();
		$autoship['price']   = $new_item['price'];
		$autoship['type']    = $product->is_type( 'simple' ) ? 'simple' : 'variation';
		$autoship['qty']     = $new_item['quantity'];
		$autoship['id']      = $external_id;
		$autoship['rule_id'] = 'simple' === $autoship['type'] ? $external_id : wp_get_post_parent_id( $external_id );

		// Get the users Wholesale Role.
		$user_wholesale_role = $this->autoship_get_user_wholesale_role();

		// Apply the wholesale pricing adjustment based on the products rule sets.
		$autoship['adjusted_price'] = $this->autoship_adjust_wholesale_price( $autoship['id'] );

		// No Wholesale price - use the current Prices else use wholesale as sale prices.
		if ( ! $this->NonZeroEmpty( $autoship['adjusted_price'] ) && $autoship['adjusted_price'] !== $autoship['price'] ) {

			// Validate the price is correct for this quantity.
			$apply = $this->autoship_validate_wholesale_price( true, round( $autoship['adjusted_price'] * $autoship['qty'], 2 ), $user_wholesale_role, $autoship['qty'], $external_id );

			if ( true === $apply ) {
				$new_item['salePrice'] = $autoship['adjusted_price'];
			} else {
				wc_add_notice( $apply['message'], $apply['type'] );
			}
		}

		return $new_item;
	}

	/**
	 * Adjusts the price for Add item dropdown in the Autoship Scheduled Order UI.
	 *
	 * @param array  $new_item An array of the new scheduled order items being added.
	 * @param string $frequency_type The current frequency type.
	 * @param int    $frequency The current frequency.
	 * @param int    $product_id The current autoship product id.
	 * @param int    $external_id The current wc product id.
	 * @return array The new items after adjustment
	 * @since     1.0.0
	 *
	 * The new_items An array of the new scheduled order items being added.
	 *      'quantity'          => $qty,
	 *      "ScheduledOrderId"  => $order_id,
	 *      "ProductId"         => $product_id,
	 *      "Price"             => $item_price,
	 *      'salePrice'         => $item_sale_price,
	 *      'quantity'          => $qty
	 */
	public function autoship_filter_add_schedule_items_wholesale_price( $new_item, $frequency_type, $frequency, $product_id, $external_id ) {
		// If you need a Product object for the above.
		$product = wc_get_product( $external_id );

		// Itterate throught the current scheduled items.
		$autoship            = array();
		$autoship['price']   = $new_item['price'];
		$autoship['type']    = $product->is_type( 'simple' ) ? 'simple' : 'variation';
		$autoship['qty']     = $new_item['quantity'];
		$autoship['id']      = $external_id;
		$autoship['rule_id'] = 'simple' === $autoship['type'] ? $external_id : wp_get_post_parent_id( $external_id );

		// Get the users Wholesale Role.
		$user_wholesale_role = $this->autoship_get_user_wholesale_role();

		// Apply the wholesale pricing adjustment based on the products rule sets.
		$autoship['adjusted_price'] = $this->autoship_adjust_wholesale_price( $autoship['id'] );

		// No Wholesale price - use the current Prices else use wholesale as sale prices.
		if ( ! $this->NonZeroEmpty( $autoship['adjusted_price'] ) && $autoship['adjusted_price'] !== $autoship['price'] ) {
			$new_item['salePrice'] = $autoship['adjusted_price'];
		}

		return $new_item;
	}

	/**
	 * Adjusts the price for Qty updates in the Autoship Scheduled Order UI.
	 *
	 * @param array  $updated_items An array of the current scheduled order items being updated.
	 * @param array  $original_items An array of the original scheduled order items.
	 * @param int    $order_id The QPilot order ID.
	 * @param string $action The Action being performed.
	 * @param array  $data The Scheduled Order Data.
	 *
	 * @return array The updated items after adjustment
	 * @since 1.0.0
	 */
	public function autoship_update_schedule_items_wholesale_price( $updated_items, $original_items, $order_id, $action, $data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Get the users Wholesale Role.
		$user_wholesale_role = $this->autoship_get_user_wholesale_role();

		// Not a Wholesale user skip.
		if ( empty( $user_wholesale_role ) ) {
			return $updated_items;
		}

		// Itterate throught the current scheduled items.
		$autoship = array();
		foreach ( $updated_items as $key => $updated_item ) {

			$autoship[ $key ]['price']     = $updated_item['price'];
			$autoship[ $key ]['saleprice'] = $updated_item['product']['salePrice'];
			$autoship[ $key ]['type']      = $updated_item['product']['Type'];
			$autoship[ $key ]['qty']       = $updated_item['quantity'];
			$autoship[ $key ]['id']        = $updated_item['product']['id'];
			$autoship[ $key ]['rule_id']   = 'simple' === $autoship[ $key ]['type'] ? $updated_item['product']['id'] : wp_get_post_parent_id( $updated_item['product']['id'] );
		}

		// Loop through the autoship products and adjust prices based on sets.
		foreach ( $autoship as $key => $item ) {

			// Get the WholeSale Price for this user. If no Wholesale price then the standard price should be used.
			$autoship[ $key ]['adjusted_price'] = $this->autoship_adjust_wholesale_price( $autoship[ $key ]['id'] );

			// No Wholesale price - use the current Prices else use wholesale as sale prices.
			if ( ! $this->NonZeroEmpty( $autoship[ $key ]['adjusted_price'] ) && $autoship[ $key ]['adjusted_price'] !== $autoship[ $key ]['price'] ) {

				$apply = $this->autoship_validate_wholesale_price( true, round( $autoship[ $key ]['adjusted_price'] * $item['qty'], 2 ), $user_wholesale_role, $item['qty'], $autoship[ $key ]['id'] );

				if ( true === $apply ) {
					$updated_items[ $key ]['salePrice'] = $autoship[ $key ]['adjusted_price'];
				} else {

					// Reset the price.
					$updated_items[ $key ]['salePrice'] = apply_filters( 'autoship_update_schedule_items_wholesale_price', null, $autoship[ $key ], $updated_items[ $key ], $user_wholesale_role );

					wc_add_notice( $apply['message'], $apply['type'] );
					continue;
				}
			}
		}

		return $updated_items;
	}

	/************************************
	 *         Product Page Adjustments
	 *************************************/

	/**
	 * Adds the additional wholesale rate and discount percentage to the price array
	 *
	 * @param array      $prices The current list of prices for the product.
	 * @param int        $product_id The current product id.
	 * @param wc_product $product The woocommerce product.
	 *
	 * @return array The adjusted prices array.
	 * @since 1.0.0
	 */
	public function autoship_all_prices_array_w_wholesale( $prices, $product_id, $product ) {
		// Do not run on edit SO page to prevent modifying product price before checks for requirements to get wholesale pricing.
		if ( ! is_product() && ! is_cart() ) {
			return $prices;
		}

		// if this user is a wholesale user and has a wholesale price
		// then we need to adjust all prices to be based off the new discounted price.
		// Get the User's Wholesale Role.
		$user_wholesale_role  = $this->autoship_get_user_wholesale_role();
		$base_wholesale_price = $this->autoship_retrieve_wholesale_base_price( $product_id );
		$wholesale_price      = ! empty( $user_wholesale_role ) ? $this->autoship_adjust_wholesale_price( $product_id, $base_wholesale_price ) : null;

		if ( ! is_null( $wholesale_price ) ) {

			// The Custom Autoship Checkout Price ( either wholesale or autoship ).
			$prices['autoship_checkout_price'] = $this->autoship_adjust_wholesale_price( $product_id, $base_wholesale_price, $prices['autoship_checkout_price'] );

			// The Custom Autoship Checkout Price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
			$prices['autoship_checkout_display_price'] = wc_get_price_to_display( $product, array( 'price' => $prices['autoship_checkout_price'] ) );

			// The final Checkout Price ( either Autoship or WC ).
			$prices['checkout_price'] = autoship_checkout_price(
				$product,
				array(
					'price'    => $prices['price'],
					'discount' => $prices['autoship_checkout_price'],
				)
			);

			// Record if the price is WC or Autoship.
			$prices['checkout_price_is_autoship'] = $prices['checkout_price'] !== $prices['price'];

			// The final Checkout Price ( either Autoship or WC ) including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
			$prices['checkout_display_price'] = wc_get_price_to_display( $product, array( 'price' => $prices['checkout_price'] ) );

			// The Custom Autoship Price for Recurring Orders ( either wholesale or autoship ).
			$prices['autoship_recurring_price'] = $this->autoship_adjust_wholesale_price( $product_id, $base_wholesale_price, $prices['autoship_recurring_price'] );

			// The Custom Autoship Price for Recurring Orders including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
			$prices['autoship_recurring_display_price'] = wc_get_price_to_display( $product, array( 'price' => $prices['autoship_recurring_price'] ) );

			// The regular price is the wholesale price in this instance since
			// recurring price is a discount on the wholesale.
			$prices['wholesale']     = $base_wholesale_price;
			$prices['regular_price'] = $prices['wholesale'];

			// The Products Regular Price if/when it's not on sale including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
			$prices['regular_display_price'] = wc_get_price_to_display( $product, array( 'price' => $prices['regular_price'] ) );

			// Get the calculated Percent Discount for the Checkout Price.
			$prices['autoship_percent_discount'] = autoship_percent_discount(
				$product,
				array(
					'price'    => $prices['regular_price'],
					'discount' => $prices['autoship_checkout_price'],
				)
			);

			// Get the calculated Percent Discount for the Recurring Price.
			$prices['autoship_percent_recurring_discount'] = autoship_percent_recurring_discount(
				$product,
				array(
					'price'    => $prices['regular_price'],
					'discount' => $prices['autoship_recurring_price'],
				)
			);

			$prices['wholesale_pct']        = 100 * $this->autoship_get_scheduled_discount_percentage( $product_id, $user_wholesale_role );
			$prices['wholesale_discount']   = $prices['wholesale'] - ( $prices['wholesale'] * ( $prices['wholesale_pct'] / 100 ) );
			$prices['wholesale_product_id'] = $product_id;
		}

		return $prices;
	}

	/**
	 * Adjusts the autoship notice if wholesale prices exist.
	 *
	 * @param string     $output The current autoship notice.
	 * @param array      $strings The array of notice components.
	 * @param array      $prices The current list of prices for the product.
	 * @param WC_Product $product The current product.
	 *
	 * @return string The adjusted autoship notice string
	 * @since 1.0.0
	 */
	public function autoship_checkout_recurring_discount_wholesale_string( $output, $strings, $prices, $product ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Create the new discount string if there's a wholesale price.
		$new_output = $strings['autoship_string'] . sprintf( ' <span class="autoship-checkout-percent-discount">%s%%</span> on Wholesale Orders', $prices['autoship_percent_discount'] ) . $strings['price_string'];

		// Check if there's a wholesale price for this user
		// and if so return the discount string.
		return ! isset( $prices['wholesale'] ) || is_null( $prices['wholesale'] ) ? $output : $new_output;
	}

	/**
	 * Adjusts the autoship displayed price html.
	 *
	 * @param array      $data The data to use to display.
	 * @param WC_Product $product The WooCommerce Product.
	 *
	 * @return array The adjusted data
	 * @since 1.0.0
	 */
	public function autoship_adjust_displayed_wholesale_string_price( $data, $product ) {

		if ( ! isset( $data['prices']['wholesale'] ) ) {
			return $data;
		}

		$data['discount_display_price']          = $data['discounted_price_html'];
		$data['discount_display_price_selector'] = 'simple' === $product->get_type() ? '.wholesale_price_container .woocommerce-Price-amount.amount > bdi' : '.wholesale_price_container > ins';

		return $data;
	}

	/************************************
	 *         Checkout Adjustments
	 *************************************/

	/**
	 * Adjusts the recurring price for scheduled order items at checkout.
	 *
	 * @param array         $scheduled_order_item_data The current scheduled order line item data.
	 * @param int           $order_id The WC Order id.
	 * @param WC_Order_Item $item The current WC_Order_Item data.
	 *
	 * @return array The updated scheduled order line item data.
	 * @since     1.0.0
	 */
	public function autoship_update_scheduled_order_full_item_data( $scheduled_order_item_data, $order_id, $item ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Get the User's Wholesale Role.
		$user_wholesale_role = $this->autoship_get_user_wholesale_role();

		// No Wholesale Role Skip.
		if ( empty( $user_wholesale_role ) ) {
			return $scheduled_order_item_data;
		}

		// Grab the wc product and info we need.
		$product_id = $scheduled_order_item_data['product']['id'];

		$autoship            = array();
		$product             = wc_get_product( $product_id );
		$autoship['id']      = $product_id;
		$autoship['price']   = $product->get_price();
		$autoship['qty']     = $scheduled_order_item_data['quantity'];
		$autoship['type']    = $product->is_type( 'simple' ) ? 'simple' : 'variation';
		$autoship['rule_id'] = 'simple' === $autoship['type'] ? $product_id : wp_get_post_parent_id( $product_id );

		// Get the WholeSale Price for this user. If no Wholesale price then the standard price should be used.
		$autoship['adjusted_price'] = $this->autoship_adjust_wholesale_price( $autoship['id'] );

		// No Wholesale price - use the current Prices else use wholesale as sale prices.
		if ( ! $this->NonZeroEmpty( $autoship['adjusted_price'] ) && $autoship['adjusted_price'] !== $autoship['price'] ) {

			$apply = $this->autoship_validate_wholesale_price( true, round( $autoship['adjusted_price'] * $autoship['qty'], 2 ), $user_wholesale_role, $autoship['qty'], $autoship['id'] );

			if ( true === $apply ) {
				$scheduled_order_item_data['salePrice'] = $autoship['adjusted_price'];
			}
		}

		return $scheduled_order_item_data;
	}

	/************************************
	 * Bulk Update Scheduled Orders Wholesale Pricing
	 *************************************/

	/**
	 * Check if the Pricing of an order has changed
	 *
	 * @param array $original The original Scheduled Order.
	 * @param array $news The new Scheduled Order to compare against.
	 *
	 * @return bool True if different else false.
	 */
	public function scheduled_order_items_wholesale_price_changed( $original, $news ) {
		// In this case we're assuming the structure is the same.
		$same = false;
		foreach ( $original['scheduledOrderItems'] as $key => $item ) {

			$same = $news['scheduledOrderItems'][ $key ]['salePrice'] !== $item['salePrice'];
			if ( $same ) {
				break;
			}
		}

		return $same;
	}

	/**
	 * Exports the WC Autoship Scheduled Orders to a CSV file.
	 *
	 * @param array $scheduled_order_data The original and new scheduled order data.
	 *
	 * @return int The total number of Scheduled Order lines exported
	 * @throws Exception If the file can't be opened or created.
	 */
	public function bulk_export_scheduled_order( $scheduled_order_data ) {

		// Pull the latest file and if it doesn't exist create it.
		$filename = $this->get_wholesale_pricing_updates_export_file();
		if ( empty( $filename ) ) {
			$filename = autoship_bulk_export_create_file();
		}

		// Grab the file path.
		$path = trailingslashit( autoship_get_export_directory() );

		try {

			// Open / Create the Export File.
			$file = fopen( $path . $filename, 'a+' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

			if ( ! $file ) {
				throw new Exception( 'file-open-failed', 500 );
			}

			$valid_lines = 0;

			// Go through each scheduled order and make a line for each with.
			foreach ( $scheduled_order_data['original']['scheduledOrderItems'] as $key => $item ) {

				// Only Export lines that are different & were updated.
				if ( ( $item['price'] === $scheduled_order_data['new']['scheduledOrderItems'][ $key ]['price'] ) && ( $item['salePrice'] === $scheduled_order_data['new']['scheduledOrderItems'][ $key ]['salePrice'] ) ) {
					continue;
				}

				$data                          = array();
				$data['ScheduledOrderId']      = $scheduled_order_data['original']['id'];
				$data['CustomerId']            = $scheduled_order_data['original']['customerId'];
				$data['Email']                 = $scheduled_order_data['original']['customer']['email'];
				$data['Status']                = $scheduled_order_data['original']['status'];
				$data['NextOccurenceDate']     = $scheduled_order_data['original']['nextOccurrenceUtc'];
				$data['FrequencyType']         = $scheduled_order_data['original']['frequencyType'];
				$data['Frequency']             = $scheduled_order_data['original']['frequency'];
				$data['FrequencyDisplayName']  = $scheduled_order_data['original']['frequencyDisplayName'];
				$data['ShippingFirstName']     = $scheduled_order_data['original']['shippingFirstName'];
				$data['ShippingLastName']      = $scheduled_order_data['original']['shippingLastName'];
				$data['OriginalExternalId']    = $scheduled_order_data['original']['originalExternalId'];
				$data['ScheduledOrderItem_id'] = $item['id'];
				$data['ProductId']             = $item['productId'];
				$data['ProductTitle']          = $item['product']['title'];
				$data['Quantity']              = $item['quantity'];
				$data['Price']                 = $item['price'];
				$data['SalePrice']             = $item['salePrice'];
				$data['NewPrice']              = $scheduled_order_data['new']['scheduledOrderItems'][ $key ]['price'];
				$data['NewSalePrice']          = $scheduled_order_data['new']['scheduledOrderItems'][ $key ]['salePrice'];

				$written = fputcsv( $file, $data );

				if ( $written ) {
					++$valid_lines;
				}
			}

			$file = fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		} catch ( Exception $e ) {
			// translators: %1$s is the file name, %2$s is the path to the file.
			return new WP_Error( 'file-open-failed', sprintf( __( 'CSV File %1$s can\'t be opened! Please check rights for %2$s.', 'autoship' ), $filename, $path ) );
		}

		return $valid_lines;
	}

	/**
	 * Saves the latest export file name
	 *
	 * @param string $file The latest export file name.
	 *
	 * @return bool
	 */
	public function set_wholesale_pricing_updates_export_file( $file = '' ) {
		return update_option( '__autoship_bulk_scheduled_orders_wholesale_price_file', $file );
	}

	/**
	 * Retrieves the latest export file name
	 *
	 * @return string
	 */
	public function get_wholesale_pricing_updates_export_file() {
		$lof = get_option( '__autoship_bulk_scheduled_orders_wholesale_price_file', '' );

		return $lof;
	}


	/**
	 * Saves the list of Scheduled Orders updated with Wholesale Prices
	 *
	 * @param array $orders The array of order ids to save.
	 *
	 * @return bool True if Success else false
	 */
	public function record_scheduled_orders_updated_with_wholesale_price( $orders = array() ) {
		return update_option( '__autoship_bulk_scheduled_orders_updated_with_wholesale_price_ext', $orders );
	}

	/**
	 * Retrieves the list of Scheduled Orders updated with Wholesale Prices
	 *
	 * @return array
	 */
	public function get_scheduled_orders_updated_with_wholesale_price() {
		$ids = get_option( '__autoship_bulk_scheduled_orders_updated_with_wholesale_price_ext', array() );

		return empty( $ids ) ? array() : $ids;
	}

	/**
	 * Retrieves and Applies Wholesale Pricing Rules to Scheduled Orders at Checkout
	 *
	 * @param array $scheduled_order The Scheduled Orders Data.
	 *
	 * @return array The Original Scheduled Order with Applies Wholesale Pricing rules applied.
	 */
	public function update_scheduled_order_with_wholesale_pricing( $scheduled_order ) {

		$wp_user = get_user_by( 'id', $scheduled_order['customerId'] );
		$wp_user = $wp_user ? $wp_user : null;

		foreach ( $scheduled_order['scheduledOrderItems'] as $key => $value ) {

			// If you need a Product object for the above.
			$product = wc_get_product( $value['product']['id'] );

			if ( ! $product || empty( $product ) ) {
				continue;
			}

			// Itterate throught the current scheduled items.
			$autoship            = array();
			$autoship['price']   = $value['price'];
			$autoship['type']    = $product->is_type( 'simple' ) ? 'simple' : 'variation';
			$autoship['qty']     = $value['quantity'];
			$autoship['id']      = $value['product']['id'];
			$autoship['rule_id'] = 'simple' === $autoship['type'] ? $value['product']['id'] : wp_get_post_parent_id( $value['product']['id'] );

			// Get the users Wholesale Role.
			$user_wholesale_role = $this->autoship_get_user_wholesale_role( $wp_user );

			if ( empty( $user_wholesale_role ) ) {
				continue;
			}

			// Apply the wholesale pricing adjustment based on the products rule sets.
			$autoship['adjusted_price'] = $this->autoship_adjust_wholesale_price( $autoship['id'], null, null, $wp_user );

			// No Wholesale price - use the current Prices else use wholesale as sale prices.
			if ( ! $this->NonZeroEmpty( $autoship['adjusted_price'] ) && $autoship['adjusted_price'] !== $autoship['price'] ) {
				$apply = $this->autoship_validate_wholesale_price( true, round( $autoship['adjusted_price'] * $autoship['qty'], 2 ), $user_wholesale_role, $autoship['qty'], $product->get_id() );
				if ( true === $apply ) {
					$scheduled_order['scheduledOrderItems'][ $key ]['salePrice'] = $autoship['adjusted_price'];
				}
			}
		}

		return $scheduled_order;
	}

	/**
	 * Retrieves the Download Link for a export file.
	 *
	 * @param string $file The log file name.
	 *
	 * @return string The url or empty string if none exists.
	 */
	public function get_export_file_download_link( $file ) {
		$path = autoship_admin_settings_tab_url( 'autoship-utilities' );

		return $path . '&autoship_download_wholesale_pricing_export_file=' . $file;
	}

	/**
	 * Adds the new Wholesale Pricing Bulk Update Action to the Autoship Cloud Utilities callbacks
	 *
	 * @param array $actions An array of current Bulk actions and their callback functions.
	 *
	 * @return array The filtered Actions and associated callback functions.
	 */
	public function add_update_scheduled_orders_wholesale_pricing_action( $actions ) {
		$actions['autoship_bulk_update_scheduled_orders_wholesale_pricing'] = 'autoship_bulk_update_scheduled_orders_wholesale_pricing';

		return $actions;
	}

	/**
	 * Searches for scheduled order(s) in QPilot
	 *
	 * @param int   $customer_id Optional. An Autoship customer id.
	 * @param int   $index Optional. The page index to retrieve. Default 1.
	 * @param array $params Optional. An array of search parameters.
	 *
	 * @type int $pageSize The default page size.  Default 100
	 * @type string $orderBy A product property to sort the results by
	 * @type string $order The Sort Direction the results should be returned ( DESC vs ASC )
	 * @type array $statusNames Array of Status names to search for.
	 * @type array $metadataKey Array of Order Metadata keys to search for.
	 * @type array $metadataValue Array of Order Metadata Values to search for.
	 * @type string $search A query string to search for.
	 * }
	 * @return array|WP_Error          Array of Scheduled order objects, total pages, and total count
	 *                                 WP_Error on failure.
	 *
	 * @since     1.2.4
	 * @uses QPilotClient::get_orders()
	 *
	 * If Page is not supplied it's assumed retrieve all orders.
	 */
	public function search_scheduled_orders( $customer_id = null, $index = 1, $params = array() ) {
		$params         = wp_parse_args( $params, array( 'pageSize' => 100 ) );
		$params['page'] = $index;

		$client = autoship_get_default_client();

		try {
			$orders = $client->get_orders( $customer_id, $params );
		} catch ( Exception $e ) {

			$notice = autoship_expand_http_code( $e->getCode() );

			if ( 404 === $e->getCode() ) {
				$orders  = new WP_Error( 'Order(s) Not Found', __( 'Orders could not be found in QPilot', 'autoship' ) );
				$message = isset( $customer_id ) ? sprintf( '%d Order(s) Not Found for Customer %d. Additional Details: %s', $e->getCode(), $customer_id, $e->getMessage() ) : sprintf( '%d Order(s) Not Found matching the search criteria. Additional Details: %s', $e->getCode(), $e->getMessage() );
				autoship_log_entry( __( 'Autoship Orders', 'autoship' ), $message );
			} else {
				$orders  = new WP_Error( 'Order Retrieval Failed', __( $notice['desc'], 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$message = isset( $customer_id ) ? sprintf( '%d Order Retrieval Failed for Customer %d. Additional Details: %s', $e->getCode(), $customer_id, $e->getMessage() ) : sprintf( '%d Order Retrieval Failed for the search criteria. Additional Details: %s', $e->getCode(), $e->getMessage() );
				autoship_log_entry( __( 'Autoship Orders', 'autoship' ), $message );
			}
		}

		if ( is_wp_error( $orders ) ) {
			return $orders;
		}

		return $orders;
	}

	/**
	 * Add section to Utilities settings page.
	 *
	 * @param array  $query_ids An array of query ids.
	 * @param string $notice The notice to display.
	 */
	public function autoship_wholesale_bulk_admin_utilities_html( $query_ids, $notice ) {
		// Grab current settings.
		$search_args = apply_filters(
			'autoship_bulk_update_scheduled_orders_wholesale_pricing_args',
			array(
				'pageSize'    => 1,
				'statusNames' => array(
					'Paused',
					'Active',
				),
			),
			array()
		);

		// Clear currently saved scheduled orders.
		$this->record_scheduled_orders_updated_with_wholesale_price();

		$file = $this->get_wholesale_pricing_updates_export_file();

		if ( ! empty( $file ) ) {
			$download_link = $this->get_export_file_download_link( $file );
		}

		?>

		<div class="autoship-bulk-action" id="autoship-bulk-update-scheduled-order-wholesale-pricing">

			<h2><?php echo esc_html( __( 'Bulk Scheduled Order Wholesale Pricing Update', 'autoship' ) ); ?></h2>
			<p>
				<?php
				// translators: %s is the list of statuses that will be updated.
				echo esc_html( sprintf( __( 'Batch updates all Scheduled Orders ( Statuses Included: %s ) using the Wholesale prices. The Batch Size (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ), implode( ', ', $search_args['statusNames'] ) ) );
				?>
			</p>
			<ul>
				<li style="padding-left: 10px;">
					<p><?php echo wp_kses_post( __( '- Use the <strong>“Export Changes Only”</strong> option to export a preview of all Scheduled Orders which will be changed when Wholesale prices are re-applied.  Changes will be exported in a csv file and will NOT be applied to any Scheduled Orders.', 'autoship' ) ); ?></p>
				</li>
				<?php
				// Currently this option is disabled by default.
				if ( apply_filters( 'autoship_bulk_update_scheduled_orders_wholesale_pricing_enable_apply_only', false ) ) {
					?>

					<li style="padding-left: 10px;">
						<p><?php echo wp_kses_post( __( '- Use the <strong>“Apply Changes Only”</strong> option to update all Scheduled Orders in QPilot by re-applying the current Wholesale price rules and do not export all affected Scheduled Orders to a csv file.', 'autoship' ) ); ?></p>
					</li>
				
				<?php } ?>

				<li style="padding-left: 10px;">
					<p><?php echo wp_kses_post( __( '- Use the <strong>“Apply & Export Changes”</strong> option to update all Scheduled Orders in QPilot by re-applying the current Wholesale price rules and in addition export all affected Scheduled Orders to a csv file so that changes can be reviewed after being applied.', 'autoship' ) ); ?></p>
				</li>

			</ul>
			
			<?php if ( ! empty( $file ) ) { ?>

				<hr/>

				<p id="wholesale-pricing-bulk-notice"><?php echo wp_kses_post( __( '<b>NOTE:</b> Download the last bulk wholesale pricing changes export file ', 'autoship' ) ); ?>
					<a href="<?php echo esc_url( $download_link ); ?>"
						target="_blank"><?php echo esc_html( __( 'Here', 'autoship' ) ); ?></a>. <?php echo esc_html( __( 'Wholesale pricing export files are automatically saved to a autoship-cloud-exports folder in the WP Uploads directory.' ) ); ?>
				</p>
				<hr/>
			<?php } ?>
			
			<?php
			$result = $this->search_scheduled_orders( null, 1, $search_args );

			$notice = $result->totalCount ? // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				// translators: %1$d is the total count of Scheduled Orders, %2$s is the list of statuses that will be updated.
				__( 'A total of a %1$d %2$s Scheduled Orders can be processed.', 'autoship' ) :
				__( 'There are no available Scheduled Orders to process.', 'autoship' );

			$notice = sprintf( $notice, $result->totalCount, implode( ', ', $search_args['statusNames'] ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			?>

			<h4 class="autoship-bulk-notice"><?php echo esc_html( $notice ); ?></h4>
			<h5 class="autoship-bulk-subnotice"></h5>

			<p class="form-field inline-field ">
				<label for="batch_size">Batch Size:</label>
				<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
			</p>

			<p class="form-field inline-field">
				<label for="wsp-yes-batch-preview"><input id="wsp-yes-batch-preview" type="radio" name="export_option"
															value="export" checked="checked"/>
					<?php echo esc_html( __( 'Export Changes Only', 'autoship' ) ); ?></label>
			</p>
			
			<?php
			// Currently this option is disabled by default.
			if ( apply_filters( 'autoship_bulk_update_scheduled_orders_wholesale_pricing_enable_apply_only', false ) ) {
				?>

				<p class="form-field inline-field">
					<label for="wsp-yes-batch-apply"><input id="wsp-yes-batch-apply" type="radio" name="export_option"
															value="apply"/>
						<?php echo esc_html( __( 'Apply Changes Only', 'autoship' ) ); ?></label>
				</p>
			
			<?php } ?>

			<p class="form-field inline-field">
				<label for="wsp-yes-batch-all"><input id="wsp-yes-batch-all" type="radio" name="export_option"
														value="applyexport"/>
					<?php echo esc_html( __( 'Apply & Export Changes', 'autoship' ) ); ?></label>
			</p>

			<div style="display:none;" class="autoship-meter">
				<span style="width:10%"></span>
			</div>

			<p>
				<button class="button-primary autoship-action autoship-ajax-button">
					<span><?php echo esc_html( __( 'Update Scheduled Orders', 'autoship' ) ); ?></span></button>
			</p>

			<p>
				<button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button">
					<span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button>
			</p>

			<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo esc_html( $result->totalCount ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase ?>">
			<input type="hidden" name="current_count" value="0">
			<input type="hidden" name="current_page" value="1">
			<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
			<input type="hidden" name="batch_action" value="autoship_bulk_update_scheduled_orders_wholesale_pricing">
		</div>
		<?php
	}

	/**
	 * Checks for Download Init and echo's the screen.
	 */
	public function download_export_file() {
		if ( ! isset( $_GET['autoship_download_wholesale_pricing_export_file'] ) || empty( $_GET['autoship_download_wholesale_pricing_export_file'] ) || ! current_user_can( 'export' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$file = $_GET['autoship_download_wholesale_pricing_export_file']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$path = trailingslashit( autoship_get_export_directory() );

		if ( ! file_exists( realpath( $path . $file ) ) ) {
			return;
		}

		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment;filename="' . $file . '"' );
		header( 'Cache-Control: max-age=0' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past.
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); // always modified.
		header( 'Cache-Control: private, max-age=0, must-revalidate' );
		header( 'Pragma: public' ); // HTTP/1.0.
		$contents = file_get_contents( $path . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		echo $contents; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit();
	}

	/**
	 * Gathers the Additional Posted Vals for the Batch Update Recurring
	 *
	 * @param array $args The arguments to update.
	 */
	public function autoship_batch_update_scheduled_orders_wholesale_pricing_added_args( $args ) {

		$args['export_option'] = ! isset( $_POST['export_option'] ) || empty( $_POST['export_option'] ) ? 'export' : $_POST['export_option']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return $args;
	}
}

/**
 * Load Autoship_Cloud_Wholesale_Pricing
 */
function autoship_wholesale_pricing_init() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed

	// Bail if extension in use and show admin notice.
	if ( class_exists( 'Autoship_Cloud_Wholesale_Pricing_Extension' ) ) {
		add_action( 'admin_notices', 'autoship_cloud_wholesale_pricing_notice' );

		return;
	}

	// Check for Wholesuite plugin.
	if ( class_exists( 'WooCommerceWholeSalePrices' ) ) {
		$wholesale = Autoship_Cloud_Wholesale_Pricing::get_instance();
		$wholesale->load();
	}
}

add_action( 'plugins_loaded', 'autoship_wholesale_pricing_init', 100 );


/**
 * Outputs the notice if Autoship Cloud Solutions - Wholesale Pricing Extension plugin in use
 */
function autoship_cloud_wholesale_pricing_notice() {
	?>
	<div class="error"><p>Autoship Cloud Solutions - Wholesale Pricing Extension is now part of the Autoship Cloud
			plugin.</p>
		<p>Please deactivate and uninstall the Autoship Cloud Solutions - Wholesale Pricing Extension plugin.</p>
	</div>
	<?php
}


/**
 * Bulk Updates Scheduled Orders Wholesale Pricing
 *
 * @param array $args The arguments.
 *
 * @return array The results of the update
 */
function autoship_bulk_update_scheduled_orders_wholesale_pricing( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'current_page'  => - 1,
			'current_count' => 0,
			'batch_size'    => 10,
			'total_count'   => 0,
			'statuses'      => array( 'Paused', 'Active' ),
			'export_option' => 'export',
		)
	);

	// Add Export if an export option was selected.
	if ( 'export' === $args['export_option'] || 'applyexport' === $args['export_option'] ) {

		$current_server_time = date( 'Y_m_d_g_i_s_a' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$filename            = 'autoship_bulk_scheduled_orders_wholesale_pricing_export_' . $current_server_time . '.csv';
		// Add the Column Headers to the Export file.
		$fields = array(
			'ScheduledOrderId',
			'CustomerId',
			'Email',
			'Status',
			'NextOccurenceDate',
			'FrequencyType',
			'Frequency',
			'FrequencyDisplayName',
			'ShippingFirstName',
			'ShippingLastName',
			'OriginalExternalId',
			'ScheduledOrderItem_id',
			'ProductId',
			'ProductTitle',
			'Quantity',
			'Price',
			'SalePrice',
			'NewPrice',
			'NewSalePrice',
		);

		// Get an instance of the Wholesale Pricing.
		$wsp_instance = Autoship_Cloud_Wholesale_Pricing::get_instance();

		// Create // Retrieve the Export File.
		$file = ! $args['current_count'] ? autoship_bulk_export_create_file( $filename, $fields ) : $wsp_instance->get_wholesale_pricing_updates_export_file();

		// Check for errors getting or retrieving the export file.
		if ( is_wp_error( $file ) ) {
			return array(
				'success'       => false,
				'total_pct'     => 0,
				'current_count' => $args['total_count'],
				'notice'        => $file->get_error_message(),
			);
		}
	}

	// Clear currently saved scheduled orders if this is a new process.
	if ( ! $args['current_count'] ) {
		$wsp_instance->record_scheduled_orders_updated_with_wholesale_price();
	}

	// Get the currently saved order ids.
	$recorded = $wsp_instance->get_scheduled_orders_updated_with_wholesale_price();

	// Setup the Search Arguments for the Scheduled orders and allow devs to filter.
	$search_args = apply_filters(
		'autoship_wholesale_pricing_extensions_bulk_update_scheduled_orders_wholesale_pricing_args',
		array(
			'pageSize'    => $args['batch_size'],
			'statusNames' => $args['statuses'],
		),
		$args
	);

	// Retrieve the IDs.
	$page = $wsp_instance->search_scheduled_orders( null, $args['current_page'] <= 0 ? 1 : $args['current_page'], $search_args );

	$count          = 0;
	$updated_orders = array();

	// As long as we haven't processed the full set.
	if ( $args['current_count'] < $args['total_count'] ) {

		$count = 0;
		foreach ( $page->items as $scheduled_order ) {

			$order = autoship_convert_object_to_array( $scheduled_order );

			// Run the Scheduled Order through the Wholesale Pricing rules.
			$updated_order = $wsp_instance->update_scheduled_order_with_wholesale_pricing( $order );

			// Check if there was indeed a change and only if the prices changed continue and update it in QPilot.
			if ( $wsp_instance->scheduled_order_items_wholesale_price_changed( $order, $updated_order ) ) {

				// Only apply the changes if the option selected includes it.
				if ( 'apply' === $args['export_option'] || 'applyexport' === $args['export_option'] ) {
					// Initiate a new instance of the API Client.
					$client = autoship_get_default_client();

					try {

						// Send the updated order back to QPilot using the API.
						$updated = $client->update_scheduled_order( $updated_order['id'], $updated_order );

					} catch ( Exception $e ) {

						// Add any errors to the Autoship Logs.
						$notice = autoship_expand_http_code( $e->getCode() );
						autoship_log_entry( __( 'Autoship Orders', 'autoship' ), sprintf( '%d Update Scheduled Order #%s via Wholesale Pricing  Bulk Utlity for Customer #%d Failed.  Additional Details: %s', $e->getCode(), $updated_order['id'], $updated_order['customerId'], $e->getMessage() ) );
					}
				}

				// Adjust all counters.
				$ids[ $updated_order['id'] ] = $updated_order['id'];

				// Only write tp the export file if selected to do so.
				if ( 'export' === $args['export_option'] || 'applyexport' === $args['export_option'] ) {

					$updated_orders[ $updated_order['id'] ] = array(
						'original' => $order,
						'new'      => $updated_order,
					);

					// Write changes to the csv file.
					$written = $wsp_instance->bulk_export_scheduled_order( $updated_orders[ $updated_order['id'] ] );
				}
			}

			$last = $updated_order['id'];
			++$count;
		}

		$pct = $count && $args['total_count'] ? round( 100 * ( ( $count + $args['current_count'] ) / $args['total_count'] ), 2 ) : 0;

	} else {

		$pct = 100;

	}

	$recorded = array_merge( $recorded, isset( $ids ) ? $ids : array() );
	$wsp_instance->record_scheduled_orders_updated_with_wholesale_price( $recorded );

	// Finally calculate the percentage completed and update for next round.
	$pct           = ! empty( $page ) ? $pct : 100;
	$pct           = $pct >= 100 ? 100 : $pct;
	$download_link = '';
	if ( 100 !== $pct ) {

		$notice_text = array(
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders, %3$d is the number of Scheduled Orders updated.
			'applyexport' => __( '%1$s%% of the %2$d Scheduled Orders have been processed. A total of %3$d Scheduled Orders have been updated with new pricing and added to the csv export file.', 'autoship' ),
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders, %3$d is the number of Scheduled Orders updated.
			'export'      => __( '%1$s%% of the %2$d Scheduled Orders have been processed. A total of %3$d Scheduled Orders would have been updated with new pricing.  Pricing changes that would have been applied have been added to the csv export file.', 'autoship' ),
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders, %3$d is the number of Scheduled Orders updated.
			'apply'       => __( '%1$s%% of the %2$d Scheduled Orders have been processed. A total of %3$d Scheduled Orders have been updated with new pricing.', 'autoship' ),
		);

		$notice = sprintf( $notice_text[ $args['export_option'] ], $pct < 5 ? 5 : $pct, $args['total_count'], count( $recorded ) );

	} else {

		$notice_text = array(
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders, %3$d is the number of Scheduled Orders updated, %4$s is the download link.
			'applyexport' => __( '%1$s%% of the %2$d Scheduled Orders have been processed. A total of %3$d Scheduled Orders have been updated with new pricing and added to the csv export file. Download the export file by <a href="%4$s">clicking here</a>', 'autoship' ),
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders, %3$d is the number of Scheduled Orders updated, %4$s is the download link.
			'export'      => __( '%1$s%% of the %2$d Scheduled Orders have been processed. A total of %3$d Scheduled Orders would have been updated with new pricing. All pricing changes that would have been applied have been exported to the csv export file which can be downloaded by <a href="%4$s">clicking here</a>.', 'autoship' ),
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders, %3$d is the number of Scheduled Orders updated.
			'apply'       => __( '%1$s%% of the %2$d Scheduled Orders have been processed. A total of %3$d Scheduled Orders have been updated with new pricing.', 'autoship' ),
		);

		if ( 'apply' === $args['export_option'] ) {

			$notice = sprintf( $notice_text[ $args['export_option'] ], $pct < 5 ? 5 : $pct, $args['total_count'], count( $recorded ) );

		} else {

			$download_link = $wsp_instance->get_export_file_download_link( $file );

			$notice = sprintf( $notice_text[ $args['export_option'] ], $pct < 5 ? 5 : $pct, $args['total_count'], count( $recorded ), $download_link );

		}

		if ( ! count( $recorded ) ) {
			// translators: %1$s is the percentage completed, %2$d is the total number of Scheduled Orders.
			$notice = sprintf( __( '%1$s%% of the %2$d Scheduled Orders have been processed. No updates were needed to the Scheduled Orders.', 'autoship' ), $pct < 5 ? 5 : $pct, $args['total_count'] );
		}

		// Finally log the updates in the Autoship Log files.
		autoship_log_entry( __( 'Autoship Bulk Update Orders Pricing', 'autoship' ), sprintf( 'Updated %d Scheduled Orders via Wholesale Pricing  Bulk Utility Completed.  Updated Orders: %s', count( $recorded ), implode( ',', $recorded ) ) );

	}

	return ! $count ? array(
		'success'       => false,
		'total_pct'     => 0,
		'current_count' => $args['total_count'],
		'notice'        => __( 'A Problem was encountered processing the Scheduled Orders.  Please try again.', 'autoship' ),
	) : array(
		'success'        => true,
		'page'           => ++$args['current_page'],
		'last_record'    => isset( $last ) ? $last : 0,
		'updated_record' => isset( $ids ) ? $ids : array(),
		'count'          => $count,
		'current_count'  => 100 === $pct ? $args['total_count'] : $count + $args['current_count'],
		'total_pct'      => $pct < 5 ? 5 : $pct,
		'notice'         => $notice,
		'download_link'  => $download_link,
	);
}
