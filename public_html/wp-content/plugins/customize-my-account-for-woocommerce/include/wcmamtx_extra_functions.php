<?php

include('wcmamtx_countof_functions.php');
/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_placeholder_img_src')) {

	function sysbasics_menu_item_custom_output( $item_output, $item, $depth, $args ) {

		$menu_item_classes = $item->classes;

    //print_r($item);

		if ( is_array($menu_item_classes) &&  !in_array( 'customize-my-account-for-woocommerce-dropdown', $menu_item_classes ) ) {
			return $item_output;
		}

		if ( !is_user_logged_in() ) {

            $show_only_logged_in    = isset($wcmamtx_plugin_options['show_only_logged_in']) ? $wcmamtx_plugin_options['show_only_logged_in'] : "no";

            if ($show_only_logged_in == "yes") {
                return $items;
            }


            $nav_header_widget_text_logout = isset($wcmamtx_plugin_options['nav_header_widget_text_logout']) ? $wcmamtx_plugin_options['nav_header_widget_text_logout'] : esc_html__('Log In','customize-my-account-for-woocommerce');


            $Menu_link = '<li class="menu-item menu-item-type-post_type menu-item-object-page wcmamtx_menu wcmamtx_menu_logged_out"><a class="menu-link nav-top-link" aria-expanded="true" aria-haspopup="menu"  href="'.$frontend_url.'">'.$nav_header_widget_text_logout.'</a>';

            $items .= $Menu_link;

            return $items;
        
        } 

		ob_start(); 

		$frontend_url = get_permalink(get_option('woocommerce_myaccount_page_id'));


		$wcmamtx_plugin_options = (array) get_option('wcmamtx_plugin_options');

		$nav_header_widget_text = isset($wcmamtx_plugin_options['nav_header_widget_text']) ? $wcmamtx_plugin_options['nav_header_widget_text'] : esc_html__('My Account','customize-my-account-for-woocommerce');

		?>
		<ul class="custom-sub-menu">
			<?php 





            //$items = wcmamtx_get_my_account_menu();

			wcmamtx_get_menu_shortcode_content($items,$item); 



			?>


		</li>
	</ul>
	<?php

	$custom_sub_menu_html = ob_get_clean();

    // Append after <a> element of the menu item targeted
	$item_output = $custom_sub_menu_html;

	return $item_output;
     }
}


/**
 * Check weather module is enabled or not.
 *
 * @since 2.12.0
 * @param string $key equals to module slug.
 * @return string
 */



if (!function_exists('wcmamtx_is_module_enabled')) {

    function wcmamtx_is_module_enabled($key) {

        $matchstick =  "yes";


        $module_settings = (array) get_option( 'wcmamtx_module_settings' );

        $el_widgets1 = array(
          'sample'=>esc_html__('sample','customize-my-account-for-woocommerce'),
          'user-avatar'=>esc_html__('User Avatar (Included)','customize-my-account-for-woocommerce'),
          'elementor-templates'=>esc_html__('Elementor Templates (Included)','customize-my-account-for-woocommerce'),
        );


        $el_widgets2 = array(
          'user-avatar'=>esc_html__('User Avatar (Included)','customize-my-account-for-woocommerce'),
          'elementor-templates'=>esc_html__('Elementor Templates (Included)','customize-my-account-for-woocommerce'),
          'Order-actions'=>esc_html__('Order Actions (Pro Module)','customize-my-account-for-woocommerce'),
          'Order-columns'=>esc_html__('Order Columns (Pro Module)','customize-my-account-for-woocommerce'),
          'Download-columns'=>esc_html__('Download Columns (Pro Module)','customize-my-account-for-woocommerce'),
          'sample'=>esc_html__('sample','customize-my-account-for-woocommerce')
        );

        $el_widgets = isset($module_settings['el_widgets']) && !empty($module_settings['el_widgets']) ? $module_settings['el_widgets'] : $el_widgets1;

        if (isset($el_widgets[$key])) {
            $matchstick =  "yes";
        } else {
            $matchstick =  "no";
        }

        return $matchstick;
    }

}

/**
 * Check weather module is enabled or not.
 *
 * @since 2.12.0
 * @param string $key equals to module slug.
 * @return string
 */



if (!function_exists('wcmamtx_is_module_enabled_init')) {

    function wcmamtx_is_module_enabled_init($key) {

        $matchstick =  "yes";


        $module_settings = (array) get_option( 'wcmamtx_module_settings' );

        

        $el_widgets1 = array(
          'user-avatar',
          'elementor-templates',
          'sample'
        );


        $el_widgets2 = array(
          'user-avatar',
          'elementor-templates',
          'Order-actions',
          'Order-columns',
          'Download-columns',
          'sample'
        );

        $el_widgets = isset($module_settings['el_widgets']) && !empty($module_settings['el_widgets']) ? $module_settings['el_widgets'] : $el_widgets1;

        if (isset($el_widgets[$key])) {
            $matchstick =  "yes";
        } else {
            $matchstick =  "no";
        }

        return $matchstick;
    }

}



