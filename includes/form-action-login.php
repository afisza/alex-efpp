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
                'label' => __('Show "Remember me" checkbox', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'alex-efpp'),
                'label_off' => __('No', 'alex-efpp'),
                'default' => 'yes',
                'description' => __('Pokazuje checkbox "Zapamiętaj mnie" przed przyciskiem submit. Użytkownik może zaznaczyć, czy chce być zapamiętany.', 'alex-efpp'),
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

        $widget->add_control(
            'efpp_login_show_reset_link',
            [
                'label' => __('Show Reset Password link', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'alex-efpp'),
                'label_off' => __('No', 'alex-efpp'),
                'default' => 'no',
                'separator' => 'before',
                'description' => __('Pokazuje link do formularza resetowania hasła. Po kliknięciu ukryje ten formularz i pokaże formularz resetowania.', 'alex-efpp'),
            ]
        );

        $widget->add_control(
            'efpp_login_form_id',
            [
                'label' => __('Login Form ID', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. login-form-123',
                'description' => __('ID formularza logowania (używane do ukrywania). Wpisz ID z ustawień formularza Elementor (Form ID) lub nazwę formularza (Form Name).', 'alex-efpp'),
                'condition' => [
                    'efpp_login_show_reset_link' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'efpp_reset_password_form_id',
            [
                'label' => __('Reset Password Form ID', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. reset-form-456',
                'description' => __('ID formularza resetowania hasła (używane do pokazania). Wpisz ID z ustawień formularza Elementor (Form ID) lub nazwę formularza (Form Name).', 'alex-efpp'),
                'condition' => [
                    'efpp_login_show_reset_link' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'efpp_reset_password_link_text',
            [
                'label' => __('Reset Password Link Text', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Zapomniałeś hasła?', 'alex-efpp'),
                'placeholder' => __('Zapomniałeś hasła?', 'alex-efpp'),
                'condition' => [
                    'efpp_login_show_reset_link' => 'yes',
                ],
            ]
        );

        $widget->end_controls_section();

        // Style Section - Remember Me Checkbox
        $widget->start_controls_section(
            'section_efpp_login_remember_style',
            [
                'label' => __('Remember Me Checkbox', 'alex-efpp'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'efpp_login_remember' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'efpp_remember_checkbox_color',
            [
                'label' => __('Checkbox Color', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-remember-me-checkbox' => 'accent-color: {{VALUE}};',
                ],
            ]
        );

        $widget->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_remember_text_typography',
                'label' => __('Text Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-remember-me-checkbox + span',
            ]
        );

        $widget->add_control(
            'efpp_remember_text_color',
            [
                'label' => __('Text Color', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-remember-me-checkbox + span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->add_responsive_control(
            'efpp_remember_spacing',
            [
                'label' => __('Spacing', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group-efpp-remember-me' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $widget->add_responsive_control(
            'efpp_remember_alignment',
            [
                'label' => __('Alignment', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'alex-efpp'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'alex-efpp'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'alex-efpp'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group-efpp-remember-me' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_section();

        // Style Section - Reset Password Link
        $widget->start_controls_section(
            'section_efpp_login_reset_link_style',
            [
                'label' => __('Reset Password Link', 'alex-efpp'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'efpp_login_show_reset_link' => 'yes',
                ],
            ]
        );

        $widget->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_reset_link_typography',
                'label' => __('Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-switch-to-reset-link',
            ]
        );

        $widget->start_controls_tabs('efpp_reset_link_tabs');

        $widget->start_controls_tab(
            'efpp_reset_link_normal',
            [
                'label' => __('Normal', 'alex-efpp'),
            ]
        );

        $widget->add_control(
            'efpp_reset_link_color',
            [
                'label' => __('Color', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-switch-to-reset-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_tab();

        $widget->start_controls_tab(
            'efpp_reset_link_hover',
            [
                'label' => __('Hover', 'alex-efpp'),
            ]
        );

        $widget->add_control(
            'efpp_reset_link_hover_color',
            [
                'label' => __('Color', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-switch-to-reset-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_tab();

        $widget->end_controls_tabs();

        $widget->add_responsive_control(
            'efpp_reset_link_spacing',
            [
                'label' => __('Spacing', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-switch-to-reset-link' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $widget->add_responsive_control(
            'efpp_reset_link_alignment',
            [
                'label' => __('Alignment', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'alex-efpp'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'alex-efpp'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'alex-efpp'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-switch-to-reset-link' => 'text-align: {{VALUE}};',
                ],
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

        // Sprawdź czy użytkownik zaznaczył "Zapamiętaj mnie"
        $remember = false;
        
        // Najpierw sprawdź wartość z checkboxa w formularzu
        if (isset($fields['efpp_remember_me'])) {
            $remember_field = $fields['efpp_remember_me'];
            $remember_value = $remember_field['value'] ?? $remember_field['raw_value'] ?? '';
            $remember = !empty($remember_value) && ($remember_value === '1' || $remember_value === 1 || $remember_value === true);
        }
        
        // Jeśli checkbox nie istnieje, użyj wartości z ustawień (backward compatibility)
        if (!isset($fields['efpp_remember_me'])) {
            $remember = !empty($settings['efpp_login_remember']);
        }
        
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



