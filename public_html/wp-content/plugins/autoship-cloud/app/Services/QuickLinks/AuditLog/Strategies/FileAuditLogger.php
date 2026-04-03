<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * File Audit Logger Strategy.
 *
 * Fallback audit logging strategy using file system storage.
 * Used when database logging fails.
 *
 * @package Autoship\Services\QuickLinks\AuditLog\Strategies
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog\Strategies;

use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditLoggerInterface;
use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;
use Autoship\Services\QuickLinks\AuditLog\DTOs\AuditEntry;

/**
 * File Audit Logger Strategy.
 *
 * Stores audit log entries in JSON format in the file system.
 * Creates security files to prevent direct access.
 */
class FileAuditLogger implements AuditLoggerInterface {

	/**
	 * Storage directory path.
	 *
	 * @var string
	 */
	private $directory;

	/**
	 * Whether the directory has been initialized.
	 *
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * WordPress functions interface.
	 *
	 * @var AuditFunctionInterface
	 */
	private $wp_functions;

	/**
	 * Constructor.
	 *
	 * @param AuditFunctionInterface $wp_functions WordPress functions interface.
	 * @param array                  $settings     Settings with optional 'directory' key.
	 */
	public function __construct( AuditFunctionInterface $wp_functions, array $settings = array() ) {
		$this->wp_functions = $wp_functions;

		if ( isset( $settings['directory'] ) && ! empty( $settings['directory'] ) ) {
			$this->directory = $this->wp_functions->trailingslashit( $settings['directory'] );
		} else {
			$upload_dir      = $this->wp_functions->wp_upload_dir();
			$this->directory = $this->wp_functions->trailingslashit( $upload_dir['basedir'] ) . 'autoship-audit-logs/';
		}
	}

	/**
	 * Log an audit entry.
	 *
	 * @param AuditEntry $entry The entry to log.
	 *
	 * @return bool True if logging succeeded.
	 */
	public function log( AuditEntry $entry ): bool {
		if ( ! $this->ensure_directory_exists() ) {
			return false;
		}

		$filename = $this->get_log_filename();
		$filepath = $this->directory . $filename;

		$line = $this->format_log_line( $entry );

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$handle = @fopen( $filepath, 'a' );
		if ( false === $handle ) {
			return false;
		}

		if ( ! flock( $handle, LOCK_EX ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose( $handle );
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		$result = fwrite( $handle, $line . "\n" );

		flock( $handle, LOCK_UN );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $handle );

		return false !== $result;
	}

	/**
	 * Check if file logging is available.
	 *
	 * @return bool True if available.
	 */
	public function is_available(): bool {
		return $this->ensure_directory_exists();
	}

	/**
	 * Clean up old log files.
	 *
	 * @param int $retention_days Days to retain.
	 *
	 * @return int Number of files deleted.
	 */
	public function cleanup( int $retention_days ): int {
		if ( ! is_dir( $this->directory ) ) {
			return 0;
		}

		$cutoff_timestamp = strtotime( "-{$retention_days} days" );
		$deleted_count    = 0;

		$files = glob( $this->directory . 'quicklink-audit-*.log' );
		if ( false === $files ) {
			return 0;
		}

		foreach ( $files as $file ) {
			if ( preg_match( '/quicklink-audit-(\d{4}-\d{2}-\d{2})-[a-f0-9]+\.log$/', $file, $matches ) ) {
				$file_date = strtotime( $matches[1] );
				if ( $file_date && $file_date < $cutoff_timestamp ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
					if ( @unlink( $file ) ) {
						++$deleted_count;
					}
				}
			}
		}

		return $deleted_count;
	}

	/**
	 * Get strategy name.
	 *
	 * @return string
	 */
	public function get_strategy_name(): string {
		return 'file';
	}

	/**
	 * Generate log filename based on current date.
	 *
	 * @return string
	 */
	private function get_log_filename(): string {
		$date = gmdate( 'Y-m-d' );
		$hash = substr( $this->wp_functions->wp_hash( 'quicklink-audit' ), 0, 8 );
		return "quicklink-audit-{$date}-{$hash}.log";
	}

	/**
	 * Format audit entry as log line.
	 *
	 * @param AuditEntry $entry The entry.
	 *
	 * @return string JSON-encoded log line.
	 */
	private function format_log_line( AuditEntry $entry ): string {
		$data      = $entry->to_array();
		$timestamp = $entry->get_created_at();

		return sprintf(
			'[%s] %s',
			$timestamp,
			$this->wp_functions->wp_json_encode( $data, JSON_UNESCAPED_SLASHES )
		);
	}

	/**
	 * Ensure storage directory exists.
	 *
	 * @return bool True if directory is available.
	 */
	private function ensure_directory_exists(): bool {
		if ( $this->initialized ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
			return is_dir( $this->directory ) && is_writable( $this->directory );
		}

		if ( ! is_dir( $this->directory ) ) {
			$created = $this->wp_functions->wp_mkdir_p( $this->directory );
			if ( ! $created ) {
				return false;
			}
			$this->create_security_files();
		}

		$this->initialized = true;
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
		return is_dir( $this->directory ) && is_writable( $this->directory );
	}

	/**
	 * Create security files to prevent direct access.
	 *
	 * @return void
	 */
	private function create_security_files(): void {
		// Create .htaccess to deny direct access.
		$htaccess_path = $this->directory . '.htaccess';
		if ( ! file_exists( $htaccess_path ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			$handle = @fopen( $htaccess_path, 'w' );
			if ( false !== $handle ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fwrite( $handle, 'deny from all' );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				fclose( $handle );
			}
		}

		// Create empty index.html to prevent directory listing.
		$index_path = $this->directory . 'index.html';
		if ( ! file_exists( $index_path ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			$handle = @fopen( $index_path, 'w' );
			if ( false !== $handle ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fwrite( $handle, '' );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				fclose( $handle );
			}
		}
	}

	/**
	 * Get the storage directory path.
	 *
	 * @return string
	 */
	public function get_directory(): string {
		return $this->directory;
	}
}
