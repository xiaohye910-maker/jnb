<?php
/**
 * Main IContact file
 *
 * @package Icontact
 * @since 4.3.0
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-icontact.php';
require_once __DIR__ . '/hustle-icontact-form-settings.php';
require_once __DIR__ . '/hustle-icontact-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Icontact' );
