<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class for interacting with the Constant Contact API using OAuth 2.0 PKCE
 *
 * @package Hustle
 */

if ( class_exists( 'Hustle_ConstantContact_Api' ) ) {

	/**
	 * Class for interacting with the Constant Contact API using OAuth 2.0 PKCE
	 */
	class Hustle_ConstantContact_Api_V2 extends Opt_In_WPMUDEV_API implements Hustle_ConstantContact_Api_Interface {
		private const APIKEY = '039ad0c4-76a3-423e-9f56-4d0506234d48';

		// Random client ID we use to verify our calls.
		const CLIENT_ID = 'q9Pl7KVr-T4jyf5XU-GTMtgDfO-tmRj52pP';

		const REFERER     = 'hustle_constantcontact_referer';
		const CURRENTPAGE = 'hustle_constantcontact_current_page';

		/**
		 * OAuth instance
		 *
		 * @var Hustle_ConstantContact_OAuth
		 */
		private $oauth;

		/**
		 * Auth token
		 *
		 * @var string
		 */
		private $option_token_name = 'hustle_opt-in-constant_contact-tokenv2';

		/**
		 * Is error
		 *
		 * @var bool
		 */
		public $is_error = false;

		/**
		 * Error message
		 *
		 * @var string
		 */
		public $error_message;

		/**
		 * Sending
		 *
		 * @var boolean
		 */
		public $sending = false;

		/**
		 * API Client
		 *
		 * @var Hustle_Contact_API_V3_Client
		 */
		protected $api_client;


		/**
		 * Hustle_ConstantContact_Api constructor.
		 */
		public function __construct() {
			$this->api_client = new Hustle_Contact_API_V3_Client();
			$this->init();
		}

		/**
		 * Initialize instance.
		 */
		public function init() {
			$this->create_oauth();
			$token = $this->get_token( 'access_token' );

			if ( $token ) {
				$this->api_client->set_access_token( $token );
			}
		}

		/**
		 * Create OAuth instance
		 */
		protected function create_oauth() {
			$this->oauth = new Hustle_ConstantContact_OAuth( $this->get_api_key() );
		}

		/**
		 * Helper function to listen to request callback sent from WPMUDEV
		 */
		public function process_callback_request() {
			if ( $this->validate_callback_request( 'constantcontact' ) ) {
				$code   = filter_input( INPUT_GET, 'code', FILTER_SANITIZE_SPECIAL_CHARS );
				$status = 'error';

				// Get the referer page that sent the request.
				$referer      = get_option( self::REFERER );
				$current_page = get_option( self::CURRENTPAGE );
				if ( $code ) {
					if ( $this->get_access_token( $code ) ) {
						if ( ! empty( $referer ) ) {
							$status = 'success';
						}
					}

					if ( ! empty( $referer ) ) {
						$referer = add_query_arg( 'status', $status, $referer );
						wp_safe_redirect( $referer );
						exit;
					}

					// Allow retry but don't log referrer.
					$authorization_uri = $this->get_authorization_uri( false, false, $current_page );

					$this->api_die( __( 'Constant Contact integration failed!', 'hustle' ), $authorization_uri, $referer );
				}
			}
		}

		/**
		 * Get Access token
		 *
		 * @param string $code Code.
		 */
		public function get_access_token( $code ) {
			try {
				$access_token = $this->oauth->get_access_token( $code, $this->get_redirect_uri() );
				$this->update_auth_token( $access_token );
			} catch ( \Exception $e ) {
				return false;
			}

			return true;
		}

		/**
		 * Get API key
		 *
		 * @return string
		 */
		protected function get_api_key() {
			return self::APIKEY;
		}

		/**
		 * Get authorization token
		 *
		 * @return array|false
		 */
		public function get_auth_token() {
			$token_data = get_option( $this->option_token_name );

			if ( ! $token_data ) {
				// Token data is not available.
				return false;
			}

			if ( ! empty( $token_data['expiration_time'] ) ) {
				$expiration_time = intval( $token_data['expiration_time'] );

				// Check if the token has expired.
				if ( $expiration_time < time() ) {
					if ( ! empty( $token_data['refresh_token'] ) ) {
						// Try to refresh the token.
						try {
							$new_token_data = $this->oauth->refresh_access_token( $token_data['refresh_token'] );
							if ( $new_token_data ) {
								// Save token data.
								$this->update_auth_token( $new_token_data );
								return $new_token_data;
							}
						} catch ( \Exception $e ) {
							return false;
						}
					}
					return false;
				}
			}

			return $token_data;
		}


		/**
		 * Generates authorization URL
		 *
		 * @param int    $module_id Module ID.
		 * @param bool   $log_referrer Log referrer.
		 * @param string $page Page.
		 *
		 * @return string
		 * @throws \Exception Throws an exception if the site is not connected to WPMU Dev HUB.
		 */
		public function get_authorization_uri( $module_id = 0, $log_referrer = true, $page = 'hustle_embedded' ) {

			if ( $log_referrer ) {
				$this->log_referer( $page, $module_id );
			}
			$state = $this->prepare_state_param();

			return $this->oauth->get_authorization_url( $this->get_redirect_uri(), $state );
		}

		/**
		 * Store $referer to use after retrieving the access token
		 *
		 * @param string $page Page.
		 * @param int    $module_id Module ID.
		 */
		protected function log_referer( $page, $module_id ) {
			$params = array(
				'page'   => $page,
				'action' => 'external-redirect',
				'slug'   => 'constantcontact',
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

		/**
		 * Compose redirect_uri to use on request argument.
		 * The redirect uri must be constant and should not be change per request.
		 *
		 * @return string
		 */
		public function get_redirect_uri() {
			return $this->redirect_uri(
				'constantcontact',
				'authorize',
				array(
					'client_id' => self::CLIENT_ID,
				)
			);
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

			return add_query_arg( $params, self::get_remote_api_url() );
		}

		/**
		 * Get token value by key.
		 *
		 * @param string $key Key.
		 * @return bool|mixed
		 */
		public function get_token( $key ) {
			$auth = $this->get_auth_token();

			if ( ! empty( $auth ) && ! empty( $auth[ $key ] ) ) {
				return $auth[ $key ];
			}

			return false;
		}

		/**
		 * Update token data.
		 *
		 * @param array $token Token.
		 * @return void
		 */
		public function update_auth_token( array $token ) {
			update_option( $this->option_token_name, $token );
		}

		/**
		 * Get current account information.
		 *
		 * @return object
		 *
		 * @throws Exception Throws an exception if the API request fails.
		 */
		public function get_account_info() {
			return $this->api_client->get_account_info();
		}

		/**
		 * Retrieve contact lists from ConstantContact.
		 *
		 * @return array
		 *
		 * @throws Exception Throws an exception if the API request fails.
		 */
		public function get_contact_lists() {
			return $this->api_client->get_contact_lists();
		}

		/**
		 * Retrieve contact from ConstantContact.
		 *
		 * @param string $email Email.
		 * @return false|object
		 */
		public function get_contact( $email ) {
			$email = sanitize_email( $email );
			if ( $email ) {
				return $this->api_client->get_contact( $email );
			}
			return false;
		}

		/**
		 * Check if contact exists in certain list.
		 *
		 * @param object $contact Contact object.
		 * @param string $list_id List ID.
		 * @return bool
		 */
		public function contact_exist( $contact, $list_id ) {
			if ( ! $contact || ! $list_id ) {
				return false;
			}

			if ( ! $contact instanceof Hustle_ConstantContact_Contact ) {
				return false;
			}

			if ( in_array( $list_id, $contact->list_memberships, true ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Subscribe contact.
		 *
		 * @param string $email Email.
		 * @param string $first_name First name.
		 * @param string $last_name Last name.
		 * @param string $target_list Constant contact list.
		 * @param array  $custom_fields Custom fields.
		 * @return mixed
		 *
		 * @throws Exception Throws error when subscription fails.
		 */
		public function subscribe( $email, $first_name, $last_name, $target_list, $custom_fields = array() ) {
			if ( ! $email || ! $target_list ) {
				return false;
			}

			$contact = $this->get_contact( $email );
			if ( $contact ) {
				// Contact exists, update subscription.
				return $this->update_subscription( $contact, $first_name, $last_name, $target_list, $custom_fields );
			}

			// Create new contact.
			$contact                   = new Hustle_ConstantContact_Contact();
			$contact->email_address    = new Hustle_ConstantContact_EmailAddress(
				array(
					'address'            => $email,
					'permission_to_send' => 'explicit',
				)
			);
			$contact->first_name       = $first_name;
			$contact->last_name        = $last_name;
			$contact->list_memberships = array( $target_list );

			$available_custom_fields = $this->api_client->get_custom_fields();
			if ( ! empty( $available_custom_fields ) ) {
				// Map available Constant Contact custom fields to Hustle fields.
				$fields = array();
				foreach ( $available_custom_fields as $field ) {
					if ( isset( $custom_fields[ $field->name ] ) ) {
						$field->value = $custom_fields[ $field->name ];
						$fields[]     = $field;
					}
				}

				$contact->custom_fields = $fields;
			}

			// Add new contact.
			return $this->api_client->create_contact( $contact );
		}

		/**
		 * Remove wp_options rows.
		 *
		 * @return void
		 */
		public function remove_wp_options() {
			delete_option( $this->option_token_name );
			delete_option( self::REFERER );
			delete_option( self::CURRENTPAGE );
			$this->oauth->reset_auth_keys();
		}

		/**
		 * Update Subscription.
		 *
		 * @param object $contact Contact.
		 * @param string $first_name First name.
		 * @param string $last_name Last name.
		 * @param string $target_list Constant contact list.
		 * @param array  $custom_fields Custom fields.
		 * @return mixed
		 *
		 * @throws Exception Throws error when subscription update fails.
		 */
		public function update_subscription( $contact, $first_name, $last_name, $target_list, $custom_fields = array() ) {
			if ( ! $contact || ! $target_list ) {
				return false;
			}

			if ( ! $contact instanceof Hustle_ConstantContact_Contact ) {
				return false;
			}

			$contact->first_name = $first_name;
			$contact->last_name  = $last_name;

			$available_custom_fields = $this->api_client->get_custom_fields();
			if ( ! empty( $available_custom_fields ) ) {
				// Map available Constant Contact custom fields to Hustle fields.
				$fields = array();
				foreach ( $available_custom_fields as $field ) {
					if ( isset( $custom_fields[ $field->name ] ) ) {
						$field->value = $custom_fields[ $field->name ];
						$fields[]     = $field;
					}
				}

				$contact->custom_fields = $fields;
			}

			if ( ! in_array( $target_list, $contact->list_memberships, true ) ) {
				// Add the contact to the new list.
				$contact->list_memberships[] = $target_list;
			}

			return $this->api_client->update_contact( $contact );
		}

		/**
		 * Delete subscriber from the list.
		 *
		 * @param string $list_id List ID.
		 * @param string $email Email.
		 * @return bool
		 *
		 * @throws Exception Throws error when deletion fails.
		 */
		public function delete_email( $list_id, $email ) {
			$contact = $this->get_contact( $email );
			if ( ! $contact ) {
				return false;
			}

			// Search for the list ID in the contact's list memberships.
			$list_idx = array_search( $list_id, $contact->list_memberships, true );
			if ( false !== $list_idx ) {
				// Remove the list ID from the contact's list memberships.
				unset( $contact->list_memberships[ $list_idx ] );
			}

			$this->api_client->update_contact( $contact );
			return true;
		}
	}
}
