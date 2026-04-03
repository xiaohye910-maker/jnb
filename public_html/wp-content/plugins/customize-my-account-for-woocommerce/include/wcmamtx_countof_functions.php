<?php


if (!function_exists('wcmamtx_counter_bubble')) {

    function wcmamtx_counter_bubble($key,$value,$sidebar = null) {

    	if (!is_account_page()) {
    		return;
    	}

    	$count_of = 'none';



         switch($key) {
         	case "points":
                if (is_array($value) ) {

                    if (!isset($value['count_bubble'])) {
                         $value['count_bubble'] = "01";
                    } else {
                        $value['count_bubble'] = $value['count_bubble'];
                    }
                   
                }

               
                
                $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";

                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";

                $section_style = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "display:block;" : "display:none;";

                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "always";

                wcmamtx_render_wpswings_points_count_bubble_html($count_bubble,$hide_empty,$sidebar);
                
            break;

             case "wishlist":

                

                $wishlist_mode = "none";

                $wishlist_mode = wcmamtx_detect_wishlist_mode();



                if ($wishlist_mode == "none") {
                    $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";
                } else {

                    if (is_array($value) ) {

                        if (!isset($value['count_bubble'])) {
                            $value['count_bubble'] = "01";
                        } else {
                            $value['count_bubble'] = $value['count_bubble'];
                        }

                    }

                    $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";

                }
          
                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";


                $count_of = isset($value['count_of']) ? $value['count_of'] : $wishlist_mode;


                wcmamtx_countof_conditional_switch($count_of,$value,$sidebar);

                


            break;

         	case "woo-wallet":
                if (is_array($value) ) {

                    if (!isset($value['count_bubble'])) {
                         $value['count_bubble'] = "01";
                    } else {
                        $value['count_bubble'] = $value['count_bubble'];
                    }
                   
                }

                $count_of = isset($value['count_of']) ? $value['count_of'] : "woo-wallet-balance";
                
                $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";

                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";

                $section_style = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "display:block;" : "display:none;";

                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "always";

                wcmamtx_render_woo_wallet_count_bubble_html($count_bubble,$hide_empty,$sidebar);
                
            break;

            case "orders":

                if (is_array($value) ) {

                    if (!isset($value['count_bubble'])) {
                         $value['count_bubble'] = "01";
                    } else {
                        $value['count_bubble'] = $value['count_bubble'];
                    }
                   
                }

                $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";

                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";
                
                wcmamtx_render_order_count_bubble_html($count_bubble,$hide_empty,$sidebar);
                
            break;

            case "downloads":

                if (is_array($value) ) {

                    if (!isset($value['count_bubble'])) {
                         $value['count_bubble'] = "01";
                    } else {
                        $value['count_bubble'] = $value['count_bubble'];
                    }
                   
                }
                
                $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";

                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";

                wcmamtx_render_download_count_bubble_html($count_bubble,$hide_empty,$sidebar);
                
            break;

            default:
                $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";


                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";



                $count_of = isset($value['count_of']) ? $value['count_of'] : "none";

                if (isset($count_of) && ($count_of != null)) {

                	if (($key != "orders") || ($key != "downloads") || ($key != "wishlist")) {


                       wcmamtx_countof_conditional_switch($count_of,$value,$sidebar);


                	}

                	do_action("wcmamtx_cutom_count_of_html",$count_bubble,$hide_empty,$count_of,$sidebar);

                }

               
                  
            break;
        }

    }

}


