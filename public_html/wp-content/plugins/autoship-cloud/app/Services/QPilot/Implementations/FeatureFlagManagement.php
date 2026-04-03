<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Feature Flag Management implementation for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 2.11.0
 */

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\FeatureFlagManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;

/**
 * Implementation of the FeatureFlagManagementInterface.
 *
 * Communicates with QPilot's feature flag endpoints to retrieve
 * globally and per-site enabled feature flags.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 2.11.0
 */
class FeatureFlagManagement implements FeatureFlagManagementInterface {

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
	 * Get all globally enabled feature flags.
	 *
	 * @return array<string> List of enabled feature flag names.
	 */
	public function get_feature_flags(): array {
		$response = $this->api_client->get( '/api/FeatureFlags' );

		return $this->extract_flags( $response );
	}

	/**
	 * Get feature flags enabled for the current site.
	 *
	 * @return array<string> List of enabled feature flag names.
	 */
	public function get_site_feature_flags(): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "/api/Sites/{$site_id}/FeatureFlags";
		$response = $this->api_client->get( $endpoint );

		return $this->extract_flags( $response );
	}

	/**
	 * Extract flag names from a QPilot API response.
	 *
	 * Handles both envelope responses (with a data property) and
	 * flat array responses.
	 *
	 * @param mixed $response The raw API response.
	 *
	 * @return array<string> List of feature flag names.
	 */
	private function extract_flags( $response ): array {
		// Handle envelope response: { data: [...] }.
		if ( is_object( $response ) && isset( $response->data ) && is_array( $response->data ) ) {
			return $response->data;
		}

		// Handle flat array response.
		if ( is_array( $response ) ) {
			return $response;
		}

		return array();
	}
}
