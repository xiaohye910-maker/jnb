jQuery(document).ready(function($) {

    // Go to the lead step.
    $(document).on('click', '#welcome-next-button', function() {
        navigator('welcome', 'lead', 17);
    });

    // Go to the dismiss step.
    $(document).on('click', '#welcome-dismiss-button', function() {
        if ( $(this).hasClass('disabled') ) {
            return;
        }

        navigator('welcome', 'dismiss', 100);
    });

    // Go to the register step if lead can be created.
    $(document).on('click', '#lead-next-button', function() {
        const button = $('#lead-next-button');
        const link = $('#welcome-dismiss-button');

        disableCallToActionButton(button);
        disableSupportLink(link);

        $('#welcome-dismiss-button').removeAttr('href').addClass('disabled');

        const isValid = validateLeadForm();
        if (!isValid) {
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
            return;
        }

        const lead = {
            action:   'autoship_quicklaunch_lead_handler',
            email:    $('#autoship-lead-email').val().trim(),
            role:     $('#autoship-lead-role').val(),
            revenue:  $('#autoship-lead-revenue').val(),
            category: $('#autoship-lead-category').val(),
            reason:   $('#autoship-lead-reason').val(),
            terms:    $('#autoship-lead-terms').is(':checked')
        };

        $.ajax({
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:     lead,
            dataType: 'json'
        })
        .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                navigator('lead', 'register', 34);
            } else {
                showFormErrorMessage(response.data.message ?? 'An error occurred');

                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
            }
        })
        .fail(function() {
            showFormErrorMessage('There was an unexpected error while processing your request.');
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
        });
    });


    // Go to login step.
    $(document).on('click', '#register-login-button', function() {
        if ( $(this).hasClass('disabled') ) {
            return;
        }

        navigator('register', 'login');
    });



    // Go to product step if account can be created.
    $(document).on('click', '#register-next-button', function() {
        const button = $('#register-next-button');
        const link   = $('#register-login-button');

        disableCallToActionButton(button);
        disableSupportLink(link);

        const isValid = validateRegistrationForm();
        if (!isValid) {
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
            return;
        }

        const iti = window.intlTelInput.getInstance(document.querySelector("#autoship-registration-phone-local"));

        let account = {
            action:   'autoship_quicklaunch_registration_handler',
            email:    $('#autoship-registration-email').val().trim(),
            password: $('#autoship-registration-password').val(),
            phone:    ''
        };

        // Up to this point, the phone number is valid or empty if it's not required.
        account.phone = iti.getNumber();
        if (account.phone === '') {
            account.phone = '0000000000';
        }

        // Remove the '+' sign from the phone number.
        account.phone = account.phone.replace(/\+/g, '');

        $.ajax({
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:     account,
            dataType: 'json'
        })
        .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                navigator('register', 'product', 68);
            } else {
                showFormErrorMessage(response.data.message ?? 'An error occurred');

                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
            }
        })
        .fail(function() {
            showFormErrorMessage('There was an unexpected error while processing your request.');
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
        });
    });

    // Go to register step.
    $(document).on('click', '#login-register-button', function() {
        if ( $(this).hasClass('disabled') ) {
            return;
        }

        navigator('login', 'register');
    });

    $(document).on('click', '#login-next-button', function() {
        const button = $('#login-next-button');
        const link   = $('#login-register-button');

        disableCallToActionButton(button);
        disableSupportLink(link);

        const isValid = validateLoginForm();
        if (!isValid) {
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
            return;
        }

        const login = {
            action:   'autoship_quicklaunch_login_handler',
            email:    $('#autoship-login-email').val().trim(),
            password: $('#autoship-login-password').val()
        };

        $.ajax({
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:     login,
            dataType: 'json'
        })
        .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                const destination = response.data.operation ?? 'choose';

                if (destination === 'created') {
                    navigator('login', 'product', 51);
                }
                else {
                    navigator('login', 'connection', 51);
                }
            } else {
                showFormErrorMessage(response.data.message ?? 'An error occurred');

                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
            }
        })
        .fail(function() {
            showFormErrorMessage('There was an unexpected error while processing your request.');
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
        });
    });

    // Go to the product step if site can be connected.
    $(document).on('click', '#connection-next-button', function() {
        const button = $('#connection-next-button');
        const link   = $('#connection-back-button');

        disableCallToActionButton(button);
        disableSupportLink(link);

        const option = $('#autoship-connection-type').val();

        let operation = {
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:      {},
            dataType: 'json'
        };

        if (option === 'create')
        {
            const isValid = validateSiteCreationForm();
            if (!isValid) {
                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
                return;
            }

            operation.data.action = 'autoship_quicklaunch_connection_creation_handler';
            operation.data.store  = $('#autoship-connection-store-name').val().trim();
        }
        else
        {
            const isValid = validateSiteConnectionForm();
            if (!isValid) {
                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
                return;
            }

            operation.data.action = 'autoship_quicklaunch_connection_site_handler';
            operation.data.site  = $('#autoship-connection-site-id').val();
        }

        $.ajax(operation)
         .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                navigator('connection', 'product', 68);
            } else {
                showFormErrorMessage(response.data.message ?? 'An error occurred');

                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
            }
        })
        .fail(function() {
            showFormErrorMessage('There was an unexpected error while processing your request.');
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
        });
    });

    // Shows or hides the sites connection container.
    $(document).on('change', '#autoship-connection-type', function() {
        const option = $(this).val();
        if (option === 'create')
        {
            $('#autoship-connection-sites-creator').show();
            $('#autoship-connection-sites-container').hide();
        }
        else
        {
            $('#autoship-connection-sites-creator').hide();
            $('#autoship-connection-sites-container').show();
        }
    });


    // $(document).on('click', '#autoship-button-demo', function() {
    //     window.open('https://autoship.cloud/request-a-demo/', '_blank');
    // });

    // Go to the login step if the account can be logged out.
    $(document).on('click', '#connection-back-button', function() {

        // Sign out action.
        const button = $('#connection-next-button');
        const link   = $('#connection-back-button');

        disableCallToActionButton(button);
        disableSupportLink(link);

        const logout = {
            action:   'autoship_quicklaunch_logout_handler'
        };

        $.ajax({
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:     logout,
            dataType: 'json'
        })
        .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                navigator('connection', 'login', 51);
            } else {
                showFormErrorMessage(response.data.message ?? 'An error occurred');

                enableCallToActionButton(button, 'Continue');
                enableSupportLink(link);
            }
        })
        .fail(function() {
            showFormErrorMessage('There was an unexpected error while processing your request.');
            enableCallToActionButton(button, 'Continue');
            enableSupportLink(link);
        });
    });

    $(document).on('click', '.autoship-frequency-container-toggler', function() {
        $(this).parent().hide();


        const remaining = $('.autoship-frequency-container-row:hidden');
        if (remaining !== null && remaining.length > 0) {
            $('#autoship-frequency-add').attr('href', 'javascript:void(0)').removeClass('disabled');
        }
    });

    $(document).on('click', '#autoship-frequency-add', function() {
        const notshown = $('.autoship-frequency-container-row:hidden').first();

        if (notshown) {
            notshown.show();
        }

        const remaining = $('.autoship-frequency-container-row:hidden');
        if (remaining !== null && remaining.length <= 0) {
            $('#autoship-frequency-add').removeAttr('href').addClass('disabled');
        }
    });

    $(document).on('change', '#autoship-product-discount', function(){
        validateProductForm();
    });

    $(document).on('click', '#product-next-button', function() {
        const button = $('#product-next-button');

        disableCallToActionButton(button);

        const isValid = validateProductForm();
        if (!isValid) {
            enableCallToActionButton(button, 'Continue');
            return;
        }

        const product = buildProductData();

        $.ajax({
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:     product,
            dataType: 'json',
            timeout: 45000
        })
        .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                navigator('product', 'payments', 85);
            } else {
                showFormErrorMessage(response.data.message ?? 'An error occurred');

                enableCallToActionButton(button, 'Continue');
            }
        })
        .fail(function() {
            showFormErrorMessage('There was an unexpected error while processing your request.');
            enableCallToActionButton(button, 'Continue');
        });





    });

    // $(document).on('click', '#product-back-button', function() {
    //     $('#autoship-progress-bar').css('width', '68%');
    //     navigator('product', 'site');
    // });

    // $(document).on('click', '#product-setup-button', function() {
    //     $('#product-next-button').attr('disabled', false);
    // });


    $(document).on('click', '#payments-next-button', function() {
        $('#autoship-progress-bar').css('width', '100%');
        navigator('payments', 'completed');
    });

    $(document).on('click', '#payments-back-button', function() {
        $('#autoship-progress-bar').css('width', '85%');
        navigator('payments', 'product');
    });

    $(document).on('click', '.gateway-setup-button', function() {

        if (!confirm('Are you sure you want to install this payment gateway?')) {
            return;
        }

        const method = $(this).data('gateway-id');
        const button = $('#payments-next-button');
        disableCallToActionButton(button);

        const gateway = {
            action:     'autoship_quicklaunch_payment_method_handler',
            gateway_id: method,
        };

        $.ajax({
            url:      autoship_quicklauncher.ajax_url,
            method:   'POST',
            data:     gateway,
            dataType: 'json'
        })
        .done(function(response, textStatus, jqXHR) {
            if ( response.success ) {
                // Change the action.
                $(`#autoship_${method}_action`).html('<p class="autoship-highlighted-text">This payment gateway is enabled on Autoship.</p>');

                // Change the icon.
                $(`#autoship_${method}_status_icon`).attr('src', response.data.status_icon);

                $('#autoship_quicklaunch_proceed').val(response.data.can_proceed);
            } else {
                alert(response.data.message ?? 'An error occurred');
            }

            if ($('#autoship_quicklaunch_proceed').val() == 'true'){
                enableCallToActionButton(button, 'Finish');
            }
        })
        .fail(function() {
            alert(response.data.message ?? 'An error occurred');

            if ($('#autoship_quicklaunch_proceed').val() == 'true') {
                enableCallToActionButton(button, 'Finish');
            }
        });
    });



    $(document).on('click', '#completed-dashboard-button', function() {
        $('#autoship-progress-bar').css('width', '100%');
        window.location.href = autoship_quicklauncher.admin_url;
    });

    $(document).on('click', '#completed-product-button', function() {
        const productUrl = $(this).data('product-url');
        window.location.href = productUrl;
    });


    $(document).on('click', '#completed-reset-button', function() {
        $('#autoship-progress-bar').css('width', '0');
        const data = {
            action: 'autoship_quicklaunch_reset_handler',
        };

        $.post(autoship_quicklauncher.ajax_url, data, function(response) {
            navigator('completed', 'welcome');
        });
    });



    $(document).on('click', '#autoship-discount-enabler', function() {
        const $checked = $(this).is(':checked');
        if ($checked) {
            $('#autoship-discount-container').show();
        }
        else{
            $('#autoship-discount-container').hide();
        }
    });


    $(document).on('click', '#autoship-frequency-enabler', function() {
        const $checked = $(this).is(':checked');
        if ($checked) {

            $('#autoship-frequency-container').show();
        }
        else{
            $('#autoship-frequency-container').hide();
        }
    });

    $(document).on('click', '#product-creation-button', function() {
        window.location.href = 'post-new.php?post_type=product';
    });

    $(document).on('click', '#payments-contact-button', function() {
        if (typeof Beacon === 'function') {
            Beacon('open')
        } else {
            console.log('Beacon is not available.');
        }
    });



    const setWizardProgress = function(progress) {
        $('#autoship-progress-bar').css('width', `${progress}%`);
    }

    const navigator = function(current_step, next_step, progress = null) {
        if (progress != null && !isNaN(progress) && progress >= 0 && progress <= 100) {
            setWizardProgress(progress);
        }

        const data = {
            action: 'autoship_quicklaunch_step_handler',
            current_step: current_step,
            step: next_step,
        };

        $.post(autoship_quicklauncher.ajax_url, data, function(response) {
            $('#autoship-quicklaunch-content').html(response);

            enablePhoneInput();
        });
    }

    const isValidEmail = function (email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    const isValidPassword = function (password) {
        return /^.{10,16}$/.test(password);
    }

    const isValidStoreName = function (store) {
        return /^.{1,100}$/.test(store);
    }

    const removeErrorMessages = function() {
        // Remove any existing error messages.
        $('.autoship-error-message').remove();
        $('.autoship-invalid-field').removeClass('autoship-invalid-field');
    }

    const showFieldErrorLabel = function(field) {
        field.prev('label').addClass('autoship-invalid-field');
    }

    const showFormErrorMessage = function(message) {
        $('#autoship-quicklaunch-form-errors').html(`<span class="autoship-error-message">${message}</span>`);
    }


    const validateLeadForm = function(){
        let valid = true;

        removeErrorMessages();

        const email = $('#autoship-lead-email').val().trim();
        if ( email === '' || !isValidEmail(email) ) {
            showFieldErrorLabel($('#autoship-lead-email'));
            // $('#autoship-lead-email').prev('label').addClass('autoship-invalid-field');
            valid = false;
        }

        $('#autoship-lead-role, #autoship-lead-revenue, #autoship-lead-category, #autoship-lead-reason').each(function() {
            const validating = $(this).val();
            if (validating === null || validating === '') {
                showFieldErrorLabel($(this));
                // $(this).prev('label').addClass('autoship-invalid-field');
                valid = false;
            }
        });

        // Show the error in the form if the terms are not checked.
        if (!$('#autoship-lead-terms').is(':checked')) {
            showFormErrorMessage('Please accept the terms of service.');

            valid = false;
        }

        return valid;
    }


    const validateRegistrationForm = function(){
        let valid = true;

        removeErrorMessages();

        const email = $('#autoship-registration-email').val().trim();
        if ( email === '' || !isValidEmail(email) ) {
            showFieldErrorLabel($('#autoship-registration-email'));
            valid = false;
        }

        const password = $('#autoship-registration-password').val();
        if (password === '' || !isValidPassword(password)) {
            showFieldErrorLabel($('#autoship-registration-password'));
            showFormErrorMessage('The password must be between 10 and 16 characters.');
            valid = false;
        }

        const confirmation = $('#autoship-registration-confirmation').val();
        if (confirmation === '' || !isValidPassword(confirmation)) {
            showFieldErrorLabel($('#autoship-registration-confirmation'));
            showFormErrorMessage('The password must be between 10 and 16 characters.');

            valid = false;
        }

        if (valid && password !== confirmation) {
            showFormErrorMessage('The passwords do not match.');

            valid = false;
        }

        // Check if the phone number is required.
        autoship_quicklauncher.phone_required = autoship_quicklauncher.phone_required ?? true;
        
        const iti = window.intlTelInput.getInstance(document.querySelector("#autoship-registration-phone-local"));
        let phone = iti.getNumber();

        if (autoship_quicklauncher.phone_required || (phone !== '')) {
            
            if (valid && iti && !iti.isValidNumber()) {
                $('#autoship-registration-phone-local-label').addClass('autoship-invalid-field');
                showFormErrorMessage('The phone number must be valid.');

                valid = false;
            }
        }

        return valid;
    }


    const validateLoginForm = function(){
        let valid = true;

        removeErrorMessages();

        const email = $('#autoship-login-email').val().trim();
        if ( email === '' || !isValidEmail(email) ) {
            showFieldErrorLabel($('#autoship-login-email'));
            valid = false;
        }

        const password = $('#autoship-login-password').val();
        if (password === '' || !isValidPassword(password)) {
            showFieldErrorLabel($('#autoship-login-password'));
            valid = false;
        }

        return valid;
    }


    const validateSiteCreationForm = function(){
        let valid = true;

        removeErrorMessages();

        const store = $('#autoship-connection-store-name').val().trim();
        if ( store === '' || !isValidStoreName(store) ) {
            showFieldErrorLabel($('#autoship-connection-store-name'));
            valid = false;
        }

        return valid;
    }





    const validateSiteConnectionForm = function(){
        let valid = true;

        removeErrorMessages();

        const validating = $('#autoship-connection-site-id').val();

        if (validating === null || validating === '') {
            showFieldErrorLabel($('#autoship-connection-site-id'));
            valid = false;
        }

        return valid;
    }



    const validateProductForm = function(){
        let valid = true;

        removeErrorMessages();

        const product = $('#autoship-product-selector').val();
        if ( product == null || product === '' ) {
            showFieldErrorLabel($('#autoship-product-selector'));
            valid = false;
        }

        const discountEnabled    = $('#autoship-discount-enabler').is(':checked');
        if ( discountEnabled ) {
            const rawDiscount = $('#autoship-product-discount').val().trim();

            const floatDiscount = parseFloat(rawDiscount);
            // Check if the discount is a valid number between 1 and 99.
            if (isNaN(floatDiscount) || floatDiscount < 1.0 || floatDiscount > 99.0) {
                let message = 'The discount must be a number between 1 and 99.';

                showFieldErrorLabel($('#autoship-product-discount'));
                $('#autoship-product-discount-errors').html(`<span class="autoship-error-message">${message}</span>`);

                valid = false;
            }
            else if (floatDiscount % 1 > 0.0){
                // Check if the discount has decimals.
                let message = 'The discount must be a integer number.';

                showFieldErrorLabel($('#autoship-product-discount'));
                $('#autoship-product-discount-errors').html(`<span class="autoship-error-message">${message}</span>`);

                valid = false;
            }
        }

        const frequenciesEnabled = $('#autoship-frequency-enabler').is(':checked');
        if ( frequenciesEnabled ) {
            const rawFrequencyNumber1    = $('#autoship-product-frequency-number-1').val().trim();
            const rawFrequencyNumber2    = $('#autoship-product-frequency-number-2').val().trim();
            const rawFrequencyNumber3    = $('#autoship-product-frequency-number-3').val().trim();
            const frequencyType1         = $('#autoship-product-frequency-type-1').val();
            const frequencyType2         = $('#autoship-product-frequency-type-2').val();
            const frequencyType3         = $('#autoship-product-frequency-type-3').val();
            const isFrequencyType2Hidden = $('#autoship-frequency-container-2').is(':hidden');
            const isFrequencyType3Hidden = $('#autoship-frequency-container-3').is(':hidden');

            if (!validateFrequencyOption(1, rawFrequencyNumber1, frequencyType1) )
            {
                valid = false;
            }

            if (!isFrequencyType2Hidden && !validateFrequencyOption(2, rawFrequencyNumber2, frequencyType2) )
            {
                valid = false;
            }

            if (!isFrequencyType3Hidden && !validateFrequencyOption(3, rawFrequencyNumber3, frequencyType3) )
            {
                valid = false;
            }
        }

        return valid;
    }



    const validateFrequencyOption = function(position, rawNumber, type)
    {
        let errorPlaceholderName = `#autoship-product-frequency-errors-${position}`;

        var number = parseInt(rawNumber, 10);
        if (isNaN(number) || number <= 0) {
            // Show error in position.
            let message = 'The value must be a positive integer number.';

            $(errorPlaceholderName).html(`<p class="autoship-error-message"><strong>${message}</strong></p>`);

            return false;
        }

        if ((type === "Days" || type === "Weeks" || type === "Months") && number > 365)
        {
            // Show error in position.
            let message = 'The number must be less or equal to 365.';

            $(errorPlaceholderName).html(`<p class="autoship-error-message"><strong>${message}</strong></p>`);

            return false;
        }

        if (type === "DayOfTheWeek" && number > 7)
        {
            let message = 'The number must be less or equal to 7.';

            $(errorPlaceholderName).html(`<p class="autoship-error-message"><strong>${message}</strong></p>`);

            return false;
        }

        if (type === "DayOfTheMonth" && number > 31)
        {
            let message = 'The number must be less or equal to 31.';

            $(errorPlaceholderName).html(`<p class="autoship-error-message"><strong>${message}</strong></p>`);

            return false;
        }

        return true;
    }

    const buildProductData = function(){


        const productId          = $('#autoship-product-selector').val();
        const discountEnabled    = $('#autoship-discount-enabler').is(':checked');
        const frequenciesEnabled = $('#autoship-frequency-enabler').is(':checked');

        let discountPercent = 0;
        if ( discountEnabled ) {
            const rawDiscount = $('#autoship-product-discount').val().trim();
            discountPercent = parseFloat(rawDiscount);
        }

        const isFrequency2Enabled = frequenciesEnabled && !$('#autoship-frequency-container-2').is(':hidden');
        const isFrequency3Enabled = frequenciesEnabled && !$('#autoship-frequency-container-3').is(':hidden');

        const product = {
            action:              'autoship_quicklaunch_product_handler',
            product_id:          productId,
            discount_enabled:    discountEnabled,
            discount_percent:    discountPercent,
            frequency_1_enabled: frequenciesEnabled,
            frequency_1_type:    frequenciesEnabled ? $('#autoship-product-frequency-type-1').val() : 'months',
            frequency_1_number:  frequenciesEnabled ? parseInt($('#autoship-product-frequency-number-1').val().trim()) : 0,
            frequency_2_enabled: isFrequency2Enabled,
            frequency_2_type:    isFrequency2Enabled ? $('#autoship-product-frequency-type-2').val() : 'months',
            frequency_2_number:  isFrequency2Enabled ? parseInt($('#autoship-product-frequency-number-2').val().trim()) : 0,

            frequency_3_enabled: isFrequency3Enabled,
            frequency_3_type:    isFrequency3Enabled ? $('#autoship-product-frequency-type-3').val() : 'months',
            frequency_3_number:  isFrequency3Enabled ? parseInt($('#autoship-product-frequency-number-3').val().trim()) : 0,
        };

        return product;
    }

    const disableCallToActionButton = function(button) {
        button.attr('disabled', true).text('').addClass('loading');
    }
    const enableCallToActionButton = function(button, label) {
        button.attr('disabled', false).removeClass('loading').text(label);
    }

    const disableSupportLink = function(link) {
        link.removeAttr('href').addClass('disabled');
    }

    const enableSupportLink = function(link) {
        link.attr('href', '#').removeClass('disabled');
    }

    const enablePhoneInput = function() {
        const phoneNumberInput = document.querySelector('#autoship-registration-phone-local');
        if (phoneNumberInput != null) {
            const iti = window.intlTelInput(phoneNumberInput, {
                separateDialCode: false,
                initialCountry: 'us',
                preferredCountries: ['us']
            });

            phoneNumberInput.addEventListener('input', function(event) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    }

    enablePhoneInput();
});