if (!function_exists('wcmamtx_countof_conditional_switch')) {

	function wcmamtx_countof_conditional_switch($count_of,$value,$sidebar = null) {

		$count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";


        $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";


		switch($count_of) {

			case "order_count":
			wcmamtx_render_order_count_bubble_html($count_bubble,$hide_empty,$sidebar);
			break;

			case "downloads_count":
			wcmamtx_render_download_count_bubble_html($count_bubble,$hide_empty,$sidebar);
			break;

			case "cpt_count":
			wcmamtx_render_cpt_count_bubble_html($count_bubble,$hide_empty,$value,$sidebar);
			break;

			case "yith_wishlist":

			if (function_exists('wcmamtx_render_yith_wishlist_count_bubble_html')) {
				if ( is_plugin_active( 'yith-woocommerce-wishlist/init.php' ) || is_plugin_active( 'yith-woocommerce-wishlist-premium/init.php' )) {

					wcmamtx_render_yith_wishlist_count_bubble_html($count_bubble,$hide_empty,$sidebar);
				}

			}
			break;

			case "wpc_wishlist":

			if ( is_plugin_active( 'woo-smart-wishlist/wpc-smart-wishlist.php' ) || is_plugin_active( 'woo-smart-wishlist-premium/wpc-smart-wishlist.php' )) {

				if ( class_exists( 'WPCleverWoosw' ) ) {
					wcmamtx_render_wpc_wishlist_count_bubble_html($count_bubble,$hide_empty,$sidebar);
				}
			}
			break;


			case "woodmart_wishlist":

			if ( is_plugin_active( 'woodmart-core/woodmart-core.php' ) && function_exists('woodmart_get_wishlist_count')) {

				$woodmart_wishlist_on = (array) get_option("xts-woodmart-options");

				$woodmart_wishlist_on = $woodmart_wishlist_on['wishlist'];

				$woodmart_wishlist_on = isset($woodmart_wishlist_on) && ($woodmart_wishlist_on == 1) ? "yes" : "no";

				if ($woodmart_wishlist_on == "yes") {
					wcmamtx_render_woodmart_wishlist_count_bubble_html($count_bubble,$hide_empty,$sidebar);
				}


			} 

			break;

			case "woo-wallet-balance":
			if ( is_plugin_active( 'woo-wallet/woo-wallet.php' ) ) {
				wcmamtx_render_woo_wallet_count_bubble_html($count_bubble,$hide_empty,$sidebar);
			}       
			break;

			case "points":
			    if ( is_plugin_active( 'points-and-rewards-for-woocommerce/points-rewards-for-woocommerce.php' ) ) {

				    wcmamtx_render_wpswings_points_count_bubble_html($count_bubble,$hide_empty,$sidebar);
			    }       
			break;


			case "none":
			break;

		}

		do_action('wcmamtx_after_countof_wrapper');
	}
}


if (!function_exists('wcmamtx_get_total_orderid_count')) {

    function wcmamtx_get_total_orderid_count() {

        $user_id = get_current_user_id();
        // Get TOTAL number of orders for customer
        $numorders = wc_get_customer_order_count( $user_id );

        // Get CANCELLED orders for customer
        $args = array(
            'customer_id' => $user_id,
            'post_status' => 'cancelled',
            'post_type' => 'shop_order',
            'return' => 'ids',
        );

        $numorders_cancelled = 0;
        $numorders_cancelled = count( wc_get_orders( $args ) ); // count the array of orders

        // NON-CANCELLED equals TOTAL minus CANCELLED
        $num_not_cancelled = $numorders - $numorders_cancelled;

        if ($num_not_cancelled > 0) {
            return $num_not_cancelled;
        } else {
            return 0;
        }

        
    }

}


if (!function_exists('wcmamtx_detect_wishlist_mode')) {

    function wcmamtx_detect_wishlist_mode() {

        $wishlist_mode = "none";
		

		if ( is_plugin_active( 'yith-woocommerce-wishlist/init.php' ) || is_plugin_active( 'yith-woocommerce-wishlist-premium/init.php' )) {
            $wishlist_mode = "yith_wishlist";

            return $wishlist_mode;
        }


        if ( is_plugin_active( 'woo-smart-wishlist/wpc-smart-wishlist.php' ) || is_plugin_active( 'woo-smart-wishlist-premium/wpc-smart-wishlist.php' )) {
            $wishlist_mode = "wpc_wishlist";

            return $wishlist_mode;
        }

        if ( is_plugin_active( 'woodmart-core/woodmart-core.php' ) && function_exists('woodmart_get_wishlist_count')) {

        	$woodmart_wishlist_on = (array) get_option("xts-woodmart-options");

        	$woodmart_wishlist_on = $woodmart_wishlist_on['wishlist'];

        	$woodmart_wishlist_on = isset($woodmart_wishlist_on) && ($woodmart_wishlist_on == 1) ? "yes" : "no";

        	if ($woodmart_wishlist_on == "yes") {
        		$wishlist_mode = "woodmart_wishlist";

                return $wishlist_mode;
        	}


        }

        return $wishlist_mode; 

        
    }

}


