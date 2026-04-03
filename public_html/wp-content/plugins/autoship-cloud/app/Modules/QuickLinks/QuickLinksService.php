<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLinks Service (WordPress Facade).
 *
 * Handles WordPress-specific integration for QuickLinks feature.
 *
 * @package Autoship\Modules\QuickLinks
 * @since   3.2.0
 */

namespace Autoship\Modules\QuickLinks;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Services\QuickLinks\RateLimiter\RateLimiter;
use Autoship\Services\QuickLinks\EmailScanner\EmailScannerDetector;
use Autoship\Services\QuickLinks\AuditLog\QuickLinkAuditService;
use Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService;
use Autoship\Modules\QuickLinks\Controllers\QuickLinkController;

/**
 * QuickLinks Service.
 *
 * WordPress facade for QuickLinks functionality.
 * Handles rewrite rules, initialization, and request routing.
 */
class QuickLinksService {


	/**
	 * The QuickLink service instance.
	 *
	 * @var QuickLinkServiceInterface
	 */
	private QuickLinkServiceInterface $quicklink_service;

	/**
	 * The rate limiter instance.
	 *
	 * @var RateLimiter
	 */
	private RateLimiter $rate_limiter;

	/**
	 * The email scanner detector instance.
	 *
	 * @var EmailScannerDetector
	 */
	private EmailScannerDetector $scanner_detector;

	/**
	 * The audit service instance.
	 *
	 * @var QuickLinkAuditService
	 */
	private QuickLinkAuditService $audit_service;

	/**
	 * The confirmation service instance.
	 *
	 * @var QuickLinkConfirmationService
	 */
	private QuickLinkConfirmationService $confirmation_service;

	/**
	 * Indicates whether the service has been initialized.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;

	/**
	 * The QuickLink controller instance.
	 *
	 * @var QuickLinkController|null
	 */
	private ?QuickLinkController $controller = null;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkServiceInterface    $quicklink_service    The QuickLink service instance.
	 * @param RateLimiter                  $rate_limiter         The rate limiter instance.
	 * @param EmailScannerDetector         $scanner_detector     The email scanner detector instance.
	 * @param QuickLinkAuditService        $audit_service        The audit service instance.
	 * @param QuickLinkConfirmationService $confirmation_service The confirmation service instance.
	 */
	public function __construct(
		QuickLinkServiceInterface $quicklink_service,
		RateLimiter $rate_limiter,
		EmailScannerDetector $scanner_detector,
		QuickLinkAuditService $audit_service,
		QuickLinkConfirmationService $confirmation_service
	) {
		$this->quicklink_service    = $quicklink_service;
		$this->rate_limiter         = $rate_limiter;
		$this->scanner_detector     = $scanner_detector;
		$this->audit_service        = $audit_service;
		$this->confirmation_service = $confirmation_service;
	}

	/**
	 * Initialize the QuickLinks service.
	 *
	 * Registers WordPress hooks and rewrite rules.
	 *
	 * @return void
	 */
	public function initialize(): void {

		if ( self::$initialized ) {
			return;
		}

		// Register rewrite rules.
		add_action( 'init', array( $this, 'register_rewrite_rules' ) );

		// Register query vars.
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );

		// Handle QuickLink requests.
		add_action( 'template_redirect', array( $this, 'handle_quicklink_request' ), 1 );

