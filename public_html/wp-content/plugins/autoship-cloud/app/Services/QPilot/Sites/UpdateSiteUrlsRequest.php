<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * UpdateSiteUrlsRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Sites;

/**
 * Request object for updating site URLs.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class UpdateSiteUrlsRequest {
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
	 */
	public function __construct() {
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
		$data = array();

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