if (!function_exists('wcmamtx_get_total_wpswings_points_count')) {

    function wcmamtx_get_total_wpswings_points_count() {

       global $wpdb;
			
			$user_id = get_current_user_id();

			$wps_wpr_overall__accumulated_points = get_user_meta( $user_id, 'wps_wpr_overall__accumulated_points', true );
            $user_points = ! empty( $wps_wpr_overall__accumulated_points ) ? $wps_wpr_overall__accumulated_points : 0;

			return $user_points;

        
    }

}




if (!function_exists('wcmamtx_get_total_woowallet_count')) {

    function wcmamtx_get_total_woowallet_count() {

       global $wpdb;
			
			$user_id = get_current_user_id();
			
			
			$wallet_balance = 0;
			if ( $user_id ) {
				$wallet_balance = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(CASE WHEN t.type = 'credit' THEN t.amount ELSE -t.amount END) as balance FROM {$wpdb->base_prefix}woo_wallet_transactions AS t WHERE t.user_id=%d AND t.deleted=0", $user_id ) ); // @codingStandardsIgnoreLine
				$wallet_balance = (float) apply_filters( 'woo_wallet_current_balance', $wallet_balance, $user_id );
			}
			return $wallet_balance;

        
    }

}








if (!function_exists('wcmamtx_get_user_post_type_count')) {

    function wcmamtx_get_user_post_type_count($post_type,$status) {

        $user_id = get_current_user_id();
        $args['author'] = $user_id;
        $args['post_type'] = $post_type;
        $args['post_status'] = $status;
        $ps = get_posts($args);
        return count($ps);

        
    }

}


/**
 * Get account li html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_render_cpt_count_bubble_html')) {

	function wcmamtx_render_cpt_count_bubble_html($count_bubble,$hide_empty,$value,$sidebar = null) {

		



		$custom_post_type = isset($value['count_post_type']) ? isset($value['count_post_type']) : "";

		$cpt_status = isset($value['cpt_status']) ? isset($value['cpt_status']) : "publish";



		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_user_post_type_count($custom_post_type,$cpt_status);

		if ($hide_empty == "yes") {


			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") && ($custom_post_type != "") && ($empty_goahead == 'yes') && ($count_of == "post_type") && (is_numeric($get_count))) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> <?php if ($get_count == 0) { echo 'empty'; } ?>">         
				<?php echo wcmamtx_get_user_post_type_count($custom_post_type,$cpt_status); ?>
			</span>
			<?php
		}


	}

}





if (!function_exists('wcmamtx_render_wpswings_points_count_bubble_html')) {

	function wcmamtx_render_wpswings_points_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_wpswings_points_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") &&  ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wcmamtx_get_total_wpswings_points_count(); ?>

			</span>
			<?php
		}


	}

}

/**
 * Get account li html.
 *
 * @since 1.0.0
 * @param string $endpoint Endpoint.
 * @return string
 */

if (!function_exists('wcmamtx_render_woo_wallet_count_bubble_html')) {

	function wcmamtx_render_woo_wallet_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_woowallet_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") &&  ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> amount <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wc_price(wcmamtx_get_total_woowallet_count()); ?>

			</span>
			<?php
		}


	}

}







if (!function_exists('wcmamtx_render_order_count_bubble_html')) {

	function wcmamtx_render_order_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_orderid_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") &&  ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wcmamtx_get_total_orderid_count(); ?>

			</span>
			<?php
		}


	}

}




if (!function_exists('wcmamtx_render_download_count_bubble_html')) {

	function wcmamtx_render_download_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_downloads_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") && ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wcmamtx_get_total_downloads_count(); ?>

			</span>
			<?php
		}


	}

}

