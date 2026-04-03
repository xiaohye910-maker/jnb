<ul class="wcmamtx_vertical_menu">
    <?php foreach ( $wcmamtx_tabs as $key => $value ) { 

        if (isset($value['endpoint_name']) && ($value['endpoint_name'] != '')) {
            $name = $value['endpoint_name'];
        } else {
            $name = $value;
        }

        $should_show = 'yes';


        if (isset($value['visibleto']) && ($value['visibleto'] != "all")) {

            $allowedroles  = isset($value['roles']) ? $value['roles'] : "";

            $allowedusers  = isset($value['users']) ? $value['users'] : array();

            $is_visible    = wcmamtx_check_role_visibility($allowedroles,$value['visibleto'],$allowedusers);

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

    $hide_in_navigation = isset($value['hide_in_navigation']) && ($value['hide_in_navigation'] == "01") ? "enabled" : "disabled";

    if (isset($hide_in_navigation) && ($hide_in_navigation == "enabled")) {

       $should_show = 'no';

   }

   if (($should_show == "yes") && ($is_visible == "yes")) {

    if (isset($value['wcmamtx_type']) && ($value['wcmamtx_type'] == "group")) {


        wcmamtx_get_account_menu_group_html( $name,$key ,$value ,$icon_extra_class,$extraclass,$icon_source );




    } else {

        if ($parent == "none") {
            wcmamtx_get_account_menu_li_html( $name,$key ,$value ,$icon_extra_class,$extraclass,$icon_source );
        }

    } ?>

<?php } ?>

<?php } ?>
</ul>


<nav class="woocommerce-MyAccount-navigation wsmt_extra_navclass <?php echo $menu_position_extra_class; ?>">

    <?php

    $show_avatar = 'yes';

    $avatar_settings = (array) get_option( 'wcmamtx_avatar_settings' );

    if (isset($avatar_settings['disable_avatar']) && ($avatar_settings['disable_avatar'] == "yes")) {

        $show_avatar = 'no';
    } else {
        $show_avatar = 'yes';
    }

      $user_avatar_enable = wcmamtx_is_module_enabled_init("user-avatar");


    if (($show_avatar == 'yes') && (isset($user_avatar_enable) && ($user_avatar_enable == "yes")) ) {
        echo do_shortcode('[sysBasics-user-avatar]');
    }

    $intro_text_hello = "yes";

    

    if (isset($avatar_settings['intro_text_hello']) && ($avatar_settings['intro_text_hello'] == "yes")) {

        $intro_text_hello = 'no';
    } else {
        $intro_text_hello = 'yes';
    }
    

    if ($intro_text_hello == "yes") { 

        global $current_user;
        wp_get_current_user();

        $allowed_html = array(
            'a' => array(
                'href' => array(),
            ),
        );


        ?>

        <div class="wcmamtx_intro_text">
            <span class="wcmamtx_intro_text1"><?php echo ucfirst($current_user->user_login); ?></span>
            
            <span class="wcmamtx_intro_text2">
                <?php
                printf(
                    /* translators: 1: user display name 2: logout url */
                    wp_kses( __( '<a href="%1$s">Log out</a>', 'customize-my-account-for-woocommerce' ), $allowed_html ),
                    esc_url( wc_logout_url() ),

                );
                ?>
            </span>
        </div>

        <?php
    }

    ?>


    <?php do_action( 'wcmamtx_after_account_navigation' ); ?>
</nav>