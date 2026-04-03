<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Custom Field Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_CustomField' ) ) {

	/**
	 * Class Hustle_ConstantContact_CustomField
	 *
	 * Model for custom field data
	 */
	class Hustle_ConstantContact_CustomField extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * Custom field ID
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Custom field name
		 *
		 * @var string
		 */
		public $name;

		/**
		 * Custom field type
		 *
		 * @var string
		 */
		public $type;

		/**
		 * Custom field updated at
		 *
		 * @var string
		 */
		public $updated_at;

		/**
		 * Custom field created at
		 *
		 * @var string
		 */
		public $created_at;

		/**
		 * Field value
		 *
		 * @var string
		 */
		public $value;

		/**
		 * Populate the model properties from an associative array
		 *
		 * @param array $data Associative array of data to populate the model.
		 */
		public function populate_from_data( array $data ) {
			parent::populate_from_data( $data );
			$this->id = isset( $data['custom_field_id'] ) ? $data['custom_field_id'] : '';
		}

		/**
		 * Convert to contact field format
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			return array(
				'custom_field_id' => sanitize_text_field( $this->id ),
				'value'           => sanitize_text_field( $this->value ),
			);
		}
	}
}
