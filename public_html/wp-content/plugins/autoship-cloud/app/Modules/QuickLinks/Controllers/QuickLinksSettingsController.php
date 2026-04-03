<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLinks Settings Controller.
 *
 * Handles QuickLinks settings tab in Autoship admin.
 *
 * @package Autoship\Modules\QuickLinks\Controllers
 * @since   3.2.0
 */

namespace Autoship\Modules\QuickLinks\Controllers;

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;
use Autoship\Services\QuickLinks\Confirmation\ConfirmationCleanupScheduler;
use Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService;
use Autoship\Services\QuickLinks\RateLimiter\RateLimiterStorageFactory;

/**
 * QuickLinks Settings Controller.
 *
 * Manages the Quicklinks settings tab in the Autoship admin settings page.
 * Handles tab registration, rendering, and AJAX operations for settings.
 */
class QuickLinksSettingsController {

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'autoship_quicklinks_settings';

	/**
	 * Settings nonce action.
	 *
	 * @var string
	 */
	const SETTINGS_NONCE_ACTION = 'autoship_quicklinks_settings_nonce';

	/**
	 * Action nonce action.
	 *
	 * @var string
	 */
	const ACTION_NONCE_ACTION = 'autoship_quicklinks_action_nonce';

	/**
	 * Register the controller hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the settings tab.
		add_filter( 'autoship_admin_settings_tabs', array( $this, 'add_settings_tab' ) );

		// Exclude submit button for this tab (we use AJAX).
		add_filter( 'autoship_admin_settings_tab_include_submit', array( $this, 'exclude_submit_button' ), 10, 2 );

		// Register render callback.
		add_action( 'autoship_admin_settings_tab_autoship-quicklinks', array( $this, 'render_settings_tab' ) );

		// Register AJAX handlers.
		add_action( 'wp_ajax_autoship_quicklinks_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_autoship_quicklinks_flush_rewrite', array( $this, 'ajax_flush_rewrite_rules' ) );
		add_action( 'wp_ajax_autoship_quicklinks_run_cleanup', array( $this, 'ajax_run_cleanup' ) );
		add_action( 'wp_ajax_autoship_quicklinks_clear_rate_limits', array( $this, 'ajax_clear_rate_limits' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add the Quicklinks settings tab.
	 *
	 * @param array $tabs Existing tabs.
	 *
	 * @return array Modified tabs.
	 */
	public function add_settings_tab( array $tabs ): array {
		// Only add tab if quicklinks feature is enabled.
		$features = Plugin::get_service_container()->get( FeatureManagerInterface::class );
		if ( ! $features->is_enabled( 'quicklinks' ) ) {
			return $tabs;
		}

		$tabs['autoship-quicklinks'] = array(
			'label'      => __( 'Quick Actions Settings', 'autoship' ),
			'callback'   => array( $this, 'render_settings_tab' ),
			'link_class' => '',
		);

		return $tabs;
	}

	/**
	 * Exclude the default submit button for our tab.
	 *
	 * @param bool   $should_include Whether to include the submit button.
	 * @param string $tab            Current tab.
	 *
	 * @return bool Modified include value.
	 */
	public function exclude_submit_button( bool $should_include, string $tab ): bool {
		if ( 'autoship-quicklinks' === $tab ) {
			return false;
		}

		return $should_include;
	}