/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('load_wcmamtx_optional_class')) {

	function load_wcmamtx_optional_class() {

		$default_class = '';
		
		$advancedsettings  = (array) get_option('wcmamtx_advanced_settings');  

		$tabs              = wc_get_account_menu_items();

		$core_fields       = 'dashboard,orders,downloads,edit-address,edit-account,customer-logout';





		$core_fields_array =  array(
			'dashboard'       => esc_html__('Dashboard','woocommerce'),
			'orders'          => esc_html__('Orders','woocommerce'),
			'downloads'       => esc_html__('Downloads','woocommerce'),
			'edit-address'    => esc_html__('Addresses','woocommerce'),
			'edit-account'    => esc_html__('Account Details','woocommerce'),
			'customer-logout' => esc_html__('Log out','woocommerce')
		);

		$tabs                = apply_filters( 'woocommerce_account_menu_items', $tabs, $core_fields_array );



		$frontend_menu_items = get_option('wcmamtx_frontend_items');




		if ((sizeof($advancedsettings) != 1)) {

			foreach ($tabs as $ikey=>$ivalue) {

				$match = wcmtxka_find_string_match_pro($ikey,$advancedsettings);

				if (!array_key_exists($ikey, $advancedsettings) && !array_key_exists($ikey, $core_fields_array) && ($match == "notfound")) {



					$advancedsettings[$ikey] = array(
						'show' => 'yes',
						'third_party' => 'yes',
						'endpoint_key' => $ikey,
						'wcmamtx_type' => 'endpoint',
						'parent'       => 'none',
						'endpoint_name'=> $ivalue,
					);           

				}
			}






		}





		if (!isset($advancedsettings) || (sizeof($advancedsettings) == 1)) {
			$default_class = "wcmamtx_one_time_save";

		} else {

			$default_class = "";
		}

		return $default_class;

	}

}

/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_placeholder_img_src')) {

	function wcmamtx_placeholder_img_src() {
		return ''.wcmamtx_PLUGIN_URL.'assets/images/placeholder.png';
	}

}


/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_default_tab_color')) {

	function wcmamtx_get_default_tab_color($key) {
		$default_color = '#e9e9ef';

		$default_colors  = array("#8d9897","#d19cdc","#b7e272","#f3a4ba","#c38181","#a4c6f3","#a5eba7","#f3e1a4","#d1c594","#d19cdc5c","#818ea95c","#d9e6ff","#edf2d0");
		$default_color_i = array_rand($default_colors);
        $default_color   = $default_colors[$default_color_i];
		return $default_color;
	}

}



/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_show_disabled_toggle_image')) {

	function wcmamtx_show_disabled_toggle_image() {
		echo '<a href="#" data-toggle="modal" data-target="#wcmamtx_upgrade_modal" class=""><img class="wcmamtx_disabled_image_popup" src="'.wcmamtx_PLUGIN_URL.'assets/images/disabled_pro_toggle.png"></a>';
	}

}



include('wcmamtx_thirdparty_functions.php');


/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_show_disabled_input')) {

	function wcmamtx_show_disabled_input() {
		echo '<a href="#" data-toggle="modal" data-target="#wcmamtx_upgrade_modal" class=""><img class="wcmamtx_disabled_image_popup" src="'.wcmamtx_PLUGIN_URL.'assets/images/disabled_pro_toggle.png"></a>';
	}

}




/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_load_pro_feature_preview')) {

	function wcmamtx_load_pro_feature_preview() { ?>
        <br/>
        <strong style="color:green;"><?php echo esc_html__( 'Lifetime license starts from $39 USD, One time purchase no yearly renewal.' ,'customize-my-account-for-woocommerce'); ?></strong>
        <br/><br/><br/>
		<strong><?php echo esc_html__( 'Pro Version Features' ,'customize-my-account-for-woocommerce'); ?></strong>
		<br/>
		
      
      	<table class="pro_preview_table">

      		<tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Unlimited Endpoints' ,'customize-my-account-for-woocommerce'); ?></td></tr>

      		<tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Unlimited Groups' ,'customize-my-account-for-woocommerce'); ?></td></tr>

      		<tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Custom Content Before and After third party endpoints' ,'customize-my-account-for-woocommerce'); ?></td></tr>

      		<tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Change Default Dashboard Page' ,'customize-my-account-for-woocommerce'); ?></td></tr>

      		<tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Order Columns and Actions' ,'customize-my-account-for-woocommerce'); ?></td></tr>

      		<tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Download Columns' ,'customize-my-account-for-woocommerce'); ?></td></tr>
            
            <tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Horizontal Navigation Menu' ,'customize-my-account-for-woocommerce'); ?></td></tr>

            <tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'Ajax Navigation between Endpoints' ,'customize-my-account-for-woocommerce'); ?></td></tr>

            <tr><td><i class="fa fa-check"></i></td><td><?php echo esc_html__( 'My Account Menu Navigation Widget' ,'customize-my-account-for-woocommerce'); ?></td></tr>

      	</table>
      
		
	<?php 
    }

}




/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_pro_added_endpoint')) {

	function wcmamtx_pro_added_endpoint($value) {
		if ((isset($value["content"]) && ($value["content"] != "")) && (!isset($value["third_party"]) || ($value["third_party"] != "yes"))) {
			$pro_added = "yes";
		} else {
			$pro_added = "yes";
		}

		return $pro_added;
	}

}




