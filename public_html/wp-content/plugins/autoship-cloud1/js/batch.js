jQuery(document).ready(function ($) {

    $("#cancel-msg").hide();
    var cancel_btn = $("button.cancel-import-schedules");

    cancel_btn.on("click",function () {
        var consent = confirm('Are you sure you want to cancel import?');
        if (!consent) {
            return;
        }

        location.reload();
        return;
    });

    function initBatch(action, container, getParameters, onComplete) {

        var $container = $(container);
        var count = {};
        var ids = [];

        function doBatch(lastId, parameters) {
            var queryStringData = {
                'action': action,
                'last_id': lastId
            };


            if (lastId !== 0 && action === "autoship_import_schedule_data") {

                cancel_btn.removeAttr("disabled");

                ids.push(lastId);

                uniqueCount = ids;

                uniqueCount.forEach(function (i) {
                    count[i] = (count[i] || 0) + 1;
                });

                if (count[lastId] >= 100) {
                    $('.batch-button').removeAttr('disabled');
                    cancel_btn.attr("disabled", "disabled");
                    $("#cancel-msg").show();
                    return;
                }
            }


            if (parameters) {
                for (var name in parameters) {
                    queryStringData[name] = parameters[name];
                }
            }
            var queryStringPairs = [];
            for (var name in queryStringData) {
                var pair = encodeURIComponent(name)
                    + '='
                    + encodeURIComponent(queryStringData[name]);
                queryStringPairs.push(pair);
            }

            var endpoint = ajaxurl + '?' + queryStringPairs.join('&');

            $.get(endpoint, function success(response) {
                updateProgress(response);

                if (response.result === 1) {

                  if (response.count < response.total) {
                      doBatch(response.last_id, parameters);
                  } else {
                      $('.batch-button').removeAttr('disabled');
                      $("button.cancel-import-schedules").attr("disabled", "disabled");
                  }

                }
            });
        }

        function updateProgress(data) {
            var percentProgress = Math.floor( data.complete_percent );
            var $batchProgress = $container.find('.batch-progress');
            $batchProgress.find('.meter').css('width', percentProgress + '%');
            if (percentProgress < 100) {
                $batchProgress.find('.readout').text(percentProgress + '%');
            } else {
                $batchProgress.find('.readout').text(percentProgress + '% Finished!');
                if (onComplete) {
                    onComplete.call($container, data);
                }
            }

            var $batchFailures = $container.find('.batch-failures');
            for (var i in data.failed) {
                $batchFailures.append(
                    '<li>Error: #' + data.failed[i][0] + ' with message: ' + data.failed[i][1] + '</li>'
                );
            }
        }

        function resetProgress() {
            var $batchProgress = $container.find('.batch-progress');
            $batchProgress.addClass('active');
            $batchProgress.find('.meter').css('width', '0%');
            $batchProgress.find('.readout').text('0%');
            $container.find('.batch-failures').empty();
        }

        $container.find('.batch-button').on('click', function () {
            var consent = confirm('Are you sure you want to continue? This action cannot be undone.');
            if (!consent) {
                return;
            }
            var parameters = null;
            if (getParameters) {
                parameters = getParameters.call($container);
                if (null === parameters) {
                    return;
                }
            }

            $(this).attr('disabled', 'disabled');


            resetProgress();
            doBatch(0, parameters);
        });
    }

    initBatch('autoship_import_schedule_data', '#autoship-import-schedules');
    initBatch('autoship_import_product_settings', '#autoship-import-product-settings');

});

