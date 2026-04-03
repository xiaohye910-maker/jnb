<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\IntegrationManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Integrations\IntegrationStatusResponse;
use Autoship\Services\QPilot\Integrations\IntegrationCheckResponse;
use Autoship\Services\QPilot\Integrations\SiteIntegrationsResponse;
use Autoship\Services\QPilot\Integrations\MigrationStatusResponse;
use Exception;

/**
 * Implementation of the IntegrationManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class IntegrationManagement implements IntegrationManagementInterface {
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
	 * Get integration status.
	 *
	 * @return IntegrationStatusResponse The integration status response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_integration_status(): IntegrationStatusResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Integrations/Status";

		$response = $this->api_client->get( $endpoint );

		return new IntegrationStatusResponse( $response );
	}

	/**
	 * Check integration.
	 *
	 * @return IntegrationCheckResponse The integration check response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function check_integration(): IntegrationCheckResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Integrations/Check";

		$response = $this->api_client->get( $endpoint );

		return new IntegrationCheckResponse( $response );
	}

	/**
	 * Get site integrations.
	 *
	 * @return SiteIntegrationsResponse The site integrations response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_site_integrations(): SiteIntegrationsResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Integrations";

		$response = $this->api_client->get( $endpoint );

		return new SiteIntegrationsResponse( $response );
	}

	/**
	 * Migrate processing version.
	 *
	 * @return MigrationStatusResponse The migration status response.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function migrate_processing_version(): MigrationStatusResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Integrations/MigrateProcessingVersion";

		$response = $this->api_client->post( $endpoint, array() );

		return new MigrationStatusResponse( $response );
	}
}
