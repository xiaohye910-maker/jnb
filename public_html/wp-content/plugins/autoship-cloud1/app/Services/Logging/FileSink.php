<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The file logger class.
 *
 * @package  Autoship
 * @since    2.8.9
 */
namespace Autoship\Services\Logging;

use DirectoryIterator;
use Exception;

/**
 * The file logger class.
 *
 * @package  Autoship
 * @since    2.8.9
 */
class FileSink implements SinkInterface {
	/**
	 * Whether logging is enabled
	 *
	 * @var bool
	 */
	private bool $enabled;

	/**
	 * Whether tracing is enabled
	 *
	 * @var bool
	 */
	private bool $tracing_enabled;

	/**
	 * The log file path
	 *
	 * @var string
	 */
	private string $log_path;

	/**
	 * Contains the value if the logs can be cleaned up.
	 *
	 * @var bool
	 */
	private bool $can_cleanup_logs;

	/**
	 * Get the keep count value.
	 *
	 * @var int
	 */
	private int $keep_count;

	/**
	 * Get the log filename.
	 *
	 * @var string
	 */
	private string $log_filename;

	/**
	 * Contains the value which indicates if the log directory exists.
	 *
	 * @var bool|null
	 */
	private ?bool $log_directory_exists;

	/**
	 * Contains the log directory.
	 *
	 * @var string|mixed|null
	 */
	private string $log_directory;

	/**
	 * Gets the last cleanup time.
	 *
	 * @var int
	 */
	private int $last_cleanup = 0;

	/**
	 * Gets the cleanup interval.
	 *
	 * @var int
	 */
	private int $cleanup_interval = 3600;

	/**
	 * Contains the log buffer.
	 *
	 * @var array
	 */
	private array $buffer = array();

	/**
	 * The buffer size limit.
	 *
	 * @var int
	 */
	private int $buffer_size_limit = 20;

	/**
	 * Constructor
	 */
	public function __construct() {

		$logging_state  = false;
		$enabled_option = get_option( 'autoship_logging_state', 'active' );
		if ( 'active' === $enabled_option ) {
			$logging_state = true;
		}

		$tracing_state  = false;
		$tracing_option = get_option( 'autoship_debug_state', '' );
		if ( 'active' === $tracing_option ) {
			$tracing_state = true;
		}

		$this->enabled         = $logging_state;
		$this->tracing_enabled = $tracing_state;

		// Check last cleanup time.
		$last_cleanup = get_option( 'autoship_logs_last_cleanup', 0 );
		if ( is_numeric( $last_cleanup ) && $last_cleanup > 0 && $last_cleanup <= time() ) {
			$this->last_cleanup = (int) $last_cleanup;
		}

		// Load the cleanup filter.
		$can_cleanup            = apply_filters( 'autoship_clean_logger_files', true );
		$this->can_cleanup_logs = ! is_bool( $can_cleanup ) ? true : $can_cleanup;

		// This should go in the constructor and be a field.
		$keep_count = apply_filters( 'autoship_keep_logger_files_count', 30 );
		if ( ! is_int( $keep_count ) || $keep_count < 1 ) {
			$keep_count = 30;
		}

		$this->keep_count = $keep_count;

		// This is the log filename building.
		$handle = apply_filters( 'autoship_file_name_handle', strtolower( get_bloginfo( 'title' ) ) );

		$date_suffix = gmdate( 'Y-m-d' );
		$hash_suffix = substr( wp_hash( $handle ), 0, 10 );

		$this->log_filename = sanitize_file_name(
			implode(
				'-',
				array(
					preg_replace( '/[^a-z0-9\-]/', '', strtolower( $handle ) ),
					$date_suffix,
					$hash_suffix,
					'autoship',
				)
			) . '.log'
		);

		register_shutdown_function( array( $this, 'flush_logs' ) );

		$cleanup_interval = apply_filters( 'autoship_log_cleanup_interval', 3600 );
		if ( is_int( $cleanup_interval ) && $cleanup_interval > 300 ) {
			$this->cleanup_interval = $cleanup_interval;
		}

		$paths         = wp_upload_dir( null, false );
		$log_directory = apply_filters( 'autoship_logs_directory_path', $paths['basedir'] . DIRECTORY_SEPARATOR . 'autoship-logs' . DIRECTORY_SEPARATOR );

		$this->log_directory = $log_directory;
		$this->log_path      = trailingslashit( $this->log_directory );
	}

