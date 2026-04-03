<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_Module_Fields class file.
 *
 * @package Hustle
 * @since 7.8.11
 */
class Hustle_Module_Fields {
	const FIELDS = array(
		'emails' => array(
			'recipient'     => array(
				'type'        => 'email',
				'required_if' => 'automated_email',
			),
			'email_subject' => array(
				'type'        => 'string',
				'required_if' => 'automated_email',
			),
		),
	);
}
