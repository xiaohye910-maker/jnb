<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Autoship Utilities Installer Class
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\EnvironmentInterface;
use Autoship\Core\InstallerInterface;
use Autoship\Core\SettingsInterface;

/**
 * The installer class for the Autoship Utilities.
 *
 * @package Autoship
 * @since 2.8.7
 */
class Installer implements InstallerInterface {
	const INSTALL_ID_OPTION_NAME  = 'autoship_plugin_registration_install_id';
	const INSTALL_KEY_OPTION_NAME = 'autoship_plugin_registration_install_key';

	/**
	 * The installation ID.
	 *
	 * @var string
	 */
	private string $install_id;

	/**
	 * The installation key.
	 *
	 * @var string
	 */
	private string $install_key;

	/**
	 * The environment instance.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * The settings instance.
	 *
	 * @var SettingsInterface
	 */
	private SettingsInterface $settings;

	/**
	 * The constructor.
	 *
	 * @param EnvironmentInterface $environment The environment instance.
	 * @param SettingsInterface    $settings    The settings instance.
	 */
	public function __construct( EnvironmentInterface $environment, SettingsInterface $settings ) {
		$this->environment = $environment;
		$this->settings    = $settings;

		$install_id = $this->settings->get_option( self::INSTALL_ID_OPTION_NAME );
		if ( ! empty( $install_id ) ) {
			$this->install_id = $install_id;
		}

		$install_key = $this->settings->get_option( self::INSTALL_KEY_OPTION_NAME );
		if ( ! empty( $install_key ) ) {
			$this->install_key = $install_key;
		}
	}


	/**
	 * Gets the value indicating whether the installation is registered or not.
	 *
	 * @return bool
	 */
	public function is_installed(): bool {
		return $this->has_installation_id() && $this->has_installation_key();
	}

	/**
	 * Performs the registration of the current installation.
	 *
	 * @return bool
	 */
	public function install(): bool {
		// Check if the installation is already registered and return the key if it is.
		if ( $this->is_installed() ) {
			return $this->get_installation_key();
		}

		// Check if the installation ID is already set and generate a new one if it is not.
		if ( ! $this->has_installation_id() ) {
			$this->generate_installation_id();
		}

		// If the installation ID could not be generated. Log the error and return false.
		if ( empty( $this->install_id ) ) {
			return false;
		}

		$registration_data = array(
			'InstallId' => $this->install_id,
			'SiteUrl'   => $this->environment->get_site_url(),
			'Email'     => $this->environment->get_admin_email(),
			'Version'   => $this->environment->get_autoship_version(),
			'Creation'  => gmdate( 'Y-m-d\TH:i:s\Z' ),
		);

		$key = $this->register( $registration_data );
		if ( empty( $key ) ) {
			return false;
		}

		// Store the installation key in the database.
		$registered = $this->settings->update_option( self::INSTALL_KEY_OPTION_NAME, $key );
		if ( ! $registered ) {
			return false;
		}

		$this->install_key = $key;

		return true;
	}

	/**
	 * Indicates whether the installation is registered or not.
	 *
	 * @return bool True if the installation is registered, false otherwise.
	 */
	public function has_installation_id(): bool {
		return ! empty( $this->install_id );
	}

	/**
	 * Gets a value indicating whether the installation is registered or not.
	 *
	 * @return bool True if the installation is registered, false otherwise.
	 */
	private function has_installation_key(): bool {
		return ! empty( $this->install_key );
	}

	/**
	 * Gets the installation id.
	 *
	 * @return false|string The installation id string, false when the key is not found.
	 */
	public function get_installation_id() {
		if ( empty( $this->install_id ) ) {
			return false;
		}

		return $this->install_id;
	}

	/**
	 * Gets the installation key.
	 *
	 * @return false|string The installation key string, false when the key is not found.
	 */
	public function get_installation_key() {
		if ( empty( $this->install_key ) ) {
			return false;
		}

		return $this->install_key;
	}

	/**
	 * Registers the installation with the Autoship Registration server.
	 *
	 * @param array $registration_data The registration data.
	 * @return string The installation key or null if the key could not be retrieved from the remote service.
	 */
	private function register( array $registration_data ): string {

		$install_key = '';
		$response    = wp_remote_post(
			$this->environment->get_api_url() . '/Quicklaunch/Install',
			array(
				'headers' => array(
					'Content-Type'      => 'application/json',
					'Accept'            => 'application/json',
					'X-Autoship-Client' => "AC-WC-{$this->environment->get_autoship_version()}",
				),
				'body'    => wp_json_encode( $registration_data ),
			)
		);

		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return $install_key;
		}

		$body = json_decode( $response['body'], true );
		return sanitize_text_field( $body['installKey'] ?? '' );
	}

	/**
	 * Generates the installation ID.
	 *
	 * @return void
	 */
	private function generate_installation_id(): void {
		if ( ! empty( $this->install_id ) ) {
			return;
		}

		// Store the installation ID in the database.
		$install_id = wp_generate_uuid4();
		$updated    = $this->settings->update_option( self::INSTALL_ID_OPTION_NAME, $install_id );
		if ( ! $updated ) {
			// Log here.

			return;
		}

		$this->install_id = $install_id;
	}
}
