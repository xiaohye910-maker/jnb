<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * SiteSettingsResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Sites;

/**
 * Response object for site settings data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class SiteSettingsResponse {
	/**
	 * The site ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * The user ID.
	 *
	 * @var int
	 */
	private int $user_id;

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
	 * The site metadata.
	 *
	 * @var array|null
	 */
	private ?array $metadata = null;

	/**
	 * The raw response data.
	 *
	 * @var \stdClass
	 */
	private \stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param \stdClass $data The response data.
	 */
	public function __construct( \stdClass $data ) {
		$this->raw_data                         = $data;
		$this->id                               = $data->id;
		$this->user_id                          = $data->userId; // phpcs:ignore
		$this->scheduled_orders_client_page_url = $data->scheduledOrdersClientPageUrl ?? null; // phpcs:ignore
		$this->payment_methods_page_url         = $data->paymentMethodsPageUrl ?? null; // phpcs:ignore
		$this->metadata                         = $data->metadata ?? null;
	}

	/**
	 * Get the site ID.
	 *
	 * @return int The site ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the user ID.
	 *
	 * @return int The user ID.
	 */
	public function get_user_id(): int {
		return $this->user_id;
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
	 * Get the site metadata.
	 *
	 * @return array|null The site metadata.
	 */
	public function get_metadata(): ?array {
		return $this->metadata;
	}

	/**
	 * Get the raw response data.
	 *
	 * @return \stdClass The raw response data.
	 */
	public function get_raw_data(): \stdClass {
		return $this->raw_data;
	}

	/**
	 * Convert the response to an array.
	 *
	 * @return array The response as an array.
	 */
	public function to_array(): array {
		return (array) $this->raw_data;
	}
}
