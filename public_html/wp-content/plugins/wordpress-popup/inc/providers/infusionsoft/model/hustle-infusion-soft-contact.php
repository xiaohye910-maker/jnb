<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Contact class for Infusionsoft provider integration.
 *
 * Handles contact information including email, first name, and last name
 * for Infusionsoft opt-in functionality.
 *
 * @since 1.0.0
 */
/**
 * Opt_In_Infusionsoft_Contact class
 */
class Opt_In_Infusionsoft_Contact {
	/**
	 * Contact ID.
	 *
	 * @var int|null
	 */
	private $id;

	/**
	 * Contact email address.
	 *
	 * @var string
	 */
	private $email;

	/**
	 * Contact first name.
	 *
	 * @var string
	 */
	private $first_name;

	/**
	 * Contact last name.
	 *
	 * @var string
	 */
	private $last_name;

	/**
	 * Contact custom fields.
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Constructor for Infusionsoft contact.
	 *
	 * @param string   $email      Contact email address.
	 * @param string   $first_name Contact first name.
	 * @param string   $last_name  Contact last name.
	 * @param int|null $id         Contact ID.
	 */
	public function __construct( $email = '', $first_name = '', $last_name = '', $id = null ) {
		$this->email      = $email;
		$this->first_name = $first_name;
		$this->last_name  = $last_name;
		$this->id         = $id;
	}

	/**
	 * Get contact ID.
	 *
	 * @return int|null The contact ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set contact ID.
	 *
	 * @param int|null $id The contact ID to set.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get contact email address.
	 *
	 * @return string The contact email address.
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set contact email address.
	 *
	 * @param string $email The email address to set.
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Get contact first name.
	 *
	 * @return string The contact first name.
	 */
	public function get_first_name() {
		return $this->first_name;
	}

	/**
	 * Set contact first name.
	 *
	 * @param string $first_name The first name to set.
	 */
	public function set_first_name( $first_name ) {
		$this->first_name = $first_name;
	}

	/**
	 * Get contact last name.
	 *
	 * @return string The contact last name.
	 */
	public function get_last_name() {
		return $this->last_name;
	}

	/**
	 * Set contact last name.
	 *
	 * @param string $last_name The last name to set.
	 */
	public function set_last_name( $last_name ) {
		$this->last_name = $last_name;
	}

	/**
	 * Get custom fields.
	 *
	 * @return Hustle_Infusion_Soft_Custom_Field[] The custom fields.
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Set custom fields.
	 *
	 * @param Hustle_Infusion_Soft_Custom_Field[] $fields The custom fields to set.
	 */
	public function set_fields( $fields ) {
		$this->fields = $fields;
	}

	/**
	 * Add a custom field.
	 *
	 * @param Hustle_Infusion_Soft_Custom_Field $field The custom field to add.
	 */
	public function add_field( Hustle_Infusion_Soft_Custom_Field $field ) {
		$this->fields[ $field->get_name() ] = $field;
	}

	/**
	 * Get contact data formatted for API submission.
	 *
	 * @return array The contact data formatted for API.
	 */
	public function get_contact_data_for_api() {
		$data = array(
			'email_addresses' => array(
				array(
					'email' => $this->get_email(),
					'field' => 'EMAIL1',
				),
			),
			'given_name'      => $this->get_first_name(),
			'family_name'     => $this->get_last_name(),
		);

		if ( ! empty( $this->fields ) ) {
			$data['custom_fields'] = array();
			foreach ( $this->fields as $field ) {
				$field_id = $field->get_field_id();

				if ( ! empty( $field_id ) ) {
					$data['custom_fields'][] = array(
						'id'      => $field_id,
						'content' => $field->get_value(),
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Get contact data formatted for XML-RPC submission.
	 *
	 * @return array The contact data formatted for XML-RPC.
	 */
	public function get_contact_data_for_xmlrpc() {
		$data = array(
			'Email'     => $this->get_email(),
			'FirstName' => $this->get_first_name(),
			'LastName'  => $this->get_last_name(),
		);

		if ( ! is_null( $this->get_id() ) ) {
			$data['Id'] = $this->get_id();
		}

		return $data;
	}
}
