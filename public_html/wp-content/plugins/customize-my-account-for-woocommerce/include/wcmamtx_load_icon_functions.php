<?php
if (!function_exists('wcmamtx_get_account_menu_li_icon_html')) {

	function wcmamtx_get_account_menu_li_icon_html($icon_source,$value,$key) {

		
        
        switch ($icon_source) {

        	case "custom":

        	$icon       = isset($value['icon']) ? $value['icon'] : "";

        	if ($icon != '') { ?>
        		<i class="<?php echo $icon; ?>"></i>
        	<?php }
        	break;

        	case "dashicon":

        	$icon       = isset($value['dashicon']) ? $value['dashicon'] : "";

			if ($icon != '') { ?>
				<span class="dashicons <?php echo $icon; ?>"></span>
			<?php } else { ?>

				<i class="fa fa-file-alt"></i>

			<?php }
        	break;

        	case "noicon":

        	break;


        	case "upload":

        	$swatchimage = isset($value['upload_icon']) ? $value['upload_icon'] : "";

        	if (isset($swatchimage) && ($swatchimage != "")) {
        		$swatchurl     = wp_get_attachment_thumb_url( $swatchimage );

        		?>
        		<img class="wcmamtx_upload_image_icon" src="<?php echo $swatchurl; ?>">
        		<?php
        	} else {
        		?>
        		<img class="wcmamtx_upload_image_icon" src="<?php echo wcmamtx_placeholder_img_src(); ?>">
        		<?php
        	}

        	

        	break;



        	default:

        	$icon ='fa fa-file-alt';

			switch($key) {
				case "dashboard":
				$icon ='fa fa-tachometer-alt';
				break;

				case "orders":
				$icon ='fa fa-shopping-basket';
				break;

				case "downloads":
				$icon ='fa fa-file-download';
				break;

				case "edit-address":
				$icon ='fa fa-home';
				break;

				case "edit-account":
				$icon ='fa fa-user';
				break;

				case "customer-logout":
				$icon ='fa fa-sign-out-alt';
				break;

                case "wishlist":
				$icon ='fa fa-heart';
				break;

				case "points":
				$icon ='fa fa-award';
				break;

				case "wt-smart-coupon":
				$icon ='fa fa-gift';
				break;

				case "my-auction-setting":
				$icon ='fa fa-cog';
				break;

				case "my-auction":
				$icon ='fa fa-gavel';
				break;

				case "my-auction-watchlist":
				$icon ='fa fa-list';
				break;

				case "woo-wallet":
				$icon ='fa fa-wallet';
				break;

				case "wps_subscriptions":
				$icon ='fa fa-user-plus';
				break;
                
                case "subscriptions":
				$icon ='fa fa-user-plus';
				break;

				default:
				$icon ='fa fa-file-alt';
				break;

			}

			if ($icon != '') { ?>
				<i class="<?php echo $icon; ?>"></i>
			<?php } else { ?>
				<i class="fa fa-file-alt"></i>
			<?php }
        	break;

        }


	}

}