<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Label Service Interface.
 *
 * @package Autoship
 * @subpackage Labels
 * @since 2.13.0
 */

namespace Autoship\Services\Labels;

/**
 * Service contract for white-label terminology customization.
 *
 * Manages the four customizable terms: "Autoship", "Autoship and Save",
 * "Scheduled Order", and "Scheduled Orders". Merchants configure
 * replacements via Autoship Cloud > Settings > Options > Labels.
 *
 * @package Autoship
 * @subpackage Labels
 * @since 2.13.0
 */
interface LabelServiceInterface {

	/**
	 * Gets the custom label for an exact term match.
	 *
	 * Supported terms (case-insensitive):
	 * - 'autoship'
	 * - 'autoship and save'
	 * - 'scheduled order'
	 * - 'scheduled orders'
	 *
	 * Preserves the caller's case: if the input is entirely lowercase,
	 * the returned label is also lowercased. Otherwise the configured
	 * label is returned as-is.
	 *
	 * Returns the original term unchanged if no customization is set.
	 *
	 * @param string $term The term to look up.
	 *
	 * @return string The custom label or the original term.
	 */
	public function get_label( string $term ): string;

	/**
	 * Searches the supplied text for all known terms and replaces
	 * them with their custom labels (both capitalized and lowercase).
	 *
	 * Longer terms are replaced first to avoid partial matches
	 * (e.g. "Autoship and Save" before "Autoship").
	 *
	 * Returns the text unchanged if no customizations are configured.
	 *
	 * @param string $text The text to process.
	 *
	 * @return string The text with custom labels applied.
	 */
	public function apply_labels( string $text ): string;

	/**
	 * Returns all configured custom labels as an associative array.
	 *
	 * Keys are lowercase canonical terms, values are the custom labels.
	 * Only includes terms that have a non-empty customization.
	 *
	 * @return array<string, string> The custom labels.
	 */
	public function get_all_custom_labels(): array;
}
