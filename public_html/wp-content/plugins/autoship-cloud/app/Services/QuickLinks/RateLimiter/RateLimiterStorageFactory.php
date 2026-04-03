<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Rate Limiter Storage Factory.
 *
 * Creates the appropriate storage strategy based on configuration.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter;

use Autoship\Services\QuickLinks\RateLimiter\Interfaces\RateLimiterStorageInterface;
use Autoship\Services\QuickLinks\RateLimiter\Strategies\TransientStorage;
use Autoship\Services\QuickLinks\RateLimiter\Strategies\DatabaseStorage;
use Autoship\Services\QuickLinks\RateLimiter\Strategies\WpCacheStorage;
use Autoship\Services\QuickLinks\RateLimiter\Strategies\FileStorage;

/**
 * Rate Limiter Storage Factory.
 *
 * Creates storage strategy instances based on configuration.
 * Automatically falls back to TransientStorage if the selected
 * strategy is not available.
 */
class RateLimiterStorageFactory {

	/**
	 * Create a storage strategy based on configuration.
	 *
	 * @param array $config Configuration array with 'strategy' and 'settings' keys.
	 *
	 * @return RateLimiterStorageInterface The storage strategy instance.
	 */
	public function create( array $config ): RateLimiterStorageInterface {
		$strategy = isset( $config['strategy'] ) ? $config['strategy'] : 'transient';
		$settings = isset( $config['settings'][ $strategy ] ) ? $config['settings'][ $strategy ] : array();

		$storage = $this->create_strategy( $strategy, $settings );

		// Fallback to transient if strategy is not available.
		if ( ! $storage->is_available() ) {
			// Log the fallback.
			do_action(
				'autoship_log',
				'warning',
				'QuickLink: Rate limiter storage fallback to transient',
				array(
					'requested_strategy' => $strategy,
					'reason'             => 'Storage not available',
				)
			);

			return new TransientStorage();
		}

		return $storage;
	}

	/**
	 * Create a specific storage strategy.
	 *
	 * @param string $strategy Strategy name (transient, database, wp_cache, file).
	 * @param array  $settings Strategy-specific settings.
	 *
	 * @return RateLimiterStorageInterface The storage strategy instance.
	 */
	private function create_strategy( string $strategy, array $settings ): RateLimiterStorageInterface {
		switch ( $strategy ) {
			case 'database':
				return new DatabaseStorage( $settings );

			case 'wp_cache':
				return new WpCacheStorage( $settings );

			case 'file':
				return new FileStorage( $settings );

			case 'transient':
			default:
				return new TransientStorage( $settings );
		}
	}

	/**
	 * Get the list of available storage strategies.
	 *
	 * @return array Array of strategy names.
	 */
	public function get_available_strategies(): array {
		return array( 'transient', 'database', 'wp_cache', 'file' );
	}

	/**
	 * Check if a specific strategy is available.
	 *
	 * @param string $strategy Strategy name.
	 * @param array  $settings Strategy-specific settings.
	 *
	 * @return bool True if the strategy is available.
	 */
	public function is_strategy_available( string $strategy, array $settings = array() ): bool {
		$storage = $this->create_strategy( $strategy, $settings );
		return $storage->is_available();
	}
}
