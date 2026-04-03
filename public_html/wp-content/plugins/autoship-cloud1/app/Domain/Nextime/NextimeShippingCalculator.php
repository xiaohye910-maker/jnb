<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Shipping Calculator
 *
 * @package Autoship Cloud Nextime Extension
 * @subpackage Nextime
 * @since 2.10.0
 */

namespace Autoship\Domain\Nextime;

use Autoship\Core\Plugin;
use Autoship\Services\Logging\Logger;
use Autoship\Services\Nextime\Carriers\ShippingOptionsRequest;
use Autoship\Services\Nextime\Carriers\ShippingOptionsRequestItem;
use Autoship\Services\Nextime\NextimeServiceInterface;
use Autoship\Services\Nextime\NextimeSettingsInterface;
use Throwable;
use WC_Shipping_Method;

/**
 * Defines the Nextime Shipping Calculator.
 *
 * @package Autoship
 * @subpackage Nextime
 */
class NextimeShippingCalculator extends WC_Shipping_Method {

	/**
	 * The api key of the service.
	 *
	 * @var string
	 */
	public string $api_key;

	/**
	 * The default cost of the shipping.
	 *
	 * @var float
	 */
	public float $default_cost;

	/**
	 * The default format of the delivery date.
	 *
	 * @var string
	 */
	public string $default_format;

	/**
	 * The default site identifier.
	 *
	 * @var int
	 */
	public int $site_id;

	/**
	 * Initializes a new instance of the shipping calculator.
	 *
	 * @param int $instance_id The instance id of the calculator.
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id                 = 'autoship_nextime';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Autoship Advanced Shipping - Nextime', 'autoship' );
		$this->method_description = __( 'Fetch shipping rates from Nextime and include calculation date.', 'autoship' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
		);

		$this->init();
	}

	/**
	 * Initialize the form fields for separate instances.
	 *
	 * @return void
	 */
	private function init_instance_form_fields(): void {
		$fields = array(
			'title'          => array(
				'title'       => __( 'Method Title', 'autoship' ),
				'type'        => 'text',
				'description' => __( 'Title to be displayed at checkout.', 'autoship' ),
				'default'     => __( 'Nextime', 'autoship' ),
				'desc_tip'    => true,
			),
			'default_cost'   => array(
				'title'             => __( 'Default Shipping Cost', 'autoship' ),
				'type'              => 'number',
				'description'       => __( 'Default cost (in store currency) to use when API fails.', 'autoship' ),
				'default'           => '0',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
			),
			'default_format' => array(
				'title'       => __( 'Delivery Date Format ', 'autoship' ),
				'type'        => 'text',
				'description' => __( 'Date format to be displayed at checkout.', 'autoship' ),
				'default'     => 'l, F dS Y',
				'desc_tip'    => true,
			),
		);

		$this->instance_form_fields = $fields;
	}

	/**
	 * Load and process admin options.
	 *
	 * @return void
	 */
	public function init(): void {
		// Save settings in admin if any have been defined.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		$this->init_instance_form_fields();

		$this->title          = $this->get_option( 'title' );
		$this->default_cost   = floatval( $this->get_option( 'default_cost', 0 ) );
		$this->default_format = $this->get_option( 'default_format', 'l, F dS Y' );

		$this->site_id = 0;
		$this->api_key = '';

		// Get the Nextime settings.
		try {
			$container = Plugin::get_service_container();
			$settings  = $container->get( NextimeSettingsInterface::class );
			if ( $settings instanceof NextimeSettingsInterface ) {
				$this->api_key = $settings->get_site_token();
				$this->site_id = $settings->get_site_id();
			} else {
				Logger::log( 'Autoship Nextime Extension', 'Unable to retrieve Nextime settings.' );
			}
		} catch ( Throwable $e ) {
			Logger::log( 'Autoship Nextime Extension', 'Unable to retrieve Nextime settings.' );
		}
	}

