<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for account management.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core;

/**
 * Interface for account management.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface AccountManagerInterface {

	/**
	 * Log in the user using the quicklaunch method.
	 *
	 * @param string $user_email The user email.
	 * @param string $user_password The user password.
	 * @return void
	 */
	public function login( string $user_email, string $user_password );

	/**
	 * Register the user using the quicklaunch method.
	 *
	 * @param string $user_email The user email.
	 * @param string $user_password The user password.
	 * @param string $user_phone The user phone.
	 * @return void
	 */
	public function register( string $user_email, string $user_password, string $user_phone ): void;
}
