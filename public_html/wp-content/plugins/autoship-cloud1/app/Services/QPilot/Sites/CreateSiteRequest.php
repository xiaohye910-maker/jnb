<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * CreateSiteRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Sites;

/**
 * Request object for creating a site.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CreateSiteRequest {
	/**
	 * The user ID.
	 *
	 * @var int|null
	 */
	private ?int $user_id = null;

	/**
	 * The API consumer key.
	 *
	 * @var string
	 */
	private string $api_key1;

	/**
	 * The API consumer secret.
	 *
	 * @var string
	 */
	private string $api_key2;

	/**
	 * The scheduled orders client page URL.
	 *
	 * @var string|null
	 */
	private ?string $scheduled_orders_client_page_url = null;

	/**
	 * The payment methods page URL.
	 *
	 * @var string|null
	 */
	private ?string $payment_methods_page_url = null;

	/**
	 * Additional site info.
	 *
	 * @var array|null
	 */
	private ?array $site_info = null;

	/**
	 * Constructor.
	 *
	 * @param string $api_key1 The API consumer key.
	 * @param string $api_key2 The API consumer secret.
	 */
	public function __construct( string $api_key1, string $api_key2 ) {
		$this->api_key1 = $api_key1;
		$this->api_key2 = $api_key2;
	}

	/**
	 * Set the user ID.
	 *
	 * @param int $user_id The user ID.
	 * @return self
	 */
	public function set_user_id( int $user_id ): self {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Set the scheduled orders client page URL.
	 *
	 * @param string $url The scheduled orders client page URL.
	 * @return self
	 */
	public function set_scheduled_orders_client_page_url( string $url ): self {
		$this->scheduled_orders_client_page_url = $url;
		return $this;
	}

	/**
	 * Set the payment methods page URL.
	 *
	 * @param string $url The payment methods page URL.
	 * @return self
	 */
	public function set_payment_methods_page_url( string $url ): self {
		$this->payment_methods_page_url = $url;
		return $this;
	}

	/**
	 * Set additional site info.
	 *
	 * @param array $site_info Additional site info.
	 * @return self
	 */
	public function set_site_info( array $site_info ): self {
		$this->site_info = $site_info;
		return $this;
	}

	/**
	 * Get the user ID.
	 *
	 * @return int|null The user ID.
	 */
	public function get_user_id(): ?int {
		return $this->user_id;
	}

	/**
	 * Get the API consumer key.
	 *
	 * @return string The API consumer key.
	 */
	public function get_api_key1(): string {
		return $this->api_key1;
	}

	/**
	 * Get the API consumer secret.
	 *
	 * @return string The API consumer secret.
	 */
	public function get_api_key2(): string {
		return $this->api_key2;
	}

	/**
	 * Get the scheduled orders client page URL.
	 *
	 * @return string|null The scheduled orders client page URL.
	 */
	public function get_scheduled_orders_client_page_url(): ?string {
		return $this->scheduled_orders_client_page_url;
	}

	/**
	 * Get the payment methods page URL.
	 *
	 * @return string|null The payment methods page URL.
	 */
	public function get_payment_methods_page_url(): ?string {
		return $this->payment_methods_page_url;
	}

	/**
	 * Get additional site info.
	 *
	 * @return array|null Additional site info.
	 */
	public function get_site_info(): ?array {
		return $this->site_info;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'apiKey1' => $this->api_key1,
			'apiKey2' => $this->api_key2,
		);

		if ( null !== $this->user_id ) {
			$data['userId'] = $this->user_id;
		}

		if ( null !== $this->scheduled_orders_client_page_url ) {
			$data['ScheduledOrdersClientPageUrl'] = $this->scheduled_orders_client_page_url;
		}

		if ( null !== $this->payment_methods_page_url ) {
			$data['PaymentMethodsPageUrl'] = $this->payment_methods_page_url;
		}

		// Merge additional site info if available.
		if ( is_array( $this->site_info ) ) {
			$data = array_merge( $this->site_info, $data );
		}

		return $data;
	}
}
