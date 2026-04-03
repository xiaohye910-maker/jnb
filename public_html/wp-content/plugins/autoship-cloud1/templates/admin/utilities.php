<?php
/**
 * The autoship admin utilities page.
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Core\FeatureManager;
use Autoship\Core\Plugin;
use Autoship\Modules\Synchronizers\Products\ProductSynchronizer;

do_action( 'autoship_before_autoship_admin_utilities' );

$upgrade            = isset( $_GET['autoship_activate_sync_upgrade'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$processing_version = autoship_get_saved_site_processing_version();
?>

<?php if ( ! $upgrade && isset( $_GET['autoship_activate_legacy_processing_upgrade'] ) && 'v3' !== $processing_version ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
	<div class="autoship-bulk-action upgrade-action" id="autoship-activate-legacy-processing-upgrade">
		<h2><?php echo esc_html( __( 'Update Required: Upgrade Existing Scheduled Orders to New Processing Date/Time Format', 'autoship' ) ); ?></h2>
		<p><?php echo wp_kses_post( __( 'Your new version of <i>Autoship Cloud powered by QPilot</i> requires an upgrade to your existing Scheduled Orders in order to continue order processing successfully.', 'autoship' ) ); ?></p>
		<p><?php echo esc_html( __( 'Using the “Upgrade Processing Version” will update the Date and Time format of your existing Scheduled Orders. This upgrade cannot be reversed and will change the processing engine that your connected QPilot Site uses moving forward.', 'autoship' ) ); ?></p>
		<p><?php echo wp_kses_post( __( '<strong>IMPORTANT</strong>: If you have made any customizations to how Next Occurrence Dates are generated for Scheduled Orders -  either through custom code, a custom extension plugin, or similar - please test this upgrade in a staging site environment <strong>before upgrading your production site.</strong>', 'autoship' ) ); ?></p>
		<hr/>
		<p class="form-field inline-field">
		<input type="checkbox"
		id="autoship_upgrade_site_processing_version"
		name="autoship_upgrade_site_processing_version"
		value="confirm"
		required
		autocomplete="false" />
		<label for="autoship_upgrade_site_processing_version" style="display:inline;"><?php echo esc_html( __( 'I have read the upgrade notice and understand that running the action to “Upgrade Processing Version” will change how Scheduled Orders are processed by QPilot.', 'autoship' ) ); ?></label>
		</p>
		<p><button type="submit" class="button-primary" name="upgrade_processing" value="upgrade_processing"><span><?php echo esc_html( __( 'Upgrade Processing Version', 'autoship' ) ); ?></span></button></p>
		<?php wp_nonce_field( 'autoship-activate-legacy-processing-upgrade', 'autoship-activate-legacy-processing-upgrade-nonce' ); ?>
		<input type="hidden" name="autoship-action" value="autoship_upgrade_site_processing_version">
	</div>
	<?php return; ?>
<?php endif; ?>

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

// If the global option is still set this is an upgrade so we need to show the reset option not individual option.
if ( autoship_global_sync_active_enabled() ) {

	$maybe_query_ids                              = array();
	$maybe_query_ids['simple']                    = autoship_batch_query_maybe_active_product_ids( 'simple' );
	$maybe_query_ids['variable']                  = autoship_batch_query_maybe_active_product_ids( 'variable' );
	$maybe_query_ids['variation']                 = autoship_batch_query_maybe_active_product_ids( 'variation' );
	$maybe_query_ids['simple_variable_variation'] = array_merge( $maybe_query_ids['simple'], $maybe_query_ids['variable'], $maybe_query_ids['variation'] );
	?>
	<div class="autoship-bulk-action upgrade-action" id="autoship-bulk-reset-activate-product-sync">

	<h2><?php echo esc_html( __( 'Update WooCommerce Product Synchronization', 'autoship' ) ); ?></h2>
	<p><?php echo wp_kses_post( __( '<i>Autoship Cloud powered by QPilot</i> now enables merchants to select which WooCommerce Products should be synchronized with your connected QPilot Site.<br/>Currently, all WooCommerce Products are synchronized with your QPilot Site: even those that have no settings enabled for Autoship.<br/>Running this update will ensure that only the WooCommerce Products that are setup for Autoship continue to synchronize with your QPilot Site.', 'autoship' ) ); ?></p>
	<p><strong><?php echo wp_kses_post( __( 'IMPORTANT: Please review before selecting your update action', 'autoship' ) ); ?></strong></p>
	<hr/>
	<div style="padding: 10px;"><p><?php echo wp_kses_post( __( '<strong>OPTION 1: Activate All Products Currently Enabled for Autoship:</strong><br/>Use the “Activate All Products” option to Activate all WooCommerce Products that currently have Autoship options enabled. These options include any products or variations that are enabled to: Display Schedule Options and/or Available to Add to Scheduled Orders or Process With Scheduled Orders.', 'autoship' ) ); ?></p></div>
	<div style="padding: 10px;"><p><?php echo wp_kses_post( __( '<strong>OPTION 2: Deactivate All Products:</strong><br/>Select the “Deactivate all Products” option to deactivate all WooCommerce Products. You should then <a href="https://support.autoship.cloud/article/448-7-enabling-products-for-autoship">select which products are Activated for Product Sync with QPilot</a>.', 'autoship' ) ); ?></p>
	<p>
	<?php
		// translators: %s: URL to WP-Admin Products page.
		echo wp_kses_post( sprintf( __( 'Once all products are deactivated, you can quickly select specific products to Activate by visiting the Edit Product screen in <a href="%s">WP-Admin > Products</a> for each product you want to synchronize and enabling the “Activate Product Sync” checkbox under the Autoship tab.', 'autoship' ), autoship_admin_products_page_url() ) );
	?>
	</p></div>
	<p><?php echo esc_html( __( 'The Batch Size for this update (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>
	<hr/>

	<h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $notice['simple_variable_variation'], count( $maybe_query_ids['simple_variable_variation'] ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="form-field inline-field ">
		<label for="batch_size"><?php echo esc_html( __( 'Batch Size:', 'autoship' ) ); ?></label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="no-batch-enable-reset-sync" data-adjust-batch-total="<?php echo count( $maybe_query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $notice['simple_variable_variation'], count( $maybe_query_ids['simple_variable_variation'] ) ) ); ?>"><input id="no-batch-enable-reset-sync" type="radio" name="enable_reset_active_sync_option" value="no" checked="checked"/>
		<?php echo esc_html( __( 'Activate All Products', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="yes-batch-enable-reset-sync" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>"><input id="yes-batch-enable-reset-sync" type="radio" name="enable_reset_active_sync_option" value="yes"/>
		<?php echo esc_html( __( 'Deactivate All Products', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field">

		<input type="checkbox"
		id="autoship_include_availability_on_sync"
		name="autoship_include_availability_on_sync"
		value="yes"
		autocomplete="false" checked />
		<label for="autoship_include_availability_on_sync"><?php echo esc_html( __( 'Enable the Add to Scheduled Order and Process on Scheduled Orders options for all products synchronized with your QPilot Site.', 'autoship' ) ); ?></label>

	</p>


	<div style="display:none;" class="autoship-meter">
		<span style="width:10%"></span>
	</div>

	<p><button class="button-primary autoship-action autoship-ajax-button"><span><?php echo esc_html( __( 'Update Products', 'autoship' ) ); ?></span></button></p>

	<p><button class="button-secondary autoship-cancel-action autoship-ajax-cancel-button"><span><?php echo esc_html( __( 'Cancel Update', 'autoship' ) ); ?></span></button></p>

	<input type="hidden" class="total-toggle-counters" name="total_count" value="<?php echo count( $maybe_query_ids['simple_variable_variation'] ); ?>">
	<input type="hidden" name="current_count" value="0">
	<input type="hidden" name="current_page" value="1">
	<input type="hidden" name="autoship-action" value="autoship_batch_update_products">
	<input type="hidden" name="batch_action" value="autoship_bulk_update_reset_active_sync">

	</div>

<?php } else { ?>

	<div class="autoship-bulk-action" id="autoship-bulk-activate-product-sync">

	<h2><?php echo esc_html( __( 'Update Product Synchronization', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Autoship Cloud powered by QPilot enables merchants to select which WooCommerce Products should be synchronized with your connected QPilot Site.', 'autoship' ) ); ?></p>
	<ul>
	<li style="padding-left: 10px;"><p><?php echo wp_kses_post( __( '- Use the <strong>“Activate All Products”</strong> option to Activate all WooCommerce Products to Sync with QPilot.', 'autoship' ) ); ?></p></li>
	<li style="padding-left: 10px;"><p><?php echo wp_kses_post( __( '- Use the <strong>“Deactivate All Products”</strong> option to Deactivate all WooCommerce Products from Syncing with QPilot.', 'autoship' ) ); ?></p></li>
	</ul>
	<p><?php echo esc_html( __( 'The Batch Size for this update (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>
	<hr/>

	<h4 class="autoship-bulk-notice"><?php wp_kses_post( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="form-field inline-field ">
		<label for="batch_size">Batch Size:</label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle autoship_trigger" for="yes-batch-enable-sync" data-show-target="#autoship-include-availability-sync" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>"><input id="yes-batch-enable-sync" type="radio" name="enable_active_sync_option" value="yes" checked />
		<?php echo esc_html( __( 'Activate All Products', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle autoship_trigger" for="no-batch-enable-sync" data-hide-target="#autoship-include-availability-sync" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>"><input id="no-batch-enable-sync" type="radio" name="enable_active_sync_option" value="no"/>
		<?php echo esc_html( __( 'Deactivate All Products', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field" id="autoship-include-availability-sync">

		<label for="autoship_include_availability_on_sync"
		><input type="checkbox"
		id="autoship_include_availability_on_sync"
		name="autoship_include_availability_on_sync"
		value="yes"
		autocomplete="false" checked />
		<?php echo esc_html( __( 'Enable the Add to Scheduled Order and Process on Scheduled Orders options for all products synchronized with your QPilot Site.', 'autoship' ) ); ?></label>

	</p>

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

<?php } ?>

<?php if ( ! $upgrade ) { ?>

<hr/>

<div class="autoship-bulk-action" id="autoship-bulk-checkout-discount">

	<h2><?php echo esc_html( __( 'Bulk Update Autoship Checkout Price', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Updates the Autoship Checkout Price for all WooCommerce Simple Products and Product Variations in your store.', 'autoship' ) ); ?></p>
	<p><?php echo esc_html( __( 'To update, enter a Percentage as a decimal value (for example: enter "0.10" for 10%) to discount the Regular Price of the WooCommerce Product or Variation to be used as the Autoship Checkout Price.  The Batch Size (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>

	<hr/>

	<?php $ids = autoship_global_sync_active_enabled() ? $query_ids['simple_variation'] : $query_active_ids['simple_variation']; ?>

	<h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $notice['simple_variation'], count( $ids ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="help"><?php echo esc_html( __( 'Select which price to use when calculating the checkout price.', 'autoship' ) ); ?></p>

	<p class="form-field inline-field">
		<label for="checkout_regular_base_price"><input id="checkout_regular_base_price" type="radio" name="base_price" value="regular" checked="checked"/>
		<?php echo esc_html( __( 'Regular Price', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label for="checkout_sale_base_price"><input id="checkout_sale_base_price" type="radio" name="base_price" value="sale"/>
		<?php echo esc_html( __( 'Sale Price', 'autoship' ) ); ?></label>
	</p>

	<div style="padding:20px 0px;">

		<p class="form-field inline-field">
			<label for="batch_size">Batch Size:</label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
		</p>

		<p class="form-field inline-field">
			<label for="checkout_pct">Percent Discount:</label>
		<input type="number" class="small-text" name="checkout_pct" value=".1" placeholder=".10" step=".01" min="0"/>
		</p>

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

	<hr/>

<div class="autoship-bulk-action" id="autoship-bulk-recurring-discount">

	<h2><?php echo esc_html( __( 'Bulk Update Autoship Recurring Price', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Updates the Autoship Recurring Price for all WooCommerce Simple Products and Product Variations in your store.', 'autoship' ) ); ?></p>
	<p><?php echo esc_html( __( 'To update, enter a Percentage as a decimal value (for example: enter "0.10" for 10%) to discount the Regular Price of the WooCommerce Product or Variation to be used as the Autoship Recurring Price.  The Batch Size (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>

	<hr/>

	<?php $ids = autoship_global_sync_active_enabled() ? $query_ids['simple_variation'] : $query_active_ids['simple_variation']; ?>

	<h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $notice['simple_variation'], count( $ids ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="help"><?php echo esc_html( __( 'Select which price to use when calculating the recurring price.' ) ); ?></p>

	<p class="form-field inline-field">
		<label for="recurring_regular_base_price"><input id="recurring_regular_base_price" type="radio" name="base_recurring_price" value="regular" checked="checked"/>
		<?php echo esc_html( __( 'Regular Price', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label for="recurring_sale_base_price"><input id="recurring_sale_base_price" type="radio" name="base_recurring_price" value="sale"/>
		<?php echo esc_html( __( 'Sale Price', 'autoship' ) ); ?></label>
	</p>

	<div style="padding:20px 0px;">

		<p class="form-field inline-field">
			<label for="batch_size">Batch Size:</label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
		</p>

		<p class="form-field inline-field">
			<label for="recurring_pct">Percent Discount:</label>
		<input type="number" class="small-text" name="recurring_pct" value=".1" placeholder=".10" step=".01" min="0"/>
		</p>

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

<hr/>

<div class="autoship-bulk-action" id="autoship-bulk-enable-autoship">

	<h2><?php echo esc_html( __( 'Bulk Enable WooCommerce Products to Display Autoship Options', 'autoship' ) ); ?></h2>
	<p><?php echo wp_kses_post( __( 'Enables or disables the display of Autoship options on WooCommerce Product Pages for all Simple Products, Variable Products and Product Variations. The <em>Enable Autoship</em> option updates WooCommerce Simple Products, Variable Products and Product Variations while the <em>Disable Autoship</em> option removes the display from WooCommerce Simple Products and Variable Products.  The Batch Size (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>
	<p><?php echo wp_kses_post( __( '<b>Important:</b> You will still need to enable product availability in Autoship Cloud.', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/441-product-availability-and-stock-status" target="_blank"><?php echo esc_html( __( 'Learn more', 'autoship' ) ); ?></a>.</p>

	<hr/>

	<?php

	$ids  = autoship_global_sync_active_enabled() ? $query_ids['simple_variable_variation'] : $query_active_ids['simple_variable_variation'];
	$text = autoship_global_sync_active_enabled() ? $notice['simple_variable_variation'] : $active_notice['simple_variable_variation'];

	?>

	<h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $text, count( $ids ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="form-field inline-field ">
		<label for="batch_size">Batch Size:</label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="yes-batch-enable" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable_variation'] ); ?>" data-adjust-batch-notice="<?php esc_attr( sprintf( $notice['simple_variable_variation'], count( $query_ids['simple_variable_variation'] ) ) ); ?>"><input id="yes-batch-enable" type="radio" name="enable_autoship_option" value="yes" checked="checked"/>
		<?php echo esc_html( __( 'Enable Autoship', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="no-batch-enable" data-adjust-batch-total="<?php echo count( $query_ids['simple_variable'] ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $notice['simple_variable'], count( $query_ids['simple_variable'] ) ) ); ?>"><input id="no-batch-enable" type="radio" name="enable_autoship_option" value="no"/>
		<?php echo esc_html( __( 'Disable Autoship', 'autoship' ) ); ?></label>
	</p>

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

<hr/>
<div class="autoship-bulk-action" id="autoship-bulk-frequencies-autoship">
	<h2><?php echo esc_html( __( 'Bulk Update Frequency Options', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Update the frequency options for all products on your store that are actively synced with Autoship Cloud. Updating these settings will update the frequency options shown for Autoship products and offered to customers on your site. If you have overridden frequency options for a specific product in WP-Admin > Products > Edit > Autoship, then updating these settings will not update those products.', 'autoship' ) ); ?></p>
	<p><b><?php esc_html( __( 'Important', 'autoship' ) ); ?></b><?php echo esc_html( __( 'For additional information on', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/376-change-default-frequency-options" target="_blank"><?php echo esc_html( __( 'how to manage frequency options for your product see here.', 'autoship' ) ); ?></a>.</p>
	<p><strong><?php echo esc_html( __( 'Note', 'autoship' ) ); ?>:</strong> <?php echo esc_html( __( 'The Batch Size (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>
	<hr style="margin-top: 20px; margin-bottom: 20px" />

	<?php
		$frequency_ids                    = autoship_global_sync_active_enabled() ? $query_ids['simple_variable_variation'] : $query_active_ids['simple_variable_variation'];
		$frequency_notice                 = autoship_global_sync_active_enabled() ? $notice['simple_variable_variation'] : $active_notice['simple_variable_variation'];
		$frequency_types                  = autoship_valid_relative_next_occurrence_types();
		$has_frequency_updatable_products = count( $frequency_ids ) > 0;
	?>

	<?php if ( ! $has_frequency_updatable_products ) : ?>

		<h4 class="autoship-bulk-notice"><?php echo esc_html( __( 'There are no available products to bulk update. Please activate the products first by enabling product sync and ensuring they are displayed in Autoship Cloud.', 'autoship' ) ); ?></h4>
		<h5 class="autoship-bulk-subnotice"></h5>

	<?php else : ?>
		<div id="frequency_update_options">
			<div class="frequency_option" id="frequency_option_1">
				<h3 style="display:inline; margin-right: 10px;"><?php echo esc_html( __( 'Frequency Option', 'autoship' ) ); ?> 1</h3>
				<div class="form-field" style="margin-top:10px; margin-bottom: 10px;">
					<label for="frequency_number_option_1"><?php echo esc_html( __( 'Frequency Type', 'autoship' ) ); ?>:</label>
					<select class="option-form-control" name="frequency_number_option_1" style="min-width: 250px !important;" <?php echo esc_attr( ! $has_frequency_updatable_products ? 'disabled=disabled' : '' ); ?> >
						<option value=""><?php echo esc_html( __( '-- Select frequency type--', 'autoship' ) ); ?></option>
						<?php foreach ( $frequency_types as $ftype => $flabel ) : ?>
							<option value="<?php echo esc_attr( $ftype ); ?>"><?php echo esc_html( $flabel ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-field" style="margin-bottom: 10px;">
					<label for="frequency_number_option_1"><?php echo esc_html( __( 'Frequency', 'autoship' ) ); ?>:</label>
					<input type="number" min="1" class="regular-text" name="frequency_number_option_1" step="1" placeholder="Enter a frequency number" style="max-width: 250px"  <?php echo esc_attr( ! $has_frequency_updatable_products ? 'disabled=disabled' : '' ); ?> pattern="\d*"/>
				</div>
				<div class="form-field" style="margin-bottom: 20px;">
					<label for="frequency_display_name_option_1"><?php echo esc_html( __( 'Display Name:', 'autoship' ) ); ?></label>
					<input type="text" class="regular-text" name="frequency_display_name_option_1" value="" placeholder="Optional display name" style="max-width: 250px" <?php echo esc_attr( ! $has_frequency_updatable_products ? 'disabled=disabled' : '' ); ?>  />
				</div>    
			</div>
		</div>

		<hr style="margin-top: 20px; margin-bottom: 20px" />

		<h4 class="autoship-bulk-notice" id="autoship_bulk_frequency_options_notice">
			<?php
				/* translators: %s: number of products */
				echo esc_html( sprintf( __( 'There are %s products that can be set frequency options.', 'autoship' ), count( $frequency_ids ) ) );
			?>
		</h4>
		<h5 class="autoship-bulk-subnotice" id="autoship_bulk_frequency_options_subnotice"></h5>

		<div id="frequency_update_settings">
			<p class="form-field">
				<label for="frequency_update_batch_size"><?php echo esc_html( __( 'Batch Size:', 'autoship' ) ); ?></label>
				<input type="number" class="small-text" name="frequency_update_batch_size" id="frequency_update_batch_size" value="10" placeholder="10" step="1" min="1"/>
			</p>
		</div>
		

		<div style="display:none;" class="autoship-meter" id="autoship_bulk_frequency_options_progress_container">
			<span id="autoship_bulk_frequency_options_progress_bar" style="width:10%"></span>
		</div>

		<div style="margin-top:20px;">
			<input type="hidden" name="frequency_update_products_total" id="frequency_update_products_total" value="<?php echo count( $frequency_ids ); ?>">
			<input type="hidden" name="frequency_update_products_count" id="frequency_update_products_count" value="0">
			<input type="hidden" name="frequency_update_products_page"  id="frequency_update_products_page" value="1">
			
			<button style="margin-right:10px;" type="button" class="button-primary button-start-bulk-frequency-update" <?php echo esc_attr( ! $has_frequency_updatable_products ? 'disabled=disabled' : '' ); ?> ><span><?php echo esc_html( __( 'Update Frequency Options', 'autoship' ) ); ?></span></button>

			<button type="button" class="button-primary cancel-button button-cancel-frequency-update" style="display:none; background: red; color: white;"><?php echo esc_html( __( 'Cancel', 'autoship' ) ); ?></button>

			<button type="button" class="button-secondary button-create-frequency" <?php echo esc_attr( ! $has_frequency_updatable_products ? 'disabled=disabled' : '' ); ?> ><span><?php echo esc_html( __( 'Add Another +', 'autoship' ) ); ?></span></button>
		</div>
	<?php endif; ?>
