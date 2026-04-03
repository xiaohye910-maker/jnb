<?php 
$advancedsettings  = (array) get_option('wcmamtx_advanced_settings');  

$tabs              = wc_get_account_menu_items();

$new_entries       = get_option('wcmamtx_tabs_to_add_third_party');

if ((isset($new_entries)) && (!empty($new_entries))) {

    

    foreach ($new_entries as $itkey=>$itvalue) {
        if (!array_key_exists($itkey, $tabs)) {

            $third_party_goahead = wcmamtx_third_party_goahead_check($itkey);

            if ($third_party_goahead == "yes") {
                $tabs[$itkey] = $itvalue;

                if (array_key_exists($itkey, $advancedsettings)) {
                    unset($new_entries[$itkey]);

                    update_option('wcmamtx_tabs_to_add_third_party',$new_entries);
                }

                
            }

            
            
        }
    }

}

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
        ?>
        <ol class="accordion wcmamtx-accordion" style="display:none;">
            <?php
                $rownum = 0;
                foreach ($tabs as $key=>$value) {

                    if (preg_match('/\b'.$key.'\b/', $core_fields )) { 

                        $third_party = null;

                    } else {

                        $third_party = 'yes';

                    }

                    
                    
                    $this->get_accordion_content($key,$value,$core_fields,$value,null,$third_party); 
                    $rownum++;

                }
        ?>
        </ol><?php
      
      } else {

        ?>
        <ol class="accordion wcmamtx-accordion" style="display:none;">
            <?php
                $rownum = 0;
                foreach ($advancedsettings as $key=>$value) {

                    

                    $name = isset($value['endpoint_name']) ? $value['endpoint_name'] : "";

                    $third_party = isset($value['third_party']) ? $value['third_party'] : null;



                    if (isset($value['parent']) && ($value['parent'] == "none")) {



                        $this->get_accordion_content($key,$name,$core_fields,$value,null,$third_party);
                    } 
                
                    $rownum++;

                }
        ?></ol><?php
      }
    ?>
        <script>
            var wmthash=<?php echo $rownum; ?>;
        </script>
        <div id="iconPicker" class="modal fade">
              <div class="modal-dialog">
                    <div class="modal-content">
                          <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                  <h4 class="modal-title"><?php  echo esc_html__('Icon Picker','customize-my-account-for-woocommerce'); ?></h4>
                          </div>
                          <div class="modal-body">
                              <div>
                                  <ul class="icon-picker-list">
                                    <li>
                                      <a data-class="{{item}} {{activeState}}" data-index="{{index}}">
                                        <span class="{{item}}"></span>
                                         <span class="name-class">{{item}}</span>
                                      </a>
                                    </li>
                                  </ul>
                              </div>
                          </div>
                          <div class="modal-footer">
                                <button type="button" id="change-icon" class="btn btn-success">
                                  <span class="fa fa-check-circle-o"></span>
                                  <?php  echo esc_html__('Use Selected Icon','customize-my-account-for-woocommerce'); ?>
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                          </div>
                    </div>
              </div>
        </div>


