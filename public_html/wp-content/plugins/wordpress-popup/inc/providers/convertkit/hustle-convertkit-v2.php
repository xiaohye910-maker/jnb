<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_ConvertKit_V2 class
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConvertKit_V2' ) ) :

	include_once 'hustle-convertkit-api-v2.php';

	/**
	 * ConvertKit V2 Email Integration (API Key only)
	 *
	 * @class Hustle_ConvertKit_V2
	 * @version 7.8.11
	 **/
	class Hustle_ConvertKit_V2 extends Hustle_ConvertKit {

		/**
		 * Api
		 *
		 * @var Hustle_ConvertKit_API_V2
		 */
		protected static $api;

		/**
		 * Version
		 *
		 * @since 7.8.11
		 * @var string
		 */
		protected $version = '2.0';

		/**
		 * Array of options which should exist for confirming that settings are completed
		 *
		 * @since 7.8.11
		 * @var array
		 */
		protected $completion_options = array( 'api_key' );

		/**
		 * Provider constructor.
		 */
		public function __construct() {
			$this->icon_2x = plugin_dir_url( __FILE__ ) . 'images/icon.png';
			$this->logo_2x = plugin_dir_url( __FILE__ ) . 'images/logo.jpg';
		}

		/**
		 * Configure the API key settings. Global settings.
		 *
		 * @since 7.8.11
		 *
		 * @param array $submitted_data Submitted data.
		 * @return array
		 */
		public function configure_api_key( $submitted_data ) {
			$has_errors      = false;
			$default_data    = array(
				'api_key' => '',
				'name'    => '',
			);
			$current_data    = $this->get_current_data( $default_data, $submitted_data );
			$is_submit       = isset( $submitted_data['api_key'] );
			$global_multi_id = $this->get_global_multi_id( $submitted_data );

			$api_key_valid = true;

			if ( $is_submit ) {

				$api_key_valid     = ! empty( $current_data['api_key'] );
				$api_key_validated = $api_key_valid && $this->validate_credentials( $submitted_data['api_key'] );

				if ( ! $api_key_validated ) {
					$error_message = $this->provider_connection_falied();
					$api_key_valid = false;
					$has_errors    = true;
				}

				if ( ! $has_errors ) {
					$settings_to_save = array(
						'api_key' => $current_data['api_key'],
						'name'    => $current_data['name'],
						'version' => $this->version,
					);
					// If not active, activate it.
					// TODO: Wrap this in a friendlier method.
					if ( Hustle_Provider_Utils::is_provider_active( $this->slug )
							|| Hustle_Providers::get_instance()->activate_addon( $this->slug ) ) {
						$this->save_multi_settings_values( $global_multi_id, $settings_to_save );
					} else {
						$error_message = __( "Provider couldn't be activated.", 'hustle' );
						$has_errors    = true;
					}
				}

				if ( ! $has_errors ) {

					return array(
						'html'         => Hustle_Provider_Utils::get_integration_modal_title_markup( __( 'Kit Added', 'hustle' ), __( 'You can now go to your pop-ups, slide-ins and embeds and assign them to this integration', 'hustle' ) ),
						'buttons'      => array(
							'close' => array(
								'markup' => Hustle_Provider_Utils::get_provider_button_markup( __( 'Close', 'hustle' ), 'sui-button-ghost', 'close' ),
							),
						),
						'redirect'     => false,
						'has_errors'   => false,
						'notification' => array(
							'type' => 'success',
							'text' => '<strong>' . $this->get_title() . '</strong> ' . esc_html__( 'Successfully connected', 'hustle' ),
						),
					);

				}
			}

			$options = array(
				array(
					'type'     => 'wrapper',
					'class'    => $api_key_valid ? '' : 'sui-form-field-error',
					'elements' => array(
						'label'   => array(
							'type'  => 'label',
							'for'   => 'api_key',
							'value' => __( 'API Key', 'hustle' ),
						),
						'api_key' => array(
							'type'        => 'text',
							'name'        => 'api_key',
							'value'       => $current_data['api_key'],
							'placeholder' => __( 'Enter API Key', 'hustle' ),
							'id'          => 'api_key',
							'icon'        => 'key',
						),
						'error'   => array(
							'type'  => 'error',
							'class' => $api_key_valid ? 'sui-hidden' : '',
							'value' => __( 'Please enter a valid ConvertKit API key', 'hustle' ),
						),
					),
				),
				array(
					'type'     => 'wrapper',
					'style'    => 'margin-bottom: 0;',
					'elements' => array(
						'label'   => array(
							'type'  => 'label',
							'for'   => 'instance-name-input',
							'value' => __( 'Identifier', 'hustle' ),
						),
						'name'    => array(
							'type'        => 'text',
							'name'        => 'name',
							'value'       => $current_data['name'],
							'placeholder' => __( 'E.g. Business Account', 'hustle' ),
							'id'          => 'instance-name-input',
						),
						'message' => array(
							'type'  => 'description',
							'value' => __( 'Helps distinguish your integrations if you have connected multiple accounts for this integration.', 'hustle' ),
						),
					),
				),
			);

			if ( $has_errors ) {
				$error_notice = array(
					'type'  => 'notice',
					'icon'  => 'info',
					'class' => 'sui-notice-error',
					'value' => esc_html( $error_message ),
				);
				array_unshift( $options, $error_notice );
			}

			$step_html = Hustle_Provider_Utils::get_integration_modal_title_markup(
				__( 'Configure Kit', 'hustle' ),
				sprintf(
					/* translators: 1. opening 'a' tag to Kit account, 2. closing 'a' tag */
					__( 'Log in to your %1$sKit%2$s account to get your API Key.', 'hustle' ),
					'<a href="https://app.kit.com/account_settings/developer_settings" target="_blank">',
					'</a>'
				)
			);
			$step_html .= Hustle_Provider_Utils::get_html_for_options( $options );

			$is_edit = $this->settings_are_completed( $global_multi_id );
			if ( $is_edit ) {
				$buttons = array(
					'disconnect' => array(
						'markup' => Hustle_Provider_Utils::get_provider_button_markup(
							__( 'Disconnect', 'hustle' ),
							'sui-button-ghost',
							'disconnect',
							true
						),
					),
					'save'       => array(
						'markup' => Hustle_Provider_Utils::get_provider_button_markup(
							__( 'Save', 'hustle' ),
							'',
							'connect',
							true
						),
					),
				);
			} else {
				$buttons = array(
					'connect' => array(
						'markup' => Hustle_Provider_Utils::get_provider_button_markup(
							__( 'Connect', 'hustle' ),
							'sui-button-right',
							'connect',
							true
						),
					),
				);

			}

			$response = array(
				'html'       => $step_html,
				'buttons'    => $buttons,
				'has_errors' => $has_errors,
			);

			return $response;
		}

		/**
		 * Validate the provided API key.
		 *
		 * @since 7.8.11
		 *
		 * @param string $api_key Api key.
		 * @return bool
		 */
		private function validate_credentials( $api_key ) {
			if ( empty( $api_key ) ) {
				return false;
			}

			try {
				// Check if API key is valid.
				$api   = self::api( $api_key );
				$forms = $api->get_forms(); // check API key.

				if ( is_wp_error( $forms ) ) {
					Hustle_Provider_Utils::maybe_log( __METHOD__, __( 'Invalid Kit API key.', 'hustle' ) );
					return false;
				}
			} catch ( Exception $e ) {
				Hustle_Provider_Utils::maybe_log( __METHOD__, $e->getMessage() );
				return false;
			}

			return true;
		}

		/**
		 * Get api
		 *
		 * @param string $api_key Api key.
		 * @param string $api_secret Api secret.
		 * @return Hustle_ConvertKit_API_Interface
		 */
		public static function api( $api_key, $api_secret = '' ) {

			if ( empty( self::$api ) ) {
				try {
					self::$api    = new Hustle_ConvertKit_API_V2( $api_key );
					self::$errors = array();
				} catch ( Exception $e ) {
					self::$errors = array( 'api_error' => $e );
				}
			}
			return self::$api;
		}

		/**
		 * Creates necessary custom fields for the form
		 *
		 * @param string $global_multi_id Global multi ID.
		 * @param array  $fields Fields.
		 * @return array|mixed|object|WP_Error
		 */
		public function maybe_create_custom_fields( $global_multi_id, array $fields ) {
			$api_key = $this->get_setting( 'api_key', '', $global_multi_id );

			// check if already existing.
			$custom_fields = self::api( $api_key )->get_form_custom_fields();
			$proceed       = true;
			foreach ( $custom_fields as $custom_field ) {
				if ( isset( $fields[ $custom_field->key ] ) ) {
					unset( $fields[ $custom_field->key ] );
				}
			}
			// create necessary fields
			// Note: we don't delete fields here, let the user do it on ConvertKit app.convertkit.com .
			$api = self::api( $api_key );
			foreach ( $fields as $key => $field ) {
				$add_custom_field = $api->create_custom_fields(
					array(
						'label' => $field['label'],
					)
				);
				if ( is_wp_error( $add_custom_field ) ) {
					$proceed = false;
					break;
				}
			}

			return $proceed;
		}

		/**
		 * Get 3.0 provider mappings
		 *
		 * @return array
		 */
		public function get_30_provider_mappings() {
			return array(
				'api_key' => 'api_key',
			);
		}
	}

endif;
