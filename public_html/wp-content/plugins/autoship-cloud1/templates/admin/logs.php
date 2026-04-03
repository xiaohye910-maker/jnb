<?php
/**
 * This template is used to display the Autoship Cloud logs in the admin area.
 *
 * @package Autoship
 * @since 1.0.0
 */

?>

<?php
do_action( 'autoship_before_autoship_admin_logs' );

$files               = autoship_get_log_files();
$file_count          = count( $files );
$first_download_link = '';

$log_files = array();
foreach ( $files as $name => $time ) {
	$download_link = autoship_get_log_file_download_link( $name );
	$display_name  = apply_filters( 'autoship_log_file_download_select_display_name', gmdate( 'l\, F dS Y', $time ), $time, $name );

	$log_files[ $download_link ] = $display_name;

	if ( empty( $first_download_link ) ) {
		$first_download_link = $download_link;
	}
}

?>
	<h2><?php echo esc_html( __( 'Autoship Cloud Logs', 'autoship' ) ); ?></h2>

	<p class="autoship-logs-description"><?php echo esc_html( __( 'The Autoship Cloud Logging system is useful for troubleshooting issues with your Autoship Cloud and QPilot Integration. Entries are added to daily log files and the latest log files are retained.', 'autoship' ) ); ?></p>

	<div class="autoship-bulk-action">
		<div class="options_group autoship-logs">
			<div class="autoship-logs-download">
				<h3><?php echo esc_html( __( 'Autoship Log File Download', 'autoship' ) ); ?></h3>
				<?php if ( empty( $files ) ) : ?>
					<p><i><?php echo esc_html( __( 'There are currently no log files.', 'autoship' ) ); ?></i></p>
				<?php else : ?>
					<p><?php echo wp_kses_post( __( 'Select a log file in the drop down below and then click the <strong>Download File</strong> button to download the selected file.', 'autoship' ) ); ?></p>
					<div class="options_group options-form-group log-files">
						<div class="option-form-row auto-flex-row">
							<div class="option-form-group auto-flex-col">
								<select id="_autoship_log_file_select" class="option-form-control" name="_autoship_log_file_select">
									<?php foreach ( $log_files as $download_link => $display_name ) : ?>
										<option value="<?php echo esc_url( $download_link ); ?>"><?php echo esc_html( $display_name ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="auto-flex-col-auto">
								<a href="<?php echo esc_url( $first_download_link ); ?>" class="btn-primary autoship-action autoship-ajax-button flex-btn no-label download">
									<span><?php echo esc_html( __( 'Download File', 'autoship' ) ); ?></span>
								</a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php

do_action( 'autoship_after_autoship_admin_logs' );
