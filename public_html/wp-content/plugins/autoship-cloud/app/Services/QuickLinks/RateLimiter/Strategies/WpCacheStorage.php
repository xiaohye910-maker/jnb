<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WP Cache Storage Strategy for Rate Limiter.
 *
 * Uses WordPress object cache for rate limit storage.
 * Requires a persistent object cache (Redis, Memcached) to be effective.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter\Strategies
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter\Strategies;

use Autoship\Services\QuickLinks\RateLimiter\Interfaces\RateLimiterStorageInterface;

/**
 * WP Cache Storage Strategy.
 *
 * Stores rate limit data using WordPress object cache.
 * Works best with persistent object cache plugins (Redis, Memcached).
 * Falls back gracefully if no persistent cache is configured.
 */
class WpCacheStorage implements RateLimiterStorageInterface {

	/**
	 * Cache group name.
	 *
	 * @var string
	 */
	private string $group;

	/**
	 * Cache key prefix.
	 *
	 * @var string
	 */
	private string $prefix = 'ql_rate_';

	/**
	 * Constructor.
	 *
	 * @param array $settings Settings array with optional 'group' and 'prefix' keys.
	 */
	public function __construct( array $settings = array() ) {
		$this->group = isset( $settings['group'] ) ? $settings['group'] : 'autoship_ratelimit';

		if ( isset( $settings['prefix'] ) && is_string( $settings['prefix'] ) ) {
			$this->prefix = $settings['prefix'];
		}
	}

	/**
	 * Get the current number of attempts for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return int The number of attempts (0 if none).
	 */
	public function get_attempts( string $key ): int {
		$cache_key = $this->build_cache_key( $key );
		$value     = wp_cache_get( $cache_key, $this->group );

		if ( false === $value ) {
			return 0;
		}

		return (int) $value;
	}

	/**
	 * Increment the attempt count for a key.
	 *
	 * @param string $key         The rate limit key.
	 * @param int    $ttl_seconds Time-to-live in seconds.
	 *
	 * @return int The new attempt count after incrementing.
	 */
	public function increment( string $key, int $ttl_seconds ): int {
		$cache_key = $this->build_cache_key( $key );
		$current   = $this->get_attempts( $key );
		$new_count = $current + 1;

		// wp_cache_set with expiration.
		wp_cache_set( $cache_key, $new_count, $this->group, $ttl_seconds );

		return $new_count;
	}

	/**
	 * Reset (delete) the attempt count for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return void
	 */
	public function reset( string $key ): void {
		$cache_key = $this->build_cache_key( $key );
		wp_cache_delete( $cache_key, $this->group );
	}

	/**
	 * Check if this storage strategy is available.
	 *
	 * Returns true only if a persistent object cache is configured.
	 * Without persistent cache, rate limits won't persist across requests.
	 *
	 * @return bool True if persistent object cache is available.
	 */
	public function is_available(): bool {
		// Check if an external object cache is in use.
		// wp_using_ext_object_cache() returns true for Redis, Memcached, etc.
		return wp_using_ext_object_cache();
	}

	/**
	 * Clean up expired entries.
	 *
	 * Object cache handles expiration automatically.
	 *
	 * @return void
	 */
	public function cleanup(): void {
		// Object cache handles expiration automatically - nothing to do.
	}

	/**
	 * Build the cache key from the rate limit key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return string The cache key.
	 */
	private function build_cache_key( string $key ): string {
		return $this->prefix . md5( $key );
	}

	/**
	 * Get the cache group name.
	 *
	 * @return string The cache group.
	 */
	public function get_group(): string {
		return $this->group;
	}
}
