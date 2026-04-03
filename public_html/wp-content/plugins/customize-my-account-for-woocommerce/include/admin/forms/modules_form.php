<?php 


$module_settings = (array) get_option( 'wcmamtx_module_settings' );

?> 

<table class="widefat wcmamtx_options_table">

    


    <tr>
      <div class="alert alert-primary" role="alert">
        <?php echo esc_html__('You can enable/disable modules as per your requirement','customize-my-account-for-woocommerce'); ?>
        
      </div>
    </tr>
       
    <tr width="100%">

      <td width="100%">
        <?php 

        $el_widgets1 = array(
          'my-account-fields'    =>esc_html__('My Account Fields','customize-my-account-for-woocommerce'),
          'user-avatar'=>esc_html__('User Avatar (Included)','customize-my-account-for-woocommerce'),
          'elementor-templates'=>esc_html__('User Avatar (Included)','customize-my-account-for-woocommerce'),
          'sample'=>esc_html__('sample','customize-my-account-for-woocommerce')
        );


        $el_widgets2 = array(
          'my-account-fields'    =>esc_html__('My Account Fields','customize-my-account-for-woocommerce'),
          'user-avatar'          =>esc_html__('User Avatar (Included)','customize-my-account-for-woocommerce'),
          'elementor-templates'  =>esc_html__('Elementor Templates (Included)','customize-my-account-for-woocommerce'),
          'Order-actions'        =>esc_html__('Order Actions (Pro Module)','customize-my-account-for-woocommerce'),
          'Order-columns'=>esc_html__('Order Columns (Pro Module)','customize-my-account-for-woocommerce'),
          'Download-columns'=>esc_html__('Download Columns (Pro Module)','customize-my-account-for-woocommerce'),
          'sample'=>esc_html__('sample','customize-my-account-for-woocommerce')
        );



                
        $el_widgets = isset($module_settings['el_widgets']) && !empty($module_settings['el_widgets']) ? $module_settings['el_widgets'] : $el_widgets1;


               
          ?>
          <ul class="wcmamtx_elwidget_ul2">
          <?php

        foreach ($el_widgets2 as $key=>$value) { ?>

          <li class="wcmamtx_module_<?php echo $key; ?>">
            <label><?php echo esc_attr($value); ?></label>

            <?php if ($key != "my-account-fields") { ?>
              <input type="checkbox" data-toggle="toggle" data-on="<?php echo esc_html__('Yes','customize-my-account-for-woocommerce'); ?>" data-off="<?php echo esc_html__('No','customize-my-account-for-woocommerce'); ?>" class="wcmamtx_widget_checkbox" name="wcmamtx_module_settings[el_widgets][<?php echo $key; ?>]" value="yes" <?php if (isset($el_widgets[$key])) { echo 'checked';}?> <?php if (isset($el_widgets2[$key]) && (!isset($el_widgets1[$key]))) { echo 'disabled';}?> >
            <?php } else { ?>
              
              <a class="button-sm button button-primary" target="_blank" href="https://www.sysbasics.com/product/woocommerce-my-account-fields/">
                <?php echo esc_html__( 'Get the Plugin' ,'customize-my-account-for-woocommerce'); ?>
              </a>
            <?php  } ?>
            <?php 

            if (isset($el_widgets2[$key]) && (!isset($el_widgets1[$key]))) { 
               wcmamtx_show_disabled_toggle_image();
            }

             switch($key) {
              case "user-avatar":
                echo '<p class="wcmamtx_module_info">User avatar modules allows users to upload custom avatar instead of gravtar, display username before navigation.</p>';
              break;

              case "my-account-fields":
                echo '<p class="wcmamtx_module_info">Plugin to manage my account fields in my account page , register form , also create custom my account forms to use inside custom endpoints.</p>';
              break;

              case "elementor-templates":
                echo '<p class="wcmamtx_module_info">Elementor users can create custom my account elementor templates, plugin supports all widgets related to my account page, You can override login , register page and much more.</p>';
              break;

              

              

              case "Order-actions":
                echo '<p class="wcmamtx_module_info">Manage existing order actions as well as add new custom order actions. <a href="https://www.sysbasics.com/go/customize/" target="_blank">More Details</a></p>';
              break;

              case "Order-columns":
                echo '<p class="wcmamtx_module_info">Manage existing order Columns as well as add new custom order Columns where you can display user data collected from checkout fields module. <a href="https://www.sysbasics.com/go/customize/" target="_blank">More Details</a></p>';
              break;

              case "Download-columns":
                echo '<p class="wcmamtx_module_info">Manage existing Download Columns. <a href="https://www.sysbasics.com/go/customize/" target="_blank">More Details</a></p>';
              break;

              
            }

             ?>
            
          </li>           

        <?php }

        ?>
          </ul>
      </td>
    </tr>
</table>