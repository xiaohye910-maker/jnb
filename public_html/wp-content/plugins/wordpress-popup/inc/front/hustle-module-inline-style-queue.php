<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Hustle_Module_Inline_Style_Queue
 *
 * @package Hustle
 */
class Hustle_Module_Inline_Style_Queue {

	/**
	 * A queue of inline styles to be printed.
	 *
	 * @var array
	 */
	private static $inline_styles = array();

	/**
	 * Initialize the class
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'late_enqueue_bundle_inline_styles' ), 100 );
	}

	/**
	 * Enqueue inline style
	 *
	 * @param string $style_id Style ID.
	 * @param string $style Style.
	 */
	public static function enqueue_inline_style( $style_id, $style ) {
		$style = str_replace( array( "\r", "\n" ), '', $style );

		if ( ! did_action( 'wp_enqueue_scripts' ) ) {
			// Not yet enqueued, add to the bundle.
			self::$inline_styles[ $style_id ] = $style;
		} else {
			self::$inline_styles[ $style_id ] = true;
			// Already enqueued, add it immediately.
			self::late_enqueue_single_style( $style_id, $style );
		}
	}

	/**
	 * Late enqueue single inline style
	 *
	 * @param string $style_id Style ID.
	 * @param string $style Style.
	 */
	public static function late_enqueue_single_style( $style_id, $style ) {
		wp_register_style(
			$style_id,
			false,
			array(),
			'1.0.0'
		);
		wp_enqueue_style( $style_id );

		wp_add_inline_style(
			$style_id,
			$style
		);
	}

	/**
	 * Has inline style
	 *
	 * @param string $style_id Style ID.
	 * @return bool
	 */
	public static function has_inline_style( $style_id ) {
		return isset( self::$inline_styles[ $style_id ] );
	}

	/**
	 * Late enqueue bundle inline styles
	 *
	 * @return void
	 */
	public static function late_enqueue_bundle_inline_styles() {
		$all_styles = '';

		foreach ( self::$inline_styles as $style ) {
			if ( ! is_string( $style ) ) {
				continue;
			}

			$all_styles .= $style;
		}

		if ( $all_styles ) {

			if ( ! wp_style_is( 'hustle_inline_styles_front', 'enqueued' ) ) {
				// Enqueue the bundle if not already enqueued.
				wp_register_style(
					'hustle_inline_styles_front',
					false,
					array(),
					'1.0.0'
				);
				wp_enqueue_style( 'hustle_inline_styles_front' );
			}

			wp_add_inline_style(
				'hustle_inline_styles_front',
				$all_styles
			);
		}

		/**
		 * Fires after enqueueing inline styles.
		 */
		do_action( 'hustle_after_enqueue_inline_styles' );
	}
}
