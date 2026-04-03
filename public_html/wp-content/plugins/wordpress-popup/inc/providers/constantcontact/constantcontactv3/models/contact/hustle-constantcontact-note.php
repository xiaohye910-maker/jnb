<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact v3 Note Model
 *
 * @package Hustle
 */

if ( ! class_exists( 'Hustle_ConstantContact_Note' ) ) {

	/**
	 * Class Hustle_ConstantContact_Note
	 *
	 * Model for note data
	 */
	class Hustle_ConstantContact_Note extends Hustle_ConstantContact_Updatable_Model {

		/**
		 * Note ID
		 *
		 * @var string
		 */
		public $note_id;

		/**
		 * Created at timestamp
		 *
		 * @var string
		 */
		public $created_at;

		/**
		 * Note content
		 *
		 * @var string
		 */
		public $content;

		/**
		 * Prepare data for update
		 *
		 * @return array
		 */
		public function prepare_for_update() {
			return array(
				'note_id'    => sanitize_text_field( $this->note_id ),
				'created_at' => sanitize_text_field( $this->created_at ),
				'content'    => sanitize_textarea_field( $this->content ),
			);
		}
	}
}
