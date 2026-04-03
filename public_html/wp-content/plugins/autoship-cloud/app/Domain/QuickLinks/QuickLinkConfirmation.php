<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLink Confirmation Entity.
 *
 * Domain entity representing a QuickLink confirmation with state machine.
 *
 * @package Autoship\Domain\QuickLinks
 * @since   2.11.0
 */

namespace Autoship\Domain\QuickLinks;

/**
 * QuickLink Confirmation Entity.
 *
 * Represents a pending confirmation for a QuickLink action.
 * Implements a state machine for status transitions:
 * - pending -> confirmed -> executed
 * - pending -> confirmed -> failed
 * - pending -> cancelled
 * - pending -> expired
 */
class QuickLinkConfirmation {

	/**
	 * Status constants.
	 */
	const STATUS_PENDING   = 'pending';
	const STATUS_CONFIRMED = 'confirmed';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_EXPIRED   = 'expired';
	const STATUS_EXECUTED  = 'executed';
	const STATUS_FAILED    = 'failed';

	/**
	 * Expiration time in minutes.
	 */
	const EXPIRATION_MINUTES = 30;

	/**
	 * Record ID.
	 *
	 * @var int|null
	 */
	private $id;

	/**
	 * UUID for the confirmation.
	 *
	 * @var string
	 */
	private $uuid;

	/**
	 * QuickLink slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Scheduled order ID.
	 *
	 * @var int
	 */
	private $scheduled_order_id;

	/**
	 * Site ID.
	 *
	 * @var int
	 */
	private $site_id;

	/**
	 * Action type (numeric).
	 *
	 * @var int
	 */
	private $action_type;

	/**
	 * Action name.
	 *
	 * @var string
	 */
	private $action_name;

	/**
	 * Current status.
	 *
	 * @var string
	 */
	private $status;

	/**
	 * IP address when confirmation was shown.
	 *
	 * @var string|null
	 */
	private $ip_address;

	/**
	 * IP address when confirmation was submitted.
	 *
	 * @var string|null
	 */
	private $submission_ip_address;

	/**
	 * Whether IP changed between showing and submission.
	 *
	 * @var bool
	 */
	private $ip_changed = false;

	/**
	 * User agent string.
	 *
	 * @var string|null
	 */
	private $user_agent;

	/**
	 * HTTP referer.
	 *
	 * @var string|null
	 */
	private $referer;

	/**
	 * Customer ID from QPilot.
	 *
	 * @var int|null
	 */
	private $customer_id;

	/**
	 * WordPress user ID.
	 *
	 * @var int|null
	 */
	private $wp_user_id;

	/**
	 * When confirmation page was shown.
	 *
	 * @var string
	 */
	private $shown_at;

	/**
	 * When confirmation was submitted.
	 *
	 * @var string|null
	 */
	private $submitted_at;

	/**
	 * When action was executed.
	 *
	 * @var string|null
	 */
	private $executed_at;

	/**
	 * When confirmation expired.
	 *
	 * @var string|null
	 */
	private $expired_at;

	/**
	 * Time in seconds between showing and submission.
	 *
	 * @var int|null
	 */
	private $time_to_submit_seconds;

	/**
	 * Verification metadata (JSON).
	 *
	 * @var array|null
	 */
	private $verification_metadata;

	/**
	 * Execution result (JSON).
	 *
	 * @var array|null
	 */
	private $execution_result;

	/**
	 * Created timestamp.
	 *
	 * @var string|null
	 */
	private $created_at;

	/**
	 * Updated timestamp.
	 *
	 * @var string|null
	 */
	private $updated_at;

