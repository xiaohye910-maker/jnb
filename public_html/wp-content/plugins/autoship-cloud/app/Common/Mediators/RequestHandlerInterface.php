<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The request handler interface.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Mediators;

use Autoship\Common\Results\Result;

/**
 * Handles a requests and returns a result.
 *
 * @template TRequest of RequestInterface
 */
interface RequestHandlerInterface {

	/**
	 * Process an IRequest.
	 *
	 * @param RequestInterface $request Request to process.
	 *
	 * @return Result
	 */
	public function handle( RequestInterface $request ): Result;
}