	/**
	 * Log a message with a specific type
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @param string $type The type of log entry.
	 * @return bool Whether the logging was successful
	 */
	public function log( string $message, string $context = '', string $type = '' ): bool {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		if ( ( 'TRACE' === $type || 'DEBUG' === $type ) && ! $this->is_tracing_enabled() ) {
			return false;
		}

		// Add to buffer.
		$this->buffer[] = array(
			'message' => $message,
			'context' => $context,
			'type'    => $type,
			'time'    => gmdate( 'm-d-Y @ H:i:s' ),
		);

		// Flush if the buffer is full.
		if ( count( $this->buffer ) >= $this->buffer_size_limit ) {
			$this->flush_logs();
		}

		return true;
	}

	/**
	 * Log an informational message.
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function info( string $message, string $context = '' ): bool {
		return $this->log( $message, $context, 'INFO' );
	}

	/**
	 * Log a trace message for performance tracking.
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function trace( string $message, string $context = '' ): bool {
		return $this->log( $message, $context, 'TRACE' );
	}

	/**
	 * Log a debug message
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function debug( string $message, string $context = '' ): bool {
		return $this->log( $message, $context, 'DEBUG' );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function error( string $message, string $context = '' ): bool {
		return $this->log( $message, $context, 'ERROR' );
	}

	/**
	 * Log a warning message
	 *
	 * @param string $message The message to log.
	 * @param string $context The context of the message.
	 * @return bool Whether the logging was successful.
	 */
	public function warning( string $message, string $context = '' ): bool {
		return $this->log( $message, $context, 'WARNING' );
	}

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool Whether logging is enabled.
	 */
	public function is_enabled(): bool {
		return $this->enabled;
	}

	/**
	 * Check if tracing is enabled.
	 *
	 * @return bool Whether logging is enabled.
	 */
	public function is_tracing_enabled(): bool {
		return $this->tracing_enabled;
	}

