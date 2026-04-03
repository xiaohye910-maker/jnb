<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Autoship Advanced Shipping Service.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10
 */

namespace Autoship\Modules\Nextime;

use Autoship\Core\Environment;
use Autoship\Services\Logging\Logger;
use Autoship\Services\Nextime\NextimeServiceInterface;
use Autoship\Services\Nextime\NextimeSettingsInterface;
use Exception;
use WC_Order;

/**
 * Defines the Autoship Advanced Shipping Service (Nextime).
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10
 */
class NextimeService {

	/**
	 * The Nextime settings.
	 *
	 * @var NextimeSettingsInterface
	 */
	private NextimeSettingsInterface $settings;

	/**
	 * The Nextime service.
	 *
	 * @var NextimeServiceInterface
	 */
	private NextimeServiceInterface $nextime;

	/**
	 * Indicates whether the Nextime service has been initialized.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;

	/**
	 * Initialize the plugin by setting up dependencies, locale, admin, and public hooks.
	 *
	 * @param NextimeSettingsInterface $settings The Nextime settings.
	 * @param NextimeServiceInterface  $nextime The Nextime service.
	 */
	public function __construct( NextimeSettingsInterface $settings, NextimeServiceInterface $nextime ) {
		$this->settings = $settings;
		$this->nextime  = $nextime;
	}

	/**
	 * Initializes the Nextime service.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( self::$initialized ) {
			return;
		}

		// This action creates the shipping method in WooCommerce.
		add_action( 'woocommerce_shipping_init', array( $this, 'create_shipping_method' ) );

		// This action copies the Nextime metadata from the shipping line to the order.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'nextime_copy_all_shipping_meta_to_order' ), 10 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'nextime_copy_all_shipping_meta_to_order' ), 10 );

		// This action enqueues the Nextime WooCommerce blocks script to display dates in a new line.
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_nextime_woocommerce_blocks_script' ) );

		// This filter appends the Nextime metadata into the scheduled order data to send to QPilot if it exists.
		add_filter( 'woocommerce_scheduled_order_data', array( $this, 'add_nextime_meta_to_scheduled_order_data' ), 10, 2 );

		// This filter appends the Nextime metadata into the scheduled order data to send to QPilot if it exists.
		add_filter( 'autoship_create_scheduled_order_data', array( $this, 'add_nextime_meta_to_scheduled_order_data' ), 10, 2 );

		// Add Nextime to available shipping methods.
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_nextime_shipping_method' ) );

		self::$initialized = true;
	}

	/**
	 * Registers the Nextime Shipping calculator.
	 *
	 * @param array $methods The methods to register.
	 *
	 * @return mixed
	 */
	public function add_nextime_shipping_method( array $methods ): array {
		$methods['autoship_nextime'] = '\Autoship\Domain\Nextime\NextimeShippingCalculator';

		return $methods;
	}

	/**
	 * Creates the shipping method.
	 *
	 * @return void
	 */
	public function create_shipping_method(): void {
		if ( ! class_exists( '\Autoship\Domain\Nextime\NextimeShippingCalculator' ) ) {
			require_once __DIR__ . '/../../Domain/Nextime/NextimeShippingCalculator.php';
		}
	}

	/**
	 * After the order object is instantiated (but before it's saved),
	 * copy _Nextime_… stuff from each shipping line to the order.
	 *
	 * @param mixed $order_id    The order object being created.
	 */
	public function nextime_copy_all_shipping_meta_to_order( $order_id ): void {

		if ( $order_id instanceof WC_Order ) {
			$order = $order_id;
		} else {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				Logger::log( 'Autoship Nextime', "Unable to copy the shipping metadata to the WooCommerce order before sending it to QPilot. The input data is: $order_id" );

				return;
			}
		}

		if ( ! ( $order instanceof WC_Order ) ) {
			Logger::log( 'Autoship Nextime', "Unable to validate the WooCommerce order to copy the metadata from Nextime before sending it to QPilot. The input data is: $order_id" );

			return;
		}

		// Get all shipping items in this order.
		$shipping_items = $order->get_items( 'shipping' );