if (!function_exists('wcmamtx_get_menu_shortcode_content')) {


	function wcmamtx_get_menu_shortcode_content($items,$item) {

		$frontend_url = get_permalink(get_option('woocommerce_myaccount_page_id'));

		$wcmamtx_plugin_options = (array) get_option('wcmamtx_plugin_options');



		$nav_header_widget_text = isset($item->title) ? $item->title : esc_html__('My Account','customize-my-account-for-woocommerce');




		$widget_show_enabled    = isset($wcmamtx_plugin_options['nav_header_widget']) ? $wcmamtx_plugin_options['nav_header_widget'] : "no";




		if ( !is_user_logged_in() ) {

			$show_only_logged_in    = isset($wcmamtx_plugin_options['show_only_logged_in']) ? $wcmamtx_plugin_options['show_only_logged_in'] : "no";

			if ($show_only_logged_in == "yes") {
				return $items;
			}


			$nav_header_widget_text_logout = isset($wcmamtx_plugin_options['nav_header_widget_text_logout']) ? $wcmamtx_plugin_options['nav_header_widget_text_logout'] : esc_html__('Log In','customize-my-account-for-woocommerce');


			$Menu_link = '<li class="menu-item menu-item-type-post_type menu-item-object-page wcmamtx_menu wcmamtx_menu_logged_out"><a class="menu-link nav-top-link" aria-expanded="true" aria-haspopup="menu"  href="'.$frontend_url.'">'.$nav_header_widget_text_logout.'</a>';

			$items .= $Menu_link;

			echo  $items;

		} 





		$Menu_link  = '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children"><a class="menu-link" href="'.$frontend_url.'">'.$nav_header_widget_text.'<i class="fa fa-chevron-down wcmamtx_nav_chevron"></i></a>';

		$Menu_link .= '<ul class="sub-menu nav-dropdown nav-dropdown-default" style="">';

		$Menu_link .= wcmamtx_get_my_account_menu_plain_li();

		$Menu_link .= '</ul></li>';



		$items .= $Menu_link;

		echo  $items;
	}

}


/**
 * Get account menu item classes.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_account_menu_item_classes')) {

	function wcmamtx_get_account_menu_item_classes( $endpoint,$value ) {

		global $wp;

		$core_fields       = 'dashboard,orders,downloads,edit-address,edit-account,customer-logout';

		$icon_source       = "default";

		switch($icon_source) {

			case "default":
			   $extra_li_class = '';
			break;

			case "noicon":
			   $extra_li_class = 'wcmamtx_no_icon';
			break;

			case "custom":
			   $extra_li_class = 'wcmamtx_custom_icon';
			break;

			case "dashicon":
			   $extra_li_class = 'wcmamtx_custom_icon';
			break;

			default:
			   $extra_li_class = 'wcmamtx_custom_icon';
			break;

		}
        
        

        $classes = array(
        	'woocommerce-MyAccount-navigation-link',
        	'woocommerce-MyAccount-navigation-link--' . $endpoint,
        	''.$extra_li_class.''
        );
        
        
		

	    // Set current item class.
		$current = isset( $wp->query_vars[ $endpoint ] );
		if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
		    $current = true; // Dashboard is not an endpoint, so needs a custom check.
	    } elseif ( 'orders' === $endpoint && isset( $wp->query_vars['view-order'] ) ) {
		    $current = true; // When looking at individual order, highlight Orders list item (to signify where in the menu the user currently is).
	    } elseif ( 'payment-methods' === $endpoint && isset( $wp->query_vars['add-payment-method'] ) ) {
		    $current = true;
	    }
 
	    if ( $current ) {
		    $classes[] = 'is-active';
	    }

	    $classes = apply_filters( 'woocommerce_account_menu_item_classes', $classes, $endpoint );

	    return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
    }
}


// **********************************************************************//
// ! My account menu
// **********************************************************************//