		self::$initialized = true;
	}

	/**
	 * Register WordPress rewrite rules for QuickLinks.
	 *
	 * URL format: /autoship/l/{slug}/{order_id}
	 * Example: /autoship/l/resume-subscription/12345
	 *
	 * Confirmation URLs:
	 * - POST /autoship/lc/confirm/{uuid} - Submit confirmation
	 * - GET  /autoship/lc/confirm/{uuid} - View confirmation status
	 * - GET  /autoship/lc/cancel/{uuid}  - Cancel confirmation
	 *
	 * @return void
	 */
	public function register_rewrite_rules(): void {
		// Register the QuickLink rewrite rule.
		// Pattern: /autoship/l/{slug}/{order_id}.
		add_rewrite_rule(
			'^autoship/l/([^/]+)/([0-9]+)/?$',
			'index.php?autoship_quicklink=1&quicklink_slug=$matches[1]&quicklink_order_id=$matches[2]',
			'top'
		);

		// UUID regex pattern for validation.
		$uuid_pattern = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

		// Register confirmation routes.
		// Pattern: /autoship/lc/confirm/{uuid}.
		add_rewrite_rule(
			'^autoship/lc/confirm/(' . $uuid_pattern . ')/?$',
			'index.php?autoship_quicklink_confirm=1&quicklink_confirm_uuid=$matches[1]',
			'top'
		);

		// Pattern: /autoship/lc/cancel/{uuid}.
		add_rewrite_rule(
			'^autoship/lc/cancel/(' . $uuid_pattern . ')/?$',
			'index.php?autoship_quicklink_cancel=1&quicklink_cancel_uuid=$matches[1]',
			'top'
		);

		// Register rewrite tags.
		add_rewrite_tag( '%autoship_quicklink%', '([^&]+)' );
		add_rewrite_tag( '%quicklink_slug%', '([^&]+)' );
		add_rewrite_tag( '%quicklink_order_id%', '([0-9]+)' );
		add_rewrite_tag( '%autoship_quicklink_confirm%', '([^&]+)' );
		add_rewrite_tag( '%quicklink_confirm_uuid%', '([^&]+)' );
		add_rewrite_tag( '%autoship_quicklink_cancel%', '([^&]+)' );
		add_rewrite_tag( '%quicklink_cancel_uuid%', '([^&]+)' );
	}

	/**
	 * Register query vars for QuickLinks.
	 *
	 * @param array $vars The existing query vars.
	 *
	 * @return array The modified query vars.
	 */
	public function register_query_vars( array $vars ): array {
		$vars[] = 'autoship_quicklink';
		$vars[] = 'quicklink_slug';
		$vars[] = 'quicklink_order_id';
		$vars[] = 'autoship_quicklink_confirm';
		$vars[] = 'quicklink_confirm_uuid';
		$vars[] = 'autoship_quicklink_cancel';
		$vars[] = 'quicklink_cancel_uuid';

		return $vars;
	}

	/**
	 * Handle QuickLink requests.
	 *
	 * Checks if the current request is a QuickLink request
	 * and delegates to the controller.
	 *
	 * @return void
	 */
	public function handle_quicklink_request(): void {
		// Check if this is any type of quicklink request.
		$is_quicklink = get_query_var( 'autoship_quicklink', false );
		$is_confirm   = get_query_var( 'autoship_quicklink_confirm', false );
		$is_cancel    = get_query_var( 'autoship_quicklink_cancel', false );

		// If not a quicklink request, return early.
		if ( ! $is_quicklink && ! $is_confirm && ! $is_cancel ) {
			return;
		}

		// Check if Quick Actions is enabled in settings.
		if ( ! $this->is_enabled() ) {
			// Redirect to home page if disabled.
			wp_safe_redirect( home_url() );
			exit;
		}

		// Initialize controller if needed.
		$this->ensure_controller();

		// Handle confirmation request.
		if ( $is_confirm ) {
			$uuid = get_query_var( 'quicklink_confirm_uuid', '' );
			if ( ! empty( $uuid ) ) {
				$this->controller->handle_confirmation_request( $uuid );
				exit;
			}
			return;
		}

		// Handle cancel request.
		if ( $is_cancel ) {
			$uuid = get_query_var( 'quicklink_cancel_uuid', '' );
			if ( ! empty( $uuid ) ) {
				$this->controller->handle_cancel_request( $uuid );
				exit;
			}
			return;
		}

		// Handle regular QuickLink request.
		if ( ! $is_quicklink ) {
			return;
		}

		// Get QuickLink parameters.
		$slug     = get_query_var( 'quicklink_slug', '' );
		$order_id = absint( get_query_var( 'quicklink_order_id', 0 ) );

		if ( empty( $slug ) || empty( $order_id ) ) {
			return;
		}

		// Delegate to controller.
		$this->controller->handle_request( $slug, $order_id );

		// Exit to prevent WordPress from continuing.
		exit;
	}

	/**
	 * Ensure controller is initialized.
	 *
	 * @return void
	 */
	private function ensure_controller(): void {
		if ( null === $this->controller ) {
			$this->controller = new QuickLinkController(
				$this->quicklink_service,
				$this->rate_limiter,
				$this->scanner_detector,
				$this->audit_service,
				$this->confirmation_service
			);
		}
	}

	/**
	 * Check if Quick Actions is enabled in settings.
	 *
	 * @return bool True if enabled, false if disabled.
	 */
	private function is_enabled(): bool {
		$settings = get_option( 'autoship_quicklinks_settings', array() );

		// Default to enabled if setting doesn't exist.
		if ( empty( $settings ) || ! isset( $settings['enabled'] ) ) {
			return true;
		}

		return (bool) $settings['enabled'];
	}

	/**
	 * Flush rewrite rules.
	 *
	 * Should be called after plugin activation or when
	 * rewrite rules are modified.
	 *
	 * @return void
	 */
	public function flush_rewrite_rules(): void {
		$this->register_rewrite_rules();
		flush_rewrite_rules();
	}
}
