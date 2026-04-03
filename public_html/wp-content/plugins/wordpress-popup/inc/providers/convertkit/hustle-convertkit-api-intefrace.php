<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_ConvertKit_Api_Interface interface
 *
 * @package Hustle
 */

/**
 * ConvertKit API Interface
 *
 * @interface Hustle_ConvertKit_Api_Interface
 **/
interface Hustle_ConvertKit_Api_Interface {

	/**
	 * Retrieves ConvertKit forms as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_forms();

	/**
	 * Retrieves ConvertKit subscribers as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_subscribers();

	/**
	 * Retrieves ConvertKit form's custom fields as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_form_custom_fields();

	/**
	 * Add new custom fields to subscription
	 *
	 * @param array $field_data Fields data.
	 * @return array|mixed|object|WP_Error
	 */
	public function create_custom_fields( $field_data );

	/**
	 * Add new subscriber
	 *
	 * @param string $form_id Form ID.
	 * @param array  $data Data.
	 * @return array|mixed|object|WP_Error
	 */
	public function subscribe( $form_id, $data );

	/**
	 * Update subscriber
	 *
	 * @since 4.0
	 *
	 * @param string $id ID.
	 * @param array  $data Data.
	 * @return array|mixed|object|WP_Error
	 */
	public function update_subscriber( $id, $data );

	/**
	 * Delete subscriber from the list
	 *
	 * @param string $list_id List ID.
	 * @param string $email Email.
	 *
	 * @return bool
	 */
	public function delete_email( $list_id, $email );

	/**
	 * Verify if an email is already a subscriber.
	 *
	 * @param string $email Email.
	 *
	 * @return object|false Returns data of existing subscriber if exist otherwise false.
	 **/
	public function is_subscriber( $email );

	/**
	 * Verify if an email is already a subscriber in a form.
	 *
	 * @param string  $email Email.
	 * @param integer $form_id Form ID.
	 *
	 * @return boolean|integer True if the subscriber exists, otherwise false.
	 **/
	public function is_form_subscriber( $email, $form_id );
}
