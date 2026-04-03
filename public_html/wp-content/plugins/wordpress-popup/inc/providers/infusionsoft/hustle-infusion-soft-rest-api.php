<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Opt_In_Infusionsoft_Rest_Api class
 *
 * @package Hustle
 */

if ( class_exists( 'Opt_In_Infusionsoft_Rest_Api' ) ) {
	return;
}

/**
 * Class Opt_In_Infusionsoft_Rest_Api
 */
class Opt_In_Infusionsoft_Rest_Api implements Opt_In_Infusionsoft_Connection {

	private const BASE_URL = 'https://api.infusionsoft.com/crm/rest/v1/';

	/**
	 * Api key
	 *
	 * @var string $personal_token
	 */
	private $personal_token;

	/**
	 * Store the values getting from custom field request.
	 *
	 * @var array
	 */
	public $custom_fields_with_data_type;

	/**
	 * Constructor.
	 *
	 * @param string $personal_token Read information here https://developer.infusionsoft.com/pat-and-sak/.
	 */
	public function __construct( $personal_token ) {
		$this->personal_token = $personal_token;
	}

	/**
	 * Make KEAP API request
	 *
	 * @param string $path url Absolute url.
	 * @param array  $body Body params.
	 * @param int    $method Request method.
	 *
	 * @return array|WP_Error
	 */
	protected function make_api_request( $path, $body = array(), $method = Opt_In_Infusionsoft_Request_Method::HTTP_GET ) {
		if ( empty( $path ) ) {
			return array();
		}
		$url = self::BASE_URL . $path;

		return $this->make_request( $url, $body, $method );
	}

	/**
	 * Make request
	 *
	 * @param string $url Absolute url.
	 * @param array  $body Body params.
	 * @param int    $method Request method.
	 */
	protected function make_request( $url, $body = array(), $method = Opt_In_Infusionsoft_Request_Method::HTTP_GET ) {
		if ( empty( $url ) ) {
			return array();
		}

		$headers = $this->prepare_headers();

		switch ( $method ) {
			case Opt_In_Infusionsoft_Request_Method::HTTP_GET:
				$url = add_query_arg( $body, $url );
				$res = wp_remote_get(
					$url,
					array(
						'headers' => $headers,
					)
				);
				break;
			case Opt_In_Infusionsoft_Request_Method::HTTP_POST:
				$res = wp_remote_post(
					$url,
					array(
						'headers' => $headers,
						'body'    => wp_json_encode( $body ),
					)
				);
				break;
			case Opt_In_Infusionsoft_Request_Method::HTTP_PUT:
				$res = wp_remote_request(
					$url,
					array(
						'method'  => 'PUT',
						'headers' => $headers,
						'body'    => wp_json_encode( $body ),
					)
				);
				break;
			case Opt_In_Infusionsoft_Request_Method::HTTP_PATCH:
				$res = wp_remote_request(
					$url,
					array(
						'method'  => 'PATCH',
						'headers' => $headers,
						'body'    => wp_json_encode( $body ),
					)
				);
				break;
			case Opt_In_Infusionsoft_Request_Method::HTTP_DELETE:
				$res = wp_remote_request(
					$url,
					array(
						'method'  => 'DELETE',
						'headers' => $headers,
					)
				);
				break;
		}

		$utils                     = Hustle_Provider_Utils::get_instance();
		$utils->last_url_request   = $url;
		$utils->last_data_received = $res;
		$utils->last_data_sent     = urlencode_deep( $body );

		$code    = wp_remote_retrieve_response_code( $res );
		$message = wp_remote_retrieve_response_message( $res );
		$err     = new WP_Error();

		if ( $code < 204 ) {

			$response_body = wp_remote_retrieve_body( $res );
			if ( is_wp_error( $response_body ) ) {
				$err->add( 'Invalid_app_name', __( 'Invalid app name, please check app name and try again', 'hustle' ) );
				return $err;
			}

			$data = json_decode( $response_body, true );
			if ( is_null( $data ) ) {
				$err->add( 'Bad_response', __( 'Remote server returned invalid response', 'hustle' ) );
				return $err;
			}

			return $data;
		}

		$err->add( $code, $message );
		return $err;
	}

	/**
	 * Builds headers array for new request
	 *
	 * @return array
	 */
	private function prepare_headers() {
		return array(
			'Content-Type'   => 'application/json',
			'Accept-Charset' => 'UTF-8',
			'Authorization'  => 'Bearer ' . $this->personal_token,
		);
	}

	/**
	 * Get the built-in custom fields at Keap account.
	 *
	 * @return string[]|WP_Error
	 **/
	public function get_builtin_custom_field_names() {
		$custom_fields = array(
			'Anniversary',
			'AssistantName',
			'AssistantPhone',
			'Birthday',
			'City',
			'City2',
			'City3',
			'Company',
			'CompanyID',
			'ContactNotes',
			'ContactType',
			'Country',
			'Country2',
			'Country3',
			'Email',
			'EmailAddress2',
			'EmailAddress3',
			'Fax1',
			'Fax1Type',
			'Fax2',
			'Tax2Type',
			'FirstName',
			'JobTitle',
			'Language',
			'LastName',
			'MiddleName',
			'Nickname',
			'Password',
			'Phone1',
			'Phone1Ext',
			'Phone1Type',
			'Phone2',
			'Phone2Ext',
			'Phone2Type',
			'PostalCode',
			'PostalCode2',
			'ReferralCode',
			'SpouseName',
			'State',
			'State2',
			'StreetAddress1',
			'StreetAddress2',
			'Suffix',
			'TimeZone',
			'Title',
			'Website',
			'ZipFour1',
			'ZipFour2',
		);

		return $custom_fields;
	}