if ( ! function_exists( 'wcmamtx_get_my_account_menu_plain_li' ) ) {
	function wcmamtx_get_my_account_menu_plain_li() {
		$user_info  = get_userdata( get_current_user_id() );
		$user_roles = $user_info->roles;

		$out = '';

		$wcmamtx_tabs   =  (array) get_option('wcmamtx_advanced_settings');

		$items = wc_get_account_menu_items();

		$core_fields    = 'dashboard,orders,downloads,edit-address,edit-account,customer-logout';

		$core_fields_array =  array(
			'dashboard'=>'dashboard',
			'orders'=>'orders',
			'downloads'=>'downloads',
			'edit-address'=>'edit-address',
			'edit-account'=>'edit-account',
			'customer-logout'=>'customer-logout'
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
						'third_party' => 'yes',
						'endpoint_key' => $ikey,
						'wcmamtx_type' => 'endpoint',
						'parent'       => 'none',
						'endpoint_name'=> $ivalue,
					);   
				}           

			}
		}





		$plugin_options = get_option('wcmamtx_plugin_options');

		$icon_position  = 'right';
		$icon_extra_class = '';

		if (!is_array($wcmamtx_tabs)) { 
			$wcmamtx_tabs = $items;
		}

		if (!isset($wcmamtx_tabs) || (sizeof($wcmamtx_tabs) == 1)) {
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





		foreach ( $wcmamtx_tabs as $key => $value ) {
			
			if (isset($value['endpoint_name']) && ($value['endpoint_name'] != '')) {
				$name = $value['endpoint_name'];
			} else {
				$name = $value;
			}

			$should_show = 'yes';


			if (isset($value['visibleto']) && ($value['visibleto'] != "all")) {

				$allowedroles  = isset($value['roles']) ? $value['roles'] : "";
                
                $allowedusers  = isset($value['users']) ? $value['users'] : array();


				$is_visible = wcmamtx_check_role_visibility($allowedroles,$value['visibleto'],$allowedusers);

			} else {

				$is_visible = 'yes';
			}



			if (isset($value['show']) && ($value['show'] == "no")) {

				$should_show = 'no';

			}


			if (isset($value['class']) && ($value['class'] != '')) {
				$extraclass = str_replace(',',' ', $value['class']);
			} else {
				$extraclass = '';
			}

			if (isset($value['endpoint_key']) && ($value['endpoint_key'] != '')) {
				$key = $value['endpoint_key'];
			}

			if (isset($value['parent']) && ($value['parent'] != '')) {
				$parent = $value['parent'];
			} else {
				$parent = 'none';
			}



			$icon_source       = "default";

			$hide_myaccount_widget = isset($value['hide_myaccount_widget']) && ($value['hide_myaccount_widget'] == "01") ? "enabled" : "disabled";

            if (isset($hide_myaccount_widget) && ($hide_myaccount_widget == "enabled")) {
                
                 $should_show = 'no';
                
            }

			if (($should_show == "yes") && ($is_visible == "yes")) {

				if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "group")) {


					$openclose = 'closed';

					$out .='<li class="wcmamtx_group2_sub menu-item menu-item-type-post_type menu-item-object-page '.$extraclass.' closed"><a href="#" class="wcmamtx_group_sub menu-link">'.esc_html( $name ).'&emsp;<i class="fa fa-chevron-down wcmamtx_group_fa"></i></a>';




					
					$m_icon_extra_class = '';






					$all_keys  = get_option('wcmamtx_advanced_settings'); 
					$plugin_options = get_option('wcmamtx_plugin_options'); 

					$matches   = wcmamtx_get_child_li($all_keys, $key); 




					if (sizeof($matches) > 0) { 
						$out .='<ul class="wcmamtx_sub_level" style="display:none;">';

						foreach ($matches as $mkey=>$mvalue) {

							$mkey  = isset($mvalue['endpoint_key']) ? $mvalue['endpoint_key'] : $mkey;

							if (isset($mvalue['endpoint_name']) && ($mvalue['endpoint_name'] != '')) {
								$liname = $mvalue['endpoint_name'];
							} else {
								$liname = $mvalue;
							}

							$should_show = 'yes';



							if (isset($mvalue['show']) && ($mvalue['show'] == "no")) {

								$should_show = 'no';

							}

							if (isset($mvalue['visibleto']) && ($mvalue['visibleto'] != "all")) {

								$allowedroles  = isset($mvalue['roles']) ? $mvalue['roles'] : "";
								$allowedusers  = isset($mvalue['users']) ? $mvalue['users'] : array();

								$is_visible = wcmamtx_check_role_visibility($allowedroles,$mvalue['visibleto'],$allowedusers);

							} else {

								$is_visible = 'yes';
							}

							$icon_source_child       = "default";

							if (isset($mvalue['class']) && ($mvalue['class'] != '')) {
								$mextraclass = str_replace(',',' ', $mvalue['class']);
							} else {
								$mextraclass = '';
							}


							if (($should_show == "yes") && ($is_visible == "yes")) {

								$out .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a class="menu-link sub-menu-link" href="' . wcmamtx_get_account_endpoint_url( $mkey ) . '"><span>' . esc_html( $liname ) . '</span></a></li>';
							}
						}
						$out .='</ul>';
					} 

					$out .='</li>';




				} else {

					if ($parent == "none") {
						$out .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a class="menu-link" href="' . wcmamtx_get_account_endpoint_url( $key ) . '"><span>' . esc_html( $name ) . '</span></a></li>';
					}

				} 
			}
		}

		$out .='';

		return $out;
	}
}




include("wcmamtx_load_icon_functions.php");


/**
 * Get account li html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_account_menu_li_html')) {

	function wcmamtx_get_account_menu_li_html( $name , $key , $value ,$icon_extra_class,$extraclass,$icon_source) { 

		$wsmt_li_fontsize = get_theme_mod('wsmt_li_fontsize');
         
        $font_size = isset($wsmt_li_fontsize) ? $wsmt_li_fontsize : "16px";

        $wsmt_li_padding = get_theme_mod('wsmt_li_padding');
         
        $padding_left = isset($wsmt_li_padding) ? $wsmt_li_padding : "0px";


		?>

		<li  class="<?php echo wcmamtx_get_account_menu_item_classes( $key , $value ); ?> <?php echo $extraclass; ?> <?php if ($icon_source == "custom") { echo $icon_extra_class; } ?>">
			<a class="woocommerce-MyAccount-navigation-link_a"  href="<?php echo wcmamtx_get_account_endpoint_url( $key ); ?>" <?php if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "link") && (isset($value['link_targetblank'])) && ($value['link_targetblank'] == 01) ) { echo 'target="_blank"'; } ?>>
				<?php wcmamtx_get_account_menu_li_icon_html($icon_source,$value,$key); ?>
				<span class="wcmamtx_sticky_icon_name">
					<?php echo esc_html( $name ); ?>
					
				</span>
				<?php 
				$hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";

				if ($hide_sidebar == "no") {
					echo wcmamtx_counter_bubble($key,$value,"yes"); 
				}
				
				?>
			</a>
		</li>

	<?php }
}






/**
 * License activation reminder.
 *
 * @since 1.0.0
 * @param string .
 * @return string
 */

