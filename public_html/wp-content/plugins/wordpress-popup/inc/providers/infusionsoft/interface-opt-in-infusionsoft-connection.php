<?php
/**
 * Opt_In_Infusionsoft_Connection interface
 *
 * @package Hustle
 */

if ( interface_exists( 'Opt_In_Infusionsoft_Connection' ) ) {
	return;
}

/**
 * Interface Opt_In_Infusionsoft_Connection
 */
interface Opt_In_Infusionsoft_Connection {
	/**
	 * Get the custom fields at Keap account.
	 *
	 * @return Hustle_Infusion_Soft_Custom_Field[]|WP_Error
	 **/
	public function get_custom_fields();

	/**
	 * Get the built-in custom fields at Keap account.
	 *
	 * @return string[]|WP_Error
	 **/
	public function get_builtin_custom_field_names();
	/**
	 * Create custom field at Keap account.
	 *
	 * @param string $name Name of the custom field.
	 * @param string $type Type of the custom field.
	 * @return int|WP_Error The ID of the created custom field or WP_Error on failure.
	 **/
	public function add_custom_field( $name, $type = 'Text' );
	/**
	 * Add new contact to infusionsoft and return contact ID on success or WP_Error.
	 *
	 * @param array $contact            An array of contact details.
	 **/
	public function add_contact( $contact );
	/**
	 * Updates an existing contact.
	 *
	 * @param int   $contact_id Contact ID.
	 * @param array $contact Array of contact details to be updated.
	 * @return integer|WP_Error Contact ID if everything went well, WP_Error otherwise.
	 */
	public function update_contact( $contact_id, $contact );
	/**
	 * Delete subscriber from the list
	 *
	 * @param string $contact_id Contact ID.
	 * @param string $list_id List ID.
	 *
	 * @return bool
	 */
	public function remove_contact_from_list( $contact_id, $list_id );
	/**
	 * Email exists?
	 *
	 * @param string $email Email.
	 * @return int Contact Id if exists, 0 if not exists, WP_Error on error.
	 */
	public function email_exist( $email );
	/**
	 * Adds contact with $contact_id to group with $group_id
	 *
	 * @param string $contact_id Contact ID.
	 * @param string $tag_id Tag ID.
	 * @return bool|WP_Error
	 */
	public function add_tag_to_contact( $contact_id, $tag_id );
	/**
	 * Get lists
	 *
	 * @return type
	 */
	public function get_lists();
}
