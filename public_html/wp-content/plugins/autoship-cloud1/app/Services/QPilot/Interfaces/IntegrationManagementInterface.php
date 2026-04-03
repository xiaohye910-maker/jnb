<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Integration Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

use Autoship\Services\QPilot\Integrations\IntegrationStatusResponse;
use Autoship\Services\QPilot\Integrations\IntegrationCheckResponse;
use Autoship\Services\QPilot\Integrations\SiteIntegrationsResponse;
use Autoship\Services\QPilot\Integrations\MigrationStatusResponse;

/**
 * Interface for integration management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface IntegrationManagementInterface {
	/**
	 * Get integration status.
	 *
	 * @return IntegrationStatusResponse The integration status response.
	 */
	public function get_integration_status(): IntegrationStatusResponse;

	/**
	 * Check integration.
	 *
	 * @return IntegrationCheckResponse The integration check response.
	 */
	public function check_integration(): IntegrationCheckResponse;

	/**
	 * Get site integrations.
	 *
	 * @return SiteIntegrationsResponse The site integrations response.
	 */
	public function get_site_integrations(): SiteIntegrationsResponse;

	/**
	 * Migrate processing version.
	 *
	 * @return MigrationStatusResponse The migration status response.
	 */
	public function migrate_processing_version(): MigrationStatusResponse;
}