	/**
	 * Checks if the log directory exists and if not, tries to create it.
	 *
	 * @return bool True if the directory created or exists else false.
	 */
	private function ensure_log_directory_exists(): bool {

		if ( isset( $this->log_directory_exists ) ) {
			return $this->log_directory_exists;
		}

		$log_filename = $this->log_filename;
		$path         = trailingslashit( $this->log_directory );
		$dirname      = dirname( $path . $log_filename );

		if ( is_dir( $dirname ) ) {
			$this->log_directory_exists = true;

			return true;
		}

		$success = wp_mkdir_p( $path );

		if ( ! is_writable( $path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
			error_log( 'Autoship Logger Exception: Log directory exists but is not writable.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			$this->log_directory_exists = false;

			return false;
		}

		if ( ! $success ) {
			error_log( 'Autoship Logger Exception: Failed to create or open the log directory.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			$this->log_directory_exists = false;

			return false;
		}

		// Create the directory and add security plus bot check with htaccess.
		if ( ! file_exists( trailingslashit( $path ) . '.htaccess' ) ) {
			$htaccess_handle = fopen( trailingslashit( $path ) . '.htaccess', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( $htaccess_handle ) {
				fwrite( $htaccess_handle, 'deny from all' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fclose( $htaccess_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}
		}

		// Create an empty index.html file so it doesn't display the contents.
		if ( ! file_exists( trailingslashit( $path ) . 'index.html' ) ) {
			$index_handle = fopen( trailingslashit( $path ) . 'index.html', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( $index_handle ) {
				fwrite( $index_handle, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fclose( $index_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}
		}

		$this->log_directory_exists = true;

		return true;
	}

	/**
	 * Returns the value that indicates if the logger has log cleanup capabilities.
	 *
	 * @return bool
	 */
	public function can_cleanup_logs(): bool {
		return $this->can_cleanup_logs;
	}

	/**
	 * Performs a log cleanup if the logger allows it.
	 *
	 * @return bool
	 */
	public function cleanup_logs(): bool {
		if ( ! $this->can_cleanup_logs() ) {
			return false;
		}

		// Check for the Log Directory.
		if ( ! $this->ensure_log_directory_exists() ) {
			return false;
		}

		// Get all log files directly with glob (more efficient).
		$log_files = glob( $this->log_directory . '/*.log' );

		// If there are no logs, or we haven't hit the threshold, then don't delete the logs.
		if ( count( $log_files ) <= $this->keep_count ) {
			return false;
		}

		// Sort files by modification time (oldest first).
		usort(
			$log_files,
			function ( $a, $b ) {
				return filemtime( $a ) - filemtime( $b );
			}
		);

		// Slice off a chunk of files to delete.
		$files_to_delete = array_slice( $log_files, 0, count( $log_files ) - $this->keep_count, true );

		// Delete each of the files.
		foreach ( $files_to_delete as $filename => $timestamp ) {

			$file_path = trailingslashit( $this->log_directory ) . $filename . '.log';
			if ( is_file( $file_path ) && strpos( realpath( $file_path ), realpath( $this->log_directory ) ) === 0 ) {
				unlink( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}

		return true;
	}

	/**
	 * Returns the value that indicates if the logger has log listing capabilities.
	 *
	 * @return bool
	 */
	public function can_list_logs(): bool {
		return true;
	}

	/**
	 * Retrieves the current log files sorted by last mod datetime.
	 *
	 * @return array An array of filenames.
	 */
	public function list_logs(): array {
		// Check for the Log Directory and Make it if it doesn't exist.
		if ( ! $this->ensure_log_directory_exists() ) {
			return array();
		}

		$result      = array();
		$log_pattern = '/^[\w\-]+-\d{4}-\d{2}-\d{2}-[a-f0-9]+-autoship\.log$/';

		try {
			$dir = new DirectoryIterator( $this->log_directory );
			foreach ( $dir as $file_info ) {
				if ( $file_info->isFile() && preg_match( $log_pattern, $file_info->getFilename() ) ) {
					$name            = preg_replace( '/\\.[^.\\s]{3}$/', '', $file_info->getFilename() );
					$result[ $name ] = $file_info->getMTime();
				}
			}

			// Sort the files by modification time (newest first).
			arsort( $result );

			return $result;
		} catch ( Exception $e ) {
			error_log( sprintf( 'Autoship Logger Exception: Failed to list log files: %s', $e->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array();
		}
	}

	/**
	 * Returns the value that indicates if the logger has download capabilities.
	 *
	 * @return bool
	 */
	public function can_download_logs(): bool {
		return true;
	}

	/**
	 * Streams a log file for download.
	 *
	 * @param string $log_name The filename of the log.
	 * @return void
	 */
	public function download_log( string $log_name ): void {
		$file     = "$log_name.log";
		$path     = trailingslashit( $this->log_directory );
		$filepath = $path . $file;

		// Verify the file exists and is within the allowed directory.
		$real_path = realpath( $filepath );
		$logs_dir  = realpath( $path );

		if ( ! $real_path || ! file_exists( $real_path ) || ! is_file( $real_path ) || strpos( $real_path, $logs_dir ) !== 0 || ! preg_match( '/\.log$/', $real_path ) ) {
			wp_die( esc_html( __( 'Invalid log file requested', 'autoship' ) ) );
		}

		// Check file size and set the time limit accordingly.
		$filesize = filesize( $real_path );
		$timeout  = max( ini_get( 'max_execution_time' ), 60 );

		set_time_limit( $timeout );

		// Set headers for file download.
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $file ) . '"' );
		header( 'Content-Length: ' . $filesize );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: DENY' );

		// Disable output buffering to reduce memory usage.
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		readfile( $real_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile

		exit();
	}

	/**
	 * Flushes the buffer logs into the file.
	 *
	 * @return bool
	 */
	public function flush_logs(): bool {

		// Only process if there are entries.
		if ( empty( $this->buffer ) ) {
			return true;
		}

		// Process cleanup only once per batch.
		$this->try_cleanup_logs();

		// Format all entries.
		$entries = '';
		foreach ( $this->buffer as $log ) {

			$type_prefix = '';
			if ( ! empty( $log['type'] ) ) {
				$type_prefix = "[{$log['type']}] ";
			}

			$entries .= "[{$log['time']}] $type_prefix{$log['context']} - {$log['message']}\n";
		}

		// Write all entries at once.
		$result = $this->write_to_log_file( $entries );

		// Clear buffer.
		$this->buffer = array();

		return $result;
	}

	/**
	 * Tries to clean up the logs if the time allows it.
	 *
	 * @return void
	 */
	private function try_cleanup_logs(): void {
		if ( ! $this->can_cleanup_logs() ) {
			return;
		}

		$current_time = time();
		if ( $current_time - $this->last_cleanup >= $this->cleanup_interval ) {
			$this->cleanup_logs();
			$this->last_cleanup = $current_time;

			update_option( 'autoship_logs_last_cleanup', $current_time );
		}
	}

	/**
	 * Write the content into the log file.
	 *
	 * @param string $content The content to write.
	 * @return bool
	 */
	private function write_to_log_file( string $content ): bool {
		if ( ! $this->ensure_log_directory_exists() ) {
			return false;
		}

		$log_path = $this->log_path . $this->log_filename;

		$log_file = @fopen( $log_path, 'a' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $log_file ) {
			error_log( sprintf( 'Autoship Logger Exception: Failed to create or open the log file %s.', $this->log_filename ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}

		if ( false === @fwrite( $log_file, $content . "\n" ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			error_log( sprintf( 'Autoship Logger Exception: Failed to write to log file %s.', $this->log_filename ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			@fclose( $log_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_fclose

			return false;
		}

		@fclose( $log_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return true;
	}
}
