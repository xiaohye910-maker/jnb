<?php
/**
 * Healthcheck legacy compatibility wrappers.
 *
 * All logic has been migrated to the Healthcheck module in app/.
 * These functions delegate to the service container and exist solely
 * for backward compatibility with 60+ call sites in legacy code.
 *
 * Hook registrations (add_action / add_filter) have been moved to
 * HealthcheckAjaxController::register_hooks(),
 * HealthcheckRestController::register_hooks(), and
 * HealthcheckCompatibility::register_hooks().
 *
 * @package Autoship
 * @since 1.0.0
 * @deprecated 2.12.0 Consolidated from src/api-health.php and src/api.php.
 */

use Autoship\Core\Plugin;
use Autoship\Domain\Healthcheck\HttpErrorCodes;
use Autoship\Modules\Healthcheck\HealthcheckService;
use Autoship\Services\Healthcheck\HealthcheckSettingsInterface;

// ==========================================================
// HTTP Error Code Helpers
// ==========================================================

/**
 * Checks if the supplied Error Code is a User Message Code.
 *
 * @param int $code The HTTP Error Code.
 *
 * @return bool True if it is else false.
 */
function autoship_is_user_http_message( $code ) {
	return HttpErrorCodes::is_user_message( (int) $code );
}

/**
 * Retrieves the list of default error code messages and descriptions.
 * Can be modified using {@see autoship_api_error_codes} filer.
 *
 * @return array of arrays. codes with corresponding message and description.
 */
function autoship_http_codes() {
	return HttpErrorCodes::get_codes();
}

/**
 * Retrieves the Message and Description associated with an error code.
 * Can be modified using the {@see autoship_api_error_code_mapping} filter.
 *
 * @param int    $code The error code to look up.
 * @param string $key The key to return. Defaults to empty string which returns the full array.
 * @param string $message The message to return if this is a user facing error.
 *
 * @return array     An array of the message and description.
 */
function autoship_expand_http_code( $code, $key = '', $message = '' ) {
	return HttpErrorCodes::expand( (int) $code, $key, $message );
}

// ==========================================================
// Integration Health Status
// ==========================================================

/**
 * Conditional Clearing of the Integration Status for when saving
 * Any settings that effect The Health Status checks.
 *
 * @param mixed $oldvalue The old option value.
 * @param mixed $_newvalue The new option value.
 */
function autoship_refresh_integration_status_on_save( $oldvalue, $_newvalue ) {
	if ( $oldvalue !== $_newvalue ) {
		autoship_clear_integration_point_statuses();
	}
}

/**
 * Retrieves a list of the integration status timestamp
 * fields and corresponding REST actions.
 *
 * @return array The fields to actions.
 */
function autoship_integration_status_fields() {
	try {
		$settings = Plugin::get_service_container()->get( HealthcheckSettingsInterface::class );
		return $settings->get_status_fields();
	} catch ( Exception $e ) {
		// Fallback if container not ready.
		return array(
			'autoship_wc_get_checked_utc' => 'wc_get',
			'autoship_put_checked_utc'    => 'put',
			'autoship_post_checked_utc'   => 'post',
		);
	}
}

/**
 * Updates the integrations status flag
 *
 * @param bool|string $health True or 'healthy' if HEALTHY else UNHEALTHY. Default 'healthy'.
 * @param string      $notice Optional notice to add to the health status.
 *
 * @return string        The updated health status. 'healthy' or 'unhealthy'.
 */
function autoship_update_integration_health_status( $health = 'healthy', $notice = '' ) {
	$val = ( true === $health ) || ( 'healthy' === strtolower( (string) $health ) ) ? 'healthy' : 'unhealthy';

	try {
		$settings = Plugin::get_service_container()->get( HealthcheckSettingsInterface::class );
		$settings->set_health_status( $val );
		$settings->set_health_details( $notice );
	} catch ( Exception $e ) {
		// Fallback if container not ready.
		update_option( 'autoship_health', $val, false );
		update_option( 'autoship_health_details', $notice, false );
	}

	return $val;
}


/**
 * Gets the integrations status flag
 *
 * @return bool False if UNHEALTHY or true if HEALTHY
 */
function autoship_get_integration_health_status() {
	try {
		$settings = Plugin::get_service_container()->get( HealthcheckSettingsInterface::class );
		return $settings->get_health_status();
	} catch ( Exception $e ) {
		// Fallback if container not ready.
		wp_cache_delete( 'autoship_health', 'options' );
		return get_option( 'autoship_health' );
	}
}


/**
 * Retrieves the integrations status for the supplied type
 *
 * @param string $type The integration status to get. Valid types are GET, POST, PUT.
 *
 * @return string        The UTC Unix Timestamp or empty string.
 */
