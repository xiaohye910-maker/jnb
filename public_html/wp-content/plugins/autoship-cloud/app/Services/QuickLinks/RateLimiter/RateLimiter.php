<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Rate Limiter Service.
 *
 * Main service for rate-limiting QuickLinks requests.
 * Uses a storage strategy for persistence.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter;

use Autoship\Services\QuickLinks\RateLimiter\Interfaces\RateLimiterStorageInterface;

/**
 * Rate Limiter Service.
 *
 * Provides rate-limiting functionality for QuickLinks.
 * Limits requests per IP address per QuickLink slug.
 */
class RateLimiter {

	/**
	 * Storage backend.
	 *
	 * @var RateLimiterStorageInterface
	 */
	private RateLimiterStorageInterface $storage;

	/**
	 * Maximum number of attempts allowed.
	 *
	 * @var int
	 */
	private int $max_attempts;

	/**
	 * Time window in seconds.
	 *
	 * @var int
	 */
	private int $window_seconds;

	/**
	 * Whether rate limiting is enabled.
	 *
	 * @var bool
	 */
	private bool $enabled;

	/**
	 * Constructor.
	 *
	 * @param RateLimiterStorageInterface $storage        Storage backend.
	 * @param int                         $max_attempts   Maximum attempts (default: 5).
	 * @param int                         $window_seconds Time window in seconds (default: 60).
	 * @param bool                        $enabled        Whether enabled (default: true).
	 */
	public function __construct(
		RateLimiterStorageInterface $storage,
		int $max_attempts = 5,
		int $window_seconds = 60,
		bool $enabled = true
	) {
		$this->storage        = $storage;
		$this->max_attempts   = $max_attempts;
		$this->window_seconds = $window_seconds;
		$this->enabled        = $enabled;
	}

	/**
	 * Check if the given IP/slug combination is rate-limited.
	 *
	 * @param string $ip   The IP address.
	 * @param string $slug The QuickLink slug.
	 *
	 * @return bool True if rate limited, false otherwise.
	 */
	public function is_limited( string $ip, string $slug ): bool {
		if ( ! $this->enabled ) {
			return false;
		}

		$key      = $this->build_key( $ip, $slug );
		$attempts = $this->storage->get_attempts( $key );

		return $attempts >= $this->max_attempts;
	}

	/**
	 * Record an attempt for the given IP/slug combination.
	 *
	 * @param string $ip   The IP address.
	 * @param string $slug The QuickLink slug.
	 *
	 * @return int The new attempt count.
	 */
	public function record_attempt( string $ip, string $slug ): int {
		if ( ! $this->enabled ) {
			return 0;
		}

		$key = $this->build_key( $ip, $slug );

		// Probabilistic cleanup (1 in 100 requests).
		if ( 1 === wp_rand( 1, 100 ) ) {
			$this->storage->cleanup();
		}

		return $this->storage->increment( $key, $this->window_seconds );
	}

	/**
	 * Get the number of remaining attempts.
	 *
	 * @param string $ip   The IP address.
	 * @param string $slug The QuickLink slug.
	 *
	 * @return int The number of remaining attempts.
	 */
	public function get_remaining( string $ip, string $slug ): int {
		if ( ! $this->enabled ) {
			return $this->max_attempts;
		}

		$key      = $this->build_key( $ip, $slug );
		$attempts = $this->storage->get_attempts( $key );

		return max( 0, $this->max_attempts - $attempts );
	}

	/**
	 * Get the number of seconds until the rate limit resets.
	 *
	 * Note: For sliding window implementations (like transients),
	 * this returns the full window. For fixed window implementations,
	 * this could return the actual time remaining.
	 *
	 * @param string $ip   The IP address.
	 * @param string $slug The QuickLink slug.
	 *
	 * @return int Seconds until reset.
	 */
	public function get_retry_after( string $ip, string $slug ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters kept for interface consistency and future per-key expiry tracking.
		// For simplicity, return the full window.
		// A more sophisticated implementation could track the actual expiry time.
		return $this->window_seconds;
	}

	/**
	 * Reset the rate limit for an IP/slug combination.
	 *
	 * @param string $ip   The IP address.
	 * @param string $slug The QuickLink slug.
	 *
	 * @return void
	 */
	public function reset( string $ip, string $slug ): void {
		$key = $this->build_key( $ip, $slug );
		$this->storage->reset( $key );
	}

	/**
	 * Get the maximum number of attempts.
	 *
	 * @return int Maximum attempts.
	 */
	public function get_max_attempts(): int {
		return $this->max_attempts;
	}

	/**
	 * Get the time window in seconds.
	 *
	 * @return int Window in seconds.
	 */
	public function get_window_seconds(): int {
		return $this->window_seconds;
	}

	/**
	 * Check if rate limiting is enabled.
	 *
	 * @return bool True if enabled.
	 */
	public function is_enabled(): bool {
		return $this->enabled;
	}

	/**
	 * Build the rate limit key from IP and slug.
	 *
	 * Format: quicklink:{ip}:{slug}
	 *
	 * @param string $ip   The IP address.
	 * @param string $slug The QuickLink slug.
	 *
	 * @return string The rate limit key.
	 */
	private function build_key( string $ip, string $slug ): string {
		return 'quicklink:' . $ip . ':' . $slug;
	}
}
