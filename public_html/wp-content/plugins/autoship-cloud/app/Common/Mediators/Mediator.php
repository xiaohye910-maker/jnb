<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The mediator implementation.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Common\Mediators;

use Autoship\Common\Results\Result;
use Autoship\Services\Logging\LoggerInterface;
use Throwable;
use function get_class;

/**
 * Minimal implementation of Mediator (sequential, no behaviors).
 */
class Mediator implements MediatorInterface {

	/**
	 * Contains the handler resolver.
	 *
	 * @var HandlerResolverInterface
	 */
	private HandlerResolverInterface $resolver;

	/**
	 * Contains the logger interface.
	 *
	 * @var LoggerInterface|null
	 */
	private ?LoggerInterface $logger;

	/**
	 * Constructor of the mediator.
	 *
	 * @param HandlerResolverInterface $resolver The handler resolver.
	 * @param LoggerInterface|null     $logger The logger interface.
	 */
	public function __construct( HandlerResolverInterface $resolver, ?LoggerInterface $logger = null ) {
		$this->resolver = $resolver;
		$this->logger   = $logger;
	}

	/**
	 * Sends a request and gets a result.
	 *
	 * @param RequestInterface $request The request to send.
	 *
	 * @return Result
	 */
	public function send( RequestInterface $request ): Result {
		$request_class = get_class( $request );

		if ( $this->logger && $this->logger->is_enabled() ) {
			$this->logger->debug( 'mediator', 'send: ' . $request_class );
		}

		$handler = $this->resolver->resolve_request_handler( $request_class );

		try {
			$result = $handler->handle( $request );

			if ( $this->logger && $this->logger->is_enabled() ) {
				$this->logger->info(
					'mediator',
					'send completed: ' . $request_class . ' success=' . ( $result->is_success() ? 'true' : 'false' )
				);
			}

			return $result;
		} catch ( Throwable $ex ) {
			if ( $this->logger && $this->logger->is_enabled() ) {
				$this->logger->error(
					'mediator',
					"exception in send: $request_class => {$ex->getMessage()}"
				);
			}

			// Converts an uncaught exception into a Result::fail.
			return Result::fail(
				'Unhandled exception',
				'UNHANDLED_EXCEPTION',
				array(
					'exception' => get_class( $ex ),
					'message'   => $ex->getMessage(),
				)
			);
		}
	}

	/**
	 * Publish an event.
	 *
	 * @param EventInterface $event The event to publish.
	 *
	 * @return void
	 *
	 * @throws Throwable When an error occurs while publishing the event.
	 */
	public function publish( EventInterface $event ): void {
		$event_class = get_class( $event );

		if ( $this->logger && $this->logger->is_enabled() ) {
			$this->logger->debug( 'mediator', 'publish: ' . $event_class );
		}

		$handlers = $this->resolver->resolve_event_handlers( $event_class );

		foreach ( $handlers as $handler ) {
			try {
				$handler->handle( $event );
			} catch ( Throwable $ex ) {
				if ( $this->logger && $this->logger->is_enabled() ) {
					$this->logger->error(
						'mediator',
						'exception in publish: ' . $event_class . ' => ' . $ex->getMessage()
					);
				}

				// Fail-fast: re-throw the exception to not hide event errors.
				throw $ex;
			}
		}

		if ( $this->logger && $this->logger->is_enabled() ) {
			$this->logger->info( 'mediator', 'publish completed: ' . $event_class );
		}
	}
}