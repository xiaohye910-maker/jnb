<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_ConstantContact_API_V2 class for non hub sites
 */

/**
 * Hustle_ConstantContact_API_V2_Non_Hub class
 *
 * @package Hustle
 */
class Hustle_ConstantContact_API_V2_Non_Hub extends Hustle_ConstantContact_API_V2 {

	/**
	 * Constant Contact API Key
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * Get API key
	 *
	 * @return string
	 */
	protected function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Set API key
	 *
	 * @param string $key API key.
	 */
	public function set_api_key( $key ) {
		$this->api_key = $key;
		$this->create_oauth();
	}

	/**
	 * Get redirect URL
	 *
	 * @param string $provider Provider.
	 * @param string $action Action.
	 * @param array  $params Params.
	 * @param bool   $migration Migration.
	 * @return string
	 */
	public function redirect_uri( $provider, $action, $params = array(), $migration = 0 ) {
		$params = wp_parse_args(
			$params,
			array(
				'action'   => $action,
				'provider' => $provider,
			)
		);

		return add_query_arg( $params, site_url() );
	}

	/**
	 * Get state params
	 *
	 * @return array
	 */
	protected function get_state_params() {
		return array(
			'nonce' => $this->get_nonce_value(),
			'url'   => site_url( '/' ),
		);
	}

	/**
	 * Validates request callback from WPMU DEV
	 *
	 * @param string $provider Provider.
	 * @return bool
	 */
	public function validate_callback_request( $provider ) {
		$slug = isset( $_GET['provider'] ) ? sanitize_text_field( wp_unslash( $_GET['provider'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $provider !== $slug ) {
			return false;
		}

		$state     = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$code      = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$client_id = isset( $_GET['client_id'] ) ? sanitize_text_field( wp_unslash( $_GET['client_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (
			empty( $state ) ||
			empty( $code ) ||
			empty( $client_id )
		) {
			return false;
		}

		if ( Hustle_ConstantContact_Api_V2::CLIENT_ID !== $client_id ) {
			return false;
		}

		$state = base64_decode( $state ); // phpcs:ignore
		if ( ! $state ) {
			return false;
		}

		$params = json_decode( $state, true );
		if ( ! $params || count( $params ) < 2 ) {
			return false;
		}

		$nonce = $params['nonce'];
		$url   = isset( $params['url'] ) ? $params['url'] : '';

		if ( site_url( '/' ) !== $url ) {
			return false;
		}

		return $this->verify_nonce( $nonce );
	}
}
