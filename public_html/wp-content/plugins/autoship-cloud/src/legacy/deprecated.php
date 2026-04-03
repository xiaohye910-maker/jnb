<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Deprecated functions from past Autoship versions. You shouldn't use these
 * functions and look for the alternatives instead. The functions will be
 * removed in a later version.
 *
 * @package Autoship
 * @since 1.0.0
 * @deprecated 2.0.0
 */

/*
 * Deprecated functions come here to die.
 */

/**
 * Retrieves Checkout Price - Either Autoship or Product price
 *
 * @param WC_Product|int $product WC_Product, WC_Product_Variation or Id.
 *
 * @return float  The checkout price.
 * @deprecated 2.0 Use autoship_checkout_price()
 * @see autoship_checkout_price()
 */
function autoship_discounted_price( $product ) {
	_deprecated_function( __FUNCTION__, '2.0', 'autoship_checkout_price()' );

	return autoship_checkout_price( $product );
}

/**
 * Gets formatted amount for display.
 *
 * @param float $amount The total amount to format.
 * @param array $args Arguments to format a price {
 *     Array of arguments.
 *     Defaults to empty array.
 *
 * @type bool $ex_tax_label Adds exclude tax label.
 *                                      Defaults to false.
 * @type string $currency Currency code.
 *                                      Defaults to empty string (Use the result from get_woocommerce_currency()).
 * @type string $decimal_separator Decimal separator.
 *                                      Defaults the result of wc_get_price_decimal_separator().
 * @type string $thousand_separator Thousand separator.
 *                                      Defaults the result of wc_get_price_thousand_separator().
 * @type string $decimals Number of decimals.
 *                                      Defaults the result of wc_get_price_decimals().
 * @type string $price_format Price format depending on the currency position.
 *                                      Defaults the result of get_woocommerce_price_format().
 * }
 *
 * @return string
 * @deprecated 2.0 Use autoship_get_formatted_price()
 */
function autoship_get_formatted_amount( $amount, $args = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.2', 'autoship_get_formatted_price()' );

	return apply_filters( 'autoship_get_formatted_amount', autoship_get_formatted_price( $amount, $args ), $amount, $args );
}

/**
 * Outputs the Autoship Schedule Options Template to the frontend Cart
 * Directly below the cart item name.
 *
 * @param string $product_link The current Product Name or Anchor Link.
 * @param array  $cart_item The current cart item data.
 * @param string $cart_item_key The current cart item key.
 *
 * @deprecated 2.0.5 Use autoship_display_cart_item_options()
 */
function autoship_cart_item_name( $product_link, $cart_item, $cart_item_key ) {
	_deprecated_function( __FUNCTION__, '2.0.5', 'autoship_display_cart_item_data()' );

	return autoship_display_cart_item_options( $cart_item, $cart_item_key );
}

/**
 * Adds formatted Autoship Data to the cart item data and variations for display on the frontend.
 *
 * @param array $data The key to value array of cart item data to use for display ( Label => Value ).
 * @param array $item The current cart item's data array.
 *
 * @return array $data The updated cart item's display data array.
 * @deprecated 2.0.5 Use autoship_display_cart_item_data()
 */
function autoship_get_item_data( $data, $item ) {
	_deprecated_function( __FUNCTION__, '2.0.5', 'autoship_display_cart_item_options()' );

	// If not an autoship item bail.
	if ( ! isset( $item['autoship_frequency_type'] ) || empty( $item['autoship_frequency_type'] ) || ! isset( $item['autoship_frequency'] ) ) {
		return $data;
	}

	// Get the autoship values for this item.
	$frequency      = intval( $item['autoship_frequency'] );
	$frequency_type = $item['autoship_frequency_type'];
	$product_id     = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];

	// Get the formatting and display name for the schedule - first check if there is a custom name else get default.
	$options                        = autoship_product_frequency_options( $product_id );
	$frequency_display_name         = autoship_search_for_frequency_display_name( $frequency_type, $frequency, $options );
	$product_frequency_display_name = apply_filters( 'autoship_product_frequency_display_name', $frequency_display_name, $product_id );

	$data[] = array(
		'name'  => apply_filters( 'autoship_frequency_cart_order_item_schedule_display_label', __( 'Schedule', 'autoship' ) ),
		'value' => $product_frequency_display_name,
	);

	if ( ! empty( $item['autoship_next_occurrence'] ) ) {

		$formatted_date = autoship_format_next_occurrence_for_display( $item['autoship_next_occurrence'] );

		$data[] = array(
			'name'  => apply_filters( 'autoship_frequency_cart_order_item_next_occurence_display_label', __( 'Next Order', 'autoship' ), $formatted_date, $product_id ),
			'value' => apply_filters( 'autoship_frequency_cart_order_item_next_occurence_display_date_value', __( $formatted_date, 'autoship' ), $product_id ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		);
	}

	return $data;
}

