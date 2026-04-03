<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Implements the login handler.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

use Autoship\Core\AccountManagerInterface;
use Autoship\Core\FeatureManagerInterface;
use Autoship\Core\SitesManagerInterface;
use Autoship\Services\Logging\LoggerInterface;
use Exception;

/**
 * Implements the login handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class LoginStepHandler extends BaseStepHandler implements StepHandlerInterface {

	/**
	 * The feature manager instance.
	 *
	 * @var FeatureManagerInterface
	 */
	private FeatureManagerInterface $feature_manager;

	/**
	 * The account manager instance.
	 *
	 * @var AccountManagerInterface
	 */
	private AccountManagerInterface $account_manager;

	/**
	 * The sites manager instance.
	 *
	 * @var SitesManagerInterface
	 */
	private SitesManagerInterface $sites_manager;

	/**
	 * The logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param FeatureManagerInterface $feature_manager The feature manager.
	 * @param AccountManagerInterface $account_manager The account manager.
	 * @param SitesManagerInterface   $sites_manager The sites manager.
	 * @param LoggerInterface         $logger The logger.
	 * @return void
	 */
	public function __construct( FeatureManagerInterface $feature_manager, AccountManagerInterface $account_manager, SitesManagerInterface $sites_manager, LoggerInterface $logger ) {
		$this->feature_manager = $feature_manager;
		$this->account_manager = $account_manager;
		$this->sites_manager   = $sites_manager;
		$this->logger          = $logger;
		add_action( 'wp_ajax_autoship_quicklaunch_login_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles a logout request.
	 *
	 * @return void
	 */
	public function handle() {
		// Check if the quick launch is enabled.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch' ) ) {
			wp_send_json_error( array( 'message' => __( 'The Autoship Quick Launch is not enabled.', 'autoship' ) ) );
			return;
		}

		// If the lead registration is not enabled, bypass it and continue using mock data.
		if ( ! $this->feature_manager->is_enabled( 'quicklaunch_account_login' ) ) {
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
			$found_sites = $this->sites_manager->get_user_sites( $user_email, $user_password );
		} catch ( Exception $exception ) {
			$this->logger->log(
				__( 'Autoship Quicklaunch Exception', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to retrieve the user sites. Message: %s', $exception->getMessage() )
			);

			wp_send_json_error( array( 'message' => __( 'Unable to log on to the remote service.', 'autoship' ) ) );
			return;
		}

		if ( 1 === count( $found_sites ) ) {
			$this->logger->log(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				__( 'The user logged in successfully. There is one site associated with the user and store url that can be connected. We will attempt to connect.', 'autoship' )
			);
		} else {
			$this->logger->log(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				__( 'The user logged in successfully. There are no sites associated with the user and store url that can be connected. We will create a new site.', 'autoship' )
			);
		}

		// If the user doesn't have any sites, we can create a new one.
		try {
			$this->account_manager->login( $user_email, $user_password );

			$this->logger->log(
				__( 'Autoship Quicklaunch Info', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( __( 'The user %s logged in successfully to the Qpilot service.', 'autoship' ), $user_email )
			);
		} catch ( Exception $exception ) {
			$this->logger->log(
				__( 'Autoship Quicklaunch Exception', 'autoship' ),
				// translators: %s is the exception message.
				sprintf( 'An exception occurred when attempting to login the user. Message: %s', $exception->getMessage() )
			);

			wp_send_json_error( array( 'message' => __( 'The user could not be connected to the service.', 'autoship' ) ) );
			return;
		}

		try {
			$this->sites_manager->connect_site();
		} catch ( Exception $exception ) {
			$this->logger->log(
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
