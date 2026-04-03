<?php
/**
 * Main file for Mautic
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-mautic.php';
require_once __DIR__ . '/hustle-mautic-form-settings.php';
require_once __DIR__ . '/hustle-mautic-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Mautic' );
