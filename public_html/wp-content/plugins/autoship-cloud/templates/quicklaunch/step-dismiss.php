<?php
/**
 * The dismiss step for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

defined( 'ABSPATH' ) || exit;

$settings_page = autoship_admin_settings_page_url();

?>
<div class="autoship-quicklaunch-content-step">
	<h1 class="autoship-font-26"><?php echo esc_html( __( 'Want to set things up', 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'on your own', 'autoship' ) ); ?></span>?</h1>
	
	<p class="autoship-font-md">
		<?php echo esc_html( __( 'You can always use our Quick Launch to walk through setup in just 5 minutes! Click on', 'autoship' ) ); ?>
		<strong><?php echo esc_html( __( 'Install Now', 'autoship' ) ); ?></strong> <?php echo esc_html( __( 'to restart it', 'autoship' ) ); ?>.
	</p>
	
	<div class="autoship-quicklaunch-content-step-fields " style="padding-top: 10px;">
		<div class="autoship-mb-50">
			<iframe width="500" height="315" src="https://www.youtube.com/embed/jXKzjHmk4UY?si=fRpgD9jFhZwttdPO" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
		</div>
		
		<div class="autoship-mb-50">
			<h2><?php echo esc_html( __( 'Autoship Resources', 'autoship' ) ); ?></h2>
			<p class="autoship-font-sm">
				<?php echo esc_html( __( 'Ready for manual setup? Start with the resources below. Remember, if you need help at any point, our support team is just a chat bubble away.', 'autoship' ) ); ?>
			</p>
			<ul>
				<li><a href="https://support.autoship.cloud/category/1079-getting-started" target="_blank" class="autoship-highlighted-text autoship-text-no-decoration autoship-font-sm"><?php echo esc_html( __( 'Getting Started Guide', 'autoship' ) ); ?></a></li>
				<li><a href="https://support.autoship.cloud/category/1080-install-and-launch" target="_blank" class="autoship-highlighted-text autoship-text-no-decoration autoship-font-sm"><?php echo esc_html( __( 'Install and Launch Guide', 'autoship' ) ); ?></a></li>
				<li><a href="https://support.autoship.cloud/category/388-customization" target="_blank" class="autoship-highlighted-text autoship-text-no-decoration autoship-font-sm"><?php echo esc_html( __( 'Customization Guides', 'autoship' ) ); ?></a></li>
				<li><a href="https://support.autoship.cloud/category/758-troubleshooting" target="_blank" class="autoship-highlighted-text autoship-text-no-decoration autoship-font-sm"><?php echo esc_html( __( 'Troubleshooting Guides', 'autoship' ) ); ?></a></li>
				<li><a href="https://support.autoship.cloud/category/375-frequently-asked-questions" target="_blank" class="autoship-highlighted-text autoship-text-no-decoration autoship-font-sm"><?php echo esc_html( __( 'Frequently Asked Questions', 'autoship' ) ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="autoship-quicklaunch-content-step-actions autoship-mt-50">
		<div class="autoship-mb-20">
			<button type="button" id="completed-reset-button" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100">
				<?php echo esc_html( __( 'Install Now', 'autoship' ) ); ?>
			</button>
			<p class="autoship-font-16">
				<a href="<?php echo esc_url( $settings_page ); ?>" id="settings-button" class="autoship-quicklaunch-content-steps-action-link autoship-font-16"><?php echo esc_html( __( 'Go to Settings', 'autoship' ) ); ?></a>
			</p>
		</div>
	</div>
</div>