		foreach ( $shipping_items as $item_id => $shipping_item ) {
			if ( 'autoship_nextime' !== $shipping_item->get_method_id() ) {
				continue;
			}

			Logger::log(
				__( 'Autoship Nextime Extension', 'autoship' ),
				sprintf(
				// translators: The order id %1$s, and the shipping item id %2$s.
					__( 'Performing Nextime metadata copy to the WooCommerce Order ID: %1$s based on the Shipping Item ID: %2$s.', 'autoship' ),
					$order->get_id(),
					$item_id
				)
			);

			// Define the keys to copy from the Shipping Item to the Order.
			$keys_to_copy = array(
				'_Nextime_Delivery_Date',
				'_Nextime_Charge_Date',
				'_Nextime_Ship_Date',
				'_Nextime_Shipping_Method',
				'_Nextime_Shipping_Name',
				'_Nextime_Shipping_Total',
				'_Nextime_Calculated_Date',
			);

			$changed = false;
			foreach ( $keys_to_copy as $meta_key ) {

				$value = $shipping_item->get_meta( $meta_key, true );
				if ( ! empty( $value ) ) {

					Logger::log(
						__( 'Autoship Nextime Extension', 'autoship' ),
						sprintf(
						// translators: The meta key to review %1$s, the value %2$s.
							__( 'Updating the order meta data with key %1$s with and value %2$s.', 'autoship' ),
							$meta_key,
							$value
						)
					);

					$order->update_meta_data( $meta_key, $value );
					$changed = true;
				} else {
					Logger::log(
						__( 'Autoship Nextime Extension', 'autoship' ),
						sprintf(
						// translators: The meta key to review %1$s, the value %2$s.
							__( 'The shipping item does contain the meta key %1$s with an empty value %2$s.', 'autoship' ),
							$meta_key,
							$value
						)
					);
				}
			}

			if ( $changed ) {
				Logger::log(
					__( 'Autoship Nextime Extension', 'autoship' ),
					sprintf(
					// translators: The meta key to review %s.
						__( 'Saving order with ID %s.', 'autoship' ),
						$order->get_id()
					)
				);

				$order->save();
			}
		}
	}

	/**
	 * Adds the WooCommerce order's Nextime metadata to the scheduled order data.
	 *
	 * @param array $scheduled_order_data The scheduled order array.
	 * @param int   $order_id The order identifier.
	 *
	 * @return mixed
	 */
	public function add_nextime_meta_to_scheduled_order_data( array $scheduled_order_data, int $order_id ): array {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return $scheduled_order_data;
		}

		$nextime_keys = array(
			'_Nextime_Delivery_Date',
			'_Nextime_Charge_Date',
			'_Nextime_Ship_Date',
			'_Nextime_Shipping_Method',
			'_Nextime_Shipping_Name',
			'_Nextime_Shipping_Total',
			'_Nextime_Calculated_Date',
		);

		$metadata = array();
		foreach ( $nextime_keys as $key ) {
			$value = $order->get_meta( $key, true );
			if ( '' !== $value ) {
				$metadata[ $key ] = $value;
			}
		}

		// If there is Nextime metadata, add it to the scheduled order data.
		if ( ! empty( $metadata ) ) {
			if ( array_key_exists( 'metadata', $scheduled_order_data ) ) {
				$scheduled_order_data['metadata'] = array_merge( $scheduled_order_data['metadata'], $metadata );
			} else {
				$scheduled_order_data['metadata'] = $metadata;
			}
		}

		return $scheduled_order_data;
	}

	/**
	 * Enqueues the assets for the quicklaunch page.
	 */
	public function enqueue_nextime_woocommerce_blocks_script() {
		$environment = new Environment();
		$version     = $environment->get_autoship_version();
		$plugin_url  = $environment->get_autoship_plugin_url();

		wp_enqueue_script( 'autoship-nextime-blocks-script', $plugin_url . 'js/nextime/script.js', array( 'wp-hooks', 'wp-element', 'wp-i18n', 'wc-blocks-checkout' ), $version, true );
	}

	/**
	 * Verify if Nextime is enabled or not.
	 *
	 * @return bool
	 */
	public function is_nextime_enabled(): bool {
		try {
			// Check if the site is connected to QPilot.
			if ( ! $this->can_perform_check() ) {
				return false;
			}

			// Check if there must be a check.
			if ( ! $this->must_perform_check() ) {
				return $this->settings->get_is_enabled();
			}

			$response = $this->nextime->get_site_settings();

			$this->settings->set_site_id( $response->get_site_id() );
			$this->settings->set_site_token( $response->get_site_token() );
			$this->settings->set_integration_id( $response->get_integration_id() );
			$this->settings->set_display_delivery_date( $response->should_display_delivery_date() );
			$this->settings->set_align_next_occurrence_date( $response->should_align_next_occurrence_date() );
			$this->settings->set_is_enabled( $response->is_enabled() );
			$this->settings->set_last_check_status( $response->is_enabled() ? 'enabled' : 'disabled' );
			$this->settings->set_last_check_timestamp( time() );

			return $this->settings->get_is_enabled();
		} catch ( Exception $exception ) {
			Logger::log(
				'Autoship Nextime Exception',
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to boot the Nextime Module. Details: %s', $exception->getMessage() )
			);

			return false;
		}
	}

	/**
	 * Returns true if the Nextime check must be performed.
	 *
	 * @return bool
	 */
	private function must_perform_check(): bool {
		$last_status = $this->settings->get_last_check_status();
		if ( 'unknown' === $last_status ) {
			return true;
		}

		// Check if the last check was more than 10 minutes ago.
		$last_timestamp = $this->settings->get_last_check_timestamp();
		$delta          = apply_filters( 'autoship_nextime_check_interval', 60 * 60 * 24 );
		$elapsed        = time() - $last_timestamp;

		return $elapsed > $delta;
	}

	/**
	 * Returns a value indicating whether the check can be performed.
	 *
	 * @return bool
	 */
	private function can_perform_check(): bool {
		// Check if the site is connected to QPilot.
		return autoship_has_credentials() && autoship_has_auth_token();
	}
}
