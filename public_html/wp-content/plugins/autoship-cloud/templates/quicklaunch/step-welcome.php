<?php
/**
 * The welcome step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="autoship-quicklaunch-content-step">
	<h1><?php echo esc_html( __( 'Welcome to Autoship', 'autoship' ) ); ?></h1>

	<p class="autoship-quicklaunch-content-step-subtitle"><?php echo esc_html( __( "The world's", 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'most flexible', 'autoship' ) ); ?></span> <?php echo esc_html( __( 'subscription tool', 'autoship' ) ); ?></p>

	<img src="<?php echo esc_url( Autoship_Plugin_Url ); ?>/images/subscription-upsells.png"  class="autoship-step-image" alt="Autoship"/>

	<p class="autoship-font-md autoship-w-700"><?php echo esc_html( __( 'Autoship lets your customers', 'autoship' ) ); ?> <span class="autoship-highlighted-text"><?php echo esc_html( __( 'subscribe to any product', 'autoship' ) ); ?></span>, <?php echo esc_html( __( 'in any quantity', 'autoship' ) ); ?>, <span class="autoship-highlighted-text"><?php echo esc_html( __( 'on their terms', 'autoship' ) ); ?></span>.</p>

	<p class="autoship-mb-40 autoship-mt-50 autoship-font-md"><?php echo esc_html( __( 'Get started in just 5 minutes!', 'autoship' ) ); ?></p>
	<div class="autoship-quicklaunch-content-step-actions">
		<div class="autoship-mb-20">
			<button type="button" id="welcome-next-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Continue', 'autoship' ) ); ?></button>
			<a href="#" id="welcome-dismiss-button" class="autoship-quicklaunch-content-steps-action-link"><?php echo esc_html( __( "I'll set up my store manually", 'autoship' ) ); ?></a>
		</div>
	</div>
</div>

