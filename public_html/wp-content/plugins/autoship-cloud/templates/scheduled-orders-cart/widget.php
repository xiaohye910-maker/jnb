<?php
/**
 * Contains the iframe for the widget in the scheduled orders cart.
 *
 * @package Autoship
 * @since 1.0.0
 */

$url = $url ?? '';

?>
<iframe src="<?php echo esc_attr( $url ); ?>" class="autoship-widget-iframe" frameborder="0"></iframe>