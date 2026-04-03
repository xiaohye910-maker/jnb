<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for time abstraction.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core;

/**
 * Interface for time abstraction.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface ClockInterface {

	/**
	 * Returns the current date/time formatted as a string.
	 *
	 * @param string $format The date format string.
	 *
	 * @return string
	 */
	public function now( string $format ): string;

	/**
	 * Returns the current Unix timestamp.
	 *
	 * @return int
	 */
	public function timestamp(): int;
}
