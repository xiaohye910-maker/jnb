<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The service container for the Autoship modules.
 *
 * @package Autoship
 * @since 2.8.7
 */

namespace Autoship\Core;

use Exception;

/**
 * The service container for the Autoship modules.
 *
 * @package Autoship
 * @since 2.8.7
 */
class ServiceContainer {

	/**
	 * The registered services.
	 *
	 * @var array
	 */
	protected array $services = array();

	/**
	 * Registers a service.
	 *
	 * @param string   $key     The name of the service.
	 * @param callable $factory The factory to include services.
	 *
	 * @return void
	 */
	public function register( string $key, callable $factory ) {
		$this->services[ $key ] = $factory;
	}

	/**
	 * Retrieve a service.
	 *
	 * @param string $key The key of the registered service.
	 * @return mixed
	 *
	 * @throws Exception Thrown if the service is not registered.
	 */
	public function get( string $key ) {
		if ( ! isset( $this->services[ $key ] ) ) {
			throw new Exception( esc_html( "Service $key not registered." ) );
		}

		if ( is_callable( $this->services[ $key ] ) ) {
			$this->services[ $key ] = call_user_func( $this->services[ $key ] );
		}

		return $this->services[ $key ];
	}
}
