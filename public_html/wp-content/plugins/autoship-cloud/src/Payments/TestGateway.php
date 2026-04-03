<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Defines the test gateway for Autoship Cloud. It can be configured as a payment method in WooCommerce.
 *
 * @package Autoship
 * @since 1.0.0
 */

/**
 * The Autoship Payments Test Gateway, used for simulating payments.
 *
 * @package Autoship
 * @since 1.0.0
 */
class Autoship_Payments_TestGateway extends WC_Payment_Gateway {

	/**
	 * Initializes a new instance of the class.
	 */
	public function __construct() {
		$this->id                 = 'autoship-test-gateway';
		$this->method_title       = __( 'Autoship Test Gateway', 'autoship' );
		$this->method_description = __( 'This payment gateway is for testing Autoship only.', 'autoship' );
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Set up the configuration fields.
	 *
	 * @return void
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'autoship' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Autoship Test Gateway', 'autoship' ),
				'default' => 'no',
			),
			'title'       => array(
				'title'       => __( 'Title', 'autoship' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'autoship' ),
				'default'     => __( 'Autoship Test Gateway', 'autoship' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'autoship' ),
				'type'        => 'textarea',
				'default'     => '',
				'placeholder' => $this->get_default_description(),
			),
		);
	}

	/**
	 * Simulates an order payment.
	 *
	 * @param int $order_id The WooCommerce order identifier.
	 *
	 * @return array
	 * @throws WC_Data_Exception When there is a misconfiguration.
	 */
	public function process_payment( $order_id ): array {
		// Get order.
		$order = wc_get_order( $order_id );

		// Set tokenization meta.
		$gateway_customer_id = 'test-customer-' . $order->get_user_id();
		$gateway_payment_id  = 'test-payment';
		$order->set_payment_method( $this->id );
		$order->add_meta_data( '_autoship_test_gateway_customer_id', $gateway_customer_id );
		$order->add_meta_data( '_autoship_test_gateway_payment_id', $gateway_payment_id );

		// Set complete.
		$transaction_id = 'test-' . time();
		$order->payment_complete( $transaction_id );

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thank-you redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Defines the payment fields.
	 *
	 * @return void
	 */
	public function payment_fields(): void {
		$description = $this->get_description();
		if ( empty( $description ) ) {
			$description = $this->get_default_description();
		}

		echo wp_kses_post( '<div class="autoship-test-gateway-description">' . $description . '</div>' );
	}

	/**
	 * Gets the default description.
	 *
	 * @return string|null
	 */
	public function get_default_description(): ?string {
		return __( 'Complete a test checkout. No payments will be collected.', 'autoship' );
	}
}