</div>

<hr/>

<div class="autoship-bulk-action" id="autoship-bulk-enable-availability">

	<h2><?php echo esc_html( __( 'Bulk Enable Product Availability', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Enables or disables the Add to Scheduled Order and Process on Scheduled Orders for all Simple Products and Product Variations. The Batch Size (default is "10") determines the number of products to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Products.', 'autoship' ) ); ?></p>
	<p><?php echo wp_kses_post( __( '<b>Important:</b> For additional information on the Add to Scheduled Order and Process on Scheduled Orders options', 'autoship' ) ); ?> <a href="https://support.autoship.cloud/article/441-product-availability-and-stock-status" target="_blank"><?php echo esc_html( __( 'Click Here', 'autoship' ) ); ?></a>.</p>

	<hr/>

	<?php
	$ids  = autoship_global_sync_active_enabled() ? $query_ids['simple_variation'] : $query_active_ids['simple_variation'];
	$text = autoship_global_sync_active_enabled() ? $notice['simple_variation'] : $active_notice['simple_variation'];
	?>

	<h4 class="autoship-bulk-notice"><?php echo wp_kses_post( sprintf( $text, count( $ids ) ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="form-field inline-field ">
		<label for="batch_size">Batch Size:</label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="yes-batch-enable-avail" data-adjust-batch-total="<?php echo count( $ids ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $text, count( $ids ) ) ); ?>"><input id="yes-batch-enable-avail" type="radio" name="enable_availability_option" value="yes" checked="checked"/>
		<?php echo esc_html( __( 'Enable Availability', 'autoship' ) ); ?></label>
	</p>

	<p class="form-field inline-field">
		<label class="batch-total-toggle" for="no-batch-enable-avail" data-adjust-batch-total="<?php echo count( $ids ); ?>" data-adjust-batch-notice="<?php echo esc_attr( sprintf( $text, count( $ids ) ) ); ?>"><input id="no-batch-enable-avail" type="radio" name="enable_availability_option" value="no"/>
		<?php echo esc_html( __( 'Disable Availability', 'autoship' ) ); ?></label>
	</p>

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

<hr/>

<div class="autoship-bulk-action" id="autoship-bulk-update-customer-metrics">
	<h2><?php echo esc_html( __( 'Bulk Update Customer Metrics', 'autoship' ) ); ?></h2>
	<p><?php echo esc_html( __( 'Imports Customer Metrics Data from the QPilot Cloud into the WordPress database for your subscription customers. The Batch Size (default is "10") determines the number of customers to update in a single round. Decreasing the Batch Size helps prevent timeout issues for sites with many WooCommerce Customers.', 'autoship' ) ); ?></p>
	<hr/>
	
	<?php
	// Retrieve the total customer in API count.
	$total = autoship_get_total_customers_cached();

	// translators: %d is the number of customers.
	$text = $total ? __( 'A total of a %d Customers can be processed.', 'autoship' ) : __( 'There are no available customers to process.', 'autoship' );
	?>

	<h4 class="autoship-bulk-notice"><?php echo esc_html( sprintf( $text, $total ) ); ?></h4>
	<h5 class="autoship-bulk-subnotice"></h5>

	<p class="form-field inline-field ">
		<label for="batch_size">Batch Size:</label>
		<input type="number" class="small-text" name="batch_size" value="10" placeholder="10" step="1" min="0"/>
	</p>

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
 
	<hr/>
	<?php
		$container      = Plugin::get_service_container();
		$synchronizer   = $container->get( ProductSynchronizer::class );
		$has_connection = $synchronizer->has_connection();
	?>
	<?php if ( Autoship\Core\FeatureManager::is_enabled( 'product_sync' ) && $has_connection ) : ?>
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
		<h2 style="vertical-align: middle;">
			<?php echo esc_html( __( 'Automatic Product Sync Utility', 'autoship' ) ); ?>

			<?php if ( $sync_settings_service_enabled ) : ?>
				<small class="badge badge-success" style="background-color: #00a32a; border-radius: 10px; color: white; padding: 7px; margin-left: 10px; font-size: 10px;"><?php echo esc_html( __( 'ENABLED', 'autoship' ) ); ?></small>
			<?php else : ?>
				<small class="badge badge-danger"  style="background-color: red; border-radius: 10px;color: white; padding: 7px; margin-left: 10px; font-size: 10px;"><?php echo esc_html( __( 'DISABLED', 'autoship' ) ); ?></small>
			<?php endif; ?>
		</h2>
		<p>
			<?php echo esc_html( __( 'This utility allows you to manage automatic product synchronization with Autoship Cloud using the WooCommerce Action Scheduler. You can enable or disable automatic syncing, define the number of products to process per batch, and set the interval in minutes between each scheduled run. These settings help ensure that your product data remains up to date while providing control over sync performance and server load.', 'autoship' ) ); ?>
		</p>
	
		<?php if ( $sync_settings_service_enabled ) : ?>

		<h4 class="autoship-bulk-notice"><?php echo esc_html( __( 'There are', 'autoship' ) ); ?> <strong><?php echo esc_html( $pending_sync_products ); ?></strong> <?php echo esc_html( __( 'products pending to sync for today.', 'autoship' ) ); ?></h4>

			<?php if ( $has_last_sync ) : ?>
				<h5 class="autoship-bulk-subnotice">
					<?php echo esc_html( __( 'Last synchronization was executed on', 'autoship' ) ); ?> <strong><?php echo esc_html( $last_sync_timestamp ); ?></strong> <?php echo esc_html( __( 'and took', 'autoship' ) ); ?> <?php echo esc_html( round( $last_sync_execution, 2 ) ); ?> <?php echo esc_html( __( 'seconds to process', 'autoship' ) ); ?> <?php echo esc_html( $last_sync_products ); ?> <?php echo esc_html( __( 'products.', 'autoship' ) ); ?>
				</h5>
			<?php endif; ?>
	
		<p class="form-field inline-field ">
			<label for="product-sync-batch-size"><?php echo esc_html( __( 'Batch Size', 'autoship' ) ); ?></label>
			<input name="product-sync-batch-size" id="product-sync-batch-size" type="number" style="min-width: 100px !important;" class="small-text" value="<?php echo esc_attr( $sync_settings_batch_size ); ?>" placeholder="<?php echo esc_attr( $suggested ); ?>" step="1" min="10" max="500"/> <?php echo esc_html( __( 'products', 'autoship' ) ); ?>
			<?php
			// translators: %1$d is the number of the recommended batch size, %2$d is the number of products.
			$suggested_batch_size_text = sprintf( __( 'The suggested batch size is %1$d based on your %2$d products using an interval of 5 minutes.', 'autoship' ), $suggested, $product_count );
			?>
			<span class="dashicons dashicons-editor-help" style="vertical-align: middle" title="<?php echo esc_attr( $suggested_batch_size_text ); ?>"></span>
		</p>

		<p class="form-field inline-field " style="display:block;">
			<label for="product-sync-interval"><?php echo esc_html( __( 'Run Every', 'autoship' ) ); ?></label>
			<select name="product-sync-interval" id="product-sync-interval" style="min-width: 100px !important;">
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
		</p>

		<p class="form-field inline-field " style="display:block;margin-top:10px;">
			<label for="product-sync-logging">
				<input type="checkbox" name="product-sync-logging" id="product-sync-logging" value="1" <?php echo esc_attr( $sync_settings_service_logs ? 'checked="checked"' : '' ); ?> />
				
				<?php echo esc_html( __( 'Enable synchronization logs to track activity and errors.', 'autoship' ) ); ?>
			</label>
			<span class="dashicons dashicons-editor-help" style="vertical-align: middle" title="<?php echo esc_attr( __( 'If the logs are enabled, you can download them from Autoship > Settings > Logs section.', 'autoship' ) ); ?>"></span>
		</p>
	
			<?php if ( $sync_remaining_hours > 24 ) : ?>
				<p style="color: red;"><strong><?php echo esc_html( __( 'Warning:', 'autoship' ) ); ?></strong> <?php echo esc_html( __( 'Using this settings will take more than 1 day to sync the remaining products.', 'autoship' ) ); ?></p>
			<?php endif; ?>

		<div class="autoship-product-sync-error-container"></div>
	
		<p style="margin-top:40px;">
			<button type="button" id="autoship-product-sync-updater" class="button-primary" style="margin-right: 10px;"><span><?php echo esc_html( __( 'Update Settings', 'autoship' ) ); ?></span></button>
			<button type="button" id="autoship-product-sync-disabler" class="button"><span><?php echo esc_html( __( 'Disable Product Sync', 'autoship' ) ); ?></span></button>
		</p>

		<div id="autoship-disable-sync-dialog" style="display:none;">
			<p>
				<?php echo esc_html( __( 'Disabling this utility will prevent data from WooCommerce Products being sent to Autoship Cloud unless you manually sync the data for an individual product or perform a bulk update.', 'autoship' ) ); ?>
			</p>
			<p><strong><?php echo esc_html( __( 'Do you wish to continue?' ) ); ?></strong></p>
		</div>
	
	<?php else : ?>
		<div class="autoship-product-sync-error-container"></div>
		<p style="margin-top:20px;">
			<button type="button" id="autoship-product-sync-enabler" class="button-primary"><span><?php echo esc_html( __( 'Enable Product Sync', 'autoship' ) ); ?></span></button>
		</p>
	<?php endif; ?>
	</div>

<?php endif; ?>
	
	<?php do_action( 'autoship_after_autoship_admin_utilities', $query_ids, $notice ); ?>

<?php } ?>
