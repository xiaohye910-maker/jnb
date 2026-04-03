<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Account Info Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_AccountInfo' ) ) {

	/**
	 * Class Hustle_ConstantContact_AccountInfo
	 *
	 * Model for account information data
	 */
	class Hustle_ConstantContact_AccountInfo extends Hustle_ConstantContact_Base_Model {

		/**
		 * Contact email
		 *
		 * @var string
		 */
		public $email;

		/**
		 * Contact phone
		 *
		 * @var string
		 */
		public $contact_phone;

		/**
		 * Country code
		 *
		 * @var string
		 */
		public $country_code;

		/**
		 * Encoded account ID
		 *
		 * @var string
		 */
		public $id;

		/**
		 * First name
		 *
		 * @var string
		 */
		public $first_name;

		/**
		 * Last name
		 *
		 * @var string
		 */
		public $last_name;

		/**
		 * Organization name
		 *
		 * @var string
		 */
		public $organization_name;

		/**
		 * Organization phone
		 *
		 * @var string
		 */
		public $organization_phone;

		/**
		 * State code
		 *
		 * @var string
		 */
		public $state_code;

		/**
		 * Time zone ID
		 *
		 * @var string
		 */
		public $time_zone_id;

		/**
		 * Website
		 *
		 * @var string
		 */
		public $website;

		/**
		 * Physical address
		 *
		 * @var Hustle_ConstantContact_PhysicalAddress
		 */
		public $physical_address;

		/**
		 * Company logo
		 *
		 * @var Hustle_ConstantContact_CompanyLogo
		 */
		public $company_logo;

		/**
		 * Populate the model properties from an associative array
		 *
		 * @param array $data Associative array of data to populate the model.
		 */
		public function populate_from_data( array $data ) {
			$this->email              = isset( $data['contact_email'] ) ? $data['contact_email'] : '';
			$this->contact_phone      = isset( $data['contact_phone'] ) ? $data['contact_phone'] : '';
			$this->country_code       = isset( $data['country_code'] ) ? $data['country_code'] : '';
			$this->id                 = isset( $data['encoded_account_id'] ) ? $data['encoded_account_id'] : '';
			$this->first_name         = isset( $data['first_name'] ) ? $data['first_name'] : '';
			$this->last_name          = isset( $data['last_name'] ) ? $data['last_name'] : '';
			$this->organization_name  = isset( $data['organization_name'] ) ? $data['organization_name'] : '';
			$this->organization_phone = isset( $data['organization_phone'] ) ? $data['organization_phone'] : '';
			$this->state_code         = isset( $data['state_code'] ) ? $data['state_code'] : '';
			$this->time_zone_id       = isset( $data['time_zone_id'] ) ? $data['time_zone_id'] : '';
			$this->website            = isset( $data['website'] ) ? $data['website'] : '';

			// Handle nested objects.
			if ( isset( $data['physical_address'] ) && is_array( $data['physical_address'] ) ) {
				$this->physical_address = new Hustle_ConstantContact_PhysicalAddress( $data['physical_address'] );
			}

			if ( isset( $data['company_logo'] ) && is_array( $data['company_logo'] ) ) {
				$this->company_logo = new Hustle_ConstantContact_CompanyLogo( $data['company_logo'] );
			}
		}
	}
}
