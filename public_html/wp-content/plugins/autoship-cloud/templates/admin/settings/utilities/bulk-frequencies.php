<?php
/**
 * Bulk Update Frequency Options section template.
 *
 * Variables provided by BulkFrequenciesController::render_section():
 *
 * @var int   $product_count  Number of products eligible for frequency updates.
 * @var array $frequency_types Available frequency types (key => label).
 * @var bool  $has_products   Whether there are eligible products.
 *
 * @package Autoship
 * @since 2.11.1
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="autoship-bulk-action" id="autoship-bulk-frequencies-autoship">
	<h2><i class="pi pi-clock"></i> <?php echo esc_html( __( 'Bulk Update Frequency Options', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Update the frequency options displayed for all synced Autoship products. Products with individually overridden frequencies will not be affected.', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/376-change-default-frequency-options" target="_blank"><?php echo esc_html( __( 'Learn more', 'autoship' ) ); ?></a>.</p>

	<?php if ( ! $has_products ) : ?>

		<h4 class="autoship-bulk-notice"><?php echo esc_html( __( 'There are no available products to bulk update. Please activate the products first by enabling product sync and ensuring they are displayed in Autoship Cloud.', 'autoship' ) ); ?></h4>
		<h5 class="autoship-bulk-subnotice"></h5>

	<?php else : ?>
		<div id="frequency_update_options">
			<div class="frequency_option" id="frequency_option_1">
				<h3><?php echo esc_html( __( 'Frequency Option', 'autoship' ) ); ?> 1</h3>
				<div class="asc-form-row">
					<div class="asc-form-field">
						<label for="frequency_number_option_1"><?php echo esc_html( __( 'Frequency Type', 'autoship' ) ); ?></label>
						<select class="option-form-control" name="frequency_number_option_1">
							<option value=""><?php echo esc_html( __( '-- Select frequency type--', 'autoship' ) ); ?></option>
							<?php foreach ( $frequency_types as $ftype => $flabel ) : ?>
								<option value="<?php echo esc_attr( $ftype ); ?>"><?php echo esc_html( $flabel ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="asc-form-field">
						<label for="frequency_number_option_1"><?php echo esc_html( __( 'Frequency', 'autoship' ) ); ?></label>
						<input type="number" min="1" class="regular-text" name="frequency_number_option_1" step="1" placeholder="Enter a frequency number" pattern="\d*"/>
					</div>
				</div>
				<div class="form-field">
					<label for="frequency_display_name_option_1"><?php echo esc_html( __( 'Display Name', 'autoship' ) ); ?></label>
					<input type="text" class="regular-text" name="frequency_display_name_option_1" value="" placeholder="Optional display name" />
				</div>
			</div>
		</div>


		<h4 class="autoship-bulk-notice" id="autoship_bulk_frequency_options_notice">
			<?php
				/* translators: %s: number of products */
				echo esc_html( sprintf( __( 'There are %s products that can be set frequency options.', 'autoship' ), $product_count ) );
			?>
		</h4>
		<h5 class="autoship-bulk-subnotice" id="autoship_bulk_frequency_options_subnotice"></h5>

		<div id="frequency_update_settings">
			<div class="asc-form-group">
				<label class="asc-form-label" for="frequency_update_batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
				<input type="number" class="small-text" name="frequency_update_batch_size" id="frequency_update_batch_size" value="10" placeholder="10" step="1" min="1"/>
			</div>
		</div>


		<div style="display:none;" class="autoship-meter" id="autoship_bulk_frequency_options_progress_container">
			<span id="autoship_bulk_frequency_options_progress_bar" style="width:10%"></span>
		</div>

		<div class="asc-button-group" style="margin-top: 1.25rem;">
			<input type="hidden" name="frequency_update_products_total" id="frequency_update_products_total" value="<?php echo esc_attr( $product_count ); ?>">
			<input type="hidden" name="frequency_update_products_count" id="frequency_update_products_count" value="0">
			<input type="hidden" name="frequency_update_products_page"  id="frequency_update_products_page" value="1">

			<button type="button" class="button-primary button-start-bulk-frequency-update"><span><?php echo esc_html( __( 'Update Frequency Options', 'autoship' ) ); ?></span></button>

			<button type="button" class="button-primary cancel-button button-cancel-frequency-update" style="display:none;"><?php echo esc_html( __( 'Cancel', 'autoship' ) ); ?></button>

			<button type="button" class="button-secondary button-create-frequency"><span><?php echo esc_html( __( 'Add Another +', 'autoship' ) ); ?></span></button>
		</div>
	<?php endif; ?>
</div>
