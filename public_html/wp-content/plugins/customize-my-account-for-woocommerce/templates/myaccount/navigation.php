<?php

/**
 * Navigation.php template
 *
 * Shows main navigation menu.
 *
 * This template can be overridden by copying it to your theme your-theme/sysbasics-myaccount/navigation.php , for better practice create your child theme and copy it to your-child-theme/sysbasics-myaccount/navigation.php.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );



$wcmamtx_tabs          =  (array) get_option('wcmamtx_advanced_settings');

$wcmamtx_pro_settings  = (array) get_option('wcmamtx_pro_settings'); 

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



$plugin_options = get_option('wcmamtx_plugin_options');


$menu_shape = 'vertical';



if (isset($plugin_options['horizontal_menu']) && ($plugin_options['horizontal_menu'] == "yes")) {

    $menu_shape = 'horizontal';

} else {

    $menu_shape = 'vertical';
}

$icon_position  = 'right';
$icon_extra_class = '';

if (!is_array($wcmamtx_tabs)) { 
    $wcmamtx_tabs = $items;
    
}

if (!isset($wcmamtx_tabs) || (sizeof($wcmamtx_tabs) === 1) || isset($wcmamtx_tabs[0])) {
    $wcmamtx_tabs = $items;
    
}



if (isset($plugin_options['icon_position']) && ($plugin_options['icon_position'] != '')) {
    $icon_position = $plugin_options['icon_position'];
}

if (isset($plugin_options['menu_position']) && ($plugin_options['menu_position'] != '')) {
    $menu_position = $plugin_options['menu_position'];
}



switch($icon_position) {
	case "right":
	   $icon_extra_class = "wcmamtx_custom_right";
	break;

	case "left":
	   $icon_extra_class = "wcmamtx_custom_left";
	break;

	default:
	   $icon_extra_class = "wcmamtx_custom_right";
	break;
}

$menu_position_extra_class = "";

if (isset($menu_position) && ($menu_position != '')) {
    switch($menu_position) {
        case "left":
        $menu_position_extra_class = "wcmamtx_menu_left";
        break;

        case "right":
        $menu_position_extra_class = "wcmamtx_menu_right";
        break;

        default:
        $menu_position_extra_class = "";
        break;
    }
}






if ($menu_shape == 'vertical') {

   
    include ("vertical_menu_shape.php");
   
  
 
} else {



    include ("menu_default.php");


}

do_action( 'woocommerce_after_account_navigation' ); ?>