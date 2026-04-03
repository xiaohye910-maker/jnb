<?php
/**
 * Main file for Zapier
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-zapier-api.php';
require_once __DIR__ . '/hustle-zapier.php';
require_once __DIR__ . '/hustle-zapier-form-settings.php';
require_once __DIR__ . '/hustle-zapier-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Zapier' );
