<?php
/**
 * The autoship admin utilities page.
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;
use Autoship\Modules\Synchronizers\Products\ProductSynchronizer;

do_action( 'autoship_before_autoship_admin_utilities' );

?>

<?php
$query_ids              = array();
$query_ids['simple']    = autoship_batch_query_product_ids( 'simple' );
$query_ids['variable']  = autoship_batch_query_product_ids( 'variable' );
$query_ids['variation'] = autoship_batch_query_product_ids( 'variation' );

$query_ids['simple_variation']          = array_merge( $query_ids['simple'], $query_ids['variation'] );
$query_ids['simple_variable']           = array_merge( $query_ids['simple'], $query_ids['variable'] );
$query_ids['simple_variable_variation'] = array_merge( $query_ids['simple'], $query_ids['variable'], $query_ids['variation'] );

// Depending on Global get active or all are active.
$query_active_ids = array(
	'simple'           => array(),
	'variation'        => array(),
	'simple_variation' => array(),
);

if ( ! autoship_global_sync_active_enabled() ) {
	$query_active_ids['simple']                    = autoship_batch_query_active_product_ids( 'simple' );
	$query_active_ids['variable']                  = autoship_batch_query_active_product_ids( 'variable' );
	$query_active_ids['variation']                 = autoship_batch_query_active_product_ids( 'variation' );
	$query_active_ids['simple_variation']          = array_merge( $query_active_ids['simple'], $query_active_ids['variation'] );
	$query_active_ids['simple_variable']           = array_merge( $query_active_ids['simple'], $query_active_ids['variable'] );
	$query_active_ids['simple_variable_variation'] = array_merge( $query_active_ids['simple'], $query_active_ids['variable'], $query_active_ids['variation'] );
}

$notice = array();
// translators: %d: number of products.
$notice['simple_variation'] = count( $query_ids['simple_variation'] ) ? __( 'A total of a %d Products and Product Variations can be processed.', 'autoship' ) : __( 'There are no available products to process.', 'autoship' );

// translators: %d: number of products.
$notice['simple_variable'] = count( $query_ids['simple_variable_variation'] ) ? __( 'A total of a %d Simple and Variable Products can be processed.', 'autoship' ) : __( 'There are no available products to process.', 'autoship' );

// translators: %d: number of products.
$notice['simple_variable_variation'] = count( $query_ids['simple_variable_variation'] ) ? __( 'A total of a %d Simple Products, Variable Products, and Product Variations can be processed.', 'autoship' ) : __( 'There are no available products to process.', 'autoship' );

$active_notice = $notice;
if ( ! autoship_global_sync_active_enabled() ) {
	// translators: %d: number of products.
	$active_notice['simple_variation'] = count( $query_active_ids['simple_variation'] ) ? __( 'A total of a %d Products and Product Variations can be processed.', 'autoship' ) : __( 'There are no available products to process.', 'autoship' );
}
?>

<div class="asc-settings-columns">

<!-- ====================================================================
     ROW 1, LEFT: Update Product Synchronization
     ==================================================================== -->
	<div class="autoship-bulk-action" id="autoship-bulk-activate-product-sync">

	<h2><i class="pi pi-sync"></i> <?php echo esc_html( __( 'Update Product Synchronization', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Control which WooCommerce products are synchronized with your connected QPilot Site. Activate or deactivate all products in bulk.', 'autoship' ) ); ?></p>
	
	<h4 class="autoship-bulk-notice"><?php wp_kses_post( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<div class="asc-form-group" style="margin-bottom: 0; padding-bottom: 0;">
		<div class="asc-radio-group">
			<label class="asc-radio-option batch-total-toggle autoship_trigger" for="yes-batch-enable-sync" data-show-target="#autoship-include-availability-sync" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>">
				<input id="yes-batch-enable-sync" type="radio" name="enable_active_sync_option" value="yes" checked />
				<span><?php echo esc_html( __( 'Activate All Products', 'autoship' ) ); ?></span>
			</label>
			<label class="asc-radio-option batch-total-toggle autoship_trigger" for="no-batch-enable-sync" data-hide-target="#autoship-include-availability-sync" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>">
				<input id="no-batch-enable-sync" type="radio" name="enable_active_sync_option" value="no"/>
				<span><?php echo esc_html( __( 'Deactivate All Products', 'autoship' ) ); ?></span>
			</label>
		</div>
	</div>

	<div class="asc-form-group" id="autoship-include-availability-sync" style="margin-bottom: 0;">
		<label for="autoship_include_availability_on_sync" style="display: flex; align-items: flex-start; gap: 0.5rem; font-weight: 400;">
			<input type="checkbox"
			id="autoship_include_availability_on_sync"
			name="autoship_include_availability_on_sync"
			value="yes"
			autocomplete="false" checked style="margin-top: 0.2rem;" />
			<?php echo esc_html( __( 'Enable the Add to Scheduled Order and Process on Scheduled Orders options for all products synchronized with your site.', 'autoship' ) ); ?>
		</label>
	</div>

	<div class="asc-form-group">
		<label class="asc-form-label" for="batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</div>

	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Products', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo count( $query_ids['simple_variable_variation'] ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_active_sync">

	</div>

<!-- ====================================================================
     ROW 1, RIGHT: Bulk Enable Autoship Options
     ==================================================================== -->
<div class="autoship-bulk-action" id="autoship-bulk-enable-autoship">

	<h2><i class="pi pi-check-circle"></i> <?php echo esc_html( __( 'Bulk Enable WooCommerce Products to Display Autoship Options', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Show or hide Autoship options on all WooCommerce product pages in bulk.', 'autoship' ) ); ?>
	<?php echo wp_kses_post( __( '<b>Important:</b> Product availability must also be enabled in Autoship Cloud.', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/441-product-availability-and-stock-status" target="_blank"><?php echo esc_html( __( 'Learn more', 'autoship' ) ); ?></a>.</p>

	<?php

	$ids  = autoship_global_sync_active_enabled() ? $query_ids['simple_variable_variation'] : $query_active_ids['simple_variable_variation'];
	$text = autoship_global_sync_active_enabled() ? $notice['simple_variable_variation'] : $active_notice['simple_variable_variation'];

	?>


	<div class="asc-form-group" style="margin-bottom: 0; padding-bottom: 0;">
		<div class="asc-radio-group">
			<label class="asc-radio-option batch-total-toggle" for="yes-batch-enable" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>">
				<input id="yes-batch-enable" type="radio" name="enable_autoship_option" value="yes" checked="checked"/>
				<span><?php echo esc_html( __( 'Enable Autoship', 'autoship' ) ); ?></span>
			</label>
			<label class="asc-radio-option batch-total-toggle" for="no-batch-enable" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable'] ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $notice['simple_variable'], count( $query_ids['simple_variable'] ) ) ); ?>">
				<input id="no-batch-enable" type="radio" name="enable_autoship_option" value="no"/>
				<span><?php echo esc_html( __( 'Disable Autoship', 'autoship' ) ); ?></span>
			</label>
		</div>
	</div>

    <h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $text, count( $ids ) ) ); ?></h4>
    <h5 class="autoship-bulk-subnotice"></h5>

    <div class="asc-form-group" style="margin-top:1.5rem;">
		<label class="asc-form-label" for="batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</div>

	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Products', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo count( $query_ids['simple_variable'] ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_enable_autoship">

</div>

<!-- ====================================================================
     ROW 2, LEFT: Bulk Update Checkout Price
     ==================================================================== -->
<div class="autoship-bulk-action" id="autoship-bulk-checkout-discount">

	<h2><i class="pi pi-dollar"></i> <?php echo esc_html( __( 'Bulk Update Autoship Checkout Price', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Set a percentage discount on the regular or sale price to calculate the Autoship Checkout Price for all products. Enter the discount as a decimal (e.g. 0.10 for 10%).', 'autoship' ) ); ?></p>

	<?php $ids = autoship_global_sync_active_enabled() ? $query_ids['simple_variation'] : $query_active_ids['simple_variation']; ?>


	<p class="help"><?php echo esc_html( __( 'Select which price to use when calculating the checkout price.', 'autoship' ) ); ?></p>

	<p class="form-field inline-field">
		<label for="checkout_regular_base_price"><input id="checkout_regular_base_price" type="radio" name="base_price" value="regular" checked="checked"/>
		<?php echo esc_html( __( 'Regular Price', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label for="checkout_sale_base_price"><input id="checkout_sale_base_price" type="radio" name="base_price" value="sale"/>
		<?php echo esc_html( __( 'Sale Price', 'autoship' ) ); ?></label>
	</p>

    <h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $notice['simple_variation'], count( $ids ) ) ); ?></h4>
    <h5 class="autoship-bulk-subnotice"></h5>


    <div class="asc-form-row" style="margin-top: 1.5rem;">
		<div class="asc-form-field">
			<label for="checkout_pct"><?php echo esc_html( __( 'Percent Discount', 'autoship' ) ); ?></label>
			<input type="number" class="small-text" name="checkout_pct" value=".1" placeholder=".10" step=".01" min="0"/>
		</div>
		<div class="asc-form-field">
			<label for="batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
			<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
		</div>
	</div>

	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Products', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo count( $ids ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_checkout_price">

</div>

<!-- ====================================================================
     ROW 2, RIGHT: Bulk Update Recurring Price
     ==================================================================== -->
<div class="autoship-bulk-action" id="autoship-bulk-recurring-discount">

	<h2><i class="pi pi-replay"></i> <?php echo esc_html( __( 'Bulk Update Autoship Recurring Price', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Set a percentage discount on the regular or sale price to calculate the Autoship Recurring Price for all products. Enter the discount as a decimal (e.g. 0.10 for 10%).', 'autoship' ) ); ?></p>

	<?php $ids = autoship_global_sync_active_enabled() ? $query_ids['simple_variation'] : $query_active_ids['simple_variation']; ?>

	<p class="help"><?php echo esc_html( __( 'Select which price to use when calculating the recurring price.' ) ); ?></p>

	<p class="form-field inline-field">
		<label for="recurring_regular_base_price"><input id="recurring_regular_base_price" type="radio" name="base_recurring_price" value="regular" checked="checked"/>
		<?php echo esc_html( __( 'Regular Price', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label for="recurring_sale_base_price"><input id="recurring_sale_base_price" type="radio" name="base_recurring_price" value="sale"/>
		<?php echo esc_html( __( 'Sale Price', 'autoship' ) ); ?></label>
	</p>


    <h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $notice['simple_variation'], count( $ids ) ) ); ?></h4>
    <h5 class="autoship-bulk-subnotice"></h5>


	<div class="asc-form-row" style="margin-top: 1rem;">
		<div class="asc-form-field">
			<label for="recurring_pct"><?php echo esc_html( __( 'Percent Discount', 'autoship' ) ); ?></label>
			<input type="number" class="small-text" name="recurring_pct" value=".1" placeholder=".10" step=".01" min="0"/>
		</div>
		<div class="asc-form-field">
			<label for="batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
			<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
		</div>
	</div>

	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Products', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo count( $ids ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_recurring_price">

</div>

<?php do_action( 'autoship_utilities_bulk_frequencies_section' ); ?>

<!-- ====================================================================
     ROW 3, RIGHT: Bulk Enable Product Availability
     ==================================================================== -->
<div class="autoship-bulk-action" id="autoship-bulk-enable-availability">

	<h2><i class="pi pi-box"></i> <?php echo esc_html( __( 'Bulk Enable Product Availability', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Enable or disable the Add to Scheduled Order and Process on Scheduled Orders options for all products in bulk.', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/441-product-availability-and-stock-status" target="_blank"><?php echo esc_html( __( 'Learn more', 'autoship' ) ); ?></a>.</p>


	<?php
	$ids  = autoship_global_sync_active_enabled() ? $query_ids['simple_variation'] : $query_active_ids['simple_variation'];
	$text = autoship_global_sync_active_enabled() ? $notice['simple_variation'] : $active_notice['simple_variation'];
	?>


	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="yes-batch-enable-avail" data-adjust-batch-total="<?php echo count( $ids ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $text, count( $ids ) ) ); ?>"><input id="yes-batch-enable-avail" type="radio" name="enable_availability_option" value="yes" checked="checked"/>
		<?php echo esc_html( __( 'Enable Availability', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="no-batch-enable-avail" data-adjust-batch-total="<?php echo count( $ids ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $text, count( $ids ) ) ); ?>"><input id="no-batch-enable-avail" type="radio" name="enable_availability_option" value="no"/>
		<?php echo esc_html( __( 'Disable Availability', 'autoship' ) ); ?></label>
	</p>

    <h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $text, count( $ids ) ) ); ?></h4>
    <h5 class="autoship-bulk-subnotice"></h5>


    <div class="asc-form-group">
		<label class="asc-form-label" for="batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</div>

	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Products', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo count( $ids ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_enable_availability">

</div>

<!-- ====================================================================
     ROW 4, LEFT: Bulk Update Customer Metrics
     ==================================================================== -->
<div class="autoship-bulk-action" id="autoship-bulk-update-customer-metrics">
	<h2><i class="pi pi-users"></i> <?php echo esc_html( __( 'Bulk Update Customer Metrics', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Import subscription customer metrics from QPilot Cloud into your WordPress database.', 'autoship' ) ); ?></p>
	

	<?php
	// Retrieve the total customer in API count.
	$total = autoship_get_total_customers_cached();

	// translators: %d is the number of customers.
	$text = $total ? __( 'A total of a %d Customers can be processed.', 'autoship' ) : __( 'There are no available customers to process.', 'autoship' );
	?>

	<h4 class="autoship-bulk-notice"><?php echo esc_html( sprintf( $text, $total ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<div class="asc-form-group">
		<label class="asc-form-label" for="batch_size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</div>

	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Customers', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo esc_attr( $total ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_customer_metrics">

</div>

<!-- ====================================================================
     ROW 4, RIGHT: Automatic Product Sync Utility
     ==================================================================== -->
<?php
	$container      = Plugin::get_service_container();
	$synchronizer   = $container->get( ProductSynchronizer::class );
	$has_connection = $synchronizer->has_connection();
?>
<?php if ( Plugin::get_service_container()->get( FeatureManagerInterface::class )->is_enabled( 'product_sync' ) && $has_connection ) : ?>
	<?php

	$pending_sync_products         = $synchronizer->get_number_of_remaining_products();
	$product_count                 = $synchronizer->get_number_autoship_enabled_products();
	$state                         = $synchronizer->get_last_sync_status();
	$has_last_sync                 = $synchronizer->has_last_sync();
	$last_sync_timestamp           = gmdate( 'Y-m-d H:i:s', $state['timestamp'] );
	$last_sync_execution           = $state['processing_time'];
	$last_sync_products            = $state['actual_size'];
	$sync_settings_batch_size      = $synchronizer->get_batch_size();
	$sync_settings_interval_time   = $synchronizer->get_interval();
	$sync_settings_service_enabled = $synchronizer->is_enabled();
	$sync_settings_service_logs    = $synchronizer->is_logging_enabled();
	$sync_remaining_hours          = $synchronizer->get_estimated_remaining_hours();
	$suggested                     = $synchronizer->get_suggested_batch_size();
	?>
<div class="autoship-bulk-action" id="autoship-product-sync-settings">
	<h2>
		<i class="pi pi-cog"></i> <?php echo esc_html( __( 'Automatic Product Sync Utility', 'autoship' ) ); ?>
		<?php if ( $sync_settings_service_enabled ) : ?>
			<span class="asc-badge asc-badge-success"><?php echo esc_html( __( 'ENABLED', 'autoship' ) ); ?></span>
		<?php else : ?>
			<span class="asc-badge asc-badge-danger"><?php echo esc_html( __( 'DISABLED', 'autoship' ) ); ?></span>
		<?php endif; ?>
	</h2>
	<p>
		<?php echo esc_html( __( 'Automatically sync product data with Autoship Cloud using the WooCommerce Action Scheduler. Configure batch size and run interval to balance performance and server load.', 'autoship' ) ); ?>
	</p>

	<?php if ( $sync_settings_service_enabled ) : ?>

	<h4 class="autoship-bulk-notice"><?php echo esc_html( __( 'There are', 'autoship' ) ); ?> <strong><?php echo esc_html( $pending_sync_products ); ?></strong> <?php echo esc_html( __( 'products pending to sync for today.', 'autoship' ) ); ?></h4>

		<?php if ( $has_last_sync ) : ?>
			<h5 class="autoship-bulk-subnotice">
				<?php echo esc_html( __( 'Last synchronization was executed on', 'autoship' ) ); ?> <strong><?php echo esc_html( $last_sync_timestamp ); ?></strong> <?php echo esc_html( __( 'and took', 'autoship' ) ); ?> <?php echo esc_html( round( $last_sync_execution, 2 ) ); ?> <?php echo esc_html( __( 'seconds to process', 'autoship' ) ); ?> <?php echo esc_html( $last_sync_products ); ?> <?php echo esc_html( __( 'products.', 'autoship' ) ); ?>
			</h5>
		<?php endif; ?>

	<div class="asc-form-row">
		<div class="asc-form-field">
			<label for="product-sync-batch-size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?>
				<?php
				// translators: %1$d is the number of the recommended batch size, %2$d is the number of products.
				$suggested_batch_size_text = sprintf( __( 'The suggested batch size is %1$d based on your %2$d products using an interval of 5 minutes.', 'autoship' ), $suggested, $product_count );
				?>
				<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( $suggested_batch_size_text ); ?>"></span>
			</label>
			<input name="product-sync-batch-size" id="product-sync-batch-size" type="number" class="small-text" value="<?php echo esc_attr( $sync_settings_batch_size ); ?>" placeholder="<?php echo esc_attr( $suggested ); ?>" step="1" min="10" max="500"/>
		</div>
		<div class="asc-form-field">
			<label for="product-sync-interval"><?php echo esc_html( __( 'Run Every', 'autoship' ) ); ?></label>
			<select name="product-sync-interval" id="product-sync-interval">
			<option value="300"   <?php echo esc_attr( 300 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>5 <?php echo esc_html( __( 'minutes', 'autoship' ) ); ?></option>
			<option value="600"   <?php echo esc_attr( 600 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>10 <?php echo esc_html( __( 'minutes', 'autoship' ) ); ?></option>
			<option value="900"   <?php echo esc_attr( 900 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>15 <?php echo esc_html( __( 'minutes', 'autoship' ) ); ?></option>
			<option value="1200"  <?php echo esc_attr( 1200 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>20 <?php echo esc_html( __( 'minutes', 'autoship' ) ); ?></option>
			<option value="1800"  <?php echo esc_attr( 1800 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>30 <?php echo esc_html( __( 'minutes', 'autoship' ) ); ?></option>
			<option value="3600"  <?php echo esc_attr( 3600 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>1 <?php echo esc_html( __( 'hour', 'autoship' ) ); ?></option>
			<option value="7200"  <?php echo esc_attr( 7200 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>2 <?php echo esc_html( __( 'hours', 'autoship' ) ); ?></option>
			<option value="10800" <?php echo esc_attr( 10800 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>3 <?php echo esc_html( __( 'hours', 'autoship' ) ); ?></option>
			<option value="14400" <?php echo esc_attr( 14400 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>4 <?php echo esc_html( __( 'hours', 'autoship' ) ); ?></option>
			<option value="21600" <?php echo esc_attr( 21600 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>6 <?php echo esc_html( __( 'hours', 'autoship' ) ); ?></option>
			<option value="28800" <?php echo esc_attr( 28800 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>8 <?php echo esc_html( __( 'hours', 'autoship' ) ); ?></option>
			<option value="43200" <?php echo esc_attr( 43200 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>12 <?php echo esc_html( __( 'hours', 'autoship' ) ); ?></option>
			<option value="86400" <?php echo esc_attr( 86400 === $sync_settings_interval_time ? 'selected="selected"' : '' ); ?>>1 <?php echo esc_html( __( 'daily', 'autoship' ) ); ?></option>
			</select>
		</div>
	</div>

	<div class="asc-checkbox">
		<input type="checkbox" name="product-sync-logging" id="product-sync-logging" value="1" <?php echo esc_attr( $sync_settings_service_logs ? 'checked="checked"' : '' ); ?> />
		<label for="product-sync-logging" class="asc-checkbox-label">
			<?php echo esc_html( __( 'Enable synchronization logs to track activity and errors.', 'autoship' ) ); ?>
			<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( __( 'If the logs are enabled, you can download them from Autoship > Settings > Logs section.', 'autoship' ) ); ?>"></span>
		</label>
	</div>

		<?php if ( $sync_remaining_hours > 24 ) : ?>
			<p style="color: red;"><strong><?php echo esc_html( __( 'Warning:', 'autoship' ) ); ?></strong> <?php echo esc_html( __( 'Using this settings will take more than 1 day to sync the remaining products.', 'autoship' ) ); ?></p>
		<?php endif; ?>

	<div class="autoship-product-sync-error-container"></div>

	<div class="asc-button-group" style="margin-top: 1.5rem;">
		<button type="button" id="autoship-product-sync-updater" class="button-primary"><span><?php echo esc_html( __( 'Update Settings', 'autoship' ) ); ?></span></button>
		<button type="button" id="autoship-product-sync-disabler" class="button-secondary"><span><?php echo esc_html( __( 'Disable Product Sync', 'autoship' ) ); ?></span></button>
	</div>

	<div id="autoship-disable-sync-dialog" style="display:none;">
		<p>
			<?php echo esc_html( __( 'Disabling this utility will prevent data from WooCommerce Products being sent to Autoship Cloud unless you manually sync the data for an individual product or perform a bulk update.', 'autoship' ) ); ?>
		</p>
		<p><strong><?php echo esc_html( __( 'Do you wish to continue?' ) ); ?></strong></p>
	</div>

	<?php else : ?>
		<div class="autoship-product-sync-error-container"></div>
		<div class="asc-button-group" style="margin-top: 1rem;">
			<button type="button" id="autoship-product-sync-enabler" class="button-primary"><span><?php echo esc_html( __( 'Enable Product Sync', 'autoship' ) ); ?></span></button>
		</div>
	<?php endif; ?>
</div>

<?php endif; ?>

</div><!-- .asc-settings-columns -->

<?php do_action( 'autoship_after_autoship_admin_utilities', $query_ids, $notice ); ?>
