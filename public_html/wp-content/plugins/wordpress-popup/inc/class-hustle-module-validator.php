<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_Module_Fields_Validator class file.
 *
 * @package Hustle
 * @since 7.8.11
 */
class Hustle_Module_Fields_Validator {

	/**
	 * Validates the module data before saving.
	 *
	 * @param array $fields The fields to validate, structured as [field_name] => rules.
	 * @param array $data The module data to validate.
	 * @return array An array containing 'is_valid' (boolean) and 'errors' (array) keys.
	 */
	public function validate( $fields, $data ) {
		$errors = array();

		foreach ( $fields as $field_name => $rules ) {
			$value = isset( $data[ $field_name ] ) ? $data[ $field_name ] : null;
			// Check for required fields.
			if ( isset( $rules['required'] ) && $rules['required'] && empty( $value ) ) {
				$errors[ $field_name ][] = sprintf(
					esc_html__( 'The field is required.', 'hustle' ),
				);
				continue;
			}

			// Check for required_if condition.
			if (
				isset( $rules['required_if'] ) &&
				isset( $data[ $rules['required_if'] ] )
			) {
				if ( 1 === (int) $data[ $rules['required_if'] ] ) {
					if ( empty( $value ) ) {
						$errors[ $field_name ][] = sprintf(
							esc_html__( 'The field is required.', 'hustle' ),
						);
						continue;
					}
				} else {
					// If the condition is not met, skip further validation for this field.
					continue;
				}
			}

			// If the field is not required and empty, skip further validation.
			if ( empty( $value ) ) {
				continue;
			}

			// Validate field type.
			switch ( $rules['type'] ) {
				case 'email':
					$list = array_map( 'trim', explode( ',', $value ) );
					foreach ( $list as $email ) {
						if ( '{email}' === $email ) {
							// If the placeholder is present, skip validation for this email.
							continue;
						}

						if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
							$errors[ $field_name ][] = esc_html__( 'Please enter a valid email address.', 'hustle' );
							break;
						}
					}
					break;
				default:
					break;
			}
		}

		return array(
			'is_valid' => empty( $errors ),
			'errors'   => $errors,
		);
	}
}
