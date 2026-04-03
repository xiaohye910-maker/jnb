<div class="wcmamtx_body">
  
  
  <!-- Mobile wcmamtx_sidebar Menu Button -->
  <button class="wcmamtx_sidebar-menu-button">
    <span class="material-symbols-rounded">
      <?php echo esc_html__('menu','customize-my-account-for-woocommerce'); ?>
    
    </span>
  </button>

  <aside class="wcmamtx_sidebar">
    <!-- wcmamtx_sidebar Header -->
    <header class="wcmamtx_sidebar-header">
      
      <button class="wcmamtx_sidebar-toggler">
        <span class="material-symbols-rounded">
        
        <?php echo esc_html__('chevron_left','customize-my-account-for-woocommerce'); ?>
        </span>
      </button>
    </header>

    <nav class="wcmamtx_sidebar-nav">
      <ul class="">
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

          $pro_added = wcmamtx_pro_added_endpoint($value);

          if (($parent == "none") && ($pro_added == "no")) {


            wcmamtx_get_account_menu_li_html( $name,$key ,$value ,$icon_extra_class,$extraclass,$icon_source );
          }

        } ?>

      <?php } ?>
      
    <?php } ?>
  </ul>
</nav>
</aside>


</div>