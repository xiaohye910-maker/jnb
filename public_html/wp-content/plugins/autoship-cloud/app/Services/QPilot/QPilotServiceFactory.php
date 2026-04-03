<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName

namespace Autoship\Services\QPilot;

/**
 * Factory for creating QPilot service clients.
 *
 * @package Autoship\Services\QPilot
 * @since 1.0.0
 */
class QPilotServiceFactory {
	/**
	 * Create a new QPilot service client instance.
	 *
	 * @param string $api_url The QPilot API URL.
	 * @param string $token_auth The authentication token.
	 * @param int    $site_id The site ID.
	 * @param int    $user_id Optional user ID.
	 * @param string $source Optional source of the API call.
	 *
	 * @return QPilotServiceInterface
	 */
	public static function create( string $api_url, string $token_auth, int $site_id, int $user_id = 0, string $source = 'WordPress' ): QPilotServiceInterface { // phpcs:ignore
		$client = new QPilotServiceClient( $api_url );
		$client->set_token_auth( $token_auth );
		$client->set_site_id( $site_id );

		if ( $user_id > 0 ) {
			$client->set_user_id( $user_id );
		}

		$client->set_source( $source );

		return $client;
	}
}