if (!function_exists('wcmamtx_load_pro_reminder_div')) {

	function wcmamtx_load_pro_reminder_div() { 
		?>

		<div class="wcmamtx_notice_div">

			<div class="wcmamtx_notice_div_uppertext">
				<?php echo esc_html__( 'This feature is available in pro version only.','customize-my-account-for-woocommerce'); ?>

			</div>

			<?php wcmamtx_load_pro_feature_preview(); ?>

			<div class="wcmamtx_notice_div_lowerbutton">
				

				

				<a type="button" target="_blank" href="https://sysbasics.com/go/customize/"  class="btn btn-success wcmamtx_frontend_link" >
					<span class="dashicons dashicons-lock"></span>
					<?php echo esc_html__( 'Upgrade to Pro' ,'customize-my-account-for-woocommerce'); ?>
				</a>

				<br><br>

               
			</div>
		</div>

		<?php 
	}
}


/**
 * License activation reminder.
 *
 * @since 1.0.0
 * @param string .
 * @return string
 */

if (!function_exists('wcmamtx_show_limit_info')) {

	function wcmamtx_show_limit_info() { 
		?>

		<div class="wcmamtx_notice_div">

			<div class="wcmamtx_notice_div_uppertext">
				<?php echo esc_html__( 'Free version of plugin only supports 2 endpoint and 2 groups.Pro Version supports unlimited number of endpoints and groups.','customize-my-account-for-woocommerce'); ?>

			</div>

			<div class="wcmamtx_notice_div_lowerbutton">
				

				<a type="button" target="_blank" href="https://sysbasics.com/go/customize/"  class="btn btn-success wcmamtx_frontend_link" >
					<span class="dashicons dashicons-lock"></span>
					<?php echo esc_html__( 'Upgrade to pro' ,'customize-my-account-for-woocommerce'); ?>
				</a>

				<br><br>

                
			</div>
		</div>

		<?php 
	}
}


