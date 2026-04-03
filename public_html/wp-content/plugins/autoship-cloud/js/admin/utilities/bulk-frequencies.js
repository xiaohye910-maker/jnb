/**
 * Bulk Frequency Options Update Utility.
 *
 * Handles the UI and AJAX batch processing for bulk-updating
 * product frequency options from the Utilities admin tab.
 *
 * @package Autoship
 * @since 2.11.1
 */
(function ($) {
  "use strict";

  var maxOptions = 5;
  var processRunning = false;
  var ajaxRequest; // Variable to store the request for cancellation

  var refreshFrequencyOptionsState = function () {
    var container = $("#frequency_update_options");

    container.find(".frequency_option").each(function (index) {
      var newIndex = index + 1; // Start from 1
      $(this).attr("id", "frequency_option_" + newIndex);
      $(this)
        .find("h3")
        .text("Frequency Option " + newIndex);

      $(this)
        .find("label")
        .each(function () {
          var oldFor = $(this).attr("for");
          if (oldFor) {
            $(this).attr("for", oldFor.replace(/\d+$/, newIndex));
          }
        });

      $(this)
        .find("select, input")
        .each(function () {
          var oldName = $(this).attr("name");
          if (oldName) {
            $(this).attr("name", oldName.replace(/\d+$/, newIndex));
          }
        });
    });

    // Enable or disable add frequency option button.
    $(".button-create-frequency").prop(
      "disabled",
      container.find(".frequency_option").length >= maxOptions
    );

    // Shows or hides remove buttons.
    $(".remove-frequency").toggle(
      container.find(".frequency_option").length > 1
    );

    // Clear errors from UI.
    $("#frequency_update_options .frequency_option").each(function () {
      $(this).find(".error").remove();
    });

    $("#frequency_update_settings").each(function () {
      $(this).find(".error").remove();
    });
  };

  var resetFrequencyOptionsButtonsState = function () {
    var progressBar = $("#autoship_bulk_frequency_options_progress_bar");
    var progressContainer = $(
      "#autoship_bulk_frequency_options_progress_container"
    );
    var submitButton = $(".button-start-bulk-frequency-update");
    var cancelButton = $(".button-cancel-frequency-update");
    var addButton = $(".button-create-frequency");

    // Reset Scrollbar State
    progressContainer.fadeOut("slow");
    setTimeout(function () {
      progressBar.animate(
        {
          width: "10%",
        },
        1200
      );
    }, 1000);

    // Reset Buttons State
    submitButton.prop("disabled", false);
    submitButton.show();
    addButton.show();
    cancelButton.hide();

    // Enable Remove Frequency Option Buttons
    $("#frequency_update_options")
      .find(".remove-frequency")
      .each(function () {
        $(this).prop("disabled", false);
        $(this).show();
      });
  };

  var showCancelUpdateFrequencyOptionsButton = function () {
    var progressContainer = $(
      "#autoship_bulk_frequency_options_progress_container"
    );
    var progressBar = $("#autoship_bulk_frequency_options_progress_bar");
    var submitButton = $(".button-start-bulk-frequency-update");
    var cancelButton = $(".button-cancel-frequency-update");
    var addButton = $(".button-create-frequency");

    $("#frequency_update_options")
      .find(".remove-frequency")
      .each(function () {
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
  };

  var setFrequencyOptionsNoticesState = function (notice, subnotice) {
    $("#autoship_bulk_frequency_options_notice").text(notice);
    $("#autoship_bulk_frequency_options_subnotice").text(subnotice);
  };

  var setupCreateFrequencyOptionButton = function () {
    $(".button-create-frequency").on("click", function (e) {
      e.preventDefault();

      var container = $("#frequency_update_options");
      var count = container.children().length;
      var option = container.children().first();

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
          $(
            '<a href="#" class="remove-frequency" style="text-decoration: none; color: red;"><i class="dashicons dashicons-trash"></i></a>'
          ).insertAfter(newOption.find("h3"));
        }

        container.append(newOption);

        refreshFrequencyOptionsState();
      }
    });
  };

  var setupRemoveFrequencyOptionButton = function () {
    $("#frequency_update_options").on(
      "click",
      ".remove-frequency",
      function (e) {
        e.preventDefault();

        if (
          $("#frequency_update_options").find(".frequency_option").length > 1
        ) {
          $(this).closest(".frequency_option").remove();
          refreshFrequencyOptionsState();
        }
      }
    );
  };

  var setupCancelUpdateFrequencyOptionsButton = function () {
    $(".button-cancel-frequency-update").on("click", function () {
      if (ajaxRequest) {
        ajaxRequest.abort();
      }

      processRunning = false;

      setFrequencyOptionsNoticesState("Batch Processing Cancelled", "");
      resetFrequencyOptionsButtonsState();
      refreshFrequencyOptionsState();
    });
  };

  var updateFrequencyOptionsPage = function (
    page,
    pages,
    processed,
    formData
  ) {
    if (page > pages) {
      processRunning = false;

      return;
    }

    $("#autoship_bulk_frequency_options_subnotice").text(
      "Processing Batch " + page + " Started..."
    );

    formData.current_count = processed;
    formData.current_page = page;

    ajaxRequest = $.post(
      autoshipBulkFrequencies.ajaxUrl,
      formData,
      function (response) {
        if (response.success) {
          var progressBar = $(
            "#autoship_bulk_frequency_options_progress_bar"
          );
          progressBar.animate(
            {
              width: response.data.total_pct + "%",
            },
            500
          );

          $("#autoship_bulk_frequency_options_notice").html(
            response.data.notice
          );

          if (response.data.total_pct >= 100) {
            // The process is completed.
            processRunning = false;

            $("#autoship_bulk_frequency_options_subnotice").text("");

            resetFrequencyOptionsButtonsState();

            refreshFrequencyOptionsState();
          } else {
            updateFrequencyOptionsPage(
              page + 1,
              pages,
              response.data.current_count,
              formData
            );
          }
        } else {
          processRunning = false;

          showFrequencyOptionUpdateError(response.data.notice);
        }
      },
      "json"
    ).fail(function () {
      processRunning = false;

      showFrequencyOptionUpdateError(
        "An error occurred while starting the process."
      );
    });
  };

  var validateFrequencyOptions = function () {
    $("#frequency_update_options .frequency_option").each(function () {
      $(this).find(".error").remove();
    });

    var isValid = true;
    $("#frequency_update_options .frequency_option").each(function () {
      var type = $(this)
        .find("select[name^='frequency_number_option']")
        .val();

      if (type === undefined || type === "") {
        $(this)
          .find("select[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">Select a frequency type.</small>'
          );
        $(this)
          .find("select[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }

      var rawNumber = $(this)
        .find("input[name^='frequency_number_option']")
        .val();
      var floatNumber = parseFloat(rawNumber);
      if (isNaN(floatNumber) || floatNumber <= 0) {
        $(this)
          .find("input[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">The frequency number must be a positive integer greater than 0.</small>'
          );
        $(this)
          .find("input[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }

      if (floatNumber % 1 > 0.0) {
        $(this)
          .find("input[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">The frequency number must be a positive integer grater than 0, but no decimals allowed.</small>'
          );
        $(this)
          .find("input[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }

      var number = parseInt(rawNumber, 10);
      if (isNaN(number) || number <= 0) {
        $(this)
          .find("input[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">The frequency number must be a positive integer greater than 0.</small>'
          );
        $(this)
          .find("input[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }

      if (
        (type === "Days" || type === "Weeks" || type === "Months") &&
        number > 365
      ) {
        $(this)
          .find("input[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">The frequency number must be an integer between 1 and 365 inclusive.</small>'
          );
        $(this)
          .find("input[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }

      if (type === "DayOfTheWeek" && number > 7) {
        $(this)
          .find("input[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">The frequency number must be an integer between 1 and 7 inclusive. The allowed values are: 1 = Sunday, 2 = Monday, etc.</small>'
          );
        $(this)
          .find("input[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }

      if (type === "DayOfTheMonth" && number > 31) {
        $(this)
          .find("input[name^='frequency_number_option']")
          .parent()
          .append(
            '<small class="error" style="color: red;">The frequency number must be an integer between 1 and 31 inclusive</small>'
          );
        $(this)
          .find("input[name^='frequency_number_option']")
          .focus();
        isValid = false;
        return false;
      }
    });

    return isValid;
  };

  var showFrequencyOptionUpdateError = function (message) {
    setFrequencyOptionsNoticesState(message, "");
    resetFrequencyOptionsButtonsState();
    refreshFrequencyOptionsState();

    processRunning = false;
  };

  var setupUpdateFrequencyOptionButton = function () {
    $(".button-start-bulk-frequency-update").on("click", function (e) {
      e.preventDefault();

      if (processRunning) {
        return;
      }

      $("#frequency_update_settings").each(function () {
        $(this).find(".error").remove();
      });

      var rawBatchSize = $("#frequency_update_batch_size").val();

      var floatBatchSize = parseFloat(rawBatchSize);
      if (isNaN(floatBatchSize) || floatBatchSize <= 0) {
        $("#frequency_update_batch_size")
          .parent()
          .append(
            '<small class="error" style="color: red;">The batch size number must be a positive integer greater than 0.</small>'
          );
        $("#frequency_update_batch_size").focus();

        return;
      }

      if (floatBatchSize % 1 > 0.0) {
        $("#frequency_update_batch_size")
          .parent()
          .append(
            '<small class="error" style="color: red;">The batch size must be a positive integer grater than 0, but no decimals allowed.</small>'
          );
        $("#frequency_update_batch_size").focus();
        return;
      }

      var batchSize = parseInt(rawBatchSize, 10);
      if (isNaN(batchSize) || batchSize <= 0) {
        $("#frequency_update_batch_size")
          .parent()
          .append(
            '<small class="error" style="color: red;">The batch size number must be a positive integer greater than 0.</small>'
          );
        $("#frequency_update_batch_size").focus();
        return;
      }

      var rawTotalCount = $("#frequency_update_products_total").val();
      var totalCount = parseInt(rawTotalCount, 10);
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
        action: autoshipBulkFrequencies.action,
        nonce: autoshipBulkFrequencies.nonce,
        batch_size: $("#frequency_update_batch_size").val(),
        frequencies: [], // Array to store frequency data
        total_count: $("#frequency_update_products_total").val(),
        current_count: $("#frequency_update_products_count").val(),
        current_page: $("#frequency_update_products_page").val(),
      };

      $("#frequency_update_options .frequency_option").each(function () {
        var frequencyData = {
          id: $(this).attr("id"), // Unique ID
          frequency_type: $(this)
            .find("select[name^='frequency_number_option']")
            .val(),
          frequency_number: $(this)
            .find("input[name^='frequency_number_option']")
            .val(),
          display_name: $(this)
            .find("input[name^='frequency_display_name_option']")
            .val(),
        };

        formData.frequencies.push(frequencyData);
      });

      setFrequencyOptionsNoticesState(
        "Batch Processing Started",
        "Processing Batch 1 Started..."
      );
      showCancelUpdateFrequencyOptionsButton();

      processRunning = true;

      updateFrequencyOptionsPage(1, pages, 0, formData);
    });
  };

  var initializeFrequencyOptionsBulkUpdater = function () {
    setupCreateFrequencyOptionButton();
    setupRemoveFrequencyOptionButton();
    setupUpdateFrequencyOptionButton();

    setupCancelUpdateFrequencyOptionsButton();

    // Remove the remove link from the first frequency option on first load.
    if (!$("#frequency_option_1").find(".remove-frequency").length) {
      $(
        '<a href="#" class="remove-frequency" style="text-decoration: none; color: red;"><i class="dashicons dashicons-trash"></i></a>'
      ).insertAfter("#frequency_option_1 h3");
    }

    // Ensure initial UI state
    refreshFrequencyOptionsState();
  };

  $(document).ready(function () {
    initializeFrequencyOptionsBulkUpdater();
  });
})(jQuery);
