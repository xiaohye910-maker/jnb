<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The site connection manager class.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core\Implementations;

use Autoship\Core\CredentialsInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Core\OAuthServiceInterface;
use Autoship\Core\SitesManagerInterface;
use Exception;

/**
 * The site connection manager class.
 *
 * @package Autoship
 */
class SitesManager implements SitesManagerInterface {

	/**
	 * The environment instance.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * The credentials interface.
	 *
	 * @var CredentialsInterface
	 */
	private CredentialsInterface $credentials;

	/**
	 * The OAuth service interface.
	 *
	 * @var OAuthServiceInterface
	 */
	private OAuthServiceInterface $oauth_service;

	/**
	 * Constructor.
	 *
	 * @param EnvironmentInterface  $environment   The environment instance.
	 * @param CredentialsInterface  $credentials   The credentials interface.
	 * @param OAuthServiceInterface $oauth_service The OAuth service interface.
	 */
	public function __construct(
		EnvironmentInterface $environment,
		CredentialsInterface $credentials,
		OAuthServiceInterface $oauth_service
	) {
		$this->environment   = $environment;
		$this->credentials   = $credentials;
		$this->oauth_service = $oauth_service;
	}

	/**
	 * Retrieves the user sites.
	 *
	 * @param string $user_email The user email.
	 * @param string $user_password The user password.
	 * @return array
	 * @throws Exception When the user could not log on or retrieve information about the sites.
	 */
	public function get_user_sites( string $user_email, string $user_password ): array {

		$response = wp_remote_post(
			$this->environment->get_api_url() . '/AccessTokens/Login',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'accessScope' => 'Merchant',
						'email'       => $user_email,
						'password'    => $user_password,
					)
				),
				'method'  => 'POST',
				'timeout' => 10,
			)
		);

		$token_bearer = null;
		$user_id      = null;

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$body = json_decode( $response['body'], true );
			if ( isset( $body['token'] ) ) {
				$token_bearer = $body['tokenBearerAuth'];
				$user_id      = $body['userId'];
			}
		}

		if ( empty( $token_bearer ) || empty( $user_id ) ) {
			throw new Exception( esc_html( __( 'Unable to login with the given information', 'autoship' ) ) );
		}

		$sites_response = wp_remote_request(
			$this->environment->get_api_url() . '/Sites',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token_bearer,
					'Content-Type'  => 'application/json',
				),
				'method'  => 'GET',
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $sites_response ) || ! is_array( $sites_response ) ) {
			throw new Exception( esc_html( __( 'Unable to retrieve sites from service.', 'autoship' ) ) );
		}

		// Retrieves the site information from the user.
		$sites = json_decode( $sites_response['body'], true );

		$store_url = strtolower( wp_parse_url( $this->environment->get_site_url(), PHP_URL_HOST ) );

		// Convert the complex response body to something simpler.
		$found_sites = array();
		foreach ( $sites as $site ) {
			$site_id        = $site['id'];
			$site_name      = $site['name'];
			$site_url       = $site['url'];
			$site_hostname  = strtolower( wp_parse_url( $site_url, PHP_URL_HOST ) );
			$site_wordpress = isset( $site['metadata']['_qpilot_wordpress_version'] );

			// If the site is not a WordPress site, or it doesn't match the url. Ignore it.
			if ( true !== $site_wordpress || $store_url !== $site_hostname ) {
				continue;
			}

			$found_sites[] = array(
				'id'   => $site_id,
				'name' => $site_name,
				'url'  => $site_url,
			);
		}

		return $found_sites;
	}

	/**
	 * Connects the current store to an autoship site. This method uses the stored autoship tokens.
	 *
	 * @return int The site identifier.
	 * @throws Exception When the tokens are not available or the site could not be created or connected.
	 */
	public function connect_site(): int {
		// Verifies the presence of tokens and client id using injected credentials interface.
		if ( ! $this->credentials->has_credentials() || ! $this->credentials->has_auth_token() ) {
			throw new Exception( 'The site could not be connected because the tokens are not available.' );
		}

		// Connect using the injected OAuth service.
		return $this->oauth_service->connect_site();
	}
}
