<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Custom Field model class for Infusionsoft provider.
 *
 * Handles custom field data including field type, group association,
 * label, and field options for Infusionsoft integration.
 *
 * @since 1.0.0
 */
/**
 * Hustle_Infusion_Soft_Custom_Field class
 */
class Hustle_Infusion_Soft_Custom_Field {

	/**
	 * The custom field ID.
	 *
	 * @var int|null
	 */
	private $field_id;

	/**
	 * The field name.
	 *
	 * @var string
	 */
	private $name;
	/**
	 * The field type (text, email, select, etc.).
	 *
	 * @var string
	 */
	private $field_type;

	/**
	 * The display label for the field.
	 *
	 * @var string
	 */
	private $label;

	/**
	 * The value of the custom field.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Constructor for Infusionsoft custom field.
	 *
	 * @param string $name       The field name.
	 * @param string $field_type The field type.
	 * @param string $label      The field label.
	 * @param mixed  $value      The field value.
	 */
	public function __construct( $name, $field_type = '', $label = '', $value = null ) {
		$this->name       = $name;
		$this->field_type = $field_type;
		$this->label      = $label;
		$this->value      = $value;
	}

	/**
	 * Get the custom field ID.
	 *
	 * @return int|null The custom field ID.
	 */
	public function get_field_id() {
		return $this->field_id;
	}

	/**
	 * Set the custom field ID.
	 *
	 * @param int|null $field_id The custom field ID to set.
	 */
	public function set_field_id( $field_id ) {
		$this->field_id = $field_id;
	}

	/**
	 * Get field name.
	 *
	 * @return string The field name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get field type.
	 *
	 * @return string The field type.
	 */
	public function get_field_type() {
		return $this->field_type;
	}

	/**
	 * Set field type.
	 *
	 * @param string $field_type The field type to set.
	 */
	public function set_field_type( $field_type ) {
		$this->field_type = $field_type;
	}

	/**
	 * Get field label.
	 *
	 * @return string The field label.
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Set field label.
	 *
	 * @param string $label The label to set.
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * Get field value.
	 *
	 * @return mixed The field value.
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Set field value.
	 *
	 * @param mixed $value The value to set.
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Get properties of the custom field.
	 *
	 * Returns an associative array of field properties.
	 *
	 * @return array The properties of the custom field.
	 */
	public function get_props() {
		return array(
			'field_type' => $this->get_field_type(),
			'label'      => $this->get_label(),
		);
	}

	/**
	 * Get REST API data for the custom field.
	 *
	 * Returns an associative array formatted for API submission.
	 *
	 * @return array The API data for the custom field.
	 */
	public function prepare_for_rest_api() {
		return array(
			'field_type' => $this->get_mapped_field_type(),
			'label'      => $this->get_label(),
		);
	}

	/**
	 * Get mapped field type for REST API.
	 *
	 * Maps internal field types to API-compatible types.
	 *
	 * @return string The mapped field type.
	 */
	public function get_mapped_field_type() {
		$mapping = array(
			'name'       => 'Text',
			'text'       => 'Text',
			'number'     => 'WholeNumber',
			'email'      => 'Email',
			'phone'      => 'PhoneNumber',
			'url'        => 'Website',
			'datepicker' => 'Date',
			// Add more mappings as needed.
		);

		return isset( $mapping[ $this->field_type ] ) ? $mapping[ $this->field_type ] : 'Text';
	}
}
