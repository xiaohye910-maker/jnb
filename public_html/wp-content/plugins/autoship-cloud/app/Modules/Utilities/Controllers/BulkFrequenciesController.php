<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Bulk Frequencies Controller for the Utilities module.
 *
 * @package Autoship
 * @since 2.12.1
 */

namespace Autoship\Modules\Utilities\Controllers;

use Autoship\Core\AutoshipSettingsInterface;
use Autoship\Core\EnvironmentInterface;
use Autoship\Modules\Utilities\Services\FrequencyUpdateService;
use Autoship\Modules\Utilities\Services\ProductQueryService;

/**
 * Controller for the bulk frequency update utility.
 *
 * Handles AJAX requests for batch-processing frequency option updates
 * across products, script enqueuing, and template rendering.
 *
 * @package Autoship\Modules\Utilities\Controllers
 * @since 2.12.1
 */
class BulkFrequenciesController {

	/**
	 * Nonce action for verifying AJAX requests.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'autoship_bulk_frequencies_nonce';

	/**
	 * AJAX action name for processing batches.
	 *
	 * @var string
	 */
	const AJAX_ACTION = 'autoship_bulk_update_frequency_options';

	/**
	 * The frequency update service.
	 *
	 * @var FrequencyUpdateService
	 */
	private FrequencyUpdateService $frequency_service;

	/**
	 * The product query service.
	 *
	 * @var ProductQueryService
	 */
	private ProductQueryService $product_service;

	/**
	 * The environment.
	 *
	 * @var EnvironmentInterface
	 */
	private EnvironmentInterface $environment;

	/**
	 * The Autoship settings.
	 *
	 * @var AutoshipSettingsInterface
	 */
	private AutoshipSettingsInterface $settings;

	/**
	 * Constructor.
	 *
	 * @param FrequencyUpdateService    $frequency_service The frequency update service.
	 * @param ProductQueryService       $product_service   The product query service.
	 * @param EnvironmentInterface      $environment       The environment.
	 * @param AutoshipSettingsInterface $settings          The Autoship settings.
	 */
	public function __construct(
		FrequencyUpdateService $frequency_service,
		ProductQueryService $product_service,
		EnvironmentInterface $environment,
		AutoshipSettingsInterface $settings
	) {
		$this->frequency_service = $frequency_service;
		$this->product_service   = $product_service;
		$this->environment       = $environment;
		$this->settings          = $settings;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_process_batch' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'autoship_utilities_bulk_frequencies_section', array( $this, 'render_section' ) );
	}

	/**
	 * Enqueue scripts for the utilities admin page.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// Check if we're on the utilities tab.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'autoship-utilities' !== $tab ) {
			return;
		}

		wp_enqueue_script(
			'autoship-bulk-frequencies',
			$this->environment->get_autoship_plugin_url() . 'js/admin/utilities/bulk-frequencies.js',
			array( 'jquery' ),
			$this->environment->get_autoship_version(),
			true
		);

		wp_localize_script(
			'autoship-bulk-frequencies',
			'autoshipBulkFrequencies',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'action'  => self::AJAX_ACTION,
				'strings' => array(
					'processing'      => __( 'Processing...', 'autoship' ),
					'complete'        => __( 'Bulk update complete.', 'autoship' ),
					'error'           => __( 'An error occurred during the bulk update.', 'autoship' ),
					'cancelled'       => __( 'Bulk update cancelled.', 'autoship' ),
					'validationError' => __( 'Please fill in all frequency option fields.', 'autoship' ),
					// translators: %1$s is the percentage completed, %2$d is total products.
					'progressNotice'  => __( '%1$s%% of the %2$d Products have been processed.', 'autoship' ),
				),
			)
		);
	}

	/**
	 * Render the bulk frequencies section in the utilities template.
	 *
	 * @return void
	 */
	public function render_section(): void {
		$product_count   = $this->product_service->get_frequency_updatable_product_count();
		$frequency_types = $this->settings->get_frequency_types();
		$has_products    = $product_count > 0;

		$template_path = $this->environment->get_plugin_dir() . '/templates/admin/settings/utilities/bulk-frequencies.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}

	/**
	 * AJAX handler for processing a batch of frequency updates.
	 *
	 * Verifies nonce and capabilities, parses request data from $_POST
	 * directly (avoiding filter_input_array which fails in PHP-FPM),
	 * validates frequencies, and processes the batch.
	 *
	 * @return void
	 */
	public function handle_process_batch(): void {
		// Verify nonce (fixes Bug 3: missing nonce verification).
		if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			$this->send_error( __( 'Invalid security token.', 'autoship' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( __( 'Unauthorized access', 'autoship' ) );
		}

		// Read frequencies from $_POST (fixes Bug 1: filter_input_array returns null in PHP-FPM).
		$frequencies = isset( $_POST['frequencies'] ) ? wp_unslash( $_POST['frequencies'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$validated_frequencies = $this->frequency_service->validate_frequencies( $frequencies );

		if ( is_wp_error( $validated_frequencies ) ) {
			$this->send_error( $validated_frequencies->get_error_message() );
		}

		$input         = $this->parse_request_data();
		$batch_size    = $input['batch_size'];
		$total_count   = $input['total_count'];
		$current_count = $input['current_count'];
		$current_page  = $input['current_page'];

		$count = 0;
		$pct   = 100;

		if ( $current_count < $total_count ) {
			$products = $this->product_service->get_frequency_updatable_products( $current_page, $batch_size );

			if ( empty( $products ) ) {
				$count = $total_count;
			} else {
				foreach ( $products as $product ) {
					$this->frequency_service->update_product_frequencies( $product, $validated_frequencies );
				}

				$count = count( $products );
				$pct   = $count && $total_count ? round( 100 * ( ( $count + $current_count ) / $total_count ), 2 ) : 0;
			}
		}

		$pct = $count ? $pct : 100;
		$pct = min( $pct, 100 );

		wp_send_json_success(
			array(
				'page'           => $current_page + 1,
				'last_record'    => 0,
				'updated_record' => array(),
				'count'          => $count,
				'current_count'  => 100 === (int) $pct ? $total_count : $count + $current_count,
				'total_pct'      => max( $pct, 5 ),
				// translators: %1$s is the percentage completed, %2$d is the total number of products.
				'notice'         => sprintf( __( '%1$s%% of the %2$d Products have been processed.', 'autoship' ), max( $pct, 5 ), $total_count ),
			)
		);
		wp_die();
	}

	/**
	 * Parse request data from $_POST with safe defaults.
	 *
	 * Uses $_POST directly instead of filter_input_array(INPUT_POST),
	 * which can return null in certain PHP-FPM configurations.
	 *
	 * @return array{batch_size: int, total_count: int, current_count: int, current_page: int}
	 */
	private function parse_request_data(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in handle_process_batch().
		return array(
			'batch_size'    => isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 10,
			'total_count'   => isset( $_POST['total_count'] ) ? absint( $_POST['total_count'] ) : 0,
			'current_count' => isset( $_POST['current_count'] ) ? absint( $_POST['current_count'] ) : 0,
			'current_page'  => isset( $_POST['current_page'] ) ? absint( $_POST['current_page'] ) : 1,
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Send a JSON error response.
	 *
	 * @param string $message The error message.
	 *
	 * @return void
	 */
	private function send_error( string $message ): void {
		wp_send_json_error(
			array(
				'total_pct'     => 0,
				'current_count' => 0,
				'notice'        => $message,
			)
		);
	}
}
