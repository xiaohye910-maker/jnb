<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle HubSpot API v3.
 *
 * @since 7.8.11
 *
 * @package Hustle
 */

/**
 * Class Hustle_Hubspot_API_V3
 * Implements HubSpot API v3 endpoints
 */
class Hustle_Hubspot_API_V3 extends Hustle_HubSpot_Api {

	/**
	 * Retrieve contact lists from HubSpot using v3 API
	 *
	 * @return array
	 */
	public function get_contact_list() {
		$listing = array();

		$args = array(
			'count'           => 200,
			'offset'          => 0,
			'processingTypes' => array( 'MANUAL', 'SNAPSHOT' ),
		);
		$res  = $this->send_authenticated_post( 'crm/v3/lists/search', $args, false, true );

		if ( ! is_wp_error( $res ) && ! empty( $res->lists ) ) {
			$listing = wp_list_pluck( $res->lists, 'name', 'listId' );
		}

		return $listing;
	}

	/**
	 * Check if the given email address is already a subscriber using v3 API.
	 *
	 * @param string $email The email address to check.
	 *
	 * @return bool|mixed
	 */
	public function email_exists( $email ) {
		$endpoint = 'crm/v3/objects/contacts/' . $email;
		$args     = array( 'idProperty' => 'email' );

		$res = $this->send_authenticated_get( $endpoint, $args );

		if ( ! is_wp_error( $res ) && ! empty( $res->id ) ) {
			// For backward compatibility
			// HubSpot API v3 returns contact ID in 'id' field, but Hustle expects it in 'vid' field,
			// so we map it here.
			$res->vid = $res->id;

			return $res;
		}

		return false;
	}

	/**
	 * Get the list of existing properties from HubSpot account using v3 API.
	 *
	 * @return array
	 */
	public function get_properties() {
		$properties = array();
		$res        = $this->send_authenticated_get( 'crm/v3/properties/contacts' );

		if ( ! is_wp_error( $res ) && ! empty( $res->results ) ) {
			foreach ( $res->results as $prop ) {
				$properties[ $prop->name ] = $prop->label;
			}
		}

		return $properties;
	}

	/**
	 * Add new field contact property to HubSpot using v3 API.
	 *
	 * @param array $property Property.
	 *
	 * @return bool
	 */
	public function add_property( array $property ) {
		$res = $this->send_authenticated_post( 'crm/v3/properties/contacts', $property, false, true );

		return ! is_wp_error( $res ) && ! empty( $res->name );
	}

	/**
	 * Add contact subscriber to HubSpot using v3 API.
	 *
	 * @param array $data Data.
	 *
	 * @return mixed
	 * @throws Exception Custom fields do not exist.
	 */
	public function add_contact( $data ) {
		$props = array();

		// Add error log entries for subscription errors caused by custom fields not registered in HubSpot.
		$default_data        = array( 'first_name', 'last_name' );
		$existing_properties = array_merge( $this->get_properties(), array_flip( $default_data ) );
		$filtered_data       = array_intersect_key( $data, $existing_properties );

		$difference = array_diff_key( $data, $filtered_data );
		if ( ! empty( $difference ) ) {
			$invalid_fields = implode( ', ', array_keys( $difference ) );
			throw new Exception(
				esc_html(
					sprintf(
						/* translators: %s: List of invalid fields */
						esc_html__( 'These fields are preventing your users from subscribing because they do not exist in your Hubspot account: %s', 'hustle' ),
						esc_html( $invalid_fields )
					)
				)
			);
		}

		foreach ( $data as $key => $value ) {
			if ( 'first_name' === $key ) {
				$key = 'firstname';
			}
			if ( 'last_name' === $key ) {
				$key = 'lastname';
			}

			$props[ $key ] = $value;
		}

		$args     = array( 'properties' => $props );
		$endpoint = 'crm/v3/objects/contacts';

		$res = $this->send_authenticated_post( $endpoint, $args, false, true );

		if ( ! is_wp_error( $res ) && ! empty( $res->id ) ) {
			return $res->id;
		}

		return $res;
	}

