/**
 * EFPP Remember Me Checkbox
 * Adds "Zapamiętaj mnie" (Remember me) checkbox before submit button in login forms
 */
(function($) {
    'use strict';

    function addRememberMeCheckbox() {
        $('.elementor-form').each(function() {
            var $form = $(this);
            var $widget = $form.closest('.elementor-widget-form');
            
            // Check if checkbox already exists
            if ($form.find('.efpp-remember-me-checkbox').length > 0) {
                return;
            }
            
            // Find submit button container
            var $submitContainer = $form.find('.e-form__buttons');
            
            if ($submitContainer.length === 0) {
                return;
            }
            
            // Check if widget has data attribute indicating Remember Me should be shown
            var showRememberMe = false;
            if ($widget.length > 0) {
                showRememberMe = $widget.data('efpp-show-remember-me') === '1' || $widget.data('efpp-show-remember-me') === 1;
            }
            
            // If data attribute is not set, don't add checkbox
            // This ensures checkbox only appears when EFPP Login action is enabled
            if (!showRememberMe) {
                return;
            }
            
            // Get form ID
            var formId = $form.data('id') || $widget.data('id') || 'form-' + Math.random().toString(36).substr(2, 9);
            var fieldId = 'efpp_remember_me_' + formId;
            
            // Create checkbox HTML
            var checkboxHtml = $('<div>', {
                'class': 'elementor-field-type-checkbox elementor-field-group elementor-column elementor-field-group-efpp-remember-me elementor-col-100',
                'style': 'margin-bottom: 1em;'
            }).append(
                $('<div>', {
                    'class': 'elementor-field elementor-field-checkbox'
                }).append(
                    $('<label>', {
                        'for': fieldId,
                        'class': 'elementor-field-label',
                        'style': 'display: flex; align-items: center; cursor: pointer;'
                    }).append(
                        $('<input>', {
                            'type': 'checkbox',
                            'id': fieldId,
                            'name': 'form_fields[efpp_remember_me]',
                            'value': '1',
                            'class': 'elementor-field efpp-remember-me-checkbox',
                            'style': 'margin-right: 0.5em; width: auto; height: auto;'
                        })
                    ).append(
                        $('<span>').text(efppRememberMe.text || 'Zapamiętaj mnie')
                    )
                )
            );
            
            // Insert before submit button
            $submitContainer.before(checkboxHtml);
        });
    }

    // Run on document ready
    $(document).ready(function() {
        setTimeout(addRememberMeCheckbox, 100);
    });

    // Run when Elementor frontend is ready
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        elementorFrontend.hooks.addAction('frontend/element_ready/form.default', function($scope) {
            setTimeout(addRememberMeCheckbox, 100);
        });
    }

    // Run when Elementor popup is shown
    $(window).on('elementor/popup/show', function() {
        setTimeout(addRememberMeCheckbox, 200);
    });

})(jQuery);

