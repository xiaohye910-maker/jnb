<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Factory for creating QuickLink action strategies.
 *
 * Uses the Factory pattern to instantiate the appropriate action
 * strategy based on the action type.
 *
 * @package Autoship\Services\QuickLinks
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks;

use Autoship\Services\QuickLinks\Interfaces\QuickLinkActionInterface;
use Autoship\Services\QuickLinks\Interfaces\QuickLinkRepositoryInterface;
use Autoship\Services\QuickLinks\Actions\ResumeAction;
use Autoship\Services\QuickLinks\Actions\PauseAction;
use Autoship\Services\QuickLinks\Actions\ProcessNowAction;
use Autoship\Services\QuickLinks\Actions\ReactivateAction;
use Autoship\Domain\QuickLinks\ActionType;
use Autoship\Services\Logging\LoggerInterface;
use InvalidArgumentException;

/**
 * Factory for creating QuickLink action instances.
 *
 * Uses the Factory pattern to create an appropriate action
 * strategy based on an action type.
 */
class QuickLinkActionFactory {

	/**
	 * Map of action types to class names.
	 *
	 * @var array<int, class-string<QuickLinkActionInterface>>
	 */
	private const ACTION_MAP = array(
		ActionType::RESUME      => ResumeAction::class,
		ActionType::PAUSE       => PauseAction::class,
		ActionType::PROCESS_NOW => ProcessNowAction::class,
		ActionType::REACTIVATE  => ReactivateAction::class,
	);

	/**
	 * QuickLink repository.
	 *
	 * @var QuickLinkRepositoryInterface
	 */
	private QuickLinkRepositoryInterface $repository;

	/**
	 * Logger instance.
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @param QuickLinkRepositoryInterface $repository The repository instance.
	 * @param LoggerInterface              $logger     The logger instance.
	 */
	public function __construct( QuickLinkRepositoryInterface $repository, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->logger     = $logger;
	}

	/**
	 * Create action instance for given type.
	 *
	 * @param int $action_type The action type constant.
	 *
	 * @return QuickLinkActionInterface
	 * @throws InvalidArgumentException If the action type is not supported.
	 */
	public function create( int $action_type ): QuickLinkActionInterface {
		if ( ! isset( self::ACTION_MAP[ $action_type ] ) ) {
			throw new InvalidArgumentException(
				esc_html(
					sprintf(
					/* translators: %d: action type */
						__( 'Unsupported action type: %d', 'autoship' ),
						$action_type
					)
				)
			);
		}

		$class_name = self::ACTION_MAP[ $action_type ];

		return new $class_name( $this->repository, $this->logger );
	}

	/**
	 * Check if the action type is supported.
	 *
	 * @param int $action_type The action type to check.
	 *
	 * @return bool
	 */
	public function is_supported( int $action_type ): bool {
		return isset( self::ACTION_MAP[ $action_type ] );
	}
}
