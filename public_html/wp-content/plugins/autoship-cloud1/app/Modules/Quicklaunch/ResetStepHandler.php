<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class resets the steps on the quicklaunch.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

/**
 * Implements the reset quicklaunch handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class ResetStepHandler implements StepHandlerInterface {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_autoship_quicklaunch_reset_handler', array( $this, 'handle' ) );
	}

	/**
	 * Handles the quicklaunch reset step.
	 *
	 * @return void
	 */
	public function handle() {
		delete_option( 'autoship_quicklaunch_last_step' );
		delete_option( 'autoship_quicklaunch_product' );
		delete_option( 'autoship_quicklaunch_completed' );
		delete_option( 'autoship_plugin_registration_lead_id' );

		wp_cache_delete( 'autoship_quicklaunch_last_step', 'options' );
		wp_cache_delete( 'autoship_quicklaunch_product', 'options' );
		wp_cache_delete( 'autoship_quicklaunch_completed', 'options' );
		wp_cache_delete( 'autoship_plugin_registration_lead_id', 'options' );

		wp_send_json_success( array( 'message' => 'ok' ) );
	}
}
