<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Carriers Management service.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Implementations;

use Autoship\Domain\Nextime\DeliveryDate;
use Autoship\Domain\Nextime\ShippingLine;
use Autoship\Domain\Nextime\ShippingOptions;
use Autoship\Domain\Nextime\ShippingRate;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Services\Nextime\Carriers\ShippingOptionsRequest;
use Autoship\Services\Nextime\Carriers\ShippingOptionsResponse;
use Autoship\Services\Nextime\Interfaces\CarriersManagementInterface;
use Autoship\Services\Nextime\NextimeHttpClientInterface;
use Autoship\Services\Nextime\NextimeHttpException;

/**
 * The Nextime Carriers Management.
 *
 * @package Autoship\Services\Nextime
 */
class NextimeCarriersManagement implements CarriersManagementInterface {

	/**
	 * The Nextime HTTP client.
	 *
	 * @var NextimeHttpClientInterface
	 */
	private NextimeHttpClientInterface $client;

	/**
	 * The logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param NextimeHttpClientInterface $client The Nextime HTTP client.
	 * @param LoggerInterface            $logger The logger instance.
	 */
	public function __construct( NextimeHttpClientInterface $client, LoggerInterface $logger ) {
		$this->client = $client;
		$this->logger = $logger;
	}

	/**
	 * Gets the shipping options for the given request.
	 *
	 * @param ShippingOptionsRequest $request The shipping options request.
	 *
	 * @return ShippingOptionsResponse The shipping options response.
	 */
	public function get_shipping_options( ShippingOptionsRequest $request ): ShippingOptionsResponse {
		$body = $this->get_shipping_options_data( $request );

		try {
			$data = $this->client->post( 'shipping-options', $body );
			if ( ! empty( $data ) ) {
				return $this->build_shipping_options_response( $data );
			}
		} catch ( NextimeHttpException $e ) {
			$this->logger->log( 'Error building Nextime Shipping Options', $e->getMessage() );
		}

		return $this->build_failed_response();
	}

	/**
	 * Gets the shipping options data to send to Nextime.
	 *
	 * @param ShippingOptionsRequest $request The request to retrieve shipping rates.
	 *
	 * @return array
	 */
	private function get_shipping_options_data( ShippingOptionsRequest $request ): array {
		$destination = array(
			'country'    => $request->get_country(),
			'postalCode' => $request->get_postal_code(),
		);

		// Add optional fields only if they exist and are non‐empty.
		$state = trim( $request->get_state() );
		if ( ! empty( $state ) ) {
			$destination['state'] = $state;
		}

		$city = trim( $request->get_city() );
		if ( ! empty( $city ) ) {
			$destination['city'] = $city;
		}

		$street = trim( $request->get_street() );
		if ( ! empty( $street ) ) {
			$destination['street'] = $street;
		}

		$street2 = trim( $request->get_street_2() );
		if ( ! empty( $street2 ) ) {
			$destination['street2'] = $street2;
		}

		$data = array(
			'orderId'         => $request->get_order_id(),
			'shippingAddress' => $destination,
		);

		$items = array();
		foreach ( $request->get_items() as $item ) {
			$items[] = array(
				'id'           => $item->get_id(),
				'productId'    => $item->get_product_id(),
				'qty'          => $item->get_quantity(),
				'regularPrice' => $item->get_regular_price(),
				'salePrice'    => $item->get_sale_price(),
			);
		}

		$data['items'] = $items;

		return $data;
	}

	/**
	 * Creates a shipping line from the given data.
	 *
	 * @param array $shipping_line_data The shipping line data.
	 *
	 * @return ShippingLine
	 */
	private function build_shipping_line( array $shipping_line_data ): ShippingLine {
		$line = new ShippingLine();

		$name                 = trim( $shipping_line_data['name'] );
		$integration_provider = trim( $shipping_line_data['integrationProvider'] );
		$shipping_method      = trim( $shipping_line_data['shippingMethod'] );
		$next_order_date      = trim( $shipping_line_data['nextOrderDate'] );
		$next_shipping_date   = trim( $shipping_line_data['nextShippingDate'] );

		if ( ! empty( $name ) ) {
			$line->set_name( $name );
		}

		if ( ! empty( $integration_provider ) ) {
			$line->set_integration_provider( $integration_provider );
		}

		if ( ! empty( $shipping_method ) ) {
			$line->set_shipping_method( $shipping_method );
		}

		if ( ! empty( $next_order_date ) ) {
			$line->set_next_order_date( $next_order_date );
		}

		if ( ! empty( $next_shipping_date ) ) {
			$line->set_next_shipping_date( $next_shipping_date );
		}

		$line->set_total( floatval( $shipping_line_data['total'] ) );
		$line->set_lead_time_in_hours( intval( $shipping_line_data['leadTimeInHours'] ) );

		return $line;
	}

