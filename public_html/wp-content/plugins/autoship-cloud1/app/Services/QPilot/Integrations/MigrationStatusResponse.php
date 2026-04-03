<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * MigrationStatusResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Integrations;

use stdClass;

/**
 * Response object for migration status data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class MigrationStatusResponse {
	/**
	 * Whether the migration was successful.
	 *
	 * @var bool|null
	 */
	private ?bool $success = null;

	/**
	 * The migration status.
	 *
	 * @var string|null
	 */
	private ?string $status = null;

	/**
	 * The migration messages.
	 *
	 * @var array|null
	 */
	private ?array $messages = null;

	/**
	 * The current processing version.
	 *
	 * @var string|null
	 */
	private ?string $current_version = null;

	/**
	 * The previous processing version.
	 *
	 * @var string|null
	 */
	private ?string $previous_version = null;

	/**
	 * The raw response data.
	 *
	 * @var stdClass
	 */
	private stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param stdClass $data The response data.
	 */
	public function __construct( stdClass $data ) {
		$this->raw_data         = $data;
		$this->success          = $data->success ?? null;
		$this->status           = $data->status ?? null;
		$this->messages         = $data->messages ?? null;
		$this->current_version  = $data->currentVersion ?? null; // phpcs:ignore
		$this->previous_version = $data->previousVersion ?? null; // phpcs:ignore
	}

	/**
	 * Get whether the migration was successful.
	 *
	 * @return bool|null Whether the migration was successful.
	 */
	public function is_success(): ?bool {
		return $this->success;
	}

	/**
	 * Get the migration status.
	 *
	 * @return string|null The migration status.
	 */
	public function get_status(): ?string {
		return $this->status;
	}

	/**
	 * Get the migration messages.
	 *
	 * @return array|null The migration messages.
	 */
	public function get_messages(): ?array {
		return $this->messages;
	}

	/**
	 * Get the current processing version.
	 *
	 * @return string|null The current processing version.
	 */
	public function get_current_version(): ?string {
		return $this->current_version;
	}

	/**
	 * Get the previous processing version.
	 *
	 * @return string|null The previous processing version.
	 */
	public function get_previous_version(): ?string {
		return $this->previous_version;
	}

	/**
	 * Get the raw response data.
	 *
	 * @return stdClass The raw response data.
	 */
	public function get_raw_data(): stdClass {
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
