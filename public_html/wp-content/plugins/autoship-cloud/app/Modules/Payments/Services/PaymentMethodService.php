<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Method Service
 *
 * Encapsulates shared payment method CRUD operations for QPilot.
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Modules\Payments\Services;

use Autoship\Services\Logging\Logger;
use Exception;
use WC_Payment_Token;
use WC_Payment_Tokens;
use WP_Error;

/**
 * Service for managing payment method synchronization with QPilot.
 *
 * Replaces the shared procedural functions in src/payments.php:
 * - autoship_add_payment_method()           → upsert()
 * - autoship_delete_payment_method()        → delete()
 * - autoship_add_general_payment_method()   → add_from_token()
 * - autoship_add_non_wc_token_payment_method() → add_from_gateway_response()
 * - autoship_delete_general_payment_method()   → delete_for_token()
 * - autoship_delete_tokenized_payment_method() → delete_tokenized()
 * - autoship_delete_non_wc_token_payment_method() → delete_non_wc_token()
 * - autoship_add_tokenized_payment_method()    → add_tokenized()
 *
 * @package Autoship\Modules\Payments\Services
 * @since 2.11.0
 */
class PaymentMethodService {

	/**
	 * The payment method data builder.
	 *
	 * @var PaymentMethodDataBuilder
	 */
	private PaymentMethodDataBuilder $data_builder;

	/**
	 * Constructor.
	 *
	 * @param PaymentMethodDataBuilder $data_builder The data builder instance.
	 */
	public function __construct( PaymentMethodDataBuilder $data_builder ) {
		$this->data_builder = $data_builder;
	}