	/**
	 * Calculate the shipping rate by calling Nextime API and processing the response body.
	 *
	 * @param array $package The package to get the shipping rate.
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ): void {
		try {
			$container = Plugin::get_service_container();
			$nextime   = $container->get( NextimeServiceInterface::class );
			if ( ! ( $nextime instanceof NextimeServiceInterface ) ) {
				Logger::log( 'Autoship Nextime Extension', 'Unable to retrieve Nextime service. Using default rate.' );

				$this->add_default_rate();
				return;
			}
		} catch ( Throwable $e ) {
			Logger::log( 'Autoship Nextime Extension', 'Unable to retrieve Nextime service. Using default rate.' );

			$this->add_default_rate();
			return;
		}

		// Verify the required information for the Nextime request.
		if ( empty( $this->site_id ) ) {
			Logger::log( 'Autoship Nextime Extension', 'The Nextime Site ID is missing. Using default rate.' );

			$this->add_default_rate();
			return;
		}

		// Verify required fields (country & postal_code).
		$country = isset( $package['destination']['country'] ) ? trim( $package['destination']['country'] ) : '';
		$postal  = isset( $package['destination']['postcode'] ) ? trim( $package['destination']['postcode'] ) : '';

		// If there is no country or postal code, return the default shipping rate.
		if ( empty( $country ) || empty( $postal ) ) {
			Logger::log(
				'Autoship Nextime Extension',
				sprintf(
					// translators: The country value is %1$s, and the postal code value is %2$s.
					'Unable to calculate shipping rate due an invalid country (%1$s) or postal code (%2$s) values. Using default rate.',
					$country,
					$postal
				)
			);

			$this->add_default_rate();
			return;
		}

		// Generates a random order ID.
		$order_id = str_shuffle( md5( microtime() ) );

		$request = new ShippingOptionsRequest( $order_id, $postal, $country );

		// Add optional fields only if they exist and are non‐empty.
		if ( ! empty( $package['destination']['state'] ) ) {
			$request->set_state( trim( $package['destination']['state'] ) );
		}

		if ( ! empty( $package['destination']['city'] ) ) {
			$request->set_city( trim( $package['destination']['city'] ) );
		}

		if ( ! empty( $package['destination']['address'] ) ) {
			$request->set_street( trim( $package['destination']['address'] ) );
		}

		if ( ! empty( $package['destination']['address_2'] ) ) {
			$request->set_street2( trim( $package['destination']['address_2'] ) );
		}

		if ( ! empty( $package['contents'] ) && is_array( $package['contents'] ) ) {
			$product_id = 1;
			foreach ( $package['contents'] as $item ) {

				$request_item = new ShippingOptionsRequestItem();
				$request_item->set_id( $product_id );
				$request_item->set_product_id( $item['product_id'] );
				$request_item->set_quantity( $item['quantity'] );
				$request_item->set_regular_price( $item['line_total'] );
				$request_item->set_sale_price( $item['line_total'] );

				$request->add_item( $request_item );

				++$product_id;
			}
		}

		$response = $nextime->get_shipping_options( $request );

		// Gets the shipping rate from the response.
		$rate = $response->get_shipping_rate();
		if ( null === $rate ) {
			Logger::log( 'Autoship Nextime Extension', 'There was an error while retrieving rates from Nextime. The shipping rate is not available. Using default rate.' );
			$this->add_default_rate();
			return;
		}

		// Checks if the rate was successful.
		if ( true !== $rate->get_succeeded() ) {
			Logger::log( 'Autoship Nextime Extension', 'There was an error while retrieving rates from Nextime, using default rate.' );
			$errors = $rate->get_errors();
			if ( ! empty( $errors ) ) {
				foreach ( $errors as $error ) {
					Logger::log( 'Autoship Nextime Extension', $error );
				}
			}
			$this->add_default_rate();
			return;
		}

		$options = $rate->get_shipping_options();
		if ( null === $options ) {
			$this->add_default_rate();
			return;
		}

		$date       = gmdate( 'Y-m-d H:i:s' );
		$deliveries = $options->get_delivery_dates();

		// If rates returned, add each; otherwise fallback.
		if ( empty( $deliveries ) ) {
			Logger::log( 'Autoship Nextime Extension', 'There are no available rates from Nextime. Using default rate.' );

			$this->add_default_rate();
			return;
		}

		$counter = 1;
		foreach ( $deliveries as $delivery ) {
			if ( ! $delivery instanceof DeliveryDate ) {
				Logger::log( 'Autoship Nextime Extension', 'The expected delivery date is not valid. Looking for more.' );
				continue;
			}

			// If we have 2 rates, we can stop.
			if ( $counter > 2 ) {
				break;
			}

			$lines = $delivery->get_shipping_lines();
			if ( empty( $lines ) ) {
				Logger::log( 'Autoship Nextime Extension', 'The expected shipping lines are not valid. Looking for more.' );
				continue;
			}

			$line = $lines[0];
			if ( ! $line instanceof ShippingLine ) {
				Logger::log( 'Autoship Nextime Extension', 'The expected shipping line is not valid. Looking for more.' );
				continue;
			}

			// Build the meta_data array.
			$meta_data = array(
				'_Nextime_Calculated_Date' => $date,
				'_Nextime_Delivery_Date'   => $delivery->get_delivery_date() ?? '',
				'_Nextime_Charge_Date'     => $line->get_next_order_date() ?? '',
				'_Nextime_Ship_Date'       => $line->get_next_shipping_date() ?? '',
				'_Nextime_Shipping_Method' => $line->get_shipping_method() ?? '',
				'_Nextime_Shipping_Name'   => $line->get_name() ?? '',
				'_Nextime_Shipping_Total'  => $line->get_total() ?? '',
			);

			$rate = array(
				'id'        => $this->id . '_' . $counter,
				'label'     => "{$line->get_name()} ({$delivery->get_formatted_delivery_date( $this->default_format )})",
				'cost'      => $line->get_total(),
				'meta_data' => $meta_data,
			);

			$this->add_rate( $rate );

			++$counter;
		}
	}

	/**
	 * Adds the default shipping rate.
	 *
	 * @return void
	 */
	private function add_default_rate(): void {
		$rate = $this->create_default_rate();
		$this->add_rate( $rate );
	}

	/**
	 * Represents the default shipping rate.
	 *
	 * @return array
	 */
	private function create_default_rate(): array {
		return array(
			'id'        => $this->id,
			'label'     => $this->title,
			'cost'      => $this->default_cost,
			'meta_data' => array(
				'_autoship_nextime' => array(
					'_Nextime_Calculated_Date' => date_i18n( 'Y-m-d H:i:s' ),
				),
			),
		);
	}
}
