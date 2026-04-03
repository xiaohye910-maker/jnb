<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 SMS Channel Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_SmsChannel' ) ) {

	/**
	 * Class Hustle_ConstantContact_SmsChannel
	 *
	 * Model for SMS channel data
	 */
	class Hustle_ConstantContact_SmsChannel extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * SMS channel ID
		 *
		 * @var string
		 */
		public $sms_channel_id;

		/**
		 * SMS address (phone number)
		 *
		 * @var string
		 */
		public $sms_address;

		/**
		 * Dial code
		 *
		 * @var string
		 */
		public $dial_code;

		/**
		 * Country code
		 *
		 * @var string
		 */
		public $country_code;

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
		 * SMS channel consents
		 *
		 * @var Hustle_ConstantContact_SmsConsent[]
		 */
		public $sms_channel_consents = array();

		/**
		 * Populate the model properties from an associative array
		 *
		 * @param array $data Associative array of data to populate the model.
		 */
		public function populate_from_data( array $data ) {
			$this->sms_channel_id = isset( $data['sms_channel_id'] ) ? $data['sms_channel_id'] : '';
			$this->sms_address    = isset( $data['sms_address'] ) ? $data['sms_address'] : '';
			$this->dial_code      = isset( $data['dial_code'] ) ? $data['dial_code'] : '';
			$this->country_code   = isset( $data['country_code'] ) ? $data['country_code'] : '';
			$this->update_source  = isset( $data['update_source'] ) ? $data['update_source'] : '';
			$this->create_source  = isset( $data['create_source'] ) ? $data['create_source'] : '';

			// SMS channel consents.
			if ( isset( $data['sms_channel_consents'] ) && is_array( $data['sms_channel_consents'] ) ) {
				foreach ( $data['sms_channel_consents'] as $consent ) {
					$this->sms_channel_consents[] = new Hustle_ConstantContact_SmsConsent( $consent );
				}
			}
		}

		/**
		 * Prepare data for update
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			$data = array(
				'full_sms_address'     => $this->sms_address,
				'sms_channel_consents' => array(),
			);

			foreach ( $this->sms_channel_consents as $consent ) {
				$data['sms_channel_consents'][] = $consent->prepare_for_update();
			}

			return $data;
		}
	}
}
