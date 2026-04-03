<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_InfusionSoft_Form_Hooks class
 *
 * @package Hustle
 */

/**
 * Class Hustle_InfusionSoft_Form_Hooks
 * Define the form hooks that are used by Keap
 *
 * @since 4.0
 */
class Hustle_InfusionSoft_Form_Hooks extends Hustle_Provider_Form_Hooks_Abstract {

	private const CACHE_KEY = 'hustle_infusionsoft_custom_fields';

	/**
	 * Add Keap data to entry.
	 *
	 * @since 4.0
	 *
	 * @param array $submitted_data Submitted data.
	 *
	 * @return array
	 * @throws Exception Required fields are missed.
	 */
	public function add_entry_fields( $submitted_data ) {

		$module_id              = $this->module_id;
		$form_settings_instance = $this->form_settings_instance;

		$response = array(
			'is_sent'       => false,
			'description'   => '',
			'data_sent'     => '',
			'data_received' => '',
			'url_request'   => '',
		);

		/**
		 * Filter submitted form data to be processed
		 *
		 * @since 4.0
		 *
		 * @param array                                    $submitted_data
		 * @param int                                      $module_id                current module_id
		 * @param Hustle_InfusionSoft_Form_Settings        $form_settings_instance
		 */
		$submitted_data = apply_filters(
			'hustle_provider_infusionsoft_form_submitted_data',
			$submitted_data,
			$module_id,
			$form_settings_instance
		);

		$addon_setting_values = $form_settings_instance->get_form_settings_values();

		$tags = $addon_setting_values['list_name'];

		try {
			$addon   = $this->addon;
			$api     = $addon::api( $addon->get_access_token() );
			$message = __( 'Successfully added or updated member on Keap list', 'hustle' );

			if ( empty( $submitted_data['email'] ) ) {
				throw new Exception( __( 'Required Field "email" was not filled by the user.', 'hustle' ) );
			}

			$submitted_data = $this->check_legacy( $submitted_data );

			if ( ! class_exists( 'Opt_In_Infusionsoft_Contact' ) ) {
				require_once __DIR__ . '/model/hustle-infusion-soft-contact.php';
			}
			$contact = new Opt_In_Infusionsoft_Contact();

			if ( isset( $submitted_data['email'] ) ) {
				$contact->set_email( $submitted_data['email'] );
			}
			if ( isset( $submitted_data['first_name'] ) ) {
				$contact->set_first_name( $submitted_data['first_name'] );
			}
			if ( isset( $submitted_data['last_name'] ) ) {
				$contact->set_last_name( $submitted_data['last_name'] );
			}

			$module = Hustle_Module_Model::new_instance( $module_id );
			if ( is_wp_error( $module ) ) {
				throw new Exception( $module->get_error_message() );
			}

			$utils         = Hustle_Provider_Utils::get_instance();
			$custom_fields = wp_cache_get( self::CACHE_KEY, '', false );
			// If the custom fields are not cached, get them from the API.
			if ( false === $custom_fields ) {
				$custom_fields = $api->get_custom_fields();
				if ( is_wp_error( $custom_fields ) ) {
					throw new Exception( $custom_fields->get_error_message() );
				}

				// Cache the custom fields for 1 hour.
				wp_cache_set( self::CACHE_KEY, $custom_fields, '', 3600 );
			}

			// If there were errors when connecting to the api.
			if ( isset( $custom_fields->errors ) ) {

				$response = array(
					'is_sent'     => false,
					'description' => '',
				);

			} else { // If there weren't errors.

				$found_fields     = array();
				$not_added_fields = array();

				$unmatched_custom_fields_datatypes = $this->find_unmatched_custom_fields_datatypes( $submitted_data, $api->custom_fields_with_data_type );

				if ( ! class_exists( 'Hustle_Infusion_Soft_Custom_Field' ) ) {
					require_once __DIR__ . '/model/hustle-infusion-soft-custom-field.php';
				}

				$reserved_fields = $api->get_builtin_custom_field_names();
				$reserved_fields = array_merge( $reserved_fields, array_keys( $custom_fields ) );
				$reserved_fields = array_map( 'strtolower', $reserved_fields );

				foreach ( $submitted_data as $key => $value ) {

					// If the field is not in the form fields, use the key as the name.
					$name_from_key = str_replace( '_', '', $key );

					if ( isset( $custom_fields[ $name_from_key ] ) ) {
						$field = $custom_fields[ $name_from_key ];
						// If the field is already in the custom fields, update its value.
						$field->set_value( $value );

						$found_fields[ $key ] = $field;
					} else {
						$field = new Hustle_Infusion_Soft_Custom_Field( $name_from_key );
						$field->set_value( $value );

						// Create non-existing custom field.
						$found_fields[ $key ] = $field;
					}
				}

				// Add new custom fields.
				if ( ! empty( $found_fields ) ) {
					$purge_fields_cache = false;

					$form_fields = $module->get_form_fields();
					foreach ( $found_fields as $name => $value ) {
						// Create the field if it doesn't exist.
						if (
							empty( $value->get_field_id() ) &&
							! in_array( strtolower( $value->get_name() ), $reserved_fields, true )
						) {

							if ( isset( $form_fields[ $name ] ) ) {
								$value->set_field_type( $form_fields[ $name ]['type'] );
							}
							$added_field = $api->add_custom_field( $value->get_name(), $value->get_mapped_field_type() );

							if ( is_wp_error( $added_field ) || empty( $added_field ) ) {
								$not_added_fields[] = $name;
								// We coulnd't create the field - let not submit it.
								unset( $submitted_data[ $name ] );
								// Skip this field if it couldn't be added.
								continue;
							} else {
								$value->set_field_id( $added_field );
								$purge_fields_cache = true;
							}
						}

						$contact->add_field( $value );
					}

					if ( $purge_fields_cache ) {
						// Purge the cache for custom fields if any created.
						wp_cache_delete( self::CACHE_KEY );
					}
				}

				if ( ! empty( $not_added_fields ) ) {
					$message = __( "The contact was subscribed but these custom fields couldn't be added: ", 'hustle' ) . implode( ', ', $not_added_fields );
				}

				/**
				 * Fires before adding subscriber
				 *
				 * @since 4.0.2
				 *
				 * @param int    $module_id
				 * @param array  $submitted_data
				 * @param object $form_settings_instance
				 */
				do_action(
					'hustle_provider_infusionsoft_before_add_subscriber',
					$module_id,
					$submitted_data,
					$form_settings_instance
				);

				$contact_id = $api->email_exist( $contact->get_email() );

				if ( is_wp_error( $contact_id ) ) {
					throw new Exception( $contact_id->get_error_message() );
				} elseif ( $contact_id > 0 ) {
					$contact_id = $api->update_contact( $contact_id, $contact->get_contact_data_for_api() );
				} else {
					$contact_id = $api->add_contact( $contact->get_contact_data_for_api() );
				}

				/**
				 * Fires after adding subscriber
				 *
				 * @since 4.0.2
				 *
				 * @param int    $module_id
				 * @param array  $submitted_data
				 * @param mixed  $contact_id
				 * @param object $form_settings_instance
				 */
				do_action(
					'hustle_provider_infusionsoft_after_add_subscriber',
					$module_id,
					$submitted_data,
					$contact_id,
					$form_settings_instance
				);

				if ( is_wp_error( $contact_id ) ) {
					throw new Exception( $contact_id->get_error_message() );
				}

				$tag_id  = ! empty( $addon_setting_values['list_id'] ) ? (int) $addon_setting_values['list_id'] : null;
				$tag_res = $api->add_tag_to_contact( $contact_id, $tag_id );

				if ( is_wp_error( $tag_res ) ) {
					// The tag id isn't selected, its value type isn't the correct type, or other errors.
					throw new Exception( $tag_res->get_error_message() );

				} elseif ( '0' === $tag_res ) {
					// The contact was added but couldn't be tagged. Happens when the selected tag doesn't exist in IS, for example.
					throw new Exception( __( "The contact was subscribed but it couldn't be tagged. Please make sure the selected tag exists.", 'hustle' ) );
				}

				$is_sent = true;

				if ( $unmatched_custom_fields_datatypes ) {
					$is_sent = false;
					$message = __( "The contact was subscribed but these custom fields' might not have been saved: ", 'hustle' ) . implode( ', ', $unmatched_custom_fields_datatypes );
				}

				$response = array(
					'is_sent'       => $is_sent,
					'description'   => $message,
					'tags_names'    => $tags,
					'data_sent'     => $utils->get_last_data_sent(),
					'data_received' => $utils->get_last_data_received(),
					'url_request'   => $utils->get_last_url_request(),
				);

			}
		} catch ( Exception $e ) {
			$entry_fields = $this->exception( $e );
		}

		if ( ! isset( $entry_fields ) ) {
			$entry_fields = array(
				array(
					'name'  => 'status',
					'value' => $response,
				),
			);
		}

		return apply_filters(
			'hustle_provider_infusionsoft_entry_fields',
			$entry_fields,
			$module_id,
			$submitted_data,
			$form_settings_instance
		);
	}

