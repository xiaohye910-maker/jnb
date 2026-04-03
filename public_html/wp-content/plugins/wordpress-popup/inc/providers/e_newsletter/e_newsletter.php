<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Main file for E Newsletter
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-e-newsletter.php';
require_once __DIR__ . '/hustle-e-newsletter-form-settings.php';
require_once __DIR__ . '/hustle-e-newsletter-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_E_Newsletter' );
