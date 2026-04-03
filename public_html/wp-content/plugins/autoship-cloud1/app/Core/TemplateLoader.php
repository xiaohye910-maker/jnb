<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * The template loader class for the Autoship modules.
 *
 * @package  Autoship
 * @since    2.8.7
 */

namespace Autoship\Core;

/**
 * The template loader class for the Autoship modules.
 *
 * @package Autoship
 * @since 2.8.7
 */
class TemplateLoader {

	/**
	 * Load a template from a module.
	 *
	 * @param string $module The module name (e.g., 'Quicklaunch').
	 * @param string $template The template name without extension (e.g., 'form-login').
	 * @param array  $data Data to extract into the template (optional).
	 * @return void
	 */
	public static function render( string $module, string $template, array $data = array() ) {
		$template_path_in_theme = 'autoship/' . strtolower( $module ) . '/' . $template . '.php';

		// Check if the theme has an override.
		$theme_template = locate_template( $template_path_in_theme );

		if ( $theme_template ) {
			$template_file = $theme_template;
		} else {
			// Fall back to the default plugin internal template.
			$template_file = plugin_dir_path( __DIR__ ) . '/../Modules/' . $module . '/Templates/' . $template . '.php';
		}

		if ( file_exists( $template_file ) ) {
			extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			include $template_file;
		}
	}
}
