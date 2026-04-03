<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The mediator interface.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Mediators;

use Autoship\Common\Results\Result;

/**
 * Principal interface for the mediator.
 */
interface MediatorInterface {

	/**
	 * Sends a request and gets a result.
	 *
	 * @param RequestInterface $request The request to send.
	 *
	 * @return Result
	 */
	public function send( RequestInterface $request ): Result;

	/**
	 * Publish an event.
	 *
	 * @param EventInterface $event The event to publish.
	 *
	 * @return void
	 */
	public function publish( EventInterface $event ): void;
}