	/**
	 * Unsubscribe
	 *
	 * @param string $email Email.
	 */
	public function unsubscribe( $email ) {
		$addon                  = $this->addon;
		$form_settings_instance = $this->form_settings_instance;
		$addon_setting_values   = $form_settings_instance->get_form_settings_values();
		$list_id                = $addon_setting_values['list_id'];

		try {
			$api = $addon::api( $addon->get_access_token() );

			$contact_id = $api->email_exist( $email );
			if ( 0 === $contact_id ) {
				// Not found, nothing to do.
				return;
			}

			$api->remove_contact_from_list( $contact_id, $list_id );
		} catch ( Exception $e ) {
			Opt_In_Utils::maybe_log( $addon->get_slug(), 'unsubscribtion is failed', $e->getMessage() );
		}
	}

	/**
	 * Check whether the email is already subscribed.
	 *
	 * @since 4.0
	 *
	 * @param array $submitted_data Submitted data.
	 * @param bool  $allow_subscribed Allow already subscribed.
	 * @return bool
	 */
	public function on_form_submit( $submitted_data, $allow_subscribed = true ) {

		$is_success             = true;
		$module_id              = $this->module_id;
		$form_settings_instance = $this->form_settings_instance;
		$addon                  = $this->addon;
		$addon_setting_values   = $form_settings_instance->get_form_settings_values();

		if ( empty( $submitted_data['email'] ) ) {
			return __( 'Required Field "email" was not filled by the user.', 'hustle' );
		}

		if ( ! $allow_subscribed ) {

			/**
			 * Filter submitted form data to be processed
			 *
			 * @since 4.0
			 *
			 * @param array                                    $submitted_data
			 * @param int                                      $module_id                current module_id
			 * @param Hustle_InfusionSoft_Form_Settings $form_settings_instance
			 */
			$submitted_data = apply_filters(
				'hustle_provider_infusionsoft_form_submitted_data_before_validation',
				$submitted_data,
				$module_id,
				$form_settings_instance
			);

			$api             = $addon::api( $addon->get_access_token() );
			$existing_member = $this->get_subscriber( $api, $submitted_data['email'] );

			if ( $existing_member ) {
				$is_success = self::ALREADY_SUBSCRIBED_ERROR;
			}
		}

		/**
		 * Return `true` if success, or **(string) error message** on fail
		 *
		 * @since 4.0
		 *
		 * @param bool                                     $is_success
		 * @param int                                      $module_id                current module_id
		 * @param array                                    $submitted_data
		 * @param Hustle_InfusionSoft_Form_Settings $form_settings_instance
		 */
		$is_success = apply_filters(
			'hustle_provider_infusionsoft_form_submitted_data_after_validation',
			$is_success,
			$module_id,
			$submitted_data,
			$form_settings_instance
		);

		// process filter.
		if ( true !== $is_success ) {
			// only update `submit_form_error_message` when not empty.
			if ( ! empty( $is_success ) ) {
				$this->submit_form_error_message = (string) $is_success;
			}
			return $is_success;
		}

		return true;
	}

