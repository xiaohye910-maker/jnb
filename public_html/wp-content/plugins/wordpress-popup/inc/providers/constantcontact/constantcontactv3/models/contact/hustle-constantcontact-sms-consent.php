<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 SMS Consent Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_SmsConsent' ) ) {

	/**
	 * Class Hustle_ConstantContact_SmsConsent
	 *
	 * Model for SMS consent data
	 */
	class Hustle_ConstantContact_SmsConsent extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * SMS consent permission
		 *
		 * @var string
		 */
		public $sms_consent_permission;

		/**
		 * Consent type
		 *
		 * @var string
		 */
		public $consent_type;

		/**
		 * Opt in date
		 *
		 * @var string
		 */
		public $opt_in_date;

		/**
		 * Opt out date
		 *
		 * @var string
		 */
		public $opt_out_date;

		/**
		 * Advertised frequency
		 *
		 * @var string
		 */
		public $advertised_frequency;

		/**
		 * Advertised interval
		 *
		 * @var string
		 */
		public $advertised_interval;

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
				'sms_consent_permission' => sanitize_text_field( strtolower( $this->sms_consent_permission ) ),
				'consent_type'           => sanitize_text_field( strtolower( $this->consent_type ) ),
			);
		}
	}
}