/**
 * Get account li html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_account_endpoint_url')) {

	function wcmamtx_get_account_endpoint_url($key) {



		$core_url =  esc_url( wc_get_account_endpoint_url( $key ) );


		if (!isset($core_url) || ($core_url == "")) {

			

			if ( 'dashboard' === $key ) {
				return wc_get_page_permalink( 'myaccount' );
			}

			if ( 'customer-logout' === $key ) {
				return wc_logout_url();
			}

			return ''.wc_get_page_permalink( 'myaccount' ).''.$key.'/';
		}


		return apply_filters('wcmamtx_override_endpoint_url',$core_url,$key);

	}
}


/**
 * Get account li html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_account_order_items')) {

	function wcmamtx_get_account_order_items() {

		$columns = array(
			'order-number'  => esc_html__( 'Order', 'customize-my-account-for-woocommerce' ),
			'order-date'    => esc_html__( 'Date', 'customize-my-account-for-woocommerce' ),
			'order-status'  => esc_html__( 'Status', 'customize-my-account-for-woocommerce' ),
			'order-total'   => esc_html__( 'Total', 'customize-my-account-for-woocommerce' ),
			'order-actions' => '&nbsp;',
		);

		return apply_filters('woocommerce_account_orders_columns',$columns);

	}
}


/**
 * Get account li html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_meta_values')) {

	function wcmamtx_get_meta_values( $post_type = 'order', $exclude_empty = false, $exclude_hidden = false)
	{

		$meta_keys = array();
        global $wpdb;
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s'
    ";
    if($exclude_empty) 
        $query .= " AND $wpdb->postmeta.meta_key != ''";
    if($exclude_hidden) 
        $query .= " AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' ";

    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));

    return $meta_keys;


	}
}


/**
 * Get account group html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_account_menu_group_html')) {

	function wcmamtx_get_account_menu_group_html( $name , $key , $value ,$icon_extra_class,$extraclass,$icon_source) { 

		


        if (isset($value['group_open_default']) && ($value['group_open_default'] == "01" )) { 
        	$openclose = 'open'; 

        } else {

        	
            
            $match_index = 0;
            

            $browser_url = $_SERVER['REQUEST_URI'];

            $parts = explode("/", $browser_url);

            $parsed  = isset($parts[1]) ? $parts[1] : "";           
            $parsed2 = isset($parts[2]) ? $parts[2] : "";
            $parsed3 = isset($parts[3]) ? $parts[3] : "";
            $parsed4 = isset($parts[3]) ? $parts[3] : "";
            


			$all_keys  = get_option('wcmamtx_advanced_settings'); 
			$plugin_options = get_option('wcmamtx_plugin_options'); 

			$matches   = wcmamtx_get_child_li($all_keys, $key); 
        	
			if (sizeof($matches) > 0) { 
				foreach ($matches as $mkey=>$mvalue) {

					$mkey  = isset($mvalue['endpoint_key']) ? $mvalue['endpoint_key'] : $mkey;

					if (($parsed == $mkey) || ($parsed2 == $mkey) || ($parsed3 == $mkey) || ($parsed4 == $mkey)) {

                        $match_index++;
                        
					} 
				}
			}

			if ($match_index > 0) {
				$openclose = 'open';
			} else {
				$openclose = 'closed';
			}


        }

		?>

		<li class="wcmamtx_group2 <?php echo wcmamtx_get_account_menu_item_classes( $key , $value ); ?> <?php echo $extraclass; ?> <?php if ($icon_source == "custom") { echo $icon_extra_class; } ?> <?php echo $openclose; ?> ">
			<a href="#" class="wcmamtx_group">
				<?php 
				if ($openclose == 'open') { ?>
					<i class="fa fa-chevron-up wcmamtx_group_fa" ></i>
				<?php } else { ?>
                    <i class="fa fa-chevron-down wcmamtx_group_fa"></i>
				<?php }
				?>
				<?php 
				if ($icon_source == "custom") {
					$icon       = isset($value['icon']) ? $value['icon'] : "";

					if ($icon != '') { ?>
						<i class="<?php echo $icon; ?>"></i>
					<?php }
				} else if ($icon_source == "dashicon") {
					$icon       = isset($value['dashicon']) ? $value['dashicon'] : "";

					if ($icon != '') { ?>
						<span class="dashicons <?php echo $icon; ?>"></span>
					<?php }

				}
				?>
				<span class="wcmamtx_sticky_icon_name"><?php echo esc_html( $name ); ?></span>
			</a>
			<?php


			$m_icon_position  = 'right';
            $m_icon_extra_class = '';

            if (isset($plugin_options['icon_position']) && ($plugin_options['icon_position'] != '')) {
            	$m_icon_position = $plugin_options['icon_position'];
            }



            switch($m_icon_position) {
            	case "right":
            	$m_icon_extra_class = "wcmamtx_custom_right";
            	break;

            	case "left":
            	$m_icon_extra_class = "wcmamtx_custom_left";
            	break;

            	default:
            	$m_icon_extra_class = "wcmamtx_custom_right";
            	break;
            }

            $all_keys  = get_option('wcmamtx_advanced_settings'); 
			$plugin_options = get_option('wcmamtx_plugin_options'); 

			$matches   = wcmamtx_get_child_li($all_keys, $key); 
            
            
			

			if (sizeof($matches) > 0) { ?>
				<ul class="wcmamtx_sub_level" style="<?php if ($openclose == "open") { echo 'display:block;'; } else { echo 'display:none;'; } ?>">
					<?php
					foreach ($matches as $mkey=>$mvalue) {

						$mkey  = isset($mvalue['endpoint_key']) ? $mvalue['endpoint_key'] : $mkey;
						
						if (isset($mvalue['endpoint_name']) && ($mvalue['endpoint_name'] != '')) {
							$liname = $mvalue['endpoint_name'];
						} else {
							$liname = $mvalue;
						}

						$should_show = 'yes';



						if (isset($mvalue['show']) && ($mvalue['show'] == "no")) {

							$should_show = 'no';

						}

						if (isset($mvalue['visibleto']) && ($mvalue['visibleto'] != "all")) {

							$allowedroles  = isset($mvalue['roles']) ? $mvalue['roles'] : "";
							$allowedusers  = isset($mvalue['users']) ? $mvalue['users'] : array();

							$is_visible = wcmamtx_check_role_visibility($allowedroles,$mvalue['visibleto'],$allowedusers);

						} else {

							$is_visible = 'yes';
						}

						$icon_source_child       = isset($mvalue['icon_source']) ? $mvalue['icon_source'] : "default";

						if (isset($mvalue['class']) && ($mvalue['class'] != '')) {
							$mextraclass = str_replace(',',' ', $mvalue['class']);
						} else {
							$mextraclass = '';
						}


						if (($should_show == "yes") && ($is_visible == "yes")) {

							wcmamtx_get_account_menu_li_html( $liname, $mkey ,$mvalue ,$m_icon_extra_class,$mextraclass,$icon_source_child );
					    }
					}
					?>
				</ul>
			<?php } ?>
			
		</li>

	<?php }
}




// **********************************************************************//
// ! My account menu
// **********************************************************************//

