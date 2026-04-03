<?php
/**
 * Quicklinks Settings Admin Template.
 *
 * @package Autoship
 * @since   3.2.0
 */

defined( 'ABSPATH' ) || exit;

// Settings are passed from the controller.
$enabled      = isset( $settings['enabled'] ) ? $settings['enabled'] : true;
$rate_limiter = isset( $settings['rate_limiter'] ) ? $settings['rate_limiter'] : array();
$scanner      = isset( $settings['scanner'] ) ? $settings['scanner'] : array();
$maintenance  = isset( $settings['maintenance'] ) ? $settings['maintenance'] : array();

// Rate limiter defaults.
$rl_enabled        = isset( $rate_limiter['enabled'] ) ? $rate_limiter['enabled'] : true;
$rl_strategy       = isset( $rate_limiter['strategy'] ) ? $rate_limiter['strategy'] : 'transient';
$rl_max_attempts   = isset( $rate_limiter['max_attempts'] ) ? $rate_limiter['max_attempts'] : 5;
$rl_window_seconds = isset( $rate_limiter['window_seconds'] ) ? $rate_limiter['window_seconds'] : 60;

// Scanner defaults.
$sc_enabled                 = isset( $scanner['enabled'] ) ? $scanner['enabled'] : true;
$sc_behavioral_detection    = isset( $scanner['behavioral_detection'] ) ? $scanner['behavioral_detection'] : true;
$sc_require_accept_language = isset( $scanner['require_accept_language'] ) ? $scanner['require_accept_language'] : true;
$sc_require_html_accept     = isset( $scanner['require_html_accept'] ) ? $scanner['require_html_accept'] : true;
$sc_min_suspicious_count    = isset( $scanner['min_suspicious_count'] ) ? $scanner['min_suspicious_count'] : 2;

// Maintenance defaults.
$mt_confirmation_retention = isset( $maintenance['confirmation_retention_days'] ) ? $maintenance['confirmation_retention_days'] : 90;
$mt_audit_retention        = isset( $maintenance['audit_retention_days'] ) ? $maintenance['audit_retention_days'] : 90;

// Check if the persistent object cache is available.
$has_object_cache = wp_using_ext_object_cache();

// Last/Next cleanup times.
$last_cleanup_display = $last_cleanup ? date_i18n( get_option( 'date_format' ) . ' settings.php' . get_option( 'time_format' ), strtotime( $last_cleanup ) ) : __( 'Never', 'autoship' );
$next_cleanup_display = $next_cleanup ? date_i18n( get_option( 'date_format' ) . ' settings.php' . get_option( 'time_format' ), $next_cleanup ) : __( 'Not scheduled', 'autoship' );

do_action( 'autoship_before_quicklinks_settings' );
?>