	/**
	 * Builds a delivery date from the given data.
	 *
	 * @param array $delivery_date_data The delivery date data.
	 *
	 * @return DeliveryDate
	 */
	private function build_delivery_date( array $delivery_date_data ): DeliveryDate {
		$delivery = new DeliveryDate();

		$external_id          = trim( $delivery_date_data['externalId'] );
		$delivery_date        = trim( $delivery_date_data['deliveryDate'] );
		$shipping_cutoff_date = trim( $delivery_date_data['shippingCutOffDate'] );

		if ( ! empty( $external_id ) ) {
			$delivery->set_external_id( $external_id );
		}

		if ( ! empty( $delivery_date ) ) {
			$delivery->set_delivery_date( $delivery_date );
		}

		if ( ! empty( $shipping_cutoff_date ) ) {
			$delivery->set_shipping_cutoff_date( $shipping_cutoff_date );
		}

		$delivery->set_considered_secondary_cutoff( boolval( $delivery_date_data['consideredSecondaryCutOff'] ) );

		foreach ( $delivery_date_data['shippingLines'] as $shipping_line_data ) {
			$delivery->add_shipping_line( $this->build_shipping_line( $shipping_line_data ) );
		}

		return $delivery;
	}

	/**
	 * Builds the shipping options from the given data.
	 *
	 * @param array $shipping_options_data The shipping options data.
	 *
	 * @return ShippingOptions
	 */
	private function build_shipping_options( array $shipping_options_data ): ShippingOptions {
		$options = new ShippingOptions();

		$mode = trim( $shipping_options_data['mode'] );
		if ( ! empty( $mode ) ) {
			$options->set_mode( $mode );
		}

		$options->set_recommended_delivery_date( $this->build_delivery_date( $shipping_options_data['recommendedDeliveryDate'] ) );

		foreach ( $shipping_options_data['deliveryDates'] as $delivery_date_data ) {
			$options->add_delivery_date( $this->build_delivery_date( $delivery_date_data ) );
		}

		return $options;
	}

	/**
	 * Builds a shipping rate from the given data.
	 *
	 * @param array $shipping_rate_data The shipping rate data.
	 *
	 * @return ShippingRate
	 */
	private function build_shipping_rate( array $shipping_rate_data ): ShippingRate {
		$rate = new ShippingRate();

		$succeeded = boolval( $shipping_rate_data['succeeded'] );

		$rate->set_succeeded( $succeeded );
		if ( $succeeded && isset( $shipping_rate_data['shippingLines'] ) ) {
			$rate->set_shipping_options( $this->build_shipping_options( $shipping_rate_data['shippingLines'] ) );
		}

		if ( isset( $shipping_rate_data['errors'] ) ) {
			$rate->set_errors( $shipping_rate_data['errors'] );
		}

		return $rate;
	}

	/**
	 * Creates a failed shipping rate response.
	 *
	 * @return ShippingOptionsResponse
	 */
	private function build_failed_response(): ShippingOptionsResponse {
		$response = new ShippingOptionsResponse();
		$rate     = new ShippingRate();

		$rate->set_succeeded( false );
		$response->set_shipping_rate( $rate );

		return $response;
	}

	/**
	 * Creates a shipping rate response from the given data.
	 *
	 * @param array $data The shipping rate data.
	 *
	 * @return ShippingOptionsResponse
	 */
	private function build_shipping_options_response( array $data ): ShippingOptionsResponse {
		$response = new ShippingOptionsResponse();
		$response->set_shipping_rate( $this->build_shipping_rate( $data ) );

		return $response;
	}
}
