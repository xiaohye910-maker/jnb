<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLink Confirmation Service.
 *
 * Main service for handling QuickLink two-step confirmations.
 *
 * @package Autoship\Services\QuickLinks\Confirmation
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Confirmation;

use Autoship\Domain\QuickLinks\QuickLinkConfirmation;
use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationFunctionInterface;
use Autoship\Services\QuickLinks\Confirmation\Interfaces\ConfirmationRepositoryInterface;

/**
 * QuickLink Confirmation Service.
 *
 * Orchestrates confirmation operations including creation,
 * validation, execution, and cancellation.
 */
class QuickLinkConfirmationService {

	/**
	 * Nonce action prefix.
	 */
	const NONCE_ACTION_PREFIX = 'autoship_quicklink_confirm_';

	/**
	 * Repository for confirmation persistence.
	 *
	 * @var ConfirmationRepositoryInterface
	 */
	private $repository;

	/**
	 * WordPress functions interface.
	 *
	 * @var ConfirmationFunctionInterface
	 */
	private $wp_functions;

	/**
	 * Constructor.
	 *
	 * @param ConfirmationRepositoryInterface $repository   Repository implementation.
	 * @param ConfirmationFunctionInterface   $wp_functions WordPress functions interface.
	 */
	public function __construct(
		ConfirmationRepositoryInterface $repository,
		ConfirmationFunctionInterface $wp_functions
	) {
		$this->repository   = $repository;
		$this->wp_functions = $wp_functions;
	}

	/**
	 * Create a new confirmation.
	 *
	 * @param string   $slug               QuickLink slug.
	 * @param int      $scheduled_order_id Scheduled order ID.
	 * @param int      $site_id            Site ID.
	 * @param int      $action_type        Action type code.
	 * @param string   $action_name        Human-readable action name.
	 * @param array    $metadata           Optional verification metadata.
	 * @param int|null $customer_id        Optional customer ID.
	 * @param int|null $wp_user_id         Optional WordPress user ID.
	 *
	 * @return QuickLinkConfirmation|null The created confirmation or null on failure.
	 */
	public function create_confirmation(
		string $slug,
		int $scheduled_order_id,
		int $site_id,
		int $action_type,
		string $action_name,
		array $metadata = array(),
		$customer_id = null,
		$wp_user_id = null
	) {
		$uuid     = $this->wp_functions->generate_uuid();
		$shown_at = $this->wp_functions->current_time( 'mysql', true );

		$confirmation = new QuickLinkConfirmation(
			$uuid,
			$slug,
			$scheduled_order_id,
			$site_id,
			$action_type,
			$action_name,
			$shown_at,
			$this->wp_functions->get_client_ip(),
			$this->wp_functions->get_user_agent(),
			$this->wp_functions->get_referer()
		);

		if ( ! empty( $metadata ) ) {
			$confirmation->set_verification_metadata( $metadata );
		}

		if ( null !== $customer_id ) {
			$confirmation->set_customer_id( $customer_id );
		}

		if ( null !== $wp_user_id ) {
			$confirmation->set_wp_user_id( $wp_user_id );
		}

		if ( ! $this->repository->save( $confirmation ) ) {
			return null;
		}

		$this->wp_functions->do_action( 'autoship_quicklink_confirmation_created', $confirmation );

		return $confirmation;
	}

	/**
	 * Process a confirmation submission.
	 *
	 * Uses pessimistic locking to prevent race conditions.
	 *
	 * @param string $uuid  Confirmation UUID.
	 * @param string $nonce WordPress nonce for verification.
	 *
	 * @return array{success: bool, confirmation: ?QuickLinkConfirmation, error: ?string}
	 */
	public function process_confirmation( string $uuid, string $nonce ): array {
		// Verify nonce.
		if ( ! $this->wp_functions->wp_verify_nonce( $nonce, self::NONCE_ACTION_PREFIX . $uuid ) ) {
			return array(
				'success'      => false,
				'confirmation' => null,
				'error'        => 'invalid_nonce',
			);
		}

		// Start transaction for pessimistic locking.
		$this->repository->begin_transaction();

		try {
			// Get confirmation with lock.
			$confirmation = $this->repository->find_by_uuid_for_update( $uuid );

			if ( null === $confirmation ) {
				$this->repository->rollback();
				return array(
					'success'      => false,
					'confirmation' => null,
					'error'        => 'not_found',
				);
			}

			$current_time = $this->wp_functions->current_time( 'mysql', true );

			// Check if already processed.
			if ( ! $confirmation->is_pending() ) {
				$this->repository->rollback();
				return array(
					'success'      => false,
					'confirmation' => $confirmation,
					'error'        => 'already_processed',
				);
			}

			// Check if expired.
			if ( $confirmation->is_expired( $current_time ) ) {
				$confirmation->mark_expired( $current_time );
				$this->repository->update( $confirmation );
				$this->repository->commit();

				$this->wp_functions->do_action( 'autoship_quicklink_confirmation_expired', $confirmation );

				return array(
					'success'      => false,
					'confirmation' => $confirmation,
					'error'        => 'expired',
				);
			}

			// Mark as confirmed.
			$submission_ip     = $this->wp_functions->get_client_ip();
			$shown_timestamp   = strtotime( $confirmation->get_shown_at() );
			$current_timestamp = strtotime( $current_time );
			$time_to_submit    = $current_timestamp - $shown_timestamp;

			$confirmation->mark_confirmed( $submission_ip, $current_time, $time_to_submit );
			$this->repository->update( $confirmation );
			$this->repository->commit();

			// Fire IP change action if applicable.
			if ( $confirmation->has_ip_changed() ) {
				$this->wp_functions->do_action(
					'autoship_quicklink_confirmation_ip_changed',
					$confirmation,
					$confirmation->get_ip_address(),
					$submission_ip
				);
			}

			$this->wp_functions->do_action( 'autoship_quicklink_confirmation_confirmed', $confirmation );

			return array(
				'success'      => true,
				'confirmation' => $confirmation,
				'error'        => null,
			);

		} catch ( \Exception $e ) {
			$this->repository->rollback();
			return array(
				'success'      => false,
				'confirmation' => null,
				'error'        => 'exception',
			);
		}
	}

