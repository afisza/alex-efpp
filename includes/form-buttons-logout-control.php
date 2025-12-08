<?php

if (!defined('ABSPATH')) exit;

use Elementor\Controls_Manager;

// Dodaj kontrolkę do sekcji Buttons
add_action('elementor/element/before_section_end', function ($element, $section_id, $args) {
    if ('form' !== $element->get_name() || 'section_buttons' !== $section_id) {
        return;
    }

    $element->add_control(
        'efpp_submit_as_logout',
        [
            'label' => 'Use as Logout button',
            'type' => Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'alex-efpp'),
            'label_off' => __('No', 'alex-efpp'),
            'return_value' => 'yes',
            'default' => '',
            'description' => 'Submit button will perform logout functionality',
            'separator' => 'before',
        ]
    );
}, 10, 3);

// Hook do automatycznego wykonania logout, gdy switcher jest włączony
// Wykonujemy logout przed innymi akcjami i kończymy wykonywanie formularza
add_action('elementor_pro/forms/pre_send', function ($record, $ajax_handler) {
    $settings = $record->get('form_settings');
    
    // Sprawdź czy switcher "Use as Logout button" jest włączony
    if (!empty($settings['efpp_submit_as_logout']) && $settings['efpp_submit_as_logout'] === 'yes') {
        // Sprawdź czy użytkownik jest zalogowany
        if (!is_user_logged_in()) {
            $ajax_handler->add_error_message('Nie jesteś zalogowany.');
            $ajax_handler->is_success = false;
            return;
        }
        
        // Wykonaj logout używając tej samej logiki co akcja logout
        wp_logout();
        
        // Dodaj komunikat sukcesu
        $success_message = $settings['efpp_logout_message'] ?? 'Zostałeś pomyślnie wylogowany.';
        $ajax_handler->add_success_message($success_message);
        
        // Przekierowanie
        $redirect_url = $settings['efpp_logout_redirect'] ?? '';
        if (!empty($redirect_url) && filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            $ajax_handler->add_response_data('redirect_url', esc_url_raw($redirect_url));
        } else {
            // Domyślnie przekieruj na stronę główną
            $ajax_handler->add_response_data('redirect_url', home_url('/'));
        }
        
        // Oznacz jako sukces - Elementor automatycznie obsłuży odpowiedź i przekierowanie
        $ajax_handler->is_success = true;
    }
}, 5, 2);

