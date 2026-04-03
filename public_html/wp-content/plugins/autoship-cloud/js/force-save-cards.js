if ( typeof AUTOSHIP_SAVE_PAYMENT_ELEMENT_IDS !== "undefined" ){
  (function (checkboxIds) {
      checkboxIds.forEach(function (id) {
          var checkbox = document.getElementById(id);
          if (checkbox !== null) {
              checkbox.checked = true;
              checkbox.addEventListener('click', function() {
                  this.checked = true;
              });
          }
      });
  })( AUTOSHIP_SAVE_PAYMENT_ELEMENT_IDS );
}

// Used for payment methow which checkbox doesn't have ID attr set
if ( typeof AUTOSHIP_SAVE_PAYMENT_ELEMENT_NAMES !== "undefined" ){
  (function (checkboxNames) {
      checkboxNames.forEach(function (id) {
          var checkbox = document.querySelector('input[name="wc-fkwcs_stripe-new-payment-method"]');
          if (checkbox !== null) {
              checkbox.checked = true;
              checkbox.addEventListener('click', function() {
                  this.checked = true;
              });
          }
      });
  })( AUTOSHIP_SAVE_PAYMENT_ELEMENT_NAMES );
}
