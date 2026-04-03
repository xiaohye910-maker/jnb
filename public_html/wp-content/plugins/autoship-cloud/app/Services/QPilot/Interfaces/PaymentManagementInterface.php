<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Payment Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

use Autoship\Services\QPilot\Payments\CreatePaymentMethodRequest;
use Autoship\Services\QPilot\Payments\UpsertPaymentMethodRequest;
use Autoship\Services\QPilot\Payments\PaymentMethodResponse;

/**
 * Interface for payment method management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface PaymentManagementInterface {
	/**
	 * Get a payment method by ID.
	 *
	 * @param int $method_id The payment method ID.
	 * @return PaymentMethodResponse The payment method object.
	 */
	public function get_payment_method( int $method_id ): PaymentMethodResponse;

	/**
	 * Get payment methods for a customer.
	 *
	 * @param int $customer_id The customer ID.
	 * @return array<PaymentMethodResponse> An array of PaymentMethodResponse objects.
	 */
	public function get_payment_methods( int $customer_id ): array;

	/**
	 * Create a payment method.
	 *
	 * @param CreatePaymentMethodRequest $request The payment method data.
	 * @return PaymentMethodResponse The created payment method object.
	 */
	public function create_payment_method( CreatePaymentMethodRequest $request ): PaymentMethodResponse;

	/**
	 * Update a payment method.
	 *
	 * @param UpsertPaymentMethodRequest $request The payment method data.
	 * @return PaymentMethodResponse The updated payment method object.
	 */
	public function upsert_payment_method( UpsertPaymentMethodRequest $request ): PaymentMethodResponse;

	/**
	 * Delete a payment method.
	 *
	 * @param int $method_id The payment method ID.
	 * @return bool True on success.
	 */
	public function delete_payment_method( int $method_id ): bool;

	/**
	 * Get payment integrations.
	 *
	 * @return array An array of payment integration objects.
	 */
	public function get_payment_integrations(): array;

	/**
	 * Create a payment integration.
	 *
	 * @param array $integration_data The payment integration data.
	 * @return object The created payment integration object.
	 */
	public function create_payment_integration( array $integration_data ): object;
}
