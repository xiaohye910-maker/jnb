<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Service Client
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime;

use Autoship\Services\Nextime\Carriers\ShippingOptionsRequest;
use Autoship\Services\Nextime\Carriers\ShippingOptionsResponse;
use Autoship\Services\Nextime\Interfaces\CarriersManagementInterface;
use Autoship\Services\Nextime\Interfaces\SitesManagementInterface;
use Autoship\Services\Nextime\Access\SiteSettingsResponse;

/**
 * The Nextime Service Client
 *
 * @package Autoship\Services\Nextime
 * @since 2.10.1
 */
class NextimeServiceClient implements NextimeServiceInterface {
	/**
	 * The API client.
	 *
	 * @var NextimeSettingsInterface
	 */
	private NextimeSettingsInterface $settings;

	/**
	 * Sites management implementation.
	 *
	 * @var SitesManagementInterface
	 */
	private SitesManagementInterface $sites_management;

	/**
	 * Customer management implementation.
	 *
	 * @var CarriersManagementInterface
	 */
	private CarriersManagementInterface $carriers_management;

	/**
	 * Constructor.
	 *
	 * @param NextimeSettingsInterface    $settings The API client.
	 * @param SitesManagementInterface    $sites_management The sites management.
	 * @param CarriersManagementInterface $carriers_management The carriers management.
	 */
	public function __construct( NextimeSettingsInterface $settings, SitesManagementInterface $sites_management, CarriersManagementInterface $carriers_management ) {
		$this->settings            = $settings;
		$this->sites_management    = $sites_management;
		$this->carriers_management = $carriers_management;
	}

	/**
	 * Gets the shipping options for the given request.
	 *
	 * @param ShippingOptionsRequest $request The shipping options request.
	 *
	 * @return ShippingOptionsResponse The shipping options response.
	 */
	public function get_shipping_options( ShippingOptionsRequest $request ): ShippingOptionsResponse {
		return $this->carriers_management->get_shipping_options( $request );
	}

	/**
	 * Gets the site settings from Nextime.
	 *
	 * @return SiteSettingsResponse The Nextime site settings.
	 */
	public function get_site_settings(): SiteSettingsResponse {
		return $this->sites_management->get_site_settings();
	}

	/**
	 * Get the API key.
	 *
	 * @return NextimeSettingsInterface The API key.
	 */
	public function get_settings(): NextimeSettingsInterface {
		return $this->settings;
	}
}
