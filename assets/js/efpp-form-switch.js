/**
 * EFPP Form Switch
 * Switches between Login and Reset Password forms
 */
(function($) {
    'use strict';

    function initFormSwitching() {
        // Debug only in development
        if (window.location.search.indexOf('efpp_debug=1') !== -1) {
            console.log('EFPP Form Switch: Initializing form switching');
            var allFormWidgets = $('.elementor-widget-form');
            console.log('EFPP Form Switch: Found form widgets:', allFormWidgets.length);
            allFormWidgets.each(function() {
                var $w = $(this);
                console.log('EFPP Form Switch: Widget data attributes:', {
                    showResetLink: $w.data('efpp-show-reset-link'),
                    showLoginLink: $w.data('efpp-show-login-link'),
                    loginFormId: $w.data('efpp-login-form-id'),
                    resetFormId: $w.data('efpp-reset-form-id')
                });
            });
        }
        
        // First, hide reset password forms if login form has reset link
        $('.elementor-widget-form[data-efpp-show-reset-link="1"]').each(function() {
            var $loginWidget = $(this);
            var resetFormId = $loginWidget.data('efpp-reset-form-id');
            
            if (resetFormId) {
                var $resetForm = findFormWidget(resetFormId);
                if ($resetForm.length > 0) {
                    $resetForm.addClass('efpp-form-hidden');
                    // CSS will handle hiding via .efpp-form-hidden class
                    if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                        console.log('EFPP Form Switch: Hidden reset password form on start', resetFormId);
                    }
                }
            }
        });
        
        // Also hide login forms if reset password form has login link
        $('.elementor-widget-form[data-efpp-show-login-link="1"]').each(function() {
            var $resetWidget = $(this);
            var loginFormId = $resetWidget.data('efpp-login-form-id');
            
            if (loginFormId) {
                var $loginForm = findFormWidget(loginFormId);
                if ($loginForm.length > 0) {
                    // Only hide if reset form is visible (not already hidden)
                    if (!$resetWidget.hasClass('efpp-form-hidden')) {
                        $loginForm.addClass('efpp-form-hidden');
                        // CSS will handle hiding via .efpp-form-hidden class
                        if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                            console.log('EFPP Form Switch: Hidden login form on start', loginFormId);
                        }
                    }
                }
            }
        });
        
        // Handle Login forms with Reset Password link
        $('.elementor-widget-form[data-efpp-show-reset-link="1"]').each(function() {
            var $widget = $(this);
            var $form = $widget.find('.elementor-form');
            
            if ($form.length === 0) {
                return;
            }
            
            // Check if link already exists
            if ($form.find('.efpp-switch-to-reset-link').length > 0) {
                return;
            }
            
            var loginFormId = $widget.data('efpp-login-form-id');
            var resetFormId = $widget.data('efpp-reset-form-id');
            var linkText = $widget.data('efpp-reset-link-text') || 'Zapomniałeś hasła?';
            
            if (!loginFormId || !resetFormId) {
                return;
            }
            
            // Find submit button container
            var $submitContainer = $form.find('.e-form__buttons');
            if ($submitContainer.length === 0) {
                return;
            }
            
            // Create link
            var $link = $('<a>', {
                'href': '#',
                'class': 'efpp-switch-to-reset-link',
                'data-login-form-id': loginFormId,
                'data-reset-form-id': resetFormId,
                'text': linkText
            });
            
            // Insert after submit button
            $submitContainer.after($link);
            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                console.log('EFPP Form Switch: Added reset password link to login form');
            }
        });
        
        // Handle Reset Password forms with Login link
        // Note: This form might be hidden initially, but we still need to add the link
        // First, check all reset password forms to see if they should have login link
        $('.elementor-widget-form').each(function() {
            var $widget = $(this);
            var $form = $widget.find('.elementor-form');
            
            // Check if this form has reset password action but no login link attribute
            if ($form.length > 0) {
                // Try to detect if this is a reset password form by checking for reset password fields
                var hasResetPasswordAction = $widget.find('input[type="email"][name*="email"]').length > 0 && 
                                             $widget.attr('data-efpp-show-login-link') !== '1';
                
                if (hasResetPasswordAction && window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.warn('EFPP Form Switch: Reset password form found without login link attributes. Make sure "Show Login link" is enabled in form settings.');
                }
            }
        });
        
        $('.elementor-widget-form[data-efpp-show-login-link="1"]').each(function() {
            var $widget = $(this);
            // Use .find() even if widget is hidden - jQuery can still find elements
            var $form = $widget.find('.elementor-form');
            
            if ($form.length === 0) {
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.log('EFPP Form Switch: Form not found in widget', $widget[0]);
                }
                return;
            }
            
            // Check if link already exists
            if ($form.find('.efpp-switch-to-login-link').length > 0) {
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.log('EFPP Form Switch: Login link already exists in reset form');
                }
                return;
            }
            
            var loginFormId = $widget.data('efpp-login-form-id');
            var resetFormId = $widget.data('efpp-reset-form-id');
            var linkText = $widget.data('efpp-login-link-text') || 'Wróć do logowania';
            
            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                console.log('EFPP Form Switch: Reset Password form found', {
                    loginFormId: loginFormId,
                    resetFormId: resetFormId,
                    linkText: linkText,
                    widget: $widget[0],
                    hasDataAttr: $widget.attr('data-efpp-show-login-link'),
                    formFound: $form.length > 0,
                    isHidden: $widget.hasClass('efpp-form-hidden') || $widget.css('display') === 'none'
                });
            }
            
            if (!loginFormId) {
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.warn('EFPP Form Switch: Missing login form ID', {
                        loginFormId: loginFormId,
                        resetFormId: resetFormId
                    });
                }
                return;
            }
            
            // If resetFormId is not provided, try to get it from form settings
            if (!resetFormId) {
                var $formElement = $form.find('form');
                if ($formElement.length > 0) {
                    resetFormId = $formElement.attr('id') || $formElement.attr('name') || '';
                }
                // Also try to get from widget data-id
                if (!resetFormId) {
                    var widgetId = $widget.data('id');
                    if (widgetId && window.elementorFrontend && window.elementorFrontend.config && window.elementorFrontend.config.elements && window.elementorFrontend.config.elements.data) {
                        var elementData = window.elementorFrontend.config.elements.data[widgetId];
                        if (elementData && elementData.settings) {
                            resetFormId = elementData.settings.form_id || elementData.settings.form_name || '';
                        }
                    }
                }
                
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.log('EFPP Form Switch: Auto-detected reset form ID', {
                        resetFormId: resetFormId,
                        widgetId: $widget.data('id')
                    });
                }
            }
            
            if (!resetFormId) {
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.warn('EFPP Form Switch: Could not determine reset form ID', {
                        loginFormId: loginFormId
                    });
                }
                // Still try to add link even without resetFormId - it might work
            }
            
            // Find submit button container - try multiple selectors
            var $submitContainer = $form.find('.e-form__buttons');
            if ($submitContainer.length === 0) {
                // Try alternative selector
                $submitContainer = $form.find('.elementor-field-type-submit');
            }
            if ($submitContainer.length === 0) {
                // Try finding submit button and get its parent
                var $submitButton = $form.find('button[type="submit"], input[type="submit"]');
                if ($submitButton.length > 0) {
                    $submitContainer = $submitButton.closest('.elementor-field-group, .e-form__buttons');
                }
            }
            
            if ($submitContainer.length === 0) {
                // Try form fields wrapper as fallback
                $submitContainer = $form.find('.elementor-form-fields-wrapper');
                if ($submitContainer.length === 0) {
                    // Last resort: append to form directly
                    $submitContainer = $form;
                }
            }
            
            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                console.log('EFPP Form Switch: Adding login link to reset form', {
                    submitContainerFound: $submitContainer.length > 0,
                    submitContainerClass: $submitContainer.attr('class'),
                    linkText: linkText
                });
            }
            
            // Create link
            var $link = $('<a>', {
                'href': '#',
                'class': 'efpp-switch-to-login-link',
                'data-login-form-id': loginFormId,
                'text': linkText
            });
            
            // Add reset form ID if available
            if (resetFormId) {
                $link.attr('data-reset-form-id', resetFormId);
            }
            
            // Insert after submit button container
            $submitContainer.after($link);
            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                console.log('EFPP Form Switch: Added login link to reset password form', {
                    linkAdded: $link.length > 0,
                    linkText: linkText
                });
            }
        });
    }

    function findFormWidget(formId) {
        var $foundWidget = null;
        
        // Try multiple methods to find the form
        // Method 1: By form ID attribute
        $foundWidget = $('.elementor-form[id="' + formId + '"]').closest('.elementor-widget-form');
        
        // Method 2: By form name attribute
        if ($foundWidget.length === 0) {
            $foundWidget = $('.elementor-form[name="' + formId + '"]').closest('.elementor-widget-form');
        }
        
        // Method 3: By widget data-id
        if ($foundWidget.length === 0) {
            $foundWidget = $('.elementor-widget-form[data-id="' + formId + '"]');
        }
        
        // Method 4: Search through all form widgets and check their settings
        if ($foundWidget.length === 0 && window.elementorFrontend && window.elementorFrontend.config && window.elementorFrontend.config.elements && window.elementorFrontend.config.elements.data) {
            $('.elementor-widget-form').each(function() {
                var $widget = $(this);
                var widgetId = $widget.data('id');
                
                if (widgetId && elementorFrontend.config.elements.data[widgetId]) {
                    var elementData = elementorFrontend.config.elements.data[widgetId];
                    if (elementData && elementData.settings) {
                        var settingsFormId = elementData.settings.form_id || elementData.settings.form_name || '';
                        if (settingsFormId === formId || widgetId === formId) {
                            $foundWidget = $widget;
                            return false; // break loop
                        }
                    }
                }
            });
        }
        
        return $foundWidget;
    }

    function switchForms($link) {
        var loginFormId = $link.data('login-form-id');
        var resetFormId = $link.data('reset-form-id');
        
        if (!loginFormId || !resetFormId) {
            console.warn('EFPP Form Switch: Missing form IDs');
            return;
        }
        
        // Find forms
        var $loginForm = findFormWidget(loginFormId);
        var $resetForm = findFormWidget(resetFormId);
        
        if (window.location.search.indexOf('efpp_debug=1') !== -1) {
            console.log('EFPP Form Switch: switchForms called', {
                loginFormId: loginFormId,
                resetFormId: resetFormId,
                loginFormFound: $loginForm.length > 0,
                resetFormFound: $resetForm.length > 0,
                loginFormElement: $loginForm[0],
                resetFormElement: $resetForm[0]
            });
        }
        
        // Switch forms with fade animations
        if ($loginForm.length > 0 && $resetForm.length > 0) {
            // Animation duration in milliseconds
            var fadeDuration = 300;
            
            // Hide login form, show reset form
            if ($link.hasClass('efpp-switch-to-reset-link')) {
                // First, ensure reset form is prepared but hidden
                $resetForm.removeClass('efpp-form-hidden');
                $resetForm.css({
                    'display': 'block',
                    'opacity': '0',
                    'visibility': 'visible'
                });
                $resetForm[0].style.removeProperty('display');
                
                // Fade out login form, then fade in reset form
                $loginForm.fadeOut(fadeDuration, function() {
                    // After login form fades out, hide it completely
                    $loginForm.addClass('efpp-form-hidden');
                    $loginForm[0].style.setProperty('display', 'none', 'important');
                    
                    // Now fade in reset form
                    $resetForm.fadeIn(fadeDuration, function() {
                        // Ensure it stays visible
                        $resetForm.css({
                            'opacity': '1',
                            'visibility': 'visible'
                        });
                        $resetForm[0].style.setProperty('display', 'block', 'important');
                    });
                });
                
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.log('EFPP Form Switch: Showing reset form', {
                        resetFormFound: $resetForm.length > 0,
                        resetFormId: resetFormId,
                        resetFormDisplay: $resetForm[0].style.display
                    });
                }
                
                // Always try to add login link to reset form when it becomes visible
                // This ensures the link is there even if it wasn't added initially
                setTimeout(function() {
                    var $resetFormElement = $resetForm.find('.elementor-form');
                    var $existingLink = $resetFormElement.find('.efpp-switch-to-login-link');
                    
                    if ($resetFormElement.length > 0) {
                        // Check if reset form has the data attribute for login link
                        var hasLoginLinkAttr = $resetForm.attr('data-efpp-show-login-link') === '1';
                        var resetLoginFormId = $resetForm.data('efpp-login-form-id');
                        var resetResetFormId = $resetForm.data('efpp-reset-form-id');
                        var resetLinkText = $resetForm.data('efpp-login-link-text') || 'Wróć do logowania';
                        
                        if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                            console.log('EFPP Form Switch: Checking reset form for login link', {
                                hasLoginLinkAttr: hasLoginLinkAttr,
                                resetLoginFormId: resetLoginFormId,
                                resetResetFormId: resetResetFormId,
                                existingLinkFound: $existingLink.length > 0,
                                formElementFound: $resetFormElement.length > 0
                            });
                        }
                        
                        // Add link if it doesn't exist and form has login link attribute
                        if ($existingLink.length === 0 && hasLoginLinkAttr && resetLoginFormId && resetResetFormId) {
                            // Find submit button container - try multiple selectors
                            var $submitContainer = $resetFormElement.find('.e-form__buttons');
                            if ($submitContainer.length === 0) {
                                $submitContainer = $resetFormElement.find('.elementor-field-type-submit');
                            }
                            if ($submitContainer.length === 0) {
                                var $submitButton = $resetFormElement.find('button[type="submit"], input[type="submit"]');
                                if ($submitButton.length > 0) {
                                    $submitContainer = $submitButton.closest('.elementor-field-group, .e-form__buttons');
                                }
                            }
                            if ($submitContainer.length === 0) {
                                // Fallback: append to form wrapper
                                $submitContainer = $resetFormElement.find('.elementor-form-fields-wrapper');
                                if ($submitContainer.length === 0) {
                                    $submitContainer = $resetFormElement;
                                }
                            }
                            
                            // Create and add link
                            var $newLink = $('<a>', {
                                'href': '#',
                                'class': 'efpp-switch-to-login-link',
                                'data-login-form-id': resetLoginFormId,
                                'data-reset-form-id': resetResetFormId,
                                'text': resetLinkText
                            });
                            
                            // Insert after submit container
                            $submitContainer.after($newLink);
                            
                            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                                console.log('EFPP Form Switch: Added login link to reset form after switch', {
                                    linkAdded: $newLink.length > 0,
                                    linkText: resetLinkText,
                                    submitContainerFound: $submitContainer.length > 0,
                                    submitContainerClass: $submitContainer.attr('class')
                                });
                            }
                        } else if ($existingLink.length > 0) {
                            // Link exists, make sure it's visible
                            $existingLink.css('display', 'block');
                            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                                console.log('EFPP Form Switch: Login link already exists in reset form');
                            }
                        } else if (!hasLoginLinkAttr || !resetLoginFormId || !resetResetFormId) {
                            // Try to re-initialize form switching
                            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                                console.warn('EFPP Form Switch: Reset form missing data attributes, re-initializing', {
                                    hasLoginLinkAttr: hasLoginLinkAttr,
                                    resetLoginFormId: resetLoginFormId,
                                    resetResetFormId: resetResetFormId
                                });
                            }
                            initFormSwitching();
                        }
                    }
                }, 100);
                
                // Scroll to reset form if needed (after fade in completes)
                setTimeout(function() {
                    $('html, body').animate({
                        scrollTop: $resetForm.offset().top - 100
                    }, fadeDuration);
                }, fadeDuration);
                
                // Re-run initFormSwitching after a delay to ensure link is added
                setTimeout(function() {
                    initFormSwitching();
                }, fadeDuration + 100);
            }
            // Hide reset form, show login form
            else if ($link.hasClass('efpp-switch-to-login-link')) {
                // First, ensure login form is prepared but hidden
                $loginForm.removeClass('efpp-form-hidden');
                $loginForm.css({
                    'display': 'block',
                    'opacity': '0',
                    'visibility': 'visible'
                });
                $loginForm[0].style.removeProperty('display');
                
                // Fade out reset form, then fade in login form
                $resetForm.fadeOut(fadeDuration, function() {
                    // After reset form fades out, hide it completely
                    $resetForm.addClass('efpp-form-hidden');
                    $resetForm[0].style.setProperty('display', 'none', 'important');
                    
                    // Now fade in login form
                    $loginForm.fadeIn(fadeDuration, function() {
                        // Ensure it stays visible
                        $loginForm.css({
                            'opacity': '1',
                            'visibility': 'visible'
                        });
                        $loginForm[0].style.setProperty('display', 'block', 'important');
                        
                        // Scroll to login form if needed
                        $('html, body').animate({
                            scrollTop: $loginForm.offset().top - 100
                        }, fadeDuration);
                    });
                });
                
                if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                    console.log('EFPP Form Switch: Showing login form', {
                        loginFormFound: $loginForm.length > 0,
                        loginFormId: loginFormId,
                        loginFormDisplay: $loginForm[0].style.display
                    });
                }
            }
        } else {
            if (window.location.search.indexOf('efpp_debug=1') !== -1) {
                console.warn('EFPP Form Switch: Could not find forms', {
                    loginFormId: loginFormId,
                    resetFormId: resetFormId,
                    loginFormFound: $loginForm.length > 0,
                    resetFormFound: $resetForm.length > 0
                });
            }
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        setTimeout(initFormSwitching, 100);
    });

    // Initialize when Elementor frontend is ready
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        elementorFrontend.hooks.addAction('frontend/element_ready/form.default', function($scope) {
            setTimeout(initFormSwitching, 100);
        });
    }

    // Run when Elementor popup is shown
    $(window).on('elementor/popup/show', function() {
        setTimeout(initFormSwitching, 200);
    });

    // Also run after Elementor preview refresh
    $(window).on('elementor/frontend/init', function() {
        setTimeout(initFormSwitching, 300);
    });

    // Handle link clicks
    $(document).on('click', '.efpp-switch-to-reset-link, .efpp-switch-to-login-link', function(e) {
        e.preventDefault();
        var $clickedLink = $(this);
        
        if (window.location.search.indexOf('efpp_debug=1') !== -1) {
            console.log('EFPP Form Switch: Link clicked', {
                linkClass: $clickedLink.attr('class'),
                loginFormId: $clickedLink.data('login-form-id'),
                resetFormId: $clickedLink.data('reset-form-id')
            });
        }
        
        switchForms($clickedLink);
    });

})(jQuery);
