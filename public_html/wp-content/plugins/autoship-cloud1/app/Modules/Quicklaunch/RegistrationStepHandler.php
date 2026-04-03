<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class implements the account registration step handler.
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
 * The account registration step handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class RegistrationStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * Initializes a new instance of the class.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_autoship_quicklaunch_registration_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles the request.
	 *
	 * @return void
	 */
	public function handle(): void {
		// Check if the quick launch is enabled.
		if ( ! FeatureManager::is_enabled( 'quicklaunch' ) ) {
			wp_send_json_error( array( 'message' => __( 'The Autoship Quick Launch is not enabled.', 'autoship' ) ) );
		}

		// If the register is not enabled, bypass it and continue using mock data.
		if ( ! FeatureManager::is_enabled( 'quicklaunch_account_register' ) ) {
			wp_send_json_success( array( 'message' => 'ok' ) );
		}

		// Get the login information from the request.
		$user_email    = $this->get_post_string_value( 'email' );
		$user_password = $this->get_post_string_value( 'password' );
		$user_phone    = preg_replace( '/\D+/', '', $this->get_post_string_value( 'phone' ) );

		// Validate that the user information is correct.
		$email_errors    = $this->validate_email( $user_email );
		$password_errors = $this->validate_password( $user_password );
		$phone_errors    = $this->validate_phone( $user_phone );
		$errors          = array_merge( $email_errors, $password_errors, $phone_errors );

		// If there are any errors, return the error message.
		if ( count( $errors ) > 0 ) {
			$error_message = $errors[0];

			// translators: %s is the validation error message.
			wp_send_json_error( array( 'message' => sprintf( __( 'Please correct the following error: %s.', 'autoship' ), $error_message ) ) );
		}

		// If the user doesn't have any sites, we can create a new one.
		try {
			AccountManager::register( $user_email, $user_password, $user_phone );

			autoship_log_entry(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( __( 'The user %s logged in successfully to the Qpilot service.', 'autoship' ), $user_email )
			);
		} catch ( Exception $exception ) {
			$error_message = $exception->getMessage();

			if ( 0 < stripos( $error_message, 'already registered' ) || 0 < stripos( $error_message, 'user could not be registered' ) ) {
				autoship_log_entry(
					__( 'Autoship Quicklaunch Exception', 'autoship' ),
					// translators: %s is the exception message.
					sprintf( 'The user could not be registered to the service because the email is already registered. Message: %s', $exception->getMessage() )
				);

				wp_send_json_error( array( 'message' => __( 'An account already exists under this email, please sign in.', 'autoship' ) ) );
			}

			autoship_log_entry(
				__( 'Autoship Quicklaunch Exception', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to login the user. Message: %s', $exception->getMessage() )
			);

			wp_send_json_error( array( 'message' => __( 'The user could not be registered. Please try again or contact support.', 'autoship' ) ) );
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
		}

		// All good. The tokens and site are stored successfully, which indicates the autoship installation is ready.
		wp_send_json_success( array( 'message' => 'ok' ) );
	}

	/**
	 * Validates an email address.
	 *
	 * @param string $email The given email address.
	 * @return array
	 */
	private function validate_email( string $email ): array {
		$errors = array();

		if ( empty( $email ) ) {
			$errors[] = 'Email is required.';
		} elseif ( ! is_email( $email ) ) {
			$errors[] = 'Invalid email format.';
		} elseif ( strlen( $email ) > 128 ) {
			$errors[] = 'Email must not exceed 128 characters.';
		}

		return $errors;
	}

	/**
	 * Validates a password.
	 *
	 * @param string $password The given password.
	 * @return array
	 */
	private function validate_password( string $password ): array {
		$errors = array();
		// Validate password.
		if ( empty( $password ) ) {
			$errors[] = 'Password is required.';
		} elseif ( strlen( $password ) < 10 || strlen( $password ) > 20 ) {
			$errors[] = 'Password must be between 10 and 20 characters.';
		}

		return $errors;
	}

	/**
	 * Validates the phone if it's present.
	 *
	 * @param string $phone The phone number to validate.
	 * @return array
	 */
	private function validate_phone( string $phone ): array {
		$errors = array();

		// If the phone is not empty, we can validate it.
		if ( ! empty( $phone ) ) {
			$numbers = preg_replace( '/\D+/', '', $phone );

			if ( strlen( $numbers ) < 7 || strlen( $numbers ) > 15 ) {

				$errors[] = 'The phone number must be between 7 and 15 characters.';
			}
		}

		return $errors;
	}
}