	/**
	 * Update contact subscriber in HubSpot using v3 API.
	 *
	 * @param string $id ID.
	 * @param array  $data Data.
	 *
	 * @return mixed
	 * @throws Exception Custom fields do not exist.
	 */
	public function update_contact( $id, $data ) {
		$props = array();

		// Add error log entries for subscription errors caused by custom fields not registered in HubSpot.
		$default_data        = array( 'first_name', 'last_name' );
		$existing_properties = array_merge( $this->get_properties(), array_flip( $default_data ) );
		$filtered_data       = array_intersect_key( $data, $existing_properties );

		$difference = array_diff_key( $data, $filtered_data );
		if ( ! empty( $difference ) ) {
			$invalid_fields = implode( ', ', array_keys( $difference ) );
			throw new Exception(
				esc_html(
					sprintf(
						/* translators: %s: List of invalid fields */
						esc_html__( 'These fields are preventing your users from subscribing because they do not exist in your Hubspot account: %s', 'hustle' ),
						esc_html( $invalid_fields )
					)
				)
			);
		}

		foreach ( $data as $key => $value ) {
			if ( 'first_name' === $key ) {
				$key = 'firstname';
			}
			if ( 'last_name' === $key ) {
				$key = 'lastname';
			}

			$props[ $key ] = $value;
		}

		$args     = array( 'properties' => $props );
		$endpoint = 'crm/v3/objects/contacts/' . $id;

		$res = $this->send_authenticated_patch( $endpoint, $args, false, true );

		if ( ! is_wp_error( $res ) && ! empty( $res->id ) ) {
			return $res->id;
		}

		return $res;
	}

	/**
	 * Add contact to contact list using v3 API.
	 *
	 * @param string $contact_id Contact ID.
	 * @param string $email Email.
	 * @param string $email_list Email list.
	 *
	 * @return bool|mixed
	 */
	public function add_to_contact_list( $contact_id, $email, $email_list ) {
		$args     = array( $contact_id );
		$endpoint = 'crm/v3/lists/' . $email_list . '/memberships/add';
		$res      = $this->send_authenticated_put( $endpoint, $args, false, true );

		if ( ! is_wp_error( $res ) ) {
			return true;
		}

		if ( ! empty( $res->status ) && 'error' === $res->status && ! empty( $res->message ) ) {
			$res = new WP_Error( 'provider_error', $res->message );
		}

		return $res;
	}

	/**
	 * Delete subscriber from the list using v3 API
	 *
	 * @param string $list_id List ID.
	 * @param string $email Email.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function delete_email( $list_id, $email ) {
		$email_exist = $this->email_exists( $email );
		if ( ! $email_exist || empty( $email_exist->id ) ) {
			return false;
		}

		$endpoint = 'crm/v3/lists/' . $list_id . '/memberships/remove';
		$args     = array( $email_exist->id );
		$res      = $this->send_authenticated_put( $endpoint, $args, false, true );

		return ! is_wp_error( $res );
	}

	/**
	 * Get legacy list mapping for the given list ID.
	 *
	 * @param string $ids List IDs.
	 *
	 * @return object|WP_Error
	 */
	public function get_legacy_list_mappings( $ids ) {
		$endpoint = 'crm/v3/lists/idmapping';
		$res      = $this->send_authenticated_post( $endpoint, $ids, false, true );

		return $res;
	}

	/**
	 * Helper function to send authenticated PATCH request.
	 *
	 * @param string  $end_point The endpoint the request will be sent to.
	 * @param array   $query_args Args.
	 * @param boolean $x_www Whether the request is sent in application/x-www-form format.
	 * @param boolean $json If json.
	 *
	 * @return mixed
	 */
	public function send_authenticated_patch( $end_point, $query_args = array(), $x_www = false, $json = false ) {
		$access_token = $this->get_token( 'access_token' );
		return $this->request( $end_point, 'PATCH', $query_args, $access_token, $x_www, $json );
	}

	/**
	 * Helper function to send authenticated PUT request.
	 *
	 * @param string  $end_point The endpoint the request will be sent to.
	 * @param array   $query_args Args.
	 * @param boolean $x_www Whether the request is sent in application/x-www-form format.
	 * @param boolean $json If json.
	 *
	 * @return mixed
	 */
	public function send_authenticated_put( $end_point, $query_args = array(), $x_www = false, $json = false ) {
		$access_token = $this->get_token( 'access_token' );
		return $this->request( $end_point, 'PUT', $query_args, $access_token, $x_www, $json );
	}

	/**
	 * Get client id and scope data.
	 *
	 * @return array
	 */
	protected function get_client_data() {
		return array();
	}
}
