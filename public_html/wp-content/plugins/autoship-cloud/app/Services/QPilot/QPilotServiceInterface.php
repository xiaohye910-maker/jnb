<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QPilot Service Interface.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot;

use Autoship\Services\QPilot\Interfaces\AccessManagementInterface;
use Autoship\Services\QPilot\Interfaces\CustomerManagementInterface;
use Autoship\Services\QPilot\Interfaces\ProductManagementInterface;
use Autoship\Services\QPilot\Interfaces\OrderManagementInterface;
use Autoship\Services\QPilot\Interfaces\PaymentManagementInterface;
use Autoship\Services\QPilot\Interfaces\CouponManagementInterface;
use Autoship\Services\QPilot\Interfaces\FeatureFlagManagementInterface;
use Autoship\Services\QPilot\Interfaces\IntegrationManagementInterface;
use Autoship\Services\QPilot\Interfaces\SiteManagementInterface;

/**
 * Main interface for QPilot API service client.
 *
 * This interface extends all specialized interfaces for different aspects of the QPilot API
 * and provides common configuration methods. It serves as the main contract for
 * implementing QPilot API clients.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface QPilotServiceInterface extends
	AccessManagementInterface,
	CustomerManagementInterface,
	ProductManagementInterface,
	OrderManagementInterface,
	PaymentManagementInterface,
	CouponManagementInterface,
	IntegrationManagementInterface,
	SiteManagementInterface,
	FeatureFlagManagementInterface {
	/**
	 * Get the authentication token.
	 *
	 * @return string The authentication token.
	 */
	public function get_token_auth(): string;

	/**
	 * Set the authentication token.
	 *
	 * @param string $token_auth The authentication token.
	 * @return void
	 */
	public function set_token_auth( string $token_auth ): void;

	/**
	 * Get the user ID.
	 *
	 * @return int The user ID.
	 */
	public function get_user_id(): int;

	/**
	 * Set the user ID.
	 *
	 * @param int $user_id The user ID.
	 * @return void
	 */
	public function set_user_id( int $user_id ): void;

	/**
	 * Get the site ID.
	 *
	 * @return int The site ID.
	 */
	public function get_site_id(): int;

	/**
	 * Set the site ID.
	 *
	 * @param int $site_id The site ID.
	 * @return void
	 */
	public function set_site_id( int $site_id ): void;

	/**
	 * Set the source of the API call.
	 *
	 * @param string $source The source of the call.
	 * @return void
	 */
	public function set_source( string $source ): void;
}
