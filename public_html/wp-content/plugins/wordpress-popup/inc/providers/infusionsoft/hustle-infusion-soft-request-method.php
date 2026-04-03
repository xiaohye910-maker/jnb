<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_Infusion_Soft_Request_Method class
 *
 * @package Hustle
 */

if ( class_exists( 'Opt_In_Infusionsoft_Request_Method' ) ) {
	return;
}

/**
 * Class Hustle_Infusion_Soft_Request_Method
 */
final class Opt_In_Infusionsoft_Request_Method {
	const HTTP_GET    = 1;
	const HTTP_POST   = 2;
	const HTTP_PUT    = 3;
	const HTTP_DELETE = 4;
	const HTTP_PATCH  = 5;
}
