<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class represents a payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Domain;

/**
 * Represents a payment integration.
 *
 * @package Autoship
 * @since 2.8.7
 */
class PaymentIntegration {

	/**
	 * The method type.
	 *
	 * @var int
	 */
	private int $method_type;

	/**
	 * The method id.
	 *
	 * @var string
	 */
	private string $method_id;

	/**
	 * The method name.
	 *
	 * @var string
	 */
	private string $method_name;

	/**
	 * The api account.
	 *
	 * @var string
	 */
	private string $api_account;

	/**
	 * The first api key.
	 *
	 * @var string
	 */
	private string $api_key_1;

	/**
	 * The second api key.
	 *
	 * @var string
	 */
	private string $api_key_2;

	/**
	 * The test mode value.
	 *
	 * @var bool
	 */
	private bool $test_mode;

	/**
	 * The authorizing only value.
	 *
	 * @var bool
	 */
	private bool $authorize_only;

	/**
	 * Initializes a new instance of the class.
	 */
	public function __construct() {
		$this->test_mode      = false;
		$this->authorize_only = false;
		$this->method_type    = 0;
		$this->method_id      = '';
		$this->method_name    = '';
		$this->api_account    = '';
		$this->api_key_1      = '';
		$this->api_key_2      = '';
	}

	/**
	 * Sets the method type.
	 *
	 * @param int $method_type The method type.
	 *
	 * @return void
	 */
	public function set_method_type( int $method_type ): void {
		$this->method_type = $method_type;
	}

	/**
	 * Gets the method type.
	 *
	 * @return int
	 */
	public function get_method_type(): int {
		return $this->method_type;
	}

	/**
	 * Sets the method id.
	 *
	 * @param string $method_id The method id.
	 *
	 * @return void
	 */
	public function set_method_id( string $method_id ): void {
		$this->method_id = $method_id;
	}

	/**
	 * Gets the method id.
	 *
	 * @return string
	 */
	public function get_method_id(): string {
		return $this->method_id;
	}

	/**
	 * Sets the method name.
	 *
	 * @param string $method_name The method name.
	 *
	 * @return void
	 */
	public function set_method_name( string $method_name ): void {
		$this->method_name = $method_name;
	}

	/**
	 * Gets the method name.
	 *
	 * @return string
	 */
	public function get_method_name(): string {
		return $this->method_name;
	}

	/**
	 * Sets the api account.
	 *
	 * @param string $api_account The api account.
	 *
	 * @return void
	 */
	public function set_api_account( string $api_account ): void {
		$this->api_account = $api_account;
	}

	/**
	 * Get the api account.
	 *
	 * @return string
	 */
	public function get_api_account(): string {
		return $this->api_account;
	}

	/**
	 * Set the first api key.
	 *
	 * @param string $api_key_1 The first api key.
	 *
	 * @return void
	 */
	public function set_api_key_1( string $api_key_1 ): void {
		$this->api_key_1 = $api_key_1;
	}

	/**
	 * Get the first api key.
	 *
	 * @return string
	 */
	public function get_api_key_1(): string {
		return $this->api_key_1;
	}

	/**
	 * Set the second api key.
	 *
	 * @param string $api_key_2 The second api key.
	 *
	 * @return void
	 */
	public function set_api_key_2( string $api_key_2 ): void {
		$this->api_key_2 = $api_key_2;
	}

	/**
	 * Get the second api key.
	 *
	 * @return string
	 */
	public function get_api_key_2(): string {
		return $this->api_key_2;
	}

	/**
	 * Set the test mode value.
	 *
	 * @param bool $test_mode The test mode value.
	 * @return void
	 */
	public function set_test_mode( bool $test_mode ): void {
		$this->test_mode = $test_mode;
	}

	/**
	 * Get the test mode value.
	 *
	 * @return bool
	 */
	public function get_test_mode(): bool {
		return $this->test_mode;
	}

	/**
	 * Set the authorization only value.
	 *
	 * @param bool $authorize_only The value that indicates if it is authorization only or not.
	 *
	 * @return void
	 */
	public function set_authorize_only( bool $authorize_only ): void {
		$this->authorize_only = $authorize_only;
	}

	/**
	 * Get the authorization only value.
	 *
	 * @return bool
	 */
	public function get_authorize_only(): bool {
		return $this->authorize_only;
	}

	/**
	 * Gets the value indicating if the payment integration is in test mode or not.
	 *
	 * @return bool
	 */
	public function is_test_mode(): bool {
		return true === $this->test_mode;
	}

	/**
	 * Gets the value indicating if the payment integration is valid or not.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		return true;
	}

	/**
	 * Gets the integration data as an associative array.
	 *
	 * @return array
	 */
	public function get_data(): array {
		$data = array(
			'PaymentMethodType' => PaymentMethodType::get_enum_name( $this->method_type ),
			'TestMode'          => $this->test_mode,
			'AuthorizeOnly'     => $this->authorize_only,
		);

		if ( ! empty( $this->api_account ) ) {
			$data['ApiAccount'] = $this->api_account;
		}

		if ( ! empty( $this->api_key_1 ) ) {
			$data['ApiKey1'] = $this->api_key_1;
		}

		if ( ! empty( $this->api_key_2 ) ) {
			$data['ApiKey2'] = $this->api_key_2;
		}

		return $data;
	}
}
