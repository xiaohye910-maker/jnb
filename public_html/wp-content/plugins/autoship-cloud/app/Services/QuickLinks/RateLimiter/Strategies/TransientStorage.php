<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Transient Storage Strategy for Rate Limiter.
 *
 * Uses WordPress transients for rate limit storage.
 * This is the default and fallback storage strategy.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter\Strategies
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter\Strategies;

use Autoship\Services\QuickLinks\RateLimiter\Interfaces\RateLimiterStorageInterface;

/**
 * Transient Storage Strategy.
 *
 * Stores rate limit data using WordPress transients.
 * Transients auto-expire, so no cleanup is needed.
 */
class TransientStorage implements RateLimiterStorageInterface {

	/**
	 * Transient key prefix.
	 *
	 * @var string
	 */
	private string $prefix = 'ql_rate_';

	/**
	 * Constructor.
	 *
	 * @param array $settings Optional settings (not used for transients).
	 */
	public function __construct( array $settings = array() ) {
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
		$transient_key = $this->build_transient_key( $key );
		$value         = get_transient( $transient_key );

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
		$transient_key = $this->build_transient_key( $key );
		$current       = $this->get_attempts( $key );
		$new_count     = $current + 1;

		// Set transient with TTL.
		// Note: If transient already exists, this resets the TTL.
		// This is acceptable for rate limiting - the window slides.
		set_transient( $transient_key, $new_count, $ttl_seconds );

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
		$transient_key = $this->build_transient_key( $key );
		delete_transient( $transient_key );
	}

	/**
	 * Check if this storage strategy is available.
	 *
	 * Transients are always available in WordPress.
	 *
	 * @return bool Always returns true.
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * Clean up expired entries.
	 *
	 * Transients auto-expire, so no cleanup is needed.
	 *
	 * @return void
	 */
	public function cleanup(): void {
		// Transients auto-expire - nothing to do.
	}

	/**
	 * Build the transient key from the rate limit key.
	 *
	 * WordPress transient names are limited to 172 characters.
	 * We use MD5 to ensure consistent length.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return string The transient key.
	 */
	private function build_transient_key( string $key ): string {
		return $this->prefix . md5( $key );
	}
}
