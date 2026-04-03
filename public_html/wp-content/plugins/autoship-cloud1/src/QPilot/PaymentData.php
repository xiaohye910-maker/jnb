<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Defines the QPilotPaymentData placeholder class.
 *
 * @package Autoship
 * @since 1.0.0
 */

/**
 * The QPilot Payment Data Class.
 *
 * @package Autoship
 * @since 1.0.0
 */
class QPilotPaymentData {

	/**
	 * The type of payment method.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * The type of payment method.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * The last four digits of the payment method.
	 *
	 * @var string
	 */
	public $last_four;

	/**
	 * The expiration date of the payment method.
	 *
	 * @var string
	 */
	public $expiration;

	/**
	 * The gateway payment ID.
	 *
	 * @var string
	 */
	public $gateway_payment_id;

	/**
	 * The gateway payment type.
	 *
	 * @var int
	 */
	public $gateway_payment_type;

	/**
	 * The gateway customer ID.
	 *
	 * @var string
	 */
	public $gateway_customer_id;

	/**
	 * Initialize a new instance of the class.
	 *
	 * @param string $type The type of payment method.
	 * @param string $gateway_payment_id The gateway payment ID.
	 * @param string $gateway_payment_type The gateway payment type.
	 * @param string $gateway_customer_id The gateway customer ID.
	 * @param string $expiration The expiration date of the payment method.
	 * @param string $last_four The last four digits of the payment method.
	 * @param string $description The description of the payment method.
	 */
	public function __construct( $type = null, $gateway_payment_id = null, $gateway_payment_type = 7, $gateway_customer_id = null, $expiration = null, $last_four = null, $description = null ) {
		$this->description          = $description;
		$this->type                 = $type;
		$this->last_four            = $last_four;
		$this->expiration           = $expiration;
		$this->gateway_payment_id   = $gateway_payment_id;
		$this->gateway_payment_type = $gateway_payment_type;
		$this->gateway_customer_id  = $gateway_customer_id;
	}
}