	/**
	 * Create or update a payment method in QPilot.
	 *
	 * Replaces autoship_add_payment_method().
	 *
	 * @param array $payment_method_data The payment method data array for QPilot.
	 *
	 * @return \stdClass|WP_Error The resulting payment method object or WP_Error on failure.
	 */
	public function upsert( array $payment_method_data ) {
		$client = autoship_get_default_client();

		try {
			$method = $client->upsert_payment_method( $payment_method_data );
		} catch ( Exception $e ) {
			$notice = autoship_expand_http_code( $e->getCode() );
			autoship_log_entry( __( 'Autoship Payment Methods', 'autoship' ), sprintf( 'Error creating QPilot Payment Method. Additional Details: %s - %s', $e->getCode(), $e->getMessage() ) );

			return new WP_Error( 'Creating QPilot Payment Method Failed', __( $notice['desc'], 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		}

		return $method;
	}

	/**
	 * Delete a payment method from QPilot by its QPilot method ID.
	 *
	 * Replaces autoship_delete_payment_method().
	 *
	 * @param int $method_id The QPilot payment method ID.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function delete( int $method_id ) {
		$client = autoship_get_default_client();

		try {
			$client->delete_payment_method( $method_id );
		} catch ( Exception $e ) {
			$notice = autoship_expand_http_code( $e->getCode() );
			autoship_log_entry( __( 'Autoship Payment Methods', 'autoship' ), sprintf( 'Error deleting QPilot Payment Method. Additional Details: %s - %s', $e->getCode(), $e->getMessage() ) );

			return new WP_Error( 'Deleting QPilot Payment Method Failed', __( $notice['desc'], 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		}

		return true;
	}

	/**
	 * Add a payment method to QPilot from a WooCommerce payment token.
	 *
	 * Replaces autoship_add_general_payment_method().
	 * Builds the payment method data from the token and optionally upserts to QPilot.
	 *
	 * @param WC_Payment_Token $token The WooCommerce payment token.
	 * @param bool             $return_data_only If true, return the data array without upserting.
	 *
	 * @return \stdClass|WP_Error|array|false The payment method object, data array, WP_Error, or false.
	 */
	public function add_from_token( WC_Payment_Token $token, bool $return_data_only = false ) {
		if ( is_wp_error( $token ) || empty( $token ) ) {
			return false;
		}

		$payment_method_data = $this->data_builder->build( $token );

		if ( empty( $payment_method_data ) ) {
			return false;
		}

		return $return_data_only ? $payment_method_data : $this->upsert( $payment_method_data );
	}

	/**
	 * Add a payment method to QPilot from a non-WC-table gateway response.
	 *
	 * Used by Skyverge framework gateways (Square, Braintree, Authorize.Net)
	 * that don't use the standard WooCommerce payment tokens table directly.
	 *
	 * Replaces autoship_add_non_wc_token_payment_method().
	 *
	 * @param object   $response The gateway response object (must implement get_payment_token()).
	 * @param \WC_Order $order The WC Order object.
	 * @param string   $autoship_method_type The autoship method type identifier.
	 *
	 * @return \stdClass|WP_Error|null The payment method object, WP_Error on failure, or null.
	 */
	public function add_from_gateway_response( object $response, \WC_Order $order, string $autoship_method_type ) {
		$token              = $response->get_payment_token();
		$autoship_method_id = $token->get_id();

		// Apply filters for non-standard gateways to tokenize the method data.
		$token = apply_filters( 'autoship_payment_method_tokenization', WC_Payment_Tokens::get( $autoship_method_id ), $autoship_method_id, $autoship_method_type );
		if ( empty( $token ) ) {
			return $token;
		}

		// Build the data and upsert.
		$payment_method_data = $this->data_builder->build( $token );

		if ( empty( $payment_method_data ) ) {
			return null;
		}

		return $this->upsert( $payment_method_data );
	}

	/**
	 * Delete a tokenized payment method from QPilot based on a WC token.
	 *
	 * Looks up the customer's payment methods in QPilot and deletes the matching one.
	 *
	 * Replaces autoship_delete_general_payment_method().
	 *
	 * @param int              $token_id The WC payment token ID.
	 * @param WC_Payment_Token $token The WC payment token object.
	 * @param string           $type The QPilot payment method type name (e.g. 'Stripe', 'AuthorizeNet').
	 *
	 * @return bool|WP_Error True on success, false if not needed, WP_Error on failure.
	 */
	public function delete_for_token( int $token_id, WC_Payment_Token $token, string $type = '' ) {
		$wc_customer_id = $token->get_user_id();

		$customer = autoship_check_autoship_customer( $wc_customer_id, 'autoship_delete_general_payment' );

		if ( ! $customer || empty( $type ) ) {
			return false;
		}

		$payment_method_id = $token->get_token();
		$payment_methods   = $customer->paymentMethods; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( empty( $payment_methods ) ) {
			return true;
		}

		$result = false;

		foreach ( $payment_methods as $method ) {
			$valid = ( $method->type === $type && $method->gatewayCustomerId === $wc_customer_id && $method->gatewayPaymentId === $payment_method_id ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Filters for custom matching between QPilot and WooCommerce.
			$valid = apply_filters( 'autoship_delete_general_payment_method_qpilot_match', $valid, $type, $token, $method );
			$valid = apply_filters( "autoship_delete_{$type}_payment_method_qpilot_match", $valid, $type, $token, $method );

			if ( $valid ) {
				$result = $this->delete( $method->id );
				break;
			}
		}

		return $result;
	}

	/**
	 * Handle deletion of a WooCommerce tokenized payment method.
	 *
	 * Entry point hooked to 'woocommerce_payment_token_deleted'. Routes to the
	 * correct gateway type and calls delete_for_token().
	 *
	 * Replaces autoship_delete_tokenized_payment_method().
	 *
	 * @param int              $token_id The WC payment token ID.
	 * @param WC_Payment_Token $token The WC payment token object.
	 *
	 * @return bool|WP_Error True on success, false if not needed, WP_Error on failure.
	 */
	public function delete_tokenized( int $token_id, WC_Payment_Token $token ) {
		$gateway_id = apply_filters( 'autoship_delete_tokenized_payment_method_gateway_id', $token->get_gateway_id(), $token );

		$gateway_id_types = apply_filters( 'autoship_delete_tokenized_payment_method_extend_gateway_types', autoship_standard_gateway_id_types(), $gateway_id, $token );

		if ( ! array_key_exists( $gateway_id, $gateway_id_types ) ) {
			return false;
		}

		do_action( 'autoship_delete_tokenized_payment_method_extend_gateway', $gateway_id, $gateway_id_types, $token );

		return $this->delete_for_token( $token_id, $token, $gateway_id_types[ $gateway_id ] );
	}

	/**
	 * Handle addition of a WooCommerce tokenized payment method.
	 *
	 * Entry point hooked to 'woocommerce_new_payment_token'. Routes to the
	 * correct gateway type and calls add_from_token().
	 *
	 * Replaces autoship_add_tokenized_payment_method().
	 *
	 * @param int $token_id The WC payment token ID.
	 *
	 * @return \stdClass|WP_Error|false|null The payment method, WP_Error, false, or null.
	 */
	public function add_tokenized( int $token_id ) {
		$token = WC_Payment_Tokens::get( $token_id );

		Logger::trace( 'Autoship', 'Adding Tokenized Payment Method', array( 'token_id' => $token_id ) );

		// Do not upsert payment methods at checkout.
		if ( apply_filters( 'autoship_add_tokenized_payment_method', false, $token ) ) {
			Logger::trace( 'Autoship', 'Attempting to add payment method at checkout' );

			return null;
		}

		// Get the type of gateway (standard tokenized or non-standard).
		$gateway_type = autoship_get_payment_method_gateway_type( $token->get_gateway_id() );

		// Apply filters for non-standard gateways to tokenize the method data.
		$token = apply_filters( 'autoship_payment_method_tokenization', $token, $token_id, $gateway_type );

		// Allow users to override the gateway id.
		$gateway_id = apply_filters( 'autoship_add_tokenized_payment_method_gateway_id', $token->get_gateway_id(), $token_id, $token );

		// Get the current gateway id types.
		$gateway_id_types = apply_filters( 'autoship_add_tokenized_payment_method_extend_gateway_types', autoship_standard_gateway_id_types(), $gateway_id, $token );

		if ( ! array_key_exists( $gateway_id, $gateway_id_types ) ) {
			return false;
		}

		do_action( 'autoship_add_tokenized_payment_method_extend_gateway', $gateway_id, $gateway_id_types, $token );

		return $this->add_from_token( $token );
	}

	/**
	 * Delete a non-WC-table payment method from QPilot.
	 *
	 * Used by Skyverge framework gateways (Square, Braintree) that store
	 * payment data outside the standard WooCommerce payment tokens table.
	 *
	 * Replaces autoship_delete_non_wc_token_payment_method().
	 *
	 * @param string   $gateway_payment_method_id The gateway's payment method/token ID.
	 * @param int|null $gateway_customer_id The gateway's customer ID (optional).
	 * @param string   $type The QPilot payment method type name.
	 * @param int|null $wc_customer_id The WC customer ID (defaults to current user).
	 *
	 * @return bool|WP_Error True on success, false if not found, WP_Error on failure.
	 */
	public function delete_non_wc_token( string $gateway_payment_method_id, ?int $gateway_customer_id, string $type, ?int $wc_customer_id = null ) {
		if ( empty( $wc_customer_id ) ) {
			$wc_customer_id = get_current_user_id();
		}

		$customer = autoship_check_autoship_customer( $wc_customer_id, 'autoship_delete_non_wc_token_payment' );

		if ( ! $customer ) {
			return false;
		}

		$payment_methods = $customer->paymentMethods; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		$result = false;
		foreach ( $payment_methods as $method ) {
			$valid = ! empty( $gateway_customer_id )
				? ( $method->type === $type && $method->gatewayPaymentId === $gateway_customer_id && $method->gatewayPaymentId === $gateway_payment_method_id ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				: ( $method->type === $type && $method->gatewayPaymentId === $gateway_payment_method_id ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			if ( $valid ) {
				$result = $this->delete( $method->id );
				break;
			}
		}

		return $result;
	}

	/**
	 * Get the data builder instance.
	 *
	 * @return PaymentMethodDataBuilder
	 */
	public function get_data_builder(): PaymentMethodDataBuilder {
		return $this->data_builder;
	}
}
