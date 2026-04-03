<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Physical Address Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_PhysicalAddress' ) ) {

	/**
	 * Class Hustle_ConstantContact_PhysicalAddress
	 *
	 * Model for physical address data
	 */
	class Hustle_ConstantContact_PhysicalAddress extends Hustle_ConstantContact_Base_Model {

		/**
		 * Address line 1
		 *
		 * @var string
		 */
		public $address_line1;

		/**
		 * Address line 2
		 *
		 * @var string
		 */
		public $address_line2;

		/**
		 * Address line 3
		 *
		 * @var string
		 */
		public $address_line3;

		/**
		 * City
		 *
		 * @var string
		 */
		public $city;

		/**
		 * State code
		 *
		 * @var string
		 */
		public $state_code;

		/**
		 * State name
		 *
		 * @var string
		 */
		public $state_name;

		/**
		 * Postal code
		 *
		 * @var string
		 */
		public $postal_code;

		/**
		 * Country code
		 *
		 * @var string
		 */
		public $country_code;
	}
}
