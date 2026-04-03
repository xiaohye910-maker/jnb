<?php
/**
 * Contains the functions regarding the ajax usage for the plugin.
 *
 * @package Autoship
 * @since 1.0.0
 */

/**
 * Generates a Header Response for the Supplied HTTP code.
 *
 * @param int $code The HTTP Code to retrieve the message for.
 */
function autoship_ajax_set_response_code( $code ) {
	$message = '';
	if ( 200 === $code ) {
		$message = 'OK';
	} elseif ( 204 === $code ) {
		$message = 'No Content';
	} elseif ( 400 === $code ) {
		$message = 'Bad Request';
	} elseif ( 401 === $code ) {
		$message = 'Unauthorized';
	} elseif ( 403 === $code ) {
		$message = 'Forbidden';
	} elseif ( 404 === $code ) {
		$message = 'Not Found';
	} elseif ( 500 === $code ) {
		$message = 'Error';
	}
	header( "HTTP/1.1 $code $message" );
}

/**
 * Generates a json Ajax Response for the Supplied HTTP code & Data.
 *
 * @param int   $response_code The HTTP Code to respond with.
 * @param mixed $data Optional. The data to include in the json response.
 * @param bool  $terminate Optional. True to fire the die command.
 */
function autoship_ajax_result( $response_code, $data = null, $terminate = true ) {
	autoship_ajax_set_response_code( $response_code );
	if ( null !== $data ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		$data = wp_json_encode( $data );
		header( 'Content-Length: ' . strlen( $data ) );
		echo $data; // phpcs:ignore
	}

	if ( $terminate ) {
		die();
	}
}

// =========================================
// Ajax Embedded App Support Methods
// =========================================

/**
 * Generates the token authorization for when loading the APP.
 */
function autoship_ajax_get_scheduled_orders_settings() {

	$site_customer_id = get_current_user_id();

	// Retrieves the QPilot Customer or Create if Doesn't Exist.
	$qpilot_customer = autoship_check_autoship_customer( $site_customer_id );

	if ( ! is_wp_error( $qpilot_customer ) ) {

		$client = autoship_get_default_client();

		try {
			$access_token = $client->generate_customer_access_token( $qpilot_customer->id, autoship_get_client_secret() );
			$settings     = apply_filters(
				'autoship_scheduled_orders_settings',
				array(
					'customer_id'       => $qpilot_customer->id,
					'bearer_token_auth' => $access_token->tokenBearerAuth, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					'api_url'           => autoship_get_api_url(),
					'site_customer_id'  => $site_customer_id,
					'site_ajax_url'     => admin_url( '/admin-ajax.php' ),
					'site_id'           => autoship_get_site_id(),
					'site_currency'     => get_woocommerce_currency(),
				)
			);
			autoship_ajax_result( 200, $settings );

		} catch ( Exception $e ) {

			if ( 401 === $e->getCode() ) {
				autoship_log_entry(
					__( 'Autoship Orders', 'autoship' ),
					sprintf(
						'%1$d Generate customer access token failed. Additional Details: %2$s',
						$e->getCode(),
						$e->getMessage()
					)
				);
			} elseif ( 403 === $e->getCode() ) {
				// This token is invalid.
				delete_user_meta( $site_customer_id, '_autoship_customer_id' );
			}
			autoship_ajax_result( 500, array( strval( $e->getCode() ) => $e->getMessage() ) );
		}
	}
	autoship_ajax_result( 500, 'No QPilot Customer ID' );
}

add_action( 'wp_ajax_autoship_get_scheduled_orders_settings', 'autoship_ajax_get_scheduled_orders_settings' );

/**
 * Retrieves the Autoship frequency options for a product(s) or variation(s) via Ajax.
 *
 * @param array $product_ids Optional. An Optional Array of ids to pull.
 * @param bool  $ajax Optional. True ajax call else not.
 *
 * @return array The array of freq and freq types.
 * @see autoship_product_frequency_options()
 */
function autoship_get_autoship_product_frequencies( $product_ids = array(), $ajax = true ) {

	if ( empty( $product_ids ) && isset( $_POST['productIds'] ) && ! empty( $_POST['productIds'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product_ids = $_POST['productIds']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	// Gather the Frequencies for Each Product.
	$products = array();
	foreach ( $product_ids as $id ) {
		$products[] = array(
			'productId'   => $id,
			'frequencies' => autoship_product_frequency_options( $id ),
		);
	}

	if ( ! $ajax ) {
		return $products;
	}

	autoship_ajax_result( 200, $products );
	die();
}

add_action( 'wp_ajax_autoship_get_product_frequencies', 'autoship_get_autoship_product_frequencies' );

/**
 * Retrieves the WC Customers Information via Ajax.
 */
function autoship_ajax_get_customer() {
	$customer_id = $_GET['customer_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( empty( $customer_id ) && '0' !== $customer_id ) {
		autoship_ajax_result( 400, 'A customer id is required.' );
	}

	$customer_id = intval( $customer_id );

	if ( get_current_user_id() !== $customer_id ) {
		autoship_ajax_result( 403, 'You do not have authorization to access this resource.' );
	}

	$customer     = new WC_Customer( $customer_id );
	$customerdict = array(
		'id'                  => $customer->get_id(),
		'shipping_first_name' => $customer->get_shipping_first_name(),
		'shipping_last_name'  => $customer->get_shipping_last_name(),
		'shipping_address_1'  => $customer->get_shipping_address_1(),
		'shipping_address_2'  => $customer->get_shipping_address_2(),
		'shipping_city'       => $customer->get_shipping_city(),
		'shipping_state'      => $customer->get_shipping_state(),
		'shipping_postcode'   => $customer->get_shipping_postcode(),
		'shipping_country'    => $customer->get_shipping_country(),
	);

	autoship_ajax_result( 200, $customerdict );
}

add_action( 'wp_ajax_autoship_get_customer', 'autoship_ajax_get_customer' );

/**
 * Runs Supplied Messages through the WP String Translation function
 */
function autoship_ajax_get_translation() {
	$msgid       = $_GET['msgid']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$translation = __( $msgid, 'autoship' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText

	if ( ! empty( $_GET['args'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$args = $_GET['args']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( $args as &$arg ) {
			$arg = stripslashes( $arg );
		}
		$translation = vsprintf( $translation, $args );
	}
	autoship_ajax_result( 200, $translation );
}

add_action( 'wp_ajax_autoship_get_translation', 'autoship_ajax_get_translation' );

/**
 * Retrieves the Display Name for a Frequency & Frequency Type.
 *
 * @uses autoship_get_frequency_display_name()
 */
function autoship_ajax_get_frequency_display_name() {
	$display_name = autoship_get_frequency_display_name( $_GET['frequency_type'], $_GET['frequency'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	autoship_ajax_result( 200, $display_name );
}

add_action( 'wp_ajax_autoship_get_frequency_display_name', 'autoship_ajax_get_frequency_display_name' );

/**
 * Retrieves the Scheduled Orders Header HTML
 */
function autoship_ajax_get_scheduled_order_header_html() {
	$value = get_option( 'autoship_scheduled_orders_html', '' );
	autoship_ajax_result( 200, $value );
}

add_action( 'wp_ajax_autoship_get_scheduled_order_header_html', 'autoship_ajax_get_scheduled_order_header_html' );
