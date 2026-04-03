<?php
/**
 * Main file for ConvertKit
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-convertkit-api-intefrace.php';
require_once __DIR__ . '/hustle-convertkit.php';
require_once __DIR__ . '/hustle-convertkit-v2.php';
require_once __DIR__ . '/hustle-convertkit-form-settings.php';
require_once __DIR__ . '/hustle-convertkit-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_ConvertKit_V2' );
