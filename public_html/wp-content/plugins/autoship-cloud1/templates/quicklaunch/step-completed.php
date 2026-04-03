<?php
/**
 * The completed step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

use Autoship\Core\FeatureManager;

defined( 'ABSPATH' ) || exit;

$product_id = get_option( 'autoship_quicklaunch_product', 0 );


$slug = '';
if ( ! empty( $product_id ) ) {
	$product = wc_get_product( $product_id );
	if ( ! is_wp_error( $product ) && ! empty( $product ) ) {
		$slug = $product->get_slug();
	}
}

$is_reset_enabled = FeatureManager::is_enabled( 'quicklaunch_reset' );
?>

<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-28"><span class="autoship-orange-text"><?php echo esc_html( __( 'Great job!', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'Autoship is ready to go!', 'autoship' ) ); ?></h1>

	<p class="autoship-quicklaunch-content-step-subtitle"><?php echo esc_html( __( 'Subscribe and save', 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'is just the beginning', 'autoship' ) ); ?></span></p>

	<p class="autoship-mt-50 autoship-font-md">
		<?php echo esc_html( __( "With Autoship activated, you're ready to deliver flexible, recurring experiences your customers will love.", 'autoship' ) ); ?>
	</p>

	<img src="<?php echo esc_url( Autoship_Plugin_Url ); ?>/images/b2c-image.png"  class="autoship-step-image" alt="Autoship"/>
	

	<p class="autoship-mt-40 autoship-mb-50 autoship-font-md">
		<strong>
			<?php echo esc_html( __( 'Now, visit your product page, select a subscription frequency, and complete checkout to see how it all works firsthand.', 'autoship' ) ); ?>
		</strong>
	</p>

	<div class="autoship-quicklaunch-content-step-actions">
		<div class="autoship-mb-20">

			<?php if ( ! empty( $slug ) ) : ?>
				<button id="completed-product-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100" data-product-url="<?php echo esc_url( home_url( $slug ) ); ?>">
					<?php echo esc_html( __( 'Go to Product Page', 'autoship' ) ); ?>
				</button>
			<?php endif; ?>
   
			<button type="button" id="completed-dashboard-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100" style="background-color:#5034F5; ">
				<?php echo esc_html( __( 'Go to Autoship Dashboard', 'autoship' ) ); ?>
			</button>

			<?php if ( $is_reset_enabled ) : ?>
				<a href="#" id="completed-reset-button" class="autoship-quicklaunch-content-steps-action-link autoship-font-16">
					<?php echo esc_html( __( 'Reset the Quicklaunch', 'autoship' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>