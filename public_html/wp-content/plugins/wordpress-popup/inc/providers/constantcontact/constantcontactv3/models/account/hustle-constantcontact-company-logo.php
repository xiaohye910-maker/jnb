<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Company Logo Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_CompanyLogo' ) ) {

	/**
	 * Class Hustle_ConstantContact_CompanyLogo
	 *
	 * Model for company logo data
	 */
	class Hustle_ConstantContact_CompanyLogo extends Hustle_ConstantContact_Base_Model {

		/**
		 * Logo URL
		 *
		 * @var string
		 */
		public $url;

		/**
		 * External URL
		 *
		 * @var string
		 */
		public $external_url;

		/**
		 * Internal ID
		 *
		 * @var string
		 */
		public $internal_id;
	}
}
