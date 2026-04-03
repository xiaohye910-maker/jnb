<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for QuickLink repository.
 *
 * Defines the contract for the repository that handles all communication
 * with the QPilot API for QuickLink operations.
 *
 * @package Autoship\Services\QuickLinks\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Interfaces;

/**
 * Repository interface for QuickLink data operations.
 *
 * Handles communication with QPilot API for QuickLink operations.
 * Abstracts the data access layer from business logic.
 */
interface QuickLinkRepositoryInterface {

	/**
	 * Call QPilot verify endpoint.
	 *
	 * Verifies the QuickLink is valid, checks login requirements,
	 * and returns the action type to execute.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID.
	 * @param string|null $token              Optional security token.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return array|null Response data or null on failure.
	 */
	public function verify(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		?string $ip_address = null,
		?string $user_agent = null
	): ?array;

	/**
	 * Call QPilot consume endpoint.
	 *
	 * Records that the QuickLink was used, whether successful or not.
	 * Used for analytics and usage limit enforcement.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID.
	 * @param string|null $token              Optional security token.
	 * @param bool        $success            Whether action succeeded.
	 * @param string|null $error_code         Error code if failed.
	 * @param string|null $error_message      Error message if failed.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return array|null Response data or null on failure.
	 */
	public function consume(
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
	): ?array;

	/**
	 * Change scheduled order status.
	 *
	 * Calls the QPilot Status endpoint to change the order status
	 * to Active or Paused.
	 *
	 * @param int    $site_id            The QPilot site ID.
	 * @param int    $scheduled_order_id The scheduled order ID.
	 * @param string $status             The new status ('Active' or 'Paused').
	 *
	 * @return bool Whether status was changed successfully.
	 */
	public function change_status( int $site_id, int $scheduled_order_id, string $status ): bool;

	/**
	 * Retry scheduled order (process now).
	 *
	 * Calls the QPilot Retry endpoint to process the order immediately.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return bool Whether retry was successful.
	 */
	public function retry_order( int $site_id, int $scheduled_order_id ): bool;

	/**
	 * Safe activate scheduled order.
	 *
	 * Calls the QPilot SafeActivate endpoint to reactivate
	 * a deleted or inactive order.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return bool Whether activation was successful.
	 */
	public function safe_activate( int $site_id, int $scheduled_order_id ): bool;
}
