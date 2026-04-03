<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Audit Entry DTO.
 *
 * Data Transfer Object holding all audit data points for QuickLink operations.
 *
 * @package Autoship\Services\QuickLinks\AuditLog\DTOs
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\AuditLog\DTOs;

use Autoship\Services\QuickLinks\AuditLog\Interfaces\AuditFunctionInterface;

/**
 * Audit Entry DTO.
 *
 * Holds complete audit data for a single QuickLink operation,
 * including action details, user context, and result information.
 */
class AuditEntry {

	/**
	 * WordPress functions interface.
	 *
	 * @var AuditFunctionInterface|null
	 */
	private static $wp_functions = null;

	/**
	 * QuickLink unique identifier.
	 *
	 * @var int|null
	 */
	private $quicklink_id;

	/**
	 * QuickLink URL slug.
	 *
	 * @var string|null
	 */
	private $slug;

	/**
	 * Action type (0=Resume, 1=Pause, 2=ProcessNow, 3=Reactivate).
	 *
	 * @var int
	 */
	private $action_type;

	/**
	 * Scheduled order ID.
	 *
	 * @var int
	 */
	private $scheduled_order_id;

	/**
	 * QPilot customer ID.
	 *
	 * @var int|null
	 */
	private $customer_id;

	/**
	 * Client IP address.
	 *
	 * @var string
	 */
	private $ip_address;

	/**
	 * User agent string.
	 *
	 * @var string|null
	 */
	private $user_agent;

	/**
	 * WordPress user ID if logged in.
	 *
	 * @var int|null
	 */
	private $wp_user_id;

	/**
	 * Hashed token for verification.
	 *
	 * @var string|null
	 */
	private $token_hash;

	/**
	 * Whether the action succeeded.
	 *
	 * @var bool
	 */
	private $success;

	/**
	 * Error code if failed.
	 *
	 * @var string|null
	 */
	private $error_code;

	/**
	 * Error message if failed.
	 *
	 * @var string|null
	 */
	private $error_message;

	/**
	 * Timestamp of the audit entry.
	 *
	 * @var string
	 */
	private $created_at;

	/**
	 * Execution time in milliseconds.
	 *
	 * @var int|null
	 */
	private $execution_time_ms;

	/**
	 * Whether entry is flagged for anomaly.
	 *
	 * @var bool
	 */
	private $flagged;

	/**
	 * Reason for flagging.
	 *
	 * @var string|null
	 */
	private $flag_reason;

	/**
	 * Set the WordPress functions interface.
	 *
	 * @param AuditFunctionInterface $wp_functions The WordPress functions interface.
	 *
	 * @return void
	 */
	public static function set_wp_functions( AuditFunctionInterface $wp_functions ): void {
		self::$wp_functions = $wp_functions;
	}

	/**
	 * Get current time using the interface or fallback.
	 *
	 * @return string Current time in MySQL format.
	 */
	private static function get_current_time(): string {
		if ( null !== self::$wp_functions ) {
			return self::$wp_functions->current_time( 'mysql', true );
		}

		// Fallback to direct call if interface not set.
		return gmdate( 'Y-m-d H:i:s' );
	}

	/**
	 * Constructor - PHP 7.4 compatible.
	 *
	 * @param int    $action_type        The action type.
	 * @param int    $scheduled_order_id The scheduled order ID.
	 * @param string $ip_address         The client IP address.
	 * @param bool   $success            Whether action succeeded.
	 */
	public function __construct(
		int $action_type,
		int $scheduled_order_id,
		string $ip_address,
		bool $success
	) {
		$this->action_type        = $action_type;
		$this->scheduled_order_id = $scheduled_order_id;
		$this->ip_address         = $ip_address;
		$this->success            = $success;
		$this->created_at         = self::get_current_time();
		$this->flagged            = false;
	}

	/**
	 * Set the QuickLink ID.
	 *
	 * @param int|null $quicklink_id The QuickLink ID.
	 *
	 * @return self
	 */
	public function set_quicklink_id( $quicklink_id ): self {
		$this->quicklink_id = $quicklink_id;
		return $this;
	}

	/**
	 * Set the slug.
	 *
	 * @param string|null $slug The URL slug.
	 *
	 * @return self
	 */
	public function set_slug( $slug ): self {
		$this->slug = $slug;
		return $this;
	}

	/**
	 * Set the customer ID.
	 *
	 * @param int|null $customer_id The customer ID.
	 *
	 * @return self
	 */
	public function set_customer_id( $customer_id ): self {
		$this->customer_id = $customer_id;
		return $this;
	}

	/**
	 * Set the user agent.
	 *
	 * @param string|null $user_agent The user agent string.
	 *
	 * @return self
	 */
	public function set_user_agent( $user_agent ): self {
		$this->user_agent = $user_agent;
		return $this;
	}

	/**
	 * Set the WordPress user ID.
	 *
	 * @param int|null $wp_user_id The WordPress user ID.
	 *
	 * @return self
	 */
	public function set_wp_user_id( $wp_user_id ): self {
		$this->wp_user_id = $wp_user_id;
		return $this;
	}

	/**
	 * Set the token hash.
	 *
	 * @param string|null $token_hash The hashed token.
	 *
	 * @return self
	 */
	public function set_token_hash( $token_hash ): self {
		$this->token_hash = $token_hash;
		return $this;
	}

	/**
	 * Set the error code.
	 *
	 * @param string|null $error_code The error code.
	 *
	 * @return self
	 */
	public function set_error_code( $error_code ): self {
		$this->error_code = $error_code;
		return $this;
	}

