<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;

if (!defined('ABSPATH')) exit;

class EFPP_Form_Action_Logout extends Action_Base {

    public function get_name() {
        return 'efpp_logout';
    }

    public function get_label() {
        return 'EFPP – Logout User';
    }

    public function register_settings_section($widget) {
        $widget->start_controls_section(
            'section_efpp_logout',
            [
                'label' => 'EFPP – Logout User',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'efpp_logout_message',
            [
                'label' => 'Success Message',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Zostałeś pomyślnie wylogowany.',
                'description' => 'Wiadomość wyświetlana po wylogowaniu',
            ]
        );

        $widget->add_control(
            'efpp_logout_redirect',
            [
                'label' => 'Redirect to URL',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/',
                'description' => 'URL do przekierowania po wylogowaniu. Zostaw puste, aby pozostać na tej samej stronie.',
            ]
        );

        $widget->end_controls_section();
    }

    public function run($record, $ajax_handler) {
        // Sprawdź czy użytkownik jest zalogowany
        if (!is_user_logged_in()) {
            $ajax_handler->add_error_message('Nie jesteś zalogowany.');
            return;
        }

        // Zapisz ID użytkownika przed wylogowaniem (jeśli potrzebne)
        $user_id = get_current_user_id();

        // Wyloguj użytkownika
        wp_logout();

        // Pobierz ustawienia
        $settings = $record->get('form_settings');
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
    }

    public function on_export($element) {
        return $element;
    }
}

