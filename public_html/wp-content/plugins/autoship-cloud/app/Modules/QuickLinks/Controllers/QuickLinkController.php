<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLink Controller.
 *
 * Handles QuickLink HTTP requests and orchestrates the verify → execute → consume flow.
 *
 * @package Autoship\Modules\QuickLinks\Controllers
 * @since   3.2.0
 */

namespace Autoship\Modules\QuickLinks\Controllers;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Services\QuickLinks\RateLimiter\RateLimiter;
use Autoship\Services\QuickLinks\EmailScanner\EmailScannerDetector;
use Autoship\Services\QuickLinks\AuditLog\QuickLinkAuditService;
use Autoship\Services\QuickLinks\Confirmation\QuickLinkConfirmationService;
use Autoship\Domain\QuickLinks\RedirectType;
use Autoship\Domain\QuickLinks\ActionType;
use Autoship\Domain\QuickLinks\QuickLinkConfirmation;

/**
 * QuickLink Controller.
 *
 * Processes QuickLink requests through the complete workflow:
 * 1. Validate request parameters
 * 2. Verify QuickLink with QPilot API
 * 3. Check login requirements
 * 4. Execute action if valid
 * 5. Consume QuickLink
 * 6. Handle redirect or render template
 */
class QuickLinkController {


	/**
	 * The QuickLink service instance.
	 *
	 * @var QuickLinkServiceInterface
	 */
	private QuickLinkServiceInterface $service;

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
	 * Request start time for execution timing.
	 *
	 * @var float
	 */
	private float $start_time;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkServiceInterface    $service              The QuickLink service instance.
	 * @param RateLimiter                  $rate_limiter         The rate limiter instance.
	 * @param EmailScannerDetector         $scanner_detector     The email scanner detector instance.
	 * @param QuickLinkAuditService        $audit_service        The audit service instance.
	 * @param QuickLinkConfirmationService $confirmation_service The confirmation service instance.
	 */
	public function __construct(
		QuickLinkServiceInterface $service,
		RateLimiter $rate_limiter,
		EmailScannerDetector $scanner_detector,
		QuickLinkAuditService $audit_service,
		QuickLinkConfirmationService $confirmation_service
	) {
		$this->service              = $service;
		$this->rate_limiter         = $rate_limiter;
		$this->scanner_detector     = $scanner_detector;
		$this->audit_service        = $audit_service;
		$this->confirmation_service = $confirmation_service;
	}

