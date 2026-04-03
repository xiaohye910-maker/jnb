<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * DTO for Custom Meta Tag.
 *
 * Represents a custom HTML meta tag for SEO and social sharing,
 * used in QuickLink templates.
 *
 * @package Autoship\Services\QuickLinks\DTOs
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\DTOs;

/**
 * Custom Meta Tag DTO.
 *
 * Holds data for a single HTML meta tag including
 * type (name or property), key, and value.
 */
class CustomMetaTag {

	/**
	 * Meta tag type ('name' or 'property').
	 *
	 * @var string|null
	 */
	public ?string $type = null;

	/**
	 * Meta tag key/name.
	 *
	 * @var string|null
	 */
	public ?string $key = null;

	/**
	 * Meta tag value/content.
	 *
	 * @var string|null
	 */
	public ?string $value = null;

	/**
	 * Create DTO from API response array.
	 *
	 * @param array $data API response data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		$dto->type  = $data['type'] ?? null;
		$dto->key   = $data['key'] ?? null;
		$dto->value = $data['value'] ?? null;

		return $dto;
	}

	/**
	 * Generate HTML meta tag string.
	 *
	 * @return string HTML meta tag or empty string if invalid.
	 */
	public function to_html(): string {
		if ( empty( $this->type ) || empty( $this->key ) ) {
			return '';
		}

		$escaped_value = esc_attr( $this->value ?? '' );

		if ( 'property' === $this->type ) {
			return sprintf(
				'<meta property="%s" content="%s" />',
				esc_attr( $this->key ),
				$escaped_value
			);
		}

		// Default to 'name' type.
		return sprintf(
			'<meta name="%s" content="%s" />',
			esc_attr( $this->key ),
			$escaped_value
		);
	}

	/**
	 * Check if this is an Open Graph meta tag.
	 *
	 * @return bool True if Open Graph tag.
	 */
	public function is_open_graph(): bool {
		return 'property' === $this->type && strpos( $this->key ?? '', 'og:' ) === 0;
	}

	/**
	 * Check if this is a Twitter Card meta tag.
	 *
	 * @return bool True if Twitter Card tag.
	 */
	public function is_twitter_card(): bool {
		return 'name' === $this->type && strpos( $this->key ?? '', 'twitter:' ) === 0;
	}

	/**
	 * Check if the meta tag is valid.
	 *
	 * @return bool True if valid.
	 */
	public function is_valid(): bool {
		return ! empty( $this->type ) && ! empty( $this->key );
	}
}
