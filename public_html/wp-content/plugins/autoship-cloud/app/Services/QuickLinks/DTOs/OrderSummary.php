<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * DTO for Order Summary.
 *
 * Represents the complete order summary including line items,
 * totals, and currency information for confirmation pages.
 *
 * @package Autoship\Services\QuickLinks\DTOs
 * @since   3.2.0
 */

namespace Autoship\Services\QuickLinks\DTOs;

/**
 * Order Summary DTO.
 *
 * Holds complete order summary data including items,
 * subtotal, discounts, shipping, tax, and total.
 */
class OrderSummary {

	/**
	 * Order line items.
	 *
	 * @var OrderSummaryItem[]
	 */
	public array $items = array();

	/**
	 * Subtotal before discounts, shipping, and tax.
	 *
	 * @var float|null
	 */
	public ?float $subtotal = null;

	/**
	 * Total discounts applied.
	 *
	 * @var float|null
	 */
	public ?float $discounts = null;

	/**
	 * Shipping cost.
	 *
	 * @var float|null
	 */
	public ?float $shipping = null;

	/**
	 * Tax amount.
	 *
	 * @var float|null
	 */
	public ?float $tax = null;

	/**
	 * Order total.
	 *
	 * @var float|null
	 */
	public ?float $total = null;

	/**
	 * Currency code (e.g., USD, EUR).
	 *
	 * @var string|null
	 */
	public ?string $currency_code = null;

	/**
	 * Currency symbol (e.g., $, €).
	 *
	 * @var string|null
	 */
	public ?string $currency_symbol = null;

	/**
	 * Last processing timestamp (UTC).
	 *
	 * @var string|null
	 */
	public ?string $last_processing_utc = null;

	/**
	 * Create DTO from API response array.
	 *
	 * @param array $data API response data.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		// Parse items.
		if ( ! empty( $data['items'] ) && is_array( $data['items'] ) ) {
			foreach ( $data['items'] as $item_data ) {
				$dto->items[] = OrderSummaryItem::from_array( $item_data );
			}
		}

		$dto->subtotal            = isset( $data['subtotal'] ) ? (float) $data['subtotal'] : null;
		$dto->discounts           = isset( $data['discounts'] ) ? (float) $data['discounts'] : null;
		$dto->shipping            = isset( $data['shipping'] ) ? (float) $data['shipping'] : null;
		$dto->tax                 = isset( $data['tax'] ) ? (float) $data['tax'] : null;
		$dto->total               = isset( $data['total'] ) ? (float) $data['total'] : null;
		$dto->currency_code       = $data['currencyCode'] ?? null;
		$dto->currency_symbol     = $data['currencySymbol'] ?? null;
		$dto->last_processing_utc = $data['lastProcessingUtc'] ?? null;

		return $dto;
	}

	/**
	 * Get order items.
	 *
	 * @return OrderSummaryItem[] Order items.
	 */
	public function get_items(): array {
		return $this->items;
	}

	/**
	 * Get subtotal.
	 *
	 * @return float Subtotal amount.
	 */
	public function get_subtotal(): float {
		return $this->subtotal ?? 0.0;
	}

	/**
	 * Get discounts.
	 *
	 * @return float Discounts amount.
	 */
	public function get_discounts(): float {
		return $this->discounts ?? 0.0;
	}

	/**
	 * Get shipping cost.
	 *
	 * @return float Shipping amount.
	 */
	public function get_shipping(): float {
		return $this->shipping ?? 0.0;
	}

	/**
	 * Get tax amount.
	 *
	 * @return float Tax amount.
	 */
	public function get_tax(): float {
		return $this->tax ?? 0.0;
	}

	/**
	 * Get order total.
	 *
	 * @return float Total amount.
	 */
	public function get_total(): float {
		return $this->total ?? 0.0;
	}

	/**
	 * Get currency symbol.
	 *
	 * @return string Currency symbol.
	 */
	public function get_currency_symbol(): string {
		return $this->currency_symbol ?? '$';
	}

	/**
	 * Get currency code.
	 *
	 * @return string|null Currency code.
	 */
	public function get_currency_code(): ?string {
		return $this->currency_code;
	}

	/**
	 * Get total number of items in the order.
	 *
	 * @return int Item count.
	 */
	public function get_item_count(): int {
		return count( $this->items );
	}

	/**
	 * Get total quantity of all items.
	 *
	 * @return int Total quantity.
	 */
	public function get_total_quantity(): int {
		$total = 0;
		foreach ( $this->items as $item ) {
			$total += $item->quantity ?? 0;
		}
		return $total;
	}

	/**
	 * Get formatted subtotal with currency symbol.
	 *
	 * @return string Formatted subtotal.
	 */
	public function get_formatted_subtotal(): string {
		return $this->format_currency( $this->subtotal );
	}

	/**
	 * Get formatted discounts with currency symbol.
	 *
	 * @return string Formatted discounts.
	 */
	public function get_formatted_discounts(): string {
		return $this->format_currency( $this->discounts );
	}

	/**
	 * Get formatted shipping with currency symbol.
	 *
	 * @return string Formatted shipping.
	 */
	public function get_formatted_shipping(): string {
		return $this->format_currency( $this->shipping );
	}

	/**
	 * Get formatted tax with currency symbol.
	 *
	 * @return string Formatted tax.
	 */
	public function get_formatted_tax(): string {
		return $this->format_currency( $this->tax );
	}

	/**
	 * Get formatted total with currency symbol.
	 *
	 * @return string Formatted total.
	 */
	public function get_formatted_total(): string {
		return $this->format_currency( $this->total );
	}

	/**
	 * Check if order has discounts applied.
	 *
	 * @return bool True if discounts exist.
	 */
	public function has_discounts(): bool {
		return null !== $this->discounts && $this->discounts > 0;
	}

	/**
	 * Check if order has shipping cost.
	 *
	 * @return bool True if shipping exists.
	 */
	public function has_shipping(): bool {
		return null !== $this->shipping && $this->shipping > 0;
	}

	/**
	 * Check if order has tax.
	 *
	 * @return bool True if tax exists.
	 */
	public function has_tax(): bool {
		return null !== $this->tax && $this->tax > 0;
	}

	/**
	 * Format a currency value.
	 *
	 * @param float|null $value The value to format.
	 *
	 * @return string Formatted currency string.
	 */
	private function format_currency( ?float $value ): string {
		if ( null === $value ) {
			return '';
		}

		$symbol = $this->currency_symbol ?? '$';

		return $symbol . number_format( $value, 2 );
	}
}
