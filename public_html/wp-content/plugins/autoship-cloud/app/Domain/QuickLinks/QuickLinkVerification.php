<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Value object for QuickLink verification results.
 *
 * Encapsulates the result of verifying a QuickLink with the QPilot API,
 * including validation status, action type, login requirements,
 * and redirect configuration.
 *
 * @package Autoship\Domain\QuickLinks
 * @since   2.11.0
 */

namespace Autoship\Domain\QuickLinks;

use Autoship\Services\QuickLinks\DTOs\OrderSummary;
use Autoship\Services\QuickLinks\DTOs\CustomMetaTag;

/**
 * Represents the result of verifying a QuickLink.
 *
 * Contains validation status, action type, login requirements,
 * and redirect configuration returned from the QPilot API.
 */
class QuickLinkVerification {

	/**
	 * Whether the QuickLink is valid.
	 *
	 * @var bool
	 */
	private bool $valid;

	/**
	 * Whether the QuickLink requires login.
	 *
	 * @var bool
	 */
	private bool $requires_login;

	/**
	 * Whether the QuickLink requires confirmation (H-3).
	 *
	 * @var bool
	 */
	private bool $requires_confirmation;

	/**
	 * The action type to execute.
	 *
	 * @var int|null
	 */
	private ?int $action_type;

	/**
	 * Redirect configuration.
	 *
	 * @var array
	 */
	private array $redirect;

	/**
	 * Message to display to the user.
	 *
	 * @var string|null
	 */
	private ?string $message;

	/**
	 * Error code when validation fails.
	 *
	 * @var string|null
	 */
	private ?string $error_code;

	/**
	 * Error message when validation fails.
	 *
	 * @var string|null
	 */
	private ?string $error_message;

	/**
	 * QuickLink unique identifier.
	 *
	 * @var int|null
	 */
	private ?int $quicklink_id;

	/**
	 * Scheduled order ID.
	 *
	 * @var int|null
	 */
	private ?int $scheduled_order_id;

	/**
	 * Customer ID.
	 *
	 * @var int|null
	 */
	private ?int $customer_id;

	/**
	 * Custom order name for display.
	 *
	 * @var string|null
	 */
	private ?string $order_customized_name;

	/**
	 * Order summary for the confirmation page.
	 *
	 * @var OrderSummary|null
	 */
	private ?OrderSummary $order_summary;

	/**
	 * Custom logo URL for branding.
	 *
	 * @var string|null
	 */
	private ?string $custom_logo_url;

	/**
	 * Custom stylesheet URL for branding.
	 *
	 * @var string|null
	 */
	private ?string $custom_stylesheet_url;

	/**
	 * Custom meta tags for SEO.
	 *
	 * @var CustomMetaTag[]
	 */
	private array $custom_meta_tags;

	/**
	 * Constructor.
	 *
	 * @param bool              $valid                  Whether QuickLink is valid.
	 * @param bool              $requires_login         Whether login is required.
	 * @param int|null          $action_type            Action type to execute.
	 * @param array             $redirect               Redirect configuration.
	 * @param string|null       $message                Message to display.
	 * @param bool              $requires_confirmation  Whether confirmation is required.
	 * @param string|null       $error_code             Error code.
	 * @param string|null       $error_message          Error message.
	 * @param int|null          $quicklink_id           QuickLink ID.
	 * @param int|null          $scheduled_order_id     Scheduled order ID.
	 * @param int|null          $customer_id            Customer ID.
	 * @param string|null       $order_customized_name  Custom order name.
	 * @param OrderSummary|null $order_summary          Order summary.
	 * @param string|null       $custom_logo_url        Custom logo URL.
	 * @param string|null       $custom_stylesheet_url  Custom stylesheet URL.
	 * @param array             $custom_meta_tags       Custom meta tags.
	 */
	public function __construct(
		bool $valid,
		bool $requires_login,
		?int $action_type,
		array $redirect = array(),
		?string $message = null,
		bool $requires_confirmation = false,
		?string $error_code = null,
		?string $error_message = null,
		?int $quicklink_id = null,
		?int $scheduled_order_id = null,
		?int $customer_id = null,
		?string $order_customized_name = null,
		?OrderSummary $order_summary = null,
		?string $custom_logo_url = null,
		?string $custom_stylesheet_url = null,
		array $custom_meta_tags = array()
	) {
		$this->valid                 = $valid;
		$this->requires_login        = $requires_login;
		$this->action_type           = $action_type;
		$this->redirect              = $redirect;
		$this->message               = $message;
		$this->requires_confirmation = $requires_confirmation;
		$this->error_code            = $error_code;
		$this->error_message         = $error_message;
		$this->quicklink_id          = $quicklink_id;
		$this->scheduled_order_id    = $scheduled_order_id;
		$this->customer_id           = $customer_id;
		$this->order_customized_name = $order_customized_name;
		$this->order_summary         = $order_summary;
		$this->custom_logo_url       = $custom_logo_url;
		$this->custom_stylesheet_url = $custom_stylesheet_url;
		$this->custom_meta_tags      = $custom_meta_tags;
	}

