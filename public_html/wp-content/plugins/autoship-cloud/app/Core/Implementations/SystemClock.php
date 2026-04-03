<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * System clock implementation.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\ClockInterface;

/**
 * System clock implementation.
 *
 * @package Autoship
 * @since 2.11.0
 */
class SystemClock implements ClockInterface {

	/**
	 * Returns the current date/time formatted as a string.
	 *
	 * @param string $format The date format string.
	 *
	 * @return string
	 */
	public function now( string $format ): string {
		return gmdate( $format );
	}

	/**
	 * Returns the current Unix timestamp.
	 *
	 * @return int
	 */
	public function timestamp(): int {
		return time();
	}
}
