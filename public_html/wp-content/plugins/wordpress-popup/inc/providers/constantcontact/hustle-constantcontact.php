<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_ConstantContact class
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact' ) ) :

	/**
	 * Class Hustle_ConstantContact
	 */
	class Hustle_ConstantContact extends Hustle_Provider_Abstract {

		const SLUG = 'constantcontact';

		/**
		 * Authentication Flows
		 */
		const AUTH_FLOW_LEGACY      = 'legacy';
		const AUTH_FLOW_PKCE        = 'pkce';
		const AUTH_FLOW_PKCE_CUSTOM = 'pkce_custom';

		/**
		 * Current Authentication Flow
		 *
		 * @var string
		 */
		private $auth_flow;

		/**
		 * Errors
		 *
		 * @var array
		 */
		protected static $errors;

		/**
		 * Constant Contact Provider Instance
		 *
		 * @since 3.0.5
		 *
		 * @var self|null
		 */
		protected static $instance = null;

		/**
		 * PHP min version
		 *
		 * @since 3.0.5
		 * @var string
		 */
		public static $min_php_version = '5.3';

		/**
		 * Slug
		 *
		 * @since 3.0.5
		 * @var string
		 */
		protected $slug = 'constantcontact';

		/**
		 * Version
		 *
		 * @since 3.0.5
		 * @var string
		 */
		protected $version = '2.0';

		/**
		 * Class
		 *
		 * @since 3.0.5
		 * @var string
		 */
		protected $class = __CLASS__;

		/**
		 * Title
		 *
		 * @since 3.0.5
		 * @var string
		 */
		protected $title = 'Constant Contact';

		/**
		 * Is multi on global
		 *
		 * @since 4.0
		 * @var boolean
		 */
		protected $is_multi_on_global = false;

		/**
		 * Class name of form settings
		 *
		 * @var string
		 */
		protected $form_settings = 'Hustle_ConstantContact_Form_Settings';

		/**
		 * Class name of form hooks
		 *
		 * @since 4.0
		 * @var string
		 */
		protected $form_hooks = 'Hustle_ConstantContact_Form_Hooks';

		/**
		 * API interface
		 *
		 * @var Hustle_ConstantContact_Api_Interface
		 */
		protected $api_interface;

		/**
		 * Hustle_ConstantContact constructor.
		 */
		public function __construct() {
			$this->icon_2x = plugin_dir_url( __FILE__ ) . 'images/icon-v2.png';
			$this->logo_2x = plugin_dir_url( __FILE__ ) . 'images/logo-v2.png';

			$this->init_api();
			add_action( 'init', array( $this, 'maybe_handle_oauth_response' ), 10 );
		}

		/**
		 * Initialize the API interface.
		 */
		private function init_api() {
			$this->api_interface = $this->api();
		}

		/**
		 * Maybe process OAuth response.
		 * This is called on 'init' action.
		 */
		public function maybe_handle_oauth_response() {
			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS );
			// Check if this is an OAuth callback. Only PKCE flows use this method.
			if ( 'authorize' === $action ) {
				$auth_flow = self::get_migrated_auth_flow( self::is_hub_site_connected() );
				$api       = $this->get_configured_api( $auth_flow );

				$api->process_callback_request();
			}
		}

		/**
		 * Get the migrated authentication flow based on Hub connection status.
		 *
		 * @param bool $hub_connected Is site connected to Hub.
		 * @return string
		 */
		private static function get_migrated_auth_flow( $hub_connected ) {
			if ( $hub_connected ) {
				return self::AUTH_FLOW_PKCE;
			} else {
				return self::AUTH_FLOW_PKCE_CUSTOM;
			}
		}

		/**
		 * Get Instance
		 *
		 * @return self|null
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Check if the settings are completed
		 *
		 * @since 4.0
		 * @param string $multi_id Multi ID.
		 * @return boolean
		 */
		protected function settings_are_completed( $multi_id = '' ) {
			return (bool) $this->api()->get_token( 'access_token' );
		}

		/**
		 * Get api
		 *
		 * @return \WP_Error|Hustle_ConstantContact_Api_Interface
		 */
		public function api() {
			$this->init_auth_flow();
			return $this->get_configured_api( $this->auth_flow );
		}

		/**
		 * Get configured api
		 *
		 * @param string $auth_flow Authentication flow.
		 * @return \WP_Error|Hustle_ConstantContact_Api_Interface
		 */
		private function get_configured_api( $auth_flow ) {
			$api_instance = self::static_api( $auth_flow );
			if (
				$api_instance instanceof Hustle_ConstantContact_API_V2_Non_Hub &&
				$this->is_api_key_configured()
			) {
				// Set the API key for non-hub users if it is configured.
				$settings = $this->get_settings_values();
				$api_instance->set_api_key( $settings['api_key'] );

				return $api_instance;
			}

			return $api_instance;
		}

		/**
		 * Initialize the authentication flow.
		 */
		private function init_auth_flow() {
			$legacy_token = get_option( 'hustle_opt-in-constant_contact-token', false );
			if ( $legacy_token ) {
				// Legacy users will continue using legacy flow.
				$this->auth_flow = self::AUTH_FLOW_LEGACY;
			} elseif ( self::is_hub_site_connected() ) {
				// Hub connected users will use new PKCE flow.
				$this->auth_flow = self::AUTH_FLOW_PKCE;
			} else {
				// Non-Hub users will use new custom PKCE flow.
				$this->auth_flow = self::AUTH_FLOW_PKCE_CUSTOM;
			}
		}

		/**
		 * Get the current authentication flow.
		 *
		 * @return string
		 */
		public function get_current_auth_flow() {
			return $this->auth_flow;
		}

		/**
		 * Get api by static method
		 *
		 * @param string $auth_flow Authentication flow. legacy, pkce, pkce_custom.
		 *
		 * @return \WP_Error|\Hustle_ConstantContact_Api_Interface
		 */
		public static function static_api( $auth_flow = '' ) {
			if ( ! class_exists( 'Hustle_ConstantContact_Api' ) ) {
				require_once 'hustle-constantcontact-api.php';
			}

			// Hub users using custom PKCE flow.
			if ( self::AUTH_FLOW_PKCE === $auth_flow ) {
				// Load new version of API.
				if ( ! class_exists( 'Hustle_ConstantContact_Api_V2' ) ) {
					require_once 'hustle-constantcontact-api-v2.php';
				}

				if ( self::is_hub_site_connected() ) {
					return new Hustle_ConstantContact_API_V2();
				} else {
					return new WP_Error( 'error', __( 'API Class could not be initialized. Site is not connected to Hub.', 'hustle' ) );
				}
			}

			// New custom PKCE flow that doesn't require Hub connection.
			if ( self::AUTH_FLOW_PKCE_CUSTOM === $auth_flow ) {
				// Load new version of API.
				if ( ! class_exists( 'Hustle_ConstantContact_Api_V2' ) ) {
					require_once 'hustle-constantcontact-api-v2.php';
				}

				if ( ! class_exists( 'Hustle_ConstantContact_API_V2_Non_Hub' ) ) {
					require_once 'hustle-constantcontact-api-v2-non-hub.php';
				}

				return new Hustle_ConstantContact_API_V2_Non_Hub();
			}

			if ( class_exists( 'Hustle_ConstantContact_Api' ) ) {
				// Fall back to legacy API.
				return new Hustle_ConstantContact_Api();
			} else {
				return new WP_Error( 'error', __( 'API Class could not be initialized', 'hustle' ) );
			}
		}

		/**
		 * Check if the site is connected to Hub
		 *
		 * @return boolean
		 */
		private static function is_hub_site_connected() {
			return ! empty( Opt_In_Utils::get_hub_api_key() );
		}

		/**
		 * Get the wizard callbacks for the global settings.
		 *
		 * @since 4.0
		 *
		 * @return array
		 */
		public function settings_wizards() {

			$is_migrating = $this->migration_required() &&
				$this->is_api_key_configured();

			if ( $is_migrating ) {
				// Migrating users will be required to authorize only.
				return array(
					array(
						'callback'     => array( $this, 'authorization_form' ),
						'is_completed' => array( $this, 'is_connected' ),
					),
				);
			}

			if ( $this->is_connected() ) {
				return array(
					array(
						'callback' => array( $this, 'account_info' ),
					),
				);
			}

			if ( self::is_hub_site_connected() ) {
				// Hub connected users will only need to authorize.
				return array(
					array(
						'callback' => array( $this, 'authorization_form' ),
					),
				);
			}

			// Non-Hub users using custom PKCE flow will need to configure API key and then authorize.
			return array(
				array(
					'callback'     => array( $this, 'configure_api_key' ),
					'is_completed' => array( $this, 'is_api_key_configured' ),
				),
				array(
					'callback' => array( $this, 'authorization_form' ),
				),
			);
		}

		/**
		 * Authorize form for Hub connected sites.
		 *
		 * @return array
		 */
		public function authorization_form() {
			// Site is connected to Hub.
			// Hub users will use WPMU DEV API authentication.
			/* translators: %s: plugin name */
			$description = sprintf( esc_html__( 'Connect the Constant Contact integration by authenticating it using the button below. Note that you’ll be taken to the Constant Contact website to grant access to %s and then redirected back.', 'hustle' ), Opt_In_Utils::get_plugin_name() );
			if ( $this->migration_required() ) {
				$temp_auth_flow = self::get_migrated_auth_flow( self::is_hub_site_connected() );
				$api            = $this->get_configured_api( $temp_auth_flow );
			} else {
				$api = $this->api();
			}

			$auth_url = $api->get_authorization_uri( 0, true, Hustle_Data::INTEGRATIONS_PAGE );

			// Hub connected
			// Use button that redirects to WPMU DEV authentication flow.
			$buttons = array(
				'auth' => array(
					'markup' => Hustle_Provider_Utils::get_provider_button_markup(
						__( 'Authenticate', 'hustle' ),
						'sui-button-center',
						'',
						true,
						false,
						isset( $auth_url ) ? $auth_url : ''
					),
				),
			);

			$step_html = Hustle_Provider_Utils::get_integration_modal_title_markup( __( 'Connect Constant Contact', 'hustle' ), $description );

			$response = array(
				'html'    => $step_html,
				'buttons' => $buttons,
			);

			return $response;
		}

		/**
		 * Display account info for connected users.
		 *
		 * @return array
		 */
		public function account_info() {
			$description = __( 'You are already connected to Constant Contact. You can disconnect your Constant Contact Integration (if you need to) using the button below.', 'hustle' );

			$buttons = array(
				'disconnect' => array(
					'markup' => Hustle_Provider_Utils::get_provider_button_markup(
						__( 'Disconnect', 'hustle' ),
						'sui-button-ghost sui-button-center',
						'disconnect',
						true
					),
				),
			);

			$step_html = Hustle_Provider_Utils::get_integration_modal_title_markup( __( 'Connect Constant Contact', 'hustle' ), $description );

			$account_details = $this->get_settings_values();
			$account_email   = isset( $account_details['email'] ) ? $account_details['email'] : $this->save_account_email();

			$step_html .= Hustle_Provider_Utils::get_html_for_options(
				array(
					array(
						'type'  => 'notice',
						'icon'  => 'info',
						/* translators: email associated to the account */
						'value' => sprintf( esc_html__( 'You are connected to %s', 'hustle' ), '<strong>' . esc_html( $account_email ) . '</strong>' ),
						'class' => 'sui-notice-success',
					),
				)
			);

			$response = array(
				'html'    => $step_html,
				'buttons' => $buttons,
			);

			return $response;
		}

		/**
		 * Check if the API key is configured
		 */
		public function is_api_key_configured() {
			$settings = $this->get_settings_values();
			return ! empty( $settings['api_key'] );
		}


		/**
		 * Configure the API key settings. Global settings.
		 *
		 * @since 4.0
		 *
		 * @param array $submitted_data Submitted data.
		 * @param bool  $is_submit Is submit.
		 * @param int   $module_id Module ID.
		 * @return array
		 */
		public function configure_api_key( $submitted_data, $is_submit, $module_id ) {
			$api_key = '';
			if ( $is_submit ) {
				$api_key = isset( $submitted_data['api_key'] ) ? sanitize_text_field( $submitted_data['api_key'] ) : '';
				$this->update_api_key( $api_key );

				// Reset auth flow so that the new key is used.
				$this->auth_flow = self::AUTH_FLOW_PKCE_CUSTOM;
				// Reinitialize the API interface.
				$this->init_api();
			} elseif ( $this->is_api_key_configured() ) {
				$settings = $this->get_settings_values();
				$api_key  = $settings['api_key'];
			}

			// WPMU Dev Dashboard is active but site is not connected.
			// Non-Hub users will use own PKCE API keys.
			$description = sprintf(
				/* translators: %s: link to Constant Contact developer account */
				__(
					'Please enter your Constant Contact PKCE API Key. You can retrieve it from your Constant Contact %s',
					'hustle'
				),
				'<a href="https://developer.constantcontact.com/" target="_blank" rel="noopener noreferrer">' . __( 'developer account', 'hustle' ) . '</a>'
			);

			// Non-Hub connected.
			// Use button that redirects to Constant Contact authentication flow.
			$buttons = array(
				'auth' => array(
					'markup' => Hustle_Provider_Utils::get_provider_button_markup(
						__( 'Next', 'hustle' ),
						'sui-button-right',
						'next',
						true,
						false,
						''
					),
				),
			);

			$step_html = Hustle_Provider_Utils::get_integration_modal_title_markup( __( 'Connect Constant Contact', 'hustle' ), $description );

			// Add readonly redirect URI field using options array instead of raw HTML.
			$api_instance   = $this->get_configured_api( self::AUTH_FLOW_PKCE_CUSTOM );
			$redirect_uri   = method_exists( $api_instance, 'get_redirect_uri' ) ? $api_instance->get_redirect_uri() : '';
			$redirect_field = array(
				'type'     => 'wrapper',
				'elements' => array(
					'label'    => array(
						'type'  => 'label',
						'for'   => 'constantcontact_redirect_uri',
						'value' => __( 'Redirect URI', 'hustle' ),
					),
					'readonly' => array(
						'name'  => 'constantcontact_redirect_uri',
						'id'    => 'constantcontact_redirect_uri',
						'type'  => 'readonly',
						'value' => esc_url( $redirect_uri ),
					),
					'desc'     => array(
						'type'  => 'description',
						'value' => __( 'Add this URL as the redirect / callback URL in your Constant Contact app settings.', 'hustle' ),
					),
				),
			);

			$step_html .= Hustle_Provider_Utils::get_html_for_options( array( $redirect_field ) );

			// Wrap the API key field in a wrapper and change its label to "PKCE key".
			$option = array(
				'type'     => 'wrapper',
				'class'    => '',
				'elements' => array(
					'label'   => array(
						'type'  => 'label',
						'for'   => 'api_key',
						'value' => __( 'PKCE key', 'hustle' ),
					),
					'api_key' => array(
						'type'        => 'text',
						'name'        => 'api_key',
						'placeholder' => 'e.g. 123abc456def789ghi',
						'value'       => esc_attr( $api_key ),
						'required'    => true,
						'id'          => 'api_key',
					),
				),
			);

			$step_html .= Hustle_Provider_Utils::get_html_for_options( array( $option ) );

			$response = array(
				'html'    => $step_html,
				'buttons' => $buttons,
			);

			return $response;
		}

		/**
		 * Check if migration is needed.
		 *
		 * @return boolean
		 */
		public function migration_required() {
			$api_version = $this->get_installed_version();
			if ( empty( $api_version ) ) {
				return false;
			}

			$settings = $this->get_settings_values();

			return ! empty( $settings['email'] ) &&
				version_compare( $api_version, '2.0', '<' );
		}

		/**
		 * Update the API key
		 *
		 * @param string $api_key The API key.
		 */
		public function update_api_key( $api_key ) {
			$settings = $this->get_settings_values();

			$settings['api_key'] = sanitize_text_field( $api_key );
			$this->save_settings_values( $settings );
		}

		/**
		 * Get the current account's email.
		 * If not stored already, store it.
		 *
		 * @since 4.0.2
		 *
		 * @return string
		 */
		private function save_account_email() {

			try {
				$account_details = $this->get_settings_values();
				$account_info    = $this->api_interface->get_account_info();
				$account_email   = $account_info->email;

				$account_details['email'] = $account_email;

				$this->save_settings_values( $account_details );

			} catch ( Exception $e ) {
				$account_email = __( 'The associated email could not be retrieved', 'hustle' );
			}

			return $account_email;
		}

		/**
		 * Migrate 3.0
		 *
		 * @param object $module Module.
		 * @param object $old_module Old module.
		 * @return boolean
		 */
		public function migrate_30( $module, $old_module ) {
			$migrated = parent::migrate_30( $module, $old_module );
			if ( ! $migrated ) {
				return false;
			}

			/*
			 * Our regular migration would've saved the provider settings in a format that's incorrect for constant contact
			 *
			 * Let's fix that now.
			 */
			$module_provider_settings = $module->get_provider_settings( $this->get_slug() );
			if ( ! empty( $module_provider_settings ) ) {
				// At provider level don't store anything (at least not in the regular option).
				delete_option( $this->get_settings_options_name() );

				// selected_global_multi_id not needed at module level.
				unset( $module_provider_settings['selected_global_multi_id'] );
				$module->set_provider_settings( $this->get_slug(), $module_provider_settings );
			}

			return $migrated;
		}

		/**
		 * Process the request after coming from authentication.
		 *
		 * @since 4.0.2
		 * @return array
		 */
		public function process_external_redirect() {

			$response = array();

			$status = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS );

			$is_authorized = (bool) $this->api_interface->get_token( 'access_token' );

			// API Auth was successful.
			if ( 'success' === $status && $is_authorized ) {

				$is_migration = $this->migration_required();

				if ( ! $this->is_active() ) {

					$providers_instance = Hustle_Providers::get_instance();
					$activated          = $providers_instance->activate_addon( $this->slug );

					// Provider successfully activated.
					if ( $activated ) {

						$response = array(
							'action'  => 'notification',
							'status'  => 'success',
							'message' => /* translators: integration name */ sprintf( esc_html__( '%s successfully connected.', 'hustle' ), '<strong>' . esc_html( $this->title ) . '</strong>' ),
						);

						$this->save_account_email();

					} else { // Provider couldn't be activated.

						$response = array(
							'action'  => 'notification',
							'status'  => 'error',
							'message' => wp_kses_post( $providers_instance->get_last_error_message() ),
						);
					}
				} elseif ( $is_migration ) {
					$version = $this->get_installed_version();

					if ( version_compare( $version, '2.0', '<' ) ) {
						$this->migrate_30_configuration();
						$this->init_api();
					}

					$response = array(
						'action'  => 'notification',
						'status'  => 'success',
						'message' => /* translators: integration name */ sprintf( esc_html__( '%s integration successfully migrated to the v3.0 API version.', 'hustle' ), '<strong>' . esc_html( $this->title ) . '</strong>' ),
					);
				} else {

					$response = array(
						'action'  => 'notification',
						'status'  => 'info',
						'message' => /* translators: integration name */ sprintf( esc_html__( '%s is already connected.', 'hustle' ), '<strong>' . esc_html( $this->title ) . '</strong>' ),
					);
				}

				$this->update_version( $this->version );
			} else { // API Auth failed.

				$response = array(
					'action'  => 'notification',
					'status'  => 'error',
					/* translators: integration name */
					'message' => sprintf( esc_html__( 'Authentication failed! Please check your %s credentials and try again.', 'hustle' ), esc_html( $this->title ) ),
				);

			}

			return $response;
		}

		/**
		 * Migrate api configuration to CC version 3.0
		 *
		 * @since 7.8.10
		 */
		private function migrate_30_configuration() {
			// If we were migrating, save the new auth flow.
			// We need to save the new auth flow only after successful authentication.
			// If authentication failed, we will continue using the old auth flow.
			$auth_flow = self::get_migrated_auth_flow( self::is_hub_site_connected() );
			// Remove old settings.
			$this->static_api( self::AUTH_FLOW_LEGACY )->remove_wp_options();
			$api = $this->get_configured_api( $auth_flow );

			// Migrate contact lists.
			$this->migrate_30_contact_list( $api );
		}

		/**
		 * Migrate contact lists to Constant Contact 3.0
		 *
		 * @param Hustle_ConstantContact_Api_V2 $api API.
		 *
		 * @since 7.8.10
		 */
		private function migrate_30_contact_list( $api ) {
			$modules = Hustle_Module_Collection::get_active_providers_module( $this->get_slug() );
			if ( empty( $modules ) ) {
				return;
			}

			$lists = $api->get_contact_lists();

			foreach ( $modules as $module_id ) {
				$module = Hustle_Module_Model::get_module( $module_id );
				if ( ! $module ) {
					continue;
				}

				$module_list = $module->get_provider_settings( $this->get_slug() );
				if ( empty( $module_list ) ) {
					continue;
				}

				foreach ( $lists as $contact_list ) {
					if ( $contact_list instanceof Hustle_ConstantContact_ContactsList ) {
						if ( strpos( $module_list['list_name'], $contact_list->name ) !== false ) {
							// Update the list ID in the module settings.
							$module_list['list_id'] = $contact_list->id;
							$module->set_provider_settings( $this->get_slug(), $module_list );
							break;
						}
					}
				}
			}
		}

		/**
		 * Update version
		 *
		 * @param string $version Version.
		 */
		public function update_version( $version ) {
			$version_option_name = $this->get_version_options_name();
			update_option( $version_option_name, $version );
		}

		/**
		 * Get 3.0 provider mappings
		 *
		 * @return array
		 */
		public function get_30_provider_mappings() {
			return array();
		}

		/**
		 * Remove wp_options rows
		 */
		public function remove_wp_options() {
			$this->api_interface->remove_wp_options();
		}
	}
endif;
