<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Updatable model for Constant Contact API
 *
 * This class provides functionality for models that can be updated
 *
 * @package Hustle_ConstantContact
 */
abstract class Hustle_ConstantContact_Updatable_Model extends Hustle_ConstantContact_Base_Model {

	/**
	 * Prepare the model for update by sanitizing and validating data
	 *
	 * @return array Prepared data for update.
	 */
	abstract public function prepare_for_update();
}
