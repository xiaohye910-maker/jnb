<?php
$plugin_options = (array) get_option( 'wcmamtx_pro_settings' );

if ( (isset($plugin_options['disable_dashboard_links'])) && ($plugin_options['disable_dashboard_links'] == "yes")) {
    return;
}

$wcmamtx_tabs          =  (array) get_option('wcmamtx_advanced_settings');



$items                 =  wc_get_account_menu_items();

foreach ($items as $itkey=>$itvalue) {
    if (!array_key_exists($itkey, $wcmamtx_tabs)) {

        $new_array = array($itkey=>$itvalue);
        update_option('wcmamtx_tabs_to_add_third_party',$new_array);
    }
}

$core_fields    = 'dashboard,orders,downloads,edit-address,edit-account,customer-logout';

$core_fields_array =  array(
    'dashboard'       => esc_html__('Dashboard','woocommerce'),
    'orders'          => esc_html__('Orders','woocommerce'),
    'downloads'       => esc_html__('Downloads','woocommerce'),
    'edit-address'    => esc_html__('Addresses','woocommerce'),
    'edit-account'    => esc_html__('Account Details','woocommerce'),
    'customer-logout' => esc_html__('Log out','woocommerce')
);






foreach ($items as $ikey=>$ivalue) {

    if (!array_key_exists($ikey, $wcmamtx_tabs) && !array_key_exists($ikey, $core_fields_array) ) {
        
        $match_index = 0;

        foreach ($wcmamtx_tabs as $tkey=>$tvalue) {
            if (isset($tvalue['endpoint_key']) && ($tvalue['endpoint_key'] == $ikey)) {
                $match_index++;
            }
        }

        if ($match_index == 0) {
            $wcmamtx_tabs[$ikey] = array(
              'show' => 'yes',
              'third_party'  => 'yes',
              'endpoint_key' => $ikey,
              'wcmamtx_type' => 'endpoint',
              'parent'       => 'none',
              'endpoint_name'=> $ivalue,
          );   
        }           

    }
}






$wcmamtx_tabs   = apply_filters('wcmamtx_override_dashlinks',$wcmamtx_tabs);


?>
<div class="wcmtx-my-account-links wcmtx-grid">
    <?php foreach ( $wcmamtx_tabs as $key => $value ) : 


        $should_show = 'yes';




        if (isset($value['show']) && ($value['show'] == "no")) {
            
           $should_show = 'no';
           
       }

       if (isset($value['visibleto']) && ($value['visibleto'] != "all")) {

        $allowedroles  = isset($value['roles']) ? $value['roles'] : "";

        $allowedusers  = isset($value['users']) ? $value['users'] : array();

        $is_visible    = wcmamtx_check_role_visibility($allowedroles,$value['visibleto'],$allowedusers);

    } else {

        $is_visible = 'yes';
    }




    
    if (isset($value['endpoint_name']) && ($value['endpoint_name'] != '')) {
        $name = $value['endpoint_name'];
    } else {
        $name = $value;
    }  

    

    $wcmamtx_type = isset($value['wcmamtx_type']) ? $value['wcmamtx_type'] : "default";

    $icon_source = isset($value['icon_source']) ? $value['icon_source'] : "default";

    $hide_in_link_toggle = isset($value['hide_dashboard_links']) && ($value['hide_dashboard_links'] == "01") ? "enabled" : "disabled";

    if (isset($hide_in_link_toggle) && ($hide_in_link_toggle == "enabled")) {
        
       $should_show = 'no';
       
   }

   $third_party = isset($value['third_party']) ? $value['third_party'] : null; 

   $third_party_go_ahead = 'yes';

   if (isset($third_party)) {
     
       $third_party_go_ahead = wcmamtx_third_party_goahead_check($key);

       if ($third_party_go_ahead == "no") {
        $should_show = 'no';
    }
}




            /**
             *  dashboard background color data starts
             */

            $default_color = wcmamtx_get_default_tab_color($key);

            $default_colors = array(
                'dashboard'=>'#93c1a1',
                'orders'   =>'#b4b771',
                'downloads'=>'#e3c5df',
                'edit-address'=>'#9ffcec',
                'edit-account'   =>'#e8b9b0',
                'customer-logout'=>'#dd7575'
            );

            $default_color = isset($default_colors[$key]) ? $default_colors[$key] : $default_color;

            $default_color = isset($value['dash_back_color']) ? $value['dash_back_color'] : $default_color;



            $default_color_font = '#334155';

            $default_color_font = isset($value['dash_font_color']) ? $value['dash_font_color'] : $default_color_font;


            /**
             *  dashboard background color data ends
             */


            if (($wcmamtx_type != "group") && ($should_show == 'yes') && ( $is_visible == "yes")) {
             $key = isset($value['endpoint_key']) ? $value['endpoint_key'] : $key;

             $wcmamtx_plugin_options = (array) get_option('wcmamtx_plugin_options');

             $ajax_class = isset($wcmamtx_plugin_options['ajax_navigation']) && ($wcmamtx_plugin_options['ajax_navigation'] == "yes") ? "wcmamtx_ajax_enabled" : "";
             ?>
             <div class="wcmamtx_dashboard_link <?php echo esc_attr( $key ); ?>-link <?php echo $ajax_class; ?>" style="background-color: <?php echo $default_color; ?>; color:<?php echo $default_color_font; ?>;">
                
                <a href="<?php echo wcmamtx_get_account_endpoint_url( $key ); ?>" style="color:<?php echo $default_color_font; ?>;">

                    <?php wcmamtx_counter_bubble($key,$value); ?>
                    
                    <p class="wcmtx_icon_src" style="color:<?php echo $default_color_font; ?>;">
                       <?php wcmamtx_get_account_menu_li_icon_html($icon_source,$value,$key); ?>
                   </p>

                   <?php echo esc_html( $name ); ?>
                   
               </a>
           </div>
       <?php } ?>
   <?php endforeach; ?>
</div>