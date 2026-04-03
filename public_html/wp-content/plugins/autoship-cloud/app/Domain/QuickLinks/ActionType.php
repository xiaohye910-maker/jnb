<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Enum for QuickLink action types.
 *
 * Defines the different types of actions that can be performed
 * via QuickLinks: Resume, Pause, Process Now, and Reactivate.
 * Each action type corresponds to a different QPilot API endpoint.
 *
 * @package Autoship\Domain\QuickLinks
 * @since   2.11.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * QuickLink action type enumeration.
 *
 * Represents the different types of actions that can be performed
 * via QuickLinks on scheduled orders.
 */
class ActionType {

	/**
	 * Resume the scheduled order (change status to Active).
	 *
	 * @var int
	 */
	public const RESUME = 0;

	/**
	 * Pause the scheduled order (change status to Paused).
	 *
	 * @var int
	 */
	public const PAUSE = 1;

	/**
	 * Process the scheduled order immediately (Retry endpoint).
	 *
	 * @var int
	 */
	public const PROCESS_NOW = 2;

	/**
	 * Reactivate the scheduled order (SafeActivate endpoint).
	 *
	 * @var int
	 */
	public const REACTIVATE = 3;

	/**
	 * Get human-readable name for action type.
	 *
	 * @param int $type The action type constant.
	 *
	 * @return string The action name.
	 */
	public static function get_name( int $type ): string {
		switch ( $type ) {
			case self::RESUME:
				return 'Resume';
			case self::PAUSE:
				return 'Pause';
			case self::PROCESS_NOW:
				return 'ProcessNow';
			case self::REACTIVATE:
				return 'Reactivate';
			default:
				return 'Unknown';
		}
	}

	/**
	 * Check if the action type is valid.
	 *
	 * @param int $type The action type to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid( int $type ): bool {
		return in_array(
			$type,
			array( self::RESUME, self::PAUSE, self::PROCESS_NOW, self::REACTIVATE ),
			true
		);
	}

	/**
	 * Parse action type from string or integer.
	 *
	 * Handles various string formats from QPilot API:
	 * - "Resume", "resume", "0"
	 * - "ProcessNow", "process_now", "Process Now", "2"
	 *
	 * @param string|int $value The value to parse.
	 *
	 * @return int|null The action type constant or null if invalid.
	 */
	public static function parse( $value ): ?int {
		if ( is_int( $value ) ) {
			return self::is_valid( $value ) ? $value : null;
		}

		$normalized = strtolower( str_replace( array( ' ', '-' ), '_', $value ) );

		switch ( $normalized ) {
			case 'resume':
			case '0':
				return self::RESUME;
			case 'pause':
			case '1':
				return self::PAUSE;
			case 'processnow':
			case 'process_now':
			case '2':
				return self::PROCESS_NOW;
			case 'reactivate':
			case '3':
				return self::REACTIVATE;
			default:
				return null;
		}
	}
}
