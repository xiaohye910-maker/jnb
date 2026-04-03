<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * DTO for Consume QuickLink API response.
 *
 * Simple data transfer object that holds the response data
 * from the QPilot Consume QuickLink API endpoint.
 *
 * @package Autoship\Services\QuickLinks\DTOs
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\DTOs;

/**
 * Consume QuickLink Response DTO.
 *
 * Holds the raw response data from the consume endpoint.
 */
class ConsumeQuickLinkResponse {

	/**
	 * Whether consumption was recorded successfully.
	 *
	 * @var bool|null
	 */
	public ?bool $success = null;

	/**
	 * Message from the API.
	 *
	 * @var string|null
	 */
	public ?string $message = null;

	/**
	 * Create DTO from API response array.
	 *
	 * @param array $data API response data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		$dto->success = $data['success'] ?? null;
		$dto->message = $data['message'] ?? null;

		return $dto;
	}
}
