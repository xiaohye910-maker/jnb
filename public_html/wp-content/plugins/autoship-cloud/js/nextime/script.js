/**
 * Autoship Nextime Format Label Script.
 *
 * @package Autoship
 * @subpackage Nextime
 */

jQuery(
	function ($) {
		const formatShippingLabels = function () {

			$( "span[id*='autoship_nextime']" ).each(
				function () {
					let labelHTML = $( this ).html();

					if ( labelHTML.includes( '(' ) && labelHTML.includes( ')' ) ) {
						let newHTML = labelHTML.replace( /(\(.*?\))/g, '<br><small class="autoship-nextime-datetime" style="color: gray;">$1</small>' ).replace( '(', '' ).replace( ')', '' );

						$( this ).html( newHTML );
					}
				}
			);

			if ( $( "input[id*='autoship_nextime']" ).is( ':checked' ) ) {

				$( 'div.wc-block-components-totals-shipping__via' ).each(
					function () {
						let labelHTML = $( this ).html();

						if ( labelHTML.includes( '(' ) && labelHTML.includes( ')' ) ) {
							let newHTML = labelHTML.replace( /(\(.*?\))/g, '<br><small class="autoship-nextime-datetime" style="color: gray;">$1</small>' ).replace( '(', '' ).replace( ')', '' );

							$( this ).html( newHTML );
						}
					}
				);
			}
		};

		// Run repeatedly on the DOM load to catch the elements as they render.
		setInterval( formatShippingLabels, 500 );

		// Also run whenever the checkout updates (e.g., address change).
		$( document.body ).on( 'updated_checkout', formatShippingLabels );
	}
);