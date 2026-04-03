<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Site Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

use Autoship\Services\QPilot\Sites\CreateSiteRequest;
use Autoship\Services\QPilot\Sites\UpdateSiteMetadataRequest;
use Autoship\Services\QPilot\Sites\UpdateSiteUrlsRequest;
use Autoship\Services\QPilot\Sites\SiteResponse;
use Autoship\Services\QPilot\Sites\SiteSettingsResponse;

/**
 * Interface for site management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface SiteManagementInterface {
	/**
	 * Get the default site.
	 *
	 * @return SiteResponse The site object.
	 */
	public function get_default_site(): SiteResponse;

	/**
	 * Get a site by ID.
	 *
	 * @param int $site_id The site ID.
	 * @return SiteResponse The site object.
	 */
	public function get_site( int $site_id ): SiteResponse;

	/**
	 * Create a site.
	 *
	 * @param CreateSiteRequest $request The site creation request.
	 * @return SiteResponse The created site object.
	 */
	public function create_site( CreateSiteRequest $request ): SiteResponse;

	/**
	 * Update site metadata.
	 *
	 * @param UpdateSiteMetadataRequest $request The metadata update request.
	 * @return SiteResponse The updated site object.
	 */
	public function update_site_metadata( UpdateSiteMetadataRequest $request ): SiteResponse;

	/**
	 * Get site settings.
	 *
	 * @return SiteSettingsResponse The site settings.
	 */
	public function get_settings(): SiteSettingsResponse;

	/**
	 * Update My Account relative URLs.
	 *
	 * @param UpdateSiteUrlsRequest $request The URLs update request.
	 * @return SiteSettingsResponse The updated settings.
	 */
	public function update_my_account_relative_urls( UpdateSiteUrlsRequest $request ): SiteSettingsResponse;
}
