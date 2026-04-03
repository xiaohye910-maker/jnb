<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_Infusion_Soft class
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_Infusion_Soft' ) ) :

	include_once 'hustle-infusion-soft-oauth.php';
	include_once 'interface-opt-in-infusionsoft-connection.php';
	include_once 'hustle-infusion-soft-request-method.php';
	include_once 'hustle-infusion-soft-rest-api.php';
	include_once __DIR__ . '/model/hustle-infusion-soft-contact.php';
	include_once __DIR__ . '/model/hustle-infusion-soft-custom-field.php';


	/**
	 * Class Hustle_Infusion_Soft
	 */
	class Hustle_Infusion_Soft extends Hustle_Provider_Abstract {

		const SLUG          = 'infusionsoft';
		const OAUTH_ACTION  = 'infusionsoft_oauth';
		const KEAP_APPS_URL = 'https://keys.developer.keap.com/my-apps';

		const CLIENT_ID     = 'inc_opt_infusionsoft_clientid';
		const CLIENT_SECRET = 'inc_opt_infusionsoft_clientsecret';

		/**
		 * Api
		 *
		 * @var Opt_In_Infusionsoft_Connection
		 */
		protected static $api;
		/**
		 * Errors
		 *
		 * @var array
		 */
		protected static $errors;

		/**
		 * Provider Instance
		 *
		 * @since 3.0.5
		 *
		 * @var self|null
		 */
		protected static $instance = null;

		/**
		 * OAuth
		 *
		 * @var Hustle_Infusion_Soft_OAuth
		 */
		protected $auth;

		/**
		 * Slug
		 *
		 * @since 3.0.5
		 * @var string
		 */
		protected $slug = 'infusionsoft';

		/**
		 * Is multi on global
		 *
		 * @var boolean
		 */
		protected $is_multi_on_global = false;

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
		protected $title = 'Keap';

		/**
		 * Class name of form settings
		 *
		 * @var string
		 */
		protected $form_settings = 'Hustle_Infusion_Soft_Form_Settings';

		/**
		 * Class name of form hooks
		 *
		 * @since 4.0
		 * @var string
		 */
		protected $form_hooks = 'Hustle_InfusionSoft_Form_Hooks';

		/**
		 * Array of options which should exist for confirming that settings are completed
		 *
		 * @since 4.0
		 * @var array
		 */
		protected $completion_options = array( 'account_name' );

		/**
		 * Provider constructor.
		 */
		public function __construct() {
			$this->icon_2x = plugin_dir_url( __FILE__ ) . 'images/icon.png';
			$this->logo_2x = plugin_dir_url( __FILE__ ) . 'images/logo.png';
			$this->get_oauth();
		}

		/**
		 * Get OAuth instance
		 *
		 * @return Hustle_Infusion_Soft_OAuth
		 */
		public function get_oauth() {
			if ( ! $this->auth ) {
				if ( Opt_In_Utils::get_hub_api_key() ) {
					$this->auth = new Hustle_Infusion_Soft_OAuth();
				} else {
					$settings   = $this->get_settings_values();
					$this->auth = new Hustle_Infusion_Soft_OAuth_Non_Hub();
					// Set the keys if they exist.
					if ( ! is_array( $settings ) ) {
						$settings = array();
					}
					if ( ! empty( $settings['api_key'] ) ) {
						$this->auth->set_api_key( $settings['api_key'] );
					}
					if ( ! empty( $settings['private_key'] ) ) {
						$this->auth->set_secret_key( $settings['private_key'] );
					}
				}
			}

			return $this->auth;
		}

		/**
		 * Get token
		 *
		 * @return string
		 */
		public function get_access_token() {
			$token = $this->get_oauth()->get_token();
			if ( is_wp_error( $token ) || is_null( $token ) ) {
				return '';
			}

			return $token->get_access_token();
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
		 * Returns a cached api
		 *
		 * @param string $api_key Api key.
		 * @return Opt_In_Infusionsoft_Api
		 */
		public static function api( $api_key ) {

			if ( empty( self::$api ) ) {
				try {
					self::$errors = array();
					self::$api    = new Opt_In_Infusionsoft_Rest_Api( $api_key );
				} catch ( Exception $e ) {
					self::$errors = array( 'api_error' => $e );
				}
			}

			return self::$api;
		}

		/**
		 * Get the wizard callbacks for the global settings.
		 *
		 * @since 4.0
		 *
		 * @return array
		 */
		public function settings_wizards() {
			if ( Opt_In_Utils::get_hub_api_key() ) {
				// Hub user integration.
				return array(
					array(
						'callback'     => array( $this, 'build_user_authorization_form' ),
						'is_completed' => array( $this, 'is_connected' ),
					),
				);
			} else {
				// Non hub user integration.
				return array(
					array(
						'callback'     => array( $this, 'build_non_hub_user_integration_settings' ),
						'is_completed' => array( $this, 'is_connected' ),
					),
				);
			}
		}

		/**
		 * Build authorization form for integration.
		 *
		 * @param array $submitted_data Submitted data.
		 * @param bool  $is_submit      Is submit.
		 * @param int   $module_id      Module ID.
		 * @return array Response array.
		 */
		public function build_user_authorization_form( $submitted_data, $is_submit, $module_id ) {
			$oauth = $this->get_oauth();
			$page  = $oauth->get_authorization_uri( $module_id, true, Hustle_Data::INTEGRATIONS_PAGE );

			$description  = '';
			$is_connected = $this->is_connected();

			if ( $is_connected ) {
				if ( $this->migration_required() ) {
					/* translators: %s is a link to Keap developer keys page */
					$description = sprintf( __( 'Re-authenticate your Hustle → Keap integration using OAuth2. Enter your Keap access tokens to update your integration. Get your API keys %s.', 'hustle' ), '<a href="' . esc_url( self::KEAP_APPS_URL ) . '" target="_blank">' . esc_html__( 'here', 'hustle' ) . '</a>' );
				}
				// If the user is already authorized, we can skip the connection step.
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
			} else {
				/* translators: Plugin name */
				$description = sprintf( __( 'Connect the Keap integration by authenticating it using the button below. Note that you’ll be taken to the Keap website to grant access to %s and then redirected back.', 'hustle' ), Opt_In_Utils::get_plugin_name() );

				$buttons = array(
					'connect' => array(
						'markup' => Hustle_Provider_Utils::get_provider_button_markup(
							__( 'Connect', 'hustle' ),
							'sui-button-blue sui-button-right',
							'connect',
							true,
							false,
							$page
						),
					),
				);
			}

			if ( $this->migration_required() ) {
				$title = __( 'Migrate Keap', 'hustle' );
			} else {
				$title = __( 'Connect Keap', 'hustle' );
			}

			$step_html = Hustle_Provider_Utils::get_integration_modal_title_markup(
				$title,
				$description
			);

			if ( $is_connected ) {
				// Show success message if already connected.
				$step_html .= Hustle_Provider_Utils::get_html_for_options(
					array(
						array(
							'type'  => 'notice',
							'icon'  => 'check-tick',
							'value' => esc_html__( 'Hustle is successfully integrated with Keap. You can start sending data to this integration.', 'hustle' ),
							'class' => 'sui-notice-success',
						),
					)
				);
			}

			return array(
				'html'       => $step_html,
				'buttons'    => $buttons,
				'has_errors' => false,
			);
		}

		/**
		 * Check if API key is configured for non-hub users
		 *
		 * @since 4.0
		 * @return bool
		 */
		public function is_api_key_configured() {
			$settings = $this->get_settings_values();

			return ! empty( $settings['api_key'] ) && ! empty( $settings['private_key'] );
		}

		/**
		 * Build authorization form for non-hub user integration.
		 *
		 * @param array $submitted_data Submitted data.
		 * @param bool  $is_submit      Is submit.
		 * @param int   $module_id      Module ID.
		 * @return array Response array.
		 */
		public function build_non_hub_user_integration_settings( $submitted_data, $is_submit, $module_id ) {

			if ( $this->is_connected() ) {
				return $this->user_account_info();
			}

			$has_errors = false;
			$html       = '';
			$buttons    = array();

			if ( $is_submit ) {
				$public_key = isset( $submitted_data['public_key'] ) ? sanitize_text_field( $submitted_data['public_key'] ) : '';
				$secret_key = isset( $submitted_data['secret_key'] ) ? sanitize_text_field( $submitted_data['secret_key'] ) : '';

				if ( empty( $public_key ) || empty( $secret_key ) ) {
					$has_errors = true;
				} else {
					// Save the keys.
					$settings = $this->get_settings_values();
					if ( ! is_array( $settings ) ) {
						$settings = array();
					}
					$settings['api_key']     = $public_key;
					$settings['private_key'] = $secret_key;

					$this->save_settings_values( $settings, $module_id );

					$this->get_oauth()->set_api_key( $public_key );
					$this->get_oauth()->set_secret_key( $secret_key );
				}

				$page = $this->
					get_oauth()->
					get_authorization_uri( $module_id, true, Hustle_Data::INTEGRATIONS_PAGE );

				return array(
					'redirect' => $page,
					'is_close' => false,
				);
			} else {
				$settings   = $this->get_settings_values();
				$public_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
				$secret_key = isset( $settings['private_key'] ) ? $settings['private_key'] : '';
			}

			if ( $this->migration_required() ) {
				$this->get_oauth()->set_api_key( $public_key );
				$this->get_oauth()->set_secret_key( $secret_key );

				$page = $this->
					get_oauth()->
					get_authorization_uri( $module_id, true, Hustle_Data::INTEGRATIONS_PAGE );

				return array(
					'redirect' => $page,
					'is_close' => false,
				);
			}

			ob_start();
			?>
			<div class="hustle-wizard-content">
				<?php
				$keap_link = 'https://keys.developer.keap.com/my-apps';
				echo wp_kses_post(
					Hustle_Provider_Utils::get_integration_modal_title_markup(
						__( 'Keap integration', 'hustle' ),
						/* translators: %s is a link to Keap developer keys page */
						sprintf( __( 'Authenticate your Hustle → Keap integration using OAuth2. Enter your Keap access tokens to update your integration. Get your API keys %s.', 'hustle' ), '<a href="' . esc_url( $keap_link ) . '" target="_blank">' . esc_html__( 'here', 'hustle' ) . '</a>' )
					)
				);
				?>
				
				<div class="sui-form-field">
					<label class="sui-label"><?php esc_html_e( 'Client ID', 'hustle' ); ?></label>
					<input
						name="public_key"
						placeholder="<?php esc_attr_e( 'Enter your client ID', 'hustle' ); ?>"
						value="<?php echo esc_attr( $public_key ); ?>"
						class="sui-form-control" />
				</div>

				<div class="sui-form-field">
					<label class="sui-label"><?php esc_html_e( 'Secret key', 'hustle' ); ?></label>
					<input
						name="secret_key"
						type="password"
						placeholder="<?php esc_attr_e( 'Enter your secret key', 'hustle' ); ?>"
						value="<?php echo esc_attr( $secret_key ); ?>"
						class="sui-form-control" />
				</div>
			</div>
			<?php
			$html = ob_get_clean();

			$buttons = array(
				'authorize' => array(
					'markup' => Hustle_Provider_Utils::get_provider_button_markup(
						__( 'Authorize', 'hustle' ),
						'sui-button-blue sui-button-center',
						'next',
						true,
						false,
						''
					),
				),
			);

			return array(
				'html'       => $html,
				'buttons'    => $buttons,
				'has_errors' => $has_errors,
			);
		}

		/**
		 * User account info after connection.
		 *
		 * @return array Response array.
		 */
		private function user_account_info() {
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

			$step_html = Hustle_Provider_Utils::get_integration_modal_title_markup(
				__( 'Connect Keap', 'hustle' ),
			);

			// Show success message if already connected.
			$step_html .= Hustle_Provider_Utils::get_html_for_options(
				array(
					array(
						'type'  => 'notice',
						'icon'  => 'check-tick',
						'value' => esc_html__( 'Hustle is successfully integrated with Keap. You can start sending data to this integration.', 'hustle' ),
						'class' => 'sui-notice-success',
					),
				)
			);

			return array(
				'html'       => $step_html,
				'buttons'    => $buttons,
				'has_errors' => false,
			);
		}

		/**
		 * Safe tokens after authorization complete.
		 */
		public function process_external_redirect() {

			$auth   = $this->get_oauth();
			$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'success' === $status && $auth->is_authorized() ) {
				$providers = $this->get_providers();

				if ( ! $providers->addon_is_active( $this->slug ) ) {
					// Activate the provider if not active.
					$activated = $providers->activate_addon( $this->slug );

					if ( $activated ) {
						// Success message for first time connection.
						$response = Opt_In_Utils::build_notification(
							'success',
							/* translators: integration name */
							sprintf( esc_html__( '%s successfully connected.', 'hustle' ), '<strong>' . esc_html( $this->title ) . '</strong>' )
						);
					} else {
						$response = Opt_In_Utils::build_notification(
							'error',
							/* translators: integration name */
							sprintf( esc_html__( 'Failed to activate %s. Please try again.', 'hustle' ), esc_html( $this->title ) )
						);
					}
				} else {
					$response = Opt_In_Utils::build_notification(
						'success',
						/* translators: integration name */
						sprintf( esc_html__( '%s successfully reconnected.', 'hustle' ), '<strong>' . esc_html( $this->title ) . '</strong>' )
					);
				}

				update_option( $this->get_version_options_name(), $this->version );
			} else {
				$response = Opt_In_Utils::build_notification(
					'error',
					/* translators: integration name */
					sprintf( esc_html__( 'Authentication failed! Please check your %s credentials and try again.', 'hustle' ), esc_html( $this->title ) )
				);
			}

			return $response;
		}

		/**
		 * Get providers
		 *
		 * @return Hustle_Providers
		 */
		protected function get_providers() {
			return Hustle_Providers::get_instance();
		}

		/**
		 * Check if settings are completed
		 *
		 * @param string $multi_id Multi ID.
		 * @return bool
		 */
		protected function settings_are_completed( $multi_id = '' ) {
			$token = $this->get_oauth()
				->get_token();

			if ( is_wp_error( $token ) || ! $token instanceof Hustle_Auth_Token ) {
				// Error retrieving token or invalid token.
				return false;
			}

			// Check if token is valid.
			return $token->get_access_token() &&
				$token->get_refresh_token() &&
				( time() < $token->get_expiration_time() );
		}

		/**
		 * Check if migration is needed
		 *
		 * @return bool
		 */
		private function migration_required() {
			$api_version = $this->get_installed_version();
			if ( empty( $api_version ) ) {
				return false;
			}

			$settings = $this->get_settings_values();

			return ! empty( $settings['api_key'] ) &&
				! empty( $settings['private_key'] ) &&
				version_compare(
					$api_version,
					'2.0',
					'<'
				);
		}

		/**
		 * Get 3.0 provider mappings
		 *
		 * @return type
		 */
		public function get_30_provider_mappings() {
			return array(
				'api_key'      => 'api_key',
				'account_name' => 'account_name',
			);
		}

		/**
		 * Configure migrated api keys
		 *
		 * @param array $data Data.
		 * @return void
		 */
		public function configure_migrated_api_keys( $data ) {
			if ( ! is_array( $data ) ) {
				return;
			}

			// Activate the provider if not active.
			if (
				$this->is_active() ||
				Hustle_Providers::get_instance()->activate_addon( $this->slug )
			) {

				$api_key    = isset( $data['api_key'] ) ? sanitize_text_field( $data['api_key'] ) : '';
				$secret_key = isset( $data['private_key'] ) ? sanitize_text_field( $data['private_key'] ) : '';
				// Save the keys.
				$this->update_api_keys( $api_key, $secret_key );
			}
		}

		/**
		 * Update api key and secret key.
		 *
		 * @param string $api_key Api key.
		 * @param string $secret_key Secret key.
		 * @return void
		 */
		public function update_api_keys( $api_key, $secret_key ) {
			$settings = $this->get_settings_values();
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			$settings['api_key']     = $api_key;
			$settings['private_key'] = $secret_key;

			$this->save_settings_values( $settings );
		}

		/**
		 * Clean up resources.
		 */
		public function remove_wp_options() {
			$auth = $this->get_oauth();

			if ( $auth ) {
				$auth->remove_wp_options();
			}
		}
	}

endif;