	/**
	 * Constructor.
	 *
	 * @param string      $uuid               Unique identifier.
	 * @param string      $slug               QuickLink slug.
	 * @param int         $scheduled_order_id Scheduled order ID.
	 * @param int         $site_id            Site ID.
	 * @param int         $action_type        Action type code.
	 * @param string      $action_name        Action name.
	 * @param string      $shown_at           When confirmation was shown.
	 * @param string|null $ip_address         IP address.
	 * @param string|null $user_agent         User agent.
	 * @param string|null $referer            HTTP referer.
	 */
	public function __construct(
		string $uuid,
		string $slug,
		int $scheduled_order_id,
		int $site_id,
		int $action_type,
		string $action_name,
		string $shown_at,
		$ip_address = null,
		$user_agent = null,
		$referer = null
	) {
		$this->uuid               = $uuid;
		$this->slug               = $slug;
		$this->scheduled_order_id = $scheduled_order_id;
		$this->site_id            = $site_id;
		$this->action_type        = $action_type;
		$this->action_name        = $action_name;
		$this->shown_at           = $shown_at;
		$this->ip_address         = $ip_address;
		$this->user_agent         = $user_agent;
		$this->referer            = $referer;
		$this->status             = self::STATUS_PENDING;
	}

	/**
	 * Create from database row.
	 *
	 * @param object $row Database row object.
	 *
	 * @return self
	 */
	public static function from_db_row( $row ): self {
		$confirmation = new self(
			$row->uuid,
			$row->slug,
			(int) $row->scheduled_order_id,
			(int) $row->site_id,
			(int) $row->action_type,
			$row->action_name,
			$row->shown_at,
			$row->ip_address,
			$row->user_agent,
			$row->referer
		);

		$confirmation->id                     = isset( $row->id ) ? (int) $row->id : null;
		$confirmation->status                 = $row->status;
		$confirmation->submission_ip_address  = $row->submission_ip_address;
		$confirmation->ip_changed             = (bool) $row->ip_changed;
		$confirmation->customer_id            = isset( $row->customer_id ) ? (int) $row->customer_id : null;
		$confirmation->wp_user_id             = isset( $row->wp_user_id ) ? (int) $row->wp_user_id : null;
		$confirmation->submitted_at           = $row->submitted_at;
		$confirmation->executed_at            = $row->executed_at;
		$confirmation->expired_at             = $row->expired_at;
		$confirmation->time_to_submit_seconds = isset( $row->time_to_submit_seconds ) ? (int) $row->time_to_submit_seconds : null;
		$confirmation->created_at             = $row->created_at;
		$confirmation->updated_at             = $row->updated_at;

		if ( ! empty( $row->verification_metadata ) ) {
			$confirmation->verification_metadata = json_decode( $row->verification_metadata, true );
		}

		if ( ! empty( $row->execution_result ) ) {
			$confirmation->execution_result = json_decode( $row->execution_result, true );
		}

		return $confirmation;
	}

	/**
	 * Check if confirmation is pending.
	 *
	 * @return bool
	 */
	public function is_pending(): bool {
		return self::STATUS_PENDING === $this->status;
	}

	/**
	 * Check if confirmation is confirmed.
	 *
	 * @return bool
	 */
	public function is_confirmed(): bool {
		return self::STATUS_CONFIRMED === $this->status;
	}

	/**
	 * Check if confirmation is cancelled.
	 *
	 * @return bool
	 */
	public function is_cancelled(): bool {
		return self::STATUS_CANCELLED === $this->status;
	}

	/**
	 * Check if confirmation is expired.
	 *
	 * @param string $current_time Current time in MySQL format.
	 *
	 * @return bool
	 */
	public function is_expired( string $current_time ): bool {
		if ( self::STATUS_EXPIRED === $this->status ) {
			return true;
		}

		if ( ! $this->is_pending() ) {
			return false;
		}

		$shown_timestamp   = strtotime( $this->shown_at );
		$current_timestamp = strtotime( $current_time );
		$diff_minutes      = ( $current_timestamp - $shown_timestamp ) / 60;

		return $diff_minutes > self::EXPIRATION_MINUTES;
	}

	/**
	 * Check if confirmation is executed.
	 *
	 * @return bool
	 */
	public function is_executed(): bool {
		return self::STATUS_EXECUTED === $this->status;
	}

	/**
	 * Check if confirmation is failed.
	 *
	 * @return bool
	 */
	public function is_failed(): bool {
		return self::STATUS_FAILED === $this->status;
	}

	/**
	 * Check if confirmation has been processed (not pending).
	 *
	 * @return bool
	 */
	public function is_processed(): bool {
		return ! $this->is_pending();
	}

