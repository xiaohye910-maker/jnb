<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Enum for QuickLink redirect types.
 *
 * Defines the different redirect behaviors after a QuickLink
 * action is executed: Thank-You Page, V2 Portal, or Custom URL.
 *
 * @package Autoship\Domain\QuickLinks
 * @since   2.11.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * QuickLink redirect type enumeration.
 *
 * Determines where to redirect the customer after
 * successfully executing a QuickLink action.
 */
class RedirectType {

	/**
	 * Show the thank-you page with a custom message.
	 *
	 * @var int
	 */
	public const THANK_YOU_PAGE = 0;

	/**
	 * Redirect to V2 customer portal.
	 *
	 * @var int
	 */
	public const V2_PORTAL = 1;

	/**
	 * Redirect to custom URL.
	 *
	 * @var int
	 */
	public const CUSTOM_URL = 2;

	/**
	 * Get the human-readable name for the redirect type.
	 *
	 * @param int $type The redirect type constant.
	 *
	 * @return string The redirect type name.
	 */
	public static function get_name( int $type ): string {
		switch ( $type ) {
			case self::THANK_YOU_PAGE:
				return 'ThankYouPage';
			case self::V2_PORTAL:
				return 'V2Portal';
			case self::CUSTOM_URL:
				return 'CustomUrl';
			default:
				return 'Unknown';
		}
	}

	/**
	 * Check if the redirect type is valid.
	 *
	 * @param int $type The redirect type to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid( int $type ): bool {
		return in_array(
			$type,
			array( self::THANK_YOU_PAGE, self::V2_PORTAL, self::CUSTOM_URL ),
			true
		);
	}

	/**
	 * Parse a redirect type from various formats.
	 *
	 * Accepts both integer values and string names from the API.
	 * Handles: "V2Portal", "ThankYouPage", "CustomUrl", 0, 1, 2.
	 *
	 * @param mixed $value The value to parse (int or string).
	 *
	 * @return int|null The redirect type constant, or null if invalid.
	 */
	public static function parse( $value ): ?int {
		// Handle integer values directly.
		if ( is_int( $value ) ) {
			return self::is_valid( $value ) ? $value : null;
		}

		// Handle numeric strings.
		if ( is_numeric( $value ) ) {
			$int_value = (int) $value;
			return self::is_valid( $int_value ) ? $int_value : null;
		}

		// Handle string names from API.
		if ( is_string( $value ) ) {
			$normalized = strtolower( str_replace( array( ' ', '-', '_' ), '', $value ) );

			switch ( $normalized ) {
				case 'thankyoupage':
				case 'thankyou':
					return self::THANK_YOU_PAGE;
				case 'v2portal':
				case 'portal':
					return self::V2_PORTAL;
				case 'customurl':
				case 'custom':
					return self::CUSTOM_URL;
				default:
					return null;
			}
		}

		return null;
	}
}
