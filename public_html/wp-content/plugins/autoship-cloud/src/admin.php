<?php
/**
 * The admin page functions.
 *
 * @package Autoship
 * @since 1.0.0
 */

use Autoship\Core\Plugin;
use Autoship\Services\Nextime\NextimeSettingsInterface;

/**
 * Checks the rights based on the supplied roles/caps.
 *
 * @param string $filter The rights filter name to apply.
 * @param array  $caps Optional. An array of roles or caps to check. If left empty then defaults to true unless filtered.
 * @param int    $user_id Optional. The user id to check against. If not supplied then the current user is used.
 *
 * @return bool True if they have rights, otherwise false.
 */
function autoship_rights_checker( $filter = '', $caps = array(), $user_id = 0 ) {

	// Get the god role or cap.
	$global_editor_role = apply_filters( 'autoship_global_editor_role_cap', array( 'administrator' ), $filter );

	// If there is a specific user check only fail if the current user does not have a god role and their ID doesn't match the supplied id.
	// Keep loose comparison for backwards compatibility.
	if ( $user_id && ! autoship_rights_checker( $filter, $global_editor_role ) && ( get_current_user_id() != $user_id ) ) { // phpcs:ignore
		return false;
	}

	$valid         = false;
	$caps_or_roles = ! empty( $filter ) ? apply_filters( $filter, $caps ) : $caps;

	foreach ( $caps_or_roles as $cap ) {

		if ( current_user_can( $cap ) ) {
			$valid = true;
			break;
		}
	}

	return empty( $caps_or_roles ) ? true : $valid;
}

/**
 * Outputs the Modal HTML
 *
 * @param string $modal_id The modal id.
 * @param string $content The html content for the modal.
 * @param string $classes Additional classes.
 * @param string $footer_content The html content for the modal footer.
 * @param bool   $close Whether to show the close button.
 */
