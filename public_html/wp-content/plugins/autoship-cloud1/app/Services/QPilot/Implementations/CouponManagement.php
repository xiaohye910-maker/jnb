<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot\Implementations;

use Autoship\Services\QPilot\Interfaces\CouponManagementInterface;
use Autoship\Services\QPilot\QPilotHttpClient;
use Autoship\Services\QPilot\Coupons\CouponResponse;
use Autoship\Services\QPilot\Coupons\CouponSearchRequest;
use Autoship\Services\QPilot\Coupons\CreateCouponRequest;
use Autoship\Services\QPilot\Coupons\UpdateCouponRequest;
use Autoship\Services\QPilot\Coupons\ValidateCouponsRequest;
use Autoship\Services\QPilot\Coupons\CouponValidationResponse;
use Exception;

/**
 * Implementation of the CouponManagementInterface.
 *
 * @package Autoship\Services\QPilot\Implementations
 * @since 1.0.0
 */
class CouponManagement implements CouponManagementInterface {
	/**
	 * The API client.
	 *
	 * @var QPilotHttpClient
	 */
	private QPilotHttpClient $api_client;

	/**
	 * Constructor.
	 *
	 * @param QPilotHttpClient $api_client The API client.
	 */
	public function __construct( QPilotHttpClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Get a coupon by ID.
	 *
	 * @param int $coupon_id The coupon ID.
	 *
	 * @return CouponResponse The coupon object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_coupon( int $coupon_id ): CouponResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Coupons/{$coupon_id}";
		$response = $this->api_client->get( $endpoint );

		return new CouponResponse( $response );
	}

	/**
	 * Get coupons with typed filtering parameters.
	 *
	 * @param CouponSearchRequest $request The search parameters.
	 *
	 * @return array<CouponResponse> An array of coupon objects.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_coupons( CouponSearchRequest $request ): array {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Coupons";

		$search_data = $request->to_array();
		$response    = $this->api_client->get( $endpoint, $search_data );

		$coupons = array();
		foreach ( $response as $coupon_data ) {
			$coupons[] = new CouponResponse( $coupon_data );
		}

		return $coupons;
	}

	/**
	 * Get a coupon by code.
	 *
	 * @param string $code The coupon code.
	 *
	 * @return CouponResponse The coupon object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function get_coupon_by_code( string $code ): CouponResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Coupons/ByCode";
		$params   = array( 'code' => $code );

		$response = $this->api_client->get( $endpoint, $params );

		return new CouponResponse( $response );
	}

	/**
	 * Create a coupon.
	 *
	 * @param CreateCouponRequest $request The coupon data.
	 *
	 * @return CouponResponse The created coupon object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function create_coupon( CreateCouponRequest $request ): CouponResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Coupons";

		$coupon_data           = $request->to_array();
		$coupon_data['siteId'] = $site_id;

		$response = $this->api_client->post( $endpoint, $coupon_data );

		return new CouponResponse( $response );
	}

	/**
	 * Update a coupon.
	 *
	 * @param int                 $coupon_id The coupon ID.
	 * @param UpdateCouponRequest $request The coupon data.
	 *
	 * @return CouponResponse The updated coupon object.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function update_coupon( int $coupon_id, UpdateCouponRequest $request ): CouponResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Coupons/{$coupon_id}";

		$coupon_data = $request->to_array();

		$response = $this->api_client->put( $endpoint, $coupon_data );

		return new CouponResponse( $response );
	}

	/**
	 * Delete a coupon.
	 *
	 * @param int $coupon_id The coupon ID.
	 *
	 * @return bool True on success.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function delete_coupon( int $coupon_id ): bool {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/Coupons/{$coupon_id}";

		$response = $this->api_client->delete( $endpoint );

		return true;
	}

	/**
	 * Validate coupons for an order.
	 *
	 * @param int                    $order_id The order ID.
	 * @param ValidateCouponsRequest $request The coupon codes to validate.
	 * @return CouponValidationResponse The validation results.
	 * @throws Exception Thrown on HTTP Error.
	 */
	public function validate_coupons( int $order_id, ValidateCouponsRequest $request ): CouponValidationResponse {
		$site_id  = $this->api_client->get_site_id();
		$endpoint = "Sites/{$site_id}/ScheduledOrders/{$order_id}/ValidateCoupons";

		$data = array(
			'id'      => $order_id,
			'coupons' => $request->get_coupon_codes(),
		);

		$response = $this->api_client->post( $endpoint, $data );

		return new CouponValidationResponse( $response );
	}
}
