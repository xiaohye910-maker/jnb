<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The error reason object.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Results;

/**
 * ErrorReason.
 */
class ErrorReason implements ReasonInterface {

	/**
	 * The error message.
	 *
	 * @var string
	 */
	private string $message;

	/**
	 * The error code (optional).
	 *
	 * @var string|null
	 */
	private ?string $code;

	/**
	 * Contains the metadata of the error.
	 *
	 * @var array<string, mixed>
	 */
	private array $metadata;

	/**
	 * The constructor.
	 *
	 * @param string               $message The error message.
	 * @param string|null          $code The error code (optional).
	 * @param array<string, mixed> $metadata The metadata of the error (optional).
	 */
	public function __construct( string $message, ?string $code = null, array $metadata = array() ) {
		$this->message  = $message;
		$this->code     = $code;
		$this->metadata = $metadata;
	}

	/**
	 * Gets the message of the error.
	 *
	 * @return string
	 */
	public function get_message(): string {
		return $this->message;
	}

	/**
	 * Gets the error code if it exists.
	 *
	 * @return string|null
	 */
	public function get_code(): ?string {
		return $this->code;
	}

	/**
	 * Gets the metadata of the error.
	 *
	 * @return array
	 */
	public function get_metadata(): array {
		return $this->metadata;
	}
}