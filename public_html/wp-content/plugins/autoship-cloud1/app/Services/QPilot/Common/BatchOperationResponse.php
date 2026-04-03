<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CustomerResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Common;

use stdClass;

/**
 * Batch operation response class.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class BatchOperationResponse {
	/**
	 * The number of successful operations.
	 *
	 * @var int
	 */
	private int $success_count;

	/**
	 * The number of failed operations.
	 *
	 * @var int
	 */
	private int $failure_count;

	/**
	 * Array of error messages for failed operations.
	 *
	 * @var array<string>
	 */
	private array $errors;

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
		$this->raw_data      = $data;
		$this->success_count = $data->successCount ?? 0; // phpcs:ignore
		$this->failure_count = $data->failureCount ?? 0; // phpcs:ignore
		$this->errors        = $data->errors ?? array();
	}

	/**
	 * Get the success count.
	 *
	 * @return int The number of successful operations.
	 */
	public function get_success_count(): int {
		return $this->success_count;
	}

	/**
	 * Get the failure count.
	 *
	 * @return int The number of failed operations.
	 */
	public function get_failure_count(): int {
		return $this->failure_count;
	}

	/**
	 * Get the errors.
	 *
	 * @return array<string> Array of error messages for failed operations.
	 */
	public function get_errors(): array {
		return $this->errors;
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
	 * Check if the batch operation was completely successful.
	 *
	 * @return bool True if all operations succeeded.
	 */
	public function is_success(): bool {
		return 0 === $this->failure_count;
	}
}