function autoship_generate_modal( $modal_id, $content, $classes = '', $footer_content = '', $close = true ) {
	?>

	<!-- Autoship <?php echo esc_attr( $modal_id ); ?> Modal -->
	<div id="<?php echo esc_attr( $modal_id ); ?>"
			class="autoship-modal autoship-<?php echo esc_attr( $modal_id ); ?>-modal <?php echo esc_attr( $classes ); ?>">

		<!-- Autoship Modal content -->
		<div class="autoship-modal-content">
			<?php if ( ! empty( $close ) ) : ?>
				<span class="close">&times;</span>
			<?php endif; ?>
			<div class="autoship-modal-inner-content">
				<?php
					echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
			<?php if ( ! empty( $footer_content ) ) : ?>
				<div class="autoship-modal-footer-content">
				<?php
					echo $footer_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Retrieves the Start and End Times for the Sites Processing Window
 *
 * @return array|bool An array containing the start and end times else false
 *                    if Processing Window is not supported
 */
function autoship_get_api_processing_window() {
	$settings = autoship_get_remote_saved_site_settings( true );

	return ! is_wp_error( $settings ) && isset( $settings['supportsProcessingWindow'] ) && $settings['supportsProcessingWindow'] ? array(
		'startTime' => empty( $settings['orderProcessingStartTime'] ) ? null : $settings['orderProcessingStartTime'],
		'endTime'   => empty( $settings['orderProcessingEndTime'] ) ? null : $settings['orderProcessingEndTime'],
	) : false;
}

// ==========================================================
// Page URL Functions
// ==========================================================

/**
 * Returns the url for the Admin Autoship Cloud > Settings tab
 *
 * @return string The url.
 */
function autoship_admin_settings_page_url() {
	return admin_url( '/admin.php?page=autoship' );
}

/**
 * Returns the url for the Admin Autoship Cloud > Products tab
 *
 * @return string The url.
 */
function autoship_admin_products_page_url() {
	return admin_url( '/admin.php?page=products' );
}

/**
 * Returns the url for an Admin Autoship Cloud > Settings tab
 *
 * @param string $tab The tab name.
 *
 * @return string The url.
 */
function autoship_admin_settings_tab_url( $tab ) {
	return add_query_arg( 'tab', $tab, admin_url( '/admin.php?page=autoship' ) );
}

// ==========================================================
// Options Retrieval Functions
// ==========================================================

/**
 * Quick check to see if this is a new setup
 * Checks to see if autoship_client_id, autoship_client_secret
 * or autoship_token_auth are empty.
 *
 * @return bool Ture if new or false if not.
 */
function autoship_is_new() {
	return ( empty( autoship_get_settings_fields( 'autoship_client_id', true ) ) || empty( autoship_get_settings_fields( 'autoship_client_secret', true ) ) || empty( autoship_get_settings_fields( 'autoship_token_auth', true ) ) );
}

/**
 * Quick check to see if credentials exist
 * Checks to see if autoship_client_id or autoship_client_secret
 * are empty.
 *
 * @return bool Ture if new or false if not.
 */
function autoship_has_credentials() {
	return ! ( empty( autoship_get_settings_fields( 'autoship_client_id', true ) ) || empty( autoship_get_settings_fields( 'autoship_client_secret', true ) ) );
}

/**
 * Quick check to see if auth token exists
 * Checks to see if autoship_token_auth is empty.
 *
 * @return bool Ture if new or false if not.
 */
function autoship_has_auth_token() {
	return ! empty( autoship_get_settings_fields( 'autoship_token_auth', true ) );
}

/**
 * Quick check to see if the legacy fee lines are enabled.
 *
 * @return bool Ture if Fee lines are enabled.
 */
function autoship_rest_order_fee_lines_enabled() {
	return 'yes' === autoship_get_settings_fields( 'autoship_rest_order_fee_lines_enabled', true );
}

/**
 * Quick check to see if the Display Next Occurrence Offset is enabled.
 *
 * @return bool Ture if enabled.
 */
function autoship_display_next_occurrence_offset_enabled() {
	return 'yes' === autoship_get_settings_fields( 'autoship_display_next_occurrence_offset', true );
}

// ==========================================================
// Data Retrieval Functions
// ==========================================================

/**
 * Retrieves the site/blog info.
 *
 * @param string $attribute Optional. The specific value to retrieve.
 *
 * @return string|array An array when no value is supplied.
 */
function autoship_get_site_info( $attribute = null ) {

	$site_info = array(
		'url'   => get_bloginfo( 'url' ),
		'type'  => 'WooCommerce',
		'name'  => get_bloginfo( 'title' ),
		'email' => get_bloginfo( 'admin_email' ),
	);

	return isset( $attribute ) ? $site_info[ $attribute ] : $site_info;
}

/**
 * Retrieves the Autoship Oauth Redirect Page for the site.
 *
 * @return string
 */
function autoship_get_redirect_uri() {
	return admin_url( '/admin-ajax.php?action=autoship_oauth2' );
}

/**
 * Retrieves the Autoship API Authorization token for the site.
 *
 * @return string
 */
function autoship_get_token_auth() {
	return autoship_get_settings_fields( 'autoship_token_auth', true );
}

/**
 * Retrieves the Autoship API Refresh token for the site.
 *
 * @return string
 */
function autoship_get_refresh_token() {
	return autoship_get_settings_fields( 'autoship_refresh_token', true );
}

/**
 * Retrieves the Autoship API Client Secret for the site.
 *
 * @return string
 */
function autoship_get_client_secret() {
	return autoship_get_settings_fields( 'autoship_client_secret', true );
}

/**
 * Retrieves the Autoship Token Expiration Time for the site.
 *
 * @return int The time as a Unix timestamp.
 */
function autoship_get_token_expires_in() {
	return autoship_get_settings_fields( 'autoship_token_expires_in', true );
}

/**
 * Retrieves the Autoship Token Creation Time for the site.
 *
 * @return int The time as a Unix timestamp.
 */
function autoship_get_token_created_at() {
	return autoship_get_settings_fields( 'autoship_token_created_at', true );
}

/**
 * Retrieves the Autoship User id for the site.
 *
 * @return int The id.
 */
function autoship_get_user_id() {
	$id = autoship_get_settings_fields( 'autoship_user_id', true );

	return ! empty( $id ) ? intval( $id ) : null;
}

/**
 * Retrieves the HTML Content to Display on the Scheduled Orders page
 * header.
 *
 * @return string The content.
 */
function autoship_get_scheduled_orders_html() {
	return autoship_get_settings_fields( 'autoship_scheduled_orders_html', true );
}

/**
 * Retrieves the HTML Content to Display on the Scheduled Orders page
 * when no scheduled orders exist.
 *
 * @return string The content.
 */
function autoship_get_scheduled_orders_body_html() {
	return autoship_get_settings_fields( 'autoship_scheduled_orders_body_html', true );
}

/**
 * Retrieves the Author id of the current API key set.
 *
 * @return int wc user id.
 */
function autoship_get_api_keys_author() {
	$author_id = autoship_get_settings_fields( 'autoship_api_keys_author', true );

	return ! empty( $author_id ) ? intval( $author_id ) : null;
}

/**
 * Retrieves the Author id of the current API key set and if not found
 * sets it based on the last added key set.
 *
 * @return int wc user id.
 */
function autoship_get_refreshed_api_keys_author() {
	$author = autoship_get_api_keys_author();
	if ( empty( $author ) ) {

		$author = 0;
		$keys   = autoship_get_api_keys();
		if ( ! empty( $keys ) ) {
			$last_added = end( $keys );
			$author     = $last_added->user_id;
			update_option( 'autoship_api_keys_author', $author );
		}
	}

	return $author;
}

/**
 * Retrieves the current QPilot API Keys from the woocommerce_api_keys table.
 *
 * @return array An array of current QPilot Keys
 */
function autoship_get_api_keys() {

	global $wpdb;

	$description = __( 'Autoship - QPilot', 'autoship' );
	$table       = $wpdb->prefix . 'woocommerce_api_keys';

	// Get the API keys.
	$keys = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare( "SELECT key_id, user_id, description, permissions, truncated_key, last_access FROM $table WHERE description = %s", $description ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		OBJECT_K
	);

	return $keys;
}

/**
 * Retrieves the current QPilot Site ID
 *
 * @return NULL|int The site id
 */
function autoship_get_site_id() {
	$site_id = autoship_get_settings_fields( 'autoship_site_id', true );

	return ! empty( $site_id ) ? intval( $site_id ) : null;
}

/**
 * Retrieves the current QPilot Client ID
 *
 * @return int The Client id
 */
function autoship_get_client_id() {
	$client_id = autoship_get_settings_fields( 'autoship_client_id', true );

	return ! empty( $client_id ) ? intval( $client_id ) : null;
}

/**
 * Retrieves the current Scheduled Order UI Version
 *
 * @return string The UI Type
 */
function autoship_get_scheduled_orders_display_version() {
	return autoship_get_settings_fields( 'autoship_scheduled_orders_display_version', true );
}

/**
 * Retrieves the current Scheduled Order UI Version
 *
 * @return string The UI Type
 */
function autoship_get_display_portal_without_scheduled_orders() {
	return 'yes';
}


/**
 * Retrieves the current Modal Sizes
 *
 * @return string The UI Type
 */
function autoship_get_info_modal_sizes() {

	return apply_filters(
		'autoship_info_modal_sizes',
		array(
			'small'  => '300px',
			'medium' => '500px',
			'large'  => '800px',
			'full'   => 'auto',
		)
	);
}

/**
 * Retrieves the Editable Shipping Rate option Enabled setting
 *
 * @return string yes for enabled else false
 */
function autoship_get_editable_shipping_rate_option() {
	return autoship_get_settings_fields( 'autoship_editable_shipping_rate_enabled', true );
}


/**
 * Retrieves the COD Payments Support option setting
 *
 * @return string yes for enabled else no
 */
function autoship_get_support_cod_payments_option() {
	$val = autoship_get_settings_fields( 'autoship_support_cod_payments', true );

	return empty( $val ) ? 'no' : $val;
}

/**
 * Retrieves the Legacy Support for using of Qpilot data
 *
 * @return string yes for enabled else false
 */
function autoship_get_legacy_support_qpilot_products_data() {
	$val = autoship_get_settings_fields( 'autoship_legacy_qpilot_products_data', true );

	return empty( $val ) ? 'no' : $val;
}

/**
 * Retrieves the current Scheduled Order UI Version
 *
 * @return string yes for enabled else false
 */
function autoship_get_scheduled_order_upsell_carousel() {
	$val = autoship_get_settings_fields( 'autoship_scheduled_order_upsell_carousel', true );

	return empty( $val ) ? 'no' : $val;
}

/**
 * Retrieves the current Scheduled Order UI Version
 *
 * @return string yes for enabled else false
 */
function autoship_get_scheduled_order_upsell_disable_carousel_js() {
	$val = autoship_get_settings_fields( 'autoship_scheduled_order_upsell_disable_carousel_js', true );

	return empty( $val ) ? 'no' : $val;
}

// ==========================================================
// API Processing Functions
// ==========================================================

/**
 * Retrieves the current Site Processing Version
 *
 * @return string The Version
 */
function autoship_get_saved_site_processing_version() {
	return autoship_get_settings_fields( 'autoship_saved_site_processing_version', true );
}

/**
 * Sets the current Site Processing Version
 *
 * @param string $version The Version to save.
 */
function autoship_set_saved_site_processing_version( $version = 'v2' ) {
	return update_option( 'autoship_saved_site_processing_version', $version );
}


// ==========================================================
// API Functions
// ==========================================================

/**
 * Retrieves the current site's subscription status.
 *
 * @return string The current status.
 */
function autoship_get_subscription_status() {

	$ttl       = apply_filters( 'autoship_subscription_status_ttl', 15 * 60 );
	$cache_key = '_autoship_subscription_status';
	$exp_key   = '_autoship_subscription_status_expiration';

	$now = time();
	$exp = (int) get_option( $exp_key, 0 );
	$val = get_option( $cache_key, null );

	if ( null !== $val && $exp > $now ) {
		return $val;
	}

	// Site status.
	$subscription_status = null;
	$autoship_token      = autoship_get_token_auth();
	if ( null !== $autoship_token ) {
		try {
			$client              = autoship_get_default_client();
			$user                = $client->get_default_user();
			$subscription_status = $user->subscriptionStatus; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		} catch ( Exception $e ) {
			// translators: %1$s is the exception code, %2$s is the exception message.
			autoship_log_entry( __( 'Autoship Subscription Status', 'autoship' ), sprintf( 'An %1$s Exception Occurred when attempting to retrieve the Subscription Status. Additional Details: %2$s', $e->getCode(), $e->getMessage() ) );
		}
	}

	update_option( $cache_key, $subscription_status, false );
	update_option( $exp_key, $now + $ttl, false );

	return $subscription_status;
}

/**
 * Gets the current sites Settings from QPilot.
 *
 * @param bool $sitehealth Optional. The current API Health flag.
 *
 * @return array|WP_Error The site settings or an error
 */
function autoship_get_remote_site_settings( $sitehealth = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

	$site_data = autoship_get_site_order_settings();

	if ( ! is_wp_error( $site_data ) ) {

		update_option( '_autoship_site_settings_expiration', time() + apply_filters( 'autoship_site_settings_expiration_duration', 900 ) );
		update_option( '_autoship_site_settings', $site_data, false );
	}

	return $site_data;
}

add_action( 'autoship_init_integration_test_complete', 'autoship_get_remote_site_settings', 10, 1 );

/**
 * Gets the current site's saved Qpilot Settings.
 *
 * @param bool $refresh Pull the latest settings from the API.
 * @param bool $reset_cache Optional. If true, reset the cache for this option.
 */
function autoship_get_remote_saved_site_settings( $refresh = false, $reset_cache = false ) {

	// Get the Site Settings Expiration.
	$expiration = $refresh ? 0 : get_option( '_autoship_site_settings_expiration', 0 );

	// Check if it's expired and if so refresh via the API.
	if ( $expiration < time() ) {

		// Delete the cache for this option since it's been refreshed.
		wp_cache_delete( '_autoship_site_settings', 'options' );

		// Since the settings need to be refreshed pull them via the API.
		return autoship_get_remote_site_settings();
	}

	// Delete the cache for this option since it's been refreshed.
	if ( $reset_cache ) {
		wp_cache_delete( '_autoship_site_settings', 'options' );
	}

	// Now retrieve the settings value.
	return get_option( '_autoship_site_settings', array() );
}

/**
 * Pushes site/blog Metadata to Qpilot.
 *
 * @param array $data The site metadata keys and values to push.
 *
 * @return bool|WP_Error True on success else WP_Error.
 */
function autoship_push_site_metadata( $data = array() ) {

	// Create the QPilot Client.
	$client = new QPilotClient();

	try {

		// Finally lets upsert the site info.
		$update_response = $client->update_site_metadata( $data );

	} catch ( Exception $e ) {

		// Only log the error if the metadata can't be pushed.
		autoship_log_entry( __( 'Autoship Site Metadata Push Exception', 'autoship' ), sprintf( 'An %s Exception Occurred when attempting to send Site Metadata to QPilot. Additional Details: %s', $e->getCode(), $e->getMessage() ) );

		$notice = autoship_expand_http_code( $e->getCode() );

		return new WP_Error( 'Site Settings Retrieval Failed', __( $notice['desc'], 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	}

	return true;
}

/**
 * Update URLs site settings when WP Permalinks options get changed
 *
 * @return bool|WP_Error True on success else WP_Error.
 */
function autoship_push_my_account_relative_urls() {
	// Create the QPilot Client.
	$client = new QPilotClient();
	$data   = array(
		'ScheduledOrdersClientPageUrl' => autoship_get_endpoint_url( 'scheduled-orders', '', str_replace( home_url(), '', wc_get_page_permalink( 'myaccount' ) ) ),
		'PaymentMethodsPageUrl'        => str_replace( home_url(), '', wc_get_account_endpoint_url( 'payment-methods' ) ),
	);

	try {

		// Finally lets upsert the site info.
		$update_response = $client->update_my_account_relative_urls( $data );

	} catch ( Exception $e ) {

		// Only log the error if the metadata can't be pushed.
		autoship_log_entry( __( 'Autoship Site Data Push Exception', 'autoship' ), sprintf( 'An %s Exception Occurred when attempting to send Site data to QPilot. Additional Details: %s', $e->getCode(), $e->getMessage() ) );

		$notice = autoship_expand_http_code( $e->getCode() );

		return new WP_Error( 'Updating Site My Account URLs failed', __( $notice['desc'], 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	}

	return true;
}

add_action( 'update_option_permalink_structure', 'autoship_push_my_account_relative_urls', 20 );


// ==========================================================
// Autoship Admin Message Queue
// ==========================================================

/**
 * Retrieves the messages that were added to the Notification System
 *
 * @param string $queue Optional. The message queue to pull from. Defaults to 'autoship_messages'.
 * @param bool   $reset Optional. If true clear the cookie after getting the messages. Default false.
 * @param bool   $persist Optional. True to get message to db vs cookie.
 *
 * @return array An array of messages.
 */
function autoship_get_messages( $queue = 'autoship_messages', $reset = false, $persist = false ) {

	if ( $persist ) {

		$messages     = get_option( 'autoship_persistant_notices', array() );
		$all_messages = $messages;

		if ( ! empty( $queue ) ) {
			$messages = isset( $messages[ $queue ] ) ? $messages[ $queue ] : array();
		}

		if ( $reset && isset( $all_messages[ $queue ] ) ) {
			unset( $all_messages[ $queue ] );
			update_option( 'autoship_persistant_notices', $all_messages );
		}
	} else {

		// Check for empty queue.
		if ( empty( $_COOKIE[ $queue ] ) ) {
			return array();
		}

		// Else get the cookie contents and decode them.
		$messages = json_decode( base64_decode( $_COOKIE[ $queue ] ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.PHP.DiscouragedPHPFunctions.obfuscation,WordPress.PHP.DiscouragedPHPFunctions.obfuscation,WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode_base64_decode_base64_decode_base64_decode,WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( $reset ) {

			// empty the cookie queue and reset.
			setcookie( $queue, '', time() - 3600 );
			$_COOKIE[ $queue ] = '';
		}
	}

	return $messages;
}

/**
 * Adds a messages to the Notification System
 *
 * @param string $message The actual message to add.
 * @param string $type Optional  The type of notification.
 * @param string $queue Optional. The message queue to add the message too. Defaults to 'autoship_messages'.
 * @param bool   $persist Optional  The notice storage type.
 */
function autoship_add_message( $message, $type = 'updated', $queue = 'autoship_messages', $persist = false ) {
	// Grab the current messages from the message queue.
	$messages = autoship_get_messages( $queue, false, $persist );

	// Currently limited to 4 messages to display - why?.
	if ( count( $messages ) > 4 ) {

		// Message limit has been reached.
		$message = __( 'Autoship message limit reached. Some messages are not shown.', 'autoship' );
		$type    = 'error';
	}

	// Loop through the current messages to see if the message
	// Currently being added already exists so we don't repeat ourselves.
	foreach ( $messages as $existing_message ) {
		if ( $existing_message['message'] === $message && $existing_message['type'] === $type ) {
			return;
		}
	}

	$messages[] = array(
		'message' => $message,
		'type'    => $type,
	);

	if ( $persist ) {

		// Grab all messages.
		$all_messages           = autoship_get_messages( '', false, $persist );
		$all_messages[ $queue ] = $messages;
		update_option( 'autoship_persistant_notices', $all_messages );

	} else {

		// Encode the message and add it to the cookie.
		$messages_cookie = base64_encode( wp_json_encode( $messages ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		setcookie( $queue, $messages_cookie, time() + 30 );
		$_COOKIE[ $queue ] = $messages_cookie;
	}
}

/**
 * Resets Ajax Product Sync messages from the Notification System
 */
function autoship_reset_ajax_messages() {

	$autoship_ajax_errors = autoship_get_messages( 'ajax_autoship_sync_product', true, true );
	$wc_meta_errors       = array_filter( (array) get_option( WC_Admin_Meta_Boxes::ERROR_STORE ) );

	$refresh = false;
	foreach ( $autoship_ajax_errors as $key => $autoship_ajax_error ) {

		// Check if the error is one of our ajax errors and clear it if it is.
		foreach ( $wc_meta_errors as $index => $error ) {
			if ( $autoship_ajax_error['message'] === $error ) {
				unset( $wc_meta_errors[ $index ] );
				$refresh = true;
			}
		}
	}

	if ( $refresh ) {
		update_option( WC_Admin_Meta_Boxes::ERROR_STORE, $wc_meta_errors );
	}
}

add_action( 'admin_notices', 'autoship_reset_ajax_messages', 9 );

/**
 * Prints messages from the Notification System
 *
 * NOTE: The default message queue is the 'autoship_messages'
 * queue.  Since this function is tied into the
 * {@see admin_notices} hook anything in the 'autoship_messages'
 * will be displayed by default in admin notices.
 *
 * @param string $queue Optional. The message queue to pull the message from. Defaults to 'autoship_messages'.
 * @param string $title Optional. The title to display above the messages.
 */
function autoship_print_messages( $queue = 'autoship_messages', $title = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	// Retrieve the messages from the queue.
	$messages = autoship_get_messages( $queue );

	// No messages bail.
	if ( empty( $messages ) ) {
		return;
	}

	// loop through the messages and display them.
	foreach ( $messages as $message ) {
		?>
		<div class="notice is-dismissible <?php echo esc_attr( $message['type'] ); ?>">
			<p><?php echo wp_kses_post( $message['message'] ); ?></p>
		</div>
		<?php
	}

	// empty the cookie queue and reset.
	setcookie( $queue, '', time() - 3600 );
	$_COOKIE[ $queue ] = '';
}

add_action( 'admin_notices', 'autoship_print_messages' );

/**
 * Handles the addition of Autoship messages to the notice queue.
 * Notices can be filtered using the {@see autoship_notice_handler_message_code } filter.
 * Non-Default codes can be added/caught using the {@see autoship_notice_handler_default_notice } filter.
 *
 * Error log file additions can be filtered via (@param string $code The message to add to the queue.
 *
 * @param string $code The message code to add to the queue.
 * @param string $message Optional. An optional message to add along with the default.
 * @param bool   $log Optional. When set to true the notice info is added to the debug.log.
 * @param string $queue Optional. The message queue to add the notice too. Defaults to 'autoship_messages'.
 *
 * @see autoship_notice_handler_error_log)
 */
function autoship_notice_handler( $code, $message = '', $log = false, $queue = 'autoship_messages' ) {

	$notice = array(
		'message' => $message,
		'type'    => 'updated',
		'queue'   => $queue,
	);

	switch ( $code ) {
		case 'qpilot_post_denied':
			$notice['message']  = __( 'Autoship is experiencing issues connecting to QPilot. Please check your settings and test again.', 'autoship' );
			$notice['message'] .= ! empty( $message ) ? '<br/>' . $message : '';
			$notice['type']     = 'error';
			break;

		case 'add_token_error':
			$notice['message']  = __( 'See the <a href="#">Autoship Quickstart Guide</a> for help connecting Autoship Cloud.', 'autoship' );
			$notice['message'] .= ! empty( $message ) ? '<br/>' . $message : '';
			$notice['type']     = 'error';
			break;

		case 'disconnected':
			$notice['message'] = __( 'Autoship has been disconnected!', 'autoship' );
			break;

		case 'exception':
			$notice['message'] = $message;
			$notice['type']    = 'error';

			break;
		case 'general_error':
			$notice['message'] = $message;
			$notice['type']    = 'error';
			break;

		case 'notice':
			$notice['message'] = $message;
			$notice['type']    = 'updated';
			break;

		default:
			// The default is a catch all for any non-defined messages.
			$notice['message'] = $message;
			$notice            = apply_filters( 'autoship_notice_handler_default_notice_content', $notice, $code, $message );
			break;
	}

	// Optional filter hook for adjusting / overriding default notices.
	$notice = apply_filters( 'autoship_notice_handler_all_notice_content', $notice, $code, $message );

	// Log the Notice if directed to.
	if ( $log ) {
		// translators: %s is the autoship code.
		autoship_log_entry( sprintf( __( 'Autoship %s', 'autoship' ), ucfirst( strtolower( $code ) ) ), $notice['message'] );
	}

	// Add the Notice to the Autoship Message Queue.
	autoship_add_message( $notice['message'], $notice['type'], $notice['queue'] );
}

// ==========================================================
// Menu and Settings Registration Functions
// ==========================================================

/**
 * Adds the main Autoship Cloud Options page & adds the subpages.
 * Details and Support documentation @link https://support.autoship.cloud/
 * - Autoship Cloud
 *   Native WP Screens
 *   -- Settings
 *   -- Migrations
 *   Embeded QPilot Screens
 *   -- Products
 *   -- Customers
 *   -- Scheduled Orders
 *   -- Coupons
 *   -- Shipping Rates
 *   -- Tax Rates
 *   -- Payment Integrations
 *   -- Reports
 */
function autoship_create_menu() {

	$menu_options = apply_filters(
		'autoship_admin_settings_submenu_pages',
		array(
			'dashboard'            => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Dashboard', 'autoship' ),
				'menu_title'  => __( 'Dashboard', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'dashboard',
				'function'    => 'autoship_dashboard_page',
			),
			'scheduled-orders'     => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Scheduled Orders', 'autoship' ),
				'menu_title'  => __( 'Scheduled Orders', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'scheduled-orders',
				'function'    => 'autoship_scheduled_orders_page',
			),
            'customers'            => array(
                    'parent_slug' => 'autoship',
                    'page_title'  => __( 'Customers', 'autoship' ),
                    'menu_title'  => __( 'Customers', 'autoship' ),
                    'capability'  => 'administrator',
                    'menu_slug'   => 'customers',
                    'function'    => 'autoship_customers_page',
            ),
            'products'             => array(
                    'parent_slug' => 'autoship',
                    'page_title'  => __( 'Products', 'autoship' ),
                    'menu_title'  => __( 'Products', 'autoship' ),
                    'capability'  => 'administrator',
                    'menu_slug'   => 'products',
                    'function'    => 'autoship_products_page',
            ),
			'retain-and-grow'      => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Retain & Grow', 'autoship' ),
				'menu_title'  => __( 'Retain & Grow', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'retain-and-grow',
				'function'    => 'autoship_retain_and_grow_page',
			),
			'reports'              => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Reports', 'autoship' ),
				'menu_title'  => __( 'Reports', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'reports',
				'function'    => 'autoship_reports_page',
			),
            'maps'                 => array(
                    'parent_slug' => 'autoship',
                    'page_title'  => __( 'MAPs', 'autoship' ),
                    'menu_title'  => __( 'MAPs', 'autoship' ),
                    'capability'  => 'administrator',
                    'menu_slug'   => 'maps',
                    'function'    => 'autoship_maps_page',
            ),
			'payment-integrations' => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Payment Integrations', 'autoship' ),
				'menu_title'  => __( 'Payment Integrations', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'payment-integrations',
				'function'    => 'autoship_payment_integrations_page',
			),
			'shipping-rates'       => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Shipping Rates', 'autoship' ),
				'menu_title'  => __( 'Shipping Rates', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'shipping-rates',
				'function'    => 'autoship_shipping_rates_page',
			),
			'autoship'             => array(
				'parent_slug' => 'autoship',
				'page_title'  => __( 'Settings', 'autoship' ),
				'menu_title'  => __( 'Settings', 'autoship' ),
				'capability'  => 'administrator',
				'menu_slug'   => 'autoship',
				'function'    => 'autoship_settings_page',
			),
		)
	);

	// Create new top-level menu.
	add_menu_page( __( 'Autoship Cloud', 'autoship' ), __( 'Autoship Cloud', 'autoship' ), apply_filters( 'autoship_cloud_main_page_security', 'administrator' ), 'autoship', 'autoship_settings_page', 'dashicons-cloud' );

	// Setup Sub Menu's to the Autoship Main Setting.
	foreach ( $menu_options as $submenu ) {
		add_submenu_page( $submenu['parent_slug'], $submenu['page_title'], $submenu['menu_title'], apply_filters( 'autoship_cloud_subpage_security', $submenu['capability'], $submenu['menu_slug'] ), $submenu['menu_slug'], $submenu['function'] );
	}

	remove_submenu_page( 'autoship', 'autoship' );

	// Call register settings function.
	add_action( 'admin_init', 'register_autoship_settings' );
}

add_action( 'admin_menu', 'autoship_create_menu' );

/**
 * Registers the Autoship Settings.
 */
function register_autoship_settings() {

	// Setting => Settings Group.
	$autoship_settings = apply_filters(
		'autoship_registered_admin_settings',
		array(
			'autoship_client_id'                        => 'autoship-settings-group',
			'autoship_client_secret'                    => 'autoship-settings-group',
			'autoship_cart_schedule_options_enabled'    => 'autoship-settings-group',
			'autoship_site_id'                          => 'autoship-settings-group',
			'autoship_user_id'                          => 'autoship-settings-group',
			'autoship_token_auth'                       => 'autoship-settings-group',
			'autoship_refresh_token'                    => 'autoship-settings-group',
			'autoship_product_message'                  => 'autoship-settings-group',
			'autoship_scheduled_orders_app_enabled'     => 'autoship-settings-group',
			'autoship_scheduled_orders_display_version' => 'autoship-settings-group',
			'autoship_editable_shipping_rate_enabled'   => 'autoship-settings-group',
			'autoship_v2_portal_display_without_scheduled_orders' => 'autoship-settings-group',
			'autoship_scheduled_orders_html'            => 'autoship-settings-group',
			'autoship_scheduled_orders_body_html'       => 'autoship-settings-group',
			'autoship_free_shipping'                    => 'autoship-settings-group',
			'autoship_sync_all_products_enabled'        => 'autoship-settings-group',
			'autoship_rest_order_fee_lines_enabled'     => 'autoship-settings-group',
			'autoship_display_next_occurrence_offset'   => 'autoship-settings-group',
			'autoship_translation'                      => 'autoship-settings-group',
			'autoship_and_save_translation'             => 'autoship-settings-group',
			'autoship_scheduled_order_translation'      => 'autoship-settings-group',
			'autoship_scheduled_orders_translation'     => 'autoship-settings-group',
			'autoship_product_info_display'             => 'autoship-settings-group',
			'autoship_product_info_modal_size'          => 'autoship-settings-group',
			'autoship_product_info_mobile_tooltip'      => 'autoship-settings-group',
			'autoship_product_info_url'                 => 'autoship-settings-group',
			'autoship_product_info_btn_type'            => 'autoship-settings-group',
			'autoship_product_info_btn_text'            => 'autoship-settings-group',
			'autoship_product_info_html'                => 'autoship-settings-group',
			'autoship_support_cod_payments'             => 'autoship-settings-group',
			'autoship_legacy_qpilot_products_data'      => 'autoship-settings-group',
			'autoship_scheduled_order_upsell_carousel'  => 'autoship-settings-group',
			'autoship_scheduled_order_upsell_disable_carousel_js' => 'autoship-settings-group',
			'autoship_debug_state'                      => 'autoship-settings-group',
		)
	);

	// Register settings.
	foreach ( $autoship_settings as $setting => $group ) {
		register_setting( $group, $setting );
	}

	// Set / Update the defaults.
	autoship_get_settings_fields();
}

/**
 * Returns a list of Autoship Setting Page Tabs.
 *
 * @return array of settings tabs with labels and callback.
 */
function autoship_settings_tabs() {

	// get the current health statuses.
	$healthy      = autoship_get_integration_health_status();
	$health_ext   = '<i class="icon-asc"></i>';
	$health_class = 'ahstatus';

	if ( false !== $healthy && 'healthy' === $healthy ) {
		$health_class .= ' valid';
	} elseif ( ! autoship_is_new() ) {
		$health_class .= ' error';
	}

	$tabs = array(
		'autoship-connection-settings' => array(
			'label'      => __( 'Connection Settings' . $health_ext, 'autoship' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			'callback'   => 'autoship_connection_settings_section',
			'link_class' => $health_class,
		),
		'autoship-options'             => array(
			'label'      => __( 'Options', 'autoship' ),
			'callback'   => 'autoship_options_section',
			'link_class' => '',
		),
		'autoship-utilities'           => array(
			'label'      => __( 'Utilities', 'autoship' ),
			'callback'   => 'autoship_utilities_section',
			'link_class' => '',
		),
		'autoship-extensions'          => array(
			'label'      => __( 'Extensions', 'autoship' ),
			'callback'   => 'autoship_extensions_section',
			'link_class' => '',
		),
		'autoship-logs'                => array(
			'label'      => __( 'Diagnostics', 'autoship' ),
			'callback'   => 'autoship_logs_section',
			'link_class' => '',
		),
	);

	return apply_filters( 'autoship_admin_settings_tabs', $tabs );
}

/**
 * Returns a list of Autoship Retain and Grows Page Tabs.
 *
 * @return array of settings tabs with labels and callback.
 */
function autoship_retain_and_grow_tabs() {

	$tabs = array(
		'coupons'             => array(
			'label'      => __( 'Coupons', 'autoship' ),
			'callback'   => 'autoship_admin_retain_and_grow_tabs_content',
			'link_class' => '',
		),
		'retention-workflows' => array(
			'label'      => __( 'Retention Workflows', 'autoship' ),
			'callback'   => 'autoship_admin_retain_and_grow_tabs_content',
			'link_class' => '',
		),
		'dunning'             => array(
			'label'      => __( 'Dunning', 'autoship' ),
			'callback'   => 'autoship_admin_retain_and_grow_tabs_content',
			'link_class' => '',
		),
		'quick-actions'       => array(
			'label'      => __( 'Quick Actions', 'autoship' ),
			'callback'   => 'autoship_admin_retain_and_grow_tabs_content',
			'link_class' => '',
		),
	);

	return apply_filters( 'autoship_admin_retain_and_grow_tabs', $tabs );
}

/**
 * Returns a list of Autoship Reports Page Tabs.
 *
 * @return array of settings tabs with labels and callback.
 */
function autoship_reports_tabs() {

	$tabs = array(
		'schedule_orders_metrics' => array(
			'label'      => __( 'Scheduled Orders Metrics', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
		'date_summary'            => array(
			'label'      => __( 'Products by Date Summary', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
		'schedule_nxt_order'      => array(
			'label'      => __( 'Scheduled Orders by Product', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
		'churn_reports'           => array(
			'label'      => __( 'Churn Reports', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
		'survey_results'          => array(
			'label'      => __( 'Survey Results', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
		'customer_metrics'        => array(
			'label'      => __( 'Customer Metrics', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
		'email_delivery'          => array(
			'label'      => __( 'Email Delivery', 'autoship' ),
			'callback'   => 'autoship_admin_reports_tabs_content',
			'link_class' => '',
		),
	);

	return apply_filters( 'autoship_admin_retain_and_grow_tabs', $tabs );
}

// ==========================================================
// Settings Retrieval and Default Functions
// ==========================================================

/**
 * Retrieves the Autoship settings fields.
 *
 * @param array|string $fields Optional. A set of fields to get.
 * @param bool         $single True to return the first value only. False for the full array.
 *
 * @return array An array of option values.
 */
function autoship_get_settings_fields( $fields = array(), $single = false ) {

	$fields = is_array( $fields ) ? $fields : explode( ',', $fields );

	$defaults = apply_filters(
		'autoship_registered_admin_setting_field_ids',
		array(
			'autoship_client_id',
			'autoship_client_secret',
			'autoship_token_auth',
			'autoship_user_id',
			'autoship_site_id',
			'autoship_refresh_token',
			'autoship_free_shipping',
			'autoship_sync_all_products_enabled',
			'autoship_scheduled_orders_app_enabled',
			'autoship_scheduled_orders_display_version',
			'autoship_v2_portal_display_without_scheduled_orders',
			'autoship_editable_shipping_rate_enabled',
			'autoship_saved_site_processing_version',
			'autoship_scheduled_orders_html',
			'autoship_scheduled_orders_body_html',
			'autoship_cart_schedule_options_enabled',
			'autoship_rest_order_fee_lines_enabled',
			'autoship_display_next_occurrence_offset',
			'autoship_product_message',
			'autoship_dynamic_cart',
			'autoship_health',
			'autoship_get_checked_utc',
			'autoship_put_checked_utc',
			'autoship_post_checked_utc',
			'autoship_translation',
			'autoship_and_save_translation',
			'autoship_scheduled_order_translation',
			'autoship_scheduled_orders_translation',
			'autoship_product_info_display',
			'autoship_product_info_modal_size',
			'autoship_product_info_mobile_tooltip',
			'autoship_product_info_btn_type',
			'autoship_product_info_url',
			'autoship_product_info_btn_text',
			'autoship_product_info_html',
			'autoship_support_cod_payments',
			'autoship_legacy_qpilot_products_data',
			'autoship_scheduled_order_upsell_carousel',
			'autoship_scheduled_order_upsell_disable_carousel_js',
			'autoship_debug_state',
		)
	);

	$values = array();
	$checks = empty( $fields ) ? $defaults : $fields;

	foreach ( $checks as $field ) {
		$values[ $field ] = get_option( $field, apply_filters( "autoship_get_setting_{$field}_default_init", false ) );
	}

	return apply_filters( 'autoship_get_settings_fields_values_init', $single ? current( $values ) : $values );
}

/**
 * Add a default value for the autoship_saved_site_processing_version option if
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_saved_site_processing_version_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'v2';
}

add_filter( 'autoship_get_setting_autoship_saved_site_processing_version_default_init', 'autoship_saved_site_processing_version_default_init', 10, 1 );


/**
 * Add a default value for the autoship_product_info_mobile_tooltip option
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_product_info_mobile_tooltip_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'yes';
}

add_filter( 'autoship_get_setting_autoship_product_info_mobile_tooltip_default_init', 'autoship_product_info_mobile_tooltip_default_init', 10, 1 );

/**
 * Add a default value for the autoship_product_info_btn_text when
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_product_info_btn_type_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'icon';
}

add_filter( 'autoship_get_setting_autoship_product_info_btn_type_default_init', 'autoship_product_info_btn_type_default_init', 10, 1 );

/**
 * Add a default value for the autoship_product_info_btn_text when
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_product_info_modal_size_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'medium';
}

add_filter( 'autoship_get_setting_autoship_product_info_modal_size_default_init', 'autoship_product_info_modal_size_default_init', 10, 1 );

/**
 * Add a default value for the autoship_product_info_btn_text when
 * It doesn't exist.
 *
 * @param string $value The value.
 *
 * @return string 'Info'
 */
function autoship_product_info_btn_text_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'Info';
}

add_filter( 'autoship_get_setting_autoship_product_info_btn_text_default_init', 'autoship_product_info_btn_text_default_init', 10, 1 );

/**
 * Add a default value for the autoship_product_info_display when
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_product_info_display_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return autoship_is_new() ? 'modal' : 'none';
}

add_filter( 'autoship_get_setting_autoship_product_info_display_default_init', 'autoship_product_info_display_default_init', 10, 1 );

/**
 * Add a default value for the autoship_editable_shipping_rate_enabled when
 * It doesn't exist.
 *
 * @param string $value The current value.
 *
 * @return string The default or the current value
 */
function autoship_editable_shipping_rate_enabled_default_init( $value ) {
	return autoship_is_new() || empty( $value ) ? 'no' : $value;
}

add_filter( 'autoship_get_setting_autoship_editable_shipping_rate_enabled_default_init', 'autoship_editable_shipping_rate_enabled_default_init', 10, 1 );


/**
 * Add a default value for the autoship_legacy_qpilot_products_data when
 * It doesn't exist.
 *
 * @param string $value The current value.
 *
 * @return string The default or the current value
 */
function autoship_legacy_qpilot_products_data_default_init( $value ) {
	return autoship_is_new() || empty( $value ) ? 'no' : $value;
}

add_filter( 'autoship_get_setting_autoship_legacy_qpilot_products_data_default_init', 'autoship_legacy_qpilot_products_data_default_init', 10, 1 );


/**
 * Add a default value for the autoship_product_info_html when
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_product_info_html_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

	ob_start();
	?>
	<div style="text-align:left;">
		<strong>Why Choose to Autoship?</strong>
		<ul style="padding-left:20px;">
			<li>Automatically re-order your favorite
				products on your schedule.
			</li>
			<li>Easily change the products or shipping
				date for your upcoming Scheduled Orders.
			</li>
			<li>Pause or cancel any time.</li>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}

add_filter( 'autoship_get_setting_autoship_product_info_html_default_init', 'autoship_product_info_html_default_init', 10, 1 );

/**
 * Add a default value for the autoship_scheduled_orders_body_html when
 * It doesn't exist.
 *
 * @param Mixed|bool $value The value.
 *
 * @return Mixed|bool False to not include default else
 */
function autoship_scheduled_orders_body_html_default_init( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	$label = autoship_translate_text( 'Scheduled Order' );

	ob_start();
	?>

	<h2>
		<?php
		echo esc_html( __( 'No Current Scheduled Orders', 'autoship' ) );
		?>
	</h2>
	<p>
		<?php
		echo esc_html( __( "You currently have no scheduled orders to display. Click the button below to get started and create your first {$label}.", 'autoship' ) ); // phpcs:ignore WordPress.WP.I18n.InterpolatedVariableText
		?>
	</p>
	<p style="text-align:center;">[autoship-create-scheduled-order-action]</p>

	<?php
	return ob_get_clean();
}

add_filter( 'autoship_get_setting_autoship_scheduled_orders_body_html_default_init', 'autoship_scheduled_orders_body_html_default_init', 10, 1 );

/**
 * Add a default value for the autoship_support_cod_payments when
 * It doesn't exist.
 *
 * @param string $value The current value.
 *
 * @return string The default or the current value
 */
function autoship_support_cod_payments_default_init( $value ) {
	return autoship_is_new() || empty( $value ) ? 'no' : $value;
}

add_filter( 'autoship_get_setting_autoship_support_cod_payments_default_init', 'autoship_support_cod_payments_default_init', 10, 1 );

/**
 * Add a default value for the autoship_scheduled_order_upsell_carousel when
 * It doesn't exist.
 *
 * @param string $value The current value.
 *
 * @return string The default or the current value
 */
function autoship_scheduled_order_upsell_carousel_default_init( $value ) {
	return autoship_is_new() || empty( $value ) ? 'no' : $value;
}

add_filter( 'autoship_get_setting_autoship_scheduled_order_upsell_carousel_default_init', 'autoship_scheduled_order_upsell_carousel_default_init', 10, 1 );

/**
 * Add a default value for the autoship_scheduled_order_upsell_disable_carousel_js when
 * It doesn't exist.
 *
 * @param string $value The current value.
 *
 * @return string The default or the current value
 */
function autoship_scheduled_order_upsell_disable_carousel_js_default_init( $value ) {
	return autoship_is_new() || empty( $value ) ? 'no' : $value;
}

add_filter( 'autoship_get_setting_autoship_scheduled_order_upsell_disable_carousel_js_default_init', 'autoship_scheduled_order_upsell_disable_carousel_js_default_init', 10, 1 );


/**
 * Settings Init Value, Upgrade & Migrate function
 * This allows to set the default for any new settings values.
 * Hooks into the {@see autoship_get_settings_fields_values_init} filter
 *
 * @param array $values The Autoship settings and corresponding values.
 *
 * @return array The updated Autoship settings and corresponding values.
 */
function autoship_init_new_settings( $values ) {

	$inits = array();

	// The autoship_scheduled_orders_display_version should be app, hosted or template.
	if ( isset( $values['autoship_scheduled_orders_display_version'] ) && empty( $values['autoship_scheduled_orders_display_version'] ) ) {
		$inits['autoship_scheduled_orders_display_version'] = 'yes' === $values['autoship_scheduled_orders_app_enabled'] ? 'app' : 'template';
	}

	// For each updated / initialized settings update
	// the option and the filtered values.
	foreach ( $inits as $key => $value ) {
		$values[ $key ] = $value;
		update_option( $key, $value );
	}

	return $values;
}

add_filter( 'autoship_get_settings_fields_values_init', 'autoship_init_new_settings', 10, 1 );


// ==========================================================
// Autoship Cloud Settings Tab Content Functions
// ==========================================================

/**
 * Hide Submit on Extensions Tab
 *
 * @param bool   $include_submit Whether to include or exclude the submit.
 * @param string $active_tab The current tab.
 *
 * @return bool The filtered value to include or exclude the submit.
 */
function autoship_exclude_submit_on_tab( $include_submit, $active_tab ) {

	return ! in_array( $active_tab, array( 'autoship-extensions', 'autoship-utilities', 'autoship-logs' ), true );
}

add_filter( 'autoship_admin_settings_tab_include_submit', 'autoship_exclude_submit_on_tab', 10, 2 );

/**
 * Callback for the Autoship Admin Reports Tab Content.
 *
 * @param string $id The tab id.
 */
function autoship_admin_reports_tabs_content( $id ) {
	$token_auth = autoship_get_token_auth();
	if ( empty( $token_auth ) ) {
		return;
	}
	$site_id = autoship_get_site_id();

	$reports = array(
		'date_summary'            => rawurlencode( $site_id ) . '/reports/products-by-date-summary?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'schedule_nxt_order'      => rawurlencode( $site_id ) . '/reports/scheduled-orders-by-product?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'schedule_orders_metrics' => rawurlencode( $site_id ) . '/reports/scheduled-orders-metrics?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'churn_reports'           => rawurlencode( $site_id ) . '/reports/churn-reports?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'customer_metrics'        => rawurlencode( $site_id ) . '/reports/customer-metrics?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'email_delivery'          => rawurlencode( $site_id ) . '/reports/email-delivery?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'survey_results'          => rawurlencode( $site_id ) . '/reports/survey-results?tokenBearerAuth=' . rawurlencode( $token_auth ),
	);

	do_action( 'autoship_before_autoship_admin_reports' );

	?>
	<iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo $reports[ $id ]; //phpcs:ignore ?>"
			class="autoship-admin-scheduled-orders-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>

	<?php

	do_action( 'autoship_after_autoship_admin_reports' );
}

/**
 * Callback for the Autoship Admin Reatin & Grow Tab Content.
 *
 * @param string $id The tab id.
 */
function autoship_admin_retain_and_grow_tabs_content( $id ) {
	$token_auth = autoship_get_token_auth();
	if ( empty( $token_auth ) ) {
		return;
	}
	$site_id = autoship_get_site_id();

	// Check if template exists for this tab.
	$template_path = 'admin/retain-and-grow/' . $id;

	if ( file_exists( Autoship_Plugin_Dir . '/templates/' . $template_path . '.php' ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => $site_id,
		);

		do_action( 'autoship_before_autoship_admin_retain_and_grow' );
		autoship_include_template( $template_path, $args );
		do_action( 'autoship_after_autoship_admin_retain_and_grow' );
		return;
	}

	// Fallback to iframe for tabs without templates.
	$reports = array(
		'coupons'             => rawurlencode( $site_id ) . '/coupons?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'dunning'             => rawurlencode( $site_id ) . '/settings/site-dunning?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'retention-workflows' => rawurlencode( $site_id ) . '/retain-and-grow/retention?tokenBearerAuth=' . rawurlencode( $token_auth ),
		'quick-actions'       => rawurlencode( $site_id ) . '/quick-actions?tokenBearerAuth=' . rawurlencode( $token_auth ),
	);

	do_action( 'autoship_before_autoship_admin_retain_and_grow' );
	?>

	<iframe src="<?php echo esc_attr( autoship_get_merchants_url() ); ?>/widgets/dashboard/<?php echo $reports[ $id ]; //phpcs:ignore ?>"
			class="autoship-admin-scheduled-orders-iframe autoship-admin-dashboard-iframe" frameborder="0"></iframe>
	<?php

	do_action( 'autoship_after_autoship_admin_retain_and_grow' );
}

/**
 * Generate the content to the Autoship Settings page.
 */
function autoship_settings_page() {
	autoship_include_template( 'admin/settings' );
}

/**
 * Generates the content for a Autoship Cloud > Settings > Settings section.
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_connection_settings_section( $autoship_settings ) {
	autoship_include_template( 'admin/settings/connections', array( 'autoship_settings' => $autoship_settings ) );
}

/**
 * Generates the content for a Autoship Cloud > Settings > Options section.
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_options_section( $autoship_settings ) {

	$settings = array(
		'teeny'         => false,
		'textarea_rows' => 8,
		'media_buttons' => false,
	);

    autoship_include_template( 'admin/settings/options', array( 'autoship_settings' => $autoship_settings, 'settings' => $settings ) );
}

/**
 * Generates the content for a Autoship Cloud > Settings > Utilities section.
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_utilities_section( $autoship_settings ) {
	autoship_include_template( 'admin/settings/utilities', array( 'autoship_settings' => $autoship_settings ) );
}

/**
 * Generates the content for a Autoship Cloud > Settings > Logs section.
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_logs_section( $autoship_settings ) {
	autoship_include_template( 'admin/settings/logs', array( 'autoship_settings' => $autoship_settings ) );
}

// ==========================================================
// Autoship Cloud Custom Extension Management Functions
// ==========================================================

/**
 * Retrieves the Current List of Extensions
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_get_custom_extensions( $autoship_settings = array() ) {
	return apply_filters( 'autoship_plugin_custom_extensions', array(), $autoship_settings );
}

/**
 * Generates the content for a Autoship Extensions Settings page section.
 *
 * @param array $autoship_settings The current autoship Settings fields and values.
 */
function autoship_extensions_section( $autoship_settings ) {
	autoship_include_template( 'admin/settings/extensions', array( 'autoship_settings' => $autoship_settings ) );
}

// ==========================================================
// Autoship Cloud Non-Settings Page Content Functions
// ==========================================================

/**
 * Generates the content for a Autoship Cloud > Products page.
 */
function autoship_products_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/products', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Coupons page.
 */
function autoship_coupons_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/coupons', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Retention Workflows page.
 */
function autoship_retention_workflows_page() {
    $token_auth = autoship_get_token_auth();
    if ( ! empty( $token_auth ) ) {
        $args = array(
                'token_auth' => $token_auth,
                'site_id'    => autoship_get_site_id(),
        );
        autoship_include_template( 'admin/retention-workflows', $args );
    } else {
        autoship_include_template( 'admin/no-token' );
    }
}


/**
 * Generates the content for a Autoship Cloud > Retention Workflows page.
 */
function autoship_dunning_page() {
    $token_auth = autoship_get_token_auth();
    if ( ! empty( $token_auth ) ) {
        $args = array(
                'token_auth' => $token_auth,
                'site_id'    => autoship_get_site_id(),
        );
        autoship_include_template( 'admin/dunning', $args );
    } else {
        autoship_include_template( 'admin/no-token' );
    }
}


/**
 * Generates the content for a Autoship Cloud > Customers page.
 */
function autoship_customers_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/customers', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Orders page.
 */
function autoship_scheduled_orders_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/scheduled-orders', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Shipping Rates page.
 */
function autoship_shipping_rates_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/shipping-rates', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Integrations page.
 */
function autoship_payment_integrations_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/payment-integrations', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Reports page.
 */
function autoship_reports_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/reports', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > Dashboard page.
 */
function autoship_dashboard_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/dashboard', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}

/**
 * Generates the content for a Autoship Cloud > MAPs page.
 */
function autoship_maps_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/maps', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}


/**
 * Generates the content for a Autoship Cloud > Retain & Grow page.
 */
function autoship_retain_and_grow_page() {
	$token_auth = autoship_get_token_auth();
	if ( ! empty( $token_auth ) ) {
		$args = array(
			'token_auth' => $token_auth,
			'site_id'    => autoship_get_site_id(),
		);
		autoship_include_template( 'admin/retain-and-grow', $args );
	} else {
		autoship_include_template( 'admin/no-token' );
	}
}


// ==========================================================
// Autoship Cloud Non-Settings Page Display Functions
// ==========================================================

/**
 * The version checks for major upgrades
 */
function autoship_display_invalid_products_panel() {

	if ( ! autoship_check_site_settings_for_invalid_products() ) {
		return;
	}

	?>

	<div class="autoship_general_admin_notice health-error is-dismissible">
		<?php
			echo wp_kses_post(
				sprintf(
					// translators: %s is the link to the product sync page.
					__( '<h2>Autoship Cloud Invalid Products Notice</h2><hr/><p style="max-width: 880px;">1 or more WooCommerce Products enabled for Autoship have become invalid.  This may be caused by the deletion of the product(s) in WooCommerce, or a change to the product Id(s) in your WordPress Database.<br/><br/>Please review the products sync’d with Autoship Cloud below and enable the <strong>“Invalid”</strong> filter to see which product(s) have become invalid.<br/><br/>You can use the “Update Product Synchronization” button to re-check the status of all of your products.</p><a class="button button-primary" href="%s">Update Product Synchronization</a>' ),
					esc_attr( admin_url( '/admin-ajax.php?action=autoship_retest_invalid_products' ) )
				)
			);
		?>
	</div>
	
	<?php
}

add_action( 'autoship_before_autoship_admin_products', 'autoship_display_invalid_products_panel', 99 );

// ==========================================================
// Oauth Connection Functions
// ==========================================================

/**
 * Connects to the QPilot API to retrieve the connection information
 * Checks for the code returned from QPilot
 *
 * {@see autoship_qpilot_statuscheck_routes()}
 * Prior to this the following checks are run by QPilot.
 * - A GET request it sent to the WC REST API to retrieve a product.
 * - ( new ) A PUT request is sent to the autoship statuscheck endpoint
 * - { new } A POST request is sent tp the autoship statuscheck endpoint
 */
function autoship_oauth2() {

	// Confirm the current user has rights.
	if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
		autoship_ajax_result( 403 );
		die();
	}

	// Check for a return code from QPilot.
	if ( ! isset( $_REQUEST['code'] ) || empty( $_REQUEST['code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$msg = __( 'No response code was received from QPilot.', 'autoship' );
		autoship_notice_handler( 'add_token_error', $msg );
		autoship_log_entry( __( 'Autoship Oauth Error', 'autoship' ), $msg );

		wp_redirect( admin_url( '/admin.php?page=autoship' ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		die();

	}

	// Gather the returned code.
	$code = $_REQUEST['code']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	// Create the QPilot Client.
	$client = new QPilotClient();

	try {

		$token_response = $client->oauth2( $code );

		// Update the Settinsg based on the QPilot Endpoint response.
		update_option( 'autoship_token_auth', $token_response->access_token );
		update_option( 'autoship_user_id', $token_response->user_id );
		update_option( 'autoship_refresh_token', $token_response->refresh_token );
		update_option( 'autoship_token_expires_in', $token_response->expires_in );

		// Init the creation time - used for checking expired tokens.
		$token_created_at = time();
		update_option( 'autoship_token_created_at', $token_created_at );

		// Now since we have a QPilot token lets create the WC API keys and Site in QPilot.
		autoship_oauth2_connect_site();

		// Connecting Tests the ability to POST to the QPilot endpoint but not
		// QPilot connecting with this Autoship instance
		// Automatically run the Test Connection functionality.
		if ( 'healthy' === autoship_init_integration_test() ) {

			// Finally lets upsert the site info.
			autoship_push_site_metadata( autoship_qpilot_get_sitemeta() );
		}
	} catch ( Exception $e ) {

		// If an error occurs here is's due to issues POSTing to QPilot.
		autoship_notice_handler( 'qpilot_post_denied', $e->getMessage() );
		autoship_log_entry( __( 'Autoship Oauth Exception', 'autoship' ), sprintf( 'An %s Exception Occurred when attempting Oauth connection to QPilot. Additional Details: %s', $e->getCode(), $e->getMessage() ) );

	}

	wp_redirect( admin_url( '/admin.php?page=autoship' ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	die();
}

add_action( 'wp_ajax_autoship_oauth2', 'autoship_oauth2' );

/**
 * Creates a Site in QPilot using the current blog info, and generated API Keys.
 *
 * @param int $user_id Optional. The WP User's id.
 *
 * @return stdClass The created site object.
 */
function autoship_oauth2_connect_site( $user_id = null ) {

	if ( ! isset( $user_id ) || ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Create API keys.
	$api_keys = autoship_oauth2_create_wc_api_keys( $user_id );

	$client = new QPilotClient();

	$site = null;

	try {

		// Create site.
		$site = $client->create_site( $api_keys['consumer_key'], $api_keys['consumer_secret'] );

		update_option( 'autoship_site_id', $site->id );
		do_action( 'autoship_api_site_created', $site );

	} catch ( Exception $e ) {

		// If an error occurs here is's due to issues POSTing to QPilot.
		autoship_notice_handler( 'qpilot_post_denied', $e->getMessage() );
		autoship_log_entry( __( 'Autoship Oauth Exception', 'autoship' ), sprintf( 'An %s Exception Occurred when attempting to update the Site Connection in QPilot. Additional Details: %s', $e->getCode(), $e->getMessage() ) );

	}

	return $site;
}

/**
 * Creates the WC API Keys.
 *
 * @param int $user_id Optional. The WP User's id.
 *
 * @return array An array containing the Consumer Key and Secret
 */
function autoship_oauth2_create_wc_api_keys( $user_id = null ) {

	if ( ! isset( $user_id ) || ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$description     = __( 'Autoship - QPilot', 'autoship' );
	$consumer_key    = 'ck_' . wc_rand_hash();
	$consumer_secret = 'cs_' . wc_rand_hash();
	$permissions     = 'read_write';

	$data = array(
		'user_id'         => $user_id,
		'description'     => $description,
		'permissions'     => $permissions,
		'consumer_key'    => wc_api_hash( $consumer_key ),
		'consumer_secret' => $consumer_secret,
		'truncated_key'   => substr( $consumer_key, - 7 ),
	);

	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->prefix . 'woocommerce_api_keys',
		$data,
		array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);

	// Now save the user who generated the keys.
	update_option( 'autoship_api_keys_author', $user_id );

	return array(
		'consumer_key'    => $consumer_key,
		'consumer_secret' => $consumer_secret,
	);
}

/**
 * Disconnects the Integration with QPilot
 * Removes and Deletes the settings.
 */
function autoship_oauth2_disconnect() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
		autoship_ajax_result( 403 );
		die();
	}

	autoship_clear_integration_point_statuses( true );

	delete_option( 'autoship_token_auth' );
	delete_option( 'autoship_user_id' );
	delete_option( 'autoship_site_id' );
	delete_option( 'autoship_refresh_token' );
	delete_option( 'autoship_token_expires_in' );
	delete_option( 'autoship_token_created_at' );
	delete_option( 'autoship_quicklaunch_last_step' );
	delete_option( 'autoship_quicklaunch_product' );
	delete_option( 'autoship_quicklaunch_completed' );

	// Clear the cache for the connection values.
	// Fixes a bug that causes the auth token no to be updated.
	wp_cache_delete( 'autoship_token_auth', 'options' );
	wp_cache_delete( 'autoship_user_id', 'options' );
	wp_cache_delete( 'autoship_site_id', 'options' );
	wp_cache_delete( 'autoship_refresh_token', 'options' );
	wp_cache_delete( 'autoship_token_expires_in', 'options' );
	wp_cache_delete( 'autoship_token_created_at', 'options' );
	wp_cache_delete( 'autoship_quicklaunch_last_step', 'options' );
	wp_cache_delete( 'autoship_quicklaunch_product', 'options' );
	wp_cache_delete( 'autoship_quicklaunch_completed', 'options' );

	autoship_notice_handler( 'disconnected' );
	autoship_log_entry( __( 'Autoship Oauth Status', 'autoship' ), __( 'Autoship Cloud connection has been disconnected!', 'autoship' ) );

	wp_redirect( admin_url( '/admin.php?page=autoship' ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	die();
}

add_action( 'wp_ajax_autoship_oauth2_disconnect', 'autoship_oauth2_disconnect' );

/**
 * Adds the Token Error Default Message to the notices
 */
function autoship_oauth_add_token_error_help_message() {
	autoship_notice_handler( 'add_token_error' );
}