function autoship_get_integration_point_status( $type ) {
	try {
		$settings = Plugin::get_service_container()->get( HealthcheckSettingsInterface::class );
		$val      = $settings->get_point_status( $type );

		// Legacy compat: return empty string when unset (settings returns int 0).
		return empty( $val ) ? '' : $val;
	} catch ( Exception $e ) {
		// Fallback if container not ready.
		$type   = strtolower( $type );
		$option = "autoship_{$type}_checked_utc";
		wp_cache_delete( $option, 'options' );
		$val = get_option( $option );
		return empty( $val ) ? '' : $val;
	}
}


/**
 * Checks if the supplied integration status is fresh.
 * Stale duration can be modified via {@see autoship_qpilot_integration_check_duration}
 * filter which takes the current $type and default duration.
 *
 * @return bool          True if the supplied integration has checked out ok.
 *                       false if it's stale, doesn't exist or not valid.
 */
function autoship_check_integration_status_freshness() {
	try {
		$service = Plugin::get_service_container()->get( HealthcheckService::class );
		return $service->is_fresh();
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Updates the integrations status for the supplied type
 *
 * @param string $type The integration status to update. Valid types are GET, POST, PUT.
 * @param string $timestamp The UTC Unix Timestamp to update it to.
 *
 * @return bool              True if option value has changed,
 *                           false if not or if update failed.
 */
function autoship_update_integration_point_status( $type, $timestamp ) {
	try {
		$settings = Plugin::get_service_container()->get( HealthcheckSettingsInterface::class );
		return $settings->set_point_status( $type, (int) $timestamp );
	} catch ( Exception $e ) {
		// Fallback if container not ready.
		return update_option( "autoship_{$type}_checked_utc", $timestamp, false );
	}
}

/**
 * Clears the values in the integrations status fields
 *
 * @param bool $delete_all When set to True all integrations health status fields are deleted. Includes the 'autoship_health' field.
 */
function autoship_clear_integration_point_statuses( $delete_all = false ) {
	try {
		$settings = Plugin::get_service_container()->get( HealthcheckSettingsInterface::class );

		if ( $delete_all ) {
			$settings->delete_all();
		} else {
			$settings->clear_point_statuses();
		}
	} catch ( Exception $e ) {
		// Fallback if container not ready.
		$fields = autoship_integration_status_fields();

		if ( $delete_all ) {
			$fields['autoship_health'] = 'true';
			foreach ( $fields as $field => $action ) {
				delete_option( $field );
			}
		} else {
			foreach ( $fields as $field => $action ) {
				update_option( $field, '', false );
			}
		}
	}
}

// ==========================================================
// Integration Test Runners
// ==========================================================

/**
 * Initiates the Autoship <> QPilot integration tests.
 * Hits the QPilot test endpoint where QPilot then checks
 * POST, GET, and PUT calls to WC and Autoship Endpoints.
 * If successfull the corresponding status timestamps are populated.
 *
 * @param bool $force When set to true the integration tests are forced.
 */
function autoship_init_integration_test( bool $force = false ) {
	// Check credentials before delegating (preserves legacy return value).
	$client = autoship_get_default_client();

	if ( empty( $client ) || empty( $client->get_token_auth() ) || empty( $client->get_site_id() ) ) {
		return false;
	}

	try {
		$service = Plugin::get_service_container()->get( HealthcheckService::class );
		return $service->run_check( $force );
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Tests the QPilot connection with WC REST API
 * Hooked into the Ajax action autoship_test_integration
 * Uses {@see autoship_init_integration_test}
 */
function autoship_ajax_test_integration() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore
		autoship_ajax_result( 403 );
		die();
	}

	autoship_init_integration_test( true );

	wp_redirect( admin_url( 'admin.php?page=autoship' ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	die();
}

/**
 * Tests the QPilot connection with WC REST API
 * Hooked into the Ajax action autoship_retest_invalid_products
 */
function autoship_ajax_retest_invalid_products_integration() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore
		autoship_ajax_result( 403 );
		die();
	}

	autoship_init_integration_test();

	wp_redirect( autoship_admin_products_page_url() ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	die();
}

/**
 * Checks the Integration Status timestamps and adds notices to the Queue
 * If the timestamps are empty. Status Timestamps are emptied on init testing.
 *
 * @param mixed  $result The result of the QPilot Client Request.
 * @param bool   $log    Whether to log the notices or not. Default true.
 * @param string $e_code The error code to use if an exception occurred. Default '520'.
 * @param string $e_message The error message to use if an exception occurred. Default 'An Unknown Error was encountered.'.
 *
 * @return bool              True if everything is healthy or false if not.
 */
function autoship_integration_statuses_check( $result = null, $log = true, $e_code = '520', $e_message = 'An Unknown Error was encountered.' ) {
	try {
		$service = Plugin::get_service_container()->get( HealthcheckService::class );
		return $service->process_results( $result, $log, $e_code, $e_message );
	} catch ( Exception $e ) {
		return 'unhealthy';
	}
}

// ==========================================================
// Admin UI Stubs (handled by HealthcheckCompatibility)
// ==========================================================

/**
 * Adds a Health Check notification to the Autoship Menu Option.
 * uses {@see autoship_get_integration_health_status()} to get
 * the current health.
 */
function autoship_admin_health_status_menu_bubble() {
	// Now handled by HealthcheckCompatibility::add_health_status_menu_bubble().
}

/**
 * Shows a Status Bubble on the Autoship Cloud > Settings SubMenu Option when unhealthy exist.
 *
 * @param array $menu_options An array of the current Autosihip Menu Options.
 */
function autoship_admin_settings_submenu_health_status_menu_bubble( $menu_options ) {
	// Now handled by HealthcheckCompatibility::add_submenu_health_bubble().
	return $menu_options;
}

/**
 * Filter any User Messages returned by the QPilot Client Request
 *
 * @param array $messages The messages to filter.
 * @param array $args Additional Call Details.
 */
function autoship_filter_qpilot_client_response_usermessages( $messages, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	// Now handled by HealthcheckCompatibility::filter_user_messages().
	return $messages;
}

/**
 * Logs any technical errors returned by the QPilot Client Request
 *
 * @param array $args The args to retrieve the log.
 */
function autoship_log_qpilot_client_response_errors( $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	// Now handled by HealthcheckCompatibility::log_response_errors().
}


// ===== Background Integration Check Processor =====

if ( ! function_exists( 'autoship_queue_integration_check' ) ) {
	/**
	 * Queue a background integration check using Action Scheduler.
	 *
	 * @param bool $force If true, run immediately by enqueueing an async action even if another is pending.
	 * @return void
	 */
	function autoship_queue_integration_check( $force = false ) {
		try {
			$service = Plugin::get_service_container()->get( HealthcheckService::class );
			$service->queue_check( (bool) $force );
		} catch ( Exception $e ) {
			// Fallback if container not ready.
			if ( ! function_exists( 'as_enqueue_async_action' ) ) {
				autoship_init_integration_test();
				return;
			}

			$hook    = 'autoship_run_integration_check';
			$pending = as_next_scheduled_action( $hook );
			if ( $pending && ! $force ) {
				return;
			}

			as_enqueue_async_action( $hook, array( 'forced' => (bool) $force ), 'autoship' );
		}
	}
}

// ==========================================================
// Deprecated REST API Status Check Endpoints
// ==========================================================

/**
 * Registers new REST API endpoints for QPilot status checks.
 *
 * Now handled by HealthcheckRestController::register_routes().
 *
 * @deprecated 2.12.0
 */
function autoship_qpilot_statuscheck_routes() {
	// Now handled by HealthcheckRestController::register_routes().
}

/**
 * Permission Callback for the health check endpoints.
 *
 * Now handled by HealthcheckRestController::check_permission().
 *
 * @param WP_REST_Request $request The Request.
 *
 * @return bool|WP_Error True if use has permissions else WP_Error
 *
 * @deprecated 2.12.0
 */
function autoship_qpilot_statuscheck_permission_check( $request ) {
	return apply_filters( 'autoship_qpilot_statuscheck_permission_check', true, $request );
}

/**
 * Updates the PUT status check with the current time.
 *
 * Now handled by HealthcheckRestController::handle_put().
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 *
 * @deprecated 2.12.0
 */
function autoship_qpilot_statuscheck_put_update( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	// Now handled by HealthcheckRestController::handle_put().
	$timestamp = time();
	$result    = autoship_update_integration_point_status( 'put', $timestamp );

	return new WP_REST_Response(
		array(
			'code'    => false === $result ? 'autoship_put_failure' : 'autoship_put_success',
			'message' => false === $result ? 'Autoship PUT Update Failed. WordPress Update WP Options Record Failed.' : 'Autoship PUT Update Success',
		),
		false === $result ? 500 : 200
	);
}

/**
 * Updates the POST status check with the current time.
 *
 * Now handled by HealthcheckRestController::handle_post().
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 *
 * @deprecated 2.12.0
 */
function autoship_qpilot_statuscheck_post_update( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	// Now handled by HealthcheckRestController::handle_post().
	$timestamp = time();
	$result    = autoship_update_integration_point_status( 'post', $timestamp );

	return new WP_REST_Response(
		array(
			'code'    => false === $result ? 'autoship_post_failure' : 'autoship_post_success',
			'message' => false === $result ? 'Autoship POST Update Failed. WordPress Update WP Options Record Failed.' : 'Autoship POST Update Success',
		),
		false === $result ? 500 : 200
	);
}

/**
 * Updates the GET status check with the current time.
 *
 * Now handled by HealthcheckRestController::handle_get().
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 *
 * @deprecated 2.12.0
 */
function autoship_qpilot_statuscheck_get_update( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	// Now handled by HealthcheckRestController::handle_get().
	$timestamp = time();
	$result    = autoship_update_integration_point_status( 'get', $timestamp );

	return new WP_REST_Response(
		array(
			'code'    => false === $result ? 'autoship_get_failure' : 'autoship_get_success',
			'message' => false === $result ? 'Autoship GET Update Failed. WordPress Update WP Options Record Failed.' : 'Autoship GET Update Success',
		),
		false === $result ? 500 : 200
	);
}
