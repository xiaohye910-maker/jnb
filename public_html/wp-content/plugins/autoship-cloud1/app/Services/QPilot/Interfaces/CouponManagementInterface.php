<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Coupon Management Interface for QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */

namespace Autoship\Services\QPilot\Interfaces;

use Autoship\Services\QPilot\Coupons\CouponResponse;
use Autoship\Services\QPilot\Coupons\CouponSearchRequest;
use Autoship\Services\QPilot\Coupons\CreateCouponRequest;
use Autoship\Services\QPilot\Coupons\UpdateCouponRequest;
use Autoship\Services\QPilot\Coupons\ValidateCouponsRequest;
use Autoship\Services\QPilot\Coupons\CouponValidationResponse;

/**
 * Interface for coupon management with the QPilot API.
 *
 * @package Autoship
 * @subpackage QPilot
 * @since 1.0.0
 */
interface CouponManagementInterface {
	/**
	 * Get a coupon by ID.
	 *
	 * @param int $coupon_id The coupon ID.
	 * @return CouponResponse The coupon object.
	 */
	public function get_coupon( int $coupon_id ): CouponResponse;

	/**
	 * Get coupons with typed filtering parameters.
	 *
	 * @param CouponSearchRequest $request The search parameters.
	 * @return array<CouponResponse> An array of coupon objects.
	 */
	public function get_coupons( CouponSearchRequest $request ): array;

	/**
	 * Get a coupon by code.
	 *
	 * @param string $code The coupon code.
	 * @return CouponResponse The coupon object.
	 */
	public function get_coupon_by_code( string $code ): CouponResponse;

	/**
	 * Create a coupon.
	 *
	 * @param CreateCouponRequest $request The coupon data.
	 * @return CouponResponse The created coupon object.
	 */
	public function create_coupon( CreateCouponRequest $request ): CouponResponse;

	/**
	 * Update a coupon.
	 *
	 * @param int                 $coupon_id The coupon ID.
	 * @param UpdateCouponRequest $request The coupon data.
	 * @return CouponResponse The updated coupon object.
	 */
	public function update_coupon( int $coupon_id, UpdateCouponRequest $request ): CouponResponse;

	/**
	 * Delete a coupon.
	 *
	 * @param int $coupon_id The coupon ID.
	 * @return bool True on success.
	 */
	public function delete_coupon( int $coupon_id ): bool;

	/**
	 * Validate coupons for an order.
	 *
	 * @param int                    $order_id The order ID.
	 * @param ValidateCouponsRequest $request The coupon codes to validate.
	 * @return CouponValidationResponse The validation results.
	 */
	public function validate_coupons( int $order_id, ValidateCouponsRequest $request ): CouponValidationResponse;
}
