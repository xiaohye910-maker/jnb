<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * DTO for Verify QuickLink API response.
 *
 * Simple data transfer object that holds the response data
 * from the QPilot Verify QuickLink API endpoint.
 *
 * @package Autoship\Services\QuickLinks\DTOs
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\DTOs;

/**
 * Verify QuickLink Response DTO.
 *
 * Holds the raw response data from the verify endpoint.
 * This is converted to QuickLinkVerification domain object by the service.
 */
class VerifyQuickLinkResponse {

	/**
	 * Whether the QuickLink is valid.
	 *
	 * @var bool|null
	 */
	public ?bool $valid = null;

	/**
	 * Error code when valid is false.
	 *
	 * @var string|null
	 */
	public ?string $error_code = null;

	/**
	 * Error message when valid is false.
	 *
	 * @var string|null
	 */
	public ?string $error_message = null;

	/**
	 * QuickLink unique identifier.
	 *
	 * @var int|null
	 */
	public ?int $quicklink_id = null;

	/**
	 * The action type to execute (string from API, converted to int).
	 *
	 * @var int|string|null
	 */
	public $action_type = null;

	/**
	 * Whether the QuickLink requires login.
	 *
	 * @var bool|null
	 */
	public ?bool $requires_login = null;

	/**
	 * Whether the QuickLink requires confirmation (H-3).
	 *
	 * @var bool|null
	 */
	public ?bool $requires_confirmation = null;

	/**
	 * The scheduled order ID.
	 *
	 * @var int|null
	 */
	public ?int $scheduled_order_id = null;

	/**
	 * The customer ID.
	 *
	 * @var int|null
	 */
	public ?int $customer_id = null;

	/**
	 * Usage limit type (Unlimited or LimitedUse).
	 *
	 * @var string|null
	 */
	public ?string $usage_limit_type = null;

	/**
	 * Maximum number of uses allowed.
	 *
	 * @var int|null
	 */
	public ?int $max_uses = null;

	/**
	 * Expiration timestamp (UTC).
	 *
	 * @var string|null
	 */
	public ?string $expired_utc = null;

	/**
	 * Redirect configuration.
	 *
	 * @var array|null
	 */
	public ?array $redirect = null;

	/**
	 * Message to display.
	 *
	 * @var string|null
	 */
	public ?string $message = null;

	/**
	 * Custom order name for display.
	 *
	 * @var string|null
	 */
	public ?string $order_customized_name = null;

	/**
	 * Order summary for confirmation page.
	 *
	 * @var OrderSummary|null
	 */
	public ?OrderSummary $order_summary = null;

	/**
	 * Custom logo URL for branding.
	 *
	 * @var string|null
	 */
	public ?string $custom_logo_url = null;

	/**
	 * Custom domain URL.
	 *
	 * @var string|null
	 */
	public ?string $custom_domain_url = null;

	/**
	 * Proxy path for URLs.
	 *
	 * @var string|null
	 */
	public ?string $proxy_path = null;

	/**
	 * Custom stylesheet URL for branding.
	 *
	 * @var string|null
	 */
	public ?string $custom_stylesheet_url = null;

	/**
	 * Custom meta tags for SEO.
	 *
	 * @var CustomMetaTag[]
	 */
	public array $custom_meta_tags = array();

	/**
	 * Create DTO from API response array.
	 *
	 * @param array $data API response data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		// Core validation fields.
		$dto->valid         = $data['valid'] ?? null;
		$dto->error_code    = $data['errorCode'] ?? null;
		$dto->error_message = $data['errorMessage'] ?? null;

		// QuickLink identification.
		$dto->quicklink_id = isset( $data['quickLinkId'] ) ? (int) $data['quickLinkId'] : null;

		// Action and requirements.
		$dto->action_type           = self::parse_action_type( $data['actionType'] ?? null );
		$dto->requires_login        = $data['requiresLogin'] ?? null;
		$dto->requires_confirmation = $data['requiresConfirmation'] ?? null;

		// Order and customer references.
		$dto->scheduled_order_id = isset( $data['scheduledOrderId'] ) ? (int) $data['scheduledOrderId'] : null;
		$dto->customer_id        = isset( $data['customerId'] ) ? (int) $data['customerId'] : null;

		// Usage limits.
		$dto->usage_limit_type = $data['usageLimitType'] ?? null;
		$dto->max_uses         = isset( $data['maxUses'] ) ? (int) $data['maxUses'] : null;
		$dto->expired_utc      = $data['expiredUtc'] ?? null;

		// Redirect configuration.
		$dto->redirect = $data['redirect'] ?? null;
		$dto->message  = $data['redirect']['message'] ?? null;

		// Order display.
		$dto->order_customized_name = $data['orderCustomizedName'] ?? null;

		// Order summary.
		if ( ! empty( $data['orderSummary'] ) && is_array( $data['orderSummary'] ) ) {
			$dto->order_summary = OrderSummary::from_array( $data['orderSummary'] );
		}

		// Branding.
		$dto->custom_logo_url       = $data['customLogoUrl'] ?? null;
		$dto->custom_domain_url     = $data['customDomainUrl'] ?? null;
		$dto->proxy_path            = $data['proxyPath'] ?? null;
		$dto->custom_stylesheet_url = $data['customStyleSheetUrl'] ?? null;

		// Custom meta tags.
		if ( ! empty( $data['customMetaTags'] ) && is_array( $data['customMetaTags'] ) ) {
			foreach ( $data['customMetaTags'] as $tag_data ) {
				$dto->custom_meta_tags[] = CustomMetaTag::from_array( $tag_data );
			}
		}

		return $dto;
	}

	/**
	 * Parse action type from string to integer.
	 *
	 * @param string|int|null $action_type The action type value.
	 *
	 * @return int|null The action type as integer.
	 */
	private static function parse_action_type( $action_type ): ?int {
		if ( null === $action_type ) {
			return null;
		}

		// If already an integer, return it.
		if ( is_int( $action_type ) ) {
			return $action_type;
		}

		// Map string values to integers.
		$mapping = array(
			'Resume'     => 0,
			'Pause'      => 1,
			'ProcessNow' => 2,
			'Reactivate' => 3,
		);

		return $mapping[ $action_type ] ?? null;
	}

	/**
	 * Check if the response indicates a valid QuickLink.
	 *
	 * @return bool True if valid.
	 */
	public function is_valid(): bool {
		return true === $this->valid;
	}

	/**
	 * Check if the QuickLink is expired.
	 *
	 * @return bool True if expired.
	 */
	public function is_expired(): bool {
		if ( empty( $this->expired_utc ) ) {
			return false;
		}

		$expiry_time  = strtotime( $this->expired_utc );
		$current_time = time();

		return false !== $expiry_time && $current_time > $expiry_time;
	}
}