jQuery(function ($) {

  var targets = [];

  function schedule_processor ( targets ) {

    function ajax_handler(){

      $.ajax({
        type: 'POST',
        dataType: 'json',
        data: targets.data,
        url: ajaxurl + '?action=' + targets.action,
        async: true,
        success: function(response) {

          targets.target_end = new Date();
          targets.response = response;

          target_render_handler( response );

          if ( targets.form.hasClass('active') && true == response.success && response.current_count < targets.total_count ){

            targets.data.current_page ++;
            targets.data.current_count = response.current_count;
            targets.target_start = new Date();

            ajax_handler();

          } else {

            response.success = false;
            targets.form.removeClass('active');
            targets.target_meter_bar.css( 'width' , '5%');
            target_render_handler( response );
            target_reset_handler( targets.form );

          }

          $( document ).trigger( 'autoshipBulkActionComplete', [ targets ] );
          $( document ).trigger( 'autoship_' + targets.batch_action + '_BulkActionComplete', [ targets ] );

        },
        failure: function(response) {

          response.success = false;
          targets.form.removeClass('active');
          targets.target_meter_bar.css( 'width' , '5%');
          target_render_handler( response );
          target_reset_handler( targets.form );

          $( document ).trigger( 'autoshipBulkActionFailed', [ targets ] );
          $( document ).trigger( 'autoship_' + targets.batch_action + '_BulkActionFailed', [ targets ] );
        }
      });

    }
    ajax_handler();

  }

  function target_reset_handler( form ){

    form.find('input[name=current_page]').val( 1 );
    form.find('input[name=current_count]').val( 0 );

  }

  function target_handler( form ){

    targets = [];
    targets.form = form;
    targets.target_total_counters = form.find('.total-toggle-counters');
    targets.target_btn = form.find('button.action');
    targets.target_cancel_btn = form.find('button.cancel-action');
    targets.target_meter = form.find('.autoship-meter');
    targets.target_meter_bar = targets.target_meter.find('span');
    targets.target_notice = form.find('.autoship-bulk-notice');
    targets.target_subnotice = form.find('.autoship-bulk-subnotice');

    targets.target_page_counter = form.find('input[name=current_page]');
    targets.target_page_count = targets.target_page_counter.val();
    targets.batch_action = form.find('input[name=batch_action]').val();
    targets.action = form.find('input[name=autoship-action]').val();
    targets.current_page = form.find('input[name=current_page]').val();
    targets.batch_size = form.find('input[name=batch_size]').val();
    targets.current_count = form.find('input[name=current_count]').val();
    targets.total_count = form.find('input[name=total_count]').val();

    targets.data = {};
    var inputs = form.find('input:not(:checkbox)');
    inputs.each( function(index, el) {

      key = $(this).attr('name');
      val = $(this).val();
      targets.data[key] = val;

    });
    var inputs = form.find('input:checked');
    inputs.each( function(index, el) {

      key = $(this).attr('name');
      val = $(this).val();
      targets.data[key] = val;

    });

    return targets;
  }

  function target_render_handler( response ){

    if ( true == response.success ){

        var average_string = '( ~' + ( targets.target_end - targets.target_start ) / 1000 + ' Sec Per Batch )...';

        targets.target_page_counter.val( response.page );
        targets.target_meter_bar.animate({
          width: response.total_pct + '%',
        }, 1200);
        targets.target_notice.fadeOut('fast', function(e) {
          targets.target_notice.html( response.notice ).fadeIn('slow');
        });
        targets.target_subnotice.fadeOut('fast', function(e) {
          targets.target_subnotice.html( 'Processing Batch ' + targets.data.current_page + ' Started ' + average_string ).fadeIn('slow');
        });

        if ( response.total_pct == 100 ){

          targets.target_meter_bar.animate({
            width: '5%',
          }, 1200);

        }

        return response.total_pct != 100;

    } else {

      targets.target_meter_bar.animate({
        width: '5%',
      }, 1200);
      targets.target_meter.fadeOut('slow');
      targets.target_notice.fadeOut('slow', function(e) {
        $(this).html( response.notice ).fadeIn('slow');
      });

      return false;

    }

  }

  var autoship_bulk_action_cancel = function(e){

    e.preventDefault();

    var form = $(this).closest('form');
    form.removeClass('active');

    targets.target_meter_bar.animate({
      width: '5%',
    }, 1200);
    targets.target_meter.fadeOut('slow');
    targets.target_notice.fadeOut('fast', function(e) {
      targets.target_notice.html( 'Batch Processing Cancelled.' ).fadeIn('slow');
    });
    targets.target_subnotice.fadeOut().html('');


  };

  $('.autoship-bulk-action').on( 'click', 'button.autoship-cancel-action', autoship_bulk_action_cancel );

  var autoship_bulk_action = function(e){

    e.preventDefault();

    var form = $(this).closest('.autoship-bulk-action');
    form.addClass('active');

    targets = target_handler( form );
    targets.target_notice.html('Batch Processing Started');
    targets.target_subnotice.html( 'Processing Batch ' + targets.data.current_page + ' Started...' ).fadeIn('slow');
    targets.target_meter.fadeIn();
    targets.target_start = new Date();

    schedule_processor ( targets );
    return false;

  };

  $('.autoship-bulk-action').on( 'click', 'button.autoship-action', autoship_bulk_action );

  var autoship_bulk_toggle = function(e){

    var count = $(this).attr('data-adjust-batch-total');
    var msg = $(this).attr('data-adjust-batch-notice');
    var form = $(this).closest('.autoship-bulk-action');
    form.find('.total-toggle-counters:not(input)').text( count );
    form.find('input.total-toggle-counters').val( count );
    form.find('.autoship-bulk-notice').html( msg );

  };

  $(".autoship-bulk-action").on( 'click', '.batch-total-toggle', autoship_bulk_toggle );

  // Wholesale bulk update SO pricing download link
  const WholesalePricingFileResult = function( event, resultsValues ){
    // Update the URL in the download link
    if ( typeof resultsValues.response !== undefined && typeof resultsValues.response.download_link !== undefined && resultsValues.response.download_link != '' )
    resultsValues.form.find("#wholesale-pricing-bulk-notice a").attr("href", resultsValues.response.download_link );
  }

  $( document ).on( 'autoship_autoship_bulk_update_scheduled_orders_wholesale_pricing_BulkActionComplete', WholesalePricingFileResult );

});