	/**
	 * Check if QuickLink is valid.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		return $this->valid;
	}

	/**
	 * Check if login is required.
	 *
	 * @return bool
	 */
	public function requires_login(): bool {
		return $this->requires_login;
	}

	/**
	 * Check if confirmation is required (H-3).
	 *
	 * @return bool
	 */
	public function requires_confirmation(): bool {
		return $this->requires_confirmation;
	}

	/**
	 * Get action type.
	 *
	 * @return int|null
	 */
	public function get_action_type(): ?int {
		return $this->action_type;
	}

	/**
	 * Get redirect configuration.
	 *
	 * @return array
	 */
	public function get_redirect(): array {
		return $this->redirect;
	}

	/**
	 * Get redirect type.
	 *
	 * Parses the redirect type from the API response, which may be
	 * an integer (0, 1, 2) or a string ("V2Portal", "ThankYouPage", "CustomUrl").
	 *
	 * @return int|null
	 */
	public function get_redirect_type(): ?int {
		$type = $this->redirect['type'] ?? null;

		if ( null === $type ) {
			return null;
		}

		return RedirectType::parse( $type );
	}

	/**
	 * Get custom redirect URL.
	 *
	 * @return string|null
	 */
	public function get_custom_url(): ?string {
		return $this->redirect['customUrl'] ?? null;
	}

	/**
	 * Get UTM parameters for redirect.
	 *
	 * @return array
	 */
	public function get_utm_params(): array {
		$utm_json = $this->redirect['utmParams'] ?? '{}';
		$params   = json_decode( $utm_json, true );

		return is_array( $params ) ? $params : array();
	}

	/**
	 * Get message.
	 *
	 * @return string|null
	 */
	public function get_message(): ?string {
		return $this->message;
	}

	/**
	 * Get error code.
	 *
	 * @return string|null
	 */
	public function get_error_code(): ?string {
		return $this->error_code;
	}

	/**
	 * Get error message.
	 *
	 * @return string|null
	 */
	public function get_error_message(): ?string {
		return $this->error_message;
	}

	/**
	 * Get QuickLink ID.
	 *
	 * @return int|null
	 */
	public function get_quicklink_id(): ?int {
		return $this->quicklink_id;
	}

	/**
	 * Get scheduled order ID.
	 *
	 * @return int|null
	 */
	public function get_scheduled_order_id(): ?int {
		return $this->scheduled_order_id;
	}

	/**
	 * Get customer ID.
	 *
	 * @return int|null
	 */
	public function get_customer_id(): ?int {
		return $this->customer_id;
	}

	/**
	 * Get the custom order name.
	 *
	 * @return string|null
	 */
	public function get_order_customized_name(): ?string {
		return $this->order_customized_name;
	}

	/**
	 * Get order summary.
	 *
	 * @return OrderSummary|null
	 */
	public function get_order_summary(): ?OrderSummary {
		return $this->order_summary;
	}

	/**
	 * Get custom logo URL.
	 *
	 * @return string|null
	 */
	public function get_custom_logo_url(): ?string {
		return $this->custom_logo_url;
	}

	/**
	 * Get custom stylesheet URL.
	 *
	 * @return string|null
	 */
	public function get_custom_stylesheet_url(): ?string {
		return $this->custom_stylesheet_url;
	}

	/**
	 * Get custom meta tags.
	 *
	 * @return CustomMetaTag[]
	 */
	public function get_custom_meta_tags(): array {
		return $this->custom_meta_tags;
	}

	/**
	 * Check if the order summary is available.
	 *
	 * @return bool
	 */
	public function has_order_summary(): bool {
		return null !== $this->order_summary;
	}

	/**
	 * Check if custom branding is available.
	 *
	 * @return bool
	 */
	public function has_custom_branding(): bool {
		return ! empty( $this->custom_logo_url ) || ! empty( $this->custom_stylesheet_url );
	}

	/**
	 * Check if custom meta-tags are available.
	 *
	 * @return bool
	 */
	public function has_custom_meta_tags(): bool {
		return ! empty( $this->custom_meta_tags );
	}

	/**
	 * Get all custom meta tags as HTML string.
	 *
	 * @return string HTML meta tags.
	 */
	public function get_meta_tags_html(): string {
		$html = '';
		foreach ( $this->custom_meta_tags as $tag ) {
			$html .= $tag->to_html() . "\n";
		}
		return trim( $html );
	}
}
