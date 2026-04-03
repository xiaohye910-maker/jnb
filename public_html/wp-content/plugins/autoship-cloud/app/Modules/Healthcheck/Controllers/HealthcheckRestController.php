<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * REST API controller for the Healthcheck status check endpoints.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Modules\Healthcheck\Controllers;

use Autoship\Core\ClockInterface;
use Autoship\Services\Healthcheck\HealthcheckSettingsInterface;
use \WP_REST_Request;
use \WP_REST_Response;
use \WP_REST_Server;

/**
 * REST controller for health check status check endpoints.
 *
 * Registers and handles the three QPilot integration status check
 * endpoints: PUT, POST, and GET. QPilot calls these endpoints to
 * verify connectivity and update the integration point timestamps.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class HealthcheckRestController {

	/**
	 * The REST namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'autoshipcloud/v1';

	/**
	 * The health check settings.
	 *
	 * @var HealthcheckSettingsInterface
	 */
	private HealthcheckSettingsInterface $settings;

	/**
	 * The clock service.
	 *
	 * @var ClockInterface
	 */
	private ClockInterface $clock;

	/**
	 * Constructor.
	 *
	 * @param HealthcheckSettingsInterface $settings The health check settings.
	 * @param ClockInterface               $clock    The clock service.
	 */
	public function __construct(
		HealthcheckSettingsInterface $settings,
		ClockInterface $clock
	) {
		$this->settings = $settings;
		$this->clock    = $clock;
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ), 99 );
	}

	/**
	 * Register the status check REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/statuscheck/put',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'handle_put' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/statuscheck/post',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_post' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/statuscheck/get',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle_get' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Permission callback for the status check endpoints.
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return bool|\WP_Error True if the request has permission.
	 */
	public function check_permission( WP_REST_Request $request ) {
		return apply_filters( 'autoship_qpilot_statuscheck_permission_check', true, $request );
	}

	/**
	 * Handle the PUT status check endpoint.
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_put( WP_REST_Request $request ): WP_REST_Response { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return $this->update_point_status( 'put' );
	}

	/**
	 * Handle the POST status check endpoint.
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_post( WP_REST_Request $request ) : WP_REST_Response{ // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return $this->update_point_status( 'post' );
	}

	/**
	 * Handle the GET status check endpoint.
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_get( WP_REST_Request $request ): WP_REST_Response { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return $this->update_point_status( 'get' );
	}

	/**
	 * Update the integration point status for the given type.
	 *
	 * @param string $type The integration type (put, post, or get).
	 *
	 * @return WP_REST_Response
	 */
	private function update_point_status( string $type ): WP_REST_Response {
		$timestamp = $this->clock->timestamp();
		$result    = $this->settings->set_point_status( $type, $timestamp );

		if ( false === $result ) {
			$response = new WP_REST_Response(
				array(
					'code'    => "autoship_{$type}_failure",
					'message' => 'Autoship ' . strtoupper( $type ) . ' Update Failed. WordPress Update WP Options Record Failed.',
				)
			);
			$response->set_status( 500 );

			return $response;
		}

		$response = new WP_REST_Response(
			array(
				'code'    => "autoship_{$type}_success",
				'message' => 'Autoship ' . strtoupper( $type ) . ' Update Success',
			)
		);
		$response->set_status( 200 );

		return $response;
	}
}
