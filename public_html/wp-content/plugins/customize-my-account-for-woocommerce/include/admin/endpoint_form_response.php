<?php
		if( isset( $_POST['wcmamtx_add_endpoint_nonce'] ) && wp_verify_nonce( $_POST['wcmamtx_add_endpoint_nonce'], 'wcmamtx_nonce_hidden') ) {

		
		
			

		if (isset($_POST['nds']['row_type'])) {
			$row_type     = sanitize_text_field($_POST['nds']['row_type']);
		}
		
        if (isset($_POST['nds']['label'])) {
            $new_name      = sanitize_text_field($_POST['nds']['label']);
        }



        $random_number  = mt_rand(100000, 999999);
        $random_number2 = mt_rand(100000, 999999);



        switch($row_type) {
        	case "endpoint":
        	    $new_key   = 'custom-endpoint-'.$random_number.'';
        	break;

        	case "link":
        	    $new_key   = 'custom-link-'.$random_number.'';
            break;

        	case "group":
        	    $new_key   = 'custom-group-'.$random_number.'';
            break;

        	default:
        	    $new_key   = 'custom-endpoint-'.$random_number.'';
            break;
        }


        $new_row_values    = array();

        $advancedsettings  = (array) get_option('wcmamtx_advanced_settings');

        if (!isset($advancedsettings) || (sizeof($advancedsettings) == 1)) {
            $tabs  = wc_get_account_menu_items();

            foreach ($tabs as $key=>$value) {
            
                $new_row_values[$key]['endpoint_key']        = $key;
                $new_row_values[$key]['endpoint_name']       = $value;
                $new_row_values[$key]['wcmamtx_type']        = 'endpoint';
                $new_row_values[$key]['parent']              = 'none';

                $new_row_values[$key]['class']               = isset($value['class']) ? $value['class'] : "";

                
                $new_row_values[$key]['visibleto']           = isset($value['visibleto']) ? $value['visibleto'] : "all";
                $new_row_values[$key]['roles']               = isset($value['roles']) ? $value['roles'] : array();
                $new_row_values[$key]['icon_source']         = "default";
                $new_row_values[$key]['icon']                = isset($value['icon']) ? $value['icon'] : "";
                $new_row_values[$key]['content']             = isset($value['content']) ? $value['content'] : "";
                $new_row_values[$key]['show']                = isset($value['show']) ? $value['show'] : "yes";
                $new_row_values[$key]['upload_icon']         = isset($value['upload_icon']) ? $value['upload_icon'] : "";

            }

        } else {
        	

        	foreach ($advancedsettings as $key2=>$value2) {

        		$key2 = isset($value2['endpoint_key']) ? $value2['endpoint_key'] : $key2;


            
                $new_row_values[$key2]['endpoint_key']        = $key2;
                $new_row_values[$key2]['endpoint_name']       = $value2['endpoint_name'];
                $new_row_values[$key2]['wcmamtx_type']        = $value2['wcmamtx_type'];
                $new_row_values[$key2]['parent']              = $value2['parent'];
                
                $new_row_values[$key2]['class']               = isset($value2['class']) ? $value2['class'] : "";
                $new_row_values[$key2]['visibleto']           = isset($value2['visibleto']) ? $value2['visibleto'] : "all";
                $new_row_values[$key2]['roles']               = isset($value2['roles']) ? $value2['roles'] : array();
                $new_row_values[$key2]['icon_source']         = isset($value2['icon_source']) ? $value2['icon_source'] : "default";
                $new_row_values[$key2]['icon']                = isset($value2['icon']) ? $value2['icon'] : "";
                $new_row_values[$key2]['show']                = isset($value2['show']) ? $value2['show'] : "yes";
                $new_row_values[$key2]['upload_icon']         = isset($value2['upload_icon']) ? $value2['upload_icon'] : "";




                $default_color = wcmamtx_get_default_tab_color($key2);

                $default_color_font = '#334155';


                $new_row_values[$key2]['dash_back_color']                = isset($value2['dash_back_color']) ? $value2['dash_back_color'] : $default_color;
                $new_row_values[$key2]['dash_font_color']         = isset($value2['dash_font_color']) ? $value2['dash_font_color'] : $default_color_font;


                $new_row_values[$key2]['count_of'] = isset($value2['count_of']) ? $value2['count_of'] : "none";
                
                $new_row_values[$key2]['count_bubble'] = isset($value2['count_bubble']) ? $value2['count_bubble'] : null;

                $new_row_values[$key2]['hide_empty'] = isset($value2['hide_empty']) ? $value2['hide_empty'] : null;

                $new_row_values[$key2]['hide_sidebar'] = isset($value2['hide_sidebar']) ? $value2['hide_sidebar'] : null;
                

                $new_row_values[$key2]['third_party']        = isset($value2['third_party']) ? $value2['third_party'] : null;
                

                if (isset($value2['wcmamtx_type']) && ($value2['wcmamtx_type'] == "link")) {
                	$new_row_values[$key2]['link_inputtarget']              = $value2['link_inputtarget'];
                	$new_row_values[$key2]['link_targetblank']              = $value2['link_targetblank'];
                }


                if (isset($value2['wcmamtx_type']) && ($value2['wcmamtx_type'] == "endpoint")) {
                    $new_row_values[$key2]['content']              = isset($value2['content']) ? $value2['content'] : "";
                }



                if (isset($value2['wcmamtx_type']) && ($value2['wcmamtx_type'] == "group")) {

                	$new_row_values[$key2]['group_open_default']   = isset($value2['group_open_default']) ? $value2['group_open_default'] : "no";

                }


                 if ($key2 == "dashboard") {
                    $new_row_values[$key2]['hide_dashboard_hello']            = isset($value2['hide_dashboard_hello']) ? $value2['hide_dashboard_hello'] : 00;
                    $new_row_values[$key2]['hide_intro_hello']                = isset($value2['hide_intro_hello']) ? $value2['hide_intro_hello'] : 00;
                    $new_row_values[$key2]['content_dash']                    = isset($value2['content_dash']) ? $value2['content_dash'] : "";
                }
                
                
            

            }

        }




        	if (isset($new_name) && ($new_name != '')) {
        	    $new_row_values[$new_key]['endpoint_key']        = $new_key;
                $new_row_values[$new_key]['endpoint_name']       = $new_name;
                $new_row_values[$new_key]['wcmamtx_type']        = $row_type;
                $new_row_values[$new_key]['parent']              = 'none';

                if ($row_type == "endpoint") {
                    $new_row_values[$new_key]['content']              = '{default_content}';

                    $wcmamtx_endpoint_allowed_to_add = get_option('wcmamtx_endpoint_allowed_to_add');

                    $wcmamtx_endpoint_allowed_to_add = $wcmamtx_endpoint_allowed_to_add - 1;

                    
                    update_option('wcmamtx_endpoint_allowed_to_add',$wcmamtx_endpoint_allowed_to_add);
                    

                    
                }

                if ($row_type == "group") {


                    $wcmamtx_groups_allowed_to_add = get_option('wcmamtx_groups_allowed_to_add');

                    $wcmamtx_groups_allowed_to_add = $wcmamtx_groups_allowed_to_add - 1;

                    
                    update_option('wcmamtx_groups_allowed_to_add',$wcmamtx_groups_allowed_to_add);

                }

                if ($row_type == "link") {
                    $new_row_values[$new_key]['link_inputtarget']              = esc_url(site_url());
                }
                

            }

        



        

        if (($new_row_values != $advancedsettings) && !empty($new_row_values)) {
        	update_option($this->wcmamtx_notices_settings_page,$new_row_values);
            update_option('wcmamtx_flush_rewrite_cache_required',"yes");

            if ($row_type == 'endpoint') {
                $allowed_endpoints = get_option("wcmamtx_allowed_endpoint_trial");

                if (isset($allowed_endpoints) && ($allowed_endpoints > 0))  {
                    $allowed_endpoints = $allowed_endpoints - 1;
                    update_option('wcmamtx_allowed_endpoint_trial',$allowed_endpoints);
                }
            }
        }

		// add the admin notice
			$admin_notice = "success";

		// redirect the user to the appropriate page
			wp_redirect('admin.php?page=wcmamtx_advanced_settings');
			exit;
		}			
		else {
			wp_die( __( 'Invalid nonce specified','customize-my-account-for-woocommerce' ), __( 'Error','customize-my-account-for-woocommerce' ), array(
				'response' 	=> 403,
				'back_link' => 'admin.php?page=wcmamtx_advanced_settings',

			) );
		}