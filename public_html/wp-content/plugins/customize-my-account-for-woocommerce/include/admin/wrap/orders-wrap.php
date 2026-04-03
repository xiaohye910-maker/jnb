<?php
global $wp_roles;

$extra_content_core_fields = 'downloads,edit-address,edit-account';
$exclude_content_core_fields       = 'dashboard,orders,customer-logout';


$wcmamtx_type = 'endpoint';




if (isset($value['parent']) && ($value['parent'] != "")) {

   $wcmamtx_parent = $value['parent'];
   
} else {

   $wcmamtx_parent = 'none';
   
}



if ( ! isset( $wp_roles ) ) { 
   $wp_roles = new WP_Roles();  

}

$roles    = $wp_roles->roles;

$third_party = isset($value['third_party']) ? $value['third_party'] : $third_party;


?>

<h3>
  <div class="wcmamtx_accordion_handler">
     <?php if (preg_match('/\b'.$key.'\b/', $core_fields )) { ?>

        
        <input type="checkbox" <?php if ($key == "order-actions") { echo 'disabled="disabled"'; } ?> class="wcmamtx_accordion_onoff" parentkey="<?php echo $key; ?>"  <?php if (isset($value['show']) && ($value['show'] != "no"))  { echo "checked"; } elseif (!isset($value['show'])) { echo 'checked';} ?>>

        

        <input type="hidden" class="<?php echo $key; ?>_hidden_checkbox" value='<?php if (isset($value['show']) && ($value['show'] == "no")) { echo "no"; } else { echo 'yes';} ?>' name='wcmamtx_order_actions[<?php echo $key; ?>][show]'>

    <?php } else { 

      if (isset($third_party)) {
        $key = strtolower($key);
        $key = str_replace(' ', '_', $key);
    }

    ?>
    <span type="removeicon" parentkey="<?php echo $key; ?>" class="dashicons dashicons-trash wcmamtx_accordion_remove"></span>
<?php } ?>
</div>

<span class="dashicons dashicons-menu-alt "></span><?php if (isset($name) && ($name != "")) { echo $name; } else if ($key == "order-actions") { echo esc_html__('Actions','customize-my-account-for-woocommerce'); } ?>
<span class="wcmamtx_type_label">
 <?php echo esc_html__('Order Action','customize-my-account-for-woocommerce'); ?>
</span>

</h3>

<div class="<?php echo $wcmamtx_type; ?>_accordion_content">

   <table class="wcmamtx_table widefat">

      <?php if (isset($third_party)) { ?>

         <tr>
            <td>
                
            </td>
            <td>
               <p><?php  echo esc_html__('This is third party endpoint.Some features may not work.','customize-my-account-for-woocommerce'); ?></p>
               <input type="hidden" name="wcmamtx_order_actions[<?php echo $key; ?>][third_party]" value="yes">
               <input type="hidden" name="wcmamtx_order_actions[<?php echo $key; ?>][endpoint_name]" value="<?php if (isset($name)) { echo $name; } ?>">
           </td>

       </tr>

   <?php } ?>

   

   <input type="hidden" class="wcmamtx_accordion_input" name="wcmamtx_order_actions[<?php echo $key; ?>][endpoint_key]" value="<?php if (isset($value['endpoint_key'])) { echo $value['endpoint_key']; } else { echo $key; } ?>">


   

   
   <input type="hidden" name="wcmamtx_order_actions[<?php echo $key; ?>][wcmamtx_type]" value="<?php echo $wcmamtx_type; ?>">

   <input type="hidden" name="wcmamtx_order_actions[<?php echo $key; ?>][parent]" class="wcmamtx_parent_field" value="<?php echo $wcmamtx_parent; ?>">

   <?php if (!isset($third_party) && ($key != "order-actions")) { ?>

    <tr>
        <td>
            <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Label','customize-my-account-for-woocommerce'); ?></label>
        </td>
        <td>
         
            <input type="text" class="wcmamtx_accordion_input" name="wcmamtx_order_actions[<?php echo $key; ?>][endpoint_name]" value="<?php if (isset($value['endpoint_name'])) { echo $value['endpoint_name']; } elseif ($key != "order-actions") { echo $value; } ?>">
        </td>
        
    </tr>

<?php } else { ?>

    <tr>

       <td>
        <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Label','customize-my-account-for-woocommerce'); ?></label>
    </td>
    <td>
     
      <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Actions','customize-my-account-for-woocommerce'); ?></label>
  </td>
</tr>

<?php } ?>

<tr>

   <td>
    <label class="wcmamtx_accordion_label">
       <?php  echo esc_html__('Url','customize-my-account-for-woocommerce'); ?>
   </label>
