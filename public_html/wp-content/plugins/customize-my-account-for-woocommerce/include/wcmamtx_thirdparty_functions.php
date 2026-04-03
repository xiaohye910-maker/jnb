<?php
if (!function_exists('wcmamtx_third_party_goahead_check')) {

	function wcmamtx_third_party_goahead_check($key) {
		$third_party_go_ahead = 'yes';

		$third_party_plug_array = array(
			'woo-wallet'=>array('woo-wallet/woo-wallet.php'),
			'wt-smart-coupon'=>array('wt-smart-coupons-for-woocommerce/wt-smart-coupon.php'),
			'wps_subscriptions'=>array('subscriptions-for-woocommerce/subscriptions-for-woocommerce.php'),
			'my-auction-setting'=>array('ultimate-woocommerce-auction/ultimate-woocommerce-auction.php'),
			'my-auction'=>array('ultimate-woocommerce-auction/ultimate-woocommerce-auction.php'),
			'my-auction-watchlist'=>array('ultimate-woocommerce-auction/ultimate-woocommerce-auction.php'),
			'affiliate-dashboard'=>array('yith-woocommerce-affiliates-premium/init.php','yith-woocommerce-affiliates/init.php'),
			'subscriptions'=>array('woocommerce-subscriptions/woocommerce-subscriptions.php'),
			'rtwalwm_affiliate_menu'=>array('affiliaa-affiliate-program-with-mlm/wp-wc-affiliate-program.php'),
			'points'=>array('points-and-rewards-for-woocommerce/points-rewards-for-woocommerce.php'),
		);

		$third_party_plug_array = apply_filters('wcmamtx_overide_supported_plugin_slugs',$third_party_plug_array);

		if (isset($third_party_plug_array[$key])) {
			$third_party_plug_slugs = $third_party_plug_array[$key];
		} else {
			return 'yes';
		}

		$match_index = 0;


		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		foreach ($third_party_plug_slugs as $ptvalue) {

			if ( is_plugin_active( $ptvalue )) {
                $match_index++;
		    }

		}

		if ( $match_index == 0) {
            $third_party_go_ahead = 'no';
		}

		return $third_party_go_ahead;
	}

}
?>