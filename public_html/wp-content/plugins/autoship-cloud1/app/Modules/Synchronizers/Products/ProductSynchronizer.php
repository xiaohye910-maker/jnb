<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The Autoship Product Synchronizer Utility
 *
 * @package Autoship
 * @since 2.8.6
 */

namespace Autoship\Modules\Synchronizers\Products;

use Exception;
use WP_Query;

/**
 * Represents the product synchronizer.
 *
 * Handles scheduling and execution of product sync tasks.
 */
class ProductSynchronizer {
	const TASK_HOOK      = 'autoship_scheduled_task_product_sync';
	const INTERVAL       = 900;
	const MAX_INTERVAL   = 86400;
	const MIN_BATCH_SIZE = 10;
	const MAX_BATCH_SIZE = 500;

	/**
	 * Initialize the scheduler (register hooks)
	 */
	public function init() {
		add_action( self::TASK_HOOK, array( $this, 'sync_products' ) );

		// This action is deferred until the Action Scheduler data store is initialized.
		add_action( 'init', array( $this, 'check_initial_scheduler_state' ), 20 );
	}

	/**
	 * Verifies the correct installation of the scheduled task if enabled.
	 *
	 * @return void
	 */
	public function check_initial_scheduler_state(): void {
		if ( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_schedule_recurring_action' ) ) {
			if ( $this->is_enabled() && ! as_has_scheduled_action( self::TASK_HOOK ) ) {
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'The utility is enabled, but the scheduled task was not found. Attempting to add the schedule task into the Action Scheduler.', 'autoship' ) );

				$this->enable_sync();
			}
		}
	}

	/**
	 * Get the batch size and interval for the sync task.
	 *
	 * @return int
	 */
	public function get_batch_size(): int {
		return absint( get_option( 'autoship_product_sync_settings_batch', 30 ) );
	}

	/**
	 * Set the batch size for the sync task.
	 *
	 * @param int $size The batch size to set.
	 * @return void
	 */
	public function set_batch_size( int $size ): void {
		// Set the minimum limit to 10.
		if ( $size < 10 ) {
			$size = 10;
		}

		// Set the limit due limits on the processing time.
		if ( $size > 500 ) {
			$size = 500;
		}

		if ( $this->is_logging_enabled() ) {
			$actual = $this->get_batch_size();

			// translators: %1$d is the old batch size, %2$d is the new batch size.
			$log_entry = sprintf( __( 'Setting new Product Sync Utility batch size. Old Value: %1$d - New Value: %2$d', 'autoship' ), $actual, $size );

			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}

		update_option( 'autoship_product_sync_settings_batch', $size );
	}

	/**
	 * Gets the value indicating that the synchronizer is enabled or not.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return (bool) get_option( 'autoship_product_sync_settings_enabled', true );
	}

	/**
	 * Gets the value indicating that the logging is enabled or not.
	 *
	 * @return bool
	 */
	public function is_logging_enabled(): bool {
		return (bool) get_option( 'autoship_product_sync_settings_logs', false );
	}

	/**
	 * Get the interval for the sync task.
	 *
	 * @return int
	 */
	public function get_interval(): int {
		return absint( get_option( 'autoship_product_sync_settings_interval', self::INTERVAL ) );
	}

	/**
	 * Set the interval for the sync task.
	 *
	 * @param int $interval The interval to set.
	 */
	public function set_interval( int $interval ): void {
		// Set the minimum limit to 10.
		if ( $interval < self::INTERVAL ) {
			$interval = self::INTERVAL;
		}

		// Set the maximum limit to 24 hours.
		if ( $interval > self::MAX_INTERVAL ) {
			$interval = self::MAX_INTERVAL;
		}

		if ( $this->is_logging_enabled() ) {
			$actual = $this->get_interval();

			// translators: %1$d is the old interval, %2$d is the new interval.
			$log_entry = sprintf( __( 'Setting new Product Sync Utility execution interval. Old Time: %1$d - New Time: %2$d', 'autoship' ), $actual, $interval );

			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}

		update_option( 'autoship_product_sync_settings_interval', $interval );
	}

	/**
	 * Disables the logging of the sync task.
	 *
	 * @return void
	 */
	public function disable_logging(): void {
		update_option( 'autoship_product_sync_settings_logs', false );

		if ( $this->is_logging_enabled() ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Disabled Product Sync Utility Logging.', 'autoship' ) );
		}
	}

	/**
	 * Enables the logging of the sync task.
	 *
	 * @return void
	 */
	public function enable_logging(): void {
		update_option( 'autoship_product_sync_settings_logs', true );

		if ( $this->is_logging_enabled() ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Enabled Product Sync Utility Logging.', 'autoship' ) );
		}
	}

	/**
	 * Enables the sync task.
	 *
	 * @return void
	 */
	public function enable_sync(): void {
		update_option( 'autoship_product_sync_settings_enabled', true );

		$this->schedule_task();

		if ( $this->is_logging_enabled() ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Enabled Product Sync Utility.', 'autoship' ) );
		}
	}

	/**
	 * Disables the sync task.
	 *
	 * @return void
	 */
	public function disable_sync(): void {
		update_option( 'autoship_product_sync_settings_enabled', false );

		$this->unschedule_task();

		if ( $this->is_logging_enabled() ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Disabled Product Sync Utility.', 'autoship' ) );
		}
	}

	/**
	 * Enabled the task on the action scheduler.
	 *
	 * @return void
	 */
	public function schedule_task(): void {
		if ( ! function_exists( 'as_has_scheduled_action' ) ) {
			if ( $this->is_logging_enabled() ) {
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Unable to install Product Sync Utility Scheduled Task. The function as_has_scheduled_action is not available.', 'autoship' ) );
			}

			return;
		}

		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			if ( $this->is_logging_enabled() ) {
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Unable to install Product Sync Utility Scheduled Task. The function as_schedule_recurring_action is not available.', 'autoship' ) );
			}

			return;
		}

		if ( ! as_has_scheduled_action( self::TASK_HOOK ) ) {
			$interval = $this->get_interval();

			as_schedule_recurring_action( time(), $interval, self::TASK_HOOK );

			if ( $this->is_logging_enabled() ) {
				// translators: %d is the interval in seconds.
				$log_entry = sprintf( __( 'Installed Product Sync Utility Scheduled Task with an execution time %d seconds.', 'autoship' ), $interval );

				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
			}
		}
	}

	/**
	 * Disables the task from the action scheduler.
	 *
	 * @return void
	 */
	public function unschedule_task(): void {
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			if ( $this->is_logging_enabled() ) {
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Unable to uninstall Product Sync Utility Scheduled Task. The action scheduler is not available.', 'autoship' ) );
			}

			return;
		}

		as_unschedule_all_actions( self::TASK_HOOK );

		if ( $this->is_logging_enabled() ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Uninstalled Product Sync Utility Scheduled Task.', 'autoship' ) );
		}
	}

	/**
	 * Gets last sync state.
	 *
	 * @return array
	 */
	public function get_last_sync_status(): array {
		$status          = get_option( 'autoship_product_sync_last_status', 'unknown' );
		$timestamp       = get_option( 'autoship_product_sync_last_timestamp', 0 );
		$interval        = get_option( 'autoship_product_sync_last_interval', 0 );
		$batch_size      = get_option( 'autoship_product_sync_last_batch_size', 0 );
		$processing_time = get_option( 'autoship_product_sync_last_processing_time', 0 );
		$actual_size     = get_option( 'autoship_product_sync_last_actual_size', 0 );
		$identifiers     = get_option( 'autoship_product_sync_last_actual_ids', array() );

		return array(
			'status'          => $status,
			'timestamp'       => $timestamp,
			'interval'        => $interval,
			'batch_size'      => $batch_size,
			'processing_time' => $processing_time,
			'actual_size'     => $actual_size,
			'actual_ids'      => $identifiers,
		);
	}

	/**
	 * Check if the last sync was completed.
	 *
	 * @return bool
	 */
	public function has_last_sync(): bool {
		return get_option( 'autoship_product_sync_last_status', 'unknown' ) !== 'unknown';
	}

	/**
	 * Set the last sync status.
	 *
	 * @param string $status The status of the last sync.
	 * @param int    $timestamp The timestamp of the last sync.
	 * @param float  $processing_time The processing time of the last sync.
	 * @param array  $products The products that were synced.
	 * @return void
	 */
	private function set_last_sync_status( string $status, int $timestamp, float $processing_time, array $products ): void {
		update_option( 'autoship_product_sync_last_status', $status );
		update_option( 'autoship_product_sync_last_timestamp', $timestamp );
		update_option( 'autoship_product_sync_last_interval', $this->get_interval() );
		update_option( 'autoship_product_sync_last_batch_size', $this->get_batch_size() );
		update_option( 'autoship_product_sync_last_processing_time', $processing_time );
		update_option( 'autoship_product_sync_last_actual_size', count( $products ) );
		update_option( 'autoship_product_sync_last_actual_ids', $products );

		$execution = gmdate( 'Y-m-d H:i:s', $timestamp );

		if ( $this->is_logging_enabled() ) {
			// translators: %1$s is the execution date and time, %2$d is the number of products, %3$f is the number of seconds, %4$d is the batch size, %5$d is the number of interval seconds.
			$log_entry = __( 'The last sync started at %1$s for %2$d products with a processing time of %3$f seconds using a batch size of %4$d and an interval of %5$d seconds.', 'autoship' );

			$log_entry = sprintf( $log_entry, $execution, count( $products ), $processing_time, $this->get_batch_size(), $this->get_interval() );
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}
	}

	/**
	 * Sync the enabled products with QPilot.
	 */
	public function sync_products() {
		if ( ! $this->can_perform_sync() ) {
			return;
		}

		// Get timing elements.
		$start     = microtime( true );
		$timestamp = time();

		// Gets the products.
		$batch_size = $this->get_batch_size();
		$products   = $this->get_products_to_sync( $batch_size );

		// Check if there are products to sync.
		if ( empty( $products ) ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'There are no products to sync at this time.', 'autoship' ) );

			return;
		}

		// Sync the products using the current method.
		foreach ( $products as $product_id ) {
			$this->sync_product( $product_id );
		}

		$end     = microtime( true );
		$elapsed = $end - $start;

		$this->set_last_sync_status( 'completed', $timestamp, $elapsed, $products );
	}

	/**
	 * Check if the sync can be performed.
	 *
	 * @return bool
	 */
	public function can_perform_sync(): bool {
		if ( ! $this->is_enabled() ) {

			if ( $this->is_logging_enabled() ) {
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Unable to perform Product Sync. The utility is disabled.', 'autoship' ) );
			}

			return false;
		}

		$connected = $this->has_connection();
		if ( ! $connected ) {
			if ( $this->is_logging_enabled() ) {
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Unable to perform Product Sync. Autoship is not connected.', 'autoship' ) );
			}

			return false;
		}

		return true;
	}

	/**
	 * Check if the sync can be performed with QPilot.
	 *
	 * @return bool
	 */
	public function has_connection(): bool {
		return autoship_has_credentials() && autoship_has_auth_token();
	}

	/**
	 * Syncs the product with QPilot.
	 *
	 * @param int $product_id The product to sync.
	 *
	 * @return void
	 */
	public function sync_product( int $product_id ): void {
		try {
			if ( $this->is_logging_enabled() ) {
				// translators: %d is the product ID for which the sync is being performed.
				$log_entry = sprintf( __( 'Synchronizing product with identifier: %d.', 'autoship' ), $product_id );

				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
			}

			$updated = autoship_push_product( $product_id );
			if ( false === $updated ) {
				// translators: %d is the product ID for which the sync failed or was not completed.
				$log_entry = sprintf( __( 'Synchronization for product %d failed or was not completed.', 'autoship' ), $product_id );
				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );

				return;
			}

			$last_sync_time = time();

			update_post_meta( $product_id, '_autoship_product_last_sync_timestamp', $last_sync_time );

			if ( $this->is_logging_enabled() ) {
				// translators: %1$d is the product ID, %2$s is the last sync time.
				$log_entry = sprintf( __( 'Updated product with identifier: %1$d with the last sync timestamp: %2$s.', 'autoship' ), $product_id, $last_sync_time );

				autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
			}
		} catch ( Exception $e ) {
			// translators: %1$d is the product ID, %2$s is the error message.
			$log_entry = sprintf( __( 'An error occurred while synchronizing the product with identifier %1$d: %2$s', 'autoship' ), $product_id, $e->getMessage() );

			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}
	}

	/**
	 * Get up to a specified number of autoship-enabled product identifiers that haven't been synced in the last 24 hours.
	 *
	 * @param int $limit The maximum number of products to return.
	 * @return array
	 */
	public function get_products_to_sync( int $limit ): array {

		$cutoff = time() - DAY_IN_SECONDS; // 24 hours ago

		if ( $this->is_logging_enabled() ) {
			// translators: %1$s is the cutoff time, %2$d is the batch size.
			$log_entry = sprintf( __( 'Fetching products to synchronize with cutoff time: %1$s and batch size: %2$d.', 'autoship' ), $cutoff, $limit );
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}

		$query = new WP_Query(
			array(
				'post_type'      => array( 'product', 'product_variation' ),
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => '_autoship_sync_active_enabled',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => '_autoship_product_last_sync_timestamp',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_autoship_product_last_sync_timestamp',
							'value'   => $cutoff,
							'compare' => '<',
							'type'    => 'NUMERIC',
						),
					),
				),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'simple', 'variable' ),
					),
				),
			)
		);

		$posts = $query->posts;

		if ( $this->is_logging_enabled() ) {
			// translators: %1$d is the number of products, %2$s is the cutoff time.
			$log_entry = sprintf( __( 'There is a total of %1$d products to synchronize with cutoff time: %2$s.', 'autoship' ), count( $posts ), $cutoff );
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}

		return $posts;
	}

	/**
	 * Get the number of products that need to be synced.
	 *
	 * @return int
	 */
	public function get_number_of_remaining_products(): int {
		$cutoff = time() - DAY_IN_SECONDS; // 24 hours ago

		if ( $this->is_logging_enabled() ) {
			// translators: %s is the cutoff time.
			$log_entry = sprintf( __( 'Fetching remaining product count to synchronize with cutoff time: %s.', 'autoship' ), $cutoff );
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), $log_entry );
		}

		$query = new WP_Query(
			array(
				'post_type'      => array( 'product', 'product_variation' ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => false,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => '_autoship_sync_active_enabled',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => '_autoship_product_last_sync_timestamp',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_autoship_product_last_sync_timestamp',
							'value'   => $cutoff,
							'compare' => '<',
							'type'    => 'NUMERIC',
						),
					),
				),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'simple', 'variable' ),
					),
				),
			),
		);

		return $query->found_posts;
	}

	/**
	 * Get the suggested batch size based on the number of products and the interval.
	 *
	 * @return int
	 */
	public function get_suggested_batch_size(): int {
		$times    = self::MAX_INTERVAL / self::INTERVAL;
		$products = $this->get_number_autoship_enabled_products();
		if ( ! isset( $products ) ) {
			$products = 0;
		}

		$batch = ceil( $products / $times );
		if ( $batch < self::MIN_BATCH_SIZE ) {
			$batch = self::MIN_BATCH_SIZE;
		} elseif ( $batch > self::MAX_BATCH_SIZE ) {
			$batch = self::MAX_BATCH_SIZE;
		}

		return $batch;
	}

	/**
	 * Get the estimated remaining hours for the sync task.
	 *
	 * @return int
	 */
	public function get_estimated_remaining_hours(): int {
		$remaining_products = $this->get_number_of_remaining_products();
		$batch_size         = $this->get_batch_size();
		$interval_time      = $this->get_interval();
		$remaining_batches  = 0 === $batch_size ? 0 : $remaining_products / $batch_size;

		return ceil( ( $remaining_batches * $interval_time ) / 3600 );
	}

	/**
	 * Get the number of products enabled in autoship.
	 *
	 * @return int
	 */
	public function get_number_autoship_enabled_products(): int {
		if ( $this->is_logging_enabled() ) {
			autoship_log_entry( __( 'Autoship Product Sync Utility', 'autoship' ), __( 'Fetching the number of autoship enabled products.', 'autoship' ) );
		}

		$query = new WP_Query(
			array(
				'post_type'      => array( 'product', 'product_variation' ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => false,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => '_autoship_sync_active_enabled',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'simple', 'variable' ),
					),
				),
			),
		);

		return $query->found_posts;
	}
}
