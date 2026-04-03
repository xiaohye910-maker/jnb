<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_ConvertKit_API_V2 class
 *
 * @package Hustle
 */

/**
 * ConvertKit API V2 (implements Kit API V4)
 *
 * @class Hustle_ConvertKit_API_V2
 **/
class Hustle_ConvertKit_API_V2 implements Hustle_ConvertKit_Api_Interface {

	/**
	 * Api key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Endpoint
	 *
	 * @var string
	 */
	private $endpoint = 'https://api.kit.com/v4/';

	/**
	 * Constructs class with required data
	 *
	 * Hustle_ConvertKit_API_V2 constructor.
	 *
	 * @param string $api_key Api key.
	 * @param string $api_secret Api secret (not used in v4).
	 */
	public function __construct( $api_key, $api_secret = '' ) {
		$this->api_key = $api_key;
	}

	/**
	 * Sends request to the endpoint url with the provided $action
	 *
	 * @param string $action rest action.
	 * @param string $verb Verb.
	 * @param array  $args Args.
	 * @return object|WP_Error
	 */
	private function request( $action, $verb = 'GET', $args = array() ) {
		$url = trailingslashit( $this->endpoint ) . $action;

		$_args = array(
			'method'  => $verb,
			'headers' => array(
				'X-Kit-Api-Key' => $this->api_key,
				'Content-Type'  => 'application/json;charset=utf-8',
			),
		);

		if ( 'GET' === $verb ) {
			if ( ! empty( $args ) ) {
				$url .= ( '?' . http_build_query( $args ) );
			}
		} else {
			$_args['body'] = wp_json_encode( $args );
		}

		$res = wp_remote_request( $url, $_args );

		// logging data.
		$utils                     = Hustle_Provider_Utils::get_instance();
		$utils->last_url_request   = $url;
		$utils->last_data_sent     = $_args;
		$utils->last_data_received = $res;

		if ( ! is_wp_error( $res ) && is_array( $res ) ) {
			$code = $res['response']['code'];

			if ( $code >= 200 && $code < 300 ) {
				$body = wp_remote_retrieve_body( $res );
				return json_decode( $body );
			}

			$err = new WP_Error();
			$err->add( $code, $res['response']['message'] );
			return $err;
		}

		return $res;
	}

	/**
	 * Sends rest GET request
	 *
	 * @param string $action Action.
	 * @param array  $args Args.
	 * @return array|mixed|object|WP_Error
	 */
	private function get( $action, $args = array() ) {
		return $this->request( $action, 'GET', $args );
	}

	/**
	 * Sends rest POST request
	 *
	 * @param string $action Action.
	 * @param array  $args Args.
	 * @return array|mixed|object|WP_Error
	 */
	private function post( $action, $args = array() ) {
		return $this->request( $action, 'POST', $args );
	}

	/**
	 * Sends rest PUT request
	 *
	 * @param string $action Action.
	 * @param array  $args Args.
	 * @return array|mixed|object|WP_Error
	 */
	private function put( $action, $args = array() ) {
		return $this->request( $action, 'PUT', $args );
	}

	/**
	 * Retrieves ConvertKit forms as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_forms() {
		$response = $this->get( 'forms' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response->forms ) ) {
			return new WP_Error( 'forms_not_found', __( 'Not found forms with this api key.', 'hustle' ) );
		}

		return $response->forms;
	}

	/**
	 * Retrieves ConvertKit subscribers as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_subscribers() {
		$response = $this->get( 'subscribers' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response->subscribers ) ) {
			return new WP_Error( 'subscribers_not_found', __( 'Not found subscribers with this api key.', 'hustle' ) );
		}

		return $response->subscribers;
	}

	/**
	 * Retrieves ConvertKit form's custom fields as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_form_custom_fields() {
		$response = $this->get( 'custom_fields' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response->custom_fields ) ) {
			return new WP_Error( 'custom_fields_not_found', __( 'Not found custom fields with this api key.', 'hustle' ) );
		}

		return $response->custom_fields;
	}

	/**
	 * Add new custom fields to subscription
	 *
	 * @param array $field_data Fields data.
	 * @return array|mixed|object|WP_Error
	 */
	public function create_custom_fields( $field_data ) {
		$args = array(
			'label' => $field_data['label'],
		);

		$res = $this->post( 'custom_fields', $args );

		return is_wp_error( $res ) ? $res : __( 'Successfully added custom field', 'hustle' );
	}

