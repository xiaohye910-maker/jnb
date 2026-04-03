<?php
/**
 * Scroll percentage settings.
 *
 * @package Hustle
 * @since 4.4.1
 */

if ( ! isset( $smallcaps_singular ) ) {
	$smallcaps_singular = 'module';
}
?>

<div class="sui-form-field">

	<?php /* translators: module type in small caps and in singular */ ?>
	<label class="sui-label" id="hustle-<?php echo esc_attr( $smallcaps_singular ); ?>-scroll-percentage-label"><?php esc_html_e( 'Page scrolled by', 'hustle' ); ?></label>

	<input
		type="number"
		min="0"
		max="100"
		name="trigger_on_scroll_page_percent"
		value="<?php echo esc_attr( $triggers['on_scroll_page_percent'] ); ?>"
		class="sui-form-control sui-field-has-suffix"
		data-attribute="triggers.on_scroll_page_percent"
		aria-labelledby="hustle-<?php echo esc_attr( $smallcaps_singular ); ?>-scroll-percentage-label"
	/>

	<span class="sui-field-suffix">%</span>

</div>
