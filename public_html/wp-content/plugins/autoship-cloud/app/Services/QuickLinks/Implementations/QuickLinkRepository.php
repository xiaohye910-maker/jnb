<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLink repository implementation.
 *
 * Handles all communication with the QPilot API for QuickLink operations.
 *
 * @package Autoship\Services\QuickLinks\Implementations
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Implementations;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\Logging\LoggerInterface;

/**
 * QuickLink repository implementation.
 *
 * Handles QPilot API communication for QuickLink operations.
 */
class QuickLinkRepository implements QuickLinkRepositoryInterface {

	/**
	 * QPilot HTTP client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $client;

	/**
	 * Logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param QPilotHttpClient $client The QPilot HTTP client.
	 * @param LoggerInterface  $logger The logger instance.
	 */
	public function __construct( QPilotHttpClient $client, LoggerInterface $logger ) {
		$this->client = $client;
		$this->logger = $logger;
	}

	/**
	 * Call QPilot verify endpoint.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID.
	 * @param string|null $token              Optional security token.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return array|null Response data or null on failure.
	 */
	public function verify(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		?string $ip_address = null,
		?string $user_agent = null
	): ?array {
		try {
			$endpoint = "/Sites/{$site_id}/Quicklinks/Verify/{$slug}";

			$data = array(
				'scheduledOrderId' => $scheduled_order_id,
			);

			if ( null !== $customer_id ) {
				$data['customerId'] = $customer_id;
			}

			if ( null !== $token ) {
				$data['token'] = $token;
			}

			if ( null !== $ip_address ) {
				$data['ipAddress'] = $ip_address;
			}

			if ( null !== $user_agent ) {
				$data['userAgent'] = $user_agent;
			}

			$response = $this->client->post( $endpoint, $data );

			return $this->to_array( $response );
		} catch ( \Exception $e ) {
			do_action(
				'autoship_log',
				'error',
				'QuickLink verify failed',
				array(
					'site_id'            => $site_id,
					'slug'               => $slug,
					'scheduled_order_id' => $scheduled_order_id,
					'error'              => $e->getMessage(),
				)
			);

			return null;
		}
	}

	/**
	 * Call QPilot consume endpoint.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID.
	 * @param string|null $token              Optional security token.
	 * @param bool        $success            Whether action succeeded.
	 * @param string|null $error_code         Error code if failed.
	 * @param string|null $error_message      Error message if failed.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return array|null Response data or null on failure.
	 */
	public function consume(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		bool $success = true,
		?string $error_code = null,
		?string $error_message = null,
		?string $ip_address = null,
		?string $user_agent = null
	): ?array {
		try {
			$endpoint = "/Sites/{$site_id}/Quicklinks/{$slug}/Consume";

			$data = array(
				'scheduledOrderId' => $scheduled_order_id,
				'success'          => $success,
			);

			if ( null !== $customer_id ) {
				$data['customerId'] = $customer_id;
			}

			if ( null !== $token ) {
				$data['token'] = $token;
			}

			if ( null !== $error_code ) {
				$data['errorCode'] = $error_code;
			}

			if ( null !== $error_message ) {
				$data['errorMessage'] = $error_message;
			}

			if ( null !== $ip_address ) {
				$data['ipAddress'] = $ip_address;
			}

			if ( null !== $user_agent ) {
				$data['userAgent'] = $user_agent;
			}

			$response = $this->client->post( $endpoint, $data );

			return $this->to_array( $response );
		} catch ( \Exception $e ) {
			do_action(
				'autoship_log',
				'error',
				'QuickLink consume failed',
				array(
					'site_id'            => $site_id,
					'slug'               => $slug,
					'scheduled_order_id' => $scheduled_order_id,
					'error'              => $e->getMessage(),
				)
			);

			return null;
		}
	}

