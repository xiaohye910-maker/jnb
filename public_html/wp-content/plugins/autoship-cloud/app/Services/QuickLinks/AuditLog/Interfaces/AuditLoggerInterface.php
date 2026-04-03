<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Audit Logger Interface.
 *
 * Defines the contract for audit logging strategies.
 *
 * @package Autoship\Services\QuickLinks\AuditLog\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog\Interfaces;

use Autoship\Services\QuickLinks\AuditLog\DTOs\AuditEntry;

/**
 * Audit Logger Interface.
 *
 * All audit logging strategies must implement this interface.
 */
interface AuditLoggerInterface {

	/**
	 * Log an audit entry.
	 *
	 * @param AuditEntry $entry The audit entry to log.
	 *
	 * @return bool True if logging succeeded, false otherwise.
	 */
	public function log( AuditEntry $entry ): bool;

	/**
	 * Check if this logger is available and functional.
	 *
	 * @return bool True if available.
	 */
	public function is_available(): bool;

	/**
	 * Clean up old entries based on retention policy.
	 *
	 * @param int $retention_days Number of days to retain entries.
	 *
	 * @return int Number of entries deleted.
	 */
	public function cleanup( int $retention_days ): int;

	/**
	 * Get the logger strategy name.
	 *
	 * @return string The strategy name (e.g., 'database', 'file').
	 */
	public function get_strategy_name(): string;
}
