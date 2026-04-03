<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * UpdateSiteMetadataRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Sites;

/**
 * Request object for updating site metadata.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class UpdateSiteMetadataRequest {
	/**
	 * The metadata to update.
	 *
	 * @var array
	 */
	private array $metadata;

	/**
	 * Constructor.
	 *
	 * @param array $metadata The metadata to update.
	 */
	public function __construct( array $metadata = array() ) {
		$this->metadata = $metadata;
	}

	/**
	 * Set a metadata value.
	 *
	 * @param string $key The metadata key.
	 * @param mixed  $value The metadata value.
	 * @return self
	 */
	public function set_metadata( string $key, $value ): self {
		$this->metadata[ $key ] = $value;
		return $this;
	}

	/**
	 * Set multiple metadata values.
	 *
	 * @param array $metadata The metadata to set.
	 * @return self
	 */
	public function set_metadata_array( array $metadata ): self {
		$this->metadata = array_merge( $this->metadata, $metadata );
		return $this;
	}

	/**
	 * Get a metadata value.
	 *
	 * @param string $key The metadata key.
	 * @param mixed  $default_value The default value to return if the key doesn't exist.
	 * @return mixed The metadata value or the default value.
	 */
	public function get_metadata( string $key, $default_value = null ) {
		return $this->metadata[ $key ] ?? $default_value;
	}

	/**
	 * Get all metadata.
	 *
	 * @return array The metadata.
	 */
	public function get_all_metadata(): array {
		return $this->metadata;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		return $this->metadata;
	}
}