	/**
	 * Enqueue admin scripts for the settings page.
	 *
	 * @param string $hook The current admin page hook (unused, we check tab instead).
	 *
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// Check if we're on the quicklinks tab.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'autoship-quicklinks' !== $tab ) {
			return;
		}

		// Enqueue our settings JavaScript.
		wp_enqueue_script(
			'autoship-quicklinks-settings',
			plugin_dir_url( \Autoship_Plugin_File ) . 'js/admin/quicklinks/settings.js',
			array( 'jquery' ),
			\Autoship_Version,
			true
		);

		// Localize script with AJAX data.
		wp_localize_script(
			'autoship-quicklinks-settings',
			'autoshipQuicklinksSettings',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'settingsNonce' => wp_create_nonce( self::SETTINGS_NONCE_ACTION ),
				'actionNonce'   => wp_create_nonce( self::ACTION_NONCE_ACTION ),
				'strings'       => array(
					'saving'          => __( 'Saving...', 'autoship' ),
					'saved'           => __( 'Settings saved successfully.', 'autoship' ),
					'saveError'       => __( 'Failed to save settings.', 'autoship' ),
					'flushing'        => __( 'Flushing rewrite rules...', 'autoship' ),
					'flushed'         => __( 'Rewrite rules flushed successfully.', 'autoship' ),
					'flushError'      => __( 'Failed to flush rewrite rules.', 'autoship' ),
					'cleaningUp'      => __( 'Running cleanup...', 'autoship' ),
					'cleanedUp'       => __( 'Cleanup completed successfully.', 'autoship' ),
					'cleanupError'    => __( 'Failed to run cleanup.', 'autoship' ),
					'clearingLimits'  => __( 'Clearing rate limits...', 'autoship' ),
					'clearedLimits'   => __( 'Rate limits cleared successfully.', 'autoship' ),
					'clearLimitError' => __( 'Failed to clear rate limits.', 'autoship' ),
				),
			)
		);
	}

	/**
	 * Render the settings tab.
	 *
	 * @param array $autoship_settings Current Autoship settings (unused, part of action signature).
	 *
	 * @return void
	 */
	public function render_settings_tab( array $autoship_settings = array() ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$settings = $this->get_settings();

		// Get scheduled task info.
		$last_cleanup = get_option( ConfirmationCleanupScheduler::LAST_CLEANUP_OPTION, '' );

		// Check next scheduled cleanup using Action Scheduler (used by WooCommerce).
		$next_cleanup = false;
		if ( function_exists( 'as_next_scheduled_action' ) ) {
			$next_cleanup = as_next_scheduled_action( ConfirmationCleanupScheduler::TASK_HOOK );
		}

		// Load the template.
		$template_path = \Autoship_Plugin_Dir . '/templates/admin/settings/quicklinks.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}

	/**
	 * Get the current settings.
	 *
	 * @return array The settings array.
	 */
	public function get_settings(): array {
		$settings = get_option( self::SETTINGS_OPTION, array() );

		return wp_parse_args(
			$settings,
			$this->get_default_settings()
		);
	}

	/**
	 * Get default settings.
	 *
	 * @return array The default settings.
	 */
	public function get_default_settings(): array {
		return array(
			'enabled'      => true,
			'rate_limiter' => array(
				'enabled'        => true,
				'strategy'       => 'transient',
				'max_attempts'   => 5,
				'window_seconds' => 60,
			),
			'scanner'      => array(
				'enabled'                 => true,
				'behavioral_detection'    => true,
				'require_accept_language' => true,
				'require_html_accept'     => true,
				'min_suspicious_count'    => 2,
			),
			'maintenance'  => array(
				'confirmation_retention_days' => 90,
				'audit_retention_days'        => 90,
			),
		);
	}

	/**
	 * AJAX handler for saving settings.
	 *
	 * @return void
	 */
	public function ajax_save_settings(): void {
		// Verify nonce.
		if ( ! check_ajax_referer( self::SETTINGS_NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'autoship' ) ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'autoship' ) ) );
		}

		// Get and sanitize settings from POST.
		$settings = $this->sanitize_settings( $_POST );

		// Save settings.
		update_option( self::SETTINGS_OPTION, $settings );

		// Also update the legacy options for backward compatibility.
		$this->update_legacy_options( $settings );

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'autoship' ) ) );
	}

	/**
	 * Sanitize settings from POST data.
	 *
	 * @param array $post_data The POST data.
	 *
	 * @return array Sanitized settings.
	 */
	private function sanitize_settings( array $post_data ): array {
		$defaults = $this->get_default_settings();

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$settings = array(
			'enabled'      => isset( $post_data['enabled'] ) && 'yes' === $post_data['enabled'],
			'rate_limiter' => array(
				'enabled'        => isset( $post_data['rate_limiter_enabled'] ) && 'yes' === $post_data['rate_limiter_enabled'],
				'strategy'       => isset( $post_data['rate_limiter_strategy'] ) ? sanitize_text_field( $post_data['rate_limiter_strategy'] ) : $defaults['rate_limiter']['strategy'],
				'max_attempts'   => isset( $post_data['rate_limiter_max_attempts'] ) ? absint( $post_data['rate_limiter_max_attempts'] ) : $defaults['rate_limiter']['max_attempts'],
				'window_seconds' => isset( $post_data['rate_limiter_window_seconds'] ) ? absint( $post_data['rate_limiter_window_seconds'] ) : $defaults['rate_limiter']['window_seconds'],
			),
			'scanner'      => array(
				'enabled'                 => isset( $post_data['scanner_enabled'] ) && 'yes' === $post_data['scanner_enabled'],
				'behavioral_detection'    => isset( $post_data['scanner_behavioral_detection'] ) && 'yes' === $post_data['scanner_behavioral_detection'],
				'require_accept_language' => isset( $post_data['scanner_require_accept_language'] ) && 'yes' === $post_data['scanner_require_accept_language'],
				'require_html_accept'     => isset( $post_data['scanner_require_html_accept'] ) && 'yes' === $post_data['scanner_require_html_accept'],
				'min_suspicious_count'    => isset( $post_data['scanner_min_suspicious_count'] ) ? absint( $post_data['scanner_min_suspicious_count'] ) : $defaults['scanner']['min_suspicious_count'],
			),
			'maintenance'  => array(
				'confirmation_retention_days' => isset( $post_data['maintenance_confirmation_retention_days'] ) ? absint( $post_data['maintenance_confirmation_retention_days'] ) : $defaults['maintenance']['confirmation_retention_days'],
				'audit_retention_days'        => isset( $post_data['maintenance_audit_retention_days'] ) ? absint( $post_data['maintenance_audit_retention_days'] ) : $defaults['maintenance']['audit_retention_days'],
			),
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Validate strategy is valid.
		$valid_strategies = array( 'transient', 'database', 'wp_cache', 'file' );
		if ( ! in_array( $settings['rate_limiter']['strategy'], $valid_strategies, true ) ) {
			$settings['rate_limiter']['strategy'] = $defaults['rate_limiter']['strategy'];
		}

		// Validate numeric ranges.
		$settings['rate_limiter']['max_attempts']               = max( 1, min( 100, $settings['rate_limiter']['max_attempts'] ) );
		$settings['rate_limiter']['window_seconds']             = max( 10, min( 3600, $settings['rate_limiter']['window_seconds'] ) );
		$settings['scanner']['min_suspicious_count']            = max( 1, min( 10, $settings['scanner']['min_suspicious_count'] ) );
		$settings['maintenance']['confirmation_retention_days'] = max( 1, min( 365, $settings['maintenance']['confirmation_retention_days'] ) );
		$settings['maintenance']['audit_retention_days']        = max( 1, min( 365, $settings['maintenance']['audit_retention_days'] ) );

		return $settings;
	}

	/**
	 * Update legacy options for backward compatibility.
	 *
	 * @param array $settings The new settings.
	 *
	 * @return void
	 */
	private function update_legacy_options( array $settings ): void {
		// Update rate limiter config.
		$rate_limiter_config = array(
			'enabled'        => $settings['rate_limiter']['enabled'],
			'strategy'       => $settings['rate_limiter']['strategy'],
			'max_attempts'   => $settings['rate_limiter']['max_attempts'],
			'window_seconds' => $settings['rate_limiter']['window_seconds'],
			'settings'       => array(
				'transient' => array(),
				'database'  => array( 'table_name' => 'autoship_rate_limits' ),
				'wp_cache'  => array( 'group' => 'autoship_ratelimit' ),
				'file'      => array( 'directory' => '' ),
			),
		);
		update_option( 'autoship_quicklinks_rate_limiter', $rate_limiter_config );

		// Update scanner config.
		$scanner_config = array(
			'enabled'              => $settings['scanner']['enabled'],
			'block_actions'        => array( 2 ), // Only Process Now (action_type = 2).
			'behavioral_detection' => array(
				'enabled'                 => $settings['scanner']['behavioral_detection'],
				'require_accept_language' => $settings['scanner']['require_accept_language'],
				'require_html_accept'     => $settings['scanner']['require_html_accept'],
				'min_suspicious_count'    => $settings['scanner']['min_suspicious_count'],
			),
		);
		update_option( 'autoship_quicklinks_scanner_config', $scanner_config );
	}

	/**
	 * AJAX handler for flushing rewrite rules.
	 *
	 * @return void
	 */
	public function ajax_flush_rewrite_rules(): void {
		// Verify nonce.
		if ( ! check_ajax_referer( self::ACTION_NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'autoship' ) ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'autoship' ) ) );
		}

		flush_rewrite_rules();

		wp_send_json_success( array( 'message' => __( 'Rewrite rules flushed successfully.', 'autoship' ) ) );
	}

	/**
	 * AJAX handler for running cleanup.
	 *
	 * @return void
	 */
	public function ajax_run_cleanup(): void {
		// Verify nonce.
		if ( ! check_ajax_referer( self::ACTION_NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'autoship' ) ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'autoship' ) ) );
		}

		try {
			$container            = Plugin::get_service_container();
			$confirmation_service = $container->get( QuickLinkConfirmationService::class );

			// Get retention days from settings.
			$settings       = $this->get_settings();
			$retention_days = $settings['maintenance']['confirmation_retention_days'];

			// Run cleanup.
			$deleted = $confirmation_service->cleanup( $retention_days );

			// Update last cleanup time.
			update_option( ConfirmationCleanupScheduler::LAST_CLEANUP_OPTION, current_time( 'mysql', true ) );

			wp_send_json_success(
				array(
					// translators: %d: number of deleted records.
					'message' => sprintf( __( 'Cleanup completed. %d expired records deleted.', 'autoship' ), $deleted ),
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for clearing rate limits.
	 *
	 * @return void
	 */
	public function ajax_clear_rate_limits(): void {
		// Verify nonce.
		if ( ! check_ajax_referer( self::ACTION_NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'autoship' ) ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'autoship' ) ) );
		}

		global $wpdb;

		$settings = $this->get_settings();
		$strategy = $settings['rate_limiter']['strategy'];

		$cleared = 0;

		switch ( $strategy ) {
			case 'transient':
				// Delete all quicklinks rate limit transients (prefix: ql_rate_).
				$cleared = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ql_rate_%' OR option_name LIKE '_transient_timeout_ql_rate_%'"
				);
				break;

			case 'database':
				// Truncate rate limits table.
				$table_name = $wpdb->prefix . 'autoship_rate_limits';
				$wpdb->query( "TRUNCATE TABLE {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$cleared = 1;
				break;

			case 'wp_cache':
				// Clear the cache group.
				wp_cache_flush_group( 'autoship_ratelimit' );
				$cleared = 1;
				break;

			case 'file':
				// Clear file storage directory.
				$upload_dir     = wp_upload_dir();
				$rate_limit_dir = trailingslashit( $upload_dir['basedir'] ) . 'autoship-ratelimits/';
				if ( is_dir( $rate_limit_dir ) ) {
					$this->clear_directory( $rate_limit_dir );
					$cleared = 1;
				}
				break;
		}

		wp_send_json_success(
			array(
				'message' => __( 'Rate limit data cleared successfully.', 'autoship' ),
			)
		);
	}

	/**
	 * Clear all files in a directory.
	 *
	 * @param string $dir The directory path.
	 *
	 * @return void
	 */
	private function clear_directory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$objects = scandir( $dir );
		if ( false === $objects ) {
			return;
		}

		foreach ( $objects as $object ) {
			if ( '.' === $object || '..' === $object ) {
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $object;
			if ( is_file( $path ) ) {
				unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}
	}
}