	/**
	 * Set the error message.
	 *
	 * @param string|null $error_message The error message.
	 *
	 * @return self
	 */
	public function set_error_message( $error_message ): self {
		$this->error_message = $error_message;
		return $this;
	}

	/**
	 * Set the execution time in milliseconds.
	 *
	 * @param int|null $execution_time_ms The execution time.
	 *
	 * @return self
	 */
	public function set_execution_time_ms( $execution_time_ms ): self {
		$this->execution_time_ms = $execution_time_ms;
		return $this;
	}

	/**
	 * Set the flagged status.
	 *
	 * @param bool        $flagged Whether the entry is flagged.
	 * @param string|null $reason  The reason for flagging.
	 *
	 * @return self
	 */
	public function set_flagged( bool $flagged, $reason = null ): self {
		$this->flagged = $flagged;
		if ( null !== $reason ) {
			$this->flag_reason = $reason;
		}
		return $this;
	}

	/**
	 * Set the flag reason.
	 *
	 * @param string|null $reason The reason for flagging.
	 *
	 * @return self
	 */
	public function set_flag_reason( $reason ): self {
		$this->flag_reason = $reason;
		return $this;
	}

	/**
	 * Get the QuickLink ID.
	 *
	 * @return int|null
	 */
	public function get_quicklink_id() {
		return $this->quicklink_id;
	}

	/**
	 * Get the slug.
	 *
	 * @return string|null
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the action type.
	 *
	 * @return int
	 */
	public function get_action_type(): int {
		return $this->action_type;
	}

	/**
	 * Get the scheduled order ID.
	 *
	 * @return int
	 */
	public function get_scheduled_order_id(): int {
		return $this->scheduled_order_id;
	}

	/**
	 * Get the customer ID.
	 *
	 * @return int|null
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Get the IP address.
	 *
	 * @return string
	 */
	public function get_ip_address(): string {
		return $this->ip_address;
	}

	/**
	 * Get the user agent.
	 *
	 * @return string|null
	 */
	public function get_user_agent() {
		return $this->user_agent;
	}

	/**
	 * Get the WordPress user ID.
	 *
	 * @return int|null
	 */
	public function get_wp_user_id() {
		return $this->wp_user_id;
	}

	/**
	 * Get the token hash.
	 *
	 * @return string|null
	 */
	public function get_token_hash() {
		return $this->token_hash;
	}

	/**
	 * Check if the action was successful.
	 *
	 * @return bool
	 */
	public function is_success(): bool {
		return $this->success;
	}

	/**
	 * Get the error code.
	 *
	 * @return string|null
	 */
	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * Get the error message.
	 *
	 * @return string|null
	 */
	public function get_error_message() {
		return $this->error_message;
	}

	/**
	 * Get the created timestamp.
	 *
	 * @return string
	 */
	public function get_created_at(): string {
		return $this->created_at;
	}

	/**
	 * Get the execution time in milliseconds.
	 *
	 * @return int|null
	 */
	public function get_execution_time_ms() {
		return $this->execution_time_ms;
	}

	/**
	 * Check if the entry is flagged.
	 *
	 * @return bool
	 */
	public function is_flagged(): bool {
		return $this->flagged;
	}

	/**
	 * Get the flag reason.
	 *
	 * @return string|null
	 */
	public function get_flag_reason() {
		return $this->flag_reason;
	}

	/**
	 * Convert to array for database insertion.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'quicklink_id'       => $this->quicklink_id,
			'slug'               => $this->slug,
			'action_type'        => $this->action_type,
			'scheduled_order_id' => $this->scheduled_order_id,
			'customer_id'        => $this->customer_id,
			'ip_address'         => $this->ip_address,
			'user_agent'         => $this->user_agent,
			'wp_user_id'         => $this->wp_user_id,
			'token_hash'         => $this->token_hash,
			'success'            => $this->success ? 1 : 0,
			'error_code'         => $this->error_code,
			'error_message'      => $this->error_message,
			'created_at'         => $this->created_at,
			'execution_time_ms'  => $this->execution_time_ms,
			'flagged'            => $this->flagged ? 1 : 0,
			'flag_reason'        => $this->flag_reason,
		);
	}

	/**
	 * Create from array (for reading from database).
	 *
	 * @param array $data Row data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$entry = new self(
			(int) $data['action_type'],
			(int) $data['scheduled_order_id'],
			(string) $data['ip_address'],
			(bool) $data['success']
		);

		if ( isset( $data['quicklink_id'] ) ) {
			$entry->set_quicklink_id( (int) $data['quicklink_id'] );
		}

		if ( isset( $data['slug'] ) ) {
			$entry->set_slug( $data['slug'] );
		}

		if ( isset( $data['customer_id'] ) ) {
			$entry->set_customer_id( (int) $data['customer_id'] );
		}

		if ( isset( $data['user_agent'] ) ) {
			$entry->set_user_agent( $data['user_agent'] );
		}

		if ( isset( $data['wp_user_id'] ) ) {
			$entry->set_wp_user_id( (int) $data['wp_user_id'] );
		}

		if ( isset( $data['token_hash'] ) ) {
			$entry->set_token_hash( $data['token_hash'] );
		}

		if ( isset( $data['error_code'] ) ) {
			$entry->set_error_code( $data['error_code'] );
		}

		if ( isset( $data['error_message'] ) ) {
			$entry->set_error_message( $data['error_message'] );
		}

		if ( isset( $data['execution_time_ms'] ) ) {
			$entry->set_execution_time_ms( (int) $data['execution_time_ms'] );
		}

		if ( isset( $data['flagged'] ) ) {
			$entry->set_flagged(
				(bool) $data['flagged'],
				isset( $data['flag_reason'] ) ? $data['flag_reason'] : null
			);
		}

		return $entry;
	}
}
