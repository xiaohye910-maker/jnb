<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The handler resolver interface.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Mediators;

/**
 * Handler to resolve request and event handlers.
 */
interface HandlerResolverInterface {

	/**
	 * Resolve the request handler.
	 *
	 * @param string $request_class The fully qualified class name of the request.
	 *
	 * @return RequestHandlerInterface
	 */
	public function resolve_request_handler( string $request_class ): RequestHandlerInterface;

	/**
	 * Resolve the event handlers.
	 *
	 * @param string $event_class The fully qualified class name of the event.
	 *
	 * @return EventHandlerInterface[] The event handlers.
	 */
	public function resolve_event_handlers( string $event_class ): array;
}