	/**
	 * Mark a confirmation as executed.
	 *
	 * @param QuickLinkConfirmation $confirmation   The confirmation.
	 * @param array|null            $execution_result Optional execution result data.
	 *
	 * @return bool True if marked successfully.
	 */
	public function mark_executed( QuickLinkConfirmation $confirmation, $execution_result = null ): bool {
		$executed_at = $this->wp_functions->current_time( 'mysql', true );

		if ( ! $confirmation->mark_executed( $executed_at, $execution_result ) ) {
			return false;
		}

		$result = $this->repository->update( $confirmation );

		if ( $result ) {
			$this->wp_functions->do_action( 'autoship_quicklink_confirmation_executed', $confirmation );
		}

		return $result;
	}

	/**
	 * Mark a confirmation as failed.
	 *
	 * @param QuickLinkConfirmation $confirmation   The confirmation.
	 * @param array|null            $failure_result Optional failure result data.
	 *
	 * @return bool True if marked successfully.
	 */
	public function mark_failed( QuickLinkConfirmation $confirmation, $failure_result = null ): bool {
		$executed_at = $this->wp_functions->current_time( 'mysql', true );

		if ( ! $confirmation->mark_failed( $executed_at, $failure_result ) ) {
			return false;
		}

		$result = $this->repository->update( $confirmation );

		if ( $result ) {
			$this->wp_functions->do_action( 'autoship_quicklink_confirmation_failed', $confirmation );
		}

		return $result;
	}

	/**
	 * Cancel a confirmation.
	 *
	 * @param string $uuid Confirmation UUID.
	 *
	 * @return array{success: bool, confirmation: ?QuickLinkConfirmation, error: ?string}
	 */
	public function cancel_confirmation( string $uuid ): array {
		$confirmation = $this->repository->find_by_uuid( $uuid );

		if ( null === $confirmation ) {
			return array(
				'success'      => false,
				'confirmation' => null,
				'error'        => 'not_found',
			);
		}

		if ( ! $confirmation->is_pending() ) {
			return array(
				'success'      => false,
				'confirmation' => $confirmation,
				'error'        => 'already_processed',
			);
		}

		$confirmation->mark_cancelled();
		$this->repository->update( $confirmation );

		$this->wp_functions->do_action( 'autoship_quicklink_confirmation_cancelled', $confirmation );

		return array(
			'success'      => true,
			'confirmation' => $confirmation,
			'error'        => null,
		);
	}

	/**
	 * Get a confirmation by UUID.
	 *
	 * @param string $uuid Confirmation UUID.
	 *
	 * @return QuickLinkConfirmation|null
	 */
	public function get_confirmation( string $uuid ) {
		return $this->repository->find_by_uuid( $uuid );
	}

	/**
	 * Find an existing pending confirmation for a slug and order.
	 *
	 * @param string $slug               QuickLink slug.
	 * @param int    $scheduled_order_id Scheduled order ID.
	 *
	 * @return QuickLinkConfirmation|null
	 */
	public function find_pending_confirmation( string $slug, int $scheduled_order_id ) {
		return $this->repository->find_pending_for_slug_and_order( $slug, $scheduled_order_id );
	}

	/**
	 * Get confirmation URL.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmation.
	 *
	 * @return string The confirmation page URL.
	 */
	public function get_confirmation_url( QuickLinkConfirmation $confirmation ): string {
		$base = $this->wp_functions->home_url( '/autoship/lc/confirm/' . $confirmation->get_uuid() );
		return $this->wp_functions->apply_filters( 'autoship_quicklink_confirmation_url', $base, $confirmation );
	}

	/**
	 * Get cancel URL.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmation.
	 *
	 * @return string The cancellation URL.
	 */
	public function get_cancel_url( QuickLinkConfirmation $confirmation ): string {
		$base = $this->wp_functions->home_url( '/autoship/lc/cancel/' . $confirmation->get_uuid() );
		return $this->wp_functions->apply_filters( 'autoship_quicklink_cancel_url', $base, $confirmation );
	}

	/**
	 * Generate nonce for a confirmation.
	 *
	 * @param QuickLinkConfirmation $confirmation The confirmation.
	 *
	 * @return string The nonce.
	 */
	public function generate_nonce( QuickLinkConfirmation $confirmation ): string {
		return $this->wp_functions->wp_create_nonce( self::NONCE_ACTION_PREFIX . $confirmation->get_uuid() );
	}

	/**
	 * Delete old confirmations.
	 *
	 * @param int $days Number of days to retain records.
	 *
	 * @return int Number of records deleted.
	 */
	public function cleanup( int $days = 90 ): int {
		$deleted = $this->repository->delete_older_than( $days );

		$this->wp_functions->do_action( 'autoship_quicklink_confirmations_cleaned_up', $deleted, $days );

		return $deleted;
	}

	/**
	 * Check if repository is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->repository->is_available();
	}

	/**
	 * Get repository.
	 *
	 * @return ConfirmationRepositoryInterface
	 */
	public function get_repository(): ConfirmationRepositoryInterface {
		return $this->repository;
	}
}
