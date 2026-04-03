<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for QuickLink service.
 *
 * Defines the contract for the main QuickLink service that orchestrates
 * the verify → execute → consume flow.
 *
 * @package Autoship\Services\QuickLinks\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Interfaces;

use Autoship\Domain\QuickLinks\QuickLinkVerification;
use Autoship\Domain\QuickLinks\ActionResult;

/**
 * Service interface for QuickLink operations.
 *
 * Orchestrates the verify → execute → consume flow for QuickLinks.
 */
interface QuickLinkServiceInterface {

	/**
	 * Verify a QuickLink.
	 *
	 * Calls the QPilot API to verify the QuickLink is valid,
	 * check login requirements, and get the action type.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID (null for anonymous).
	 * @param string|null $token              Optional security token.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return QuickLinkVerification The verification result.
	 */
	public function verify_quicklink(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		?string $ip_address = null,
		?string $user_agent = null
	): QuickLinkVerification;

	/**
	 * Execute a QuickLink action.
	 *
	 * Creates the appropriate action strategy and executes it.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $action_type        The action type to execute.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return ActionResult The action execution result.
	 */
	public function execute_action(
		int $site_id,
		int $action_type,
		int $scheduled_order_id
	): ActionResult;

	/**
	 * Record QuickLink consumption.
	 *
	 * Calls the QPilot API to record that the QuickLink was used,
	 * whether successful or not.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID (null for anonymous).
	 * @param string|null $token              Optional security token.
	 * @param bool        $success            Whether action succeeded.
	 * @param string|null $error_code         Error code if failed.
	 * @param string|null $error_message      Error message if failed.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return bool Whether consumption was recorded successfully.
	 */
	public function consume_quicklink(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		bool $success = true,
		?string $error_code = null,
		?string $error_message = null,
		?string $ip_address = null,
		?string $user_agent = null
	): bool;
}