	/**
	 * Mark confirmation as confirmed.
	 *
	 * @param string $submission_ip        IP address of submission.
	 * @param string $submitted_at         Timestamp of submission.
	 * @param int    $time_to_submit_seconds Time taken to submit.
	 *
	 * @return bool True if transition was valid.
	 */
	public function mark_confirmed( string $submission_ip, string $submitted_at, int $time_to_submit_seconds ): bool {
		if ( ! $this->is_pending() ) {
			return false;
		}

		$this->status                 = self::STATUS_CONFIRMED;
		$this->submission_ip_address  = $submission_ip;
		$this->submitted_at           = $submitted_at;
		$this->time_to_submit_seconds = $time_to_submit_seconds;
		$this->ip_changed             = $this->ip_address !== $submission_ip;

		return true;
	}

	/**
	 * Mark confirmation as cancelled.
	 *
	 * @return bool True if transition was valid.
	 */
	public function mark_cancelled(): bool {
		if ( ! $this->is_pending() ) {
			return false;
		}

		$this->status = self::STATUS_CANCELLED;
		return true;
	}

	/**
	 * Mark confirmation as expired.
	 *
	 * @param string $expired_at Expiration timestamp.
	 *
	 * @return bool True if transition was valid.
	 */
	public function mark_expired( string $expired_at ): bool {
		if ( ! $this->is_pending() ) {
			return false;
		}

		$this->status     = self::STATUS_EXPIRED;
		$this->expired_at = $expired_at;
		return true;
	}

	/**
	 * Mark confirmation as executed.
	 *
	 * @param string     $executed_at      Execution timestamp.
	 * @param array|null $execution_result Execution result data.
	 *
	 * @return bool True if transition was valid.
	 */
	public function mark_executed( string $executed_at, $execution_result = null ): bool {
		if ( ! $this->is_confirmed() ) {
			return false;
		}

		$this->status           = self::STATUS_EXECUTED;
		$this->executed_at      = $executed_at;
		$this->execution_result = $execution_result;
		return true;
	}

	/**
	 * Mark confirmation as failed.
	 *
	 * @param string     $executed_at      Execution attempt timestamp.
	 * @param array|null $execution_result Failure result data.
	 *
	 * @return bool True if transition was valid.
	 */
	public function mark_failed( string $executed_at, $execution_result = null ): bool {
		if ( ! $this->is_confirmed() ) {
			return false;
		}

		$this->status           = self::STATUS_FAILED;
		$this->executed_at      = $executed_at;
		$this->execution_result = $execution_result;
		return true;
	}

	/**
	 * Set verification metadata.
	 *
	 * @param array $metadata Metadata array.
	 *
	 * @return void
	 */
	public function set_verification_metadata( array $metadata ): void {
		$this->verification_metadata = $metadata;
	}

	/**
	 * Set customer ID.
	 *
	 * @param int|null $customer_id Customer ID.
	 *
	 * @return void
	 */
	public function set_customer_id( $customer_id ): void {
		$this->customer_id = $customer_id;
	}

	/**
	 * Set WordPress user ID.
	 *
	 * @param int|null $wp_user_id WordPress user ID.
	 *
	 * @return void
	 */
	public function set_wp_user_id( $wp_user_id ): void {
		$this->wp_user_id = $wp_user_id;
	}

	/**
	 * Set record ID.
	 *
	 * @param int $id Record ID.
	 *
	 * @return void
	 */
	public function set_id( int $id ): void {
		$this->id = $id;
	}

	// Getters.

