<?php
/**
 * The language functions for the Autoship Cloud plugin.
 *
 * Legacy bridge: these functions delegate to the LabelService
 * registered in the service container. All 60+ call sites across the
 * codebase continue to work without modification.
 *
 * @package Autoship
 * @since 1.0.0
 */

/**
 * Searches the supplied string for any of the translate words and replaces them.
 *
 * @param string $text The string to replace the text in.
 *
 * @return string The translated / adjusted text
 * @deprecated 2.13
 */
function autoship_search_for_translate_text( $text ) {

	try {
		$service = \Autoship\Core\Plugin::get_service_container()
			->get( \Autoship\Services\Labels\LabelServiceInterface::class );
		return $service->apply_labels( $text );
	} catch ( \Exception $e ) {
		return $text;
	}
}

/**
 * Filters the Autoship Cloud and Scheduled Order text.
 *
 * @param string $text      The text to translate.
 * @param bool   $translate If the final text should be run through translator.
 *
 * @return string The translated / adjusted text
 * @deprecated 2.13
 */
function autoship_translate_text( $text, $translate = false ) {

	try {
		$service = \Autoship\Core\Plugin::get_service_container()
			->get( \Autoship\Services\Labels\LabelServiceInterface::class );
		$new_text = $service->get_label( $text );
	} catch ( \Exception $e ) {
		$new_text = $text;
	}

	return $translate ? __( $new_text, 'autoship' ) : $new_text; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
}
