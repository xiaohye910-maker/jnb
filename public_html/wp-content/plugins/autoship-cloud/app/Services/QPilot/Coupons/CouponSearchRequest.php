<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

/**
 * CouponSearchRequest class file.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Coupons;

/**
 * Request object for searching coupons.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
class CouponSearchRequest {
	/**
	 * The search results page to return.
	 *
	 * @var int
	 */
	private int $page = 1;

	/**
	 * The page size.
	 *
	 * @var int
	 */
	private int $page_size = 100;

	/**
	 * A property to sort the results by.
	 *
	 * @var string|null
	 */
	private ?string $order_by = null;

	/**
	 * The sort direction (DESC vs ASC).
	 *
	 * @var string|null
	 */
	private ?string $order = null;

	/**
	 * A query string to search for.
	 *
	 * @var string|null
	 */
	private ?string $search = null;

	/**
	 * Filter for active coupons.
	 *
	 * @var bool|null
	 */
	private ?bool $active = null;

	/**
	 * Filter for stackable coupons.
	 *
	 * @var bool|null
	 */
	private ?bool $is_stackable = null;

	/**
	 * Filter for coupons that expire after this date.
	 *
	 * @var string|null
	 */
	private ?string $min_expiration_date = null;

	/**
	 * Filter for coupons that expire before this date.
	 *
	 * @var string|null
	 */
	private ?string $max_expiration_date = null;

	/**
	 * Filter for coupons with a specific discount type.
	 *
	 * @var string|null
	 */
	private ?string $discount_type = null;

	/**
	 * Constructor.
	 *
	 * @param int $page The search results page to return.
	 * @param int $page_size The page size.
	 */
	public function __construct( int $page = 1, int $page_size = 100 ) {
		$this->page      = $page;
		$this->page_size = $page_size;
	}

	/**
	 * Set the page.
	 *
	 * @param int $page The search results page to return.
	 * @return self
	 */
	public function set_page( int $page ): self {
		$this->page = $page;
		return $this;
	}

	/**
	 * Set the page size.
	 *
	 * @param int $page_size The page size.
	 * @return self
	 */
	public function set_page_size( int $page_size ): self {
		$this->page_size = $page_size;
		return $this;
	}

	/**
	 * Set the order by.
	 *
	 * @param string $order_by A property to sort the results by.
	 * @return self
	 */
	public function set_order_by( string $order_by ): self {
		$this->order_by = $order_by;
		return $this;
	}

	/**
	 * Set the order direction.
	 *
	 * @param string $order The sort direction (DESC vs ASC).
	 * @return self
	 */
	public function set_order( string $order ): self {
		$this->order = $order;
		return $this;
	}

	/**
	 * Set the search query.
	 *
	 * @param string $search A query string to search for.
	 * @return self
	 */
	public function set_search( string $search ): self {
		$this->search = $search;
		return $this;
	}

	/**
	 * Set the active filter.
	 *
	 * @param bool $active Filter for active coupons.
	 * @return self
	 */
	public function set_active( bool $active ): self {
		$this->active = $active;
		return $this;
	}

	/**
	 * Set the stackable filter.
	 *
	 * @param bool $is_stackable Filter for stackable coupons.
	 * @return self
	 */
	public function set_is_stackable( bool $is_stackable ): self {
		$this->is_stackable = $is_stackable;
		return $this;
	}

	/**
	 * Set the minimum expiration date.
	 *
	 * @param string $min_expiration_date Filter for coupons that expire after this date.
	 * @return self
	 */
	public function set_min_expiration_date( string $min_expiration_date ): self {
		$this->min_expiration_date = $min_expiration_date;
		return $this;
	}

	/**
	 * Set the maximum expiration date.
	 *
	 * @param string $max_expiration_date Filter for coupons that expire before this date.
	 * @return self
	 */
	public function set_max_expiration_date( string $max_expiration_date ): self {
		$this->max_expiration_date = $max_expiration_date;
		return $this;
	}

	/**
	 * Set the discount type filter.
	 *
	 * @param string $discount_type Filter for coupons with a specific discount type.
	 * @return self
	 */
	public function set_discount_type( string $discount_type ): self {
		$this->discount_type = $discount_type;
		return $this;
	}

	/**
	 * Convert the request to an array.
	 *
	 * @return array The request as an array.
	 */
	public function to_array(): array {
		$data = array(
			'page'     => $this->page,
			'pageSize' => $this->page_size,
		);

		if ( null !== $this->order_by ) {
			$data['orderBy'] = $this->order_by;
		}

		if ( null !== $this->order ) {
			$data['order'] = $this->order;
		}

		if ( null !== $this->search ) {
			$data['search'] = $this->search;
		}

		if ( null !== $this->active ) {
			$data['active'] = $this->active;
		}

		if ( null !== $this->is_stackable ) {
			$data['isStackable'] = $this->is_stackable;
		}

		if ( null !== $this->min_expiration_date ) {
			$data['minExpirationDate'] = $this->min_expiration_date;
		}

		if ( null !== $this->max_expiration_date ) {
			$data['maxExpirationDate'] = $this->max_expiration_date;
		}

		if ( null !== $this->discount_type ) {
			$data['discountType'] = $this->discount_type;
		}

		return $data;
	}
}
