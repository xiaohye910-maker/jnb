<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The user account manager.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\AccountManagerInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Core\InstallerInterface;
use Autoship\Core\SettingsInterface;
use Exception;

/**
 * The user account manager.
 *
 * @package Autoship
 * @since 2.8.7
 */
class AccountManager implements AccountManagerInterface {

	/**
	 * The environment instance.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * The installer instance.
	 *
	 * @var InstallerInterface
	 */
	private InstallerInterface $installer;

	/**
	 * The settings instance.
	 *
	 * @var SettingsInterface
	 */
	private SettingsInterface $settings;

	/**
	 * Constructor.
	 *
	 * @param EnvironmentInterface $environment The environment instance.
	 * @param InstallerInterface   $installer   The installer instance.
	 * @param SettingsInterface    $settings    The settings instance.
	 */
	public function __construct( EnvironmentInterface $environment, InstallerInterface $installer, SettingsInterface $settings ) {
		$this->environment = $environment;
		$this->installer   = $installer;
		$this->settings    = $settings;
	}

	/**
	 * Log in the user using the quicklaunch method.
	 *
	 * @param string $user_email The user email.
	 * @param string $user_password The user password.
	 * @return void
	 * @throws Exception When an error occurs while logging in.
	 */
	public function login( string $user_email, string $user_password ) {
		// Get the installation keys to send it to the registration endpoint.
		$install_id    = $this->installer->get_installation_id();
		$install_key   = $this->installer->get_installation_key();
		$site_name     = $this->environment->get_site_name();
		$site_url      = $this->environment->get_site_url();
		$site_redirect = $this->environment->get_oauth_redirect_url();

		// Attempt to create the user in the QPilot endpoint.
		$endpoint = $this->environment->get_api_url() . '/Quicklaunch/LoginUser';
		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'Email'           => $user_email,
						'Password'        => $user_password,
						'SiteName'        => $site_name,
						'SiteUrl'         => $site_url,
						'SiteRedirectUrl' => $site_redirect,
						'InstallationId'  => $install_id,
						'InstallationKey' => $install_key,
						'Integration'     => 'WooCommerce',
					)
				),
				'method'  => 'POST',
				'timeout' => 30,
			)
		);

		// Check for an error while posting the registration data.
		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			// translators: %s is the error message returned by the registration service.
			throw new Exception( esc_html( sprintf( __( 'The login service returned: %s', 'autoship' ), $response->get_error_message() ) ) );
		}

		// Decode the response body to store the values into autoship options.
		$body    = json_decode( $response['body'], true );
		$options = $this->decode_credentials( $body );
		$stored  = $this->store_options( $options );

		if ( ! $stored ) {
			throw new Exception( esc_html( __( 'Failed to save all Autoship settings.', 'autoship' ) ) );
		}
	}

	/**
	 * Register the user using the quicklaunch method.
	 *
	 * @param string $user_email The user email.
	 * @param string $user_password The user password.
	 * @param string $user_phone The user phone.
	 * @return void
	 * @throws Exception When an error occurs while logging in.
	 */
	public function register( string $user_email, string $user_password, string $user_phone ): void {
		// Get the installation keys to send it to the registration endpoint.
		$install_id    = $this->installer->get_installation_id();
		$install_key   = $this->installer->get_installation_key();
		$site_name     = $this->environment->get_site_name();
		$site_url      = $this->environment->get_site_url();
		$site_redirect = $this->environment->get_oauth_redirect_url();

		// Attempt to create the user in the QPilot endpoint.
		$endpoint = $this->environment->get_api_url() . '/Quicklaunch/RegisterUser';
		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'Email'           => $user_email,
						'Password'        => $user_password,
						'PhoneNumber'     => $user_phone,
						'SiteName'        => $site_name,
						'SiteUrl'         => $site_url,
						'SiteRedirectUrl' => $site_redirect,
						'InstallationId'  => $install_id,
						'InstallationKey' => $install_key,
						'Integration'     => 'WooCommerce',
					)
				),
				'method'  => 'POST',
				'timeout' => 30,
			)
		);

		// Check for an error while posting the registration data.
		if ( is_wp_error( $response ) ) {
			// translators: %s is the error message returned by the registration service.
			throw new Exception( esc_html( sprintf( __( 'The registration service returned: %s', 'autoship' ), $response->get_error_message() ) ) );
		}

		if ( ! is_array( $response ) ) {
			throw new Exception( esc_html( __( 'The registration service returned an unexpected value.', 'autoship' ) ) );
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status ) {
			$message = json_decode( $response['body'] );

			// translators: %s is the error message returned by the registration service.
			throw new Exception( esc_html( sprintf( __( 'The registration service returned: %s', 'autoship' ), $message ) ) );
		}

		// Decode the response body to store the values into autoship options.
		$body    = json_decode( $response['body'], true );
		$options = $this->decode_credentials( $body );
		$stored  = $this->store_options( $options );

		if ( ! $stored ) {
			throw new Exception( esc_html( __( 'Failed to save all Autoship settings.', 'autoship' ) ) );
		}
	}

	/**
	 * Decodes the credentials from the given body and returns the options to store.
	 *
	 * @param array $body The body to decode.
	 * @return array
	 * @throws Exception When the critical information is missing from the body.
	 */
	private function decode_credentials( array $body ): array {
		$user_id          = $body['user']['id'] ?? null;
		$client_id        = $body['connection']['id'] ?? null;
		$client_secret    = $body['connection']['secret'] ?? null;
		$access_token     = $body['oAuth']['accessToken'] ?? null;
		$refresh_token    = $body['oAuth']['refreshToken'] ?? null;
		$expires_in       = $body['oAuth']['expiresIn'] ?? null;
		$token_created_at = time();

		if ( empty( $user_id ) || empty( $client_id ) || empty( $client_secret ) || empty( $access_token ) ) {
			throw new Exception( esc_html( __( 'Unable to decode credentials from the authentication service.', 'autoship' ) ) );
		}

		return array(
			'autoship_user_id'          => $user_id,
			'autoship_client_id'        => $client_id,
			'autoship_client_secret'    => $client_secret,
			'autoship_token_auth'       => $access_token,
			'autoship_refresh_token'    => $refresh_token,
			'autoship_token_expires_in' => $expires_in,
			'autoship_token_created_at' => $token_created_at,
		);
	}

	/**
	 * Stores the given options into WordPress options and returns true if all were successfully stored, false otherwise.
	 *
	 * @param array $options The options to store.
	 *
	 * @return bool
	 */
	private function store_options( array $options ): bool {
		if ( empty( $options ) ) {
			return false;
		}

		$success = true;

		foreach ( $options as $option_name => $option_value ) {
			// Skip empty or invalid option names.
			if ( empty( $option_name ) ) {
				continue;
			}

			// We only want to set $success to false if the option truly failed to update.
			$result = $this->settings->update_option( $option_name, $option_value );

			// If update_option returns false, check if it's because the value didn't change.
			// Using loose comparison because the comparisons can be between strings, integers and booleans.
			if ( ! $result && $this->settings->get_option( $option_name ) != $option_value ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
				$success = false;
			}
		}

		return $success;
	}
}
