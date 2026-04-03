<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The WordPress Nextime Http Client implementation.
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime\Implementations;

use Autoship\Services\Nextime\NextimeHttpClientInterface;
use Autoship\Services\Nextime\NextimeHttpException;
use Autoship\Services\Nextime\NextimeSettingsInterface;

/**
 * Base API client for Nextime service.
 *
 * Handles common functionality like authentication, HTTP requests, etc.
 *
 * @package Autoship\Services\Nextime
 * @since 2.10.1
 */
class WordPressNextimeHttpClient implements NextimeHttpClientInterface {

	/**
	 * The settings for Nextime.
	 *
	 * @var NextimeSettingsInterface
	 */
	private NextimeSettingsInterface $settings;

	/**
	 * Constructor.
	 *
	 * @param NextimeSettingsInterface $settings The settings for Nextime.
	 */
	public function __construct( NextimeSettingsInterface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get the source of the API call.
	 *
	 * @return NextimeSettingsInterface The source.
	 */
	public function get_settings(): NextimeSettingsInterface {
		return $this->settings;
	}

	/**
	 * Perform a GET request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 *
	 * @throws NextimeHttpException Thrown on error.
	 */
	public function get( string $endpoint, array $params = array() ): array {

		$endpoint = "{$this->settings->get_api_url()}/{$this->settings->get_site_id()}/$endpoint";

		if ( count( $params ) > 0 ) {
			$query     = '?' . http_build_query( $params );
			$endpoint .= $query;
		}

		$args = array(
			'headers' => $this->get_headers(),
			'timeout' => $this->settings->get_timeout(),
		);

		$response = wp_remote_get( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$error_code    = (int) $response->get_error_code();
			$error_message = $response->get_error_message();
			throw new NextimeHttpException( esc_html( $error_message ), esc_html( $error_code ) );
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			throw new NextimeHttpException( 'Invalid response from Nextime API' );
		}

		return $decoded;
	}

	/**
	 * Perform a POST request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param array  $params Optional query parameters.
	 * @return array The response data.
	 *
	 * @throws NextimeHttpException Thrown on error.
	 */
	public function post( string $endpoint, array $data, array $params = array() ): array {

		$endpoint = "{$this->settings->get_api_url()}/{$this->settings->get_site_id()}/$endpoint";

		if ( count( $params ) > 0 ) {
			$query     = '?' . http_build_query( $params );
			$endpoint .= $query;
		}

		$args = array(
			'headers' => $this->get_headers(),
			'body'    => wp_json_encode( $data ),
			'timeout' => $this->settings->get_timeout(),
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$error_code    = (int) $response->get_error_code();
			$error_message = $response->get_error_message();
			throw new NextimeHttpException( esc_html( $error_message ), esc_html( $error_code ) );
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			throw new NextimeHttpException( 'Invalid response from Nextime API' );
		}

		return $decoded;
	}

	/**
	 * Get the request headers.
	 *
	 * @return array The headers.
	 */
	private function get_headers(): array {
		return array(
			'Content-Type'      => 'application/json',
			'Accept'            => 'application/json',
			'nextime-apikey'    => $this->settings->get_site_token(),
			'X-Autoship-Client' => $this->settings->get_source(),
		);
	}
}
