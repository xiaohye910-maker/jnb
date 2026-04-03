<?php
/**
 * The completed step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;

use Autoship\Domain\PaymentIntegrationFactory;
use Autoship\Domain\PaymentMethodType;

$client = new QPilotClient();

$integrations = $client->get_payment_integrations();
$integrated   = array();
foreach ( $integrations->items as $integration ) {
	$integrated[] = PaymentMethodType::get_enum_code( $integration->paymentMethodType ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
}

$installed_payment_methods = WC()->payment_gateways()->payment_gateways();
$enabled_payment_methods   = WC()->payment_gateways()->get_available_payment_gateways();
$supported_methods         = autoship_get_valid_payment_methods();
$installed                 = array();
$keys                      = array_keys( $supported_methods );

foreach ( $installed_payment_methods as $method ) {
	if ( in_array( $method->id, $keys, true ) ) {
		$installed[] = PaymentIntegrationFactory::create( $method->id, $method->settings );
	}
}

$proceed = false;
foreach ( $installed as $method ) {
	if ( in_array( $method->get_method_type(), $integrated, true ) ) {
		$proceed = true;
	}
}

?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-26"><?php echo esc_html( __( "Let's set up your", 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'payment methods', 'autoship' ) ); ?></span></h1>

	<p class="autoship-font-sm">
		<?php echo esc_html( __( "To process recurring orders smoothly, Autoship uses your store's payment gateway to securely handle subscriptions. Just choose the methods you'd like to offer — and we'll take care of the rest.", 'autoship' ) ); ?>
	</p>

	<div class="autoship-quicklaunch-content-step-fields">

	<?php if ( ! empty( $installed ) ) : ?>

		<h2><?php echo esc_html( __( 'Payment Gateways', 'autoship' ) ); ?></h2>

		<p class="autoship-font-sm"><?php echo esc_html( __( 'Here are the supported payment gateways installed on your store:', 'autoship' ) ); ?></p>

		<?php foreach ( $installed as $method ) : ?>
			<?php
			$is_enabled = false;
			foreach ( $enabled_payment_methods as $enabled_method ) {
				if ( $method->get_method_id() === $enabled_method->id ) {
					$is_enabled = true;
					break;
				}
			}

			$needs_setup = false;
			if ( ! $method->is_valid() ) {
				$needs_setup = true;
			}

			$is_installed = false;
			if ( in_array( $method->get_method_type(), $integrated, true ) ) {
				$is_installed = true;
			}

			$icon = Autoship_Plugin_Url . '/images/activity-icons/autoship_add.svg';
			if ( $needs_setup || ! $is_enabled ) {
				$icon = Autoship_Plugin_Url . '/images/activity-icons/autoship_alert.svg';
			} elseif ( $is_installed ) {
				$icon = Autoship_Plugin_Url . '/images/activity-icons/autoship_check.svg';
			}
			?>

		<div style="background-color: white; padding: 20px; border-radius: 10px; border: 1px solid orange; margin-bottom:20px;" >

			<div style="display:flex; justify-content: space-between;">
				<div>
					<h3 style="margin-bottom: 5px; margin-top: 5px;"><?php echo esc_html( $method->get_method_name() ); ?></h3>
					<small>
						<?php echo esc_html( __( 'Environment:', 'autoship' ) ); ?>
						<?php if ( $method->is_test_mode() ) : ?>
							<strong><?php echo esc_html( __( 'Testing', 'autoship' ) ); ?></strong>
						<?php else : ?>
							<strong><?php echo esc_html( __( 'Live', 'autoship' ) ); ?></strong>
						<?php endif; ?>
					</small>
					<br/>
					<small>
						<?php echo esc_html( __( 'Status:', 'autoship' ) ); ?>
						<?php if ( $is_enabled ) : ?>
							<strong><?php echo esc_html( __( 'Enabled', 'autoship' ) ); ?></strong>
						<?php else : ?>
							<strong><?php echo esc_html( __( 'Disabled', 'autoship' ) ); ?></strong>
						<?php endif; ?>
					</small>
					<br/>
				</div>
				<div>
					<img id="autoship_<?php echo esc_attr( $method->get_method_id() ); ?>_status_icon" src="<?php echo esc_url( $icon ); ?>"  style="width:60px; height: 60px" alt="Autoship"/>
				</div>
			</div>
			<div id="autoship_<?php echo esc_attr( $method->get_method_id() ); ?>_action">
				<?php if ( ! $is_enabled ) : ?>
					<p class="" style="color: red;"><?php echo esc_html( __( 'The payment gateway is not enabled in WooCommerce. Enable it to activate it on Autoship.', 'autoship' ) ); ?></p>
				<?php elseif ( $needs_setup ) : ?>
					<p class="" style="color: red;"><?php echo esc_html( __( 'You must finish configuring this payment gateway in WooCommerce before you can activate it on Autoship.', 'autoship' ) ); ?></p>
				<?php elseif ( $is_installed ) : ?>
					<p class="autoship-highlighted-text"><?php echo esc_html( __( 'This payment gateway is enabled on Autoship.', 'autoship' ) ); ?></p>
				<?php else : ?>
					<button style="margin-top: 10px; display:block;" data-gateway-id="<?php echo esc_attr( $method->get_method_id() ); ?>" class="gateway-setup-button button button-small button-primary"><?php echo esc_html( __( 'Enable on Autoship', 'autoship' ) ); ?></button>
				<?php endif; ?>
			</div>


		</div>

		<?php endforeach; ?>

		<p><strong><?php echo esc_html( __( 'Please', 'autoship' ) ); ?> <span class="autoship-highlighted-text"><?php echo esc_html( __( 'enable at least one supported payment gateway', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'to finish set up.', 'autoship' ) ); ?></strong></p>

		<?php elseif ( ! $proceed ) : ?>
			<div>
				<h2 class="autoship-no-gateways-installed-title">
					<?php echo esc_html( __( "Your store doesn't have any supported payment gateways installed", 'autoship' ) ); ?>
				</h2>

				<p class="autoship-font-sm"><?php echo esc_html( __( 'Here are some of the supported payment plugins available to download:', 'autoship' ) ); ?></p>

				<div class="autoship-gateway-download-container">
					<div class="autoship-gateway-download-elements">
						<div>
							<h3 class="autoship-gateway-download-title"><?php echo esc_html( __( 'WooCommerce Stripe Payment Gateway', 'autoship' ) ); ?></h3>
							<small>
								<?php echo esc_html( __( 'Author:', 'autoship' ) ); ?>
								<strong><a href="https://profiles.wordpress.org/automattic/" target="_blank"><?php echo esc_html( __( 'Automattic', 'autoship' ) ); ?></a></strong>
							</small>
							<br/>
						</div>
						<div>
							<a href="https://wordpress.org/plugins/woocommerce-gateway-stripe/" target="_blank" class="autoship-gateway-download-button button button-small button-primary"><?php echo esc_html( __( 'Download', 'autoship' ) ); ?></a>
						</div>
					</div>
				</div>

				<div class="autoship-gateway-download-container">
					<div class="autoship-gateway-download-elements">
						<div>
							<h3 class="autoship-gateway-download-title"><?php echo esc_html( __( 'Authorize.net for WooCommerce', 'autoship' ) ); ?></h3>
							<small>
								<?php echo esc_html( __( 'Author:', 'autoship' ) ); ?>
								<strong><a href="https://woocommerce.com/vendor/skyverge/" target="_blank"><?php echo esc_html( __( 'SkyVerge', 'autoship' ) ); ?></a></strong>
							</small>
							<br/>
						</div>
						<div>
							<a href="https://woocommerce.com/es/products/authorize-net/" target="_blank" class="autoship-gateway-download-button button button-small button-primary"><?php echo esc_html( __( 'Purchase', 'autoship' ) ); ?></a>
						</div>
					</div>
				</div>

				<div class="autoship-gateway-download-container">
					<div class="autoship-gateway-download-elements">
						<div>
							<h3 class="autoship-gateway-download-title"><?php echo esc_html( __( 'Braintree for WooCommerce Payment Gateway', 'autoship' ) ); ?></h3>
							<small>
								<?php echo esc_html( __( 'Author:', 'autoship' ) ); ?>
								<strong><a href="https://profiles.wordpress.org/woocommerce/" target="_blank"><?php echo esc_html( __( 'WooCommerce', 'autoship' ) ); ?></a></strong>
							</small>
							<br/>
						</div>
						<div>
							<a href="https://wordpress.org/plugins/woocommerce-gateway-paypal-powered-by-braintree" target="_blank" class="autoship-gateway-download-button button button-small button-primary"><?php echo esc_html( __( 'Download', 'autoship' ) ); ?></a>
						</div>
					</div>
				</div>

				<div class="autoship-gateway-download-container">
					<div class="autoship-gateway-download-elements">
						<div>
							<h3 class="autoship-gateway-download-title"><?php echo esc_html( __( 'Checkout.com Payment Gateway', 'autoship' ) ); ?></h3>
							<small>
								<?php echo esc_html( __( 'Author:', 'autoship' ) ); ?>
								<strong><a href="https://profiles.wordpress.org/checkoutintegration/" target="_blank"><?php echo esc_html( __( 'checkoutintegration', 'autoship' ) ); ?></a></strong>
							</small>
							<br/>
						</div>
						<div>
							<a href="https://wordpress.org/plugins/checkout-com-unified-payments-api" target="_blank" class="autoship-gateway-download-button button button-small button-primary"><?php echo esc_html( __( 'Download', 'autoship' ) ); ?></a>
						</div>
					</div>
				</div>

				<div class="autoship-gateway-download-container">
					<div class="autoship-gateway-download-elements">
						<div>
							<h3 class="autoship-gateway-download-title"><?php echo esc_html( __( 'NMI Gateway for WooCommerce', 'autoship' ) ); ?></h3>
							<small>
								<?php echo esc_html( __( 'Author:', 'autoship' ) ); ?>
								<strong><a href="https://profiles.wordpress.org/xlplugins/" target="_blank"><?php echo esc_html( __( 'XLPlugins', 'autoship' ) ); ?></a></strong>
							</small>
							<br/>
						</div>
						<div>
							<a href="https://wordpress.org/plugins/woofunnels-woocommerce-nmi-gateway/" target="_blank" class="autoship-gateway-download-button button button-small button-primary"><?php echo esc_html( __( 'Download', 'autoship' ) ); ?></a>
						</div>
					</div>
				</div>

				<p><strong><?php echo esc_html( __( 'Please', 'autoship' ) ); ?> <span class="autoship-highlighted-text"><?php echo esc_html( __( 'install, configure and enable at least one supported payment gateway', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'to finish set up.', 'autoship' ) ); ?></strong></p>
			</div>

		<?php endif; ?>

		<div class="autoship-mt-50 autoship-mb-50">
			<h2><?php echo esc_html( __( "Don't Require Credit Card Payments?", 'autoship' ) ); ?></h2>
	
			<p class="autoship-font-sm">
				<?php echo esc_html( __( 'We offer a specialized onboarding process for companies that do not require payment when subscription orders process.', 'autoship' ) ); ?>
			</p>
			<p class="autoship-font-sm">
				<?php echo esc_html( __( 'If you are a wholesaler or business that invoices customers for orders, please', 'autoship' ) ); ?> <a href="javascript:void(0);" id="payments-contact-button" style="text-decoration: none;"><?php echo esc_html( __( 'contact us now', 'autoship' ) ); ?></a> <?php echo esc_html( __( 'and we will guide you through setup & testing!', 'autoship' ) ); ?>
			</p>
		</div>
	</div>
	
	<div class="autoship-quicklaunch-content-step-actions">
		<div class="autoship-mb-20">
			<input type="hidden" id="autoship_quicklaunch_proceed" value="<?php echo esc_attr( $proceed ); ?>" />
			<button type="button" id="payments-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100" <?php echo esc_attr( ( true === $proceed ) ? '' : 'disabled="disabled' ); ?>><?php echo esc_html( __( 'Finish', 'autoship' ) ); ?></button>
			<p class="autoship-font-16">
				<?php echo esc_html( __( 'Still not convinced on the product setup?', 'autoship' ) ); ?>
				<a href="#" id="payments-back-button" class="autoship-quicklaunch-content-steps-action-link autoship-font-16"><?php echo esc_html( __( 'Go back', 'autoship' ) ); ?></a>
			</p>
		</div>
	</div>
</div>
