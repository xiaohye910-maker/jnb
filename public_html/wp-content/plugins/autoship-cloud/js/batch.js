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

  var ajaxRequest; // Variable to store the request for cancellation




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