if (!function_exists('wcmamtx_get_total_downloads_count')) {

	function wcmamtx_get_total_downloads_count() {

		
        // Check if a user is logged in and WooCommerce is active
		if ( ! is_user_logged_in() || ! function_exists( 'wc_get_customer_available_downloads' ) ) {
			return 0;
		}

		$current_user_id = get_current_user_id();
		$available_downloads = wc_get_customer_available_downloads( $current_user_id );

    // Return the total count of unique downloadable files available
		return count( $available_downloads );
		

	}


}





if (!function_exists('wcmamtx_render_yith_wishlist_count_bubble_html')) {

	function wcmamtx_render_yith_wishlist_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_yith_wishlist_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		

		if (($count_bubble == "yes") &&  ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> wcmamtx_wishlist yith_wishlist <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wcmamtx_get_total_yith_wishlist_count(); ?>

			</span>
			<?php
		}


	}

}

if (!function_exists('wcmamtx_get_total_yith_wishlist_count')) {

    function wcmamtx_get_total_yith_wishlist_count() {

        $user_id = get_current_user_id();

        if ( is_plugin_active( 'yith-woocommerce-wishlist/init.php' ) || is_plugin_active( 'yith-woocommerce-wishlist-premium/init.php' )) {

        	$wishlist_count = YITH_WCWL_Wishlists()->count_items_in_wishlist( $user_id );
        	

            return $wishlist_count;
        }

        
        
    }

}


if (!function_exists('wcmamtx_render_woodmart_wishlist_count_bubble_html')) {

	function wcmamtx_render_woodmart_wishlist_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_woodmart_wishlist_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") &&  ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> wcmamtx_wishlist woodmart_wishlist <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wcmamtx_get_total_woodmart_wishlist_count(); ?>

			</span>
			<?php
		}


	}

}

if (!function_exists('wcmamtx_get_total_woodmart_wishlist_count')) {

    function wcmamtx_get_total_woodmart_wishlist_count() {

        $user_id = get_current_user_id();


        $woodmart_wishlist_on = (array) get_option("xts-woodmart-options");

        $woodmart_wishlist_on = $woodmart_wishlist_on['wishlist'];

        $woodmart_wishlist_on = isset($woodmart_wishlist_on) && ($woodmart_wishlist_on == 1) ? "yes" : "no";

        if (($woodmart_wishlist_on == "yes") && function_exists('woodmart_get_wishlist_count')) {
            

        	$wishlist_count = woodmart_get_wishlist_count();

            return $wishlist_count;
        }

        
        
    }

}

if (!function_exists('wcmamtx_render_wpc_wishlist_count_bubble_html')) {

	function wcmamtx_render_wpc_wishlist_count_bubble_html($count_bubble,$hide_empty,$sidebar = null) {

		$empty_goahead = 'yes';

		$get_count = wcmamtx_get_total_wpc_wishlist_count();

		if ($hide_empty == "yes") {
			

			if ($get_count == 0) {
				$empty_goahead = 'no';
			} else {
				$empty_goahead = 'yes';
			}

		}

		if (($count_bubble == "yes") &&  ($empty_goahead == 'yes')) {
			?>
			<span class="<?php if (isset($sidebar)) { echo 'wcmamtx-banner-counter-sidebar'; } else {  echo 'wcmamtx-banner-counter';} ?> wcmamtx_wishlist wpc_wishlist <?php if ($get_count == 0) { echo 'empty'; } ?>">
				<?php echo wcmamtx_get_total_wpc_wishlist_count(); ?>

			</span>
			<?php
		}


	}

}

if (!function_exists('wcmamtx_get_total_wpc_wishlist_count')) {

    function wcmamtx_get_total_wpc_wishlist_count() {

        $user_id = get_current_user_id();

        
        $wishlist_count = 0;

        if ( class_exists( 'WPCleverWoosw' ) ) {
        
          $wishlist_count = esc_html( WPCleverWoosw::get_count() );
        
        }


        return $wishlist_count;
        

        
        
    }

}