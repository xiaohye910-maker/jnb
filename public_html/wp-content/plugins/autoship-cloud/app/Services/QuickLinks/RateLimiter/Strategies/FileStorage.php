<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * File Storage Strategy for Rate Limiter.
 *
 * Uses file system for rate limit storage.
 * Stores one JSON file per rate limit key.
 *
 * @package Autoship\Services\QuickLinks\RateLimiter\Strategies
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\RateLimiter\Strategies;

use Autoship\Services\QuickLinks\RateLimiter\Interfaces\RateLimiterStorageInterface;

/**
 * File Storage Strategy.
 *
 * Stores rate limit data in JSON files on the filesystem.
 * Each key gets its own file: {md5(key)}.json
 */
class FileStorage implements RateLimiterStorageInterface {

	/**
	 * Storage directory path.
	 *
	 * @var string
	 */
	private string $directory;

	/**
	 * Whether the directory has been initialized.
	 *
	 * @var bool
	 */
	private bool $initialized = false;

	/**
	 * Constructor.
	 *
	 * @param array $settings Settings array with optional 'directory' key.
	 */
	public function __construct( array $settings = array() ) {
		if ( isset( $settings['directory'] ) && ! empty( $settings['directory'] ) ) {
			$this->directory = trailingslashit( $settings['directory'] );
		} else {
			$upload_dir      = wp_upload_dir();
			$this->directory = trailingslashit( $upload_dir['basedir'] ) . 'autoship-ratelimits/';
		}
	}

	/**
	 * Get the current number of attempts for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return int The number of attempts (0 if none or expired).
	 */
	public function get_attempts( string $key ): int {
		$data = $this->read_file( $key );

		if ( null === $data ) {
			return 0;
		}

		// Check if expired.
		if ( isset( $data['expires_at'] ) && time() > $data['expires_at'] ) {
			$this->delete_file( $key );
			return 0;
		}

		return isset( $data['attempts'] ) ? (int) $data['attempts'] : 0;
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
		if ( ! $this->ensure_directory_exists() ) {
			return 0;
		}

		$file_path = $this->get_file_path( $key );
		$handle    = @fopen( $file_path, 'c+' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		if ( false === $handle ) {
			return 0;
		}

		// Lock the file for exclusive access.
		if ( ! flock( $handle, LOCK_EX ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			return 0;
		}

		// Read existing data.
		$contents = '';
		while ( ! feof( $handle ) ) {
			$contents .= fread( $handle, 8192 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		}

		$data      = ! empty( $contents ) ? json_decode( $contents, true ) : null;
		$now       = time();
		$new_count = 1;

		// Check if data exists and is not expired.
		if ( null !== $data && isset( $data['expires_at'] ) && $now < $data['expires_at'] ) {
			$new_count = ( isset( $data['attempts'] ) ? (int) $data['attempts'] : 0 ) + 1;
			// Keep the original expiry time (fixed window).
			$expires_at = $data['expires_at'];
		} else {
			// New entry or expired - reset.
			$expires_at = $now + $ttl_seconds;
		}

		$new_data = array(
			'attempts'   => $new_count,
			'expires_at' => $expires_at,
			'created_at' => isset( $data['created_at'] ) ? $data['created_at'] : $now,
			'updated_at' => $now,
		);

		// Truncate and write.
		ftruncate( $handle, 0 );
		rewind( $handle );
		fwrite( $handle, wp_json_encode( $new_data ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

		// Release lock and close.
		flock( $handle, LOCK_UN );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

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
		$this->delete_file( $key );
	}

	/**
	 * Check if this storage strategy is available.
	 *
	 * Checks if the directory can be created and is writable.
	 *
	 * @return bool True if the storage is available.
	 */
	public function is_available(): bool {
		return $this->ensure_directory_exists();
	}

	/**
	 * Clean up expired entries.
	 *
	 * Removes expired rate limit files.
	 *
	 * @return void
	 */
	public function cleanup(): void {
		if ( ! is_dir( $this->directory ) ) {
			return;
		}

		$now   = time();
		$files = glob( $this->directory . '*.json' );

		if ( false === $files ) {
			return;
		}

		foreach ( $files as $file ) {
			$contents = @file_get_contents( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( false === $contents ) {
				continue;
			}

			$data = json_decode( $contents, true );

			if ( null !== $data && isset( $data['expires_at'] ) && $now > $data['expires_at'] ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}
	}

	/**
	 * Ensure the storage directory exists.
	 *
	 * Creates the directory with security files if it doesn't exist.
	 *
	 * @return bool True if directory exists and is writable.
	 */
	private function ensure_directory_exists(): bool {
		if ( $this->initialized ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Direct check required for performance; WP_Filesystem overhead unnecessary for simple boolean check.
			return is_dir( $this->directory ) && is_writable( $this->directory );
		}

		if ( ! is_dir( $this->directory ) ) {
			$created = wp_mkdir_p( $this->directory );

			if ( ! $created ) {
				return false;
			}

			// Create security files.
			$this->create_security_files();
		}

		$this->initialized = true;
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Direct check required for performance; WP_Filesystem overhead unnecessary for simple boolean check.
		return is_dir( $this->directory ) && is_writable( $this->directory );
	}

	/**
	 * Create security files to prevent directory listing and direct access.
	 *
	 * @return void
	 */
	private function create_security_files(): void {
		// Create .htaccess to deny access.
		$htaccess_path = $this->directory . '.htaccess';
		if ( ! file_exists( $htaccess_path ) ) {
			$htaccess_handle = @fopen( $htaccess_path, 'w' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( false !== $htaccess_handle ) {
				fwrite( $htaccess_handle, 'deny from all' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fclose( $htaccess_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}
		}

		// Create empty index.html.
		$index_path = $this->directory . 'index.html';
		if ( ! file_exists( $index_path ) ) {
			$index_handle = @fopen( $index_path, 'w' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( false !== $index_handle ) {
				fwrite( $index_handle, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fclose( $index_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}
		}
	}

	/**
	 * Get the file path for a key.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return string The file path.
	 */
	private function get_file_path( string $key ): string {
		return $this->directory . md5( $key ) . '.json';
	}

	/**
	 * Read data from a rate limit file.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return array|null The data or null if not found.
	 */
	private function read_file( string $key ): ?array {
		$file_path = $this->get_file_path( $key );

		if ( ! file_exists( $file_path ) ) {
			return null;
		}

		$contents = @file_get_contents( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $contents ) {
			return null;
		}

		$data = json_decode( $contents, true );

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Delete a rate limit file.
	 *
	 * @param string $key The rate limit key.
	 *
	 * @return void
	 */
	private function delete_file( string $key ): void {
		$file_path = $this->get_file_path( $key );

		if ( file_exists( $file_path ) ) {
			@unlink( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
		}
	}

	/**
	 * Get the storage directory path.
	 *
	 * @return string The directory path.
	 */
	public function get_directory(): string {
		return $this->directory;
	}
}
