<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Base model for Constant Contact API
 *
 * This class provides common functionality for all models
 *
 * @package Hustle_ConstantContact
 */
abstract class Hustle_ConstantContact_Base_Model {

	/**
	 * Constructor for the base model
	 *
	 * @param array $data Associative array of data to populate the model.
	 */
	public function __construct( array $data = array() ) {
		$this->populate_from_data( $data );
	}

	/**
	 * Populate the model properties from an associative array
	 *
	 * @param array $data Associative array of data to populate the model.
	 */
	public function populate_from_data( array $data ) {
		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}
	}
}
