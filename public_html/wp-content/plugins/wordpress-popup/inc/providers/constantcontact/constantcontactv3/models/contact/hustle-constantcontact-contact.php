<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Contact Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_Contact' ) ) {

	/**
	 * Class Hustle_ConstantContact_Contact
	 *
	 * Model for Constant Contact v3 API Contact data
	 */
	class Hustle_ConstantContact_Contact extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * Contact ID
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Email address data
		 *
		 * @var Hustle_ConstantContact_EmailAddress
		 */
		public $email_address;

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
		 * Job title
		 *
		 * @var string
		 */
		public $job_title;

		/**
		 * Company name
		 *
		 * @var string
		 */
		public $company_name;

		/**
		 * Birthday month
		 *
		 * @var int
		 */
		public $birthday_month;

		/**
		 * Birthday day
		 *
		 * @var int
		 */
		public $birthday_day;

		/**
		 * Anniversary date
		 *
		 * @var string
		 */
		public $anniversary;

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
		 * Deleted at date
		 *
		 * @var string
		 */
		public $deleted_at;

		/**
		 * Custom fields array
		 *
		 * @var Hustle_ConstantContact_CustomField[]
		 */
		public $custom_fields = array();

		/**
		 * Phone numbers array
		 *
		 * @var Hustle_ConstantContact_PhoneNumber[]
		 */
		public $phone_numbers = array();

		/**
		 * Street addresses array
		 *
		 * @var Hustle_ConstantContact_StreetAddress[]
		 */
		public $street_addresses = array();

		/**
		 * List memberships array
		 *
		 * @var string[]
		 */
		public $list_memberships = array();

		/**
		 * Taggings array
		 *
		 * @var string[]
		 */
		public $taggings = array();

		/**
		 * Notes array
		 *
		 * @var Hustle_ConstantContact_Note[]
		 */
		public $notes = array();

		/**
		 * SMS channel data
		 *
		 * @var Hustle_ConstantContact_SmsChannel
		 */
		public $sms_channel;

		/**
		 * Populate the model properties from an associative array
		 *
		 * @param array $data Associative array of data to populate the model.
		 */
		public function populate_from_data( array $data ) {
			$this->id               = isset( $data['contact_id'] ) ? $data['contact_id'] : '';
			$this->first_name       = isset( $data['first_name'] ) ? $data['first_name'] : '';
			$this->last_name        = isset( $data['last_name'] ) ? $data['last_name'] : '';
			$this->job_title        = isset( $data['job_title'] ) ? $data['job_title'] : '';
			$this->company_name     = isset( $data['company_name'] ) ? $data['company_name'] : '';
			$this->birthday_month   = isset( $data['birthday_month'] ) ? intval( $data['birthday_month'] ) : 0;
			$this->birthday_day     = isset( $data['birthday_day'] ) ? intval( $data['birthday_day'] ) : 0;
			$this->anniversary      = isset( $data['anniversary'] ) ? $data['anniversary'] : '';
			$this->update_source    = isset( $data['update_source'] ) ? $data['update_source'] : '';
			$this->create_source    = isset( $data['create_source'] ) ? $data['create_source'] : '';
			$this->created_at       = isset( $data['created_at'] ) ? $data['created_at'] : '';
			$this->updated_at       = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
			$this->deleted_at       = isset( $data['deleted_at'] ) ? $data['deleted_at'] : '';
			$this->list_memberships = isset( $data['list_memberships'] ) ? $data['list_memberships'] : array();
			$this->taggings         = isset( $data['taggings'] ) ? $data['taggings'] : array();

			// Email address.
			if ( isset( $data['email_address'] ) && is_array( $data['email_address'] ) ) {
				$this->email_address = new Hustle_ConstantContact_EmailAddress( $data['email_address'] );
			}

			// Custom fields.
			if ( isset( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ) {
				foreach ( $data['custom_fields'] as $custom_field ) {
					$this->custom_fields[] = new Hustle_ConstantContact_CustomField( $custom_field );
				}
			}

			// Phone numbers.
			if ( isset( $data['phone_numbers'] ) && is_array( $data['phone_numbers'] ) ) {
				foreach ( $data['phone_numbers'] as $phone ) {
					$this->phone_numbers[] = new Hustle_ConstantContact_PhoneNumber( $phone );
				}
			}

			// Street addresses.
			if ( isset( $data['street_addresses'] ) && is_array( $data['street_addresses'] ) ) {
				foreach ( $data['street_addresses'] as $address ) {
					$this->street_addresses[] = new Hustle_ConstantContact_StreetAddress( $address );
				}
			}

			// Notes.
			if ( isset( $data['notes'] ) && is_array( $data['notes'] ) ) {
				foreach ( $data['notes'] as $note ) {
					$this->notes[] = new Hustle_ConstantContact_Note( $note );
				}
			}

			// SMS channel.
			if ( isset( $data['sms_channel'] ) && is_array( $data['sms_channel'] ) ) {
				$this->sms_channel = new Hustle_ConstantContact_SmsChannel( $data['sms_channel'] );
			}
		}

		/**
		 * Magic getter to provide backward compatibility for 'email' property
		 *
		 * @param string $name Property name.
		 * @return mixed
		 */
		public function __get( $name ) {
			if ( 'email' === $name ) {
				if ( $this->email_address ) {
					return $this->email_address->address;
				}
			}
			return $this->$name ?? null;
		}

		/**
		 * Prepare contact data for update.
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			$allowed_fields = array(
				'email_address',
				'first_name',
				'last_name',
				'job_title',
				'company_name',
				'birthday_month',
				'birthday_day',
				'anniversary',
				'update_source',
				'custom_fields',
				'phone_numbers',
				'street_addresses',
				'list_memberships',
				'taggings',
				'notes',
				'sms_channel',
			);

			$output_data = array( 'contact_id' => $this->id );

			$props = get_object_vars( $this );
			foreach ( $props as $prop => $value ) {
				if ( ! in_array( $prop, $allowed_fields, true ) ) {
					continue;
				}

				// Skip null values.
				if ( is_null( $value ) ) {
					continue;
				}

				// Skip zero values for birthday fields.
				if ( 'birthday_month' === $prop || 'birthday_day' === $prop ) {
					if ( 0 === $value ) {
						continue;
					}
				}

				if ( 'anniversary' === $prop ) {
					// Validate date string.
					if ( ! $this->is_valid_date_string( $value ) ) {
						continue;
					}
				}

				if ( 'update_source' === $prop ) {
					if ( ! in_array( strtolower( $value ), array( 'account', 'contact' ), true ) ) {
						$value = 'Contact';
					}
				}

				// Skip ID field because it is mapped to contact_id.
				if ( in_array( $prop, array( 'id' ), true ) ) {
					continue;
				}
				$output_data[ $prop ] = $this->sanitize_data( $value );
			}

			return $output_data;
		}

		/**
		 * Validate date string.
		 *
		 * @param string $date_string The date string to validate.
		 * @return bool True if valid, false otherwise.
		 */
		private function is_valid_date_string( $date_string ) {
			$date = DateTime::createFromFormat( 'Y-m-d', $date_string );
			return $date && $date->format( 'Y-m-d' ) === $date_string;
		}

		/**
		 * Prepare data for update.
		 *
		 * @param mixed $obj The object to prepare.
		 * @return mixed
		 */
		private function sanitize_data( $obj ) {
			if ( $obj instanceof Hustle_ConstantContact_Updatable_Model ) {
				// If the object is updatable, prepare it for update.
				return $obj->prepare_for_update();
			}

			// Sanitize numeric values.
			if ( is_numeric( $obj ) ) {
				return intval( $obj );
			}

			if ( is_array( $obj ) ) {
				// If the object is an array, sanitize each item.
				return array_map( array( $this, 'sanitize_data' ), $obj );
			}

			if ( is_string( $obj ) ) {
				// If the object is a string, sanitize it.
				return sanitize_text_field( $obj );
			}

			return $obj;
		}

		/**
		 * Prepare contact data for creation.
		 *
		 * @return array
		 */
		public function prepare_for_create() {
			$data = $this->prepare_for_update();
			// Set create_source to 'Account' by default.
			$data['create_source'] = 'Account';

			return $data;
		}
	}
}