if ( ! function_exists( 'wcmamtx_get_my_account_menu' ) ) {
	function wcmamtx_get_my_account_menu() {
		$user_info  = get_userdata( get_current_user_id() );
		$user_roles = $user_info->roles;

		$out = '<ul class="">';

		$wcmamtx_tabs   =  (array) get_option('wcmamtx_advanced_settings');

		$items = wc_get_account_menu_items();

		$core_fields    = 'dashboard,orders,downloads,edit-address,edit-account,customer-logout';

		$core_fields_array =  array(
			'dashboard'=>'dashboard',
			'orders'=>'orders',
			'downloads'=>'downloads',
			'edit-address'=>'edit-address',
			'edit-account'=>'edit-account',
			'customer-logout'=>'customer-logout'
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
						'third_party' => 'yes',
						'endpoint_key' => $ikey,
						'wcmamtx_type' => 'endpoint',
						'parent'       => 'none',
						'endpoint_name'=> $ivalue,
					);   
				}           

			}
		}





		$plugin_options = get_option('wcmamtx_plugin_options');

		$icon_position  = 'right';
		$icon_extra_class = '';

		if (!is_array($wcmamtx_tabs)) { 
			$wcmamtx_tabs = $items;
		}

		if (!isset($wcmamtx_tabs) || (sizeof($wcmamtx_tabs) == 1)) {
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





		foreach ( $wcmamtx_tabs as $key => $value ) {
			
			if (isset($value['endpoint_name']) && ($value['endpoint_name'] != '')) {
				$name = $value['endpoint_name'];
			} else {
				$name = $value;
			}

			$should_show = 'yes';


			if (isset($value['visibleto']) && ($value['visibleto'] != "all")) {

				$allowedroles  = isset($value['roles']) ? $value['roles'] : "";
				$allowedusers  = isset($value['users']) ? $value['users'] : array();



				$is_visible = wcmamtx_check_role_visibility($allowedroles,$value['visibleto'],$allowedusers);

			} else {

				$is_visible = 'yes';
			}



			if (isset($value['show']) && ($value['show'] == "no")) {

				$should_show = 'no';

			}


			if (isset($value['class']) && ($value['class'] != '')) {
				$extraclass = str_replace(',',' ', $value['class']);
			} else {
				$extraclass = '';
			}

			if (isset($value['endpoint_key']) && ($value['endpoint_key'] != '')) {
				$key = $value['endpoint_key'];
			}

			if (isset($value['parent']) && ($value['parent'] != '')) {
				$parent = $value['parent'];
			} else {
				$parent = 'none';
			}



			$icon_source       = "default";

			$hide_myaccount_widget = isset($value['hide_myaccount_widget']) && ($value['hide_myaccount_widget'] == "01") ? "enabled" : "disabled";

            if (isset($hide_myaccount_widget) && ($hide_myaccount_widget == "enabled")) {
                
                 $should_show = 'no';
                
            }

			if (($should_show == "yes") && ($is_visible == "yes")) {

				if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "group")) {


					$openclose = 'closed';

					$out .='<li class="wcmamtx_group2_sub '.wcmamtx_get_account_menu_item_classes( $key , $value ).' '.$extraclass.' closed"><a href="#" class="wcmamtx_group_sub">'.esc_html( $name ).'&emsp;<i class="fa fa-chevron-down wcmamtx_group_fa"></i></a>';




					$m_icon_position  = 'right';
					$m_icon_extra_class = '';

					if (isset($plugin_options['icon_position']) && ($plugin_options['icon_position'] != '')) {
						$m_icon_position = $plugin_options['icon_position'];
					}



					switch($m_icon_position) {
						case "right":
						$m_icon_extra_class = "wcmamtx_custom_right";
						break;

						case "left":
						$m_icon_extra_class = "wcmamtx_custom_left";
						break;

						default:
						$m_icon_extra_class = "wcmamtx_custom_right";
						break;
					}

					$all_keys  = get_option('wcmamtx_advanced_settings'); 
					$plugin_options = get_option('wcmamtx_plugin_options'); 

					$matches   = wcmamtx_get_child_li($all_keys, $key); 




					if (sizeof($matches) > 0) { 
						$out .='<ul class="wcmamtx_sub_level" style="display:none;">';

						foreach ($matches as $mkey=>$mvalue) {

							$mkey  = isset($mvalue['endpoint_key']) ? $mvalue['endpoint_key'] : $mkey;

							if (isset($mvalue['endpoint_name']) && ($mvalue['endpoint_name'] != '')) {
								$liname = $mvalue['endpoint_name'];
							} else {
								$liname = $mvalue;
							}

							$should_show = 'yes';



							if (isset($mvalue['show']) && ($mvalue['show'] == "no")) {

								$should_show = 'no';

							}

							if (isset($mvalue['visibleto']) && ($mvalue['visibleto'] != "all")) {

								$allowedroles  = isset($mvalue['roles']) ? $mvalue['roles'] : "";
								$allowedusers  = isset($mvalue['users']) ? $mvalue['users'] : array();

								$is_visible = wcmamtx_check_role_visibility($allowedroles,$mvalue['visibleto'],$allowedusers);

							} else {

								$is_visible = 'yes';
							}

							$icon_source_child       = "default";

							if (isset($mvalue['class']) && ($mvalue['class'] != '')) {
								$mextraclass = str_replace(',',' ', $mvalue['class']);
							} else {
								$mextraclass = '';
							}


							if (($should_show == "yes") && ($is_visible == "yes")) {

								$out .= '<li class="' . wc_get_account_menu_item_classes( $mkey ) . '"><a href="' . wcmamtx_get_account_endpoint_url( $mkey ) . '"><span>' . esc_html( $liname ) . '</span></a></li>';
							}
						}
						$out .='</ul>';
					} 

					$out .='</li>';




				} else {

					if ($parent == "none") {
						$out .= '<li class="' . wc_get_account_menu_item_classes( $key ) . '"><a href="' . wcmamtx_get_account_endpoint_url( $mkey ) . '"><span>' . esc_html( $name ) . '</span></a></li>';
					}

				} 
			}
		}

		return $out . '</ul>';
	}
}


