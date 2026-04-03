<?php
/**
 * Main file for CampaignMonitor
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
require_once __DIR__ . '/hustle-campaignmonitor.php';
require_once __DIR__ . '/hustle-campaignmonitor-form-settings.php';
require_once __DIR__ . '/hustle-campaignmonitor-form-hooks.php';
require_once __DIR__ . '/hustle-campaignmonitor-api.php';
Hustle_Providers::get_instance()->register( 'Hustle_Campaignmonitor' );