	/**
	 * Handle a QuickLink request.
	 *
	 * Main entry point for processing QuickLink URLs.
	 * Security check order (optimized for fail-fast):
	 * 1. Rate limiting (H-0) - prevent abuse
	 * 2. Scanner detection (H-2) - block bots before API call
	 * 3. API verification - validate link with QPilot
	 * 4. Login check (H-1) - enforce authentication for financial actions
	 * 5. Two-step confirmation (H-3) - prevent accidental execution
	 * 6. Execute action
	 *
	 * @param string $slug     The QuickLink slug.
	 * @param int    $order_id The scheduled order ID.
	 *
	 * @return void
	 */
	public function handle_request( string $slug, int $order_id ): void {
		// Start timing for audit.
		$this->start_time = microtime( true );

		// Get client IP for rate limiting.
		$ip_address = $this->get_client_ip();

		// Get user agent for verification context.
		$user_agent = $this->get_user_agent();

		// Step 1: Rate limiting (H-0) - check before any processing.
		if ( $this->rate_limiter->is_limited( $ip_address, $slug ) ) {
			$retry_after = $this->rate_limiter->get_retry_after( $ip_address, $slug );
			$this->log_audit( 0, $order_id, $ip_address, false, null, $slug, null, $user_agent, null, 'RATE_LIMITED', 'Too many requests', true, 'rate_limit_exceeded' );
			$this->render_rate_limited( $retry_after );
			return;
		}

		// Record the attempt.
		$this->rate_limiter->record_attempt( $ip_address, $slug );

		// Step 2: Scanner detection (H-2) - block before API call to save resources.
		// Email scanners have no legitimate reason to access QuickLinks.
		// This check happens early to prevent wasting API calls on automated requests.
		$headers    = $this->get_request_headers();
		$is_scanner = $this->scanner_detector->is_scanner( $user_agent, $headers );

		if ( $is_scanner && $this->scanner_detector->is_enabled() ) {
			$this->log_audit( 0, $order_id, $ip_address, false, null, $slug, null, $user_agent, null, 'SCANNER_BLOCKED', 'Email scanner detected', true, 'scanner_detected' );
			$this->render_scanner_detected();
			return;
		}

		// Get site ID from QPilot connection.
		$site_id = $this->get_site_id();
		if ( ! $site_id ) {
			$this->log_audit( 0, $order_id, $ip_address, false, null, $slug, null, $user_agent, null, 'SITE_NOT_CONNECTED', 'Site not connected to QPilot' );
			$this->render_error( __( 'Site not connected to QPilot.', 'autoship' ) );
			return;
		}

		// Get customer ID (QPilot customer ID).
		$customer_id = $this->get_customer_id();

		// Get security token from request (optional).
		$token = $this->get_token_from_request();

		// Step 3: Verify the QuickLink with QPilot API.
		$verification = $this->service->verify_quicklink(
			$site_id,
			$slug,
			$order_id,
			$customer_id,
			$token,
			$ip_address,
			$user_agent
		);

		// Get quicklink_id from verification for audit logging.
		$quicklink_id = $verification->get_quicklink_id();

		// Check if verification failed.
		if ( ! $verification->is_valid() ) {
			$message = $verification->get_message() ?? __( 'This link is invalid or has expired.', 'autoship' );
			$this->consume_failed( $site_id, $slug, $order_id, $customer_id, $token, 'INVALID_LINK', $message, $ip_address, $user_agent );
			$this->log_audit( 0, $order_id, $ip_address, false, $quicklink_id, $slug, $customer_id, $user_agent, $token, 'INVALID_LINK', $message );
			$this->render_error( $message, $verification->get_custom_logo_url(), $verification->get_custom_stylesheet_url() );
			return;
		}

		// Step 4: Get action type and check login requirement (H-1).
		$action_type = $verification->get_action_type();

		// Validate action type.
		if ( null === $action_type ) {
			$this->consume_failed( $site_id, $slug, $order_id, $customer_id, $token, 'NO_ACTION_TYPE', 'No action type specified', $ip_address, $user_agent );
			$this->log_audit( 0, $order_id, $ip_address, false, $quicklink_id, $slug, $customer_id, $user_agent, $token, 'NO_ACTION_TYPE', 'No action type specified' );
			$this->render_error( __( 'Invalid QuickLink configuration.', 'autoship' ), $verification->get_custom_logo_url(), $verification->get_custom_stylesheet_url() );
			return;
		}

		// H-1: Mandatory login for Process Now (financial actions).
		// Process Now (action_type = 2) ALWAYS requires login regardless of API settings.
		// This prevents unauthorized financial charges from intercepted/shared links.
		$requires_login = $verification->requires_login();
		if ( ActionType::PROCESS_NOW === $action_type ) {
			$requires_login = true; // OVERRIDE - always require login for financial actions.
		}

		if ( $requires_login && ! is_user_logged_in() ) {
			$this->consume_failed( $site_id, $slug, $order_id, $customer_id, $token, 'LOGIN_REQUIRED', 'User not logged in', $ip_address, $user_agent );
			$this->log_audit( $action_type, $order_id, $ip_address, false, $quicklink_id, $slug, $customer_id, $user_agent, $token, 'LOGIN_REQUIRED', 'User not logged in' );
			$this->redirect_to_login( $slug, $order_id );
			return;
		}

		// Step 5: Two-step confirmation (H-3) for enhanced security.
		// Default to true if API doesn't specify (safer behavior).
		$requires_confirmation = $verification->requires_confirmation();
		if ( null === $requires_confirmation ) {
			$requires_confirmation = true; // Default to requiring confirmation.
		}

		// Allow filter to override confirmation requirement.
		$requires_confirmation = apply_filters(
			'autoship_quicklink_requires_confirmation',
			$requires_confirmation,
			$action_type,
			$verification
		);

		if ( $requires_confirmation ) {
			$this->log_audit( $action_type, $order_id, $ip_address, true, $quicklink_id, $slug, $customer_id, $user_agent, $token, null, null, false, null );
			$this->show_confirmation( $slug, $order_id, $site_id, $verification, $action_type, $customer_id, $token );
			return;
		}

		$result = $this->service->execute_action( $site_id, $action_type, $order_id );

		// Step 4: Consume the QuickLink.
		$consume_success = $this->service->consume_quicklink(
			$site_id,
			$slug,
			$order_id,
			$customer_id,
			$token,
			$result->is_successful(),
			$result->is_successful() ? null : $result->get_error_code(),
			$result->is_successful() ? null : $result->get_error_message(),
			$ip_address,
			$user_agent
		);

		// Step 5: Handle result.
		if ( ! $result->is_successful() ) {
			$this->log_audit( $action_type, $order_id, $ip_address, false, $quicklink_id, $slug, $customer_id, $user_agent, $token, $result->get_error_code(), $result->get_error_message() );
			$this->render_error( $result->get_error_message() ?? __( 'Action failed.', 'autoship' ), $verification->get_custom_logo_url(), $verification->get_custom_stylesheet_url() );
			return;
		}

		// Log successful action.
		$this->log_audit( $action_type, $order_id, $ip_address, true, $quicklink_id, $slug, $customer_id, $user_agent, $token );

		// Step 6: Redirect or show success.
		$this->handle_redirect( $verification );
	}

	/**
	 * Get the QPilot site ID.
	 *
	 * @return int|null The site ID or null if not connected.
	 */
	private function get_site_id(): ?int {
		$site_id = get_option( 'autoship_site_id', null );

		return $site_id ? absint( $site_id ) : null;
	}

	/**
	 * Get the current customer's QPilot customer ID.
	 *
	 * @return int|null The customer ID or null if not logged in.
	 */
	private function get_customer_id(): ?int {
		if ( ! is_user_logged_in() ) {
			return null;
		}

		$user_id     = get_current_user_id();
		$customer_id = get_user_meta( $user_id, 'autoship_customer_id', true );

		return $customer_id ? absint( $customer_id ) : null;
	}

