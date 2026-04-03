<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Nextime Site Management service.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Implementations;

use Autoship\Core\Implementations\Environment;
use Autoship\Services\Nextime\Access\SiteSettingsResponse;
use Autoship\Services\Nextime\Interfaces\SitesManagementInterface;
use Autoship\Services\Nextime\NextimeHttpException;


/**
 * The Nextime sites management.
 */
class NextimeSitesManagement implements SitesManagementInterface {

	/**
	 * The environment settings.
	 *
	 * @var Environment
	 */
	private Environment $environment;

	/**
	 * Constructor.
	 *
	 * @param Environment $environment The environment settings.
	 */
	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}

	/**
	 * Gets the site settings from Nextime.
	 *
	 * @return SiteSettingsResponse The Nextime site settings.
	 */
	public function get_site_settings(): SiteSettingsResponse {

		$url      = $this->environment->get_api_url();
		$version  = $this->environment->get_autoship_version();
		$site_id  = autoship_get_site_id();
		$token    = autoship_get_token_auth();
		$endpoint = "{$url}/api/Sites/{$site_id}/ShippingRates/Nextime";

		try {
			// Ask to QPilot.
			$response = wp_remote_get(
				$endpoint,
				array(
					'headers' => array(
						'Content-Type'      => 'application/json',
						'Accept'            => 'application/json',
						'X-Autoship-Client' => "AC-WC-{$version}",
						'Authorization'     => "Bearer $token",
					),
					'method'  => 'GET',
					'timeout' => 10,
				)
			);

			// Check for an error while posting the registration data.
			if ( is_wp_error( $response ) ) {
				// Error responding to nextime endpoint.
				return new SiteSettingsResponse( 0, '', 0, false, false, false );
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $status_code ) {
				return new SiteSettingsResponse( 0, '', 0, false, false, false );
			}

			$result = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( ! is_array( $result ) || ! array_key_exists( 'SiteId', $result ) ) {

				// Nextime integration not found.
				return new SiteSettingsResponse( 0, '', 0, false, false, false );
			}

			// Get the site settings from QPilot.
			$site_id                           = $result['SiteId'] ?? 0;
			$site_token                        = $result['SiteToken'] ?? '';
			$integration_id                    = $result['IntegrationId'] ?? '';
			$should_display_delivery_date      = $result['ShouldDisplayDeliveryDate'] ?? true;
			$should_align_next_occurrence_date = $result['ShouldAlignNextOccurrenceDate'] ?? true;
			$is_enabled                        = $result['IsEnabled'] ?? false;

			if ( empty( $site_id ) ) {
				// The site was not found in the array.
				return new SiteSettingsResponse( 0, '', 0, false, false, false );
			}

			return new SiteSettingsResponse(
				$site_id,
				$site_token,
				$integration_id,
				$should_display_delivery_date,
				$should_align_next_occurrence_date,
				$is_enabled
			);

		} catch ( NextimeHttpException $e ) {
			return new SiteSettingsResponse( 0, '', 0, false, false, false );
		}
	}
}
