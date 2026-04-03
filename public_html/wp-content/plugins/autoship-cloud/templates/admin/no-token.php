<?php
/**
 * The base template for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\Plugin;

$features            = Plugin::get_service_container()->get( FeatureManagerInterface::class );
$display_beacon      = $features->is_enabled( 'quicklaunch_display_beacon' );
$display_quicklaunch = $features->is_enabled( 'quicklaunch' );
$settings_page       = autoship_admin_settings_page_url();
$quicklaunch_page    = admin_url( 'admin.php?page=quicklaunch' );

wp_enqueue_style( 'autoship-quicklaunch-style', plugin_dir_url( Autoship_Plugin_File ) . 'styles/quicklaunch.css', array(), Autoship_Version );
?>

<div class="autoship-quicklaunch-container">
	<div class="autoship-quicklaunch-header-wrapper">
		<div class="autoship-quicklaunch-header-container">
			<div class="autoship-quicklaunch-header-cta-container">
				<button id="autoship-button-demo" class="autoship-button-demo"><?php echo esc_html( __( 'Schedule a demo', 'autoship' ) ); ?></button>
			</div>
			<div class="autoship-quicklaunch-header-logo-container">
				<img class="autoship-quicklaunch-header-logo" src="<?php echo esc_url( Autoship_Plugin_Url ); ?>/images/autoship_logo_white.svg"  alt="Autoship"/>
			</div>
		</div>
		<div id="autoship-progress">
			<span id="autoship-progress-bar" style="width: 0"></span>
		</div>
	</div>
	<div class="autoship-quicklaunch-content-wrapper">
		<div class="autoship-quicklaunch-content-steps-container">
			<div id="autoship-quicklaunch-content">
				<div class="autoship-quicklaunch-content-step">
					<h1><?php echo esc_html( __( 'Ready to get', 'autoship' ) ); ?> <span class="autoship-orange-text"><?php echo esc_html( __( 'started', 'autoship' ) ); ?></span>?</h1>

					<p class="autoship-font-md autoship-w-700 autoship-mb-50"><?php echo esc_html( __( 'Autoship lets your customers', 'autoship' ) ); ?> <span class="autoship-highlighted-text"><?php echo esc_html( __( 'subscribe to any product', 'autoship' ) ); ?></span>, <?php echo esc_html( __( 'in any quantity', 'autoship' ) ); ?>, <span class="autoship-highlighted-text"><?php echo esc_html( __( 'on their terms', 'autoship' ) ); ?></span>.</p>

					<img src="<?php echo esc_url( Autoship_Plugin_Url ); ?>/images/b2c-image.png"  class="autoship-step-image" alt="Autoship"/>

					<p class="autoship-font-md autoship-w-700 autoship-mt-40">
						Give your customers flexibility and scale your revenue with confidence.
					</p>

					<p class="autoship-mb-40 autoship-mt-50 autoship-font-md"><?php echo esc_html( __( 'Get started in just 5 minutes!', 'autoship' ) ); ?></p>
					<div class="autoship-quicklaunch-content-step-actions">
						<div class="autoship-mb-20">
							<?php if ( $display_quicklaunch ) : ?>
								<button type="button" id="autoship-go-to-quicklaunch-button" data-quicklaunch-url="<?php echo esc_url( $quicklaunch_page ); ?>" class="autoship-button-call-to-action autoship-mb-20 autoship-width-100"><?php echo esc_html( __( 'Install Now', 'autoship' ) ); ?></button>
							<?php endif; ?>
							<a href="<?php echo esc_url( $settings_page ); ?>" class="autoship-quicklaunch-content-steps-action-link"><?php echo esc_html( __( 'Go to Settings', 'autoship' ) ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if ( $display_beacon ) : ?>
	<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
	<script type="text/javascript">window.Beacon('init', '5ecf2750-6b64-45fc-83bf-7ad5c7af08a0')</script>
<?php endif; ?>