	/**
	 * Get record ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get UUID.
	 *
	 * @return string
	 */
	public function get_uuid(): string {
		return $this->uuid;
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Get scheduled order ID.
	 *
	 * @return int
	 */
	public function get_scheduled_order_id(): int {
		return $this->scheduled_order_id;
	}

	/**
	 * Get site ID.
	 *
	 * @return int
	 */
	public function get_site_id(): int {
		return $this->site_id;
	}

	/**
	 * Get action type.
	 *
	 * @return int
	 */
	public function get_action_type(): int {
		return $this->action_type;
	}

	/**
	 * Get action name.
	 *
	 * @return string
	 */
	public function get_action_name(): string {
		return $this->action_name;
	}

	/**
	 * Get status.
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get IP address.
	 *
	 * @return string|null
	 */
	public function get_ip_address() {
		return $this->ip_address;
	}

	/**
	 * Get submission IP address.
	 *
	 * @return string|null
	 */
	public function get_submission_ip_address() {
		return $this->submission_ip_address;
	}

	/**
	 * Check if IP changed.
	 *
	 * @return bool
	 */
	public function has_ip_changed(): bool {
		return $this->ip_changed;
	}

	/**
	 * Get user agent.
	 *
	 * @return string|null
	 */
	public function get_user_agent() {
		return $this->user_agent;
	}

	/**
	 * Get referer.
	 *
	 * @return string|null
	 */
	public function get_referer() {
		return $this->referer;
	}

	/**
	 * Get customer ID.
	 *
	 * @return int|null
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Get WordPress user ID.
	 *
	 * @return int|null
	 */
	public function get_wp_user_id() {
		return $this->wp_user_id;
	}

	/**
	 * Get shown at timestamp.
	 *
	 * @return string
	 */
	public function get_shown_at(): string {
		return $this->shown_at;
	}

	/**
	 * Get submitted at timestamp.
	 *
	 * @return string|null
	 */
	public function get_submitted_at() {
		return $this->submitted_at;
	}

	/**
	 * Get executed at timestamp.
	 *
	 * @return string|null
	 */
	public function get_executed_at() {
		return $this->executed_at;
	}

	/**
	 * Get expired at timestamp.
	 *
	 * @return string|null
	 */
	public function get_expired_at() {
		return $this->expired_at;
	}

	/**
	 * Get time to submit in seconds.
	 *
	 * @return int|null
	 */
	public function get_time_to_submit_seconds() {
		return $this->time_to_submit_seconds;
	}

	/**
	 * Get verification metadata.
	 *
	 * @return array|null
	 */
	public function get_verification_metadata() {
		return $this->verification_metadata;
	}

	/**
	 * Get execution result.
	 *
	 * @return array|null
	 */
	public function get_execution_result() {
		return $this->execution_result;
	}

	/**
	 * Get created at timestamp.
	 *
	 * @return string|null
	 */
	public function get_created_at() {
		return $this->created_at;
	}

	/**
	 * Get updated at timestamp.
	 *
	 * @return string|null
	 */
	public function get_updated_at() {
		return $this->updated_at;
	}

	/**
	 * Convert to array for database storage.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'uuid'                   => $this->uuid,
			'slug'                   => $this->slug,
			'scheduled_order_id'     => $this->scheduled_order_id,
			'site_id'                => $this->site_id,
			'action_type'            => $this->action_type,
			'action_name'            => $this->action_name,
			'status'                 => $this->status,
			'ip_address'             => $this->ip_address,
			'submission_ip_address'  => $this->submission_ip_address,
			'ip_changed'             => $this->ip_changed ? 1 : 0,
			'user_agent'             => $this->user_agent,
			'referer'                => $this->referer,
			'customer_id'            => $this->customer_id,
			'wp_user_id'             => $this->wp_user_id,
			'shown_at'               => $this->shown_at,
			'submitted_at'           => $this->submitted_at,
			'executed_at'            => $this->executed_at,
			'expired_at'             => $this->expired_at,
			'time_to_submit_seconds' => $this->time_to_submit_seconds,
			'verification_metadata'  => $this->verification_metadata ? wp_json_encode( $this->verification_metadata ) : null,
			'execution_result'       => $this->execution_result ? wp_json_encode( $this->execution_result ) : null,
		);
	}

	/**
	 * Get all valid status values.
	 *
	 * @return array
	 */
	public static function get_valid_statuses(): array {
		return array(
			self::STATUS_PENDING,
			self::STATUS_CONFIRMED,
			self::STATUS_CANCELLED,
			self::STATUS_EXPIRED,
			self::STATUS_EXECUTED,
			self::STATUS_FAILED,
		);
	}
}
