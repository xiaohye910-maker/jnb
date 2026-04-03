<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * HTTP Error Codes utility for the Health check system.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Domain\Healthcheck;

/**
 * Static utility class for HTTP error code lookups.
 *
 * Extracts the error code map and expansion logic previously
 * embedded in src/api-health.php into a reusable domain class.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class HttpErrorCodes {

	/**
	 * The HTTP code used for customer-facing messages.
	 *
	 * Replaces the reference to QPilotClient::$customer_http_code
	 * to avoid legacy coupling.
	 *
	 * @var int
	 */
	const CUSTOMER_HTTP_CODE = 606;

	/**
	 * Retrieves the list of default error code messages and descriptions.
	 *
	 * Can be modified using the {@see autoship_api_error_codes} filter.
	 *
	 * @return array Error codes with the corresponding message and description.
	 */
	public static function get_codes(): array {

		$error_codes = array(
			301 => array(
				'msg'  => 'The requested resource has been moved permanently.',
				'desc' => 'The web page or resource has been permanently replaced with a different resource. This code is used for permanent URL redirection.',
			),
			302 => array(
				'msg'  => 'The requested resource has moved, but was found.',
				'desc' => 'The requested resource was found, just not at the location where it was expected. This code is used for temporary URL redirection.',
			),
			400 => array(
				'msg'  => 'Bad Request',
				'desc' => 'The server cannot or will not process the request due to an apparent client error (e.g., malformed request syntax, size too large, invalid request message framing, or deceptive request routing).',
			),
			401 => array(
				'msg'  => 'Unauthorized.',
				'desc' => 'This is returned by the server because authentication is required and has failed.',
			),
			403 => array(
				'msg'  => 'Access to that resource is forbidden.',
				'desc' => 'The request was valid, but the server is refusing action. The user might not have the necessary permissions for a resource, or may need an account of some sort.',
			),
			404 => array(
				'msg'  => 'The requested resource was not found.',
				'desc' => 'The requested resource could not be found but may be available in the future. Subsequent requests by the client are permissible.',
			),
			405 => array(
				'msg'  => 'Method not allowed.',
				'desc' => 'A request method is not supported for the requested resource; for example, a GET request on a form that requires data to be presented via POST, or a PUT request on a read-only resource.',
			),
			406 => array(
				'msg'  => 'Not acceptable response.',
				'desc' => 'The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request.',
			),
			408 => array(
				'msg'  => 'The server timed out waiting for the rest of the request from the browser.',
				'desc' => 'The server timed out waiting for the request. According to HTTP specifications: "The client did not produce a request within the time that the server was prepared to wait. The client MAY repeat the request without modifications at any later time."',
			),
			410 => array(
				'msg'  => 'The requested resource is gone and won\'t be coming back.',
				'desc' => 'Indicates that the resource requested is no longer available and will not be available again.',
			),
			429 => array(
				'msg'  => 'Too many requests.',
				'desc' => 'The user has sent too many requests in a given amount of time.',
			),
			499 => array(
				'msg'  => 'Client closed request.',
				'desc' => 'This is returned by NGINX when the client closes the request while NGINX is still processing it.',
			),
			500 => array(
				'msg'  => 'There was an error on the server and the request could not be completed.',
				'desc' => 'A generic error was returned by your server, an unexpected condition was encountered.',
			),
			501 => array(
				'msg'  => 'Not Implemented.',
				'desc' => 'The server either does not recognize the request method, or it lacks the ability to fulfil the request.',
			),
			503 => array(
				'msg'  => 'The server is unavailable to handle this request right now.',
				'desc' => 'The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a temporary state.',
			),
			504 => array(
				'msg'  => 'The server, acting as a gateway, timed out waiting for another server to respond.',
				'desc' => 'The server was acting as a gateway or proxy and did not receive a timely response from the upstream server',
			),
		);

		return apply_filters( 'autoship_api_error_codes', $error_codes );
	}

	/**
	 * Retrieves the message and description associated with an error code.
	 *
	 * Can be modified using the {@see autoship_api_error_code_mapping} filter.
	 *
	 * @param int    $code    The error code to look up.
	 * @param string $key     The key to return. Defaults to empty string which returns the full array.
	 * @param string $message The message to return if this is a user facing the error.
	 *
	 * @return array|string The message and description array, or a single value if $key is specified.
	 */
	public static function expand( int $code, string $key = '', string $message = '' ) {

		$defaults    = array(
			'msg'  => 'UnKnown Error',
			'desc' => 'There was an unknown error connecting to the server.',
		);
		$error_codes = self::get_codes();
		$error_codes = apply_filters( 'autoship_api_error_code_mapping', $error_codes, $code );

		// Check if this error is a User Facing Error and pass back without converting.
		if ( ! empty( $message ) && self::is_user_message( $code ) ) {

			$general_error = array(
				'msg'  => 'User Message',
				'desc' => $message,
			);

			return empty( $key ) ? $general_error : $general_error[ $key ];

		} elseif ( isset( $error_codes[ $code ] ) ) {

			return empty( $key ) ? $error_codes[ $code ] : $error_codes[ $code ][ $key ];

		}

		return empty( $key ) ? $defaults : $defaults[ $key ];
	}

	/**
	 * Checks if the supplied error code is a user message code.
	 *
	 * @param int $code The HTTP error code.
	 *
	 * @return bool True if it is a user message code, false otherwise.
	 */
	public static function is_user_message( int $code ): bool {
		return self::CUSTOMER_HTTP_CODE === $code;
	}
}
