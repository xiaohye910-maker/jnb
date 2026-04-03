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

<div class="asc-settings-column" >
	<h3><i class="pi pi-file"></i> <?php echo esc_html( __( 'File Download', 'autoship' ) ); ?></h3>
	<p class="asc-card-description"><?php echo esc_html( __( 'Download daily log files to troubleshoot issues with your Autoship Cloud and QPilot integration.', 'autoship' ) ); ?></p>

	<?php if ( empty( $files ) ) : ?>
		<div class="asc-alert asc-alert-info">
			<i class="pi pi-info-circle"></i>
			<div class="asc-alert-content">
				<?php echo esc_html( __( 'There are currently no log files available.', 'autoship' ) ); ?>
			</div>
		</div>
	<?php else : ?>
		<div class="asc-form-row">
			<div class="asc-form-field">
				<label for="_autoship_log_file_select"><?php echo esc_html( __( 'Select Log File', 'autoship' ) ); ?></label>
				<select id="_autoship_log_file_select" name="_autoship_log_file_select">
					<?php foreach ( $log_files as $download_link => $display_name ) : ?>
						<option value="<?php echo esc_url( $download_link ); ?>"><?php echo esc_html( $display_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="asc-button-group">
			<a href="<?php echo esc_url( $first_download_link ); ?>" class="button button-primary autoship-action download">
				<i class="pi pi-download" style="margin-right: 0.5rem;"></i>
				<span><?php echo esc_html( __( 'Download File', 'autoship' ) ); ?></span>
			</a>
		</div>
	<?php endif; ?>
</div>

<?php
do_action( 'autoship_after_autoship_admin_logs' );
