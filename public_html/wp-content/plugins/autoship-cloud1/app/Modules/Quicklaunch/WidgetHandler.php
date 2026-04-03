<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * This class adds the widget to the WordPress dashboard.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Modules\Quicklaunch;

/**
 * The widget handler.
 *
 * @package Autoship
 * @since 2.8.7
 */
class WidgetHandler {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Adds the action to include the widget to the WordPress dashboard.
		add_action(
			'wp_dashboard_setup',
			array(
				$this,
				'setup',
			)
		);
	}

	/**
	 * Set up the widget into the WordPress dashboard.
	 *
	 * @return void
	 */
	public function setup(): void {
		wp_add_dashboard_widget(
			'autoship_cloud_setup_widget',
			'Autoship Cloud Setup',
			array(
				$this,
				'display',
			)
		);
	}

	/**
	 * Displays the widget.
	 *
	 * @return void
	 */
	public function display(): void {
		autoship_include_template( 'quicklaunch/widget' );
	}
}
