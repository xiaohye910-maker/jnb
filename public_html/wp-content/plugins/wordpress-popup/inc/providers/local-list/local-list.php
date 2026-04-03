<?php
/**
 * Main file for Local list
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-local-list.php';
require_once __DIR__ . '/hustle-local-list-form-settings.php';
require_once __DIR__ . '/hustle-local-list-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Local_List' );