</td>
<td>
 
    <input type="text" class="wcmamtx_accordion_input" name="wcmamtx_order_actions[<?php echo $key; ?>][action_url]" value="<?php if (isset($value['action_url'])) { echo $value['action_url']; } else {
        echo ''.site_url().'/?order_id={orderid}&trekking={your_custom_meta_key}';
    } ?>" size="100">
</td>
</tr>

<tr>
   <td>
    
   </td>
   <td>
    <p><?php  echo ''.esc_html__('Example','customize-my-account-for-woocommerce').' : '.site_url().'/?order_id={orderid}&trekking={your_custom_meta_key}'; ?></p>
    <p><?php  echo esc_html__('You can use following variables inside url','customize-my-account-for-woocommerce'); ?></p>
    <ul>
       <li><?php  echo esc_html__('{orderid} = Order ID','customize-my-account-for-woocommerce'); ?></li>
       <li><?php  echo esc_html__('{your_custom_meta_key} = Order Custom Field','customize-my-account-for-woocommerce'); ?></li>
   </ul></td>
</tr>

<tr>
    <td>
        <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Icon Settings','customize-my-account-for-woocommerce'); ?></label>
    </td>
    <td>
        <?php 
        if (isset($value['icon_source']) && ($value['icon_source'] != '')) {
            $icon_source = $value['icon_source'];
        } else {
            $icon_source = 'default';
        }
        ?>

        <div class="wcmamtx_icon_settings_div">
            
            <div class="form-check wcmamtx_icon_checkbox">
                <input class="form-check-input wcmamtx_icon_source_radio" type="radio" name="wcmamtx_order_actions[<?php echo $key; ?>][icon_source]"  value="noicon" <?php if (($icon_source != "custom") || ($icon_source == "dashicon")) { echo 'checked'; } ?>>
                <label class="form-check-label wcmamtx_icon_checkbox_label">
                    <?php  echo esc_html__('No Icon','customize-my-account-for-woocommerce'); ?>
                </label>
            </div>
            <div class="form-check wcmamtx_icon_checkbox">
                <input class="form-check-input wcmamtx_icon_source_radio" type="radio" name="wcmamtx_order_actions[<?php echo $key; ?>][icon_source]"  value="custom" <?php if ($icon_source == "custom") { echo 'checked'; } ?>>
                <label class="form-check-label wcmamtx_icon_checkbox_label">
                    <?php  echo esc_html__('Font Awesome Icon','customize-my-account-for-woocommerce'); ?>
                </label>
            </div>

            <div class="form-check wcmamtx_icon_checkbox">
                <input class="form-check-input wcmamtx_icon_source_radio" type="radio" name="wcmamtx_order_actions[<?php echo $key; ?>][icon_source]"  value="dashicon" <?php if ($icon_source == "dashicon") { echo 'checked'; } ?>>
                <label class="form-check-label wcmamtx_icon_checkbox_label">
                    <?php  echo esc_html__('Dashicon','customize-my-account-for-woocommerce'); ?>
                </label>
            </div>
        </div>
    </td>
    
</tr>

<tr class="fa_icon_tr" style= "<?php if ($icon_source == "custom") { echo 'display:table-row;'; } else { echo 'display:none;'; } ?>">
    <td>
        <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Icon','customize-my-account-for-woocommerce'); ?></label>
    </td>
    <td>

        <input type="text" class="wcmamtx_iconpicker icon-class-input" name="wcmamtx_order_actions[<?php echo $key; ?>][icon]" value="<?php if (isset($value['icon'])) { echo $value['icon']; } ?>">
        <button type="button" class="btn btn-primary picker-button"><?php  echo esc_html__('Chose Font Awesome Icon','customize-my-account-for-woocommerce'); ?></button>
    </td>
    
</tr>

<tr class="show_dashicon_tr" style= "<?php if ($icon_source == "dashicon") { echo 'display:table-row;'; } else { echo 'display:none;'; } ?>">
    <td>
        <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Icon','customize-my-account-for-woocommerce'); ?></label>
    </td>
    <td>

        <input class="regular-text " id="dashicons_picker_example_<?php echo $key; ?>" type="text" name="wcmamtx_order_actions[<?php echo $key; ?>][dashicon]" value="<?php if (isset($value['dashicon'])) { echo $value['dashicon']; } ?>" />
        <input class="button dashicons-picker" type="button" value="<?php  echo esc_html__('Chose Dashicon','customize-my-account-for-woocommerce'); ?>" data-target="#dashicons_picker_example_<?php echo $key; ?>" />

    </td>
    
</tr>

</table>

</div>