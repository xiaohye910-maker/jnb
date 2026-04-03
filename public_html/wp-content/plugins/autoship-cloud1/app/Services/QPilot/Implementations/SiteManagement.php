<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\SiteManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Sites\CreateSiteRequest;
use Autoship\Services\QPilot\Sites\UpdateSiteMetadataRequest;
use Autoship\Services\QPilot\Sites\UpdateSiteUrlsRequest;
use Autoship\Services\QPilot\Sites\SiteResponse;
use Autoship\Services\QPilot\Sites\SiteSettingsResponse;

/**
 * Implementation of the SiteManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class SiteManagement implements SiteManagementInterface {
	/**
	 * The API client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $api_client;

	/**
	 * Constructor.
	 *
	 * @param QPilotHttpClient $api_client The API client.
	 */
	public function __construct( QPilotHttpClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Get the default site.
	 *
	 * @return SiteResponse The site object.
	 */
	public function get_default_site(): SiteResponse {
		$endpoint = 'Sites/Default';
		$response = $this->api_client->get( $endpoint );

		return new SiteResponse( $response );
	}

	/**
	 * Get a site by ID.
	 *
	 * @param int $site_id The site ID.
	 * @return SiteResponse The site object.
	 */
	public function get_site( int $site_id ): SiteResponse {
		$endpoint = "Sites/{$site_id}";
		$response = $this->api_client->get( $endpoint );

		return new SiteResponse( $response );
	}

	/**
	 * Create a site.
	 *
	 * @param CreateSiteRequest $request The site creation request.
	 * @return SiteResponse The created site object.
	 */
	public function create_site( CreateSiteRequest $request ): SiteResponse {
		$endpoint = 'Sites';
		$data     = $request->to_array();
		$response = $this->api_client->post( $endpoint, $data );

		return new SiteResponse( $response );
	}

	/**
	 * Update site metadata.
	 *
	 * @param UpdateSiteMetadataRequest $request The metadata update request.
	 * @return SiteResponse The updated site object.
	 */
	public function update_site_metadata( UpdateSiteMetadataRequest $request ): SiteResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Metadata";

		$data     = $request->to_array();
		$response = $this->api_client->put( $endpoint, $data );

		return new SiteResponse( $response );
	}

	/**
	 * Get site settings.
	 *
	 * @return SiteSettingsResponse The site settings.
	 */
	public function get_settings(): SiteSettingsResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Settings";

		$response = $this->api_client->get( $endpoint );

		return new SiteSettingsResponse( $response );
	}

	/**
	 * Update My Account relative URLs.
	 *
	 * @param UpdateSiteUrlsRequest $request The URLs update request.
	 * @return SiteSettingsResponse The updated settings.
	 */
	public function update_my_account_relative_urls( UpdateSiteUrlsRequest $request ): SiteSettingsResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Settings/MyAccountUrls";

		$data     = $request->to_array();
		$response = $this->api_client->put( $endpoint, $data );

		return new SiteSettingsResponse( $response );
	}
}
