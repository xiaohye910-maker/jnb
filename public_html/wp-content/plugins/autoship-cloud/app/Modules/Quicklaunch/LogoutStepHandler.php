<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class implements the account logout step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\FeatureManagerInterface;

/**
 * The logout step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class LogoutStepHandler implements StepHandlerInterface {

	/**
	 * The feature manager instance.
	 *
	 * @var FeatureManagerInterface
	 */
	private FeatureManagerInterface $feature_manager;

	/**
	 * Initializes a new instance of the class.
	 *
	 * @param FeatureManagerInterface $feature_manager The feature manager.
	 * @return void
	 */
	public function __construct( FeatureManagerInterface $feature_manager ) {
		$this->feature_manager = $feature_manager;
		add_action( 'wp_ajax_autoship_quicklaunch_logout_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles the request.
	 *
	 * @return void
	 */
	public function handle() {
		// Check if the quick launch is enabled.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch' ) ) {
			wp_send_json_error( array( 'message' => __( 'The Autoship Quick Launch is not enabled.', 'autoship' ) ) );
		}

		// If the logout is not enabled, bypass it and continue using mock data.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch_account_logout' ) ) {
			wp_send_json_success( array( 'message' => 'ok' ) );
		}

		delete_option( 'autoship_quicklaunch_login_token' );
		delete_option( 'autoship_quicklaunch_login_token_auth' );
		delete_option( 'autoship_quicklaunch_login_token_bearer' );
		delete_option( 'autoship_quicklaunch_login_token_user_id' );
		delete_option( 'autoship_quicklaunch_login_sites' );
		delete_option( 'autoship_quicklaunch_login_operation' );
		delete_option( 'autoship_quicklaunch_login_user' );
		delete_option( 'autoship_quicklaunch_login_password' );

		wp_send_json_success( array( 'message' => 'ok' ) );
	}
}
