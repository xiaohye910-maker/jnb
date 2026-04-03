<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Pause action implementation.
 *
 * Changes scheduled order status to Paused by calling the QPilot
 * Status endpoint.
 *
 * @package Autoship\Services\QuickLinks\Actions
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Actions;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkActionInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\Logging\LoggerInterface;
use Autoship\Domain\QuickLinks\ActionResult;
use Autoship\Domain\QuickLinks\ActionType;

/**
 * Pause action strategy.
 *
 * Changes scheduled order status to Paused.
 */
class PauseAction implements QuickLinkActionInterface {

	/**
	 * QuickLink repository.
	 *
	 * @var QuickLinkRepositoryInterface
	 */
	private QuickLinkRepositoryInterface $repository;

	/**
	 * Logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkRepositoryInterface $repository The repository instance.
	 * @param LoggerInterface              $logger     The logger instance.
	 */
	public function __construct( QuickLinkRepositoryInterface $repository, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->logger     = $logger;
	}

	/**
	 * Execute pause action.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return ActionResult
	 */
	public function execute( int $site_id, int $scheduled_order_id ): ActionResult {
		$this->log(
			sprintf(
				'Pause: Starting execution for order %d on site %d',
				$scheduled_order_id,
				$site_id
			)
		);

		try {
			$success = $this->repository->change_status( $site_id, $scheduled_order_id, 'Paused' );

			if ( $success ) {
				$this->log(
					sprintf(
						'Pause: Successfully paused order %d',
						$scheduled_order_id
					)
				);

				return ActionResult::success(
					array(
						'action'             => 'Pause',
						'scheduled_order_id' => $scheduled_order_id,
					)
				);
			}

			$this->log_error(
				sprintf(
					'Pause: Failed to pause order %d - repository returned false',
					$scheduled_order_id
				)
			);

			return ActionResult::failure(
				'PAUSE_FAILED',
				__( 'Failed to pause subscription.', 'autoship' ),
				array( 'scheduled_order_id' => $scheduled_order_id )
			);
		} catch ( \Exception $e ) {
			$this->log_error(
				sprintf(
					'Pause: Exception pausing order %d - %s: %s',
					$scheduled_order_id,
					get_class( $e ),
					$e->getMessage()
				)
			);

			return ActionResult::failure(
				'PAUSE_EXCEPTION',
				$e->getMessage(),
				array(
					'scheduled_order_id' => $scheduled_order_id,
					'exception'          => get_class( $e ),
				)
			);
		}
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private function log( string $message ): void {
		$this->logger->info( 'Autoship QuickLinks', $message );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private function log_error( string $message ): void {
		$this->logger->error( 'Autoship QuickLinks', $message );
	}

	/**
	 * Get action type.
	 *
	 * @return int
	 */
	public function get_action_type(): int {
		return ActionType::PAUSE;
	}

	/**
	 * Get action name.
	 *
	 * @return string
	 */
	public function get_action_name(): string {
		return 'Pause';
	}
}
