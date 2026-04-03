<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Interface for the installer.
 *
 * @package Autoship
 * @since 2.11.0
 */

namespace Autoship\Core;

/**
 * Interface for the installer.
 *
 * @package Autoship
 * @since 2.11.0
 */
interface InstallerInterface {

	/**
	 * Gets the value indicating whether the installation is registered or not.
	 *
	 * @return bool
	 */
	public function is_installed(): bool;

	/**
	 * Performs the registration of the current installation.
	 *
	 * @return bool
	 */
	public function install(): bool;

	/**
	 * Indicates whether the installation has an ID or not.
	 *
	 * @return bool True if the installation has an ID, false otherwise.
	 */
	public function has_installation_id(): bool;

	/**
	 * Gets the installation id.
	 *
	 * @return false|string The installation id string, false when the key is not found.
	 */
	public function get_installation_id();

	/**
	 * Gets the installation key.
	 *
	 * @return false|string The installation key string, false when the key is not found.
	 */
	public function get_installation_key();
}
