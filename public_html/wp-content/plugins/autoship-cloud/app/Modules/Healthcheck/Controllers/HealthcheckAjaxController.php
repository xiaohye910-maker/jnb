<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * AJAX handlers for the Health check system.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */

namespace Autoship\Modules\Healthcheck\Controllers;

use Autoship\Modules\Healthcheck\HealthcheckService;

/**
 * AJAX controller for health check operations.
 *
 * Handles WordPress AJAX requests for integration testing,
 * retesting invalid products, and forcing background checks.
 *
 * @package Autoship
 * @subpackage Healthcheck
 * @since 2.12.0
 */
class HealthcheckAjaxController {

	/**
	 * The health check service.
	 *
	 * @var HealthcheckService
	 */
	private HealthcheckService $service;

	/**
	 * Constructor.
	 *
	 * @param HealthcheckService $service The health check service.
	 */
	public function __construct( HealthcheckService $service ) {
		$this->service = $service;
	}

	/**
	 * Register AJAX hook handlers.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'wp_ajax_autoship_test_integration', array( $this, 'handle_test_integration' ), 10, 0 );
		add_action( 'wp_ajax_autoship_retest_invalid_products', array( $this, 'handle_retest_invalid_products' ), 10, 0 );
		add_action( 'wp_ajax_autoship_force_integration_check', array( $this, 'handle_force_check' ) );
	}

	/**
	 * Handle the test integration AJAX request.
	 *
	 * Tests the QPilot connection with WC REST API and redirects
	 * to the Autoship admin page.
	 *
	 * @return void
	 */
	public function handle_test_integration(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore

			// TODO: Remove this.
			autoship_ajax_result( 403 );
			die();
		}

		$this->service->run_check( true );

		wp_redirect( admin_url( 'admin.php?page=autoship' ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		die();
	}

	/**
	 * Handle the retest invalid products AJAX request.
	 *
	 * Tests the QPilot connection and redirects to the
	 * Autoship products admin page.
	 *
	 * @return void
	 */
	public function handle_retest_invalid_products(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore

			// TODO: Remove this.
			autoship_ajax_result( 403 );
			die();
		}

		$this->service->run_check();

		// TODO: Remove this.
		wp_redirect( autoship_admin_products_page_url() ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		die();
	}

	/**
	 * Handle the force integration check AJAX request.
	 *
	 * Queues a forced background integration check and returns
	 * a JSON success response.
	 *
	 * @return void
	 */
	public function handle_force_check(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}

		check_ajax_referer( 'autoship_force_integration_check', 'nonce' );

		$this->service->queue_check( true );

		wp_send_json_success( array( 'queued' => true ) );
	}
}
