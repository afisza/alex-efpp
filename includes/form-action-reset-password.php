<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;

if (!defined('ABSPATH')) exit;

class EFPP_Form_Action_Reset_Password extends Action_Base {

    public function get_name() {
        return 'efpp_reset_password';
    }

    public function get_label() {
        return 'EFPP – Reset Password';
    }

    public function register_settings_section($widget) {
        $widget->start_controls_section(
            'section_efpp_reset_password',
            [
                'label' => 'EFPP – Reset Password',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'efpp_reset_password_email',
            [
                'label' => 'Email (field ID)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. email',
                'description' => 'ID pola formularza zawierającego email użytkownika',
            ]
        );

        $widget->add_control(
            'efpp_reset_password_message',
            [
                'label' => 'Success Message',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Jeśli istnieje konto z podanym adresem email, otrzymasz wiadomość z instrukcją resetowania hasła.',
                'description' => 'Wiadomość wyświetlana po wysłaniu żądania resetowania hasła',
            ]
        );

        $widget->add_control(
            'efpp_reset_password_redirect',
            [
                'label' => 'Redirect to URL',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/login/',
                'description' => 'URL do przekierowania po wysłaniu żądania. Zostaw puste, aby pozostać na tej samej stronie.',
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

        $email_field_raw = $settings['efpp_reset_password_email'] ?? '';
        $email_field = $this->extract_field_id($email_field_raw, $available_fields);

        if (empty($email_field_raw)) {
            $ajax_handler->add_error_message('Pole email nie jest skonfigurowane w ustawieniach formularza.');
            return;
        }

        // Pobierz wartość emaila
        $email = '';
        if (isset($fields[$email_field])) {
            $email_data = $fields[$email_field];
            $email = trim($email_data['raw_value'] ?? ($email_data['value'] ?? ''));
        }

        if (empty($email)) {
            $ajax_handler->add_error_message('Adres email jest wymagany.');
            return;
        }

        // Waliduj email
        if (!is_email($email)) {
            $ajax_handler->add_error_message('Nieprawidłowy adres email.');
            return;
        }

        // Sprawdź czy użytkownik istnieje
        $user = get_user_by('email', sanitize_email($email));

        // Zawsze pokazuj tę samą wiadomość (dla bezpieczeństwa - nie zdradzamy czy email istnieje)
        $success_message = $settings['efpp_reset_password_message'] ?? 
            'Jeśli istnieje konto z podanym adresem email, otrzymasz wiadomość z instrukcją resetowania hasła.';

        if ($user) {
            // Wygeneruj klucz resetowania
            $key = get_password_reset_key($user);

            if (!is_wp_error($key)) {
                // Wyślij email z linkiem resetującym
                $message = __('Ktoś poprosił o reset hasła dla następującego konta:') . "\r\n\r\n";
                $message .= network_home_url('/') . "\r\n\r\n";
                $message .= sprintf(__('Nazwa użytkownika: %s'), $user->user_login) . "\r\n\r\n";
                $message .= __('Jeśli to pomyłka, zignoruj tę wiadomość, a hasło pozostanie bez zmian.') . "\r\n\r\n";
                $message .= __('Aby zresetować hasło, przejdź do następującego adresu:') . "\r\n\r\n";
                $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . "\r\n";

                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                $title = sprintf(__('[%s] Reset hasła'), $blogname);

                $headers = array('Content-Type: text/html; charset=UTF-8');
                
                $sent = wp_mail($user->user_email, $title, nl2br($message), $headers);

                if (!$sent) {
                    // Jeśli email nie został wysłany, loguj błąd (ale nie pokazuj użytkownikowi)
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('EFPP Reset Password: Nie udało się wysłać emaila do: ' . $user->user_email);
                    }
                }
            }
        }

        // Zawsze pokazuj sukces (dla bezpieczeństwa)
        $ajax_handler->add_success_message($success_message);

        $redirect_url = $settings['efpp_reset_password_redirect'] ?? '';
        if (!empty($redirect_url) && filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            $ajax_handler->add_response_data('redirect_url', esc_url_raw($redirect_url));
        }
    }

    public function on_export($element) {
        return $element;
    }
}