/**
 * Adds order notes after order insertion/update.
 *
 * @param int    $order The created/inserted order.
 * @param string $request The request.
 * @param int    $creating If the order is creating.
 *
 * @deprecated 2.0.7 Use autoship_woocommerce_rest_insert_shop_object_add_note()
 */
function autoship_woocommerce_rest_insert_shop_order_object( $order, $request, $creating ) {
	_deprecated_function( __FUNCTION__, '2.0.7', 'autoship_woocommerce_rest_insert_shop_object_add_note()' );
	autoship_woocommerce_rest_insert_shop_object_add_note( $order, $request, $creating );
}

/**
 * Handle custom qpilot query vars to get orders with qpilot meta keys.
 *
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Order_Query.
 *
 * @return array modified $query
 * @deprecated 2.0.7 Use autoship_handle_meta_query_by_scheduled_order_processing_id()
 */
function handle_custom_qpilot_meta_query( $query, $query_vars ) {
	_deprecated_function( __FUNCTION__, '2.0.7', 'autoship_handle_meta_query_by_scheduled_order_processing_id()' );

	return autoship_handle_meta_query_by_scheduled_order_processing_id( $query, $query_vars );
}

/**
 * Outputs the Dynamic Schedule Cart Widgets iframe
 *
 * @param string $path The iframe url.
 *
 * @deprecated 2.0.9
 */
function autoship_render_widget( $path ) {
	_deprecated_function( __FUNCTION__, '2.0.9' );
	$url = autoship_get_merchants_url() . '/widgets/' . $path;

	return autoship_render_template( 'widget', array( 'url' => $url ) );
}

/**
 * Returns the Duration Lock information for an order
 *
 * @param array|stdClass $autoship_order The scheduled order.
 * @return array The Lock Information for an order.
 * @deprecated 2.1.1
 */
function autoship_check_is_order_locked( $autoship_order ) {
	_deprecated_function( __FUNCTION__, '2.1.1', 'autoship_check_lock_status_info()' );

	// Convert stdClass objects to array if needed.
	if ( $autoship_order instanceof stdClass ) {
		$autoship_order = autoship_convert_object_to_array( $autoship_order );
	}

	// Get the Site Settings that include Lock Duration etc.
	$settings = autoship_get_site_order_settings();

	return autoship_check_lock_status_info( $autoship_order, $autoship_order['customerId'], $settings );
}


/**
 * Retrieves Autoship Recurring Price - Either Autoship recurring or checkout price
 *
 * @param WC_Product|int $product WC_Product, WC_Product_Variation or Id.
 * @param array          $prices Optional. The Current Checkout Price and Autoship Checkout Price.
 *
 * @return float  The recurring price.
 * @deprecated 2.2.0
 */
function autoship_recurring_price( $product, $prices = array() ) {
	_deprecated_function( __FUNCTION__, '2.2.0', 'autoship_get_product_recurring_price()' );

	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	$prices = wp_parse_args(
		$prices,
		array(
			'price'    => ! isset( $prices['price'] ) ? $product->get_price() : $prices['price'],
			'discount' => ! isset( $prices['discount'] ) ? autoship_get_product_recurring_price( $product->get_id() ) : $prices['discount'],
		)
	);

	// Recurring may be 0 but if not set we use the checkout price.
	$autoship_price = empty( $prices['discount'] ) ? autoship_checkout_price( $product, array( 'price' => $prices['price'] ) ) : $prices['discount'];

	return apply_filters( 'autoship_filter_recurring_price', $autoship_price, $product, $prices );
}

/**
 * Returns the formatted date from the Autoship formatted date.
 *
 * @param string|DateTime $date The date string to convert or DateTime object to use.
 * @param bool            $input Determines if it's a form input format or display format.
 * @param string          $format The date format.
 *
 * @return string formatted a date based on the offset timestamp
 * @deprecated 2.2.5
 */
function autoship_get_formatted_date( $date, $input = false, $format = 'Y-m-d' ) {

	_deprecated_function( __FUNCTION__, '2.2.5', 'autoship_get_formatted_local_date()' );

	$display_format = $input ? $format : '';

	// Now return the value based on if it's for a form input or display.
	return autoship_get_formatted_local_date( $date, $display_format );
}

/**
 * Returns the next possible date for a scheduled order.
 *
 * @param string $input The date string to convert.
 *
 * @return string formatted a date based on the offset timestamp
 * @deprecated 2.2.5
 */