	/**
	 * Add new subscriber
	 *
	 * @param string $form_id Form ID.
	 * @param array  $data Data.
	 * @return array|mixed|object|WP_Error
	 */
	public function subscribe( $form_id, $data ) {
		// First, create or update the subscriber.
		$subscriber_data = array(
			'email_address' => $data['email'],
		);

		if ( isset( $data['first_name'] ) ) {
			$subscriber_data['first_name'] = $data['first_name'];
		}

		if ( isset( $data['fields'] ) ) {
			$subscriber_data['fields'] = $data['fields'];
		}

		// Create subscriber using v4 API.
		$subscriber_res = $this->post( 'subscribers', $subscriber_data );

		if ( is_wp_error( $subscriber_res ) ) {
			return $subscriber_res;
		}

		if ( ! isset( $subscriber_res->subscriber ) ) {
			return new WP_Error( 'subscriber_creation_failed', __( 'Failed to create subscriber.', 'hustle' ) );
		}

		$subscriber_id = $subscriber_res->subscriber->id;

		// Now add subscriber to the form.
		$form_data = array(
			'referrer' => isset( $data['referrer'] ) ? $data['referrer'] : '',
		);

		$url = 'forms/' . $form_id . '/subscribers/' . $subscriber_id;
		$res = $this->post( $url, $form_data );

		return is_wp_error( $res ) ? $res : __( 'Successful subscription', 'hustle' );
	}

	/**
	 * Update subscriber
	 *
	 * @since 4.0
	 *
	 * @param string $id ID.
	 * @param array  $data Data.
	 * @return array|mixed|object|WP_Error
	 */
	public function update_subscriber( $id, $data ) {
		$url = 'subscribers/' . $id;
		$res = $this->put( $url, $data );

		return is_wp_error( $res ) ? $res : __( 'Successful subscription', 'hustle' );
	}

	/**
	 * Delete subscriber from the list
	 *
	 * @param string $list_id List ID.
	 * @param string $email Email.
	 *
	 * @return bool
	 */
	public function delete_email( $list_id, $email ) {
		// Get subscriber by email first.
		$subscriber = $this->is_subscriber( $email );

		if ( ! $subscriber ) {
			return false;
		}

		$subscriber_id = is_object( $subscriber ) ? $subscriber->id : false;

		if ( ! $subscriber_id ) {
			return false;
		}

		// Unsubscribe the subscriber using v4 API.
		$url = 'subscribers/' . $subscriber_id . '/unsubscribe';
		$res = $this->post( $url, array() );

		return ! is_wp_error( $res );
	}

	/**
	 * Verify if an email is already a subscriber.
	 *
	 * @param string $email Email.
	 *
	 * @return object|false Returns data of existing subscriber if exist otherwise false.
	 **/
	public function is_subscriber( $email ) {
		$args = array(
			'email_address' => $email,
		);

		$res = $this->get( 'subscribers', $args );

		if ( is_wp_error( $res ) ) {
			return false;
		}

		if ( ! empty( $res->subscribers ) && is_array( $res->subscribers ) ) {
			return array_shift( $res->subscribers );
		}

		return false;
	}

	/**
	 * Verify if an email is already a subscriber in a form.
	 *
	 * @param string  $email Email.
	 * @param integer $form_id Form ID.
	 *
	 * @return boolean|integer Subscriber ID if the subscriber exists, otherwise false.
	 **/
	public function is_form_subscriber( $email, $form_id ) {
		$url   = 'forms/' . $form_id . '/subscriptions';
		$res   = $this->get( $url );
		$exist = false;

		$utils                     = Hustle_Provider_Utils::get_instance();
		$utils->last_data_received = $res;
		$utils->last_url_request   = trailingslashit( $this->endpoint ) . $url;

		if ( is_wp_error( $res ) ) {
			Hustle_Provider_Utils::maybe_log( 'There was an error retrieving the subscribers from Kit: ' . $res->get_error_message() );
			return false;
		}

		if ( empty( $res->subscriptions ) ) {
			return false;
		}

		// Check subscribers in the current page.
		$subscribers    = wp_list_pluck( $res->subscriptions, 'subscriber' );
		$emails         = wp_list_pluck( $subscribers, 'email_address' );
		$subscribers_id = wp_list_pluck( $subscribers, 'id' );

		$key = array_search( $email, $emails, true );
		if ( false !== $key ) {
			return $subscribers_id[ $key ];
		}

		// Handle pagination if there are more pages.
		if ( isset( $res->pagination ) && $res->pagination->has_next_page && ! empty( $res->pagination->end_cursor ) ) {
			$cursor = $res->pagination->end_cursor;

			while ( $cursor ) {
				$args = array( 'after' => $cursor );
				$res  = $this->get( $url, $args );

				$utils                     = Hustle_Provider_Utils::get_instance();
				$utils->last_data_received = $res;
				$utils->last_url_request   = trailingslashit( $this->endpoint ) . $url;
				$utils->last_data_sent     = $args;

				if ( is_wp_error( $res ) || empty( $res->subscriptions ) ) {
					break;
				}

				$subscribers    = wp_list_pluck( $res->subscriptions, 'subscriber' );
				$emails         = wp_list_pluck( $subscribers, 'email_address' );
				$subscribers_id = wp_list_pluck( $subscribers, 'id' );

				$key = array_search( $email, $emails, true );
				if ( false !== $key ) {
					return $subscribers_id[ $key ];
				}

				// Move to next page if available.
				if ( isset( $res->pagination ) && $res->pagination->has_next_page && ! empty( $res->pagination->end_cursor ) ) {
					$cursor = $res->pagination->end_cursor;
				} else {
					break;
				}
			}
		}

		return false;
	}
}
