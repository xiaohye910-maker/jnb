<?php
/**
 * This template is used to display the Autoship Cloud Shipping Rates in the admin area.
 *
 * @package Autoship
 * @since 1.0.0
 */

?>

<?php do_action( 'autoship_before_autoship_admin_shipping_rates' ); ?>
	<iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo rawurlencode( $site_id ); ?>/shipping-rates?tokenBearerAuth=<?php echo rawurlencode( $token_auth ); ?>" class="autoship-admin-shipping-rates-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
<?php do_action( 'autoship_after_autoship_admin_shiping_rates' ); ?>
