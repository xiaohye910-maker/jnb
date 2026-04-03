<?php
/**
 * Main file for ActiveCampaign
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-activecampaign.php';
require_once __DIR__ . '/hustle-activecampaign-form-settings.php';
require_once __DIR__ . '/hustle-activecampaign-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_Activecampaign' );
