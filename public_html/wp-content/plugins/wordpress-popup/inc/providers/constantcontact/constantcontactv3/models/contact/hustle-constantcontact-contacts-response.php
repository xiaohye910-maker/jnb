<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Contacts Response Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_ContactsResponse' ) ) {

	/**
	 * Class Hustle_ConstantContact_ContactsResponse
	 *
	 * Model for the full API response containing contacts
	 */
	class Hustle_ConstantContact_ContactsResponse extends Hustle_ConstantContact_Base_Model {

		/**
		 * Contacts array
		 *
		 * @var Hustle_ConstantContact_Contact[]
		 */
		public $contacts = array();

		/**
		 * Total contacts count
		 *
		 * @var int
		 */
		public $contacts_count;

		/**
		 * Links for pagination
		 *
		 * @var array
		 */
		public $links = array();

		/**
		 * Status
		 *
		 * @var string
		 */
		public $status;

		/**
		 * Populate the model properties from an associative array
		 *
		 * @param array $data Associative array of data to populate the model.
		 */
		public function populate_from_data( $data ) {
			$this->contacts_count = isset( $data['contacts_count'] ) ? intval( $data['contacts_count'] ) : 0;
			$this->links          = isset( $data['_links'] ) ? $data['_links'] : array();
			$this->status         = isset( $data['status'] ) ? $data['status'] : '';

			// Contacts.
			if ( isset( $data['contacts'] ) && is_array( $data['contacts'] ) ) {
				foreach ( $data['contacts'] as $contact_data ) {
					$this->contacts[] = new Hustle_ConstantContact_Contact( $contact_data );
				}
			}
		}
	}
}
