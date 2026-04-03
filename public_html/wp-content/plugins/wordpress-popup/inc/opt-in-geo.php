<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Geolocation utility functions
 *
 * @package Hustle
 */

/**
 * Class Opt_In_Geo
 */
class Opt_In_Geo {
	/**
	 * Site option key
	 *
	 * @var COUNTRY_IP_MAP
	 */
	const COUNTRY_IP_MAP = 'wpoi-county-id-map';

	/**
	 * Default GeoIP provider key
	 *
	 * @var DEFAULT_GEOIP_PROVIDER
	 */
	const DEFAULT_GEOIP_PROVIDER = 'ipwhois';

	/**
	 * Group name of ip list cache.
	 */
	private const IP_CACHE_GROUP = 'hustle_ip_list';

	/**
	 * Tries to get the public IP address of the current user.
	 *
	 * @return string The IP Address
	 */
	public static function get_user_ip() {
		// check for bot.
		if ( self::is_crawler() ) {
			return false;
		}

		// Check if request is from CloudFlare.
		if ( self::is_cloudflare() ) {
			$cf_ip = filter_input( INPUT_SERVER, 'HTTP_CF_CONNECTING_IP', FILTER_VALIDATE_IP );
			if ( $cf_ip ) {
				return apply_filters( 'hustle_user_ip', $cf_ip );
			}
		}

		$result = (object) array(
			'ip'       => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_SPECIAL_CHARS ),
			'proxy'    => false,
			'proxy_ip' => '',
		);

		/*
		 * This code tries to bypass a proxy and get the actual IP address of
		 * the visitor behind the proxy.
		 * Warning: These values might be spoofed!
		 */
		$ip_fields = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		$forwarded = false;
		foreach ( $ip_fields as $key ) {
			if ( true === array_key_exists( $key, $_SERVER ) ) {
				$ips = filter_input( INPUT_SERVER, $key );
				if ( ! $ips ) {
					// Skip empty or invalid values.
					continue;
				}

				foreach ( explode( ',', $ips ) as $ip ) {
					$ip = trim( $ip );

					if ( false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
						$forwarded = $ip;
						break 2;
					}
				}
			}
		}

		// If we found a different IP address than REMOTE_ADDR then it's a proxy!
		if ( ! empty( $forwarded ) && $forwarded !== $result->ip ) {
			$result->proxy    = true;
			$result->proxy_ip = $result->ip;
			$result->ip       = $forwarded;
		}

		if ( $result->ip ) {
			$user_ip = $result->ip;
		} else {
			$user_ip = 'UNKNOWN';
		}

