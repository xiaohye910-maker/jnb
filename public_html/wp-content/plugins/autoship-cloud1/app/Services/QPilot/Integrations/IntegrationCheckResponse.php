<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * IntegrationCheckResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Integrations;

/**
 * Response object for integration check data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class IntegrationCheckResponse {
	/**
	 * Whether the integration check was successful.
	 *
	 * @var bool|null
	 */
	private ?bool $success = null;

	/**
	 * The integration check status.
	 *
	 * @var string|null
	 */
	private ?string $status = null;

	/**
	 * The integration check messages.
	 *
	 * @var array|null
	 */
	private ?array $messages = null;

	/**
	 * The integration check details.
	 *
	 * @var array|null
	 */
	private ?array $details = null;

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
		$this->raw_data = $data;
		$this->success  = $data->success ?? null;
		$this->status   = $data->status ?? null;
		$this->messages = $data->messages ?? null;
		$this->details  = $data->details ?? null;
	}

	/**
	 * Get whether the integration check was successful.
	 *
	 * @return bool|null Whether the integration check was successful.
	 */
	public function is_success(): ?bool {
		return $this->success;
	}

	/**
	 * Get the integration check status.
	 *
	 * @return string|null The integration check status.
	 */
	public function get_status(): ?string {
		return $this->status;
	}

	/**
	 * Get the integration check messages.
	 *
	 * @return array|null The integration check messages.
	 */
	public function get_messages(): ?array {
		return $this->messages;
	}

	/**
	 * Get the integration check details.
	 *
	 * @return array|null The integration check details.
	 */
	public function get_details(): ?array {
		return $this->details;
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
