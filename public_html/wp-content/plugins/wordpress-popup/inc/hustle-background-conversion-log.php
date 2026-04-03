<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Background Conversion Log
 *
 * @package Hustle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Hustle_Background_Conversion_Log
 *
 * Handles background processing for conversion log tasks.
 */
class Hustle_Background_Conversion_Log {

	/**
	 * Instance of this class
	 *
	 * @var Hustle_Background_Conversion_Log
	 */
	private static $instance = null;

	/**
	 * Cron hook name
	 *
	 * @var string
	 */
	private $cron_hook = 'hustle_conversion_log_cron';

	/**
	 * Cron interval name
	 *
	 * @var string
	 */
	private $cron_interval = 'hustle_every_minute';

	/**
	 * Get the singleton instance
	 *
	 * @return Hustle_Background_Conversion_Log
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( $this->cron_hook, array( $this, 'process_task' ) );
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
	}

	/**
	 * Initialize the background task
	 */
	public function init() {
		if ( ! wp_next_scheduled( $this->cron_hook ) ) {
			wp_schedule_event( time(), $this->cron_interval, $this->cron_hook );
		}
	}

	/**
	 * Add custom cron interval (every 15 minutes)
	 *
	 * @param array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public function add_cron_interval( $schedules ) {
		$schedules[ $this->cron_interval ] = array(
			'interval' => 900, // 900 seconds = 15 minutes
			'display'  => esc_html__( 'Every Fifteen Minutes', 'hustle' ),
		);
		return $schedules;
	}

	/**
	 * Process the background task
	 */
	public function process_task() {
		self::save_conversion_logs();
	}

	/**
	 * Save temporary conversion logs.
	 *
	 * @since 7.8.11
	 */
	public static function save_conversion_logs() {
		$temp_conversions = get_option( 'hustle_conversion_logs', array() );
		if ( ! empty( $temp_conversions ) ) {
			foreach ( $temp_conversions as $conversion ) {

				$date = date_i18n( 'Y-m-d H:i:s', $conversion['time'] );
				Hustle_Tracking_Model::get_instance()->save_tracking(
					$conversion['module_id'],
					$conversion['action'],
					$conversion['module_type'],
					$conversion['post_id'],
					$conversion['module_sub_type'],
					$date,
					$conversion['ip']
				);
			}
			delete_option( 'hustle_conversion_logs' );
		}
	}

	/**
	 * Stop the scheduled task
	 */
	public function stop() {
		$timestamp = wp_next_scheduled( $this->cron_hook );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->cron_hook );
		}
	}

	/**
	 * Prevent cloning of the instance
	 */
	private function __clone() {}
}
