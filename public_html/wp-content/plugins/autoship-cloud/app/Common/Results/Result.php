<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The result object for the mediator.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Results;

/**
 * The result object.
 *
 * @template TValue
 */
class Result {

	/**
	 * The success status.
	 *
	 * @var bool
	 */
	private bool $success;

	/**
	 * The errors of the result.
	 *
	 * @var ErrorReason[]
	 */
	private array $errors = array();

	/**
	 * The reasons of the result.
	 *
	 * @var ReasonInterface[]
	 */
	private array $reasons = array();

	/**
	 * The value of the result.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * The constructor.
	 *
	 * @param bool $success The success status.
	 */
	private function __construct( bool $success ) {
		$this->success = $success;
	}

	/**
	 * Creates a successful result.
	 *
	 * @param mixed $value The value of the result (optional).
	 *
	 * @return self
	 */
	public static function ok( $value = null ): self {
		$self        = new self( true );
		$self->value = $value;
		return $self;
	}

	/**
	 * Creates a failed result.
	 *
	 * @param string               $message The error message.
	 * @param string|null          $code The error code (optional).
	 * @param array<string, mixed> $meta The metadata of the error (optional).
	 * @return self
	 */
	public static function fail( string $message, ?string $code = null, array $meta = array() ): self {
		$self           = new self( false );
		$self->errors[] = new ErrorReason( $message, $code, $meta );
		return $self;
	}

	/**
	 * Check if the result is successful.
	 *
	 * @return bool
	 */
	public function is_success(): bool {
		return $this->success;
	}

	/**
	 * Check if the result is failed.
	 *
	 * @return bool
	 */
	public function is_failed(): bool {
		return ! $this->success;
	}

	/**
	 * Gets the value of the result.
	 *
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Get the errors of the result.
	 *
	 * @return ErrorReason[]
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Get the reasons of the result.
	 *
	 * @return ReasonInterface[]
	 */
	public function get_reasons(): array {
		return $this->reasons;
	}

	/**
	 * Add an error to the result.
	 *
	 * @param ErrorReason $error The reason to add.
	 *
	 * @return $this
	 */
	public function with_error( ErrorReason $error ): self {
		$this->errors[] = $error;
		$this->success  = false;
		return $this;
	}

	/**
	 * Add a reason to the result.
	 *
	 * @param ReasonInterface $reason The reason to add.
	 *
	 * @return $this
	 */
	public function with_reason( ReasonInterface $reason ): self {
		$this->reasons[] = $reason;
		return $this;
	}

	/**
	 * Set the value of the result.
	 *
	 * @param mixed $value The value to set.
	 *
	 * @return self
	 */
	public function with_value( $value ): self {
		$this->value = $value;
		return $this;
	}

	/**
	 * Merge another result with the current one.
	 *
	 * @param Result $other The result to merge.
	 * @return self
	 */
	public function merge( Result $other ): self {
		if ( $other->is_failed() ) {
			foreach ( $other->get_errors() as $e ) {
				$this->with_error( $e );
			}
		}

		foreach ( $other->get_reasons() as $r ) {
			$this->with_reason( $r );
		}

		if ( null === $this->value && null !== $other->get_value() ) {
			$this->value = $other->get_value();
		}

		return $this;
	}
}