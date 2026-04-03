<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Nextime Http Client
 *
 * @package Autoship
 * @subpackage Nextime
 * @since 2.10.1
 */

namespace Autoship\Services\Nextime;

interface NextimeHttpClientInterface {
	/**
	 * Perform a GET request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws NextimeHttpException Thrown on error.
	 */
	public function get( string $endpoint, array $params = array() );

	/**
	 * Perform a POST request.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param array  $params Optional query parameters.
	 * @return mixed The response data.
	 * @throws NextimeHttpException Thrown on error.
	 */
	public function post( string $endpoint, array $data, array $params = array() );
}
