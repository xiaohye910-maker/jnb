<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * QuickLink service implementation.
 *
 * Orchestrates the verify → execute → consume flow for QuickLinks.
 *
 * @package Autoship\Services\QuickLinks\Implementations
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\Implementations;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkServiceInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QuickLinks\QuickLinkActionFactory;
use Autoship\Services\QuickLinks\DTOs\VerifyQuickLinkResponse;
use Autoship\Domain\QuickLinks\QuickLinkVerification;
use Autoship\Domain\QuickLinks\ActionResult;
use Autoship\Domain\QuickLinks\ActionType;

/**
 * QuickLink service implementation.
 *
 * Main service that orchestrates QuickLink operations.
 */
class QuickLinkService implements QuickLinkServiceInterface {

	/**
	 * QuickLink repository.
	 *
	 * @var QuickLinkRepositoryInterface
	 */
	private QuickLinkRepositoryInterface $repository;

	/**
	 * Action factory.
	 *
	 * @var QuickLinkActionFactory
	 */
	private QuickLinkActionFactory $factory;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkRepositoryInterface $repository Repository instance.
	 * @param QuickLinkActionFactory       $factory    Action factory instance.
	 */
	public function __construct(
		QuickLinkRepositoryInterface $repository,
		QuickLinkActionFactory $factory
	) {
		$this->repository = $repository;
		$this->factory    = $factory;
	}

	/**
	 * Verify a QuickLink.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID.
	 * @param string|null $token              Optional security token.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return QuickLinkVerification
	 */
	public function verify_quicklink(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		?string $ip_address = null,
		?string $user_agent = null
	): QuickLinkVerification {
		$response = $this->repository->verify(
			$site_id,
			$slug,
			$scheduled_order_id,
			$customer_id,
			$token,
			$ip_address,
			$user_agent
		);

		// Handle null response (API failure).
		if ( null === $response ) {
			return new QuickLinkVerification(
				false,
				true,
				null,
				array(),
				__( 'Unable to verify QuickLink. Please try again later.', 'autoship' )
			);
		}

		// Parse response through DTO for proper type handling.
		$dto = VerifyQuickLinkResponse::from_array( $response );

		// Map DTO to domain object.
		return $this->map_to_verification( $dto );
	}

	/**
	 * Map VerifyQuickLinkResponse DTO to QuickLinkVerification domain object.
	 *
	 * @param VerifyQuickLinkResponse $dto The response DTO.
	 *
	 * @return QuickLinkVerification
	 */
	private function map_to_verification( VerifyQuickLinkResponse $dto ): QuickLinkVerification {
		return new QuickLinkVerification(
			$dto->valid ?? false,
			$dto->requires_login ?? true,
			$dto->action_type,
			$dto->redirect ?? array(),
			$dto->message,
			$dto->requires_confirmation ?? false,
			$dto->error_code,
			$dto->error_message,
			$dto->quicklink_id,
			$dto->scheduled_order_id,
			$dto->customer_id,
			$dto->order_customized_name,
			$dto->order_summary,
			$dto->custom_logo_url,
			$dto->custom_stylesheet_url,
			$dto->custom_meta_tags
		);
	}

	/**
	 * Execute a QuickLink action.
	 *
	 * @param int $site_id            The QPilot site ID.
	 * @param int $action_type        The action type to execute.
	 * @param int $scheduled_order_id The scheduled order ID.
	 *
	 * @return ActionResult
	 */
	public function execute_action(
		int $site_id,
		int $action_type,
		int $scheduled_order_id
	): ActionResult {
		try {
			// Validate action type.
			if ( ! ActionType::is_valid( $action_type ) ) {
				return ActionResult::failure(
					'INVALID_ACTION_TYPE',
					sprintf(
						/* translators: %d: action type */
						__( 'Invalid action type: %d', 'autoship' ),
						$action_type
					),
					array( 'action_type' => $action_type )
				);
			}

			// Create and execute action.
			$action = $this->factory->create( $action_type );

			return $action->execute( $site_id, $scheduled_order_id );
		} catch ( \Exception $e ) {
			return ActionResult::failure(
				'EXECUTION_EXCEPTION',
				$e->getMessage(),
				array(
					'exception'          => get_class( $e ),
					'action_type'        => $action_type,
					'scheduled_order_id' => $scheduled_order_id,
				)
			);
		}
	}

	/**
	 * Record QuickLink consumption.
	 *
	 * @param int         $site_id            The QPilot site ID.
	 * @param string      $slug               The QuickLink slug.
	 * @param int         $scheduled_order_id The scheduled order ID.
	 * @param int|null    $customer_id        The QPilot customer ID.
	 * @param string|null $token              Optional security token.
	 * @param bool        $success            Whether action succeeded.
	 * @param string|null $error_code         Error code if failed.
	 * @param string|null $error_message      Error message if failed.
	 * @param string|null $ip_address         User's IP address.
	 * @param string|null $user_agent         User's user agent.
	 *
	 * @return bool
	 */
	public function consume_quicklink(
		int $site_id,
		string $slug,
		int $scheduled_order_id,
		?int $customer_id = null,
		?string $token = null,
		bool $success = true,
		?string $error_code = null,
		?string $error_message = null,
		?string $ip_address = null,
		?string $user_agent = null
	): bool {
		$response = $this->repository->consume(
			$site_id,
			$slug,
			$scheduled_order_id,
			$customer_id,
			$token,
			$success,
			$error_code,
			$error_message,
			$ip_address,
			$user_agent
		);

		return null !== $response && ( $response['success'] ?? false );
	}
}
