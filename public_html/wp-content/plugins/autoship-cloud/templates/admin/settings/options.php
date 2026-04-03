
<div class="asc-settings-columns">



<!-- ====================================================================
     ROW 1, LEFT: Checkout & Cart
     ==================================================================== -->
    <div class="asc-settings-column">
        <h3><i class="pi pi-list"></i> <?php echo esc_html( __( 'Scheduled Orders Page', 'autoship' ) ); ?></h3>
        <p class="asc-card-description"><?php echo esc_html( __( 'Configure how Scheduled Orders are displayed in your customers\' My Account area.', 'autoship' ) ); ?></p>

        <div class="asc-form-group">
            <label class="asc-form-label"><?php echo esc_html( __( 'Displaying Scheduled Orders in My Account', 'autoship' ) ); ?></label>
            <p class="asc-card-description" style="margin-bottom: 0.75rem;"><?php echo wp_kses_post( __( 'Choose how the Scheduled Orders should be displayed in the <strong>My Account > Scheduled Orders</strong> page for your customers.', 'autoship' ) ); ?></p>

            <div class="asc-card-radio-group">
                <label class="asc-card-radio">
                    <input
                            type="radio"
                            name="autoship_scheduled_orders_display_version"
                            id="autoship_scheduled_orders_template"
                            value="template"
                            <?php echo checked( 'template', $autoship_settings['autoship_scheduled_orders_display_version'] ); ?>
                    />
                    <div class="asc-card-radio-content">
                        <div class="asc-card-radio-icon"><i class="pi pi-desktop"></i></div>
                        <span class="asc-card-radio-label"><?php echo esc_html( __( 'Native UI', 'autoship' ) ); ?></span>
                        <span class="asc-card-radio-desc"><?php echo esc_html( __( 'WordPress Templates', 'autoship' ) ); ?></span>
                    </div>
                </label>
                <label class="asc-card-radio">
                    <input
                            type="radio"
                            name="autoship_scheduled_orders_display_version"
                            id="autoship_scheduled_orders_v2_portal"
                            value="v2_portal"
                            <?php echo checked( 'v2_portal', $autoship_settings['autoship_scheduled_orders_display_version'] ); ?>
                    />
                    <div class="asc-card-radio-content">
                        <div class="asc-card-radio-icon"><i class="pi pi-bolt"></i></div>
                        <span class="asc-card-radio-label"><?php echo esc_html( __( 'V2 Portal', 'autoship' ) ); ?></span>
                        <span class="asc-card-radio-desc"><?php echo esc_html( __( 'Autoship Subscriber Portal', 'autoship' ) ); ?></span>
                    </div>
                </label>
            </div>

            <input type="hidden" name="autoship_v2_portal_display_without_scheduled_orders"
                   id="autoship_v2_portal_display_without_scheduled_orders" value="yes"/>

            <div class="autoship_native_ui_extra_options asc-nested-options"
                 style="display:<?php echo 'template' === $autoship_settings['autoship_scheduled_orders_display_version'] ? 'block' : 'none'; ?>;">
                <h4 class="asc-subsection-title"><i class="pi pi-cog" style="color: var(--qmc-primary, #3b82f6); margin-right: 0.375rem;"></i><?php echo esc_html( __( 'Native UI: Additional Options', 'autoship' ) ); ?></h4>
                <p class="asc-option-label"><?php echo esc_html( __( 'Display Product Upsell Carousel', 'autoship' ) ); ?></p>

                <input
                        type="checkbox"
                        name="autoship_scheduled_order_upsell_carousel"
                        id="autoship_scheduled_order_upsell_carousel"
                        value="yes"
                        <?php echo checked( 'yes', $autoship_settings['autoship_scheduled_order_upsell_carousel'] ); ?>
                />
                <label for="autoship_scheduled_order_upsell_carousel"><?php echo esc_html( __( 'Enable to display the Product Upsell Carousel.', 'autoship' ) ); ?></label><br/><br/>

                <div class="autoship_upsell_disable_carousel_js_wrap"
                     style="display:<?php echo 'yes' === $autoship_settings['autoship_scheduled_order_upsell_carousel'] ? 'block' : 'none'; ?>;">
                    <input
                            type="checkbox"
                            name="autoship_scheduled_order_upsell_disable_carousel_js"
                            id="autoship_scheduled_order_upsell_disable_carousel_js"
                            value="yes"
                            <?php echo checked( 'yes', $autoship_settings['autoship_scheduled_order_upsell_disable_carousel_js'] ); ?>
                    />
                    <label for="autoship_scheduled_order_upsell_disable_carousel_js"><?php echo esc_html( __( 'Select to disable loading of Carousel JS script.', 'autoship' ) ); ?></label><br/><br/>
                </div>

                <p class="asc-option-label"><?php echo esc_html( __( 'Enable Selectable Shipping Rates', 'autoship' ) ); ?></p>

                <input type="checkbox"
                       id="autoship_editable_shipping_rate_enabled"
                       name="autoship_editable_shipping_rate_enabled"
                       value="yes"
                        <?php echo checked( 'yes', $autoship_settings['autoship_editable_shipping_rate_enabled'] ); ?>
                       autocomplete="false"/>
                <label for="autoship_editable_shipping_rate_enabled"><?php echo esc_html( __( 'Allow Customers to choose a Preferred Shipping rate when editing a Scheduled Order in the Native UI Display.', 'autoship' ) ); ?></label>
            </div>

            <div class="autoship_v2_portal_extra_options asc-nested-options"
                 style="display:<?php echo 'v2_portal' === $autoship_settings['autoship_scheduled_orders_display_version'] ? 'block' : 'none'; ?>;">
                <h4 class="asc-subsection-title"><i class="pi pi-bolt" style="color: var(--qmc-primary, #3b82f6); margin-right: 0.375rem;"></i><?php echo esc_html( __( 'v2 Portal: Benefits', 'autoship' ) ); ?></h4>
                <p style="font-size: 0.875rem; color: var(--qmc-text-primary, #1f2937); margin: 0 0 0.75rem 0; line-height: 1.5;">
                    <?php echo esc_html( __( 'The v2 Portal is a custom component managed by Autoship Cloud, designed to provide a modern and seamless experience for your customers.', 'autoship' ) ); ?>
                </p>
                <ul style="margin: 0 0 0 1.25rem; padding: 0; font-size: 0.875rem; color: var(--qmc-text-primary, #1f2937); line-height: 1.8;">
                    <li><?php echo esc_html( __( 'Continuously updated with new features and improvements', 'autoship' ) ); ?></li>
                    <li><?php echo esc_html( __( 'Modern UI with responsive design for all devices', 'autoship' ) ); ?></li>
                    <li><?php echo esc_html( __( 'No template overrides needed — always up to date', 'autoship' ) ); ?></li>
                    <li><?php echo esc_html( __( 'Built-in support for editing, pausing, and managing Scheduled Orders', 'autoship' ) ); ?></li>
                </ul>
            </div>
        </div>

    </div>


    <!-- ====================================================================
         ROW 1, RIGHT: Labels & Translations
         ==================================================================== -->
    <div class="asc-settings-column">
        <h3><i class="pi pi-language"></i> <?php echo esc_html( __( 'Labels & Translations', 'autoship' ) ); ?></h3>
        <p class="asc-card-description"><?php echo esc_html( __( 'Customize the labels used throughout your shop for Autoship features.', 'autoship' ) ); ?></p>

        <div class="asc-form-group">
            <label class="asc-form-label" for="autoship_translation"><?php echo esc_html( __( 'Autoship Label', 'autoship' ) ); ?></label>
            <input type="text" id="autoship_translation" name="autoship_translation" value="<?php echo esc_attr( $autoship_settings['autoship_translation'] ); ?>" placeholder="Autoship"/>
            <p class="asc-card-description"><?php echo wp_kses_post( __( 'Replaces the word <strong>Autoship</strong> wherever it appears in your shop.', 'autoship' ) ); ?></p>
        </div>

        <div class="asc-form-group">
            <label class="asc-form-label" for="autoship_and_save_translation"><?php echo esc_html( __( 'Autoship and Save Label', 'autoship' ) ); ?></label>
            <input type="text" id="autoship_and_save_translation" name="autoship_and_save_translation" value="<?php echo esc_attr( $autoship_settings['autoship_and_save_translation'] ); ?>" placeholder="Autoship and Save"/>
            <p class="asc-card-description"><?php echo wp_kses_post( __( 'Replaces <strong>Autoship and Save</strong> on product and cart pages when a discount is offered.', 'autoship' ) ); ?></p>
        </div>

        <div class="asc-form-group">
            <label class="asc-form-label" for="autoship_scheduled_order_translation"><?php echo esc_html( __( 'Scheduled Order Label', 'autoship' ) ); ?></label>
            <input type="text" id="autoship_scheduled_order_translation" name="autoship_scheduled_order_translation" value="<?php echo esc_attr( $autoship_settings['autoship_scheduled_order_translation'] ); ?>" placeholder="Scheduled Order"/>
            <p class="asc-card-description"><?php echo wp_kses_post( __( 'Replaces the <strong>Scheduled Order</strong> label (singular) throughout your shop.', 'autoship' ) ); ?></p>
        </div>

        <div class="asc-form-group">
            <label class="asc-form-label" for="autoship_scheduled_orders_translation"><?php echo esc_html( __( 'Scheduled Orders Label', 'autoship' ) ); ?></label>
            <input type="text" id="autoship_scheduled_orders_translation" name="autoship_scheduled_orders_translation" value="<?php echo esc_attr( $autoship_settings['autoship_scheduled_orders_translation'] ); ?>" placeholder="Scheduled Orders"/>
            <p class="asc-card-description"><?php echo wp_kses_post( __( 'Replaces the <strong>Scheduled Orders</strong> label (plural) throughout your shop.', 'autoship' ) ); ?></p>
        </div>

    </div>
