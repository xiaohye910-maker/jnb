<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot;

use Exception;

/**
 * Base API client for QPilot service.
 *
 * Handles common functionality like authentication, HTTP requests, etc.
 *
 * @package Autoship\Services\QPilot
 * @since 1.0.0
 */
class QPilotHttpClient {
	/**
	 * Authentication token.
	 *
	 * @var string
	 */
	private $token_auth;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Site ID.
	 *
	 * @var int
	 */
	private $site_id;

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Source of the API call.
	 *
	 * @var string
	 */
	private $source;

	/**
	 * Constructor.
	 *
	 * @param string $api_url The QPilot API URL.
	 */
	public function __construct( string $api_url ) {
		$this->api_url = trailingslashit( $api_url );
		$this->source  = 'WordPress'; // phpcs:ignore
	}

	/**
	 * Get the authentication token.
	 *
	 * @return string The authentication token.
	 */
	public function get_token_auth(): string {
		return $this->token_auth ?? '';
	}

	/**
	 * Set the authentication token.
	 *
	 * @param string $token_auth The authentication token.
	 * @return void
	 */
	public function set_token_auth( string $token_auth ): void {
		$this->token_auth = $token_auth;
	}

	/**
	 * Get the user ID.
	 *
	 * @return int The user ID.
	 */
	public function get_user_id(): int {
		return $this->user_id ?? 0;
	}

	/**
	 * Set the user ID.
	 *
	 * @param int $user_id The user ID.
	 * @return void
	 */
	public function set_user_id( int $user_id ): void {
		$this->user_id = $user_id;
	}

	/**
	 * Get the site ID.
	 *
	 * @return int The site ID.
	 */
	public function get_site_id(): int {
		return $this->site_id ?? 0;
	}

	/**
	 * Set the site ID.
	 *
	 * @param int $site_id The site ID.
	 * @return void
	 */
	public function set_site_id( int $site_id ): void {
		$this->site_id = $site_id;
	}

	/**
	 * Set the source of the API call.
	 *
	 * @param string $source The source of the call.
	 * @return void
	 */
	public function set_source( string $source ): void {
		$this->source = $source;
	}

	/**
	 * Get the API URL.
	 *
	 * @return string The API URL.
	 */
	public function get_api_url(): string {
		return $this->api_url;
	}

	/**
	 * Get the source of the API call.
	 *
	 * @return string The source.
	 */
	public function get_source(): string {
		return $this->source;
	}

	/**
	 * Perform a GET request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws \Exception Thrown on error.
	 */
	public function get( string $endpoint, array $params = array() ) {
		$url = $this->api_url . ltrim( $endpoint, '/' );
		if ( count( $params ) > 0 ) {
			$query = '?' . http_build_query( $params );
			$url  .= $query;
		}

		$args = array(
			'method'  => 'GET',
			'headers' => $this->get_headers(),
			'timeout' => 20,
		);

		$response = wp_remote_get( $url, $args );

		return $this->process_response( $response );
	}

	/**
	 * Perform a POST request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws \Exception Thrown on error.
	 */
	public function post( string $endpoint, array $data, array $params = array() ) {
		$url = $this->api_url . ltrim( $endpoint, '/' );
		if ( count( $params ) > 0 ) {
			$query = '?' . http_build_query( $params );
			$url  .= $query;
		}

		$headers = $this->get_headers();
		$body    = wp_json_encode( $data );

		$args = array(
			'method'  => 'POST',
			'body'    => $body,
			'headers' => $headers,
			'timeout' => 20,
		);

		$response = wp_remote_post( $url, $args );

		return $this->process_response( $response );
	}

	/**
	 * Perform a PUT request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws \Exception Thrown on error.
	 */
	public function put( string $endpoint, array $data, array $params = array() ) {
		$url = $this->api_url . ltrim( $endpoint, '/' );
		if ( count( $params ) > 0 ) {
			$query = '?' . http_build_query( $params );
			$url  .= $query;
		}

		$headers = $this->get_headers();
		$body    = wp_json_encode( $data );

		$args = array(
			'method'  => 'PUT',
			'body'    => $body,
			'headers' => $headers,
			'timeout' => 20,
		);

		$response = wp_remote_request( $url, $args );

		return $this->process_response( $response );
	}

	/**
	 * Perform a PATCH request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws \Exception Thrown on error.
	 */
	public function patch( string $endpoint, array $data, array $params = array() ) {
		$url = $this->api_url . ltrim( $endpoint, '/' );
		if ( count( $params ) > 0 ) {
			$query = '?' . http_build_query( $params );
			$url  .= $query;
		}

		$headers = $this->get_headers();
		$body    = wp_json_encode( $data );

		$args = array(
			'method'  => 'PATCH',
			'body'    => $body,
			'headers' => $headers,
			'timeout' => 20,
		);

		$response = wp_remote_request( $url, $args );

		return $this->process_response( $response );
	}

	/**
	 * Perform a DELETE request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws \Exception Thrown on error.
	 */
	public function delete( string $endpoint, array $params = array() ) {
		$url = $this->api_url . ltrim( $endpoint, '/' );
		if ( count( $params ) > 0 ) {
			$query = '?' . http_build_query( $params );
			$url  .= $query;
		}

		$args = array(
			'method'  => 'DELETE',
			'headers' => $this->get_headers(),
			'timeout' => 20,
		);

		$response = wp_remote_request( $url, $args );

		return $this->process_response( $response );
	}

	/**
	 * Process the HTTP response and handle any errors.
	 *
	 * @param mixed $response The HTTP response.
	 * @return mixed The response data.
	 * @throws Exception Thrown on error.
	 */
	private function process_response( $response ) {
		// Check for WP error.
		if ( is_wp_error( $response ) ) {
			$error_code    = (int) $response->get_error_code();
			$error_message = $response->get_error_message();
			throw new Exception( esc_html( $error_message ), esc_html( $error_code ) );
		}

		// Parse the body if we can.
		$response_data = ! empty( $response['body'] ) ? json_decode( $response['body'] ) : true;

		// If there are no errors just return the results.
		if ( 200 === $response['response']['code'] || 202 === $response['response']['code'] ) {
			return $response_data;
		}

		// Set the default.
		$message = $response['response']['message'];
		$code    = (int) $response['response']['code'];

		// Use current message and code if there is no body.
		if ( empty( $response['body'] ) || ! is_object( $response_data ) ) {
			throw new Exception( esc_html( $message ), esc_html( $code ) );
		}

		// Check for custom user displayed messages.
		if ( isset( $response_data->messages ) ) {
			// Check for technical system errors.
			if ( isset( $response_data->messages->errors ) ) {
				$message = implode( ' ', $response_data->messages->errors );
			}

			// Check for user messages errors.
			if ( isset( $response_data->messages->userMessage ) ) {
				$message = implode( ' ', $response_data->messages->userMessage );
				$code    = 606; // Customer error notice code.
			}
		} elseif ( isset( $response_data->message ) ) {
			$message = $response_data->message;
		}

		throw new Exception( esc_html( $message ), esc_html( $code ) );
	}

	/**
	 * Get the request headers.
	 *
	 * @return array The headers.
	 */
	private function get_headers(): array {
		$headers = array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		);

		if ( ! empty( $this->token_auth ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->token_auth;
		}

		if ( ! empty( $this->source ) ) {
			$headers['X-Source'] = $this->source;
		}

		return $headers;
	}
}
