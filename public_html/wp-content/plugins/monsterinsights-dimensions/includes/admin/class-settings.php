<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_Admin_Custom_Dimensions_Settings {
	/**
	 * @var MonsterInsights_Admin_Custom_Dimensions Holds a MonsterInsights_Admin_Custom_Dimensions instance.
	 */
	protected $dimensions;

	public function __construct() {
		$this->dimensions = new MonsterInsights_Admin_Custom_Dimensions();

		// Deactivate WPSEO errors
		register_deactivation_hook( 'wordpress-seo/wp-seo.php', array( $this, 'wpseo_deactivate' ) );
		register_deactivation_hook( 'wordpress-seo-premium/wp-seo-premium.php', array( $this, 'wpseo_deactivate' ) );
		add_action( 'admin_notices', array( $this, 'monsterinsights_display_wpseo_deactivated_notices' ) );

		// Deactivate AIOSEO errors
		register_deactivation_hook( 'all-in-one-seo-pack/all_in_one_seo_pack.php', array(
			$this,
			'aioseo_deactivate'
		) );
		register_deactivation_hook( 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php', array(
			$this,
			'aioseo_deactivate'
		) );
		add_action( 'admin_notices', array( $this, 'monsterinsights_display_aioseo_deactivated_notices' ) );
	}

	/**
	 * Hook used for preparing a notice when WPSEO is deactivated and SEO dimensions have been set.
	 */
	public function wpseo_deactivate() {
		if ( monsterinsights_is_wp_seo_active() ) {
			$error_message = sprintf(
				__( '%1$sWarning!%2$s Deactivating Wordpress SEO will stop your SEO custom dimensions from working in Google Analytics. Please visit your %3$sGoogle Analytics settings%4$s to see which custom dimensions have been disabled.', 'monsterinsights-dimensions' ),
				'<strong>',
				'</strong>',
				'<a href="' . admin_url( 'admin.php' ) . '?page=monsterinsights_settings#/conversions">',
				'</a>'
			);

			set_transient( 'monsterinsights_wpseo_deactivated_error', $error_message, MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Hook used for preparing a notice when AIOSEO is deactivated and SEO dimensions have been set.
	 */
	public function aioseo_deactivate() {
		if ( function_exists( 'monsterinsights_is_aioseo_active' ) && monsterinsights_is_aioseo_active() ) {
			$error_message = sprintf(
				__( '%1$sWarning!%2$s Deactivating All-In-One-SEO will stop your SEO custom dimensions from working in Google Analytics. Please visit your %3$sGoogle Analytics settings%4$s to see which custom dimensions have been disabled.', 'monsterinsights-dimensions' ),
				'<strong>',
				'</strong>',
				'<a href="' . admin_url( 'admin.php' ) . '?page=monsterinsights_settings#/conversions">',
				'</a>'
			);

			set_transient( 'monsterinsights_aioseo_deactivated_error', $error_message, MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Hook used for outputting an admin notice when transient has been set on deactivation of WPSEO.
	 */
	public function monsterinsights_display_wpseo_deactivated_notices() {
		$wpseo_deactivated_error = get_transient( 'monsterinsights_wpseo_deactivated_error' );

		if ( ! empty( $wpseo_deactivated_error ) ) {
			echo '<div class="error"><p>' . wp_kses_post( $wpseo_deactivated_error ) . '</p></div>';
			delete_transient( 'monsterinsights_wpseo_deactivated_error' );
		}
	}

	/**
	 * Hook used for outputting an admin notice when transient has been set on deactivation of AIOSEO.
	 */
	public function monsterinsights_display_aioseo_deactivated_notices() {
		$aioseo_deactivated_error = get_transient( 'monsterinsights_aioseo_deactivated_error' );

		if ( ! empty( $aioseo_deactivated_error ) ) {
			echo '<div class="error"><p>' . wp_kses_post( $aioseo_deactivated_error ) . '</p></div>';
			delete_transient( 'monsterinsights_aioseo_deactivated_error' );
		}
	}
}