	/**
	 * Get security token from request.
	 *
	 * @return string|null The token or null if not provided.
	 */
	private function get_token_from_request(): ?string {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : null;

		return ! empty( $token ) ? $token : null;
	}

	/**
	 * Get client IP address.
	 *
	 * @return string|null The client IP address.
	 */
	private function get_client_ip(): ?string {
		// Check for common proxy headers.
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				// For X-Forwarded-For, use the first IP.
				if ( false !== strpos( $ip, ',' ) ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				return $ip;
			}
		}

		return null;
	}

	/**
	 * Get user agent from request.
	 *
	 * @return string|null The user agent.
	 */
	private function get_user_agent(): ?string {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null;
	}

	/**
	 * Get request headers for scanner detection.
	 *
	 * @return array The request headers.
	 */
	private function get_request_headers(): array {
		return array(
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'accept'          => isset( $_SERVER['HTTP_ACCEPT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) : '',
			'accept_language' => isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) : '',
			'referer'         => isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		);
	}

	/**
	 * Consume QuickLink as failed.
	 *
	 * @param int         $site_id       The site ID.
	 * @param string      $slug          The QuickLink slug.
	 * @param int         $order_id      The scheduled order ID.
	 * @param int|null    $customer_id   The customer ID.
	 * @param string|null $token         The security token.
	 * @param string      $error_code    The error code.
	 * @param string      $error_message The error message.
	 * @param string|null $ip_address    The IP address.
	 * @param string|null $user_agent    The user agent.
	 *
	 * @return void
	 */
	private function consume_failed(
		int $site_id,
		string $slug,
		int $order_id,
		?int $customer_id,
		?string $token,
		string $error_code,
		string $error_message,
		?string $ip_address,
		?string $user_agent
	): void {
		$this->service->consume_quicklink(
			$site_id,
			$slug,
			$order_id,
			$customer_id,
			$token,
			false,
			$error_code,
			$error_message,
			$ip_address,
			$user_agent
		);
	}

	/**
	 * Redirect to login page with return URL.
	 *
	 * @param string $slug     The QuickLink slug.
	 * @param int    $order_id The scheduled order ID.
	 *
	 * @return void
	 */
	private function redirect_to_login( string $slug, int $order_id ): void {
		$return_url = home_url( "/autoship/l/{$slug}/{$order_id}" );
		$login_url  = wp_login_url( $return_url );

		wp_safe_redirect( $login_url );
		exit;
	}

	/**
	 * Handle redirect after successful action.
	 *
	 * @param \Autoship\Domain\QuickLinks\QuickLinkVerification $verification The verification object.
	 *
	 * @return void
	 */
	private function handle_redirect( $verification ): void {
		$redirect_type = $verification->get_redirect_type();
		$custom_url    = $verification->get_custom_url();
		$utm_params    = $verification->get_utm_params();

		switch ( $redirect_type ) {
			case RedirectType::V2_PORTAL:
				$this->redirect_to_portal( $utm_params );
				break;

			case RedirectType::CUSTOM_URL:
				if ( $custom_url ) {
					$this->redirect_to_url( $custom_url, $utm_params );
				} else {
					$this->render_success( $verification );
				}
				break;

			case RedirectType::THANK_YOU_PAGE:
			default:
				$this->render_success( $verification );
				break;
		}
	}

	/**
	 * Redirect to V2 customer portal.
	 *
	 * @param array $utm_params UTM parameters to append.
	 *
	 * @return void
	 */
	private function redirect_to_portal( array $utm_params = array() ): void {
		// Get V2 portal URL from settings or generate it.
		$portal_url = $this->get_v2_portal_url();

		if ( $portal_url ) {
			$this->redirect_to_url( $portal_url, $utm_params );
		} else {
			$this->render_success( __( 'Your subscription has been updated successfully.', 'autoship' ) );
		}
	}

	/**
	 * Get V2 portal URL.
	 *
	 * @return string|null The portal URL or null if not configured.
	 */
	private function get_v2_portal_url(): ?string {
		// Check for V2 portal URL in settings.
		$portal_url = get_option( 'autoship_v2_portal_url', null );

		if ( $portal_url ) {
			return $portal_url;
		}

		// Fallback: use the scheduled orders URL (V2 portal endpoint).
		if ( function_exists( 'autoship_get_scheduled_orders_url' ) ) {
			return autoship_get_scheduled_orders_url();
		}

		// Final fallback: My Account page.
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			return wc_get_page_permalink( 'myaccount' );
		}

		return null;
	}

	/**
	 * Redirect to a custom URL with UTM parameters.
	 *
	 * Uses wp_redirect() instead of wp_safe_redirect() to allow external URLs.
	 * This is intentional because CustomUrl redirects are explicitly configured
	 * by merchants in their QuickLink settings.
	 *
	 * @param string $url        The URL to redirect to.
	 * @param array  $utm_params UTM parameters to append.
	 *
	 * @return void
	 */
	private function redirect_to_url( string $url, array $utm_params = array() ): void {
		// Validate URL format.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$this->render_error( __( 'Invalid redirect URL configured.', 'autoship' ) );
			return;
		}

		// Add UTM parameters if provided.
		if ( ! empty( $utm_params ) ) {
			$url = add_query_arg( $utm_params, $url );
		}

		// Use wp_redirect() to allow external URLs.
		// CustomUrl is explicitly configured by merchants, so external redirects are intentional.
		wp_redirect( esc_url_raw( $url ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/**
	 * Render success template.
	 *
	 * @param \Autoship\Domain\QuickLinks\QuickLinkVerification|\Autoship\Domain\QuickLinks\QuickLinkConfirmation|string|null $context The verification/confirmation object or message string.
	 * @param string|null $custom_message Optional custom message to override action default.
	 * @param array|null  $metadata       Optional metadata (used for confirmation flow branding).
	 *
	 * @return void
	 */
	private function render_success( $context = null, ?string $custom_message = null, ?array $metadata = null ): void {
		$default_message = __( 'Your subscription has been updated successfully.', 'autoship' );

		// Extract data based on context type.
		$action_type           = null;
		$order_id              = null;
		$message               = $custom_message ?? $default_message;
		$custom_logo_url       = null;
		$custom_stylesheet_url = null;
		$order_customized_name = null;

		// Handle QuickLinkVerification object.
		if ( $context instanceof \Autoship\Domain\QuickLinks\QuickLinkVerification ) {
			$action_type           = $context->get_action_type();
			$order_id              = $context->get_scheduled_order_id();
			$message               = $custom_message ?? $context->get_message() ?? $this->get_action_message( $action_type );
			$custom_logo_url       = $context->get_custom_logo_url();
			$custom_stylesheet_url = $context->get_custom_stylesheet_url();
			$order_customized_name = $context->get_order_customized_name();
		}
		// Handle QuickLinkConfirmation object.
		elseif ( $context instanceof QuickLinkConfirmation ) {
			$action_type = $context->get_action_type();
			$order_id    = $context->get_scheduled_order_id();
			$message     = $custom_message ?? $this->get_action_message( $action_type );

			// Get branding from metadata (passed from confirmation flow).
			if ( $metadata ) {
				$custom_logo_url       = $metadata['custom_logo_url'] ?? null;
				$custom_stylesheet_url = $metadata['custom_stylesheet_url'] ?? null;
				$order_customized_name = $metadata['order_customized_name'] ?? null;
			}
		}
		// Handle legacy string message.
		elseif ( is_string( $context ) ) {
			$message = $context;
		}

		// If we have order context, fetch scheduled order data.
		$status          = $action_type ? $this->get_status_from_action( $action_type ) : __( 'Updated', 'autoship' );
		$next_occurrence = null;

		if ( $order_id && function_exists( 'autoship_get_scheduled_order' ) ) {
			$scheduled_order = autoship_get_scheduled_order( $order_id );
			if ( $scheduled_order && ! is_wp_error( $scheduled_order ) ) {
				// Use real status from API.
				if ( ! empty( $scheduled_order->status ) ) {
					$status = $scheduled_order->status;
				}
				// Get next occurrence date.
				if ( ! empty( $scheduled_order->nextOccurrenceUtc ) ) {
					$next_occurrence = $scheduled_order->nextOccurrenceUtc;
				}
			}
		}

		// Load template with full context.
		$this->load_template(
			'thank-you',
			array(
				'message'               => $message,
				'action_type'           => $action_type,
				'action_name'           => $action_type ? ActionType::get_name( $action_type ) : null,
				'order_id'              => $order_id,
				'status'                => $status,
				'next_occurrence'       => $next_occurrence,
				'custom_logo_url'       => $custom_logo_url,
				'custom_stylesheet_url' => $custom_stylesheet_url,
				'order_customized_name' => $order_customized_name,
			)
		);
	}

	/**
	 * Get default message for action type.
	 *
	 * @param int|null $action_type The action type.
	 *
	 * @return string The message.
	 */
	private function get_action_message( ?int $action_type ): string {
		switch ( $action_type ) {
			case ActionType::RESUME:
				return __( 'Your subscription has been resumed and will continue on its regular schedule.', 'autoship' );
			case ActionType::PAUSE:
				return __( 'Your subscription has been paused. You can resume it anytime from your account.', 'autoship' );
			case ActionType::PROCESS_NOW:
				return __( 'Your order has been queued for immediate processing. You should receive a confirmation email shortly.', 'autoship' );
			case ActionType::REACTIVATE:
				return __( 'Your subscription has been reactivated and will resume on its regular schedule.', 'autoship' );
			default:
				return __( 'Your subscription has been updated successfully.', 'autoship' );
		}
	}

	/**
	 * Get subscription status from action type.
	 *
	 * @param int|null $action_type The action type.
	 *
	 * @return string The status label.
	 */
	private function get_status_from_action( ?int $action_type ): string {
		switch ( $action_type ) {
			case ActionType::RESUME:
			case ActionType::REACTIVATE:
				return __( 'Active', 'autoship' );
			case ActionType::PAUSE:
				return __( 'Paused', 'autoship' );
			case ActionType::PROCESS_NOW:
				return __( 'Processing', 'autoship' );
			default:
				return __( 'Updated', 'autoship' );
		}
	}

	/**
	 * Render error template.
	 *
	 * @param string      $message                The error message.
	 * @param string|null $custom_logo_url        Optional custom logo URL.
	 * @param string|null $custom_stylesheet_url  Optional custom stylesheet URL.
	 *
	 * @return void
	 */
	private function render_error( string $message, ?string $custom_logo_url = null, ?string $custom_stylesheet_url = null ): void {
		$this->load_template(
			'error',
			array(
				'message'               => $message,
				'custom_logo_url'       => $custom_logo_url,
				'custom_stylesheet_url' => $custom_stylesheet_url,
			)
		);
	}

	/**
	 * Render rate limited template.
	 *
	 * @param int $retry_after Seconds until retry is allowed.
	 *
	 * @return void
	 */
	private function render_rate_limited( int $retry_after ): void {
		// Set HTTP 429 Too Many Requests status.
		status_header( 429 );
		header( 'Retry-After: ' . $retry_after );

		// Load template.
		// Note: Rate limiting happens before verification, so no custom branding available.
		$this->load_template(
			'rate-limited',
			array(
				'retry_after'           => $retry_after,
				'message'               => __( 'Too many requests. Please wait before trying again.', 'autoship' ),
				'custom_logo_url'       => null,
				'custom_stylesheet_url' => null,
			)
		);
	}

	/**
	 * Render scanner detected template.
	 *
	 * Shows a human verification page for email scanners attempting
	 * to access financial actions. Real browsers will auto-reload.
	 *
	 * @return void
	 */
	private function render_scanner_detected(): void {
		// Set HTTP 200 OK status (not an error, just verification).
		status_header( 200 );

		// Load template.
		// Note: Scanner detection happens before verification, so no custom branding available.
		$this->load_template(
			'scanner-detected',
			array(
				'site_name'             => get_bloginfo( 'name' ),
				'custom_logo_url'       => null,
				'custom_stylesheet_url' => null,
			)
		);
	}

	/**
	 * Load a QuickLinks template.
	 *
	 * @param string $template The template name (without .php extension).
	 * @param array  $args     The template arguments.
	 *
	 * @return void
	 */
	private function load_template( string $template, array $args = array() ): void {
		// Extract args to make them available in template scope.
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		// Look for template in theme first, then plugin.
		$theme_template = locate_template( array( "autoship/quicklinks/{$template}.php" ) );

		if ( $theme_template ) {
			include $theme_template;
		} else {
			$plugin_template = \Autoship_Plugin_Dir . "/templates/quicklinks/{$template}.php";
			if ( file_exists( $plugin_template ) ) {
				include $plugin_template;
			}
		}

		exit;
	}

	/**
	 * Log an audit entry.
	 *
	 * @param int         $action_type        The action type.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param string|null $ip_address         Client IP address.
	 * @param bool        $success            Whether the action succeeded.
	 * @param int|null    $quicklink_id       QuickLink ID.
	 * @param string|null $slug               QuickLink URL slug.
	 * @param int|null    $customer_id        Customer ID.
	 * @param string|null $user_agent         User agent.
	 * @param string|null $token              Security token.
	 * @param string|null $error_code         Error code if failed.
	 * @param string|null $error_message      Error message if failed.
	 * @param bool        $flagged            Whether to flag for anomaly detection.
	 * @param string|null $flag_reason        Reason for flagging.
	 *
	 * @return void
	 */
	private function log_audit(
		int $action_type,
		int $scheduled_order_id,
		?string $ip_address,
		bool $success,
		?int $quicklink_id = null,
		?string $slug = null,
		?int $customer_id = null,
		?string $user_agent = null,
		?string $token = null,
		?string $error_code = null,
		?string $error_message = null,
		bool $flagged = false,
		?string $flag_reason = null
	): void {
		// Calculate execution time in milliseconds.
		$execution_time_ms = null;
		if ( isset( $this->start_time ) ) {
			$execution_time_ms = (int) round( ( microtime( true ) - $this->start_time ) * 1000 );
		}

		$entry = $this->audit_service->create_entry(
			$action_type,
			$scheduled_order_id,
			$ip_address ?? '0.0.0.0',
			$success,
			$quicklink_id,
			$slug,
			$customer_id,
			$user_agent,
			$token,
			$error_code,
			$error_message,
			$execution_time_ms
		);

		// Set flagged status if applicable.
		if ( $flagged ) {
			$entry->set_flagged( true );
			if ( null !== $flag_reason ) {
				$entry->set_flag_reason( $flag_reason );
			}
		}

		$this->audit_service->log( $entry );
	}

	/**
	 * Handle confirmation request.
	 *
	 * Routes to POST (submit confirmation) or GET (view status) handlers.
	 *
	 * @param string $uuid The confirmation UUID.
	 *
	 * @return void
	 */
	public function handle_confirmation_request( string $uuid ): void {
		$this->start_time = microtime( true );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$is_post = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];

		if ( $is_post ) {
			$this->handle_confirm_submission( $uuid );
		} else {
			$this->handle_confirm_get( $uuid );
		}
	}

	/**
	 * Handle confirmation submission (POST).
	 *
	 * @param string $uuid The confirmation UUID.
	 *
	 * @return void
	 */
	private function handle_confirm_submission( string $uuid ): void {
		// Get nonce from POST data.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		// Process the confirmation.
		$result = $this->confirmation_service->process_confirmation( $uuid, $nonce );

		if ( ! $result['success'] ) {
			$this->handle_confirmation_error( $result['error'], $result['confirmation'] );
			return;
		}

		// Confirmation is now in "confirmed" status, execute the action.
		$confirmation = $result['confirmation'];
		$this->execute_confirmed_action( $confirmation );
	}

	/**
	 * Handle confirmation GET request (view status).
	 *
	 * @param string $uuid The confirmation UUID.
	 *
	 * @return void
	 */
	private function handle_confirm_get( string $uuid ): void {
		$confirmation = $this->confirmation_service->get_confirmation( $uuid );

		if ( null === $confirmation ) {
			$this->render_error( __( 'Confirmation not found.', 'autoship' ) );
			return;
		}

		// Check if already processed.
		if ( $confirmation->is_processed() ) {
			$this->render_already_processed( $confirmation );
			return;
		}

		// Check if expired.
		$current_time = current_time( 'mysql', true );
		if ( $confirmation->is_expired( $current_time ) ) {
			$confirmation->mark_expired( $current_time );
			$this->confirmation_service->get_repository()->update( $confirmation );
			$this->render_expired( $confirmation );
			return;
		}

		// Show the confirmation page with a message about GET requests.
		$this->render_confirm_page( $confirmation );
	}

	/**
	 * Handle cancel request.
	 *
	 * @param string $uuid The confirmation UUID.
	 *
	 * @return void
	 */
	public function handle_cancel_request( string $uuid ): void {
		$this->start_time = microtime( true );

		$result = $this->confirmation_service->cancel_confirmation( $uuid );

		if ( ! $result['success'] ) {
			if ( 'not_found' === $result['error'] ) {
				$this->render_error( __( 'Confirmation not found.', 'autoship' ) );
			} elseif ( 'already_processed' === $result['error'] ) {
				$this->render_already_processed( $result['confirmation'] );
			}
			return;
		}

		$this->render_cancelled( $result['confirmation'] );
	}

	/**
	 * Execute the action for a confirmed confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmed confirmation.
	 *
	 * @return void
	 */
	private function execute_confirmed_action( QuickLinkConfirmation $confirmation ): void {
		$site_id     = $confirmation->get_site_id();
		$action_type = $confirmation->get_action_type();
		$order_id    = $confirmation->get_scheduled_order_id();

		// Execute the action.
		$result = $this->service->execute_action( $site_id, $action_type, $order_id );

		// Get verification metadata.
		$metadata    = $confirmation->get_verification_metadata();
		$slug        = $confirmation->get_slug();
		$customer_id = $confirmation->get_customer_id();
		$token       = isset( $metadata['token'] ) ? $metadata['token'] : null;
		$ip_address  = $this->get_client_ip();
		$user_agent  = $this->get_user_agent();

		// Consume the QuickLink.
		$this->service->consume_quicklink(
			$site_id,
			$slug,
			$order_id,
			$customer_id,
			$token,
			$result->is_successful(),
			$result->is_successful() ? null : $result->get_error_code(),
			$result->is_successful() ? null : $result->get_error_message(),
			$ip_address,
			$user_agent
		);

		if ( ! $result->is_successful() ) {
			// Mark confirmation as failed.
			$this->confirmation_service->mark_failed(
				$confirmation,
				array(
					'error_code'    => $result->get_error_code(),
					'error_message' => $result->get_error_message(),
				)
			);

			$this->render_error(
				$result->get_error_message() ?? __( 'Action failed.', 'autoship' ),
				$metadata['custom_logo_url'] ?? null,
				$metadata['custom_stylesheet_url'] ?? null
			);
			return;
		}

		// Mark confirmation as executed.
		$this->confirmation_service->mark_executed(
			$confirmation,
			array( 'success' => true )
		);

		// Render success or redirect.
		$this->render_confirmed_success( $confirmation, $metadata );
	}

	/**
	 * Handle confirmation error.
	 *
	 * @param string                     $error        Error code.
	 * @param QuickLinkConfirmation|null $confirmation Confirmation if available.
	 *
	 * @return void
	 */
	private function handle_confirmation_error( string $error, $confirmation ): void {
		// Extract branding from confirmation metadata if available.
		$custom_logo_url       = null;
		$custom_stylesheet_url = null;

		if ( $confirmation ) {
			$metadata              = $confirmation->get_verification_metadata();
			$custom_logo_url       = $metadata['custom_logo_url'] ?? null;
			$custom_stylesheet_url = $metadata['custom_stylesheet_url'] ?? null;
		}

		switch ( $error ) {
			case 'invalid_nonce':
				$this->render_error( __( 'Invalid security token. Please try again.', 'autoship' ), $custom_logo_url, $custom_stylesheet_url );
				break;

			case 'not_found':
				$this->render_error( __( 'Confirmation not found.', 'autoship' ) );
				break;

			case 'already_processed':
				$this->render_already_processed( $confirmation );
				break;

			case 'expired':
				$this->render_expired( $confirmation );
				break;

			default:
				$this->render_error( __( 'An error occurred. Please try again.', 'autoship' ), $custom_logo_url, $custom_stylesheet_url );
		}
	}

	/**
	 * Render the confirmation page.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmation.
	 *
	 * @return void
	 */
	private function render_confirm_page( QuickLinkConfirmation $confirmation ): void {
		$nonce       = $this->confirmation_service->generate_nonce( $confirmation );
		$confirm_url = $this->confirmation_service->get_confirmation_url( $confirmation );
		$cancel_url  = $this->confirmation_service->get_cancel_url( $confirmation );

		// Get verification metadata for template.
		$metadata = $confirmation->get_verification_metadata();

		$this->load_template(
			'confirm',
			array(
				'confirmation'          => $confirmation,
				'nonce'                 => $nonce,
				'confirm_url'           => $confirm_url,
				'cancel_url'            => $cancel_url,
				'action_name'           => $confirmation->get_action_name(),
				'action_verb'           => $metadata['action_verb'] ?? null,
				'order_id'              => $confirmation->get_scheduled_order_id(),
				'site_name'             => get_bloginfo( 'name' ),
				'order_customized_name' => $metadata['order_customized_name'] ?? null,
				'custom_logo_url'       => $metadata['custom_logo_url'] ?? null,
				'custom_stylesheet_url' => $metadata['custom_stylesheet_url'] ?? null,
				'order_summary_data'    => $metadata['order_summary'] ?? null,
				'custom_meta_tags'      => $metadata['custom_meta_tags'] ?? array(),
			)
		);
	}

	/**
	 * Render successful confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmation.
	 * @param array|null            $metadata     Verification metadata.
	 *
	 * @return void
	 */
	private function render_confirmed_success( QuickLinkConfirmation $confirmation, $metadata ): void {
		// Check for redirect settings in metadata.
		$redirect_type = isset( $metadata['redirect_type'] ) ? $metadata['redirect_type'] : null;
		$custom_url    = isset( $metadata['custom_url'] ) ? $metadata['custom_url'] : null;
		$utm_params    = isset( $metadata['utm_params'] ) ? $metadata['utm_params'] : array();
		$message       = isset( $metadata['message'] ) ? $metadata['message'] : null;

		if ( RedirectType::CUSTOM_URL === $redirect_type && $custom_url ) {
			$this->redirect_to_url( $custom_url, $utm_params );
			return;
		}

		if ( RedirectType::V2_PORTAL === $redirect_type ) {
			$this->redirect_to_portal( $utm_params );
			return;
		}

		// Default: show thank you page with confirmation context.
		$this->render_success( $confirmation, $message, $metadata );
	}

	/**
	 * Render cancelled confirmation page.
	 *
	 * @param QuickLinkConfirmation|null $confirmation The confirmation object.
	 *
	 * @return void
	 */
	private function render_cancelled( ?QuickLinkConfirmation $confirmation = null ): void {
		$action_name           = null;
		$custom_logo_url       = null;
		$custom_stylesheet_url = null;

		if ( $confirmation ) {
			$action_name = $confirmation->get_action_name();
			$metadata    = $confirmation->get_verification_metadata();

			if ( $metadata ) {
				$custom_logo_url       = $metadata['custom_logo_url'] ?? null;
				$custom_stylesheet_url = $metadata['custom_stylesheet_url'] ?? null;
			}
		}

		$this->load_template(
			'cancelled',
			array(
				'message'               => __( 'Your request has been cancelled.', 'autoship' ),
				'site_name'             => get_bloginfo( 'name' ),
				'action_name'           => $action_name,
				'custom_logo_url'       => $custom_logo_url,
				'custom_stylesheet_url' => $custom_stylesheet_url,
			)
		);
	}

	/**
	 * Render expired confirmation page.
	 *
	 * @param QuickLinkConfirmation|null $confirmation Optional confirmation for branding.
	 *
	 * @return void
	 */
	private function render_expired( ?QuickLinkConfirmation $confirmation = null ): void {
		$custom_logo_url       = null;
		$custom_stylesheet_url = null;

		if ( $confirmation ) {
			$metadata = $confirmation->get_verification_metadata();
			if ( $metadata ) {
				$custom_logo_url       = $metadata['custom_logo_url'] ?? null;
				$custom_stylesheet_url = $metadata['custom_stylesheet_url'] ?? null;
			}
		}

		$this->load_template(
			'expired',
			array(
				'message'               => __( 'This confirmation link has expired.', 'autoship' ),
				'site_name'             => get_bloginfo( 'name' ),
				'custom_logo_url'       => $custom_logo_url,
				'custom_stylesheet_url' => $custom_stylesheet_url,
			)
		);
	}

	/**
	 * Render already processed confirmation page.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmation.
	 *
	 * @return void
	 */
	private function render_already_processed( QuickLinkConfirmation $confirmation ): void {
		$status   = $confirmation->get_status();
		$metadata = $confirmation->get_verification_metadata();

		$custom_logo_url       = null;
		$custom_stylesheet_url = null;

		if ( $metadata ) {
			$custom_logo_url       = $metadata['custom_logo_url'] ?? null;
			$custom_stylesheet_url = $metadata['custom_stylesheet_url'] ?? null;
		}

		$this->load_template(
			'already-processed',
			array(
				'status'                => $status,
				'confirmation'          => $confirmation,
				'site_name'             => get_bloginfo( 'name' ),
				'custom_logo_url'       => $custom_logo_url,
				'custom_stylesheet_url' => $custom_stylesheet_url,
			)
		);
	}

	/**
	 * Show confirmation page from handle_request when requires_confirmation is true.
	 *
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $order_id           The scheduled order ID.
	 * @param int         $site_id            The site ID.
	 * @param object      $verification       The verification response.
	 * @param int         $action_type        The action type.
	 * @param int|null    $customer_id      The customer ID.
	 * @param string|null $token         The security token.
	 *
	 * @return void
	 */
	private function show_confirmation(
		string $slug,
		int $order_id,
		int $site_id,
		$verification,
		int $action_type,
		$customer_id,
		$token
	): void {
		// Get action name and verb.
		$action_name = $this->get_action_name( $action_type );
		$action_verb = $this->get_action_verb( $action_type );

		// Store verification metadata including order summary and branding.
		$order_summary_data = null;
		if ( $verification->has_order_summary() ) {
			$order_summary      = $verification->get_order_summary();
			$order_summary_data = array(
				'items'           => array_map(
					function ( $item ) {
						return array(
							'title'     => $item->get_title(),
							'quantity'  => $item->get_quantity(),
							'price'     => $item->get_price(),
							'total'     => $item->get_total(),
							'image_url' => $item->get_image_url(),
						);
					},
					$order_summary->get_items()
				),
				'subtotal'        => $order_summary->get_subtotal(),
				'discounts'       => $order_summary->get_discounts(),
				'shipping'        => $order_summary->get_shipping(),
				'tax'             => $order_summary->get_tax(),
				'total'           => $order_summary->get_total(),
				'currency_symbol' => $order_summary->get_currency_symbol(),
				'currency_code'   => $order_summary->get_currency_code(),
			);
		}

		// Serialize custom meta tags.
		$custom_meta_tags_data = array();
		if ( $verification->has_custom_meta_tags() ) {
			foreach ( $verification->get_custom_meta_tags() as $tag ) {
				$custom_meta_tags_data[] = array(
					'type'    => $tag->type,
					'key'     => $tag->key,
					'content' => $tag->value,
				);
			}
		}

		$metadata = array(
			'redirect_type'         => $verification->get_redirect_type(),
			'custom_url'            => $verification->get_custom_url(),
			'utm_params'            => $verification->get_utm_params(),
			'message'               => $verification->get_message(),
			'quicklink_id'          => $verification->get_quicklink_id(),
			'token'                 => $token,
			'order_customized_name' => $verification->get_order_customized_name(),
			'custom_logo_url'       => $verification->get_custom_logo_url(),
			'custom_stylesheet_url' => $verification->get_custom_stylesheet_url(),
			'order_summary'         => $order_summary_data,
			'custom_meta_tags'      => $custom_meta_tags_data,
			'action_verb'           => $action_verb,
		);

		// Create confirmation.
		$confirmation = $this->confirmation_service->create_confirmation(
			$slug,
			$order_id,
			$site_id,
			$action_type,
			$action_name,
			$metadata,
			$customer_id,
			is_user_logged_in() ? get_current_user_id() : null
		);

		if ( null === $confirmation ) {
			$this->render_error(
				__( 'Failed to create confirmation. Please try again.', 'autoship' ),
				$metadata['custom_logo_url'] ?? null,
				$metadata['custom_stylesheet_url'] ?? null
			);
			return;
		}

		// Render the confirmation page.
		$this->render_confirm_page( $confirmation );
	}

	/**
	 * Get action name from action type.
	 *
	 * Returns the full action name for display in UI elements like buttons and headers.
	 *
	 * @param int $action_type The action type code.
	 *
	 * @return string The action name.
	 */
	private function get_action_name( int $action_type ): string {
		switch ( $action_type ) {
			case ActionType::PAUSE:
				return __( 'Pause Subscription', 'autoship' );
			case ActionType::PROCESS_NOW:
				return __( 'Process Now', 'autoship' );
			case ActionType::RESUME:
				return __( 'Resume Subscription', 'autoship' );
			case ActionType::REACTIVATE:
				return __( 'Reactivate Subscription', 'autoship' );
			default:
				return __( 'Subscription Action', 'autoship' );
		}
	}

	/**
	 * Get action verb from action type.
	 *
	 * Returns the action verb for use in sentences like "You are about to {verb} your subscription."
	 * This is separate from get_action_name() to support proper internationalization,
	 * as different languages have different sentence structures.
	 *
	 * @param int $action_type The action type code.
	 *
	 * @return string The action verb (lowercase).
	 */
	private function get_action_verb( int $action_type ): string {
		switch ( $action_type ) {
			case ActionType::PAUSE:
				return __( 'pause', 'autoship' );
			case ActionType::PROCESS_NOW:
				return __( 'process', 'autoship' );
			case ActionType::RESUME:
				return __( 'resume', 'autoship' );
			case ActionType::REACTIVATE:
				return __( 'reactivate', 'autoship' );
			default:
				return __( 'update', 'autoship' );
		}
	}
}
