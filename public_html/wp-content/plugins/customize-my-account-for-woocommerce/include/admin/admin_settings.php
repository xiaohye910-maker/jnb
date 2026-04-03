<?php

if (!class_exists('wcmamtx_add_settings_page_class')) {

class wcmamtx_add_settings_page_class {
	
	

	private $wcmamtx_plugin_options_key    = 'wcmamtx_plugin_options';
	private $wcmamtx_notices_settings_page = 'wcmamtx_advanced_settings';
	private $wcmamtx_order_settings_page   = 'wcmamtx_module_settings';
	private $wcmamtx_order_actions_page    = 'wcmamtx_order_actions';
    private $wcmamtx_avatar_settings_page  = 'wcmamtx_avatar_settings';
    private $wcmamtx_wizard_page           = 'wcmamtx_wizard_settings';
    private $wcmamtx_pro_settings          = 'wcmamtx_pro_settings';
	private $wcmamtx_plugin_settings_tab   = array();
	

	
	public function __construct() {
		
		add_action( 'admin_init', array( $this, 'wcmamtx_register_settings_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) ,100);
		add_action( 'admin_enqueue_scripts', array($this, 'wcmamtx_register_admin_scripts'));
		add_action( 'admin_enqueue_scripts', array($this, 'wcmamtx_load_admin_menu_style'));
        add_action( 'wp_ajax_restore_my_account_tabs', array( $this, 'restore_my_account_tabs' ) );
        add_action( 'wp_ajax_restore_my_account_order', array( $this, 'restore_my_account_order' ) );
        add_action( 'wp_ajax_wcmamtxadmin_add_new_template', array( $this, 'wcmamtxadmin_add_new_template' ) );
        add_action( 'wp_ajax_get_elementor_templates', array( $this, 'wcmamtx_get_posts_ajax_callback' ) );
        add_action( 'admin_post_nds_form_response_endpoint', array( $this, 'add_endpoint_form_response' ));
        add_action( 'admin_post_nds_form_response_column', array( $this, 'add_column_form_response' ));
		add_action( 'admin_post_nds_form_response_action', array( $this, 'add_action_form_response' ));
        add_action( 'wp_ajax_wcmamtxadmin_get_users_ajax', array( $this, 'wcmamtxadmin_get_users_ajax_function' ) );
        add_action( 'wp_ajax_wcmamtx_dismiss_renew_notice', array( $this, 'wcmamtx_dismiss_renew_notice_function' ) );

         add_action( 'wp_ajax_wcmamtx_dismiss_dashboard_text_notice', array( $this, 'wcmamtx_dismiss_dashboard_text_notice_function' ) );

         add_action( 'wp_ajax_nopriv_wcmamtx_dismiss_dashboard_text_notice', array( $this, 'wcmamtx_dismiss_dashboard_text_notice_function' ) );

         add_action( 'wp_ajax_wcmamtx_dismiss_dashboard_text_notice2', array( $this, 'wcmamtx_dismiss_dashboard_text_notice_function2' ) );

         add_action( 'wp_ajax_nopriv_wcmamtx_dismiss_dashboard_text_notice2', array( $this, 'wcmamtx_dismiss_dashboard_text_notice_function2' ) );
        
	}


    



    public function wcmamtx_dismiss_renew_notice_function() {

         update_option("wcmamtx_dismiss_renew_notice_permanately","yes");

         die;

    }

    public function wcmamtx_dismiss_dashboard_text_notice_function() {

         update_option("wcmamtx_dismiss_dashboard_text_notice_permanately","yes");

         die;

    }


    public function wcmamtx_dismiss_dashboard_text_notice_function2() {

         update_option("wcmamtx_dismiss_dashboard_text_notice_permanately2","yes");

         die;

    }



    public function wcmamtxadmin_get_users_ajax_function() {
        $return = array();



        $users = new WP_User_Query( array(
            'search'         => '*'.esc_attr( $_GET['q'] ).'*',
            'search_columns' => array(
                'user_login',
                'user_nicename',
                'user_email',
                'user_url',
            ),
        ) );
        $users_found = $users->get_results();

        

        foreach ($users_found as $ukey=>$uvalue) {
            $return[] = array($uvalue->ID,$uvalue->user_login);
        }

        


        echo json_encode( $return );
        die;
    }



    public function add_action_form_response() {

        if( isset( $_POST['wcmamtx_add_action_nonce'] ) && wp_verify_nonce( $_POST['wcmamtx_add_action_nonce'], 'wcmamtx_nonce_hidden_action') ) {


        
        if (isset($_POST['ndsaction']['label'])) {
            $new_name      = sanitize_text_field($_POST['ndsaction']['label']);

            $random_number  = mt_rand(100000, 999999);
            
            $new_key   = 'custom-action-'.$random_number.'';
        }


    


        $new_row_values    = array();

        $advancedsettings  = (array) get_option('wcmamtx_order_actions');

        

        if ((isset($advancedsettings))  && (sizeof($advancedsettings) >= 1) && (!array_key_exists(0, $advancedsettings))) {

            foreach ($advancedsettings as $key2=>$value2) {

                
                $new_row_values[$key2]['endpoint_key']        = isset($value2['endpoint_key']) ? $value2['endpoint_key'] : $key2;;
                $new_row_values[$key2]['endpoint_name']       = $value2['endpoint_name'];
                $new_row_values[$key2]['wcmamtx_type']        = $value2['wcmamtx_type'];
                $new_row_values[$key2]['parent']              = 'none';



            }

        }
            



        if (isset($new_name) && ($new_name != '')) {
            $new_row_values[$new_key]['endpoint_key']        = $new_key;
            $new_row_values[$new_key]['endpoint_name']       = $new_name;
            $new_row_values[$new_key]['wcmamtx_type']        = $row_type;
            $new_row_values[$new_key]['parent']              = 'none';

        }

        


       
        

        if (($new_row_values != $advancedsettings) && !empty($new_row_values)) {
            update_option('wcmamtx_order_actions',$new_row_values);
        }


        

        // redirect the user to the appropriate page
            wp_redirect('admin.php?page=wcmamtx_advanced_settings&tab=wcmamtx_order_actions');
            exit;
        }           
        else {
            wp_die( __( 'Invalid nonce specified','customize-my-account-for-woocommerce' ), __( 'Error','customize-my-account-for-woocommerce' ), array(
                'response'  => 403,
                'back_link' => 'admin.php?page=wcmamtx_advanced_settings&tab=wcmamtx_order_actions',

            ) );
        }

    }

    public function add_column_form_response() {
        
        if( isset( $_POST['wcmamtx_add_column_nonce'] ) && wp_verify_nonce( $_POST['wcmamtx_add_column_nonce'], 'wcmamtx_nonce_hidden_column') ) {

        
        
            


        
        if (isset($_POST['ndscolumn']['label'])) {
            $new_name      = sanitize_text_field($_POST['ndscolumn']['label']);
        }


        $random_number  = mt_rand(100000, 999999);
        $random_number2 = mt_rand(100000, 999999);

        $row_type = 'endpoint';



        switch($row_type) {
            case "endpoint":
                $new_key   = 'custom-order-'.$random_number.'';
            break;

            
            default:
                $new_key   = 'custom-order-'.$random_number.'';
            break;
        }


        $new_row_values    = array();

        $advancedsettings  = (array) get_option('wcmamtx_order_settings');

        if (!isset($advancedsettings) || (sizeof($advancedsettings) == 1)) {
            $tabs  = wcmamtx_get_account_order_items();

            foreach ($tabs as $key=>$value) {
            
                $new_row_values[$key]['endpoint_key']        = $key;
                $new_row_values[$key]['endpoint_name']       = $value;
                $new_row_values[$key]['wcmamtx_type']        = 'endpoint';
                $new_row_values[$key]['parent']              = 'none';


            }

        } else {
            

            foreach ($advancedsettings as $key2=>$value2) {

                
            
                $new_row_values[$key2]['endpoint_key']        = isset($value2['endpoint_key']) ? $value2['endpoint_key'] : $key2;
                $new_row_values[$key2]['endpoint_name']       = $value2['endpoint_name'];
                $new_row_values[$key2]['wcmamtx_type']        = $value2['wcmamtx_type'];
                $new_row_values[$key2]['parent']              = 'none';
                
                $new_row_values[$key2]['show']                = isset($value2['show']) ? $value2['show'] : "yes";



            }

        }




            if (isset($new_name) && ($new_name != '')) {
                $new_row_values[$new_key]['endpoint_key']        = $new_key;
                $new_row_values[$new_key]['endpoint_name']       = $new_name;
                $new_row_values[$new_key]['wcmamtx_type']        = $row_type;
                $new_row_values[$new_key]['parent']              = 'none';

            }

        



        

        if (($new_row_values != $advancedsettings) && !empty($new_row_values)) {
            update_option('wcmamtx_order_settings',$new_row_values);
        }



        

        // redirect the user to the appropriate page
            wp_redirect('admin.php?page=wcmamtx_advanced_settings&tab=wcmamtx_order_settings');
            exit;
        }           
        else {
            wp_die( __( 'Invalid nonce specified','customize-my-account-for-woocommerce' ), __( 'Error','customize-my-account-for-woocommerce' ), array(
                'response'  => 403,
                'back_link' => 'admin.php?page=wcmamtx_advanced_settings&tab=wcmamtx_order_settings',

            ) );
        }
    }


	public function add_endpoint_form_response() {
		
           include("endpoint_form_response.php");
	}


	public function wcmamtx_get_posts_ajax_callback(){
 
	
	  $return          = array();
	  
      $post_type_array = array('elementor_library');
	  // you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	  $search_results  = new WP_Query( array( 
		's'                   => sanitize_text_field($_GET['q']), // the search query
		'post_status'         => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'post_type'           => $post_type_array
	  ) );
	  



	  if( $search_results->have_posts() ) {
		while( $search_results->have_posts() ) : $search_results->the_post();

		    $product_type = WC_Product_Factory::get_product_type($search_results->post->ID);	
			// shorten the title a little
			

			
				 $finaltitle=''.$search_results->post->post_title.'';
				 $return[] = array( $search_results->post->ID, $finaltitle );
			
			
			  

			 // array( Post ID, Post Title )
		endwhile;
	  } 
	   echo json_encode( $return );
	  die;
    }


	public function wcmamtxadmin_add_new_template() {

		
        /* First, check nonce */
        check_ajax_referer( 'wcmamtx_nonce', 'security' );
        check_ajax_referer( 'wcmamtx_nonce_hidden', 'nonce' );
        
        if (isset($_POST['row_type'])) {
            $row_type     = sanitize_text_field($_POST['row_type']);
        }
        
        if (isset($_POST['new_row'])) {
            $new_name      = sanitize_text_field($_POST['new_row']);

        }




        $new = array(
            'post_title' => $new_name,
            'post_status' => 'publish',
            'post_type' => 'elementor_library'
        );

        $post_id = wp_insert_post( $new );



        if (isset($post_id) && isset($row_type) && ($row_type != "") ) {
            
            switch($row_type) {

                

                case "myaccount":
                   $content = '[{"id":"a1ab84e","elType":"section","settings":[],"elements":[{"id":"ab99e3e","elType":"column","settings":{"_column_size":100,"_inline_size":null},"elements":[{"id":"f41d127","elType":"widget","settings":{"shortcode":"[woocommerce_my_account]","_margin":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true},"_padding":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true}},"elements":[],"widgetType":"shortcode"}],"isInner":false}],"isInner":false}]';
                break;


                case "cart":
                   $content = '[{"id":"a1ab84e","elType":"section","settings":[],"elements":[{"id":"ab99e3e","elType":"column","settings":{"_column_size":100,"_inline_size":null},"elements":[{"id":"f41d127","elType":"widget","settings":{"shortcode":"[woocommerce_cart]","_margin":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true},"_padding":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true}},"elements":[],"widgetType":"shortcode"}],"isInner":false}],"isInner":false}]';
                break;


                case "checkout":
                   $content = '[{"id":"a1ab84e","elType":"section","settings":[],"elements":[{"id":"ab99e3e","elType":"column","settings":{"_column_size":100,"_inline_size":null},"elements":[{"id":"f41d127","elType":"widget","settings":{"shortcode":"[woocommerce_checkout]","_margin":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true},"_padding":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true}},"elements":[],"widgetType":"shortcode"}],"isInner":false}],"isInner":false}]';
                break;


                case "orders":
                   $content = '[{"id":"06f0109","elType":"section","settings":[],"elements":[{"id":"5c84546","elType":"column","settings":{"_column_size":100,"_inline_size":null},"elements":[{"id":"7bd5aca","elType":"widget","settings":[],"elements":[],"widgetType":"my_orders_widget"}],"isInner":false}],"isInner":false}]';
                break;

                

                default:
                   $content = "";
                break;

            }


            if ($content != "") {

                update_post_meta( $post_id, '_elementor_data', $content );

            }
            

        }

        $elementor_edit_link = ''.admin_url().'post.php?post='.$post_id.'&action=elementor';

        $result = array('redirect_url'=>$elementor_edit_link,'id'=>$post_id,'text'=>get_the_title($post_id));

        echo json_encode( $result );

        die();
	}


	public function wcmamtx_load_admin_menu_style() {

	    wp_enqueue_style( 'woomatrix_admin_menu_css', ''.wcmamtx_PLUGIN_URL.'assets/css/admin_menu.css' );
	    wp_enqueue_script( 'woomatrix_admin_menu_js', ''.wcmamtx_PLUGIN_URL.'assets/js/admin_menu.js' );

	}


	public function restore_my_account_tabs() {
	    if( current_user_can('editor') || current_user_can('administrator') ) {

	    	if ( !wp_verify_nonce($_POST['nonce'], 'wcmamtx_nonce') ){ 
				die(); 
			}
	        delete_option( $this->wcmamtx_notices_settings_page );

            delete_option('wcmamtx_endpoint_allowed_to_add');
            delete_option('wcmamtx_groups_allowed_to_add');
            
            
        } 
	   die();
	}


	public function restore_my_account_order() {
	    if( current_user_can('editor') || current_user_can('administrator') ) {

	    	if ( !wp_verify_nonce($_POST['nonce'], 'wcmamtx_nonce') ){ 
				die(); 
			}
			
	        delete_option( 'wcmamtx_order_settings' );
        } 
	   die();
	}
	
	




	
	/*
	 * registers admin scripts via admin enqueue scripts
	 */
	public function wcmamtx_register_admin_scripts($hook) {
	    global $general_wcmamtxsettings_page;



			
		if ( $hook == $general_wcmamtxsettings_page )  {

		    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : "wcmamtx_advanced_settings";
 
            wp_enqueue_style( 'wcmamtx_fontawesome', ''.wcmamtx_PLUGIN_URL.'assets/css/font-awesome.min.css');

            
            wp_enqueue_script( 'wcmamtx_bootstrap', ''.wcmamtx_PLUGIN_URL.'assets/js/bootstrap.min.js');
            wp_enqueue_script( 'wcmamtx_bootstrap_toggle', ''.wcmamtx_PLUGIN_URL.'assets/js/bootstrap4-toggle.min.js');
            wp_enqueue_style( 'wcmamtx_bootstrap', ''.wcmamtx_PLUGIN_URL.'assets/css/bootstrap.min.css');
            wp_enqueue_style( 'wcmamtx_bootstrap_toggle', ''.wcmamtx_PLUGIN_URL.'assets/css/bootstrap4-toggle.min.css');

		    wp_enqueue_script( 'select2', ''.wcmamtx_PLUGIN_URL.'assets/js/select2.js' );

		    if (isset($current_tab) && (($current_tab == "wcmamtx_advanced_settings") || ($current_tab == "wcmamtx_pro_settings") )) {

                wp_enqueue_style( 'wp-color-picker' );

		      wp_enqueue_script( 'wcmamtxadmin', ''.wcmamtx_PLUGIN_URL.'assets/js/admin.js',array('jquery-ui-accordion','wp-color-picker'), '1.0.0', true );

		    } else if (isset($current_tab) && ($current_tab == "wcmamtx_order_settings")) {
		    	wp_enqueue_script( 'wcmamtxsortable', ''.wcmamtx_PLUGIN_URL.'assets/js/sortable.js' );
		    	wp_enqueue_script( 'wcmamtxsortable2', ''.wcmamtx_PLUGIN_URL.'assets/js/sortable2.js');

		    	wp_enqueue_script( 'wcmamtxadmin', ''.wcmamtx_PLUGIN_URL.'assets/js/admin2.js',array('jquery-ui-accordion'), '1.0.0', true );

		    } else if (isset($current_tab) && ($current_tab == "wcmamtx_order_actions")) {

		    	wp_enqueue_script( 'wcmamtxsortable', ''.wcmamtx_PLUGIN_URL.'assets/js/sortable.js' );
		    	wp_enqueue_script( 'wcmamtxsortable2', ''.wcmamtx_PLUGIN_URL.'assets/js/sortable2.js');

		    	wp_enqueue_script( 'wcmamtxadmin', ''.wcmamtx_PLUGIN_URL.'assets/js/admin3.js',array('jquery-ui-accordion'), '1.0.0', true );

		    } else if (isset($current_tab) && ($current_tab == "wcmamtx_plugin_options")) {

		    	wp_enqueue_script( 'wcmamtxadmin', ''.wcmamtx_PLUGIN_URL.'assets/js/admin.js',array('jquery-ui-accordion'), '1.0.0', true );

		    } else if (isset($current_tab) && ($current_tab == "wcmamtx_wizard_settings")) {

                wp_enqueue_style( 'wcmamtx-bdwizard', ''.wcmamtx_PLUGIN_URL.'assets/css/bd-wizard.css');

                

                wp_enqueue_script( 'wcmamtxsteps', ''.wcmamtx_PLUGIN_URL.'assets/js/jquery.steps.min.js',array('wcmtx_steps_jquery'), '1.0.0', true );

                wp_enqueue_script( 'wcmamtxstepsbdwizard', ''.wcmamtx_PLUGIN_URL.'assets/js/bd-wizard.js',array('wcmtx_steps_jquery'), '1.0.0', true );



               

            }

		    wp_enqueue_script( 'wcmamtx-dashicons', ''.wcmamtx_PLUGIN_URL.'assets/js/dashicons-picker.js');

		    wp_enqueue_style( 'wcmamtx-dashicons', ''.wcmamtx_PLUGIN_URL.'assets/css/dashicons-picker.css');
		
            wp_enqueue_script( 'wcmamtx-tageditor', ''.wcmamtx_PLUGIN_URL.'assets/js/tageditor.js');
		    wp_enqueue_style( 'wcmamtx-tageditor', ''.wcmamtx_PLUGIN_URL.'assets/css/tageditor.css');

	        wp_enqueue_style( 'jquery-ui-core', ''.wcmamtx_PLUGIN_URL.'assets/css/jquery-ui.css' );
            wp_enqueue_style( 'select2',''.wcmamtx_PLUGIN_URL.'assets/css/select2.css');
		 
		    wp_enqueue_style( 'wcmamtxadmin', ''.wcmamtx_PLUGIN_URL.'assets/css/admin.css' );


		 
		    $wcmamtx_js_array = array(
                'new_row_alert_text'   => esc_html__( 'Enter name for new endpoint' ,'customize-my-account-for-woocommerce'),
                'new_group_alert_text' => esc_html__( 'Enter name for new group' ,'customize-my-account-for-woocommerce'),
                'new_link_alert_text'  => esc_html__( 'Enter name for new link' ,'customize-my-account-for-woocommerce'),
                'group_mixing_text'    => esc_html__( 'Group can not be dropped into group' ,'customize-my-account-for-woocommerce'),
                'restorealert'         => esc_html__( 'Are you sure you want to restore to default my account tabs ? you can not undo this.' ,'customize-my-account-for-woocommerce'),
                'endpoint_remove_alert'   => esc_html__( "Are you sure you want to delete this ?" ,'customize-my-account-for-woocommerce'),
                'endpoint_remove_alert_thirdparty'   => esc_html__( "This is third party endpoint, if parent plugin is active, it will keep coming back. Only delete after parent plugin is deactivated otherwise just hide it." ,'customize-my-account-for-woocommerce'),
                'core_remove_alert'     => esc_html__( "this group has core endpoints. please move them before removing this group" ,'customize-my-account-for-woocommerce'),
                'dt_type'               => wcmamtx_get_version_type(),
                'pro_notice'            => esc_html__( 'This feature is available in pro version only.' ,'customize-my-account-for-woocommerce'),
                'empty_label_notice'    => esc_html__( 'Label can not be empty.' ,'customize-my-account-for-woocommerce'),
                'nonce'                 => wp_create_nonce( 'wcmamtx_nonce' ),
                'ajax_url'              => admin_url( 'admin-ajax.php' ),
                'wait_text'             => esc_html__( 'Adding....' ,'customize-my-account-for-woocommerce'),
                'order_action_text'     => esc_html__( 'Plugin do not support movement of order actions yet' ,'customize-my-account-for-woocommerce'),
                'chose_template'        => esc_html__( 'Choose Template' ,'customize-my-account-for-woocommerce'),
                'uploadimage'           => esc_html__( 'Choose an image' ,'customize-my-account-for-woocommerce'),
                'useimage'              => esc_html__( 'Use Image' ,'customize-my-account-for-woocommerce'),
                'placeholder'           => wcmamtx_placeholder_img_src(),
                'chosebulkaction'       => esc_html__( 'No Action Selected' ,'customize-my-account-for-woocommerce'),
                'firstsucess'       => esc_html__( 'Bulk Action Applied to all sucessfully.Make sure to save changes.' ,'customize-my-account-for-woocommerce'),
                
            );

            wp_localize_script( 'wcmamtxadmin', 'wcmamtxadmin', $wcmamtx_js_array );

        }
	}
	
	

	
	
	public function wcmamtx_register_settings_settings() {

        $user_avatar_enable = wcmamtx_is_module_enabled("user-avatar");

        $elementor_module_enable = wcmamtx_is_module_enabled("elementor-templates");

                
        


		$this->wcmamtx_plugin_settings_tab[$this->wcmamtx_notices_settings_page] = esc_html__( 'Endpoints' ,'customize-my-account-for-woocommerce');

		$this->wcmamtx_plugin_settings_tab[$this->wcmamtx_order_settings_page] = esc_html__( 'Modules' ,'customize-my-account-for-woocommerce');

        if (isset($user_avatar_enable) && ($user_avatar_enable == "yes")) { 

            $this->wcmamtx_plugin_settings_tab[$this->wcmamtx_avatar_settings_page] = esc_html__( 'User Avatar' ,'customize-my-account-for-woocommerce');

            register_setting( $this->wcmamtx_avatar_settings_page, $this->wcmamtx_avatar_settings_page );

            add_settings_section( 'wcmamtx_avatar_section', '', '', $this->wcmamtx_avatar_settings_page );

            add_settings_field( 'avatar_option', '', array( $this, 'wcmamtx_avatar_page' ), $this->wcmamtx_avatar_settings_page, 'wcmamtx_avatar_section' );

        }


        if (isset($elementor_module_enable) && ($elementor_module_enable == "yes")) { 

           $this->wcmamtx_plugin_settings_tab[$this->wcmamtx_plugin_options_key]    = esc_html__( 'Elementor Templates' ,'customize-my-account-for-woocommerce');


           register_setting( $this->wcmamtx_plugin_options_key, $this->wcmamtx_plugin_options_key );

           add_settings_section( 'wcmamtx_general_section', '', '', $this->wcmamtx_plugin_options_key );

           add_settings_field( 'general_option', '', array( $this, 'wcmamtx_options_page' ), $this->wcmamtx_plugin_options_key, 'wcmamtx_general_section' );

       }

        

        $this->wcmamtx_plugin_settings_tab[$this->wcmamtx_wizard_page] = esc_html__( 'Setup Wizard' ,'customize-my-account-for-woocommerce');
       

		

		register_setting( $this->wcmamtx_notices_settings_page, $this->wcmamtx_notices_settings_page );

		add_settings_section( 'wcmamtx_advance_section', '', '', $this->wcmamtx_notices_settings_page );

		add_settings_field( 'advanced_option', '', array( $this, 'linked_product_swatches_settings' ), $this->wcmamtx_notices_settings_page, 'wcmamtx_advance_section' );


		register_setting( $this->wcmamtx_order_settings_page, $this->wcmamtx_order_settings_page );

		add_settings_section( 'wcmamtx_order_section', '', '', $this->wcmamtx_order_settings_page );

		add_settings_field( 'order_option', '', array( $this, 'linked_product_swatches_order' ), $this->wcmamtx_order_settings_page, 'wcmamtx_order_section' );

        

        



        


        
		register_setting( $this->wcmamtx_wizard_page, $this->wcmamtx_wizard_page );

        add_settings_section( 'wcmamtx_wizard_section', '', '', $this->wcmamtx_wizard_page );

        add_settings_field( 'wizard_option', '', array( $this, 'wcmamtx_wizard_page' ), $this->wcmamtx_wizard_page, 'wcmamtx_wizard_section' );


        $this->wcmamtx_plugin_settings_tab[$this->wcmamtx_pro_settings] = esc_html__( 'Settings' ,'customize-my-account-for-woocommerce');


        register_setting( $this->wcmamtx_pro_settings, $this->wcmamtx_pro_settings );

        add_settings_section( 'wcmamtx_pro_settings_section', '', '', $this->wcmamtx_pro_settings );

        add_settings_field( 'pro_settings_option', '', array( $this, 'wcmamtx_pro_settings_page' ), $this->wcmamtx_pro_settings, 'wcmamtx_pro_settings_section' );

		

	}

    public function wcmamtx_pro_settings_page() {
        include ('forms/pro_settings.php');
    }


    public function wcmamtx_wizard_page() {

        include ('forms/wizard_form.php');

    }

    public function wcmamtx_avatar_page() {
        include ('forms/avatar_form.php');
    }







	/**
      * Recursive sanitation for an array
      * 
      * @param $array
      *
      * @return mixed
      */
	public function recursive_sanitize_text_field($array) {
		foreach ( $array as $key => $value ) {

			$value = sanitize_text_field( $value );

		}

		return $array;
	}
	

	

	

	/*
     * Linked product swatached settings
     * includes form field from forms folder
     */
	
	public function linked_product_swatches_settings() { 

        $permalink_structure = get_option( 'permalink_structure' );

        if ($permalink_structure == "") {
            ?>

            <div class="wcmamtx_notice_div review">

                <div class="wcmamtx_notice_div_uppertext">
                    <?php echo esc_html__( 'Looks like your site is using default permalink structure (?p=123), which is not recommended and could cause broken links on frontend, consider changing it to something else.','customize-my-account-for-woocommerce'); ?>

                    <a type="button" target="_blank" href="options-permalink.php"  class="" >

                        <?php echo esc_html__( 'Change Permalink Structure' ,'customize-my-account-for-woocommerce'); ?>
                    </a>

                    
                </div>
            </div>

            <?php
        }


        include ('forms/settings_form.php');

    }


	/*
     * Linked product swatached settings
     * includes form field from forms folder
     */
	
	public function linked_product_swatches_order() { 
         
         include ('forms/modules_form.php');
        
    }


	/*
     * Linked product swatached settings
     * includes form field from forms folder
     */
	
	public function linked_product_swatches_order_actions() { 


         wcmamtx_load_pro_reminder_div();
	   
		   
	}




    /*
     * Plugin options page
     * 
     */
    
    public function wcmamtx_options_page() { 

       include ('forms/options_form.php');
           
    }

	
	
	/*
     * Adds Admin Menu "cart notices"
     * global $general_wcmamtxsettings_page is used to include page specific scripts
     */

	public function add_admin_menus() {
	    global $general_wcmamtxsettings_page;
        
        add_menu_page(
          __( 'SysBasics', 'customize-my-account-for-woocommerce' ),
         'SysBasics',
         'manage_woocommerce',
         'sysbasics',
         array($this,'plugin_options_page'),
         ''.wcmamtx_PLUGIN_URL.'assets/images/icon.png',
         70
        );
	    

        $general_wcmamtxsettings_page = add_submenu_page( 'sysbasics', 'Customize My Account' , 'Customize My Account' , 'manage_woocommerce', $this->wcmamtx_notices_settings_page, array($this, 'plugin_options_page'));


	         
	}




	public function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : sanitize_text_field($this->wcmamtx_notices_settings_page);
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $tab;
        $dt_type = wcmamtx_get_version_type();
       
        ?>
        <div class="wrap">
           <?php $this->wcmamtx_options_tab_wrap(); ?>
            <form method="post" action="options.php">
                <?php wp_nonce_field( 'update-options' ); ?>
                <?php settings_fields( $tab ); ?>
                <?php 
                do_settings_sections( $tab ); 

                $allowed_endpoints = get_option("wcmamtx_allowed_endpoint_trial");
                
                if ($allowed_endpoints > 0) {

                    $endpoint_button_id = "#wcmamtx_example_modal";
                    $endpoint_button_class = "";

                } else {

                    $endpoint_button_id = "#wcmamtx_example_modal2";
                    $endpoint_button_class = "wcmamtx_disabled2";

                }
                
                
               
                
                
                ?>

                <div class="wcmamtx_buttons_section">
                    
                    <?php if (isset($current_tab) && ($current_tab == "wcmamtx_advanced_settings") && ($current_tab != "wcmamtx_wizard_settings") ) { ?>
                        <div class="wcmamtx_add_section_div">
                            <button type="button" href="#" data-toggle="modal" data-target="<?php echo $endpoint_button_id; ?>" data-etype="endpoint" id="wcmamtx_add_endpoint" class="btn btn-sm btn-primary wcmamtx_add_group <?php echo $endpoint_button_class; ?>">
                                <span class="dashicons dashicons-insert"></span>
                                <?php echo esc_html__( 'Add Endpoint' ,'customize-my-account-for-woocommerce'); ?>
                            </button>

                            <button type="button" href="#" data-toggle="modal" data-target="#wcmamtx_example_modal3" data-etype="link" id="wcmamtx_add_link" class="btn btn-sm btn-primary wcmamtx_add_group">
                                <span class="dashicons dashicons-insert"></span>
                                <?php echo esc_html__( 'Add Link' ,'customize-my-account-for-woocommerce'); ?>
                            </button>

                            <button type="button" href="#" data-toggle="modal" data-target="#wcmamtx_example_modal2" data-etype="group" id="wcmamtx_add_group" class="btn btn-sm btn-primary wcmamtx_add_group wcmamtx_disabled2">
                                <span class="dashicons dashicons-insert"></span>
                                <?php echo esc_html__( 'Add Group' ,'customize-my-account-for-woocommerce'); ?>
                            </button>
                            
                        </div>
                       
                    <?php } ?>

                    <div class="wcmamtx_submit_section_div">

                        <?php if (isset($current_tab)  && ($current_tab != "wcmamtx_wizard_settings")) {  

                            $load_wcmamtx_optional_class = load_wcmamtx_optional_class();

                            ?>

                            <input type="submit" name="submit" id="submit" class="btn <?php echo $load_wcmamtx_optional_class; ?> btn-sm btn-success wcmamtx_submit_button" value="<?php echo esc_html__( 'Save Changes' ,'customize-my-account-for-woocommerce'); ?>">

                        <?php }  ?>

                        <?php if (isset($current_tab) && ($current_tab == "wcmamtx_advanced_settings") && ($current_tab != "wcmamtx_wizard_settings")) { ?>

                            <input type="button" href="#" data-toggle="modal" data-target="#wcmamtx_bulk_modal" name="submit" id="wcmamtx_bulk_actions_button" class="btn-sm btn btn-dark wcmamtx_bulk_actions_button" value="<?php echo esc_html__( 'Bulk Actions' ,'customize-my-account-for-woocommerce'); ?>">

                            <input type="button" href="#" name="submit" id="wcmamtx_reset_tabs_button" class="btn-sm btn btn-danger wcmamtx_reset_tabs_button" value="<?php echo esc_html__( 'Restore Default' ,'customize-my-account-for-woocommerce'); ?>">
                           

                            
                        <?php } elseif (isset($current_tab) && ($current_tab == "wcmamtx_order_settings") && ($current_tab != "wcmamtx_wizard_settings")) {  ?>

                                <input type="button" href="#" name="submit" id="wcmamtx_reset_order_button" class="btn btn-sm btn-danger wcmamtx_reset_order_button" value="<?php echo esc_html__( 'Restore Default' ,'customize-my-account-for-woocommerce'); ?>">

                        <?php } ?>

                        <?php
                         $frontend_url = get_permalink(get_option('woocommerce_myaccount_page_id'));

                         if (($tab == "wcmamtx_order_settings") || ($tab == "wcmamtx_order_actions")) {
                             $frontend_url =  wc_get_account_endpoint_url( 'orders' );
                         }

                        ?>

                        <?php if (isset($current_tab)  && ($current_tab != "wcmamtx_wizard_settings")) {  ?>

                            <a type="button" target="_blank" href="<?php echo $frontend_url; ?>" name="submit" id="wcmamtx_frontend_link" class="btn btn-sm btn-primary wcmamtx_frontend_link" >
                               <span class="dashicons dashicons-welcome-view-site"></span>
                               <?php echo esc_html__( 'Frontend' ,'customize-my-account-for-woocommerce'); ?>
                           </a>

                       <?php } ?>

                       

                        <?php do_action( 'wcmamtx_add_author_links' ); ?>
                    </div>
                    <div class="wcmamtx_notice_div review bottom">

                        <div class="wcmamtx_notice_div_uppertext">
                            <?php echo esc_html__( 'If you have any issues with plugin,' ,'customize-my-account-for-woocommerce'); ?>
                            <a type="button" target="_blank" href="https://www.sysbasics.com/contact-us/" class="">

                                <?php echo esc_html__( 'Give us a chance' ,'customize-my-account-for-woocommerce'); ?>                  
                            </a>

                                <?php echo esc_html__( 'To fix the issue or if you like the plugin , do not forget to ' ,'customize-my-account-for-woocommerce'); ?> 
                                <a type="button" target="_blank" href="https://wordpress.org/support/plugin/customize-my-account-for-woocommerce/reviews/#new-post" class="">

                                <?php echo esc_html__( 'Rate the plugin.' ,'customize-my-account-for-woocommerce'); ?>                  
                            </a>
                        </div>
                    </div>

                    
                </div>
                
            </form>

            <div class="modal fade" id="wcmamtx_bulk_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">
                            
                            <select class="wcmamtx_bulk_action_select">
                                <option disabled selected value><?php echo esc_html__( 'Choose Action' ,'customize-my-account-for-woocommerce'); ?></option>
                                <option value="01"><?php echo esc_html__( 'Restore Default Background Color to all Dashboard Links' ,'customize-my-account-for-woocommerce'); ?></option>
                                <option value="02"><?php echo esc_html__( 'Restore Default Font Color to all Dashboard Links' ,'customize-my-account-for-woocommerce'); ?></option>
                            </select>

                            
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary bulk apply wcmamtx_bulk_action_select_apply"><?php echo esc_html__( 'Apply' ,'customize-my-account-for-woocommerce'); ?></button>

                            <button type="button" class="btn btn-secondary bulk" data-dismiss="modal"><?php echo esc_html__( 'Close' ,'customize-my-account-for-woocommerce'); ?></button>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="wcmamtx_example_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">
                          <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="nds_add_user_meta_form" >            


                                <input type="hidden" name="action" value="nds_form_response_endpoint">
                                <input type="hidden" name="wcmamtx_add_endpoint_nonce" value="<?php echo wp_create_nonce( 'wcmamtx_nonce_hidden' ); ?>" />          
                                <div class="form-group">
                                    
                                    
                                    <input class="form-control" required id="sdfsd-user_meta_key" type="text" name="<?php echo "nds"; ?>[label]" value="" placeholder="<?php echo esc_html__('Enter Label','customize-my-account-for-woocommerce'); ?>" /><br>
                                    <input type="hidden" class="form-control" nonce="<?php echo wp_create_nonce( 'wcmamtx_nonce_hidden' ); ?>" name="<?php echo "nds"; ?>[row_type]" id="wcmamtx_hidden_endpoint_type" value="">
                                </div>

                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close' ,'customize-my-account-for-woocommerce'); ?></button>
                                <button type="submit" name="submit"  class="btn btn-primary wcmamtx_new_end_point"><?php echo esc_html__( 'Add' ,'customize-my-account-for-woocommerce'); ?>

                                </button>
                            </form>
                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="wcmamtx_example_modal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">

                             <?php  wcmamtx_load_pro_reminder_div(); ?>

                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="wcmamtx_example_modal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">

                            

                            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="nds_add_user_meta_form" > 

                                


                                <input type="hidden" name="action" value="nds_form_response_endpoint">
                                <input type="hidden" name="wcmamtx_add_endpoint_nonce" value="<?php echo wp_create_nonce( 'wcmamtx_nonce_hidden' ); ?>" />          
                                <div class="form-group">
                                    
                                    
                                    <input class="form-control" required id="sdfsd-user_meta_key" type="text" name="<?php echo "nds"; ?>[label]" value="" placeholder="<?php echo esc_html__('Enter Label','customize-my-account-for-woocommerce'); ?>" /><br>
                                    <input type="hidden" class="form-control" nonce="<?php echo wp_create_nonce( 'wcmamtx_nonce_hidden' ); ?>" name="<?php echo "nds"; ?>[row_type]" id="wcmamtx_hidden_endpoint_type" value="">
                                </div>

                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close' ,'customize-my-account-for-woocommerce'); ?></button>
                                <button type="submit" name="submit"  class="btn btn-primary wcmamtx_new_end_point"><?php echo esc_html__( 'Add' ,'customize-my-account-for-woocommerce'); ?>

                                </button>
                            </form>
                            

                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="wcmamtx_order_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">

                            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="nds_add_column_form" >          


                                <input type="hidden" name="action" value="nds_form_response_column">
                                <input type="hidden" name="wcmamtx_add_column_nonce" value="<?php echo wp_create_nonce( 'wcmamtx_nonce_hidden_column' ); ?>" />          
                                <div class="form-group">
                                    
                                    
                                    <input class="form-control" required id="sdfsd-user_meta_key" type="text" name="<?php echo "ndscolumn"; ?>[label]" value="" placeholder="<?php echo esc_html__('Enter Label','customize-my-account-for-woocommerce'); ?>" /><br>
                                    
                                </div>

                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close' ,'customize-my-account-for-woocommerce'); ?></button>
                                <button type="submit" name="submit"  class="btn btn-primary wcmamtx_new_order"><?php echo esc_html__( 'Add New Column' ,'customize-my-account-for-woocommerce'); ?>

                                </button>
                            </form>

                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="wcmamtx_actions_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">

                            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="nds_add_action_form" >          


                                <input type="hidden" name="action" value="nds_form_response_action">
                                <input type="hidden" name="wcmamtx_add_action_nonce" value="<?php echo wp_create_nonce( 'wcmamtx_nonce_hidden_action' ); ?>" />          
                                <div class="form-group">
                                    
                                    
                                    <input class="form-control" required id="sdfsd-user_meta_key" type="text" name="<?php echo "ndsaction"; ?>[label]" value="" placeholder="<?php echo esc_html__('Enter Label','customize-my-account-for-woocommerce'); ?>" /><br>
                                    
                                </div>

                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close' ,'customize-my-account-for-woocommerce'); ?></button>
                                <button type="submit" name="submit"  class="btn btn-primary wcmamtx_new_actions"><?php echo esc_html__( 'Add New Action' ,'customize-my-account-for-woocommerce'); ?>

                                </button>
                            </form>

                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>


        </div>
        <?php
	}


	
	public function wcmamtx_options_tab_wrap() {

        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : sanitize_text_field($this->wcmamtx_notices_settings_page);

        if (isset($current_tab)  && ($current_tab != "wcmamtx_wizard_settings")) {

            echo '<a target="_blank" class="btn wcmamtx_docs_buton btn-success" href="https://www.sysbasics.com/knowledge-base/category/woocommerce-customize-my-account-pro/"><span class="wcmamtx_docs_icon dashicons dashicons-welcome-learn-more"></span>'.esc_html__( 'Documentation' ,'customize-my-account-for-woocommerce').'</a>';
            echo '<a target="_blank" class="btn wcmamtx_support_buton btn-warning" href="https://www.sysbasics.com/go/customize-free-help/"><span class="wcmamtx_docs_icon dashicons dashicons-admin-generic"></span>'.esc_html__( 'Support' ,'customize-my-account-for-woocommerce').'</a>';
        } else {

            echo '<a class="btn wcmamtx_exit_setup btn-danger" href="?page=' .$this->wcmamtx_notices_settings_page. '&wcmamtx_disable_wizard=yes"><span class="wcmamtx_docs_icon dashicons dashicons-controls-forward"></span>'.esc_html__( 'Skip Quick Setup Wizard' ,'customize-my-account-for-woocommerce').'</a>';

        }


        ?>


        <?php if (isset($current_tab)  && ($current_tab != "wcmamtx_wizard_settings")) {  ?>

            <a type="button" href="#" data-toggle="modal" data-target="#wcmamtx_upgrade_modal"  class="btn btn-primary wcmamtx_pro_link nav-wrap" >
                <span class="dashicons dashicons-lock"></span>
                <?php echo esc_html__( 'Upgrade to pro' ,'customize-my-account-for-woocommerce'); ?>
            </a>

        <?php  } ?>

            <div class="modal fade" id="wcmamtx_upgrade_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-body">

                            <?php wcmamtx_load_pro_feature_preview(); ?>

                            
                            
                            

                            <a type="button" target="_blank" href="https://sysbasics.com/go/customize/"  class="btn btn-success wcmamtx_frontend_link" >
                                <span class="dashicons dashicons-lock"></span>
                                <?php echo esc_html__( 'Upgrade to pro' ,'customize-my-account-for-woocommerce'); ?>
                            </a>

                            <br><br>

                            

                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>


        <?php


        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : sanitize_text_field($this->wcmamtx_notices_settings_page);

        $disable_wizard = isset( $_GET['wcmamtx_disable_wizard'] ) ? $_GET['wcmamtx_disable_wizard'] : 'no';

        if (isset($disable_wizard)  && ($disable_wizard == "yes")) {
            update_option('wcmamtx_disable_wizard','yes');
        }

        if (isset($current_tab)  && ($current_tab != "wcmamtx_wizard_settings")) {

            echo '<h2 class="nav-tab-wrapper">';

            foreach ( $this->wcmamtx_plugin_settings_tab as $tab_key => $tab_caption ) {

               $active = $current_tab == $tab_key ? 'nav-tab-active' : '';

               echo '<a class="nav-tab ' . $active . ' '.$tab_key.' " href="?page=' . $this->wcmamtx_notices_settings_page . '&tab=' . $tab_key . '">' . esc_html($tab_caption) . '</a>'; 

            }

            echo '</h2>';

        } else if (isset($current_tab)  && ($current_tab == "wcmamtx_wizard_settings")) {

            echo '<h2 class="nav-tab-wrapper">';

            $active = $current_tab == 'wcmamtx_wizard_settings' ? 'nav-tab-active' : '';

            echo '<a class="nav-tab ' . esc_html($active) . '" href="?page=' . $this->wcmamtx_notices_settings_page. '&tab=wcmamtx_wizard_settings">' . esc_html__('Quick Setup Wizard','customize-my-account-for-woocommerce') . '</a>'; 

            echo '</h2>';

        }


	}

    /**
     * render accordion content from $key and $value
     */

	public function get_accordion_content($key,$name,$core_fields,$value = null,$old_value = null,$third_party = null) {
	     
		$third_party = isset($value['third_party']) ? $value['third_party'] : $third_party; 

        $third_party_go_ahead = 'yes';

		if (isset($third_party)) {
			$key = strtolower($key);
			$key = str_replace(' ', '_', $key);
            $third_party_go_ahead = wcmamtx_third_party_goahead_check($key);
		}

        


        if ($third_party_go_ahead == "yes") {

   
        ?>

            <li keyvalue="<?php echo $key; ?>" litype="<?php if (isset($value['wcmamtx_type'])) { echo  $value['wcmamtx_type']; } ?>" class="<?php if (isset($value['show']) && ($value['show'] == "no"))  { echo "wcmamtx_disabled"; } ?> wcmamtx_endpoint <?php echo $key; ?> <?php if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "group")) { echo 'group'; } ?> <?php if (preg_match('/\b'.$key.'\b/', $core_fields )) { echo "core"; } ?>">

                <?php $this->get_main_li_content($key,$name,$core_fields,$value,$old_value,$third_party); ?>


            </li> 

        <?php

        }
    }


    /**
     * render accordion content from $key and $value
     */

	public function get_order_content($key,$name,$core_fields,$value = null,$old_value = null,$third_party = null) {
	     
		$third_party = isset($value['third_party']) ? $value['third_party'] : $third_party; 

		if (isset($third_party)) {
			$key = strtolower($key);
			$key = str_replace(' ', '_', $key);
		}
        
        ?>
        <li keyvalue="<?php echo $key; ?>" litype="<?php if (isset($value['wcmamtx_type'])) { echo  $value['wcmamtx_type']; } ?>" class="<?php if (isset($value['show']) && ($value['show'] == "no"))  { echo "wcmamtx_disabled"; } ?> wcmamtx_endpoint <?php echo $key; ?> <?php if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "group")) { echo 'group'; } ?> <?php if (preg_match('/\b'.$key.'\b/', $core_fields )) { echo "core"; } ?>">

            <?php $this->get_main_order_content($key,$name,$core_fields,$value,$old_value,$third_party); ?>


        </li> <?php
        
    }


    /**
     * render accordion content from $key and $value
     */

	public function get_order_actions($key,$name,$core_fields,$value = null,$old_value = null,$third_party = null) {
	     
		$third_party = isset($value['third_party']) ? $value['third_party'] : $third_party; 

		if (isset($third_party)) {
			$key = strtolower($key);
			$key = str_replace(' ', '_', $key);
		}
        
        ?>
        <li keyvalue="<?php echo $key; ?>" litype="<?php if (isset($value['wcmamtx_type'])) { echo  $value['wcmamtx_type']; } ?>" class="<?php if (isset($value['show']) && ($value['show'] == "no"))  { echo "wcmamtx_disabled"; } ?> wcmamtx_endpoint <?php echo $key; ?> <?php if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "group")) { echo 'group'; } ?> <?php if (preg_match('/\b'.$key.'\b/', $core_fields )) { echo "core"; } ?>">

            <?php $this->get_main_order_actions($key,$name,$core_fields,$value,$old_value,$third_party); ?>


        </li> <?php
        
    }


    public function get_main_order_actions($key,$name,$core_fields,$value = null,$old_value = null,$third_party = null) { 
         
        include ('wrap/orders-wrap.php');
    
    }


    public function get_main_order_content($key,$name,$core_fields,$value = null,$old_value = null,$third_party = null) { 
         
        include ('wrap/orders-content.php');
    
    }

    public function get_main_li_content($key,$name,$core_fields,$value = null,$old_value = null,$third_party = null) { 
         
        include ('wrap/endpoints-content.php');
    
    }


    public function get_group_content($name,$key,$value) {

        	    $all_keys  = (array) get_option('wcmamtx_advanced_settings');  
                
                $matches   = $this->wcmamtx_search($all_keys, $key);

         
    	    ?>

            	<ol class="wcmamtx_group_items">

                    <?php 
                        foreach($matches as $mkey=>$mvalue) {
                        	$mname             = $mvalue['endpoint_name'];
                        	$core_fields       = 'dashboard,orders,downloads,edit-address,edit-account,customer-logout';


                            $this->get_accordion_content($mkey,$mname,$core_fields,$mvalue,null);
                        }
                    ?>
                
                </ol>
            <?php
                
    }






    public function wcmamtx_search($array, $key) {
          
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
}


new wcmamtx_add_settings_page_class();
?>