	/**
	 * Change scheduled order status.
	 *
	 * @param int    $site_id            The QPilot site ID.
	 * @param int    $scheduled_order_id The scheduled order ID.
	 * @param string $status             The new status ('Active' or 'Paused').
	 *
	 * @return bool Whether status was changed successfully.
	 */
	public function change_status( int $site_id, int $scheduled_order_id, string $status ): bool {
		$endpoint = "/Sites/{$site_id}/ScheduledOrders/{$scheduled_order_id}/Status/{$status}";

		$this->log(
			sprintf(
				'ChangeStatus API call: PUT %s for order %d to status %s',
				$endpoint,
				$scheduled_order_id,
				$status
			)
		);

		try {
			$response = $this->client->put( $endpoint, array() );

			$this->log(
				sprintf(
					'ChangeStatus API success for order %d. New status: %s. Response: %s',
					$scheduled_order_id,
					$status,
					is_object( $response ) || is_array( $response ) ? wp_json_encode( $response ) : (string) $response
				)
			);

			return null !== $response;
		} catch ( \Exception $e ) {
			$this->log_error(
				sprintf(
					'ChangeStatus API failed for order %d to status %s. Error code: %s, Message: %s',
					$scheduled_order_id,
					$status,
					$e->getCode(),
					$e->getMessage()
				)
			);

			do_action(
				'autoship_log',
				'error',
				'QuickLink change status failed',
				array(
					'site_id'            => $site_id,
					'scheduled_order_id' => $scheduled_order_id,
					'status'             => $status,
					'error'              => $e->getMessage(),
				)
			);

			return false;
		}
	}

	/**
	 * Retry scheduled order (process now).
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return bool Whether retry was successful.
	 * @throws \Exception When the API call fails.
	 */
	public function retry_order( int $site_id, int $scheduled_order_id ): bool {
		$endpoint = "/Sites/{$site_id}/ScheduledOrders/{$scheduled_order_id}/Retry";

		$this->log(
			sprintf(
				'Retry API call: POST %s for order %d',
				$endpoint,
				$scheduled_order_id
			)
		);

		try {
			$response = $this->client->post( $endpoint, array() );

			$this->log(
				sprintf(
					'Retry API success for order %d. Response: %s',
					$scheduled_order_id,
					is_object( $response ) || is_array( $response ) ? wp_json_encode( $response ) : (string) $response
				)
			);

			return null !== $response;
		} catch ( \Exception $e ) {
			$this->log_error(
				sprintf(
					'Retry API failed for order %d. Error code: %s, Message: %s',
					$scheduled_order_id,
					$e->getCode(),
					$e->getMessage()
				)
			);

			// Re-throw so the action can handle it with full context.
			throw $e;
		}
	}

	/**
	 * Safe activate scheduled order.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return bool Whether activation was successful.
	 */
	public function safe_activate( int $site_id, int $scheduled_order_id ): bool {
		$endpoint = "/Sites/{$site_id}/ScheduledOrders/{$scheduled_order_id}/SafeActivate";

		$this->log(
			sprintf(
				'SafeActivate API call: PUT %s for order %d',
				$endpoint,
				$scheduled_order_id
			)
		);

		try {
			$response = $this->client->put( $endpoint, array() );

			$this->log(
				sprintf(
					'SafeActivate API success for order %d. Response: %s',
					$scheduled_order_id,
					is_object( $response ) || is_array( $response ) ? wp_json_encode( $response ) : (string) $response
				)
			);

			return null !== $response;
		} catch ( \Exception $e ) {
			$this->log_error(
				sprintf(
					'SafeActivate API failed for order %d. Error code: %s, Message: %s',
					$scheduled_order_id,
					$e->getCode(),
					$e->getMessage()
				)
			);

			do_action(
				'autoship_log',
				'error',
				'QuickLink safe activate failed',
				array(
					'site_id'            => $site_id,
					'scheduled_order_id' => $scheduled_order_id,
					'error'              => $e->getMessage(),
				)
			);

			return false;
		}
	}

	/**
	 * Convert a response to an array.
	 *
	 * Handles stdClass objects returned by json_decode.
	 *
	 * @param mixed $response The response to convert.
	 *
	 * @return array|null The response as an array, or null if conversion fails.
	 */
	private function to_array( $response ): ?array {
		if ( null === $response ) {
			return null;
		}

		if ( is_array( $response ) ) {
			return $response;
		}

		if ( is_object( $response ) ) {
			return json_decode( wp_json_encode( $response ), true );
		}

		return null;
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private function log( string $message ): void {
		$this->logger->info( 'Autoship QuickLinks', $message );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private function log_error( string $message ): void {
		$this->logger->error( 'Autoship QuickLinks', $message );
	}
}
