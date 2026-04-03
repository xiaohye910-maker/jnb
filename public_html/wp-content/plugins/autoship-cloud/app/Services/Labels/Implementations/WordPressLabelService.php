<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * WordPress implementation of the Label Service.
 *
 * @package Autoship
 * @subpackage Labels
 * @since 2.13.0
 */

namespace Autoship\Services\Labels\Implementations;

use Autoship\Core\SettingsInterface;
use Autoship\Services\Labels\LabelServiceInterface;

/**
 * WordPress implementation of the Label Service.
 *
 * Reads the four label customization options from WordPress settings
 * and caches them in memory for the lifetime of the request. All
 * subsequent calls to get_label() and apply_labels() use the cached
 * values with zero additional database queries.
 *
 * @package Autoship
 * @subpackage Labels
 * @since 2.13.0
 */
class WordPressLabelService implements LabelServiceInterface {

	/**
	 * The settings service.
	 *
	 * @var SettingsInterface
	 */
	private SettingsInterface $settings;

	/**
	 * Cached label customizations keyed by canonical (lowercase) term.
	 * Null until first load; empty array if no customizations are set.
	 *
	 * @var array<string, string>|null
	 */
	private ?array $labels = null;

	/**
	 * Canonical terms mapped to their WordPress option keys.
	 *
	 * Ordered longest-first within each group to ensure safe
	 * replacement: "autoship and save" before "autoship",
	 * "scheduled orders" before "scheduled order".
	 *
	 * @var array<string, string>
	 */
	private const TERM_MAP = array(
		'autoship and save' => 'autoship_and_save_translation',
		'autoship'          => 'autoship_translation',
		'scheduled orders'  => 'autoship_scheduled_orders_translation',
		'scheduled order'   => 'autoship_scheduled_order_translation',
	);

	/**
	 * Canonical terms mapped to their proper display (capitalized) form.
	 *
	 * Required because ucfirst() alone would produce "Autoship and save"
	 * instead of "Autoship and Save".
	 *
	 * @var array<string, string>
	 */
	private const TERM_DISPLAY = array(
		'autoship and save' => 'Autoship and Save',
		'autoship'          => 'Autoship',
		'scheduled orders'  => 'Scheduled Orders',
		'scheduled order'   => 'Scheduled Order',
	);

	/**
	 * Constructor.
	 *
	 * @param SettingsInterface $settings The WordPress settings service.
	 */
	public function __construct( SettingsInterface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Loads all 4 label settings once and caches for the request lifetime.
	 *
	 * @return void
	 */
	private function load_labels(): void {
		if ( null !== $this->labels ) {
			return;
		}

		$this->labels = array();

		foreach ( self::TERM_MAP as $term => $option_key ) {
			$value = $this->settings->get_option( $option_key, '' );

			if ( ! empty( $value ) ) {
				$this->labels[ $term ] = $value;
			}
		}
	}

	/**
	 * Gets the custom label for an exact term match.
	 *
	 * @param string $term The term to look up.
	 *
	 * @return string The custom label or the original term.
	 */
	public function get_label( string $term ): string {
		$this->load_labels();

		$canonical = strtolower( $term );

		if ( ! isset( $this->labels[ $canonical ] ) ) {
			return $term;
		}

		$custom_label = $this->labels[ $canonical ];

		// Preserve case: if the caller passed an all-lowercase term,
		// return the custom label in lowercase as well.
		if ( ctype_lower( $term ) ) {
			return strtolower( $custom_label );
		}

		return $custom_label;
	}

	/**
	 * Searches text for all known terms and replaces with custom labels.
	 *
	 * @param string $text The text to process.
	 *
	 * @return string The text with custom labels applied.
	 */
	public function apply_labels( string $text ): string {
		$this->load_labels();

		if ( empty( $this->labels ) ) {
			return $text;
		}

		$search_terms  = array();
		$replace_terms = array();

		// TERM_MAP is ordered longest-first, so replacements are safe.
		foreach ( self::TERM_MAP as $canonical => $option_key ) {
			if ( ! isset( $this->labels[ $canonical ] ) ) {
				continue;
			}

			$custom_label = $this->labels[ $canonical ];

			// Capitalized variant using proper display form.
			$search_terms[]  = self::TERM_DISPLAY[ $canonical ];
			$replace_terms[] = $custom_label;

			// Lowercase variant.
			$search_terms[]  = $canonical;
			$replace_terms[] = strtolower( $custom_label );
		}

		return str_replace( $search_terms, $replace_terms, $text );
	}

	/**
	 * Returns all configured custom labels.
	 *
	 * @return array<string, string> Keyed by canonical term.
	 */
	public function get_all_custom_labels(): array {
		$this->load_labels();

		return $this->labels;
	}
}
