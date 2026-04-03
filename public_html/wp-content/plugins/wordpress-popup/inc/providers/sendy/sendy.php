<?php
/**
 * Main file for Sendy
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-sendy-api.php';
require_once __DIR__ . '/hustle-sendy.php';
require_once __DIR__ . '/hustle-sendy-form-settings.php';
require_once __DIR__ . '/hustle-sendy-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Sendy' );
