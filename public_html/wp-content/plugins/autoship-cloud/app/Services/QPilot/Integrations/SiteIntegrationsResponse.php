<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * SiteIntegrationsResponse class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Integrations;

use stdClass;

/**
 * Response object for site integrations data.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class SiteIntegrationsResponse {
	/**
	 * The site integrations.
	 *
	 * @var array
	 */
	private array $integrations = array();

	/**
	 * The raw response data.
	 *
	 * @var stdClass
	 */
	private stdClass $raw_data;

	/**
	 * Constructor.
	 *
	 * @param stdClass $data The response data.
	 */
	public function __construct( stdClass $data ) {
		$this->raw_data     = $data;
		$this->integrations = (array) $data;
	}

	/**
	 * Get all integrations.
	 *
	 * @return array The site integrations.
	 */
	public function get_integrations(): array {
		return $this->integrations;
	}

	/**
	 * Get a specific integration by key.
	 *
	 * @param string $key The integration key.
	 * @return mixed|null The integration data or null if not found.
	 */
	public function get_integration( string $key ) {
		return $this->integrations[ $key ] ?? null;
	}

	/**
	 * Check if an integration exists.
	 *
	 * @param string $key The integration key.
	 * @return bool Whether the integration exists.
	 */
	public function has_integration( string $key ): bool {
		return isset( $this->integrations[ $key ] );
	}

	/**
	 * Get the number of integrations.
	 *
	 * @return int The number of integrations.
	 */
	public function count(): int {
		return count( $this->integrations );
	}

	/**
	 * Get the raw response data.
	 *
	 * @return stdClass The raw response data.
	 */
	public function get_raw_data(): stdClass {
		return $this->raw_data;
	}

	/**
	 * Convert the response to an array.
	 *
	 * @return array The response as an array.
	 */
	public function to_array(): array {
		return $this->integrations;
	}
}
