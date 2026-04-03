<?php
/**
 * Main file for Hubspot
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-hubspot.php';
require_once __DIR__ . '/hustle-hubspot-form-settings.php';
require_once __DIR__ . '/hustle-hubspot-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_HubSpot' );
