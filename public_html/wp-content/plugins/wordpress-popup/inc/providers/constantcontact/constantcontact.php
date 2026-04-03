<?php
/**
 * Main file for ConstantContact
 *
 * @package Hustle
 */

/**
 * Direct Load
 */
// Include required model classes.

require_once __DIR__ . '/constantcontactv3/models/hustle-constantcontact-base-model.php';
require_once __DIR__ . '/constantcontactv3/models/hustle-constantcontact-updatable-model.php';

// Contact models.
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-email-address.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-custom-field.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-phone-number.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-street-address.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-note.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-sms-channel.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-sms-consent.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-contacts-response.php';
require_once __DIR__ . '/constantcontactv3/models/contact/hustle-constantcontact-contact.php';

// Contacts list.
require_once __DIR__ . '/constantcontactv3/models/hustle-constantcontact-contactslist.php';

// Account info.
require_once __DIR__ . '/constantcontactv3/models/account/hustle-constantcontact-address.php';
require_once __DIR__ . '/constantcontactv3/models/account/hustle-constantcontact-company-logo.php';
require_once __DIR__ . '/constantcontactv3/models/account/hustle-constantcontact-account-info.php';

require_once __DIR__ . '/constantcontactv3/hustle-constantcontact-api-v3-client.php';

require_once __DIR__ . '/hustle-constantcontact-api-interface.php';
require_once __DIR__ . '/hustle-constantcontact-oauth.php';
require_once __DIR__ . '/hustle-constantcontact.php';
require_once __DIR__ . '/hustle-constantcontact-form-settings.php';
require_once __DIR__ . '/hustle-constantcontact-form-hooks.php';
Hustle_Providers::get_instance()->register( 'Hustle_ConstantContact' );
