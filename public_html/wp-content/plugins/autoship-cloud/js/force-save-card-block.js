/**
 * Force save card checkbox to be checked and prevent user from unchecking it.
 * This ensures payment methods always save payment information for future use.
 *
 * @package Autoship
 * @since 2.8.6
 */

document.addEventListener(
	'DOMContentLoaded',
	function () {
		// Enable and force the save card checkbox to be checked.
		const enableSaveCardCheckbox = function () {
			const selectedPaymentMethod = document.querySelector( 'input[name="radio-control-wc-payment-method-options"]:checked' );
			if ( ! selectedPaymentMethod) {
				return;
			}

			let paymentMethod = selectedPaymentMethod.value;
			const elementId   = 'radio-control-wc-payment-method-options-' + paymentMethod + '__content';
			const container   = document.getElementById( elementId );
			if ( ! container) {
				return;
			}

			const checkboxes = container.querySelectorAll( 'input[type="checkbox"]' );
			if ( ! checkboxes || checkboxes.length === 0) {
				return;
			}

			checkboxes.forEach(
				function ( checkbox ) {
					if ( ! checkbox.checked ) {
						checkbox.click();
					}
				}
			);
		};

		// Observe the payment methods block for changes.
		setInterval(
			function () {
				// If the block checkout payment method is not rendered, skip.
				const target = document.querySelector( '.wc-block-checkout__payment-method' );
				if ( ! target ) {
					// Retry if the payment methods block hasn't rendered yet.
					return;
				}

				enableSaveCardCheckbox();
			},
			250
		);
	}
);
