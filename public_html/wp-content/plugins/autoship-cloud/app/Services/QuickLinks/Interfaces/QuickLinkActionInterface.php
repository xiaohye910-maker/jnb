<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for QuickLink actions.
 *
 * Defines the contract that all QuickLink action strategies must implement.
 * Each action type (Resume, Pause, ProcessNow, Reactivate) implements this
 * interface to provide its specific execution logic.
 *
 * @package Autoship\Services\QuickLinks\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Interfaces;

use Autoship\Domain\QuickLinks\ActionResult;

/**
 * Interface for QuickLink action strategies.
 *
 * Each action type (Resume, Pause, ProcessNow, Reactivate)
 * implements this interface to handle its specific logic.
 */
interface QuickLinkActionInterface {

	/**
	 * Execute the QuickLink action.
	 *
	 * Performs the specific action (resume, pause, etc.) by calling
	 * the appropriate QPilot API endpoint through the repository.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return ActionResult The result of the action execution.
	 */
	public function execute( int $site_id, int $scheduled_order_id ): ActionResult;

	/**
	 * Get the action type ID.
	 *
	 * @return int The action type constant from ActionType enum.
	 */
	public function get_action_type(): int;

	/**
	 * Get human-readable action name.
	 *
	 * @return string The action name (e.g., 'Resume', 'Pause').
	 */
	public function get_action_name(): string;
}
