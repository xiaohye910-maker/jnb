<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Http Exception
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime;

use Exception;

/**
 * Represents an HTTP error from the Nextime API.
 */
class NextimeHttpException extends Exception {
}
