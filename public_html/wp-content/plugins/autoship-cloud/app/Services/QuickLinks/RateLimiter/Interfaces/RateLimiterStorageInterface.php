<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Rate Limiter Storage Interface.
 *
 * Defines the contract for rate limiter storage backends.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter\Interfaces
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter\Interfaces;

/**
 * Rate Limiter Storage Interface.
 *
 * All rate limiter storage strategies must implement this interface.
 * Strategies include: Transient, Database, WpCache, File.
 */
interface RateLimiterStorageInterface {

	/**
	 * Get the current number of attempts for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return int The number of attempts (0 if none).
	 */
	public function get_attempts( string $key ): int;

	/**
	 * Increment the attempt count for a key.
	 *
	 * If the key doesn't exist, it should be created with count 1.
	 * If the key exists but has expired, it should be reset to count 1.
	 *
	 * @param string $key         The rate limit key.
	 * @param int    $ttl_seconds Time-to-live in seconds.
	 *
	 * @return int The new attempt count after incrementing.
	 */
	public function increment( string $key, int $ttl_seconds ): int;

	/**
	 * Reset (delete) the attempt count for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return void
	 */
	public function reset( string $key ): void;

	/**
	 * Check if this storage strategy is available.
	 *
	 * For example, WpCache might return false if no persistent
	 * object cache is configured. Database might return false
	 * if the table doesn't exist and can't be created.
	 *
	 * @return bool True if the storage is available, false otherwise.
	 */
	public function is_available(): bool;

	/**
	 * Clean up expired entries.
	 *
	 * This is optional - some storages auto-expire (transients).
	 * For database/file storage, this removes old entries.
	 * Implementations may choose to run this probabilistically.
	 *
	 * @return void
	 */
	public function cleanup(): void;
}
