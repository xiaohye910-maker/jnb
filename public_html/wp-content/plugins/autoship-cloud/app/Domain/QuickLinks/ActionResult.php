<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Value object for QuickLink action execution results.
 *
 * Encapsulates the result of executing a QuickLink action, including
 * success/failure status, error information, and optional metadata.
 *
 * @package Autoship\Domain\QuickLinks
 * @since   2.11.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * Represents the result of executing a QuickLink action.
 *
 * Encapsulates success/failure status, error information,
 * and optional metadata about the action execution.
 */
class ActionResult {

	/**
	 * Whether the action was successful.
	 *
	 * @var bool
	 */
	private bool $success;

	/**
	 * Error code if the action failed.
	 *
	 * @var string|null
	 */
	private ?string $error_code;

	/**
	 * Error message if action failed.
	 *
	 * @var string|null
	 */
	private ?string $error_message;

	/**
	 * Additional metadata about the action.
	 *
	 * @var array
	 */
	private array $metadata;

	/**
	 * Constructor.
	 *
	 * @param bool        $success       Whether the action succeeded.
	 * @param string|null $error_code    Error code if failed.
	 * @param string|null $error_message Error message if failed.
	 * @param array       $metadata      Additional metadata.
	 */
	private function __construct(
		bool $success,
		?string $error_code = null,
		?string $error_message = null,
		array $metadata = array()
	) {
		$this->success       = $success;
		$this->error_code    = $error_code;
		$this->error_message = $error_message;
		$this->metadata      = $metadata;
	}

	/**
	 * Create a successful result.
	 *
	 * @param array $metadata Optional metadata about the successful action.
	 *
	 * @return self
	 */
	public static function success( array $metadata = array() ): self {
		return new self( true, null, null, $metadata );
	}

	/**
	 * Create a failed result.
	 *
	 * @param string $error_code    Error code identifying the failure type.
	 * @param string $error_message Human-readable error message.
	 * @param array  $metadata      Optional metadata about the failure.
	 *
	 * @return self
	 */
	public static function failure(
		string $error_code,
		string $error_message,
		array $metadata = array()
	): self {
		return new self( false, $error_code, $error_message, $metadata );
	}

	/**
	 * Check if the action was successful.
	 *
	 * @return bool
	 */
	public function is_successful(): bool {
		return $this->success;
	}

	/**
	 * Get error code.
	 *
	 * @return string|null
	 */
	public function get_error_code(): ?string {
		return $this->error_code;
	}

	/**
	 * Get error message.
	 *
	 * @return string|null
	 */
	public function get_error_message(): ?string {
		return $this->error_message;
	}

	/**
	 * Get all metadata.
	 *
	 * @return array
	 */
	public function get_metadata(): array {
		return $this->metadata;
	}

	/**
	 * Get specific metadata value.
	 *
	 * @param string $key           The metadata key.
	 * @param mixed  $default_value Default value if key not found.
	 *
	 * @return mixed
	 */
	public function get_metadata_value( string $key, $default_value = null ) {
		return $this->metadata[ $key ] ?? $default_value;
	}
}