<!-- ====================================================================
     ROW 2, LEFT: Scheduled Orders Display
     ==================================================================== -->
    <div class="asc-settings-column">
        <h3><i class="pi pi-shopping-cart"></i> <?php echo esc_html( __( 'Checkout & Cart', 'autoship' ) ); ?></h3>
        <p class="asc-card-description"><?php echo esc_html( __( 'Configure shipping, cart scheduling, and payment options for Autoship orders.', 'autoship' ) ); ?></p>

        <div class="asc-form-group">
            <label class="asc-form-label"><?php echo esc_html( __( 'Enable Free Shipping', 'autoship' ) ); ?></label>
            <div class="asc-radio-group">
                <label class="asc-radio-option">
                    <input type="radio" name="autoship_free_shipping" id="autoship_free_shipping_disable" value="" <?php echo checked( '', $autoship_settings['autoship_free_shipping'] ); ?> />
                    <span><?php echo esc_html( __( 'Disable Autoship Free Shipping. You may need to delete your cache for the changes to take effect.', 'autoship' ) ); ?></span>
                </label>
                <label class="asc-radio-option">
                    <input type="radio" name="autoship_free_shipping" id="autoship_free_shipping_enable" value="checkout+autoship" <?php echo checked( 'checkout+autoship', $autoship_settings['autoship_free_shipping'] ); ?> />
                    <span><?php echo wp_kses_post( __( 'Add the Autoship Free Shipping method to <strong>Checkout</strong>. Once enabled, add this shipping method to a Shipping Zone in order to offer Free Shipping to customers who add at least 1 item to their cart selected for Autoship.', 'autoship' ) ); ?></span>
                </label>
            </div>
        </div>

        <div class="asc-form-group">
            <label class="asc-form-label"><?php echo esc_html( __( 'Schedule Products in the Cart', 'autoship' ) ); ?></label>
            <label for="autoship_cart_schedule_options_enabled" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.5rem; font-weight: 400;">
                <input type="checkbox"
                       id="autoship_cart_schedule_options_enabled"
                       name="autoship_cart_schedule_options_enabled"
                       value="yes"
                        <?php echo checked( 'yes', $autoship_settings['autoship_cart_schedule_options_enabled'] ); ?>
                       autocomplete="false" style="margin-top: 0.2rem;"/>
                <?php echo esc_html( __( 'Enable to display Autoship options for each product in the Cart.', 'autoship' ) ); ?>
            </label>
        </div>

        <div class="asc-form-group">
            <label class="asc-form-label"><?php echo esc_html( __( 'Cash On Delivery Payments at Checkout', 'autoship' ) ); ?></label>

            <div class="asc-radio-group">
                <label class="asc-radio-option">
                    <input type="radio" name="autoship_support_cod_payments" id="autoship_support_cod_payments_disabled" value="no" <?php echo checked( 'no', $autoship_settings['autoship_support_cod_payments'] ); ?> />
                    <span><?php echo esc_html( __( 'Disabled — Do not accept Cash On Delivery for Autoship orders', 'autoship' ) ); ?></span>
                </label>
                <label class="asc-radio-option">
                    <input type="radio" name="autoship_support_cod_payments" id="autoship_support_cod_payments_enabled" value="yes" <?php echo checked( 'yes', $autoship_settings['autoship_support_cod_payments'] ); ?> />
                    <span><?php echo esc_html( __( 'Enabled — Accept Cash On Delivery as a payment method for Autoship orders', 'autoship' ) ); ?></span>
                </label>
            </div>

            <div class="asc-alert asc-alert-info" style="margin-bottom: 0.75rem;">
                <i class="pi pi-info-circle"></i>
                <div class="asc-alert-content">
                    <?php
                    // translators: %s is a link to the "Other" Payment Type documentation.
                    echo wp_kses_post( sprintf( __( '<strong>Important:</strong> Enabling Cash On Delivery (COD) requires adding the <a href="%s" target="_blank">"Other" Payment Type</a> to your Autoship Cloud Payment Integrations. Review the documentation to ensure customers cannot bypass payment on their Scheduled Orders.', 'autoship' ), 'https://support.autoship.cloud/article/1031-other-payment' ) );
                    ?>
                </div>
            </div>
        </div>

    </div>

