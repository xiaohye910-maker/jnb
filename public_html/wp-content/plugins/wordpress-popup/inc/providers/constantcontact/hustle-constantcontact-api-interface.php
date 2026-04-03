<?php
/**
 * Hustle ConstantContact API Interface
 *
 * @package Hustle
 */

/**
 * Interface Hustle_ConstantContact_Api_Interface
 */

interface Hustle_ConstantContact_Api_Interface {
	/**
	 * Listen to request callback sent from WPMUDEV.
	 *
	 * @return void
	 */
	public function process_callback_request();

	/**
	 * Generates authorization URL.
	 *
	 * @param int    $module_id Module ID.
	 * @param bool   $log_referrer Log referrer.
	 * @param string $page Page.
	 * @return string
	 */
	public function get_authorization_uri( $module_id = 0, $log_referrer = true, $page = 'hustle_embedded' );

	/**
	 * Get token value by key.
	 *
	 * @param string $key Key.
	 * @return bool|mixed
	 */
	public function get_token( $key );

	/**
	 * Compose redirect_uri to use on request argument.
	 *
	 * @return string
	 */
	public function get_redirect_uri();

	/**
	 * Get access token from code.
	 *
	 * @param string $code Code.
	 * @return bool
	 */
	public function get_access_token( $code );

	/**
	 * Get stored token data.
	 *
	 * @return array|null
	 */
	public function get_auth_token();

	/**
	 * Update token data.
	 *
	 * @param array $token Token.
	 * @return void
	 */
	public function update_auth_token( array $token );

	/**
	 * Get current account information.
	 *
	 * @return object
	 */
	public function get_account_info();

	/**
	 * Retrieve contact lists from ConstantContact.
	 *
	 * @return array
	 */
	public function get_contact_lists();

	/**
	 * Retrieve contact from ConstantContact.
	 *
	 * @param string $email Email.
	 * @return false|object
	 */
	public function get_contact( $email );

	/**
	 * Check if contact exists in certain list.
	 *
	 * @param object $contact Contact object.
	 * @param string $list_id List ID.
	 * @return bool
	 */
	public function contact_exist( $contact, $list_id );

	/**
	 * Subscribe contact.
	 *
	 * @param string $email Email.
	 * @param string $first_name First name.
	 * @param string $last_name Last name.
	 * @param string $target_list Constant contact list.
	 * @param array  $custom_fields Custom fields.
	 * @return mixed
	 */
	public function subscribe( $email, $first_name, $last_name, $target_list, $custom_fields = array() );

	/**
	 * Remove wp_options rows.
	 *
	 * @return void
	 */
	public function remove_wp_options();

	/**
	 * Update Subscription.
	 *
	 * @param object $contact Contact.
	 * @param string $first_name First name.
	 * @param string $last_name Last name.
	 * @param string $target_list Constant contact list.
	 * @param array  $custom_fields Custom fields.
	 * @return mixed
	 */
	public function update_subscription( $contact, $first_name, $last_name, $target_list, $custom_fields = array() );

	/**
	 * Delete subscriber from the list.
	 *
	 * @param string $list_id List ID.
	 * @param string $email Email.
	 * @return bool
	 */
	public function delete_email( $list_id, $email );
}
