<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;

if (!defined('ABSPATH')) exit;

class EFPP_Form_Action_Register_User extends Action_Base {

    public function get_name() {
        return 'efpp_register_user';
    }

    public function get_label() {
        return 'EFPP – Register/Update User';
    }

    public function register_settings_section($widget) {
        $widget->start_controls_section(
            'section_efpp_register_user',
            [
                'label' => 'EFPP – Register / Update user',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        // === Dynamiczne pola użytkownika z JetEngine + baza danych ===
        global $wpdb;

        $jetengine_fields = [];
        $wp_fields = [
            'display_name' => 'display_name',
            'first_name'   => 'first_name',
            'last_name'    => 'last_name',
            'nickname'     => 'nickname',
            'description'  => 'description',
        ];
        $woocommerce_fields = [];

        // === JetEngine fields
        if ( class_exists('\Jet_Engine') ) {
            $jet_fields = jet_engine()->meta_boxes->get_fields_for_context('user');

            foreach ( $jet_fields as $group ) {
                foreach ( $group as $field ) {
                    if ( ! empty( $field['name'] ) ) {
                        $jetengine_fields[ $field['name'] ] = $field['name'];
                    }
                }
            }
        }

        // === WooCommerce user meta fields (jeśli aktywne)
        if ( class_exists('WooCommerce') ) {
            $woocommerce_fields = [
                'billing_first_name' => 'billing_first_name',
                'billing_last_name'  => 'billing_last_name',
                'billing_email'      => 'billing_email',
                'billing_phone'      => 'billing_phone',
                'shipping_address_1' => 'shipping_address_1',
                'shipping_city'      => 'shipping_city',
            ];
        }

        // === Zbierz wszystkie dynamiczne meta_key z bazy
        $db_keys = $wpdb->get_col("
            SELECT DISTINCT meta_key 
            FROM $wpdb->usermeta 
            WHERE meta_key NOT LIKE '\_%'
            ORDER BY meta_key ASC
        ");

        $dynamic_fields = [];
        foreach ( $db_keys as $key ) {
            if (
                ! isset($jetengine_fields[$key]) &&
                ! isset($wp_fields[$key]) &&
                ! isset($woocommerce_fields[$key])
            ) {
                $dynamic_fields[$key] = $key;
            }
        }

        // === Złóż opcje jako zgrupowany SELECT
        $meta_key_options = [];

        // JetEngine
        foreach ( $jetengine_fields as $key => $label ) {
            $meta_key_options[ $key ] = 'JetEngine: ' . $label;
        }

        // WP
        foreach ( $wp_fields as $key => $label ) {
            $meta_key_options[ $key ] = 'WordPress: ' . $label;
        }

        // WooCommerce
        foreach ( $woocommerce_fields as $key => $label ) {
            $meta_key_options[ $key ] = 'Woo: ' . $label;
        }

        // Inne
        foreach ( $dynamic_fields as $key => $label ) {
            $meta_key_options[ $key ] = 'Inne: ' . $label;
        }

        $widget->add_control(
            'efpp_register_user_mode',
            [
                'label' => 'Mode',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'register',
                'options' => [
                    'register' => 'Register user',
                    'update'   => 'Update user',
                ],
            ]
        );

        $widget->add_control(
            'efpp_register_user_login',
            [
                'label' => 'Login (field ID)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. login',
                'condition' => [
                    'efpp_register_user_mode' => 'register',
                ],
            ]
        );

        $widget->add_control(
            'efpp_register_user_email',
            [
                'label' => 'Email (field ID)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. email',
            ]
        );

        $widget->add_control(
            'efpp_register_user_password',
            [
                'label' => 'Password (field ID)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'np. password',
            ]
        );

        $widget->add_control(
            'efpp_register_user_role',
            [
                'label' => 'User role',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'subscriber',
                'options' => [
                    'subscriber' => 'Subscriber',
                    'contributor' => 'Contributor',
                    'author' => 'Author',
                    'editor' => 'Editor',
                    'customer' => 'Customer',
                ],
                'condition' => [
                    'efpp_register_user_mode' => 'register',
                ],
            ]
        );

        $widget->add_control(
            'efpp_register_user_auto_login',
            [
                'label' => 'Login automatically after registration',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'condition' => [
                    'efpp_register_user_mode' => 'register',
                ],
            ]
        );

        $widget->add_control(
            'efpp_register_user_redirect',
            [
                'label' => 'Redirect to URL',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/dziekujemy/',
                'description' => 'Podaj pelny adres URL do przekierowania.',
            ]
        );

        $widget->add_control(
            'efpp_register_user_lookup_by',
            [
                'label' => 'Identify the user by',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'email',
                'options' => [
                    'email' => 'Email',
                    'login' => 'Login',
                    'ID'    => 'ID',
                ],
                'condition' => [
                    'efpp_register_user_mode' => 'update',
                ],
            ]
        );

        $widget->add_control(
            'efpp_register_user_meta',
            [
                'label' => 'Mapowanie pól użytkownika',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'meta_key',
                        'label' => 'Pole użytkownika',
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'options' => $meta_key_options,
                        'default' => 'display_name',
                    ],
                    [
                        'name' => 'form_field_id',
                        'label' => 'ID pola formularza',
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'field_name',
                    ],
                ],
                'title_field' => '{{ meta_key }}',
            ]
        );

        $widget->end_controls_section();
    }


    public function run($record, $ajax_handler) {
        $settings = $record->get('form_settings');
        $fields = $record->get('fields');

        $login_field    = $settings['efpp_register_user_login'] ?? '';
        $email_field    = $settings['efpp_register_user_email'] ?? '';
        $password_field = $settings['efpp_register_user_password'] ?? '';
        $role           = in_array($settings['efpp_register_user_role'] ?? '', ['subscriber', 'contributor', 'author', 'editor', 'customer']) ? $settings['efpp_register_user_role'] : 'subscriber';
        $mode           = $settings['efpp_register_user_mode'] ?? 'register';
        $auto_login     = !empty($settings['efpp_register_user_auto_login']);
        $redirect_url   = $settings['efpp_register_user_redirect'] ?? '';

        $user_login = isset($fields[$login_field]['value']) ? sanitize_user($fields[$login_field]['value']) : '';
        $user_email = isset($fields[$email_field]['value']) ? sanitize_email($fields[$email_field]['value']) : '';
        $user_pass  = isset($fields[$password_field]['value']) && !empty($fields[$password_field]['value']) ? sanitize_text_field($fields[$password_field]['value']) : wp_generate_password();

        if (!is_email($user_email)) {
            $ajax_handler->add_error_message('Nieprawidłowy adres e-mail.');
            return;
        }

        if ($mode === 'register') {
            if (empty($user_login) || strlen($user_login) < 3) {
                $ajax_handler->add_error_message('Login musi mieć co najmniej 3 znaki.');
                return;
            }

            if (username_exists($user_login) || email_exists($user_email)) {
                $ajax_handler->add_error_message('Użytkownik już istnieje.');
                return;
            }

            $user_id = wp_insert_user([
                'user_login' => $user_login,
                'user_email' => $user_email,
                'user_pass'  => $user_pass,
                'role'       => $role,
            ]);

            if (is_wp_error($user_id)) {
                $ajax_handler->add_error_message('Błąd: ' . $user_id->get_error_message());
                return;
            }

            if ($auto_login) {
                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
            }

            $ajax_handler->add_success_message('Użytkownik został zarejestrowany.');
        }

        if ($mode === 'update') {
            $lookup_by = $settings['efpp_register_user_lookup_by'] ?? 'email';
            $user_identifier = '';

            switch ($lookup_by) {
                case 'login':
                    $user_identifier = $fields[$login_field]['value'] ?? '';
                    break;
                case 'ID':
                    $user_identifier = $fields[$login_field]['value'] ?? '';
                    break;
                case 'email':
                default:
                    $user_identifier = $fields[$email_field]['value'] ?? '';
                    break;
            }

            if (empty($user_identifier)) {
                $ajax_handler->add_error_message('Brakuje wartości identyfikatora użytkownika.');
                return;
            }

            $existing_user = ($lookup_by === 'ID') ? get_user_by('ID', intval($user_identifier)) : get_user_by($lookup_by, $user_identifier);

            if (!$existing_user) {
                $ajax_handler->add_error_message("Nie znaleziono użytkownika po {$lookup_by}.");
                return;
            }

            $update_data = [ 'ID' => $existing_user->ID ];

            if (!empty($user_pass)) {
                $update_data['user_pass'] = $user_pass;
            }

            if (!empty($user_email) && $user_email !== $existing_user->user_email) {
                if (!is_email($user_email)) {
                    $ajax_handler->add_error_message('Nieprawidłowy adres e-mail.');
                    return;
                }
                $update_data['user_email'] = $user_email;
            }

            $updated = wp_update_user($update_data);

            if (is_wp_error($updated)) {
                $ajax_handler->add_error_message('Błąd podczas aktualizacji: ' . $updated->get_error_message());
                return;
            }

            $ajax_handler->add_success_message('Użytkownik został zaktualizowany.');
        }

        // Mapowanie pól meta / display_name
        $meta_map = $settings['efpp_register_user_meta'] ?? [];
        $target_user_id = $user_id ?? ($existing_user->ID ?? 0);

        foreach ($meta_map as $map_item) {
            $meta_key  = $map_item['meta_key'] ?? '';
            $field_id  = $map_item['form_field_id'] ?? '';
            $field_val = $fields[$field_id]['value'] ?? '';

            if (!$meta_key || !$field_id || $field_val === '') continue;

            $sanitized = sanitize_text_field($field_val);

            if (in_array($meta_key, ['display_name'])) {
                wp_update_user([
                    'ID' => $target_user_id,
                    $meta_key => $sanitized,
                ]);
            } else {
                update_user_meta($target_user_id, $meta_key, $sanitized);
            }
        }

        if (!empty($redirect_url) && filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            $ajax_handler->add_response_data('redirect_url', esc_url_raw($redirect_url));
        }
    }

    public function on_export($element) {
        return $element;
    }
}
