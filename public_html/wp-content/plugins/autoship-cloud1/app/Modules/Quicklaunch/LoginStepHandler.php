<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Implements the login handler.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\AccountManager;
use Autoship\Core\FeatureManager;
use Autoship\Core\SitesManager;
use Exception;

/**
 * Implements the login handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class LoginStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_autoship_quicklaunch_login_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles a logout request.
	 *
	 * @return void
	 */
	public function handle() {
		// Check if the quick launch is enabled.
		if ( ! FeatureManager::is_enabled( 'quicklaunch' ) ) {
			wp_send_json_error( array( 'message' => __( 'The Autoship Quick Launch is not enabled.', 'autoship' ) ) );
			return;
		}

		// If the lead registration is not enabled, bypass it and continue using mock data.
		if ( ! FeatureManager::is_enabled( 'quicklaunch_account_login' ) ) {
			wp_send_json_success(
				array(
					'operation' => 'choose',
					'message'   => 'ok',
				)
			);
			return;
		}

		// Get the login information from the request.
		$user_email    = $this->get_post_string_value( 'email' );
		$user_password = $this->get_post_string_value( 'password' );
		$found_sites   = array();
		try {
			$found_sites = SitesManager::get_user_sites( $user_email, $user_password );
		} catch ( Exception $exception ) {
			autoship_log_entry(
				__( 'Autoship Quicklaunch Exception', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to retrieve the user sites. Message: %s', $exception->getMessage() )
			);

			wp_send_json_error( array( 'message' => __( 'Unable to log on to the remote service.', 'autoship' ) ) );
			return;
		}

		if ( 1 === count( $found_sites ) ) {
			autoship_log_entry(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				__( 'The user logged in successfully. There is one site associated with the user and store url that can be connected. We will attempt to connect.', 'autoship' )
			);
		} else {
			autoship_log_entry(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				__( 'The user logged in successfully. There are no sites associated with the user and store url that can be connected. We will create a new site.', 'autoship' )
			);
		}

		// If the user doesn't have any sites, we can create a new one.
		try {
			AccountManager::login( $user_email, $user_password );

			autoship_log_entry(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( __( 'The user %s logged in successfully to the Qpilot service.', 'autoship' ), $user_email )
			);
		} catch ( Exception $exception ) {
			autoship_log_entry(
				__( 'Autoship Quicklaunch Exception', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to login the user. Message: %s', $exception->getMessage() )
			);

			wp_send_json_error( array( 'message' => __( 'The user could not be connected to the service.', 'autoship' ) ) );
			return;
		}

		try {
			SitesManager::connect_site();
		} catch ( Exception $exception ) {
			autoship_log_entry(
				__( 'Autoship Quicklaunch Exception', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to connect the user site. Message: %s', $exception->getMessage() )
			);

			wp_send_json_error( array( 'message' => __( 'The site could not be connected to the service.', 'autoship' ) ) );
			return;
		}

		wp_send_json_success(
			array(
				'operation' => 'created',
				'message'   => 'ok',
			)
		);
	}
}
