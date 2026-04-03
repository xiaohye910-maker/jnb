<?php
/**
 * Main file for Mailchimp
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-mailchimp.php';
require_once __DIR__ . '/hustle-mailchimp-form-settings.php';
require_once __DIR__ . '/hustle-mailchimp-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Mailchimp' );
