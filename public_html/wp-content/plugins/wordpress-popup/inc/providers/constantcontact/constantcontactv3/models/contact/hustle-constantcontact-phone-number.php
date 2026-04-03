<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Phone Number Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_PhoneNumber' ) ) {

	/**
	 * Class Hustle_ConstantContact_PhoneNumber
	 *
	 * Model for phone number data
	 */
	class Hustle_ConstantContact_PhoneNumber extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * Phone number ID
		 *
		 * @var string
		 */
		public $phone_number_id;

		/**
		 * Phone number
		 *
		 * @var string
		 */
		public $phone_number;

		/**
		 * Phone kind (home, work, mobile, etc.)
		 *
		 * @var string
		 */
		public $kind;

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
		 * Update source
		 *
		 * @var string
		 */
		public $update_source;

		/**
		 * Create source
		 *
		 * @var string
		 */
		public $create_source;

		/**
		 * Prepare data for update
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			return array(
				'phone_number' => $this->phone_number,
				'kind'         => $this->kind,
			);
		}
	}
}