function autoship_get_next_available_date( $input = false ) {

	_deprecated_function( __FUNCTION__, '2.2.5', 'autoship_get_next_available_nextoccurrence()' );

	$format = $input ? 'Y-m-d' : '';
	$date   = new DateTime();

	return apply_filters( 'autoship_get_next_available_date', autoship_get_formatted_local_date( $date, $format ) );
}


/**
 * Returns the gtm offset timestamp.
 *
 * @return string
 * @deprecated 2.2.5
 */
function autoship_get_site_zero_time_string() {

	_deprecated_function( __FUNCTION__, '2.2.5' );

	return sprintf( '00:00%+03d:00', get_option( 'gmt_offset' ) );
}

/**
 * Returns the Next Occurrence Date Time Unix Time Stamp.
 *
 * @param string $next_occurrence Next Occurrence Date string.
 * @return string Next Occurrence Date Time Unix Time Stamp
 * @deprecated 2.2.5
 */
function autoship_format_next_occurrence_timestamp( $next_occurrence ) {

	_deprecated_function( __FUNCTION__, '2.2.5' );
	if ( empty( $next_occurrence ) ) {
		return $next_occurrence;
	}

	if ( is_integer( $next_occurrence ) || preg_match( '/^\d+$/', $next_occurrence ) ) {
		return $next_occurrence;
	}

	$next_occurrence_formatted = $next_occurrence;

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $next_occurrence_formatted ) ) {
		$next_occurrence_formatted = sprintf( '%sT%s', $next_occurrence, autoship_get_site_zero_time_string() );
	}

	$next_occurrence_timestamp = strtotime( $next_occurrence_formatted );

	return $next_occurrence_timestamp;
}

/**
 * Retrieves the PayPal Payments Support option setting
 *
 * @return string yes for enabled else false
 *
 * @deprecated 2.6.5
 */
function autoship_get_support_paypal_payments_option() {
	_deprecated_function( __FUNCTION__, '2.6.5' );
	$val = autoship_get_settings_fields( 'autoship_support_paypal_payments', true );

	return empty( $val ) ? 'no' : $val;
}


/**
 * Used to Force Paypal Payments to retrieve and assign
 * the payment token to the WC Order
 *
 * @param WC_Order|int $order A WC Order object or order id.
 * @deprecated 2.6.7
 */
function autoship_woocommerce_subscription_patch( $order ) {
	if ( class_exists( '\WooCommerce\PayPalCommerce\PPCP' ) ) {

		$ppcp_settings = \WooCommerce\PayPalCommerce\PPCP::container()->get( 'wcgateway.settings' );

		if ( ( $ppcp_settings->has( 'vault_enabled' ) && $ppcp_settings->get( 'vault_enabled' ) ) || ( $ppcp_settings->has( 'vault_enabled_dcc' ) && $ppcp_settings->get( 'vault_enabled_dcc' ) ) ) {

			if ( ! class_exists( 'WC_Subscription' ) ) {

				/**
				 * Faux Subscription Object Class
				 * NOTE Only used for integrating WooCommerce PayPal Payments Plugin
				 */
				class WC_Subscription extends WC_Order {

					/**
					 * Stores the order data for the order in which the subscription was purchased (if any).
					 *
					 * @var WC_Order
					 */
					protected $order = null;

					/**
					 * Initialize the subscription object.
					 *
					 * @param int|WC_Order $subscription The subscription.
					 */
					public function __construct( $subscription ) {
						$this->order = $subscription;
						parent::__construct( $subscription );
					}

					/**
					 * __get function.
					 *
					 * @param mixed $key The key of the subscription.
					 *
					 * @return mixed
					 */
					public function __get( $key ) {

						if ( 'order' === $key ) {

							$value = $this->order;

						} else {
							$value = parent::__get( $key );
						}

						return $value;
					}

					/**
					 * Get the related orders for a subscription, including renewal orders and the initial order (if any)
					 *
					 * @param string       $return_fields The columns to return, either 'all' or 'ids'.
					 * @param array|string $order_types Can include 'any', 'parent', 'renewal', 'resubscribe' and/or 'switch'. Custom types possible via the 'woocommerce_subscription_related_orders' filter. Defaults to array( 'parent', 'renewal', 'switch' ).
					 *
					 * @return array
					 * @since 1.0.0 - Migrated from WooCommerce Subscriptions v2.0
					 */
					public function get_related_orders(
						$return_fields = 'ids',
						$order_types = array(
							'parent',
							'renewal',
							'switch',
						)
					) {
						return array();
					}
				}
			}

			do_action( 'woocommerce_subscription_payment_complete', new WC_Subscription( $order ) );
		}
	}
}
