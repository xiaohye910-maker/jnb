<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for site connection management.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core;

/**
 * Interface for site connection management.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface SitesManagerInterface {

	/**
	 * Retrieves the user sites.
	 *
	 * @param string $user_email The user email.
	 * @param string $user_password The user password.
	 * @return array
	 */
	public function get_user_sites( string $user_email, string $user_password ): array;

	/**
	 * Connects the current store to an autoship site.
	 *
	 * @return int The site identifier.
	 */
	public function connect_site(): int;
}
