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
                'title_field' => '{{ meta_key }}: {{ form_field_id }}',
            ]
        );

        $widget->end_controls_section();
    }


    /**
     * Wyciąga rzeczywiste ID pola z różnych formatów
     * Obsługuje: [field id="email"], email, custom_id itp.
     */
    private function extract_field_id($field_setting, $available_fields = []) {
        if (empty($field_setting)) {
            return '';
        }

        // Jeśli jest w formacie [field id="..."]
        if (preg_match('/\[field\s+id=["\']([^"\']+)["\']\]/i', $field_setting, $matches)) {
            $extracted_id = $matches[1];
            // Sprawdź czy takie pole istnieje
            if (in_array($extracted_id, $available_fields)) {
                return $extracted_id;
            }
            // Jeśli nie, spróbuj znaleźć podobne (np. email -> email_reg)
            foreach ($available_fields as $available_field) {
                if (strpos($available_field, $extracted_id) !== false || strpos($extracted_id, $available_field) !== false) {
                    return $available_field;
                }
            }
            return $extracted_id; // Zwróć wyciągnięte ID nawet jeśli nie znaleziono dopasowania
        }

        // Jeśli jest już czystym ID
        if (in_array($field_setting, $available_fields)) {
            return $field_setting;
        }

        // Spróbuj znaleźć podobne pole
        foreach ($available_fields as $available_field) {
            if (strpos($available_field, $field_setting) !== false || strpos($field_setting, $available_field) !== false) {
                return $available_field;
            }
        }

        // Zwróć oryginalną wartość jeśli nic nie znaleziono
        return $field_setting;
    }

    public function run($record, $ajax_handler) {
        $settings = $record->get('form_settings');
        $fields = $record->get('fields');

        // Pobierz dostępne pola do dopasowania
        $available_fields = array_keys($fields);

        // Wyciągnij rzeczywiste ID pól z ustawień
        $login_field_raw    = $settings['efpp_register_user_login'] ?? '';
        $email_field_raw    = $settings['efpp_register_user_email'] ?? '';
        $password_field_raw = $settings['efpp_register_user_password'] ?? '';

        // Wyciągnij rzeczywiste ID pól
        $login_field    = $this->extract_field_id($login_field_raw, $available_fields);
        $email_field    = $this->extract_field_id($email_field_raw, $available_fields);
        $password_field = $this->extract_field_id($password_field_raw, $available_fields);

        $role           = in_array($settings['efpp_register_user_role'] ?? '', ['subscriber', 'contributor', 'author', 'editor', 'customer']) ? $settings['efpp_register_user_role'] : 'subscriber';
        $mode           = $settings['efpp_register_user_mode'] ?? 'register';
        $auto_login     = !empty($settings['efpp_register_user_auto_login']);
        $redirect_url   = $settings['efpp_register_user_redirect'] ?? '';

        // Sprawdź czy pole e-mail jest skonfigurowane
        if (empty($email_field_raw)) {
            $ajax_handler->add_error_message('Pole e-mail nie jest skonfigurowane w ustawieniach formularza.');
            return;
        }

        // Pobierz surową wartość e-maila przed sanitizacją
        // Elementor może przekazywać wartości w 'raw_value' lub 'value'
        $raw_email = '';
        
        if (isset($fields[$email_field])) {
            $field_data = $fields[$email_field];
            
            // Sprawdź raw_value (pierwsza opcja, jak w class-form-action-post.php)
            if (isset($field_data['raw_value']) && !empty($field_data['raw_value'])) {
                $raw_email = trim($field_data['raw_value']);
            }
            // Sprawdź value (druga opcja)
            elseif (isset($field_data['value']) && !empty($field_data['value'])) {
                $raw_email = trim($field_data['value']);
            }
            // Może być przekazane bezpośrednio jako string
            elseif (is_string($field_data)) {
                $raw_email = trim($field_data);
            }
        }
        
        if (empty($raw_email)) {
            // Debug: pokaż dostępne pola i strukturę (zawsze, żeby pomóc w diagnozie)
            $debug_info = sprintf(
                'Dostępne pola: %s | Wpisane w ustawieniach: %s | Wyciągnięte ID: %s',
                implode(', ', $available_fields),
                $email_field_raw,
                $email_field
            );
            
            // Jeśli WP_DEBUG włączony, loguj szczegóły
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('EFPP Debug - Available fields: ' . print_r($available_fields, true));
                error_log('EFPP Debug - Email field raw: ' . $email_field_raw);
                error_log('EFPP Debug - Email field extracted: ' . $email_field);
                error_log('EFPP Debug - Fields structure: ' . print_r($fields, true));
            }
            
            $ajax_handler->add_error_message(
                'Adres e-mail jest wymagany. ' . 
                'Sprawdź czy pole e-mail jest poprawnie skonfigurowane. ' .
                'Debug: ' . $debug_info
            );
            return;
        }

        // Waliduj przed sanitizacją
        if (!is_email($raw_email)) {
            $ajax_handler->add_error_message('Nieprawidłowy adres e-mail: ' . esc_html($raw_email));
            return;
        }

        // Teraz sanitizuj (po walidacji)
        // Pobierz login - sprawdź raw_value i value
        $user_login = '';
        if (!empty($login_field) && isset($fields[$login_field])) {
            $login_data = $fields[$login_field];
            $user_login = sanitize_user(
                $login_data['raw_value'] ?? ($login_data['value'] ?? '')
            );
        }
        
        $user_email = sanitize_email($raw_email);
        
        // Pobierz hasło - sprawdź raw_value i value
        $user_pass = wp_generate_password(); // domyślnie wygeneruj
        if (!empty($password_field) && isset($fields[$password_field])) {
            $pass_data = $fields[$password_field];
            $pass_value = $pass_data['raw_value'] ?? ($pass_data['value'] ?? '');
            if (!empty($pass_value)) {
                $user_pass = sanitize_text_field($pass_value);
            }
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
                    $login_data = $fields[$login_field] ?? [];
                    $user_identifier = $login_data['raw_value'] ?? ($login_data['value'] ?? '');
                    break;
                case 'ID':
                    $id_data = $fields[$login_field] ?? [];
                    $user_identifier = $id_data['raw_value'] ?? ($id_data['value'] ?? '');
                    break;
                case 'email':
                default:
                    $email_data = $fields[$email_field] ?? [];
                    $user_identifier = $email_data['raw_value'] ?? ($email_data['value'] ?? '');
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

            // W trybie update, sprawdź czy e-mail się zmienił
            $email_update_data = $fields[$email_field] ?? [];
            $raw_email_update = '';
            if (isset($email_update_data['raw_value']) && !empty($email_update_data['raw_value'])) {
                $raw_email_update = trim($email_update_data['raw_value']);
            } elseif (isset($email_update_data['value']) && !empty($email_update_data['value'])) {
                $raw_email_update = trim($email_update_data['value']);
            }
            
            if (!empty($raw_email_update) && $raw_email_update !== $existing_user->user_email) {
                if (!is_email($raw_email_update)) {
                    $ajax_handler->add_error_message('Nieprawidłowy adres e-mail: ' . esc_html($raw_email_update));
                    return;
                }
                $update_data['user_email'] = sanitize_email($raw_email_update);
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
            $field_id_raw = $map_item['form_field_id'] ?? '';
            
            // Wyciągnij rzeczywiste ID pola
            $field_id = $this->extract_field_id($field_id_raw, $available_fields);
            
            // Pobierz wartość - sprawdź raw_value i value
            $field_val = '';
            if (isset($fields[$field_id])) {
                $field_data = $fields[$field_id];
                $field_val = $field_data['raw_value'] ?? ($field_data['value'] ?? '');
            }

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
