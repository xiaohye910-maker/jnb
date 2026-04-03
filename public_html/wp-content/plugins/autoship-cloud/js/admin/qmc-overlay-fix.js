/**
 * QMC PrimeNG Overlay Position Fix
 *
 * PrimeNG overlays with appendTo="body" can miscalculate their horizontal
 * position in the WordPress admin context. The overlay panel (autocomplete
 * dropdown, select dropdown, etc.) ends up at the left edge of the viewport
 * instead of aligned with its trigger element.
 *
 * This script watches for PrimeNG overlay elements appended to <body> and
 * corrects their left position based on the actual trigger element.
 *
 * @package Autoship
 * @since 2.12.2
 */
(function () {
	'use strict';

	/**
	 * Map overlay selectors to their corresponding open-state trigger selectors.
	 */
	var OVERLAY_TRIGGER_MAP = [
		{ overlay: '.p-autocomplete-overlay', trigger: '.p-autocomplete.p-autocomplete-open' },
		{ overlay: '.p-select-overlay',       trigger: '.p-select.p-select-open' },
		{ overlay: '.p-dropdown-overlay',     trigger: '.p-dropdown.p-dropdown-open' },
		{ overlay: '.p-multiselect-overlay',  trigger: '.p-multiselect.p-multiselect-open' },
	];

	/**
	 * Find the trigger element that corresponds to the given overlay node.
	 *
	 * @param {HTMLElement} overlayNode The body-level overlay element.
	 * @return {HTMLElement|null}
	 */
	function findTrigger( overlayNode ) {
		for ( var i = 0; i < OVERLAY_TRIGGER_MAP.length; i++ ) {
			var panel = overlayNode.querySelector( OVERLAY_TRIGGER_MAP[ i ].overlay );
			if ( panel ) {
				return document.querySelector( OVERLAY_TRIGGER_MAP[ i ].trigger );
			}
		}
		return null;
	}

	/**
	 * Correct the left position of a PrimeNG overlay so it aligns with its
	 * trigger element.
	 *
	 * @param {HTMLElement} overlayNode The body-level .p-overlay element.
	 */
	function correctOverlayPosition( overlayNode ) {
		var trigger = findTrigger( overlayNode );
		if ( ! trigger ) {
			return;
		}

		var rect        = trigger.getBoundingClientRect();
		var scrollLeft  = window.pageXOffset || document.documentElement.scrollLeft;
		var correctLeft = rect.left + scrollLeft;

		var currentLeft = parseFloat( overlayNode.style.left ) || 0;
		if ( Math.abs( currentLeft - correctLeft ) > 20 ) {
			overlayNode.style.left = correctLeft + 'px';
		}

		// Constrain width to match the trigger element so the overlay
		// does not extend past the right edge of the viewport.
		var triggerWidth            = rect.width;
		overlayNode.style.width    = triggerWidth + 'px';
		overlayNode.style.minWidth = triggerWidth + 'px';
		overlayNode.style.maxWidth = triggerWidth + 'px';
	}

	/**
	 * Watch for new .p-overlay elements added to <body> (PrimeNG overlay
	 * creation) and correct their position.
	 */
	var bodyObserver = new MutationObserver( function ( mutations ) {
		for ( var i = 0; i < mutations.length; i++ ) {
			var addedNodes = mutations[ i ].addedNodes;
			for ( var j = 0; j < addedNodes.length; j++ ) {
				var node = addedNodes[ j ];
				if ( node.nodeType !== 1 || ! node.classList || ! node.classList.contains( 'p-overlay' ) ) {
					continue;
				}

				// Defer correction to the next frame so PrimeNG finishes
				// setting inline styles before we override.
				(function ( overlay ) {
					requestAnimationFrame( function () {
						correctOverlayPosition( overlay );
					});

					// Also watch for PrimeNG recalculating position (e.g. on
					// scroll/resize) by observing style attribute changes.
					var styleObserver = new MutationObserver( function () {
						correctOverlayPosition( overlay );
					});
					styleObserver.observe( overlay, { attributes: true, attributeFilter: [ 'style' ] } );
				})( node );
			}
		}
	});

	bodyObserver.observe( document.body, { childList: true } );
})();
