<?php
/**
 * Main file for Aweber
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-aweber.php';
require_once __DIR__ . '/hustle-aweber-form-settings.php';
require_once __DIR__ . '/hustle-aweber-form-hooks.php';
require_once __DIR__ . '/hustle-addon-aweber-exception.php';
require_once __DIR__ . '/hustle-addon-aweber-form-settings-exception.php';
require_once __DIR__ . '/lib/class-wp-aweber-api.php';
Hustle_Providers::get_instance()->register( 'Hustle_Aweber' );
