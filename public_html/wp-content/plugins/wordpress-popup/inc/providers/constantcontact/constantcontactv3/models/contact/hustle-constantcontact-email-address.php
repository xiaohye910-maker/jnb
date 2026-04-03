<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Email Address Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_EmailAddress' ) ) {

	/**
	 * Class Hustle_ConstantContact_EmailAddress
	 *
	 * Model for email address data
	 */
	class Hustle_ConstantContact_EmailAddress extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * Email address
		 *
		 * @var string
		 */
		public $address;

		/**
		 * Permission to send
		 *
		 * @var string
		 */
		public $permission_to_send;

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
		 * Opt in source
		 *
		 * @var string
		 */
		public $opt_in_source;

		/**
		 * Opt in date
		 *
		 * @var string
		 */
		public $opt_in_date;

		/**
		 * Opt out source
		 *
		 * @var string
		 */
		public $opt_out_source;

		/**
		 * Opt out date
		 *
		 * @var string
		 */
		public $opt_out_date;

		/**
		 * Opt out reason
		 *
		 * @var string
		 */
		public $opt_out_reason;

		/**
		 * Confirm status
		 *
		 * @var string
		 */
		public $confirm_status;

		/**
		 * Prepare for update
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			$data = array(
				'address' => $this->address,
			);

			if ( ! empty( $this->permission_to_send ) ) {
				// Only these values are allowed.
				if (
					in_array(
						$this->permission_to_send,
						array(
							'implicit',
							'explicit',
							'pending_confirmation',
							'unsubscribed',
							'temp_hold',
							'not_set',
						),
						true
					)
				) {
					$data['permission_to_send'] = $this->permission_to_send;
				}
			}

			if ( ! empty( $this->opt_out_reason ) ) {
				$data['opt_out_reason'] = sanitize_text_field( $this->opt_out_reason );
			}

			return $data;
		}
	}
}
