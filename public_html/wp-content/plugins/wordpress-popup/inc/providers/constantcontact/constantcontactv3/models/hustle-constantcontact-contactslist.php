<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle Constant Contact Contacts List Model
 *
 * @package Hustle
 * @subpackage ConstantContactV3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Hustle_ConstantContact_ContactsList
 *
 * Model for Constant Contact contacts list
 */
class Hustle_ConstantContact_ContactsList extends Hustle_ConstantContact_Base_Model {

	/**
	 * List ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * List name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * List description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Whether the list is marked as favorite
	 *
	 * @var bool
	 */
	public $favorite;

	/**
	 * List creation date
	 *
	 * @var string
	 */
	public $created_at;

	/**
	 * List last update date
	 *
	 * @var string
	 */
	public $updated_at;

	/**
	 * List deletion date
	 *
	 * @var string|null
	 */
	public $deleted_at;

	/**
	 * Number of members in the list
	 *
	 * @var int
	 */
	public $membership_count;

	/**
	 * Populate the model properties from an associative array
	 *
	 * @param array $data Associative array of data to populate the model.
	 */
	public function populate_from_data( array $data ) {
		parent::populate_from_data( $data );
		$this->id = isset( $data['list_id'] ) ? $data['list_id'] : '';
	}
}
