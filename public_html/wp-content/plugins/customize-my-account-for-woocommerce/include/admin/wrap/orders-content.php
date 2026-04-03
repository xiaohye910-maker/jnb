<?php      
global $wp_roles;

        $extra_content_core_fields = 'downloads,edit-address,edit-account';
        $exclude_content_core_fields       = 'dashboard,orders,customer-logout';

        
        $wcmamtx_type = 'endpoint';
       
        


        if (isset($value['parent']) && ($value['parent'] != "")) {

        	$wcmamtx_parent = $value['parent'];
        	
        } else {

        	$wcmamtx_parent = 'none';
       
        }



        if ( ! isset( $wp_roles ) ) { 
        	$wp_roles = new WP_Roles();  

        }

        $roles    = $wp_roles->roles;

        $third_party = isset($value['third_party']) ? $value['third_party'] : $third_party;

	    
    	?>

    	<h3>
    		<div class="wcmamtx_accordion_handler">
    			<?php if (preg_match('/\b'.$key.'\b/', $core_fields )) { ?>

    				
    				<input type="checkbox" <?php if ($key == "order-actions") { echo 'disabled="disabled"'; } ?> class="wcmamtx_accordion_onoff" parentkey="<?php echo $key; ?>"  <?php if (isset($value['show']) && ($value['show'] != "no"))  { echo "checked"; } elseif (!isset($value['show'])) { echo 'checked';} ?>>

    			    

    				<input type="hidden" class="<?php echo $key; ?>_hidden_checkbox" value='<?php if (isset($value['show']) && ($value['show'] == "no")) { echo "no"; } else { echo 'yes';} ?>' name='wcmamtx_order_settings[<?php echo $key; ?>][show]'>

    			<?php } else { 

                      if (isset($third_party)) {
                      	 $key = strtolower($key);
                      	 $key = str_replace(' ', '_', $key);
                      }

    				?>
    				<span type="removeicon" parentkey="<?php echo $key; ?>" class="dashicons dashicons-trash wcmamtx_accordion_remove"></span>
    			<?php } ?>
    		</div>

    		<span class="dashicons dashicons-menu-alt "></span><?php if (isset($name) && ($name != "")) { echo $name; } else if ($key == "order-actions") { echo esc_html__('Actions','customize-my-account-for-woocommerce'); } ?>
    		<span class="wcmamtx_type_label">
    			<?php echo esc_html__('Column','customize-my-account-for-woocommerce'); ?>
    		</span>

    	</h3>

        <div class="<?php echo $wcmamtx_type; ?>_accordion_content">

        	<table class="wcmamtx_table widefat">

        		<?php if (isset($third_party)) { ?>

        			<tr>
        				<td>
                        
        				</td>
        				<td>
        					<p><?php  echo esc_html__('This is third party endpoint.Some features may not work.','customize-my-account-for-woocommerce'); ?></p>
        					<input type="hidden" name="wcmamtx_order_settings[<?php echo $key; ?>][third_party]" value="yes">
        					<input type="hidden" name="wcmamtx_order_settings[<?php echo $key; ?>][endpoint_name]" value="<?php if (isset($name)) { echo $name; } ?>">
        				</td>

        			</tr>

        		<?php } ?>

                

            	<input type="hidden" class="wcmamtx_accordion_input" name="wcmamtx_order_settings[<?php echo $key; ?>][endpoint_key]" value="<?php if (isset($value['endpoint_key'])) { echo $value['endpoint_key']; } else { echo $key; } ?>">


               

        
                <input type="hidden" name="wcmamtx_order_settings[<?php echo $key; ?>][wcmamtx_type]" value="<?php echo $wcmamtx_type; ?>">

                <input type="hidden" name="wcmamtx_order_settings[<?php echo $key; ?>][parent]" class="wcmamtx_parent_field" value="<?php echo $wcmamtx_parent; ?>">

                <?php if (!isset($third_party) && ($key != "order-actions")) { ?>

                <tr>
                    <td>
                        <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Label','customize-my-account-for-woocommerce'); ?></label>
                    </td>
                    <td>
                       
                        <input type="text" class="wcmamtx_accordion_input" name="wcmamtx_order_settings[<?php echo $key; ?>][endpoint_name]" value="<?php if (isset($value['endpoint_name'])) { echo $value['endpoint_name']; } elseif ($key != "order-actions") { echo $value; } ?>">
                    </td>
            
                </tr>

                <?php } else { ?>

                <tr>

                	<td>
                        <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Label','customize-my-account-for-woocommerce'); ?></label>
                    </td>
                    <td>
                       
                          <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Actions','customize-my-account-for-woocommerce'); ?></label>
                    </td>
                </tr>

                <?php } ?>

                <?php if ((!preg_match('/\b'.$key.'\b/', $core_fields ) && ($wcmamtx_type == 'endpoint')) && (!isset($third_party))) { 
                    
                    $ordervalues = wcmamtx_get_meta_values();
                    
                    

                	?>   

                	<tr>

                		<td>
                			<label class="wcmamtx_accordion_label">
                				<?php  echo esc_html__('Value','customize-my-account-for-woocommerce'); ?>
                					
                			</label>
                		</td>
                		<td>

                			<select class="wcmamtx_value_select" name="wcmamtx_order_settings[<?php echo $key; ?>][value]">
                				<option value="">
                					<?php  echo esc_html__('Chose an Option','customize-my-account-for-woocommerce'); ?>
                						
                				</option>
                                <option value="checkoutfield" <?php if (isset($value['value']) && ($value['value'] == "checkoutfield" )) { echo 'selected'; } ?>>
                                    <?php  echo esc_html__('Checkout Field','customize-my-account-for-woocommerce'); ?>
                                        
                                </option>
                				<option value="orderid" <?php if (isset($value['value']) && ($value['value'] == "orderid" )) { echo 'selected'; } ?>>
                					<?php  echo esc_html__('Order ID','customize-my-account-for-woocommerce'); ?>
                						
                				</option>
                                <option value="customkey" <?php if (isset($value['value']) && ($value['value'] == "customkey" )) { echo 'selected'; } ?>>
                					<?php  echo esc_html__('Use new custom meta key','customize-my-account-for-woocommerce'); ?>
                						
                				</option>

                			</select>
                		</td>
                	</tr>

                	<tr class="wcmamtx_customkey_tr" style="<?php if (isset($value['value']) && ($value['value'] == "customkey" )) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
                        <td>
                           <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Key','customize-my-account-for-woocommerce'); ?></label>
                       </td>
                       <td>
                        <input type="text" class="wcmamtx_accordion_input" name="wcmamtx_order_settings[<?php echo $key; ?>][custom_key]" value="<?php if (isset($value['custom_key'])) { echo $value['custom_key']; } else { echo $key; } ?>">
                        </td>

                    </tr>

                    <tr class="wcmamtx_checkoutfield_tr" style="<?php if (isset($value['value']) && ($value['value'] == "checkoutfield" )) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
                        <td>
                           <label class="wcmamtx_accordion_label"><?php  echo esc_html__('Checkout Field','customize-my-account-for-woocommerce'); ?></label>
                        </td>
                        <td>
                            <select class="checkout_field_rule_parentfield" name="wcmamtx_order_settings[<?php echo $key; ?>][custom_key]">
                                
                                
                                <optgroup label="<?php echo esc_html__( 'Billing Fields' ,'customize-my-account-for-woocommerce'); ?>">
                                    <?php

                                    $billing_settings = (array) get_option('syscmafwpl_billing_settings');
                                    

                                    if (sysbasics_checkout_mode =="on") { 
                                      
                                        $billing_settings = $billing_settings;

                                        

                                    } else {
                                        global $woocommerce;
                                        $countries     = new WC_Countries();

                                        $billing_settings  = $countries->get_address_fields( $countries->get_base_country(),'billing_');
                                        
                                    }

                                    foreach ($billing_settings as $optionkey=>$optionvalue) { 

                                        if ( (isset ($optionvalue['type']) && ($optionvalue['type'] == 'email'))) { 

                                        } else { 

                                            if (isset($optionvalue['label']))  { 

                                                $optionlabel = $optionvalue['label']; 

                                            } else { 

                                                $optionlabel = $optionkey; 
                                            }
                                            ?> 

                                            <option value="<?php echo $optionkey; ?>" <?php if (isset($value['custom_key']) && ($value['custom_key'] == $optionkey)) { echo 'selected';} ?> >
                                                <?php echo $optionlabel; ?>
                                                
                                            </option>

                                            <?php
                                            
                                        } 
                                    } 
                                    ?>
                                </optgroup>

                                <optgroup label="<?php echo esc_html__( 'Shipping Fields' ,'customize-my-account-for-woocommerce'); ?>">
                                    <?php
                                    $shipping_settings = (array) get_option('syscmafwpl_shipping_settings');

                                    if (sysbasics_checkout_mode == "on") { 
                                      
                                        $shipping_settings = $shipping_settings;

                                    } else {
                                        global $woocommerce;
                                        $countries     = new WC_Countries();

                                        $shipping_settings              = $countries->get_address_fields( $countries->get_base_country(),'shipping_');
                                    }

                                    foreach ($shipping_settings as $optionkey=>$optionvalue) { 

                                        if ( (isset ($optionvalue['type']) && ($optionvalue['type'] == 'email'))) { 

                                        } else { 

                                            if (isset($optionvalue['label']))  { 

                                                $optionlabel = $optionvalue['label']; 

                                            } else { 

                                                $optionlabel = $optionkey; 
                                            }
                                            ?> 

                                            <option value="<?php echo $optionkey; ?>" <?php if (isset($value['custom_key']) && ($value['custom_key'] == $optionkey)) { echo 'selected';} ?>>
                                                <?php echo $optionlabel; ?>
                                                
                                            </option>

                                            <?php
                                            
                                        } 
                                    } 
                                    ?>
                                </optgroup>

                                <?php

                                $additional_settings  = (array) get_option('syscmafwpl_additional_settings');
                                $additional_settings  = array_filter($additional_settings);

                                if (isset($additional_settings) && (sizeof($additional_settings) >= 1)) { 
                                    $conditional_fields_dropdown = $additional_settings;
                                } else {
                                    $conditional_fields_dropdown = array();
                                }




                                if (count($conditional_fields_dropdown) != 0) { ?>

                                    <optgroup label="<?php echo esc_html__( 'Additional Fields' ,'customize-my-account-for-woocommerce'); ?>">

                                        <?php 

                                        


                                        foreach ($conditional_fields_dropdown as $optionkey=>$optionvalue) { 

                                            if ( (isset ($optionvalue['type']) && ($optionvalue['type'] == 'email')) || (preg_match('/\b'.$optionkey.'\b/', $country_fields ))) { 

                                            } else { 

                                                if (isset($optionvalue['label']))  { 

                                                    $optionlabel = $optionvalue['label']; 

                                                } else { 

                                                    $optionlabel = $optionkey; 
                                                }
                                                ?> 

                                                <option value="<?php echo $optionkey; ?>" <?php if (isset($value['custom_key']) && ($value['custom_key'] == $optionkey)) { echo 'selected';} ?>>
                                                    <?php echo $optionlabel; ?>
                                                    
                                                </option>

                                                <?php
                                                
                                            } 
                                        } 
                                        

                                        
                                        ?>

                                    </optgroup>

                                <?php } ?>
                                

                            </select>
                        </td>

                    </tr>

                <?php } ?>
                
            </table>

</div>