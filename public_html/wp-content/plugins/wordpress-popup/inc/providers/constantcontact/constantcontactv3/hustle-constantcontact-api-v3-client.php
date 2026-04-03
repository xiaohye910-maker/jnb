<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class for interacting with the Constant Contact API v3
 *
 * @package Hustle
 */
class Hustle_Contact_API_V3_Client {
	const BASE_URL = 'https://api.cc.email/v3';

	/**
	 * API Access token
	 *
	 * @var string
	 */
	private $access_token = '';

	/**
	 * Set API Access token
	 *
	 * @param string $access_token API Access token.
	 */
	public function set_access_token( $access_token ) {
		$this->access_token = $access_token;
	}

	/**
	 * Get current account information.
	 *
	 * @return object
	 *
	 * @throws Exception Throws an exception if the API request fails.
	 */
	public function get_account_info() {
		// Make a request to the Constant Contact API to retrieve account information.
		$response = wp_remote_get(
			self::BASE_URL . '/account/summary',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Unable to get account information: %s', $response->get_error_message() ) ); // phpcs:ignore
		}
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$this->throw_error_from_response( 'Unable to get account information: %s', $response );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		return new Hustle_ConstantContact_AccountInfo( (array) $data );
	}

	/**
	 * Get contact lists
	 *
	 * @return array
	 *
	 * @throws Exception Throws an exception if the API request fails.
	 */
	public function get_contact_lists() {
		$lists    = array();
		$response = wp_remote_get(
			self::BASE_URL . '/contact_lists?limit=100&status=active',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Unable to get contact lists: %s', $response->get_error_message() ) ); // phpcs:ignore
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$this->throw_error_from_response( 'Unable to get contact lists: %s', $response );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( ! empty( $data->lists ) ) {
			foreach ( $data->lists as $list ) {
				$lists[] = new Hustle_ConstantContact_ContactsList( (array) $list );
			}
		}

		return $lists;
	}

	/**
	 * Create a new contact
	 *
	 * @param Hustle_ConstantContact_Contact $contact Contact object.
	 * @return Hustle_ConstantContact_Contact
	 *
	 * @throws Exception Throws an exception if the API request fails.
	 */
	public function create_contact( $contact ) {
		$response = wp_remote_post(
			self::BASE_URL . '/contacts',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $contact->prepare_for_create() ),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Unable to create contact: %s', $response->get_error_message() ) ); // phpcs:ignore
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 201 !== $response_code ) {
			return $this->throw_error_from_response( 'Unable to create contact: %s', $response );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return new Hustle_ConstantContact_Contact( $data );
	}

	/**
	 * Get a contact by email
	 *
	 * @param string $email Email address of the contact.
	 * @return object
	 *
	 * @throws Exception Throws an exception if the API request fails.
	 */
	public function get_contact( $email ) {
		$response = wp_remote_get(
			self::BASE_URL . '/contacts?include=custom_fields,list_memberships&email=' . $email,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Unable to get contact: %s', $response->get_error_message() ) ); // phpcs:ignore
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return $this->throw_error_from_response( 'Unable to get contact: %s', $response );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$response_model = new Hustle_ConstantContact_ContactsResponse( (array) $data );
		if ( ! empty( $response_model->contacts ) ) {
			return $response_model->contacts[0];
		}

		return null;
	}

	/**
	 * Update contact
	 *
	 * @param Hustle_ConstantContact_Contact $contact Contact object.
	 *
	 * @throws Exception Throws an exception if the API request fails.
	 */
	public function update_contact( $contact ) {
		$response = wp_remote_request(
			self::BASE_URL . '/contacts/' . $contact->id,
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $contact->prepare_for_update() ),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Unable to update contact: %s', $response->get_error_message() ) ); // phpcs:ignore
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$this->throw_error_from_response( 'Unable to update contact: %s', $response );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return new Hustle_ConstantContact_Contact( $data );
	}

	/**
	 * Throws formatted exception retrieved from response
	 *
	 * @param string      $format   Format string.
	 * @param array|mixed $response Response object.
	 *
	 * @throws Exception Throws an exception.
	 */
	private function throw_error_from_response( $format, $response ) {
		$errors  = $this->get_response_errors( $response );
		$message = $this->get_formatted_error_message( $format, $errors );

		throw new Exception( $message ); // phpcs:ignore
	}

	/**
	 * Get error message from response
	 *
	 * @param WP_Error|array $response Response object.
	 * @return array
	 */
	private function get_response_errors( $response ) {
		$response_text = wp_remote_retrieve_body( $response );
		$error_data    = json_decode( $response_text, true );

		if ( is_array( $error_data ) ) {
			$errors = array_map(
				function ( $error ) {
					return $error['error_message'];
				},
				$error_data
			);
			return $errors;
		}

		return $response_text;
	}

	/**
	 * Get formatted error message
	 *
	 * @param string $format Format string.
	 * @param array  $errors Array of error messages.
	 * @return string
	 */
	private function get_formatted_error_message( $format, $errors ) {
		if ( is_array( $errors ) ) {
			$errors = implode( ' ', $errors );
			return sprintf( $format, $errors ); // phpcs:ignore
		}

		return '';
	}

	/**
	 * Get custom fields
	 *
	 * @return array
	 *
	 * @throws Exception Throws an exception if the API request fails.
	 */
	public function get_custom_fields() {
		$response = wp_remote_get(
			self::BASE_URL . '/contact_custom_fields',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Unable to get custom fields: %s', $response->get_error_message() ) ); // phpcs:ignore
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$this->throw_error_from_response( 'Unable to get custom fields: %s', $response );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['custom_fields'] ) ) {
			$result = array_map(
				function ( $field ) {
					return new Hustle_ConstantContact_CustomField( (array) $field );
				},
				$data['custom_fields']
			);
			return $result;
		}
		return array();
	}
}
