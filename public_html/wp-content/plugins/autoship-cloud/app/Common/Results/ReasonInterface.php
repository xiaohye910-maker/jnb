<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The error reason interface.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Results;

/**
 * Represents the reason interface.
 */
interface ReasonInterface {

	/**
	 * Gets the message of the error.
	 *
	 * @return string
	 */
	public function get_message(): string;

	/**
	 * Gets the error code if it exists.
	 *
	 * @return string|null
	 */
	public function get_code(): ?string;

	/**
	 * Gets the metadata of the error.
	 *
	 * @return array
	 */
	public function get_metadata(): array;
}