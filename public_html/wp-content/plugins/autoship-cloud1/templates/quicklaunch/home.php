<?php
/**
 * The base template for the Autoship Quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

use Autoship\Core\FeatureManager;

if ( ! isset( $current_step ) ) {
	$current_step = 'welcome';
}

$display_beacon = FeatureManager::is_enabled( 'quicklaunch_display_beacon' );

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
				<?php autoship_include_template( "quicklaunch/step-$current_step" ); ?>
			</div>
		</div>
	</div>
</div>

<?php if ( $display_beacon ) : ?>
	<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
	<script type="text/javascript">window.Beacon('init', '5ecf2750-6b64-45fc-83bf-7ad5c7af08a0')</script>
<?php endif; ?>
