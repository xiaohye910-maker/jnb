<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\PaymentManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Payments\CreatePaymentMethodRequest;
use Autoship\Services\QPilot\Payments\UpsertPaymentMethodRequest;
use Autoship\Services\QPilot\Payments\PaymentMethodResponse;

/**
 * Implementation of the PaymentManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class PaymentManagement implements PaymentManagementInterface {
	/**
	 * The API client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $api_client;

	/**
	 * Constructor.
	 *
	 * @param QPilotHttpClient $api_client The API client.
	 */
	public function __construct( QPilotHttpClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Get a payment method by ID.
	 *
	 * @param int $method_id The payment method ID.
	 * @return PaymentMethodResponse The payment method object.
	 */
	public function get_payment_method( int $method_id ): PaymentMethodResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/PaymentMethods/{$method_id}";
		$response = $this->api_client->get( $endpoint );

		return new PaymentMethodResponse( $response );
	}

	/**
	 * Get payment methods for a customer.
	 *
	 * @param int $customer_id The customer ID.
	 * @return array<PaymentMethodResponse> An array of PaymentMethodResponse objects.
	 */
	public function get_payment_methods( int $customer_id ): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Customers/{$customer_id}/PaymentMethods";

		$response = $this->api_client->get( $endpoint );

		$payment_methods = array();
		foreach ( $response as $method_data ) {
			$payment_methods[] = new PaymentMethodResponse( $method_data );
		}

		return $payment_methods;
	}

	/**
	 * Create a payment method.
	 *
	 * @param CreatePaymentMethodRequest $request The payment method data.
	 * @return PaymentMethodResponse The created payment method object.
	 */
	public function create_payment_method( CreatePaymentMethodRequest $request ): PaymentMethodResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/PaymentMethods";

		$data     = $request->to_array();
		$response = $this->api_client->post( $endpoint, $data );

		return new PaymentMethodResponse( $response );
	}

	/**
	 * Update a payment method.
	 *
	 * @param UpsertPaymentMethodRequest $request The payment method data.
	 * @return PaymentMethodResponse The updated payment method object.
	 */
	public function upsert_payment_method( UpsertPaymentMethodRequest $request ): PaymentMethodResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/PaymentMethods/Upsert";

		$data     = $request->to_array();
		$response = $this->api_client->post( $endpoint, $data );

		return new PaymentMethodResponse( $response );
	}

	/**
	 * Delete a payment method.
	 *
	 * @param int $method_id The payment method ID.
	 * @return bool True on success.
	 */
	public function delete_payment_method( int $method_id ): bool {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/PaymentMethods/{$method_id}";

		$this->api_client->delete( $endpoint );

		return true;
	}

	/**
	 * Get payment integrations.
	 *
	 * @return array An array of payment integration objects.
	 */
	public function get_payment_integrations(): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/PaymentIntegrations";

		$response = $this->api_client->get( $endpoint );

		return $response;
	}

	/**
	 * Create a payment integration.
	 *
	 * @param array $integration_data The payment integration data.
	 * @return object The created payment integration object.
	 */
	public function create_payment_integration( array $integration_data ): object {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/PaymentIntegrations";

		$response = $this->api_client->post( $endpoint, $integration_data );

		return $response;
	}
}
