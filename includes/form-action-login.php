<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;

if (!defined('ABSPATH')) exit;

class EFPP_Form_Action_Login extends Action_Base {

    public function get_name() {
        return 'efpp_login';
    }

    public function get_label() {
        return 'EFPP – Login User';
    }

    public function register_settings_section($widget) {
        $widget->start_controls_section(
            'section_efpp_login',
            [
                'label' => 'EFPP – Login User',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'efpp_login_identifier',
            [
                'label' => 'Login/Email (field ID)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. email lub login',
                'description' => 'ID pola formularza zawierającego login lub email użytkownika',
            ]
        );

        $widget->add_control(
            'efpp_login_password',
            [
                'label' => 'Password (field ID)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. password',
                'description' => 'ID pola formularza zawierającego hasło',
            ]
        );

        $widget->add_control(
            'efpp_login_remember',
            [
                'label' => 'Remember me',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'alex-efpp'),
                'label_off' => __('No', 'alex-efpp'),
                'default' => 'yes',
            ]
        );

        $widget->add_control(
            'efpp_login_redirect',
            [
                'label' => 'Redirect to URL',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/dashboard/',
                'description' => 'URL do przekierowania po zalogowaniu. Zostaw puste, aby pozostać na tej samej stronie.',
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * Wyciąga rzeczywiste ID pola z różnych formatów
     */
    private function extract_field_id($field_setting, $available_fields = []) {
        if (empty($field_setting)) {
            return '';
        }

        if (preg_match('/\[field\s+id=["\']([^"\']+)["\']\]/i', $field_setting, $matches)) {
            $extracted_id = $matches[1];
            if (in_array($extracted_id, $available_fields)) {
                return $extracted_id;
            }
            foreach ($available_fields as $available_field) {
                if (strpos($available_field, $extracted_id) !== false || strpos($extracted_id, $available_field) !== false) {
                    return $available_field;
                }
            }
            return $extracted_id;
        }

        if (in_array($field_setting, $available_fields)) {
            return $field_setting;
        }

        foreach ($available_fields as $available_field) {
            if (strpos($available_field, $field_setting) !== false || strpos($field_setting, $available_field) !== false) {
                return $available_field;
            }
        }

        return $field_setting;
    }

    public function run($record, $ajax_handler) {
        $settings = $record->get('form_settings');
        $fields = $record->get('fields');

        $available_fields = array_keys($fields);

        $identifier_field_raw = $settings['efpp_login_identifier'] ?? '';
        $password_field_raw = $settings['efpp_login_password'] ?? '';

        $identifier_field = $this->extract_field_id($identifier_field_raw, $available_fields);
        $password_field = $this->extract_field_id($password_field_raw, $available_fields);

        if (empty($identifier_field_raw)) {
            $ajax_handler->add_error_message('Pole login/email nie jest skonfigurowane w ustawieniach formularza.');
            return;
        }

        if (empty($password_field_raw)) {
            $ajax_handler->add_error_message('Pole hasła nie jest skonfigurowane w ustawieniach formularza.');
            return;
        }

        // Pobierz wartości z pól
        $identifier = '';
        $password = '';

        if (isset($fields[$identifier_field])) {
            $identifier_data = $fields[$identifier_field];
            $identifier = trim($identifier_data['raw_value'] ?? ($identifier_data['value'] ?? ''));
        }

        if (isset($fields[$password_field])) {
            $password_data = $fields[$password_field];
            $password = $password_data['raw_value'] ?? ($password_data['value'] ?? '');
        }

        if (empty($identifier)) {
            $ajax_handler->add_error_message('Login/Email jest wymagany.');
            return;
        }

        if (empty($password)) {
            $ajax_handler->add_error_message('Hasło jest wymagane.');
            return;
        }

        // Sprawdź czy użytkownik jest już zalogowany
        if (is_user_logged_in()) {
            $ajax_handler->add_error_message('Jesteś już zalogowany.');
            return;
        }

        // Znajdź użytkownika (może być login lub email)
        $user = null;
        if (is_email($identifier)) {
            $user = get_user_by('email', $identifier);
        }
        
        if (!$user) {
            $user = get_user_by('login', $identifier);
        }

        if (!$user) {
            $ajax_handler->add_error_message('Nieprawidłowy login lub email.');
            return;
        }

        // Sprawdź hasło
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            $ajax_handler->add_error_message('Nieprawidłowe hasło.');
            return;
        }

        // Zaloguj użytkownika
        $remember = !empty($settings['efpp_login_remember']);
        
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        
        // Aktualizuj czas ostatniego logowania
        update_user_meta($user->ID, 'last_login', current_time('mysql'));

        $redirect_url = $settings['efpp_login_redirect'] ?? '';
        
        $ajax_handler->add_success_message('Zostałeś pomyślnie zalogowany.');

        if (!empty($redirect_url) && filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            $ajax_handler->add_response_data('redirect_url', esc_url_raw($redirect_url));
        }
    }

    public function on_export($element) {
        return $element;
    }
}



