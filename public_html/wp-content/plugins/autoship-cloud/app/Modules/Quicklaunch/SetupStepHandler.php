<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class adds the Quicklaunch into the WordPress admin area.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\EnvironmentInterface;
use Autoship\Core\FeatureManagerInterface;

/**
 * Implements the reset quicklaunch handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class SetupStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * The feature manager instance.
	 *
	 * @var FeatureManagerInterface
	 */
	private FeatureManagerInterface $feature_manager;

	/**
	 * The environment instance.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * Constructor.
	 *
	 * @param FeatureManagerInterface $feature_manager The feature manager.
	 * @param EnvironmentInterface    $environment The environment.
	 * @return void
	 */
	public function __construct( FeatureManagerInterface $feature_manager, EnvironmentInterface $environment ) {
		$this->feature_manager = $feature_manager;
		$this->environment     = $environment;
		add_action( 'wp_ajax_autoship_quicklaunch_step_handler', array( $this, 'handle' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_menu', array( $this, 'enqueue_menu' ) );
	}

	/**
	 * Handles the quicklaunch steps.
	 *
	 * @return void
	 */
	public function handle() {
		$step = $this->get_post_string_value( 'step' );

		switch ( $step ) {
			case 'welcome':
				$this->welcome_page();
				break;
			case 'dismiss':
				$this->dismiss_page();
				break;
			case 'lead':
				$lead_id = get_option( 'autoship_plugin_registration_lead_id' );

				// If there is an existing lead, omit the lead form and go to the account registration.
				if ( ! empty( $lead_id ) ) {
					$this->account_registration_page();
				} else {
					$this->lead_page();
				}

				break;
			case 'register':
				$this->account_registration_page();
				break;
			case 'login':
				$this->account_login_page();
				break;
			case 'connection':
				$this->site_connection_page();
				break;
			case 'product':
				$this->product_setup_page();
				break;
			case 'payments':
				$this->payments_setup_page();
				break;
			case 'completed':
				$this->completed_page();
				break;
			default:
				echo 'Invalid step';
				break;
		}

		wp_die();
	}

	/**
	 * Displays the last page on which the quicklaunch was finished.
	 *
	 * @return void
	 */
	public function home() {
		$step = get_option( 'autoship_quicklaunch_last_step', 'welcome' );

		if ( 'lead' === $step ) {
			$lead_id = get_option( 'autoship_plugin_registration_lead_id' );

			// If there is an existing lead, omit the lead form and go to the account registration.
			if ( ! empty( $lead_id ) ) {
				$step = 'register';
			}
		}

		$this->home_page( $step );
	}

	/**
	 * Displays the home page of the quicklaunch.
	 *
	 * @param string $display_step The step to display.
	 */
	public function home_page( string $display_step = 'welcome' ) {
		autoship_include_template( 'quicklaunch/home', array( 'current_step' => $display_step ) );
	}

	/**
	 * Displays the welcome page of the quicklaunch.
	 *
	 * @return void
	 */
	public function welcome_page() {
		$this->set_last_step( 'welcome' );

		autoship_include_template( 'quicklaunch/step-welcome' );
	}

	/**
	 * Displays the lead page of the quicklaunch.
	 *
	 * @return void
	 */
	public function lead_page() {
		$this->set_last_step( 'lead' );

		autoship_include_template( 'quicklaunch/step-lead' );
	}

	/**
	 * Displays the account registration page of the quicklaunch.
	 *
	 * @return void
	 */
	public function account_registration_page() {
		$this->set_last_step( 'register' );

		autoship_include_template( 'quicklaunch/step-register' );
	}

	/**
	 * Displays the account login page of the quicklaunch.
	 *
	 * @return void
	 */
	public function account_login_page() {
		$this->set_last_step( 'login' );

		autoship_include_template( 'quicklaunch/step-login' );
	}

	/**
	 * Displays the site connection page of the quicklaunch.
	 *
	 * @return void
	 */
	public function site_connection_page() {
		$this->set_last_step( 'connection' );

		autoship_include_template( 'quicklaunch/step-connection' );
	}

	/**
	 * Displays the product setup page of the quicklaunch.
	 *
	 * @return void
	 */
	public function product_setup_page() {
		$this->set_last_step( 'product' );

		autoship_include_template( 'quicklaunch/step-product' );
	}

	/**
	 * Displays the payment setup page of the quicklaunch.
	 *
	 * @return void
	 */
	public function payments_setup_page() {
		$this->set_last_step( 'payments' );

		autoship_include_template( 'quicklaunch/step-payments' );
	}

	/**
	 * Displays the completed page of the quicklaunch.
	 *
	 * @return void
	 */
	public function completed_page() {
		update_option( 'autoship_quicklaunch_completed', true );

		$this->set_last_step( 'completed' );

		autoship_include_template( 'quicklaunch/step-completed' );
	}

	/**
	 * Displays the dismissed page of the quicklaunch.
	 *
	 * @return void
	 */
	public function dismiss_page() {
		$this->set_last_step( 'dismiss' );
		autoship_include_template( 'quicklaunch/step-dismiss' );
	}

	/**
	 * Sets the last step of the quicklaunch.
	 *
	 * @param string $step The last step name.
	 *
	 * @return void
	 */
	private function set_last_step( string $step ): void {
		update_option( 'autoship_quicklaunch_last_step', $step );
	}

	/**
	 * Enqueues the assets for the quicklaunch page.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_assets( string $hook ) {
		if ( 'autoship-cloud_page_quicklaunch' !== $hook ) {
			return;
		}

		$version    = $this->environment->get_autoship_version();
		$plugin_url = $this->environment->get_autoship_plugin_url();

		wp_enqueue_style( 'autoship-quicklaunch-style', $plugin_url . 'styles/quicklaunch.css', array(), $version );
		wp_enqueue_script( 'autoship-quicklaunch-script', $plugin_url . 'js/quicklaunch.js', array( 'jquery' ), $version, true );
		wp_localize_script(
			'autoship-quicklaunch-script',
			'autoship_quicklauncher',
			array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'admin_url'      => admin_url( 'admin.php?page=dashboard' ),
				'phone_required' => $this->feature_manager->is_enabled( 'quicklaunch_phone_required' ),
			)
		);

		wp_enqueue_style( 'autoship_intl_tel_utils_styles', $plugin_url . 'assets/libs/intl-tel-input/css/intlTelInput.min.css', array(), $version );
		wp_enqueue_script( 'autoship_intl_tel_utils_script', $plugin_url . 'assets/libs/intl-tel-input/js/intlTelInputWithUtils.min.js', array(), $version, true );
	}

	/**
	 * Adds the Quicklaunch submenu to the admin page.
	 *
	 * @return void
	 */
	public function enqueue_menu(): void {
		add_submenu_page(
			'autoship',
			__( 'Autoship Quicklaunch', 'autoship' ),
			__( 'Quicklaunch', 'autoship' ),
			apply_filters( 'autoship_cloud_subpage_security', 'administrator', 'quicklaunch' ),
			'quicklaunch',
			array( $this, 'home' ),
			0
		);
	}
}
