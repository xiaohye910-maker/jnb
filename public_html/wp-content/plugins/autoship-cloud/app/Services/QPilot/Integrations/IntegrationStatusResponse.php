<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * IntegrationStatusResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Integrations;

/**
 * Response object for integration status data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class IntegrationStatusResponse {
	/**
	 * The integration status.
	 *
	 * @var string|null
	 */
	private ?string $status = null;

	/**
	 * Whether the integration is connected.
	 *
	 * @var bool|null
	 */
	private ?bool $is_connected = null;

	/**
	 * The integration version.
	 *
	 * @var string|null
	 */
	private ?string $version = null;

	/**
	 * The integration messages.
	 *
	 * @var array|null
	 */
	private ?array $messages = null;

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
		$this->raw_data     = $data;
		$this->status       = $data->status ?? null;
		$this->is_connected = $data->isConnected ?? null; // phpcs:ignore
		$this->version      = $data->version ?? null;
		$this->messages     = $data->messages ?? null;
	}

	/**
	 * Get the integration status.
	 *
	 * @return string|null The integration status.
	 */
	public function get_status(): ?string {
		return $this->status;
	}

	/**
	 * Get whether the integration is connected.
	 *
	 * @return bool|null Whether the integration is connected.
	 */
	public function is_connected(): ?bool {
		return $this->is_connected;
	}

	/**
	 * Get the integration version.
	 *
	 * @return string|null The integration version.
	 */
	public function get_version(): ?string {
		return $this->version;
	}

	/**
	 * Get the integration messages.
	 *
	 * @return array|null The integration messages.
	 */
	public function get_messages(): ?array {
		return $this->messages;
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