<!-- ====================================================================
     ROW 2, RIGHT: Product Page
     ==================================================================== -->
<div class="asc-settings-column">
<h3><i class="pi pi-shopping-bag"></i> <?php echo esc_html( __( 'Product Page', 'autoship' ) ); ?></h3>
<p class="asc-card-description"><?php echo esc_html( __( 'Configure how Autoship information and options are displayed on your product pages.', 'autoship' ) ); ?></p>

<div class="asc-form-group">
	<label class="asc-form-label" for="autoship_product_info_display"><?php echo esc_html( __( 'Product Page Autoship Info Link', 'autoship' ) ); ?></label>
	<p class="asc-card-description" style="margin-bottom: 0.5rem;"><?php echo esc_html( __( 'Choose how the Product Page Autoship Info Link should be displayed to your customers in the product pages.', 'autoship' ) ); ?></p>

	<div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
		<select name="autoship_product_info_display" id="autoship_product_info_display">
			<option value="none" <?php selected( 'none', $autoship_settings['autoship_product_info_display'] ); ?> ><?php echo esc_html( __( 'Do Not Display', 'autoship' ) ); ?></option>
			<option value="tooltip" <?php selected( 'tooltip', $autoship_settings['autoship_product_info_display'] ); ?> ><?php echo esc_html( __( 'Display as a Tooltip', 'autoship' ) ); ?></option>
			<option value="modal" <?php selected( 'modal', $autoship_settings['autoship_product_info_display'] ); ?> ><?php echo esc_html( __( 'Display as a Modal', 'autoship' ) ); ?></option>
			<option value="link" <?php selected( 'link', $autoship_settings['autoship_product_info_display'] ); ?> ><?php echo esc_html( __( 'Display as a Link', 'autoship' ) ); ?></option>
		</select>

		<select class="autoship_product_info_modal_size" name="autoship_product_info_modal_size"
		        id="autoship_product_info_modal_size"
		        style="<?php echo 'modal' !== $autoship_settings['autoship_product_info_display'] && 'tooltip' !== $autoship_settings['autoship_product_info_display'] ? 'display:none' : ''; ?> ">
			<option value="small" <?php selected( 'small', $autoship_settings['autoship_product_info_modal_size'] ); ?> ><?php echo esc_html( __( 'Small Width ( 300px )', 'autoship' ) ); ?></option>
			<option value="medium" <?php selected( 'medium', $autoship_settings['autoship_product_info_modal_size'] ); ?> ><?php echo esc_html( __( 'Medium Width ( 500px )', 'autoship' ) ); ?></option>
			<option value="large" <?php selected( 'large', $autoship_settings['autoship_product_info_modal_size'] ); ?> ><?php echo esc_html( __( 'Large Width ( 800px )', 'autoship' ) ); ?></option>
			<option value="full" <?php selected( 'full', $autoship_settings['autoship_product_info_modal_size'] ); ?> ><?php echo esc_html( __( 'Auto Width', 'autoship' ) ); ?></option>
		</select>
	</div>
