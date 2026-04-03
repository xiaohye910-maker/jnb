<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Street Address Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_StreetAddress' ) ) {

	/**
	 * Class Hustle_ConstantContact_StreetAddress
	 *
	 * Model for street address data
	 */
	class Hustle_ConstantContact_StreetAddress extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * Street address ID
		 *
		 * @var string
		 */
		public $street_address_id;

		/**
		 * Address kind (home, work, etc.)
		 *
		 * @var string
		 */
		public $kind;

		/**
		 * Street address
		 *
		 * @var string
		 */
		public $street;

		/**
		 * City
		 *
		 * @var string
		 */
		public $city;

		/**
		 * State
		 *
		 * @var string
		 */
		public $state;

		/**
		 * Postal code
		 *
		 * @var string
		 */
		public $postal_code;

		/**
		 * Country
		 *
		 * @var string
		 */
		public $country;

		/**
		 * Created at timestamp
		 *
		 * @var string
		 */
		public $created_at;

		/**
		 * Updated at timestamp
		 *
		 * @var string
		 */
		public $updated_at;

		/**
		 * Prepare data for update
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			return array(
				'kind'        => sanitize_text_field( $this->kind ),
				'street'      => sanitize_text_field( $this->street ),
				'city'        => sanitize_text_field( $this->city ),
				'state'       => sanitize_text_field( $this->state ),
				'postal_code' => (int) $this->postal_code,
				'country'     => sanitize_text_field( $this->country ),
			);
		}
	}
}
