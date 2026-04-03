<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The event handler interface.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Mediators;

/**
 * Event handler interface.
 */
interface EventHandlerInterface {
	/**
	 * Handles an event.
	 *
	 * @param EventInterface $event The event to handle.
	 *
	 * @return void
	 */
	public function handle( EventInterface $event ): void;
}