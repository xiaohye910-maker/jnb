/**
 * Quicklinks Settings Admin JavaScript.
 *
 * Handles AJAX form submission and action buttons for the Quicklinks settings page.
 *
 * @package Autoship
 * @since   3.2.0
 */

/* global jQuery, autoshipQuicklinksSettings */

(function($) {
    'use strict';

    /**
     * Quicklinks Settings Handler.
     */
    var QuicklinksSettings = {

        /**
         * Initialize the settings handler.
         */
        init: function() {
            console.log('Autoship Quicklinks Settings JS loaded');
            console.log('Form found:', $('#autoship-quicklinks-settings-form').length);
            this.bindEvents();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            // Save settings button.
            $('#autoship-quicklinks-save-settings').on('click', this.handleSaveSettings.bind(this));

            // Action buttons.
            $('#autoship-quicklinks-flush-rewrite').on('click', this.handleFlushRewrite.bind(this));
            $('#autoship-quicklinks-run-cleanup').on('click', this.handleRunCleanup.bind(this));
            $('#autoship-quicklinks-clear-rate-limits').on('click', this.handleClearRateLimits.bind(this));
        },

        /**
         * Handle save settings button click.
         *
         * @param {Event} e Click event.
         */
        handleSaveSettings: function(e) {
            e.preventDefault();

            var self = this;
            var $container = $('#autoship-quicklinks-settings-form');
            var $button = $('#autoship-quicklinks-save-settings');
            var $feedback = $('#autoship-quicklinks-save-feedback');

            // Disable button and show loading state.
            $button.prop('disabled', true).text(autoshipQuicklinksSettings.strings.saving);
            $feedback.html('').removeClass('notice notice-success notice-error');

            // Collect form data from inputs within the container.
            var formData = {
                action: 'autoship_quicklinks_save_settings',
                nonce: autoshipQuicklinksSettings.settingsNonce,
                enabled: $container.find('#quicklinks_enabled').is(':checked') ? 'yes' : 'no',
                rate_limiter_enabled: $container.find('#rate_limiter_enabled').is(':checked') ? 'yes' : 'no',
                rate_limiter_strategy: $container.find('#rate_limiter_strategy').val(),
                rate_limiter_max_attempts: $container.find('#rate_limiter_max_attempts').val(),
                rate_limiter_window_seconds: $container.find('#rate_limiter_window_seconds').val(),
                scanner_enabled: $container.find('#scanner_enabled').is(':checked') ? 'yes' : 'no',
                scanner_behavioral_detection: $container.find('#scanner_behavioral_detection').is(':checked') ? 'yes' : 'no',
                scanner_require_accept_language: $container.find('#scanner_require_accept_language').is(':checked') ? 'yes' : 'no',
                scanner_require_html_accept: $container.find('#scanner_require_html_accept').is(':checked') ? 'yes' : 'no',
                scanner_min_suspicious_count: $container.find('#scanner_min_suspicious_count').val(),
                maintenance_confirmation_retention_days: $container.find('#maintenance_confirmation_retention_days').val(),
                maintenance_audit_retention_days: $container.find('#maintenance_audit_retention_days').val()
            };

            $.ajax({
                url: autoshipQuicklinksSettings.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        self.showFeedback($feedback, response.data.message, 'success');
                    } else {
                        self.showFeedback($feedback, response.data.message || autoshipQuicklinksSettings.strings.saveError, 'error');
                    }
                },
                error: function() {
                    self.showFeedback($feedback, autoshipQuicklinksSettings.strings.saveError, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text(autoshipQuicklinksSettings.strings.saved.replace(' successfully.', ''));
                    setTimeout(function() {
                        $button.text('Save Settings');
                    }, 2000);
                }
            });
        },

        /**
         * Handle flush rewrite rules button click.
         *
         * @param {Event} e Click event.
         */
        handleFlushRewrite: function(e) {
            e.preventDefault();

            var self = this;
            var $button = $(e.currentTarget);
            var $feedback = $('#autoship-quicklinks-action-feedback');

            this.disableButton($button, autoshipQuicklinksSettings.strings.flushing);

            $.ajax({
                url: autoshipQuicklinksSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'autoship_quicklinks_flush_rewrite',
                    nonce: autoshipQuicklinksSettings.actionNonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showFeedback($feedback, response.data.message, 'success');
                    } else {
                        self.showFeedback($feedback, response.data.message || autoshipQuicklinksSettings.strings.flushError, 'error');
                    }
                },
                error: function() {
                    self.showFeedback($feedback, autoshipQuicklinksSettings.strings.flushError, 'error');
                },
                complete: function() {
                    self.enableButton($button, 'Flush Rewrite Rules');
                }
            });
        },

        /**
         * Handle run cleanup button click.
         *
         * @param {Event} e Click event.
         */
        handleRunCleanup: function(e) {
            e.preventDefault();

            var self = this;
            var $button = $(e.currentTarget);
            var $feedback = $('#autoship-quicklinks-action-feedback');

            this.disableButton($button, autoshipQuicklinksSettings.strings.cleaningUp);

            $.ajax({
                url: autoshipQuicklinksSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'autoship_quicklinks_run_cleanup',
                    nonce: autoshipQuicklinksSettings.actionNonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showFeedback($feedback, response.data.message, 'success');
                    } else {
                        self.showFeedback($feedback, response.data.message || autoshipQuicklinksSettings.strings.cleanupError, 'error');
                    }
                },
                error: function() {
                    self.showFeedback($feedback, autoshipQuicklinksSettings.strings.cleanupError, 'error');
                },
                complete: function() {
                    self.enableButton($button, 'Run Cleanup Now');
                }
            });
        },

        /**
         * Handle clear rate limits button click.
         *
         * @param {Event} e Click event.
         */
        handleClearRateLimits: function(e) {
            e.preventDefault();

            var self = this;
            var $button = $(e.currentTarget);
            var $feedback = $('#autoship-quicklinks-action-feedback');

            // Confirm before clearing.
            if (!confirm('Are you sure you want to clear all rate limit data? This will allow all IP addresses to access Quicklinks again.')) {
                return;
            }

            this.disableButton($button, autoshipQuicklinksSettings.strings.clearingLimits);

            $.ajax({
                url: autoshipQuicklinksSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'autoship_quicklinks_clear_rate_limits',
                    nonce: autoshipQuicklinksSettings.actionNonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showFeedback($feedback, response.data.message, 'success');
                    } else {
                        self.showFeedback($feedback, response.data.message || autoshipQuicklinksSettings.strings.clearLimitError, 'error');
                    }
                },
                error: function() {
                    self.showFeedback($feedback, autoshipQuicklinksSettings.strings.clearLimitError, 'error');
                },
                complete: function() {
                    self.enableButton($button, 'Clear Rate Limits');
                }
            });
        },

        /**
         * Disable a button and show loading text.
         *
         * @param {jQuery} $button The button element.
         * @param {string} text    Loading text.
         */
        disableButton: function($button, text) {
            $button.prop('disabled', true);
            $button.data('original-html', $button.html());
            $button.html('<span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>' + text);
        },

        /**
         * Enable a button and restore original text.
         *
         * @param {jQuery} $button The button element.
         * @param {string} text    Original text.
         */
        enableButton: function($button, text) {
            $button.prop('disabled', false);
            var originalHtml = $button.data('original-html');
            if (originalHtml) {
                $button.html(originalHtml);
            } else {
                $button.text(text);
            }
        },

        /**
         * Show feedback message.
         *
         * @param {jQuery} $element The feedback element.
         * @param {string} message  The message to show.
         * @param {string} type     Message type: 'success' or 'error'.
         */
        showFeedback: function($element, message, type) {
            var alertClass = type === 'success' ? 'asc-alert-success' : 'asc-alert-danger';
            var iconClass = type === 'success' ? 'pi-check-circle' : 'pi-times-circle';
            $element.html(
                '<div class="asc-alert ' + alertClass + '">' +
                    '<i class="pi ' + iconClass + '"></i>' +
                    '<div class="asc-alert-content">' + message + '</div>' +
                '</div>'
            );

            // Auto-hide success messages after 5 seconds.
            if (type === 'success') {
                setTimeout(function() {
                    $element.fadeOut(300, function() {
                        $element.html('').show();
                    });
                }, 5000);
            }
        }
    };

    // Initialize on document ready.
    $(document).ready(function() {
        QuicklinksSettings.init();
    });

})(jQuery);
