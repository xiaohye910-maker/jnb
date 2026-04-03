<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This represents the base interface for all step handlers.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

/**
 * Represents the base interface for all step handlers.
 *
 * @package Autoship
 * @since 2.8.7
 */
interface StepHandlerInterface {
	/**
	 * Handles a quick launch request.
	 *
	 * @return mixed
	 **/
	public function handle();
}
