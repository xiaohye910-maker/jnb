<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Hustle Infusion Soft OAuth class
 *
 * @package Hustle
 */
class Hustle_Infusion_Soft_OAuth extends Opt_In_WPMUDEV_API implements Hustle_Auth_Provider {
	private const CLIENT_ID   = 'z2vk8raljl2Rf37bWXQ9IfHN9HMEC3';
	private const API_KEY     = 'Ph4g9sKLtgZFdXv48j3GGCixjgfi43e2QXSJdzAJ4VCP8gW4';
	private const OPTION_NAME = 'hustle_infusionsoft_oauth_token';

	const REFERER     = 'hustle_infusionsoft_referer';
	const CURRENTPAGE = 'hustle_infusionsoft_current_page';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_process_external_redirect' ) );
	}

	/**
	 * Maybe process the external redirect.
	 */
	public function maybe_process_external_redirect() {
		if ( $this->validate_callback_request( Hustle_Infusion_Soft::SLUG ) ) {
			$this->process_external_redirect();
		}
	}

	/**
	 * Process the external redirect after user authorization.
	 *
	 * @return void
	 */
	public function process_external_redirect() {

		$referer      = get_option( self::REFERER );
		$current_page = get_option( self::CURRENTPAGE );

		// Allow retry but don't log referrer.
		$authorization_uri = $this->get_authorization_uri( false, false, $current_page );

		$error = false;
		$code  = '';

		if ( isset( $_GET['code'] ) ) {
			$code = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		}

		if ( ! $code ) {
			$this->api_die(
				__( 'Authorization code is missing.', 'hustle' ),
				$authorization_uri,
				$referer
			);
			return; // For unit tests.
		}

		// Exchange the authorization code for an access token.
		$token = $this->get_access_token( $code );
		if ( is_wp_error( $token ) ) {
			$this->api_die(
				$token->get_error_message(),
				$authorization_uri,
				$referer
			);
			return; // For unit tests.
		} elseif ( ! $token instanceof Hustle_Auth_Token ) {
			$this->api_die(
				__( 'Token request failed.', 'hustle' ),
				$authorization_uri,
				$referer
			);
			return; // For unit tests.
		}

		$scope = $token->get_scope();
		$scope = explode( '|', $scope );
		if ( count( $scope ) !== 2 ) {
			$this->api_die(
				__( 'Scope is invalid.', 'hustle' ),
				$authorization_uri,
				$referer
			);
			return; // For unit tests.
		}

		$scope         = $scope[1];
		$access_token  = $token->get_access_token();
		$refresh_token = $token->get_refresh_token();
		if (
			$access_token &&
			$refresh_token &&
			$scope
		) {
			// Save the token using the main class method.
			$this->update_token( $token );

			if ( $referer ) {
				// Redirect to the referer with success status.
				$this->success( $referer );
			}
		} else {
			$this->api_die(
				__( 'Token is invalid.', 'hustle' ),
				$authorization_uri,
				$referer
			);
			return; // For unit tests.
		}
	}

	/**
	 * Save token.
	 *
	 * @param Hustle_Auth_Token $token Token.
	 * @return void
	 */
	protected function update_token( $token ) {
		if ( ! $token instanceof Hustle_Auth_Token ) {
			return;
		}

		$option = array(
			'access_token'    => $token->get_access_token(),
			'refresh_token'   => $token->get_refresh_token(),
			'expiration_time' => $token->get_expiration_time(),
			'scope'           => $token->get_scope(),
		);
		update_option( self::OPTION_NAME, $option );
	}

	/**
	 * Redirect to success page.
	 *
	 * @param string $referer Referer URL.
	 * @return void
	 */
	protected function success( $referer ) {
		$nonce   = wp_create_nonce( 'hustle_provider_external_redirect' );
		$referer = add_query_arg(
			array(
				'provider' => Hustle_Infusion_Soft::SLUG,
				'status'   => 'success',
				'nonce'    => $nonce,
			),
			$referer
		);
		wp_safe_redirect( $referer );
		exit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the authorization URL for Infusionsoft OAuth.
	 *
	 * @param string $redirect_uri The redirect URI after authorization.
	 * @param string $state An optional state parameter to maintain state between the request and callback.
	 * @return string The authorization URL.
	 */
	public function get_auth_url( $redirect_uri, $state = '' ) {
		$params = array(
			'client_id'     => $this->get_api_key(),
			'response_type' => 'code',
			'scope'         => 'full',
			'redirect_uri'  => rawurlencode( $redirect_uri ),
			'state'         => $state,
		);

		return add_query_arg( $params, 'https://accounts.infusionsoft.com/app/oauth/authorize' );
	}

	/**
	 * Get API key (public key).
	 *
	 * @return string
	 */
	public function get_api_key() {
		return self::API_KEY;
	}

	/**
	 * Is authorized
	 *
	 * @return bool
	 */
	public function is_authorized() {
		$auth = $this->get_token();

		if (
			empty( $auth ) ||
			is_wp_error( $auth )
		) {
			return false;
		}

		if (
			empty( $auth->get_access_token() ) ||
			time() > $auth->get_expiration_time()
		) {
			// Token is missing or expired.
			return false;
		}

		return true;
	}

	/**
	 * Get saved token.
	 *
	 * @return Hustle_Auth_Token|null The saved token or null if not found.
	 */
	public function get_token() {
		$data = get_option( self::OPTION_NAME, array() );

		if ( empty( $data ) ) {
			// No token saved.
			return null;
		}

		$expiration = isset( $data['expiration_time'] ) ? (int) $data['expiration_time'] : 0;
		if ( $expiration && time() >= $expiration ) {
			// Token expired, try to refresh if refresh token is available.
			if ( ! empty( $data['refresh_token'] ) ) {
				return $this->refresh_access_token( $data['refresh_token'] );
			} else {
				return null;
			}
		}

		// Return the token instance.
		return Hustle_Auth_Token::from_array( $data );
	}

	/**
	 * Get the access token using the authorization code.
	 *
	 * @param string $code The authorization code received from Infusionsoft.
	 * @return Hustle_Auth_Token|WP_Error The access token response or an error.
	 */
	public function get_access_token( $code ) {
		$redirect_uri   = $this->get_redirect_uri();
		$token_response = $this->get_auth_token( $code, $redirect_uri );
		if ( is_wp_error( $token_response ) ) {
			return $token_response;
		}

		$status_code = wp_remote_retrieve_response_code( $token_response );
		if ( 200 !== $status_code ) {
			return new WP_Error( 'infusionsoft_oauth_error', __( 'Failed to retrieve access token from Infusionsoft.', 'hustle' ) );
		}

		$token = json_decode( wp_remote_retrieve_body( $token_response ), true );
		if ( isset( $token['error'] ) ) {
			return new WP_Error( 'infusionsoft_oauth_error', $token['error_description'] );
		}

		return Hustle_Auth_Token::from_array( $token );
	}

	/**
	 * Request the access token from WPMU DEV Infusionsoft handler using the authorization code.
	 *
	 * @param string $code The authorization code.
	 * @param string $redirect_uri The redirect URI used in the authorization request.
	 * @return array|WP_Error The response containing the access token or an error.
	 */
	protected function get_auth_token( $code, $redirect_uri ) {
		$args = array(
			'redirect_uri' => rawurlencode( $redirect_uri ),
			'grant_type'   => 'authorization_code',
			'state'        => $this->prepare_state_param(),
			'action'       => 'get_access_token',
			'code'         => $code,
		);

		$url      = $this->get_redirect_uri( $args );
		$response = wp_remote_get( $url );

		return $response;
	}

	/**
	 * Refresh the access token using the refresh token.
	 *
	 * @param string $refresh_token The refresh token.
	 * @return Hustle_Auth_Token|WP_Error The response containing the new access token or an error.
	 */
	public function refresh_access_token( $refresh_token ) {
		$args = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refresh_token,
			'action'        => 'refresh_access_token',
			'client_id'     => self::CLIENT_ID,
			'state'         => $this->prepare_state_param(),
		);

		$url      = $this->get_redirect_uri( $args );
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error( 'infusionsoft_oauth_error', __( 'Failed to refresh access token from Infusionsoft.', 'hustle' ) );
		}

		$token = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $token['error'] ) ) {
			return new WP_Error( 'infusionsoft_oauth_error', $token['error_description'] );
		}

		return Hustle_Auth_Token::from_array( $token );
	}

	/**
	 * Compose redirect_uri to use on request argument.
	 * The redirect uri must be constant and should not be change per request.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	protected function get_redirect_uri( $args = array() ) {
		$params = wp_parse_args(
			$args,
			array(
				'action'    => 'authorize',
				'provider'  => 'infusionsoft',
				'client_id' => self::CLIENT_ID,
			)
		);

		$redirect_uri = $this->get_remote_api_url();
		return add_query_arg( $params, $redirect_uri );
	}

	/**
	 * Generates authorization URL
	 *
	 * @param int    $module_id Module ID.
	 * @param bool   $log_referrer Log referrer.
	 * @param string $page Page.
	 * @return string
	 */
	public function get_authorization_uri( $module_id = 0, $log_referrer = true, $page = 'hustle_embedded' ) {
		if ( $log_referrer ) {

			$params = array(
				'page'   => $page,
				'action' => 'external-redirect',
				'slug'   => 'infusionsoft',
				'nonce'  => wp_create_nonce( 'hustle_provider_external_redirect' ),
			);

			if ( ! empty( $module_id ) ) {
				$params['id']      = $module_id;
				$params['section'] = 'integrations';
			}
			$referer = add_query_arg( $params, admin_url( 'admin.php' ) );

			update_option( self::REFERER, $referer );
			update_option( self::CURRENTPAGE, $page );
		}

		$state    = $this->prepare_state_param();
		$auth_url = $this->get_auth_url( $this->get_redirect_uri(), $state );

		return $auth_url;
	}

	/**
	 * Remove all wp options
	 *
	 * @return void
	 */
	public function remove_wp_options() {
		delete_option( self::OPTION_NAME );
		delete_option( self::REFERER );
		delete_option( self::CURRENTPAGE );
	}
}