<div id="autoship-quicklinks-settings-form">

	<div class="asc-settings-columns">

		<!-- Feature Toggle -->
		<div class="asc-settings-column">
			<h3><i class="pi pi-power-off"></i> <?php echo esc_html__( 'Feature Toggle', 'autoship' ); ?></h3>
			<p class="asc-card-description"><?php echo esc_html__( 'Enable or disable the quick action links feature for your store.', 'autoship' ); ?></p>

			<div class="asc-form-group">
				<input type="checkbox"
					id="quicklinks_enabled"
					name="enabled"
					value="yes"
					<?php checked( $enabled, true ); ?> />
				<label for="quicklinks_enabled"><?php echo esc_html__( 'Enable Quick Action Links', 'autoship' ); ?></label>
				<p class="asc-card-description"><?php echo esc_html__( 'Allow customers to manage subscriptions via email links.', 'autoship' ); ?></p>
			</div>

			<div class="asc-nested-options">
				<h4 class="asc-subsection-title"><i class="pi pi-bolt" style="color: var(--qmc-primary, #3b82f6); margin-right: 0.375rem;"></i><?php echo esc_html__( 'Quick Action Links: Benefits', 'autoship' ); ?></h4>
				<p style="font-size: 0.875rem; color: var(--qmc-text-primary, #1f2937); margin: 0 0 0.75rem 0; line-height: 1.5;">
					<?php echo esc_html__( 'Quick Action Links let your customers take common subscription actions directly from email links.', 'autoship' ); ?>
				</p>
				<ul style="margin: 0 0 0 1.25rem; padding: 0; font-size: 0.875rem; color: var(--qmc-text-primary, #1f2937); line-height: 1.8;">
					<li><?php echo esc_html__( 'One-click actions from email notifications (skip, pause, reactivate, process now)', 'autoship' ); ?></li>
					<li><?php echo esc_html__( 'Secure token-based links with built-in expiration', 'autoship' ); ?></li>
					<li><?php echo esc_html__( 'Confirmation step prevents accidental changes', 'autoship' ); ?></li>
					<li><?php echo esc_html__( 'Built-in bot detection and rate limiting for protection', 'autoship' ); ?></li>
				</ul>
			</div>
		</div>

		<!-- Rate Limiting -->
		<div class="asc-settings-column">
			<h3><i class="pi pi-shield"></i> <?php echo esc_html__( 'Rate Limiting', 'autoship' ); ?></h3>
			<p class="asc-card-description"><?php echo esc_html__( 'Limit the number of requests per IP address to prevent abuse.', 'autoship' ); ?></p>

			<div class="asc-form-group">
				<input type="checkbox"
					id="rate_limiter_enabled"
					name="rate_limiter_enabled"
					value="yes"
					<?php checked( $rl_enabled, true ); ?> />
				<label for="rate_limiter_enabled"><?php echo esc_html__( 'Enable Rate Limiting', 'autoship' ); ?></label>
			</div>

			<div class="asc-form-row">
				<div class="asc-form-field full-width">
					<label for="rate_limiter_strategy"><?php echo esc_html__( 'Storage Strategy', 'autoship' ); ?></label>
					<select name="rate_limiter_strategy" id="rate_limiter_strategy">
						<option value="transient" <?php selected( $rl_strategy, 'transient' ); ?>><?php echo esc_html__( 'Transient', 'autoship' ); ?></option>
						<option value="database" <?php selected( $rl_strategy, 'database' ); ?>><?php echo esc_html__( 'Database', 'autoship' ); ?></option>
						<option value="wp_cache" <?php selected( $rl_strategy, 'wp_cache' ); ?> <?php disabled( ! $has_object_cache ); ?>>
							<?php
							if ( $has_object_cache ) {
								echo esc_html__( 'WP Cache', 'autoship' );
							} else {
								echo esc_html__( 'WP Cache (not configured)', 'autoship' );
							}
							?>
						</option>
						<option value="file" <?php selected( $rl_strategy, 'file' ); ?>><?php echo esc_html__( 'File', 'autoship' ); ?></option>
					</select>
					<span class="asc-card-description"><?php echo esc_html__( 'This is where rate limit data is stored. Transient is recommended for most sites.', 'autoship' ); ?></span>
				</div>
			</div>

			<div class="asc-form-row">
				<div class="asc-form-field">
					<label for="rate_limiter_max_attempts"><?php echo esc_html__( 'Max Attempts', 'autoship' ); ?></label>
					<input type="number"
						id="rate_limiter_max_attempts"
						name="rate_limiter_max_attempts"
						value="<?php echo esc_attr( $rl_max_attempts ); ?>"
						min="1"
						max="100"
						step="1" />
					<span class="asc-card-description"><?php echo esc_html__( 'Requests per time window', 'autoship' ); ?></span>
				</div>
				<div class="asc-form-field">
					<label for="rate_limiter_window_seconds"><?php echo esc_html__( 'Time Window', 'autoship' ); ?></label>
					<input type="number"
						id="rate_limiter_window_seconds"
						name="rate_limiter_window_seconds"
						value="<?php echo esc_attr( $rl_window_seconds ); ?>"
						min="10"
						max="3600"
						step="1" />
					<span class="asc-card-description"><?php echo esc_html__( 'Duration in seconds', 'autoship' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Bot Detection -->
		<div class="asc-settings-column">
			<h3><i class="pi pi-eye"></i> <?php echo esc_html__( 'Bot Detection', 'autoship' ); ?></h3>
			<p class="asc-card-description"><?php echo esc_html__( 'Detect and block automated requests from email scanners and bots.', 'autoship' ); ?></p>

			<div class="asc-form-group">
				<input type="checkbox"
					id="scanner_enabled"
					name="scanner_enabled"
					value="yes"
					<?php checked( $sc_enabled, true ); ?> />
				<label for="scanner_enabled"><?php echo esc_html__( 'Enable Scanner Detection', 'autoship' ); ?></label><br/><br/>

				<input type="checkbox"
					id="scanner_behavioral_detection"
					name="scanner_behavioral_detection"
					value="yes"
					<?php checked( $sc_behavioral_detection, true ); ?> />
				<label for="scanner_behavioral_detection"><?php echo esc_html__( 'Enable Behavioral Detection', 'autoship' ); ?></label>
				<p class="asc-card-description"><?php echo esc_html__( 'Analyze request patterns to detect automated behavior.', 'autoship' ); ?></p>

				<input type="checkbox"
					id="scanner_require_accept_language"
					name="scanner_require_accept_language"
					value="yes"
					<?php checked( $sc_require_accept_language, true ); ?> />
				<label for="scanner_require_accept_language"><?php echo esc_html__( 'Require Accept-Language Header', 'autoship' ); ?></label>
				<p class="asc-card-description"><?php echo esc_html__( 'Flag requests without Accept-Language header as suspicious.', 'autoship' ); ?></p>

				<input type="checkbox"
					id="scanner_require_html_accept"
					name="scanner_require_html_accept"
					value="yes"
					<?php checked( $sc_require_html_accept, true ); ?> />
				<label for="scanner_require_html_accept"><?php echo esc_html__( 'Require HTML Accept Header', 'autoship' ); ?></label>
				<p class="asc-card-description"><?php echo esc_html__( 'Flag requests that do not accept HTML as suspicious.', 'autoship' ); ?></p>
			</div>

			<div class="asc-form-group">
				<label class="asc-form-label" for="scanner_min_suspicious_count"><?php echo esc_html__( 'Min Suspicious Signals', 'autoship' ); ?></label>
				<input type="number"
					id="scanner_min_suspicious_count"
					name="scanner_min_suspicious_count"
					value="<?php echo esc_attr( $sc_min_suspicious_count ); ?>"
					min="1"
					max="10"
					step="1" />
				<span class="asc-card-description"><?php echo esc_html__( 'Number of signals required to flag a request as a scanner.', 'autoship' ); ?></span>
			</div>
		</div>

		<!-- Maintenance -->
		<div class="asc-settings-column">
			<h3><i class="pi pi-cog"></i> <?php echo esc_html__( 'Maintenance', 'autoship' ); ?></h3>
			<p class="asc-card-description"><?php echo esc_html__( 'Configure data retention and manage scheduled cleanup tasks.', 'autoship' ); ?></p>

			<h4 class="asc-subsection-title"><i class="pi pi-database" style="color: var(--qmc-primary, #3b82f6); margin-right: 0.375rem;"></i><?php echo esc_html__( 'Data Retention', 'autoship' ); ?></h4>

			<div class="asc-form-row">
				<div class="asc-form-field">
					<label for="maintenance_confirmation_retention_days"><?php echo esc_html__( 'Confirmation Records', 'autoship' ); ?></label>
					<input type="number"
						id="maintenance_confirmation_retention_days"
						name="maintenance_confirmation_retention_days"
						value="<?php echo esc_attr( $mt_confirmation_retention ); ?>"
						min="1"
						max="365"
						step="1" />
					<span class="asc-card-description"><?php echo esc_html__( 'Retention period in days', 'autoship' ); ?></span>
				</div>
				<div class="asc-form-field">
					<label for="maintenance_audit_retention_days"><?php echo esc_html__( 'Audit Log Records', 'autoship' ); ?></label>
					<input type="number"
						id="maintenance_audit_retention_days"
						name="maintenance_audit_retention_days"
						value="<?php echo esc_attr( $mt_audit_retention ); ?>"
						min="1"
						max="365"
						step="1" />
					<span class="asc-card-description"><?php echo esc_html__( 'Retention period in days', 'autoship' ); ?></span>
				</div>
			</div>

			<h4 class="asc-subsection-title"><i class="pi pi-calendar" style="color: var(--qmc-primary, #3b82f6); margin-right: 0.375rem;"></i><?php echo esc_html__( 'Scheduled Tasks', 'autoship' ); ?></h4>

			<div class="asc-form-group" style="margin-bottom: 0.5rem;">
				<span class="asc-form-label"><?php echo esc_html__( 'Last Cleanup', 'autoship' ); ?></span>
				<code><?php echo esc_html( $last_cleanup_display ); ?></code>
			</div>
			<div class="asc-form-group">
				<span class="asc-form-label"><?php echo esc_html__( 'Next Cleanup', 'autoship' ); ?></span>
				<code><?php echo esc_html( $next_cleanup_display ); ?></code>
			</div>

			<h4 class="asc-subsection-title"><i class="pi pi-wrench" style="color: var(--qmc-primary, #3b82f6); margin-right: 0.375rem;"></i><?php echo esc_html__( 'Manual Actions', 'autoship' ); ?></h4>
			<p class="asc-card-description"><?php echo esc_html__( 'Perform maintenance tasks manually when needed.', 'autoship' ); ?></p>

			<div class="asc-button-group">
				<button type="button" class="button button-secondary" id="autoship-quicklinks-flush-rewrite">
					<i class="pi pi-refresh" style="margin-right: 0.5rem;"></i>
					<?php echo esc_html__( 'Flush Rewrite Rules', 'autoship' ); ?>
				</button>
				<button type="button" class="button button-secondary" id="autoship-quicklinks-run-cleanup">
					<i class="pi pi-trash" style="margin-right: 0.5rem;"></i>
					<?php echo esc_html__( 'Run Cleanup Now', 'autoship' ); ?>
				</button>
				<button type="button" class="button button-secondary" id="autoship-quicklinks-clear-rate-limits">
					<i class="pi pi-times-circle" style="margin-right: 0.5rem;"></i>
					<?php echo esc_html__( 'Clear Rate Limits', 'autoship' ); ?>
				</button>
			</div>

			<div id="autoship-quicklinks-action-feedback" style="margin-top: 1rem;"></div>
		</div>

	</div>

	<div class="asc-button-group" style="margin-top: 1.5rem;">
		<button type="button" class="button-primary" id="autoship-quicklinks-save-settings">
			<i class="pi pi-save" style="margin-right: 0.5rem;"></i>
			<?php echo esc_html__( 'Save Settings', 'autoship' ); ?>
		</button>
	</div>
	<div id="autoship-quicklinks-save-feedback" style="margin-top: 1rem;"></div>

</div>

<?php do_action( 'autoship_after_quicklinks_settings' ); ?>