	/**
	 * Get the custom fields at Keap account.
	 *
	 * @return Hustle_Infusion_Soft_Custom_Field[]|WP_Error
	 **/
	public function get_custom_fields() {
		$data = $this->make_api_request( 'contacts/model' );
		if ( is_wp_error( $data ) ) {
			return array();
		}

		if ( empty( $data['custom_fields'] ) ) {
			return array();
		}

		// Prepare the list of custom fields.
		$this->custom_fields_with_data_type = array();
		$custom_fields                      = array();

		foreach ( $data['custom_fields'] as $field ) {
			if ( ! isset( $field['field_name'], $field['id'], $field['field_type'] ) ) {
				continue;
			}
			$item = new Hustle_Infusion_Soft_Custom_Field(
				$field['field_name'],
				$field['field_type'],
				$field['label']
			);
			$item->set_field_id( (int) $field['id'] );

			$name                   = $field['field_name'];
			$custom_fields[ $name ] = $item;

			$this->custom_fields_with_data_type[ $name ] = $field['field_type'];
		}

		return $custom_fields;
	}
	/**
	 * Create custom field at Keap account.
	 *
	 * @param string $name Name of the custom field.
	 * @param string $type Type of the custom field.
	 * @return int|WP_Error The ID of the created custom field or WP_Error on failure.
	 **/
	public function add_custom_field( $name, $type = 'Text' ) {

		$field_data = array(
			'label'      => $name,
			'field_type' => $type,
		);

		$data = $this->make_api_request( 'contacts/model/customFields', $field_data, Opt_In_Infusionsoft_Request_Method::HTTP_POST );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return (int) $data['id'] ?? 0; // Return custom field ID or 0 if not created.
	}
	/**
	 * Add new contact to infusionsoft and return contact ID on success or WP_Error.
	 *
	 * @param array $contact            An array of contact details.
	 **/
	public function add_contact( $contact ) {
		if ( empty( $contact ) || ! is_array( $contact ) ) {
			return new WP_Error( 'invalid_contact_data', __( 'Contact data must be an array.', 'hustle' ) );
		}

		$email = $contact['email_addresses'][0]['email'] ?? '';
		if ( empty( $email ) ) {
			return new WP_Error( 'missing_email', __( 'Email address is required to add a contact.', 'hustle' ) );
		}

		$data = $this->make_api_request( 'contacts', $contact, Opt_In_Infusionsoft_Request_Method::HTTP_POST );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return (int) $data['id'] ?? 0; // Return contact ID or 0 if not created.
	}
	/**
	 * Updates an existing contact.
	 *
	 * @param int   $contact_id Contact ID.
	 * @param array $contact Array of contact details to be updated.
	 * @return integer|WP_Error Contact ID if everything went well, WP_Error otherwise.
	 */
	public function update_contact( $contact_id, $contact ) {
		if ( empty( $contact_id ) || ! is_numeric( $contact_id ) || $contact_id <= 0 ) {
			return new WP_Error( 'invalid_contact_id', __( 'Invalid contact ID provided.', 'hustle' ) );
		}

		$data = $this->make_api_request( 'contacts/' . $contact_id, $contact, Opt_In_Infusionsoft_Request_Method::HTTP_PATCH );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return (int) $data['id'] ?? 0; // Return contact ID or 0 if not updated.
	}
	/**
	 * Delete subscriber from the list
	 *
	 * @param string $contact_id Contact ID.
	 * @param string $list_id List ID.
	 *
	 * @return bool
	 */
	public function remove_contact_from_list( $contact_id, $list_id ) {
		$url = sprintf( 'contacts/%s/tags/%s', $contact_id, $list_id );
		if ( ! $url ) {
			return false;
		}

		$data = $this->make_api_request( $url, array(), Opt_In_Infusionsoft_Request_Method::HTTP_DELETE );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return true;
	}
	/**
	 * Email exists?
	 *
	 * @param string $email Email.
	 * @return int Contact Id if exists, 0 if not exists, WP_Error on error.
	 */
	public function email_exist( $email ) {
		$data = $this->make_api_request( 'contacts', array( 'email' => $email ), Opt_In_Infusionsoft_Request_Method::HTTP_GET );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( empty( $data['contacts'] ) || ! is_array( $data['contacts'] ) ) {
			return 0; // No contacts found.
		}
		return (int) $data['contacts'][0]['id'] ?? 0; // Return contact ID or 0 if not found.
	}
	/**
	 * Adds contact with $contact_id to group with $group_id
	 *
	 * @param int $contact_id Contact ID.
	 * @param int $tag_id Tag ID.
	 * @return bool|WP_Error
	 */
	public function add_tag_to_contact( $contact_id, $tag_id ) {
		$body = array(
			'tagIds' => array( $tag_id ),
		);

		$data = $this->make_api_request( 'contacts/' . $contact_id . '/tags', $body, Opt_In_Infusionsoft_Request_Method::HTTP_POST );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return true;
	}
	/**
	 * Get lists
	 *
	 * @return type
	 */
	public function get_lists() {
		$data = $this->make_api_request( 'tags', array(), Opt_In_Infusionsoft_Request_Method::HTTP_GET );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		if ( empty( $data['tags'] ) ) {
			return array();
		}

		// Prepare the list of groups.
		// Assuming 'tags' contains the list of groups.
		$groups = array();
		foreach ( $data['tags'] as $tag ) {
			if ( isset( $tag['name'] ) && isset( $tag['id'] ) ) {
				$groups[ $tag['id'] ] = $tag['name'];
			}
		}
		return $groups;
	}
}
