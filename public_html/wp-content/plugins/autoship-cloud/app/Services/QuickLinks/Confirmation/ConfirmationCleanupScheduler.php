<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Confirmation Cleanup Scheduler.
 *
 * Schedules and handles cleanup of old confirmation records using Action Scheduler.
 *
 * @package Autoship\Services\QuickLinks\Confirmation
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation;

use Autoship\Services\Logging\LoggerInterface;

/**
 * Confirmation Cleanup Scheduler.
 *
 * Uses WooCommerce Action Scheduler to periodically clean up
 * expired confirmation records older than the retention period.
 */
class ConfirmationCleanupScheduler {

	/**
	 * Action hook name for the cleanup task.
	 */
	const TASK_HOOK = 'autoship_quicklink_confirmation_cleanup';

	/**
	 * Default retention period in days.
	 */
	const DEFAULT_RETENTION_DAYS = 90;

	/**
	 * Default interval between cleanups (1 day in seconds).
	 */
	const DEFAULT_INTERVAL = 86400;

	/**
	 * Option key for last cleanup timestamp.
	 */
	const LAST_CLEANUP_OPTION = 'autoship_quicklink_confirmation_last_cleanup';

	/**
	 * The confirmation service instance.
	 *
	 * @var QuickLinkConfirmationService
	 */
	private $confirmation_service;

	/**
	 * The logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkConfirmationService $confirmation_service The confirmation service.
	 * @param LoggerInterface              $logger               The logger instance.
	 */
	public function __construct( QuickLinkConfirmationService $confirmation_service, LoggerInterface $logger ) {
		$this->confirmation_service = $confirmation_service;
		$this->logger               = $logger;
	}

	/**
	 * Initialize the scheduler.
	 *
	 * Registers the cleanup action hook.
	 *
	 * @return void
	 */
	public function initialize(): void {
		add_action( self::TASK_HOOK, array( $this, 'run_cleanup' ) );
	}

	/**
	 * Schedule the cleanup task.
	 *
	 * @return bool True if scheduled successfully.
	 */
	public function schedule_task(): bool {
		// Check if Action Scheduler is available.
		if ( ! function_exists( 'as_next_scheduled_action' ) || ! function_exists( 'as_schedule_recurring_action' ) ) {
			$this->log( 'Unable to schedule confirmation cleanup. Action Scheduler not available.' );
			return false;
		}

		// Check if already scheduled using as_next_scheduled_action (more reliable than as_has_scheduled_action).
		$next_scheduled = as_next_scheduled_action( self::TASK_HOOK );
		if ( false !== $next_scheduled ) {
			return true;
		}

		// Schedule daily cleanup.
		$interval = $this->get_interval();
		as_schedule_recurring_action( time() + $interval, $interval, self::TASK_HOOK );

		$this->log( sprintf( 'Scheduled confirmation cleanup task with interval: %d seconds.', $interval ) );

		return true;
	}

	/**
	 * Unschedule the cleanup task.
	 *
	 * @return bool True if unscheduled successfully.
	 */
	public function unschedule_task(): bool {
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			$this->log( 'Unable to unschedule confirmation cleanup. Action Scheduler not available.' );
			return false;
		}

		as_unschedule_all_actions( self::TASK_HOOK );

		$this->log( 'Unscheduled confirmation cleanup task.' );

		return true;
	}

	/**
	 * Run the cleanup process.
	 *
	 * Called by the Action Scheduler.
	 *
	 * @return int Number of records deleted.
	 */
	public function run_cleanup(): int {
		$retention_days = $this->get_retention_days();
		$deleted        = $this->confirmation_service->cleanup( $retention_days );

		// Update last cleanup timestamp.
		update_option( self::LAST_CLEANUP_OPTION, time() );

		$this->log(
			sprintf(
				'Confirmation cleanup completed. Deleted %d records older than %d days.',
				$deleted,
				$retention_days
			)
		);

		/**
		 * Fires after confirmation cleanup runs.
		 *
		 * @param int $deleted         Number of records deleted.
		 * @param int $retention_days  Retention period in days.
		 */
		do_action( 'autoship_quicklink_confirmation_cleanup_complete', $deleted, $retention_days );

		return $deleted;
	}

	/**
	 * Get the cleanup interval in seconds.
	 *
	 * @return int Interval in seconds.
	 */
	public function get_interval(): int {
		/**
		 * Filter the confirmation cleanup interval.
		 *
		 * @param int $interval Interval in seconds. Default DAY_IN_SECONDS.
		 */
		return (int) apply_filters( 'autoship_quicklink_confirmation_cleanup_interval', self::DEFAULT_INTERVAL );
	}

	/**
	 * Get the retention period in days.
	 *
	 * @return int Retention period in days.
	 */
	public function get_retention_days(): int {
		/**
		 * Filter the confirmation cleanup retention period.
		 *
		 * @param int $days Number of days to retain records. Default 90.
		 */
		return (int) apply_filters( 'autoship_quicklink_confirmation_retention_days', self::DEFAULT_RETENTION_DAYS );
	}

	/**
	 * Check if the cleanup task is scheduled.
	 *
	 * @return bool True if scheduled.
	 */
	public function is_scheduled(): bool {
		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return false;
		}

		return false !== as_next_scheduled_action( self::TASK_HOOK );
	}

	/**
	 * Get the timestamp of the last cleanup.
	 *
	 * @return int|null Timestamp or null if never run.
	 */
	public function get_last_cleanup_time() {
		$timestamp = get_option( self::LAST_CLEANUP_OPTION, null );
		return null !== $timestamp ? (int) $timestamp : null;
	}

	/**
	 * Get the next scheduled cleanup time.
	 *
	 * @return int|null Timestamp or null if not scheduled.
	 */
	public function get_next_cleanup_time() {
		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return null;
		}

		$next = as_next_scheduled_action( self::TASK_HOOK );
		return false !== $next ? (int) $next : null;
	}

	/**
	 * Log a message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private function log( string $message ): void {
		$this->logger->log( __( 'Autoship QuickLinks Confirmation Cleanup', 'autoship' ), $message );
	}
}
