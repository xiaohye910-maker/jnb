<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Feature Flag Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 2.11.0
 */

namespace Autoship\Services\QPilot\Interfaces;

/**
 * Interface for feature flag management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 2.11.0
 */
interface FeatureFlagManagementInterface {

	/**
	 * Get all globally enabled feature flags.
	 *
	 * Calls GET /api/FeatureFlags to retrieve flags enabled across all sites.
	 *
	 * @return array<string> List of enabled feature flag names.
	 */
	public function get_feature_flags(): array;

	/**
	 * Get feature flags enabled for the current site.
	 *
	 * Calls GET /api/Sites/{siteId}/FeatureFlags to retrieve flags
	 * enabled specifically for this site.
	 *
	 * @return array<string> List of enabled feature flag names.
	 */
	public function get_site_feature_flags(): array;
}