		return apply_filters( 'hustle_user_ip', esc_attr( $user_ip ) );
	}

	/**
	 * Validates that the IP that made the request is from cloudflare
	 *
	 * @param String $ip - the ip to check.
	 * @return bool
	 */
	private static function validate_cloudflare_ip( $ip ) {
		$cloudflare_ips = array(
			'199.27.128.0/21',
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/12',
		);
		$is_cf_ip       = false;
		foreach ( $cloudflare_ips as $cloudflare_ip ) {
			if ( self::cloudflare_ip_in_range( $ip, $cloudflare_ip ) ) {
				$is_cf_ip = true;
				break;
			}
		}

		return $is_cf_ip;
	}

	/**
	 * Check if the cloudflare IP is in range
	 *
	 * @param String $ip - the current IP.
	 * @param String $range - the allowed range of cloudflare ips.
	 * @return bool
	 */
	private static function cloudflare_ip_in_range( $ip, $range ) {
		if ( strpos( $range, '/' ) === false ) {
			$range .= '/32';
		}

		// $range is in IP/CIDR format eg 127.0.0.1/24.
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal           = ip2long( $range );
		$ip_decimal              = ip2long( $ip );
		$wildcard_decimal        = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal         = ~$wildcard_decimal;

		return ( ( $ip_decimal & $netmask_decimal ) === ( $range_decimal & $netmask_decimal ) );
	}

	/**
	 * Check if there are any cloudflare headers in the request
	 *
	 * @return bool
	 */
	private static function cloudflare_requests_check() {
		$flag = true;

		if ( ! isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$flag = false;
		}
		if ( ! isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
			$flag = false;
		}
		if ( ! isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$flag = false;
		}
		if ( ! isset( $_SERVER['HTTP_CF_VISITOR'] ) ) {
			$flag = false;
		}

		return $flag;
	}

	/**
	 * Check if the request is from cloudflare. If it is, we get the IP
	 *
	 * @return bool
	 */
	private static function is_cloudflare() {
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP' );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR' );
		} else {
			$ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
		}
		if ( isset( $ip ) ) {
			$request_check = self::cloudflare_requests_check();
			if ( ! $request_check ) {
				return false;
			}

			$ip_check = self::validate_cloudflare_ip( $ip );

			return $ip_check;
		}

		return false;
	}

	/**
	 * Checks if the users IP address belongs to a certain country.
	 *
	 * @return bool
	 */
	public function get_user_country() {

		// check for bot.
		if ( self::is_crawler() ) {
			return false;
		}

		// Grab the users IP address.
		$ip = self::get_user_ip();

		// Deprecated.
		$country = apply_filters_deprecated( 'wpoi-get-user-country', array( '', $ip ), '4.6.0', 'hustle_get_user_country' );

		// See if an add-on provides the country for us.
		$country = apply_filters( 'hustle_get_user_country', $country, $ip );

		if ( empty( $country ) ) {
			$country = $this->get_country_from_ip( $ip );
		}

		if ( empty( $country ) ) {
			$country = 'XX';
		}

		return $country;
	}

	/**
	 * Returns a list of available ip-resolution services.
	 *
	 * @return array List of available webservices.
	 */
	private function get_geo_services() {
		static $geo_service = null;
		if ( null === $geo_service ) {
			$geo_service = array();

			$geo_service['freeip'] = (object) array(
				'label' => 'Free IP API',
				'url'   => 'https://free.freeipapi.com/api/json/%ip%',
				'type'  => 'json',
				'field' => array( 'country_code' ),
			);

			$geo_service['ipwhois'] = (object) array(
				'label' => 'IPWhois',
				'url'   => 'https://ipwho.is/%ip%',
				'type'  => 'json',
				'field' => array( 'country_code' ),
			);

			// Deprecated.
			$geo_service = apply_filters_deprecated( 'wpoi-geo-services', array( $geo_service ), '4.6.0', 'hustle_geo_services' );

			/**
			 * Allow other modules/plugins to register a geo service.
			 */
			$geo_service = apply_filters( 'hustle_geo_services', $geo_service );
		}

		return $geo_service;
	}

	/**
	 * Returns the lookup-service details
	 *
	 * @param string $type Type.
	 * @return object Service object for geo lookup
	 */
	private function get_service( $type = null ) {
		$service = false;
		if ( null === $type ) {
			// Deprecated.
			$remote_ip_url = apply_filters_deprecated( 'wpoi-remote-ip-url', array( '' ), '4.6.0', 'hustle_remote_ip_url' );
			$remote_ip_url = apply_filters( 'hustle_remote_ip_url', $remote_ip_url );
			if ( ! empty( $remote_ip_url ) ) {
				$type = '';
			} else {
				$type = self::DEFAULT_GEOIP_PROVIDER;
			}

			// Deprecated.
			$type = apply_filters_deprecated( 'wpoi-geo-type-service', array( $type ), '4.6.0', 'hustle_geo_type_service' );

			/**
			 * Allow to choose a geo service.
			 */
			$type = apply_filters( 'hustle_geo_type_service', $type );
		}

		if ( empty( $type ) ) {
			$service = (object) array(
				'url'   => $remote_ip_url,
				'label' => 'wp-config.php',
				'type'  => 'text',
			);
		} elseif ( 'geo_db' === $type ) {
			$service = (object) array(
				'url'   => 'db',
				'label' => __( 'Local IP Lookup Table', 'hustle' ),
				'type'  => 'text',
			);
		} else {
			$geo_service = $this->get_geo_services();
			if ( isset( $geo_service[ $type ] ) ) {
				$service = $geo_service[ $type ];
			} else {
				if ( WP_DEBUG ) {
					$message = sprintf(
						/* translators: %s: geoip provider name. */
						__( 'GeoIP provider %s does not exist. Switching to default.', 'hustle' ),
						$type
					);
					trigger_error( esc_html( $message ), E_USER_NOTICE ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
				}
				$service = $geo_service[ self::DEFAULT_GEOIP_PROVIDER ];
			}
		}

		return $service;
	}

	/**
	 * Queries an external geo-API to find the country of the specified IP.
	 *
	 * @param  string $ip The IP Address.
	 * @param  object $service Lookup-Service details.
	 * @return string The country code.
	 */
	private function country_from_api( $ip, $service ) {
		$country = false;

		if ( is_object( $service ) && ! empty( $service->url ) ) {
			$url      = str_replace( '%ip%', $ip, $service->url );
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {

				$body = isset( $response['body'] ) ? $response['body'] : '';

				if ( ! is_wp_error( $response )
					&& 200 === (int) $response['response']['code']
					&& 'XX' !== $response['body']
					|| false !== ( $body = file_get_contents( $url ) ) // phpcs:ignore
				) {
					if ( 'text' === $service->type ) {
						$country = trim( $body );
					} elseif ( 'json' === $service->type ) {
						$data = (array) json_decode( $body );
						if ( isset( $service->field ) ) {
							if ( is_array( $service->field ) ) {
								$keys  = $service->field;
								$value = $data;

								$element = array_shift( $keys );
								while ( $element ) {
									if ( is_array( $value ) ) {
										if ( isset( $value[ $element ] ) ) {
											$value = $value[ $element ];
										}
									} elseif ( is_object( $value ) ) {
										if ( isset( $value->$element ) ) {
											$value = $value->$element;
										}
									}
									if ( is_string( $value ) ) {
										$country = $value;
									}

									$element = array_shift( $keys );
								}
							} else {
								$country = isset( $data[ $service->field ] ) ? $data[ $service->field ] : null;
							}
						}
					}
				}
			}
		}

		return $country;
	}

	/**
	 * Updates ip-country map and stores in  options ( sitemeta ) table
	 *
	 * @param string $ip IP.
	 * @param string $country Country.
	 * @return mixed
	 */
	private function update_ip_county_map( $ip, $country ) {
		$country_ip_map        = $this->get_ip_county_map();
		$country_ip_map[ $ip ] = $country;

		update_option( self::COUNTRY_IP_MAP, $country_ip_map );
		return $country;
	}

	/**
	 * Retrieves ip-country map from options ( sitemeta ) table
	 *
	 * @return array
	 */
	private function get_ip_county_map() {
		return get_option( self::COUNTRY_IP_MAP, array() );
	}

	/**
	 * Checks if the user is a crawler/bot.
	 *
	 * @return bool
	 */
	private static function is_crawler() {

		$user_agent = filter_input( INPUT_SERVER, 'HTTP_USER_AGENT' );
		if ( $user_agent && preg_match( '/bot|crawler|ia_archiver|mediapartners-google|80legs|wget|voyager|baiduspider|curl|yahoo!|slurp/i', $user_agent ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns country string using ip address
	 *
	 * @param string $ip IP.
	 * @return string
	 */
	public function get_country_from_ip( $ip ) {
		// check for bot.
		if ( self::is_crawler() ) {
			return false;
		}

		$ip = (string) $ip;

		if ( '127.0.0.1' === $ip ) {
			return $this->update_ip_county_map( $ip, 'localhost' );
		}

		// See if we have it cached/saved already.
		$country = $this->find_saved_ip_country( $ip );
		if ( ! empty( $country ) ) {
			return $country;
		}

		// Query external geo-API.
		$country = $this->api_find_country( $ip );
		if ( ! empty( $country ) ) {
			// Save it for next time.
			$this->update_ip_country( $ip, $country );
			return $country;
		}

		return 'XX';
	}

	/**
	 * Returns country string using ip address
	 *
	 * @param string $ip_address IP address.
	 * @return string|false
	 */
	public function find_saved_ip_country( $ip_address ) {
		$ip_country = self::get_cached_ip_country( $ip_address );
		if ( $ip_country ) {
			return $ip_country;
		}

		$country_ip_map = $this->get_ip_county_map();
		if ( isset( $country_ip_map[ $ip_address ] ) ) {
			if ( ! empty( $country_ip_map[ $ip_address ] ) ) {
				return $country_ip_map[ $ip_address ];
			}
		}

		return false;
	}

	/**
	 * Get cached ip country.
	 *
	 * @param string $ip_address IP address.
	 * @return string|false
	 */
	private static function get_cached_ip_country( $ip_address ) {
		$hash = md5( $ip_address );
		return wp_cache_get( $hash, self::IP_CACHE_GROUP );
	}

	/**
	 * Queries an external geo-API to find the country of the specified IP.
	 *
	 * @param string $ip_address IP address.
	 * @return string
	 */
	private function api_find_country( $ip_address ) {
		$service = $this->get_service();
		return $this->country_from_api( $ip_address, $service );
	}

	/**
	 * Update ip country.
	 *
	 * @param string $ip_address IP address.
	 * @param string $country Country code.
	 * @return string
	 */
	public function update_ip_country( $ip_address, $country ) {
		self::cache_ip_country( $ip_address, $country );
		return $this->update_ip_county_map( $ip_address, $country );
	}

	/**
	 * Cache ip country.
	 *
	 * @param string $ip_address IP address.
	 * @param string $country Country code.
	 * @return void
	 */
	private static function cache_ip_country( $ip_address, $country ) {
		$hash = md5( $ip_address );
		wp_cache_set( $hash, $country, self::IP_CACHE_GROUP, DAY_IN_SECONDS );
	}
}