/**
 * Get parent li items.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_get_child_li')) {


	function wcmamtx_get_child_li($array, $key) {

		$results = array();



		foreach ($array as $subkey=>$subvalue) {

			if (isset($subvalue['parent'])) {

				if ($subvalue['parent'] == $key) {
					$results[$subkey] = $subvalue;
				}
			}

		}

		return $results;
	}

}

/**
 * Get parent li items.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_check_role_visibility')) {

    function wcmamtx_check_role_visibility($allowedroles,$visibile_to,$allowedusers) {

    	$role_status       = 'no';



        switch($visibile_to) {

        	case "specific_exclude":

        	if (isset($allowedroles) && is_array($allowedroles) && (!empty($allowedroles))) {
        		if ( ! is_user_logged_in() ) {
        			$role_status       = 'no';
        			return $role_status; 
        		}

        		$allowedauthors = '';

        		foreach ($allowedroles as $role) {
        			$allowedauthors.=''.$role.',';
        		}

        		$allowedauthors=substr_replace($allowedauthors, "", -1);

        		global $current_user;
        		$user_roles = $current_user->roles;
        		$user_role = array_shift($user_roles);



        		if (!preg_match('/\b'.$user_role.'\b/', $allowedauthors )) {
        			$role_status       = 'yes';
        			return $role_status;
        		}

        	}

        	if (empty($allowedroles) && ( ! is_user_logged_in() )) {
        		$role_status       = 'yes';
        		return $role_status;
        	}

        	break;

        	case "specific":

        	if (isset($allowedroles) && is_array($allowedroles) && (!empty($allowedroles))) {
        		if ( ! is_user_logged_in() ) {
        			$role_status       = 'no';
        			return $role_status; 
        		}

        		$allowedauthors = '';

        		foreach ($allowedroles as $role) {
        			$allowedauthors.=''.$role.',';
        		}

        		$allowedauthors=substr_replace($allowedauthors, "", -1);

        		global $current_user;
        		$user_roles = $current_user->roles;
        		$user_role = array_shift($user_roles);



        		if (preg_match('/\b'.$user_role.'\b/', $allowedauthors )) {
        			$role_status       = 'yes';
        			return $role_status;
        		}

        	}

        	if (empty($allowedroles) && ( ! is_user_logged_in() )) {
        		$role_status       = 'yes';
        		return $role_status;
        	}

        	break;

        	case "specific_exclude_user":



        	if (isset($allowedusers) && is_array($allowedusers) && (!empty($allowedusers))) {

        		if ( ! is_user_logged_in() ) {
        			$user_status       = 'no';
        			return $user_status; 
        		}
                
                $user_match_index = 0;

                $user_id = get_current_user_id();

                foreach ($allowedusers as $alloweduser) {

                	if ($user_id == $alloweduser) {
                		$user_match_index++;
                	}

                }

                if ($user_match_index > 0 ) {
                	$user_status       = 'no';
        			return $user_status; 
                } else {
                	$user_status       = 'yes';
        			return $user_status; 
                }


        	}

        	break;

        	case "specific_user":

        	if (isset($allowedusers) && is_array($allowedusers) && (!empty($allowedusers))) {

        		if ( ! is_user_logged_in() ) {
        			$user_status       = 'no';
        			return $user_status; 
        		}
                
                $user_match_index = 0;

                $user_id = get_current_user_id();

                foreach ($allowedusers as $alloweduser) {

                	if ($user_id == $alloweduser) {
                		$user_match_index++;
                	}

                }

                if ($user_match_index > 0 ) {
                	$user_status       = 'yes';
        			return $user_status; 
                } else {
                	$user_status       = 'no';
        			return $user_status; 
                }


        	}

        	break;

        }


        return $role_status; 
    }
}

/**
 * Show user avatar before natigation items.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_myaccount_customer_avatar')) {

    function wcmamtx_myaccount_customer_avatar() {
	    $current_user = wp_get_current_user();

	    $plugin_options = get_option('wcmamtx_plugin_options');

	    $show_avatar    = isset($plugin_options['show_avatar']) ? $plugin_options['show_avatar'] : "no";
	    $avatar_size    = isset($plugin_options['avatar_size']) ? $plugin_options['avatar_size'] : 200;

	    if (isset($show_avatar) && ($show_avatar == "yes")) {
	    	echo '<div class="wcmamtx_myaccount_avatar">' . get_avatar( $current_user->user_email, $avatar_size , '', $current_user->display_name ) . '</div>';
	    }
    }
}
 
add_action( 'wcmamtx_before_account_navigation', 'wcmamtx_myaccount_customer_avatar', 5 );


if (!function_exists('wcmtxka_find_string_match_pro')) {

	function wcmtxka_find_string_match_pro($string,$array) {

		foreach ($array as $key=>$value) {

			$endpoint_key = isset($value['endpoint_key']) ? $value['endpoint_key'] : $key;
			
            if ($endpoint_key == $string) { // Yoshi version

        	    return 'found';
            }
        }

        return 'notfound';
    }

}

?>