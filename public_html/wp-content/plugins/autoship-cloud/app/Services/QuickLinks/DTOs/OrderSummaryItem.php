<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * DTO for Order Summary Item.
 *
 * Represents a single line item in an order summary,
 * used for displaying order details on confirmation pages.
 *
 * @package Autoship\Services\QuickLinks\DTOs
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\DTOs;

/**
 * Order Summary Item DTO.
 *
 * Holds data for a single order line item including
 * product title, quantity, pricing, and image.
 */
class OrderSummaryItem {

	/**
	 * Product title.
	 *
	 * @var string|null
	 */
	public ?string $title = null;

	/**
	 * Quantity ordered.
	 *
	 * @var int|null
	 */
	public ?int $quantity = null;

	/**
	 * Unit price.
	 *
	 * @var float|null
	 */
	public ?float $price = null;

	/**
	 * Line total (quantity * price).
	 *
	 * @var float|null
	 */
	public ?float $total = null;

	/**
	 * Product image URL.
	 *
	 * @var string|null
	 */
	public ?string $image_url = null;

	/**
	 * Create DTO from API response array.
	 *
	 * @param array $data API response data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		$dto->title     = $data['title'] ?? null;
		$dto->quantity  = isset( $data['quantity'] ) ? (int) $data['quantity'] : null;
		$dto->price     = isset( $data['price'] ) ? (float) $data['price'] : null;
		$dto->total     = isset( $data['total'] ) ? (float) $data['total'] : null;
		$dto->image_url = $data['imageUrl'] ?? null;

		return $dto;
	}

	/**
	 * Get product title.
	 *
	 * @return string Product title.
	 */
	public function get_title(): string {
		return $this->title ?? '';
	}

	/**
	 * Get quantity.
	 *
	 * @return int Quantity ordered.
	 */
	public function get_quantity(): int {
		return $this->quantity ?? 0;
	}

	/**
	 * Get unit price.
	 *
	 * @return float Unit price.
	 */
	public function get_price(): float {
		return $this->price ?? 0.0;
	}

	/**
	 * Get line total.
	 *
	 * @return float Line total.
	 */
	public function get_total(): float {
		return $this->total ?? 0.0;
	}

	/**
	 * Get product image URL.
	 *
	 * @return string|null Image URL.
	 */
	public function get_image_url(): ?string {
		return $this->image_url;
	}

	/**
	 * Get formatted price with currency symbol.
	 *
	 * @param string $symbol Currency symbol (default: $).
	 *
	 * @return string Formatted price.
	 */
	public function get_formatted_price( string $symbol = '$' ): string {
		if ( null === $this->price ) {
			return '';
		}

		return $symbol . number_format( $this->price, 2 );
	}

	/**
	 * Get formatted total with currency symbol.
	 *
	 * @param string $symbol Currency symbol (default: $).
	 *
	 * @return string Formatted total.
	 */
	public function get_formatted_total( string $symbol = '$' ): string {
		if ( null === $this->total ) {
			return '';
		}

		return $symbol . number_format( $this->total, 2 );
	}

	/**
	 * Check if product has an image URL.
	 *
	 * @return bool True if image URL exists.
	 */
	public function has_image(): bool {
		return ! empty( $this->image_url );
	}
}