jQuery(document).ready(function ($) {

  var maxOptions  = 5; 
  var processRunning = false;
  var ajaxRequest; // Variable to store the request for cancellation


  var refreshFrequencyOptionsState = function() {
    var container = $("#frequency_update_options");

    container.find(".frequency_option").each(function (index) {
        var newIndex = index + 1; // Start from 1
        $(this).attr("id", "frequency_option_" + newIndex);
        $(this).find("h3").text("Frequency Option " + newIndex);

        $(this).find("label").each(function () {
            var oldFor = $(this).attr("for");
            if (oldFor) {
                $(this).attr("for", oldFor.replace(/\d+$/, newIndex));
            }
        });

        $(this).find("select, input").each(function () {
            var oldName = $(this).attr("name");
            if (oldName) {
                $(this).attr("name", oldName.replace(/\d+$/, newIndex));
            }
        });
    });

    // Enable or disable add frequency option button.
    $(".button-create-frequency").prop("disabled", container.find(".frequency_option").length >= maxOptions);

    // Shows or hides remove buttons.
    $(".remove-frequency").toggle(container.find(".frequency_option").length > 1);

    // Clear errors from UI.
    $("#frequency_update_options .frequency_option").each(function (index) {
      $(this).find('.error').remove();
    });

    $("#frequency_update_settings").each(function (index) {
      $(this).find('.error').remove();
    });
  }

  var resetFrequencyOptionsButtonsState = function() {
    var progressBar       = $("#autoship_bulk_frequency_options_progress_bar");
    var progressContainer = $("#autoship_bulk_frequency_options_progress_container");
    var submitButton      = $(".button-start-bulk-frequency-update");
    var cancelButton      = $(".button-cancel-frequency-update");
    var addButton         = $(".button-create-frequency");

    // Reset Scrollbar State
    progressContainer.fadeOut('slow');
    setTimeout(function(){
      progressBar.animate({
        width: '10%',
     }, 1200);
    }, 1000);
    
    // Reset Buttons State
    submitButton.prop("disabled", false);
    submitButton.show();
    addButton.show();
    cancelButton.hide();

    // Enable Remove Frequency Option Buttons
    $('#frequency_update_options').find('.remove-frequency').each(function(){
      $(this).prop("disabled", false);
      $(this).show();
    });
  }

  var showCancelUpdateFrequencyOptionsButton = function(){
    var progressBar       = $("#autoship_bulk_frequency_options_progress_bar");
    var progressContainer = $("#autoship_bulk_frequency_options_progress_container");
    var submitButton      = $(".button-start-bulk-frequency-update");
    var cancelButton      = $(".button-cancel-frequency-update");
    var addButton         = $(".button-create-frequency");
    
    $('#frequency_update_options').find('.remove-frequency').each(function(){
      $(this).prop("disabled", true);
      $(this).hide();
    });

    progressContainer.show();
    progressBar.css("width", "10%");
    submitButton.prop("disabled", true);
    submitButton.hide();
    addButton.prop("disabled", true);
    addButton.hide();
    cancelButton.show();
  }

  var setFrequencyOptionsNoticesState = function(notice, subnotice){
    $('#autoship_bulk_frequency_options_notice').text(notice);
    $('#autoship_bulk_frequency_options_subnotice').text(subnotice);
  }

  var setupCreateFrequencyOptionButton = function() {
    $(".button-create-frequency").on("click", function(e) {
      e.preventDefault();

      var container   = $("#frequency_update_options");
      var count       = container.children().length;
      var option      = container.children().first();

      if (count < maxOptions) {
        count++;

        var newOption = option.clone(); // Clone first option
        newOption.attr("id", "frequency_option_" + count);

        // Clear input values
        newOption.find("select, input").each(function () {
            $(this).val("");
        });

        // Add remove link
        if (!newOption.find(".remove-frequency").length) {
          $('<a href="#" class="remove-frequency" style="text-decoration: none; color: red;"><i class="dashicons dashicons-trash"></i></a>').insertAfter(newOption.find("h3"));
        }

        container.append(newOption);

        refreshFrequencyOptionsState();
      }
    });
  }

  var setupRemoveFrequencyOptionButton = function() {
    $("#frequency_update_options").on("click", ".remove-frequency", function (e) {
      e.preventDefault();
  
      if ($("#frequency_update_options").find(".frequency_option").length > 1) {
        $(this).closest(".frequency_option").remove();
        refreshFrequencyOptionsState();
      }
    });
  }

  var setupCancelUpdateFrequencyOptionsButton = function(){
    $(".button-cancel-frequency-update").on("click", function () {
      
      if (ajaxRequest) 
      {
        ajaxRequest.abort(); 
      }
      
      processRunning = false;

      setFrequencyOptionsNoticesState('Batch Processing Cancelled', '');
      resetFrequencyOptionsButtonsState();
      refreshFrequencyOptionsState();
    });
  }

  var updateFrequencyOptionsPage = function(page, pages, processed, formData){
    if (page > pages) {

      processRunning = false;

      return;
    }

    $('#autoship_bulk_frequency_options_subnotice').text( `Processing Batch ${page} Started...`);

    formData.current_count = processed;
    formData.current_page  = page;

    ajaxRequest = $.post(ajaxurl, formData, function (response) {
      if (response.success) {

        var progressBar = $("#autoship_bulk_frequency_options_progress_bar");
        progressBar.animate({
          width: response.data.total_pct + "%",
        }, 500);

        $('#autoship_bulk_frequency_options_notice').html( response.data.notice );

        if (response.data.total_pct >= 100) {

          // The process is completed.
          processRunning = false;

          $('#autoship_bulk_frequency_options_subnotice').text('');
          
          resetFrequencyOptionsButtonsState();

          refreshFrequencyOptionsState();
        }
        else{
          updateFrequencyOptionsPage(page + 1, pages, response.data.current_count, formData);
        }

      } else {
        processRunning = false;

        showFrequencyOptionUpdateError(response.data.notice);
      }
    }, 'json')
    .fail(function(){
      processRunning = false;

      showFrequencyOptionUpdateError("An error occurred while starting the process.");
    });
  }

  var validateFrequencyOptions = function() {
    $("#frequency_update_options .frequency_option").each(function (index) {
      $(this).find('.error').remove();
    });

    var isValid = true;
    $("#frequency_update_options .frequency_option").each(function (index) {
        var id     = $(this).attr("id");
        var type   = $(this).find("select[name^='frequency_number_option']").val();

        if (type == undefined || type == "") {
          $(this).find("select[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">Select a frequency type.</small>');
          $(this).find("select[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }
      
        var rawNumber = $(this).find("input[name^='frequency_number_option']").val();
        var floatNumber = parseFloat(rawNumber);
        if (isNaN(floatNumber) || floatNumber <= 0) {
          $(this).find("input[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">The frequency number must be a positive integer greater than 0.</small>');
          $(this).find("input[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }

        if (floatNumber % 1 > 0.0)
        {
          $(this).find("input[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">The frequency number must be a positive integer grater than 0, but no decimals allowed.</small>');
          $(this).find("input[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }

        var number    = parseInt(rawNumber, 10);
        if (isNaN(number) || number <= 0) {
          $(this).find("input[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">The frequency number must be a positive integer greater than 0.</small>');
          $(this).find("input[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }  

        if ((type == "Days" || type == "Weeks" || type == "Months") && number > 365) {
          $(this).find("input[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">The frequency number must be an integer between 1 and 365 inclusive.</small>');
          $(this).find("input[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }

        if (type == "DayOfTheWeek" && number > 7) {
          $(this).find("input[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">The frequency number must be an integer between 1 and 7 inclusive. The allowed values are: 1 = Sunday, 2 = Monday, etc.</small>');
          $(this).find("input[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }          

        if (type == "DayOfTheMonth" && number > 31) {
          $(this).find("input[name^='frequency_number_option']").parent().append('<small class="error" style="color: red;">The frequency number must be an integer between 1 and 31 inclusive</small>');
          $(this).find("input[name^='frequency_number_option']").focus();
          isValid = false;
          return false;
        }                    
    });

    return isValid;
  }


  var showFrequencyOptionUpdateError = function(message) {
    setFrequencyOptionsNoticesState(message, '');
    resetFrequencyOptionsButtonsState();
    refreshFrequencyOptionsState();

    processRunning = false;
  }

  var setupUpdateFrequencyOptionButton = function(){
    $(".button-start-bulk-frequency-update").on("click", function (e) {
        e.preventDefault(); 

        if (processRunning) 
        {
          return;
        }

        $("#frequency_update_settings").each(function (index) {
          $(this).find('.error').remove();
        });

        var rawBatchSize = $("#frequency_update_batch_size").val();

        
        var floatBatchSize = parseFloat(rawBatchSize);
        if (isNaN(floatBatchSize) || floatBatchSize <= 0) {
          $("#frequency_update_batch_size").parent().append('<small class="error" style="color: red;">The batch size number must be a positive integer greater than 0.</small>');
          $("#frequency_update_batch_size").focus();

          return;
        }

        if (floatBatchSize % 1 > 0.0)
        {
          $("#frequency_update_batch_size").parent().append('<small class="error" style="color: red;">The batch size must be a positive integer grater than 0, but no decimals allowed.</small>');
          $("#frequency_update_batch_size").focus();
          return;
        }
        
        var batchSize    = parseInt(rawBatchSize, 10);
        if (isNaN(batchSize) || batchSize <= 0) {
          $("#frequency_update_batch_size").parent().append('<small class="error" style="color: red;">The batch size number must be a positive integer greater than 0.</small>');
          $("#frequency_update_batch_size").focus();
          return;
        }

        var rawTotalCount = $('#frequency_update_products_total').val();
        var totalCount    = parseInt(rawTotalCount, 10);
        if (isNaN(totalCount) || totalCount <= 0) {
          return;
        }

        var pages = Math.ceil(totalCount / batchSize);
        if (pages <= 0) {
          return;
        }

        var isValid = validateFrequencyOptions();
        if (!isValid) {
          return;
        }

        var formData = {
          action:        "autoship_bulk_update_frequency_options_process_start", // AJAX action
          batch_size:    $("#frequency_update_batch_size").val(),
          frequencies:   [], // Array to store frequency data,
          total_count:   $('#frequency_update_products_total').val(),
          current_count: $('#frequency_update_products_count').val(),
          current_page:  $('#frequency_update_products_page').val(),
        };

        $("#frequency_update_options .frequency_option").each(function (index) {
          var frequencyData = {
              id:               $(this).attr("id"), // Unique ID
              frequency_type:   $(this).find("select[name^='frequency_number_option']").val(),
              frequency_number: $(this).find("input[name^='frequency_number_option']").val(),
              display_name:     $(this).find("input[name^='frequency_display_name_option']").val()
          };

          formData.frequencies.push(frequencyData);
        });

        setFrequencyOptionsNoticesState('Batch Processing Started', 'Processing Batch 1 Started...');
        showCancelUpdateFrequencyOptionsButton();

        processRunning = true;

        updateFrequencyOptionsPage(1, pages, 0, formData);
    });
  }

  var initializeFrequencyOptionsBulkUpdater = function() {

    setupCreateFrequencyOptionButton();
    setupRemoveFrequencyOptionButton();
    setupUpdateFrequencyOptionButton();

    setupCancelUpdateFrequencyOptionsButton();

    // Remove the remove link from the first frequency option on first load.
    if (!$("#frequency_option_1").find(".remove-frequency").length) {
      $('<a href="#" class="remove-frequency" style="text-decoration: none; color: red;"><i class="dashicons dashicons-trash"></i></a>').insertAfter("#frequency_option_1 h3");
    }

    // Ensure initial UI state
    refreshFrequencyOptionsState(); 
  }

  initializeFrequencyOptionsBulkUpdater();




    const initializeProductSyncUtility = function() {

        $('#autoship-product-sync-enabler').click(function(){
            $('.autoship-product-sync-error-container > .error-message').remove();

            ajaxRequest = $.post(ajaxurl, {
                action: "autoship_product_sync_enable"
            }, function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    $('.autoship-product-sync-error-container').html(`<span class="error-message">${response.data.notice}</span>`);
                }
            }, 'json')
            .fail(function(){
                $('.autoship-product-sync-error-container').html(`<span class="error-message">There was an issue while attempting to perform the operation.</span>`);
            });
        });

        const dialog = $('#autoship-disable-sync-dialog');

        if (!dialog.length) return;

        dialog.dialog({
            autoOpen: false,
            dialogClass : 'wp-dialog',
            width: 400,
            modal: true,
            title: 'Disable Product Sync',
            closeOnEscape : true,
            buttons: [
                {
                    text: "Continue",
                    class: "button-primary",
                    click: function() {
                        $('.autoship-product-sync-error-container > .error-message').remove();

                        ajaxRequest = $.post(ajaxurl, {
                            action: "autoship_product_sync_disable"
                        }, function (response) {
                            if (response.success) {
                                $('#autoship-disable-sync-dialog').dialog("close");
                                setTimeout(function(){
                                    window.location.reload();
                                }, 500);

                            } else {
                                $('.autoship-product-sync-error-container').html(`<span class="error-message">${response.data.notice}</span>`);
                                $('#autoship-disable-sync-dialog').dialog("close");
                            }
                        }, 'json')
                        .fail(function () {

                            $('.autoship-product-sync-error-container').html(`<span class="error-message">There was an issue while attempting to perform the operation.</span>`);
                            $('#autoship-disable-sync-dialog').dialog("close");
                        });
                    }
                },
                {
                    text: "Cancel",
                    click: function() {
                        $('#autoship-disable-sync-dialog').dialog("close");
                    }
                }
            ]
        });

        $('#autoship-product-sync-disabler').click(function(e){
            e.preventDefault();
            dialog.dialog('open');
        });

        $('#autoship-product-sync-updater').click(function(){
            $('.autoship-product-sync-error-container > .error-message').remove();

            const rawBatchSize = $('#product-sync-batch-size').val();
            const batchSize = parseInt(rawBatchSize, 10);

            if (isNaN(batchSize) || batchSize < 10 || batchSize > 500) {
                $('.autoship-product-sync-error-container').html(`<span class="error-message">The batch size must be positive integer between 10 and 500</span>`);
                return;
            }

            const rawInterval = $('#product-sync-interval').val();
            const interval = parseInt(rawInterval, 10);
            if (isNaN(interval) || interval < 300 || interval > 86400) {
                $('.autoship-product-sync-error-container').html(`<span class="error-message">The interval time must be between 300 and 86400 seconds. Please select one from the list.</span>`);
                return;
            }

            const logging = $('#product-sync-logging').is(':checked');

            ajaxRequest = $.post(ajaxurl, {
                action:   "autoship_product_sync_update",
                batch:    batchSize,
                interval: interval,
                logging:  logging,
            }, function (response) {
                if (response.success) {
                    alert(response.data.notice);
                } else {
                    $('.autoship-product-sync-error-container').html(`<span class="error-message">${response.data.notice}</span>`);
                }
            }, 'json')
            .fail(function(){
                $('.autoship-product-sync-error-container').html(`<span class="error-message">There was an issue while attempting to perform the operation.</span>`);
            });
        });
    }

    initializeProductSyncUtility();
});