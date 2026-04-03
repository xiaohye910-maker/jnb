<?php
/**
 * Contains the customer bot functions.
 *
 * @package Autoship
 * @since 1.0.0
 */

$webchat_directline_secret = $webchat_directline_secret ?? '';
$autoship_customer_id      = $autoship_customer_id ?? '';
$customer_name             = $customer_name ?? '';
$token_auth                = $token_auth ?? '';

?>

<div id="autoship-customer-bot"></div>
<script>
	// <![CDATA[
	BotChat.App({
		directLine: { secret: <?php echo json_encode( $webchat_directline_secret ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode ?>, webSocket: true },
		user: {
			id: <?php echo json_encode( $autoship_customer_id ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode ?>,
			name: <?php echo json_encode( $customer_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode ?>,
			tokenAuth: <?php echo json_encode( $token_auth ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode ?>
		},
		resize: 'detect'
	}, document.getElementById( 'autoship-customer-bot' ) );
	// ]]>
</script>
