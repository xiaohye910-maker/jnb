<?php 

    $count_of_selectable = array(
        'none'=> esc_html__('None','customize-my-account-for-woocommerce'),
        'order_count' =>esc_html__('Customer Orders Count','customize-my-account-for-woocommerce'),
        'downloads_count' =>esc_html__('Customer Downloads Count','customize-my-account-for-woocommerce'),
        
    );


    $count_of_selectable = apply_filters('wcmamtx_override_count_of_array',$count_of_selectable);


    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


    if ( is_plugin_active( 'woo-wallet/woo-wallet.php' ) ) {
        $count_of_selectable['woo-wallet-balance'] = esc_html__('Tera Wallet Balance','customize-my-account-for-woocommerce');
    } 

    if ( is_plugin_active( 'points-and-rewards-for-woocommerce/points-rewards-for-woocommerce.php' ) ) {
        $count_of_selectable['points'] = esc_html__('Total Points (WP Swings)','customize-my-account-for-woocommerce');
    }

    if ( is_plugin_active( 'yith-woocommerce-wishlist/init.php' ) || is_plugin_active( 'yith-woocommerce-wishlist-premium/init.php' )) {
        $count_of_selectable['yith_wishlist'] = esc_html__('Yith Wishlist Count','customize-my-account-for-woocommerce');
    }

    if ( is_plugin_active( 'woo-smart-wishlist/wpc-smart-wishlist.php' ) || is_plugin_active( 'woo-smart-wishlist-premium/wpc-smart-wishlist.php' )) {
        $count_of_selectable['wpc_wishlist'] = esc_html__('WPC Smart Wishlist Count','customize-my-account-for-woocommerce');
    }


     if ( is_plugin_active( 'woodmart-core/woodmart-core.php' )) {

        $woodmart_wishlist_on = (array) get_option("xts-woodmart-options");

        $woodmart_wishlist_on = $woodmart_wishlist_on['wishlist'];

        $woodmart_wishlist_on = isset($woodmart_wishlist_on) && ($woodmart_wishlist_on == 1) ? "yes" : "no";

        if ($woodmart_wishlist_on == "yes") {
            $count_of_selectable['woodmart_wishlist'] = esc_html__('WoodMart Wishlist Count','customize-my-account-for-woocommerce');
        }

        
    } 


    if (($wcmamtx_type != "group")) {



        switch($key) {
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

                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";
                
            break;

            case "wishlist":
                $wishlist_mode = "none";

                $wishlist_mode = wcmamtx_detect_wishlist_mode();

                echo $wishlist_mode;

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

                $section_style = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "display:block;" : "display:none;";



                $count_of = isset($value['count_of']) ? $value['count_of'] : $wishlist_mode;


                


                $section_style_cpt = isset($count_of) && ($count_of == "cpt_count") ? "display:block;" : "display:none;";

                $section_style_usermeta = isset($count_of) && ($count_of == "usermeta_count") ? "display:block;" : "display:none;";


                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";


            break;


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

                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";
                
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

                $section_style = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "display:block;" : "display:none;";

                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";
                
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

                $section_style = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "display:block;" : "display:none;";
                

                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";
               
                
            break;

            default:
                $count_bubble = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "yes" : "no";
          
                $hide_empty = isset($value['hide_empty']) && ($value['hide_empty'] == "01") ? "yes" : "no";

                $section_style = isset($value['count_bubble']) && ($value['count_bubble'] == "01") ? "display:block;" : "display:none;";

                $count_of = isset($value['count_of']) ? $value['count_of'] : "none";


                $section_style_cpt = isset($count_of) && ($count_of == "cpt_count") ? "display:block;" : "display:none;";

                $section_style_usermeta = isset($count_of) && ($count_of == "usermeta_count") ? "display:block;" : "display:none;";


                $hide_sidebar = isset($value['hide_sidebar']) && ($value['hide_sidebar'] == "01") ? "yes" : "no";

                
            break;
        }
        ?>
        <tr>
            <td>
                <label class=" wcmamtx_accordion_label"><?php echo esc_html__('Count Bubble','customize-my-account-for-woocommerce'); ?></label>
            </td>
            <td>
                <div class="wcmamtx_count_div">
                    <div class="wcmamtx_count_div_section_main">
                        <input class="wcmamtx_accordion_input count_bubble2 wcmamtx_accordion_checkbox checkmark2" type="checkbox" name="wcmamtx_advanced_settings[<?php echo $key; ?>][count_bubble]" value="03" <?php if (isset($value['count_bubble']) && ($value['count_bubble'] == "03")) { echo 'checked'; }  ?>>
                        <input parentkey = "<?php echo $key; ?>" type="checkbox" data-toggle="toggle" data-on="<?php  echo esc_html__('Yes','customize-my-account-for-woocommerce'); ?>" data-off="<?php  echo esc_html__('No','customize-my-account-for-woocommerce'); ?>" data-size="sm" class="wcmamtx_accordion_input count_bubble wcmamtx_accordion_checkbox checkmark" type="checkbox" name="wcmamtx_advanced_settings[<?php echo $key; ?>][count_bubble]" value="01" <?php if (isset($count_bubble) && ($count_bubble == "yes")) { echo 'checked'; } elseif (!isset($value)) { echo 'checked'; }
                            // code...
                         ?>>
                    </div>
                    <div class="wcmamtx_count_div_section" style="<?php echo $section_style; ?>">
                        <label class=" wcmamtx_accordion_label"><?php echo esc_html__('Hide Empty','customize-my-account-for-woocommerce'); ?></label>

                        <input type="checkbox" data-toggle="toggle" data-on="<?php  echo esc_html__('Yes','customize-my-account-for-woocommerce'); ?>" data-off="<?php  echo esc_html__('No','customize-my-account-for-woocommerce'); ?>" data-size="sm" class="wcmamtx_accordion_input wcmamtx_accordion_checkbox checkmark" type="checkbox" name="wcmamtx_advanced_settings[<?php echo $key; ?>][hide_empty]" value="01" <?php if (isset($hide_empty) && ($hide_empty == "yes")) { echo 'checked'; } ?>>

                       
                            <label class=" wcmamtx_accordion_label"><?php echo esc_html__('Hide in sidebar','customize-my-account-for-woocommerce'); ?></label>

                            <input type="checkbox" data-toggle="toggle" data-on="<?php  echo esc_html__('Yes','customize-my-account-for-woocommerce'); ?>" data-off="<?php  echo esc_html__('No','customize-my-account-for-woocommerce'); ?>" data-size="sm" class="wcmamtx_accordion_input wcmamtx_accordion_checkbox checkmark" type="checkbox" name="wcmamtx_advanced_settings[<?php echo $key; ?>][hide_sidebar]" value="01" <?php if (isset($hide_sidebar) && ($hide_sidebar == "yes")) { echo 'checked'; } ?>>
                        
                    </div>
                    <?php 

                    if  (($key != "orders") && ($key != "downloads") && ($key != "woo-wallet") && ($key != "points")) {
                        ?>
                        <div class="wcmamtx_count_div_section" style="<?php echo $section_style; ?>">
                            <select mkey="<?php echo $key; ?>" class="wcmamtx_countof_dropdown" name="wcmamtx_advanced_settings[<?php echo $key; ?>][count_of]">
                                
                                <?php 

                                foreach ($count_of_selectable as $countkey=>$count_value) {
                                    ?>
                                    <option value="<?php echo $countkey; ?>" <?php if ((isset($count_of)) && ($count_of == $countkey)) { echo "selected"; } ?>>
                                        <?php echo $count_value ?>   
                                    </option>

                                    <?php

                                }
                            ?>
                            </select>

                            <div class="wcmamtx_count_cpt_section wcmamtx_count_cpt_section_<?php echo $key; ?>" style="<?php echo $section_style_cpt; ?>">
                               
                            </div>
                            <div class="wcmamtx_count_usermeta_section wcmamtx_count_usermeta_section_<?php echo $key; ?>" style="<?php echo $section_style_usermeta; ?>">
                                
                            </div>
                        </div>
                        &nbsp;
                        <a href="https://www.sysbasics.com/knowledge-base/add-custom-count-bubble/" target="_blank" class="wcmamtx-custom-countof-link"><p class=" bubble"><?php  echo esc_html__('Add custom count bubble','customize-my-account-for-woocommerce'); ?></p>
                        </a>
                        <?php


                    }

                    ?>
                </div>

            </td>
        </tr>
        <?php
    }


?>