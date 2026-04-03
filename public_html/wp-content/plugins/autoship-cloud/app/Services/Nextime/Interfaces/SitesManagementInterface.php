<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Sites Management Interface.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Interfaces;

use Autoship\Services\Nextime\Access\SiteSettingsResponse;

/**
 * Interface for authentication with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface SitesManagementInterface {
	/**
	 * Gets the site settings from Nextime.
	 *
	 * @return SiteSettingsResponse The Nextime site settings.
	 */
	public function get_site_settings(): SiteSettingsResponse;
}