	/**
	 * Get subscriber for providers
	 *
	 * This method is to be inherited
	 * And extended by child classes.
	 *
	 * Make use of the property `$subscriber`
	 * Method to omit double api calls
	 *
	 * @since 4.0.2
	 *
	 * @param   object $api Api.
	 * @param   mixed  $data Data.
	 * @return  mixed   array/object API response on queried subscriber
	 */
	protected function get_subscriber( $api, $data ) {

		if ( empty( $this->subscriber ) && ! isset( $this->subscriber[ md5( $data ) ] ) ) {
			$this->subscriber[ md5( $data ) ] = $api->email_exist( $data );
		}

		return $this->subscriber[ md5( $data ) ];
	}

	/**
	 * Check datatype of custom fields
	 *
	 * @param array $submitted_data Form data submitted by user.
	 * @param array $custom_fields Custom fields with their datatype.
	 *
	 * @return array
	 */
	private function find_unmatched_custom_fields_datatypes( $submitted_data, $custom_fields ) {
		$unmatched_fields = array();

		// 1  => phone. 15 => text. 16 => textarea. 18 => url. 19 => email.
		// 4 => percent. 7 => year. 11 => decimal number. 12 => whole number.
		$flexible_fields = array( 1, 15, 16, 18, 19, 4, 7, 11, 12 );

		foreach ( $submitted_data as $key => $value ) {
			if ( isset( $custom_fields[ $key ] ) && ! in_array( $custom_fields[ $key ], $flexible_fields, true ) ) {
				$unmatched_fields[] = $key;
			}
		}

		return $unmatched_fields;
	}
}