</div>

<?php $display = 'link' !== $autoship_settings['autoship_product_info_display'] ? 'display:none' : ''; ?>

<div class="asc-form-group" id="autoship_product_info_url_block" style="<?php echo esc_attr( $display ); ?>">
	<label class="asc-form-label" for="autoship_product_info_url"><?php echo esc_html( __( 'Product Page Autoship Info Link Url', 'autoship' ) ); ?></label>
	<input type="text" id="autoship_product_info_url" name="autoship_product_info_url"
	       value="<?php echo esc_attr( $autoship_settings['autoship_product_info_url'] ); ?>"
	       placeholder="www.yoursite.com/autoship-details/"/>
	<p class="asc-field-hint"><?php echo esc_html( __( 'Enter the URL to use for the Product Page Autoship Info Link.', 'autoship' ) ); ?></p>
</div>

<?php
$display = 'tooltip' !== $autoship_settings['autoship_product_info_display'] ? 'display:none' : '';
$min     = apply_filters( 'autoship_dialog_info_tooltip_min_browser_width', 1024 );
$min     = ! $min ? 1024 : $min;
?>

<div class="asc-form-group" id="autoship_product_info_mobile_tooltip_block" style="<?php echo esc_attr( $display ); ?>">
	<label class="asc-form-label"><?php echo esc_html( __( 'Display Product Page Autoship Info Tooltip Link as Modal on Mobile', 'autoship' ) ); ?></label>
	<div style="margin-top: 0.5rem;">
		<input type="checkbox"
		       id="autoship_product_info_mobile_tooltip"
		       name="autoship_product_info_mobile_tooltip"
		       value="yes"
			<?php echo checked( 'yes', $autoship_settings['autoship_product_info_mobile_tooltip'] ); ?>
			   autocomplete="false"/>
		<label for="autoship_product_info_mobile_tooltip">
			<?php
			echo esc_html( __( "Enable to display tooltips as Modals on Screens under {$min}px.", 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.InterpolatedVariableText
			?>
		</label>
	</div>
</div>

<?php $display = 'none' === $autoship_settings['autoship_product_info_display'] ? 'display:none' : ''; ?>

<div id="autoship_product_info_btn_type_block" style="<?php echo esc_attr( $display ); ?>">

	<div class="asc-form-group">
		<label class="asc-form-label"><?php echo esc_html( __( 'Product Page Autoship Info Link Type', 'autoship' ) ); ?></label>
		<p class="asc-card-description" style="margin-bottom: 0.5rem;"><?php echo esc_html( __( 'Choose if the Link should be displayed.', 'autoship' ) ); ?></p>

		<div class="asc-radio-group">
			<label for="autoship_product_info_btn_type_icon" class="asc-radio-option autoship_trigger"
			       data-hide-target="#autoship_product_info_btn_text_block">
				<input
					type="radio"
					name="autoship_product_info_btn_type"
					id="autoship_product_info_btn_type_icon"
					value="icon"
					<?php echo checked( 'icon', $autoship_settings['autoship_product_info_btn_type'] ); ?>
				/>
				<span><?php echo esc_html( __( 'Icon', 'autoship' ) ); ?></span>
			</label>
			<label for="autoship_product_info_btn_type_text" class="asc-radio-option autoship_trigger"
			       data-show-target="#autoship_product_info_btn_text_block">
				<input
					type="radio"
					name="autoship_product_info_btn_type"
					id="autoship_product_info_btn_type_text"
					value="text"
					<?php echo checked( 'text', $autoship_settings['autoship_product_info_btn_type'] ); ?>
				/>
				<span><?php echo esc_html( __( 'Text Link', 'autoship' ) ); ?></span>
			</label>
		</div>
	</div>

	<?php $display = 'icon' === $autoship_settings['autoship_product_info_btn_type'] ? 'display:none' : ''; ?>

	<div class="asc-form-group" id="autoship_product_info_btn_text_block" style="<?php echo esc_attr( $display ); ?>">
		<label class="asc-form-label" for="autoship_product_info_btn_text"><?php echo esc_html( __( 'Product Page Autoship Info Link Label', 'autoship' ) ); ?></label>
		<input type="text" id="autoship_product_info_btn_text" name="autoship_product_info_btn_text"
		       value="<?php echo esc_attr( $autoship_settings['autoship_product_info_btn_text'] ); ?>"
		       placeholder="Info"/>
		<p class="asc-field-hint"><?php echo esc_html( __( 'Enter the Text to use for the Product Page Autoship Info Link.', 'autoship' ) ); ?></p>
	</div>

</div>

<?php $display = 'none' === $autoship_settings['autoship_product_info_display'] ? 'display:none' : ''; ?>

<div class="asc-form-group" id="autoship_product_info_html_block" style="<?php echo esc_attr( $display ); ?>">
	<label class="asc-form-label" for="autoship_product_info_html"><?php echo esc_html( __( 'Product Page Autoship Info Link Content', 'autoship' ) ); ?></label>
	<p class="asc-card-description" style="margin-bottom: 0.5rem;"><?php echo esc_html( __( 'Use the editor to display content in the Autoship Info Dialog.', 'autoship' ) ); ?></p>
	<div class="html-editor-wrapper">
		<?php wp_editor( $autoship_settings['autoship_product_info_html'], 'autoship_product_info_html', $settings ); ?>
	</div>
</div>

<div class="asc-form-group">
	<label class="asc-form-label" for="autoship_product_message"><?php echo esc_html( __( 'Product Page Autoship Message', 'autoship' ) ); ?></label>
	<p class="asc-card-description" style="margin-bottom: 0.5rem;"><?php echo esc_html( __( 'Enable to display an additional message next to Autoship options on the Product page.', 'autoship' ) ); ?></p>
	<div class="html-editor-wrapper">
		<?php wp_editor( $autoship_settings['autoship_product_message'], 'autoship_product_message', $settings ); ?>
	</div>
</div>

</div>

<!-- ====================================================================
     ROW 3, FULL WIDTH: Scheduled Orders Messages
     ==================================================================== -->
<div class="asc-settings-column" style="grid-column: 1 / -1;">
<h3><i class="pi pi-envelope"></i> <?php echo esc_html( __( 'Scheduled Orders Messages', 'autoship' ) ); ?></h3>
<p class="asc-card-description"><?php echo esc_html( __( 'Customize the messages displayed on the Scheduled Orders page in My Account.', 'autoship' ) ); ?></p>

<div class="asc-editors-row">
	<div class="asc-form-group">
		<label class="asc-form-label" for="autoship_scheduled_orders_html"><?php echo esc_html( __( 'Scheduled Orders Header Message', 'autoship' ) ); ?></label>
		<p class="asc-card-description" style="margin-bottom: 0.5rem;"><?php echo esc_html( __( 'Use the editor to display content above the Scheduled Orders page.', 'autoship' ) ); ?></p>
		<div class="html-editor-wrapper">
			<?php wp_editor( $autoship_settings['autoship_scheduled_orders_html'], 'autoship_scheduled_orders_html', $settings ); ?>
		</div>
	</div>

	<div class="asc-form-group">
		<label class="asc-form-label" for="autoship_scheduled_orders_body_html"><?php echo esc_html( __( 'No Scheduled Orders Body Message', 'autoship' ) ); ?></label>
		<p class="asc-card-description" style="margin-bottom: 0.5rem;"><?php echo esc_html( __( 'Use the editor to display content on the Scheduled Orders page when no Scheduled Orders exist for the user.', 'autoship' ) ); ?></p>
		<div class="html-editor-wrapper">
			<?php wp_editor( $autoship_settings['autoship_scheduled_orders_body_html'], 'autoship_scheduled_orders_body_html', $settings ); ?>
		</div>
	</div>
</div>

</div>

<!-- ====================================================================
     ROW 4, LEFT: Order Processing
     ==================================================================== -->
<div class="asc-settings-column">
<h3><i class="pi pi-receipt"></i> <?php echo esc_html( __( 'Order Processing', 'autoship' ) ); ?></h3>
<p class="asc-card-description"><?php echo esc_html( __( 'Configure how Autoship orders are processed and displayed in WooCommerce.', 'autoship' ) ); ?></p>

<div class="asc-form-group">
	<label class="asc-form-label"><?php echo esc_html( __( 'Display QPilot Coupons as Fee Lines', 'autoship' ) ); ?></label>
	<label style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.5rem; font-weight: 400;">
		<input type="checkbox"
		       id="autoship_rest_order_fee_lines_enabled"
		       name="autoship_rest_order_fee_lines_enabled"
		       class="autoship_hide_show_toggler"
		       data-target=".virtual-coupon-override-note"
		       value="yes"
			<?php echo checked( 'yes', $autoship_settings['autoship_rest_order_fee_lines_enabled'] ); ?>
			   autocomplete="false" style="margin-top: 0.2rem;"/>
		<?php echo esc_html( __( 'Override default and include QPilot Coupons as Fee Lines on Autoship Orders.', 'autoship' ) ); ?>
	</label>
	<div class="virtual-coupon-override-note"
	     style="<?php echo 'yes' !== $autoship_settings['autoship_rest_order_fee_lines_enabled'] ? 'display:none;' : ''; ?>">
		<p><strong><?php echo esc_html( __( 'Important', 'autoship' ) ); ?></strong></p>
		<small>
			<?php echo wp_kses_post( __( 'By overriding this default setting, you are disabling the use of virtual coupons for WooCommerce Orders created via the REST API by your connected QPilot Site.  Developers can adjust this setting via the filter <strong>autoship_qpilot_orders_via_rest_enable_fee_lines</strong> within <strong>src\coupons.php</strong>', 'autoship' ) ); ?>.
		</small>
	</div>
</div>

<div class="asc-form-group">
	<label class="asc-form-label"><?php echo esc_html( __( 'Display Ship by Date in Order Management', 'autoship' ) ); ?></label>
	<label for="autoship_display_next_occurrence_offset" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.5rem; font-weight: 400;">
		<input type="checkbox"
		       id="autoship_display_next_occurrence_offset"
		       name="autoship_display_next_occurrence_offset"
		       value="yes"
			<?php echo checked( 'yes', $autoship_settings['autoship_display_next_occurrence_offset'] ); ?>
			   autocomplete="false" style="margin-top: 0.2rem;"/>
		<?php echo esc_html( __( 'Enable to include a "Ship By Date" column in the WooCommerce > Orders list table showing the calculated ship date for each order based on the Next Occurrence Offset.', 'autoship' ) ); ?>
	</label>
</div>

</div>

<!-- ====================================================================
     ROW 3, RIGHT: Advanced
     ==================================================================== -->
<div class="asc-settings-column">
    <h3><i class="pi pi-wrench"></i> <?php echo esc_html( __( 'Advanced', 'autoship' ) ); ?></h3>
    <p class="asc-card-description"><?php echo esc_html( __( 'Legacy compatibility and diagnostic options for advanced users and support.', 'autoship' ) ); ?></p>

    <div class="asc-form-group">
        <label class="asc-form-label"><?php echo esc_html( __( 'Legacy QPilot Product Data', 'autoship' ) ); ?></label>
        <label for="autoship_legacy_qpilot_products_data" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.5rem; font-weight: 400;">
            <input type="checkbox"
                   id="autoship_legacy_qpilot_products_data"
                   name="autoship_legacy_qpilot_products_data"
                   value="yes"
                <?php echo checked( 'yes', $autoship_settings['autoship_legacy_qpilot_products_data'] ); ?>
                   autocomplete="false" style="margin-top: 0.2rem;"/>
            <?php echo wp_kses_post( __( 'Enabling this option will retrieve your product data from QPilot instead of WooCommerce.', 'autoship' ) ); ?>
        </label>
    </div>

    <div class="asc-form-group">
        <label class="asc-form-label"><?php echo esc_html( __( 'Debug & Support Tracing', 'autoship' ) ); ?></label>
        <label for="autoship_debug_state" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.5rem; font-weight: 400;">
            <input type="checkbox"
                   id="autoship_debug_state"
                   name="autoship_debug_state"
                   value="active"
                <?php echo checked( 'active', $autoship_settings['autoship_debug_state'] ); ?>
                   autocomplete="false" style="margin-top: 0.2rem;"/>
            <?php echo esc_html( __( 'Enabling this option will generate detailed logs. Leaving it enabled for extended periods may impact performance and consume significant storage.', 'autoship' ) ); ?>
        </label>
    </div>
</div>

</div><!-- .asc-settings-columns -->
