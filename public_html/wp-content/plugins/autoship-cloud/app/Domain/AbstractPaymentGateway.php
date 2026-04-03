<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Abstract Payment Gateway
 *
 * @package Autoship Cloud
 * @since 2.11.0
 */

namespace Autoship\Domain;

use QPilotPaymentData;
use WC_Order;
use WC_Payment_Token;

/**
 * Abstract base class for payment gateways with processing capabilities.
 *
 * @package Autoship\Domain\PaymentIntegrations
 * @since 2.9.5
 */
abstract class AbstractPaymentGateway extends PaymentIntegration {

	/**
	 * Initializing.
	 *
	 * @return void
	 */
	abstract public function initialize(): void;

	/**
	 * Get order payment data for QPilot.
	 *
	 * @param int      $order_id The order ID.
	 * @param WC_Order $order The order object.
	 *
	 * @return ?QPilotPaymentData The payment data.
	 */
	abstract public function get_order_payment_data( int $order_id, WC_Order $order ): ?QPilotPaymentData;

	/**
	 * Add payment method data for QPilot.
	 *
	 * @param array            $payment_method_data The payment method data.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @return array The modified payment method data.
	 */
	abstract public function add_payment_method( array $payment_method_data, string $type, WC_Payment_Token $token ): array;

	/**
	 * Delete payment method validation.
	 *
	 * @param bool             $valid Current validation status.
	 * @param string           $type The payment method type.
	 * @param WC_Payment_Token $token The payment token.
	 * @param object           $method The QPilot payment method.
	 * @return bool Whether the deletion is valid.
	 */
	abstract public function delete_payment_method( bool $valid, string $type, WC_Payment_Token $token, object $method ): bool;

	/**
	 * Tokenize payment method for non-standard gateways.
	 *
	 * @param string $token The token data.
	 * @param string $autoship_method_id The Autoship method ID.
	 * @param string $autoship_method_type The Autoship method type.
	 * @return array The tokenized payment method data.
	 */
	public function tokenize_payment_method( string $token, string $autoship_method_id, string $autoship_method_type ): array {
		return array();
	}

	/**
	 * Add metadata to payment.
	 *
	 * @param array    $payment_meta The payment metadata.
	 * @param WC_Order $order The order object.
	 * @param array    $payment_method The payment method.
	 * @return array The updated payment metadata.
	 */
	public function add_metadata( array $payment_meta, WC_Order $order, array $payment_method ): array {
		// Default implementation - can be overridden by specific gateways.
		return $payment_meta;
	}

	/**
	 * Display apply to all orders button.
	 *
	 * @param array $list_item The list item data.
	 * @param mixed $payment_token The payment token (WC_Payment_Token or SkyVerge payment profile).
	 * @return string The button HTML.
	 */
	public function display_apply_to_all_orders_button( array $list_item, $payment_token ): array {
		return $list_item;
	}
}
