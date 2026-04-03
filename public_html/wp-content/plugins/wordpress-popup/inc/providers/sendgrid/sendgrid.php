<?php
/**
 * Main file for Sendgrid
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-sendgrid.php';
require_once __DIR__ . '/hustle-sendgrid-form-settings.php';
require_once __DIR__ . '/hustle-sendgrid-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_SendGrid' );
