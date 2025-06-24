<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;
use Elementor\Core\DynamicTags\Manager as TagsManager;
use Elementor\Core\DynamicTags\Manager as DynamicTagsManager;

//$manager = \Elementor\Plugin::$instance->dynamic_tags;

if (!defined('ABSPATH')) exit;

class Alex_EFPP_Form_Action_Post extends Action_Base {

    public function get_name() {
        return 'alex_efpp';
    }

    public function get_label() {
        return 'EFPP – Create/Update Post';
    }

    public function register_settings_section($widget) {
        $post_types = get_post_types(['public' => true], 'names');
        if (empty($post_types)) {
            $post_types = ['post' => 'Post'];
        }

        $widget->start_controls_section(
            'section_alex_efpp',
            [
                'label' => 'EFPP – Publish Settings',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        if (!function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        $roles = get_editable_roles();
        $role_options = [];

        foreach ($roles as $role_slug => $role_details) {
            $role_options[$role_slug] = translate_user_role($role_details['name']);
        }

        // Dodajemy 'guest' jako specjalną opcję
        $role_options['guest'] = 'Guest (niezalogowany)';

        $widget->add_control(
            'alex_efpp_allowed_role',
            [
                'label' => __('Allowed Role', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $role_options,
                'default' => 'subscriber',
                'label_block' => true,
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'alex_efpp_post_mode',
            [
                'label' => __('Action', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'create',
                'options' => [
                    'create' => __('Create Post', 'alex-efpp'),
                    'update' => __('Update Post', 'alex-efpp'),
                ],
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'alex_efpp_redirect_after_submit',
            [
                'label' => __('Redirect to post after submit', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'alex-efpp'),
                'label_off' => __('No', 'alex-efpp'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_post_mode' => 'update',
                ],
            ]
        );

        $widget->add_control(
        'alex_efpp_redirect_after_submit_create',
        [
            'label' => __('Redirect to new post after submit', 'alex-efpp'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'alex-efpp'),
            'label_off' => __('No', 'alex-efpp'),
            'return_value' => 'yes',
            'default' => 'no',
            'condition' => [
                'submit_actions' => $this->get_name(),
                'alex_efpp_post_mode' => 'create',
            ],
        ]
    );




        $widget->add_control(
            'alex_efpp_post_type',
            [
                'label' => 'Post Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $post_types,
                'default' => 'post',
                
            ]
        );

        // Pole: Product Type – tylko gdy post_type = 'product'
        $widget->add_control(
            'alex_efpp_product_type',
            [
                'label' => __('Product Type', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'simple'   => 'Simple',
                    'grouped'  => 'Grouped',
                    'external' => 'External/Affiliate',
                    'variable' => 'Variable',
                ],
                'default' => 'simple',
                'condition' => [
                    'alex_efpp_post_type' => 'product',
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'alex_efpp_post_status',
            [
                'label' => 'Post Status',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'draft' => 'Draft',
                    'publish' => 'Published',
                    'future' => 'Scheduled',
                    'pending' => 'Pending Review',
                ],
                'default' => 'draft',
            ]
        );

        $widget->add_control(
            'alex_efpp_post_date_field',
            [
                'label' => __('Field ID for Date', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'data',
                'description' => __('Enter the field ID that provides the post date (Y-m-d).', 'alex-efpp'),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_post_status' => 'future',
                ],
            ]
        );

        $widget->add_control(
            'alex_efpp_post_time_field',
            [
                'label' => __('Field ID for Time', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'czas',
                'description' => __('Enter the field ID that provides the post time (HH:mm).', 'alex-efpp'),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_post_status' => 'future',
                ],
            ]
        );

        $widget->add_control(
        'alex_efpp_post_id_field',
        [
            'label' => __('Post ID form field', 'alex-efpp'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'post_id',
            'placeholder' => 'post_id',
            'description' => __('Used only in "Update" mode.', 'alex-efpp'),
            'condition' => [
                'alex_efpp_post_mode' => 'update',
                'submit_actions' => $this->get_name(),
            ],
            'ai' => [ 'active' => false ],
        ]
    );


        $widget->add_control(
            'alex_efpp_price_field',
            [
                'label' => __('Field ID for price', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'price',
                'condition' => [
                    'alex_efpp_post_type' => 'product',
                    'submit_actions' => $this->get_name(),
                ],
                'ai' => [ 'active' => false ],
            ]
        );

        $widget->add_control(
            'alex_efpp_post_title_field',
            [
                'label' => 'Field ID for Post Title',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => '[field id="postname"]',
                'description' => 'Enter the ID of the form field that should be used as the post title.',
                'dynamic' => [ 'active' => true ],
                'ai' => [ 'active' => false ],
            ]
        );

        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $taxonomy_options = [
            'none' => '-- Select taxonomy --', // ← użyj 'none' zamiast pustego stringa
        ];

        foreach ($taxonomies as $taxonomy_slug => $taxonomy_obj) {
            $taxonomy_options[$taxonomy_slug] = $taxonomy_obj->labels->name;
        }

        $widget->add_control(
            'alex_efpp_taxonomy',
            [
                'label' => __('Taxonomy', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $taxonomy_options,
                'default' => 'none',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_post_type!' => 'page',
                ],
                'ai' => [ 'active' => false ],
            ]
        );



        $widget->add_control(
            'alex_efpp_post_category_field',
            [
                'label' => 'Field ID for Taxonomy',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => '[field id="category"]',
                'description' => 'Enter the shortcode of the field that should define the post terms.',
                'dynamic' => [ 'active' => true ],
                'ai' => [ 'active' => false ],
                'condition' => [
                    'alex_efpp_taxonomy!' => 'none', // wyświetl tylko jeśli coś wybrane
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_post_type!' => 'page',
                    'alex_efpp_taxonomy!' => 'none',
                ],
            ]
        );

        $widget->add_control(
            'alex_efpp_featured_image_field',
            [
                'label' => 'Field ID for Featured Image',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'image_url',
                'description' => 'Enter the ID of the form field that contains the image URL. Accepts shortcode or dynamic tag.',
                'dynamic' => ['active' => true],
                'ai' => [ 'active' => false ],
            ]
        );


        $widget->end_controls_section();
    }


    public function run($record, $ajax_handler) {
        $manager = \Elementor\Plugin::$instance->dynamic_tags;
        $settings = $record->get('form_settings');
        $post_mode = $settings['alex_efpp_post_mode'] ?? 'create';
        $fields = $record->get('fields');
        //$allowed_role = $settings['alex_efpp_allowed_role'] ?? 'subscriber';
        $allowed_roles = $settings['alex_efpp_allowed_role'] ?? ['subscriber'];
        if (!is_array($allowed_roles)) {
            $allowed_roles = [$allowed_roles];
        }

        $valid_roles = array_keys(get_editable_roles());
        $valid_roles[] = 'guest';

        foreach ($allowed_roles as $role) {
            if (!in_array($role, $valid_roles)) {
                $ajax_handler->add_error_message('Błędna konfiguracja ról – formularz został zatrzymany.');
                return;
            }
        }

        if (!is_user_logged_in()) {
            if (!in_array('guest', $allowed_roles)) {
                $ajax_handler->add_error_message('Tylko zalogowani użytkownicy mogą publikować treści.');
                return;
            }
        } else {
            $user = wp_get_current_user();
            $user_roles = $user->roles ?? [];

            $access_granted = false;
            foreach ($user_roles as $role) {
                if (in_array($role, $allowed_roles)) {
                    $access_granted = true;
                    break;
                }
            }

            if (!$access_granted) {
                $ajax_handler->add_error_message('Nie masz odpowiednich uprawnień do publikacji.');
                return;
            }
        }

        // Create post...


    // === TITLE ===
    $title_source = $settings['alex_efpp_post_title_field'] ?? '';
    $title_field_id = '';

    if (preg_match('/\[field id="([^"]+)"\]/', $title_source, $match)) {
        $title_field_id = $match[1]; // legacy compatibility
    } else {
        $title_field_id = $title_source;
    }

    $title = 'Untitled';
    if (!empty($title_field_id)) {
        if (!empty($fields[$title_field_id]['value'])) {
            $title = $fields[$title_field_id]['value'];
        } else {
            // fallback to dynamic tag value (e.g. user info, post title, etc.)
            $tag_text = $manager->tag_text($title_field_id);
            if (!empty($tag_text)) {
                $title = $tag_text;
            }
        }
    }


    $content = isset($fields['content']) ? $fields['content']['value'] : '';
    $type    = $settings['alex_efpp_post_type'] ?? 'post';
    $status  = $settings['alex_efpp_post_status'] ?? 'draft';

    // === POST DATE from custom date & time fields ===
        $post_date = current_time('mysql'); // fallback: teraz

        $date_field_id = $settings['alex_efpp_post_date_field'] ?? '';
        $time_field_id = $settings['alex_efpp_post_time_field'] ?? '';

        $date = !empty($date_field_id) ? ($fields[$date_field_id]['value'] ?? '') : '';
        $time = !empty($time_field_id) ? ($fields[$time_field_id]['value'] ?? '') : '';

        if ($date && $time) {
            $datetime = strtotime($date . ' ' . $time);
            if ($datetime) {
                $post_date = date('Y-m-d H:i:s', $datetime);
            }
        }

        // === CREATE or UPDATE ===
    $post_data = [
        'post_type'    => $type,
        'post_status'  => $status,
        'post_title'   => sanitize_text_field($title),
        'post_content' => wp_kses_post($content),
        'post_date'    => $post_date,
        'post_date_gmt'=> get_gmt_from_date($post_date),

    ];

    if (!empty($post_date)) {
        $post_date['post_date'] = $post_date;
        $post_date['post_date_gmt'] = get_gmt_from_date($post_date);
    }

    if ($post_mode === 'update') {
        $post_id_field = $settings['alex_efpp_post_id_field'] ?? 'post_id';
        $update_post_id = $fields[$post_id_field]['value'] ?? null;

        if (!empty($update_post_id) && get_post_status($update_post_id)) {
            $post_data['ID'] = (int) $update_post_id;
            $post_id = wp_update_post($post_data);
        } else {
            $ajax_handler->add_error_message(__('Post ID is missing or invalid.', 'alex-efpp'));
            return;
        }
    } else {
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            $ajax_handler->add_error_message(__('Error saving post.', 'alex-efpp'));
            return;
        }

        // Redirect if enabled
        if (!empty($settings['alex_efpp_redirect_after_submit_create']) && $settings['alex_efpp_redirect_after_submit_create'] === 'yes') {
            $ajax_handler->add_success_message(__('Zapisano. Trwa przekierowanie do nowego wpisu...', 'alex-efpp'));
            $ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
        } elseif (!empty($settings['alex_efpp_reload_after_submit']) && $settings['alex_efpp_reload_after_submit'] === 'yes') {
            $ajax_handler->add_success_message(__('Zapisano. Trwa odświeżanie strony...', 'alex-efpp'));
        }
    }


    // globalne sprawdzenie błędu
    if (is_wp_error($post_id)) {
        $ajax_handler->add_error_message(__('Error saving post.', 'alex-efpp'));
        return;
    }

    // redirect lub reload
    if ($post_mode === 'update') {
        if (!empty($settings['alex_efpp_redirect_after_submit']) && $settings['alex_efpp_redirect_after_submit'] === 'yes') {
            $ajax_handler->add_success_message(__('Zapisano. Trwa przekierowanie...', 'alex-efpp'));
            $ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
        } elseif (!empty($settings['alex_efpp_reload_after_submit']) && $settings['alex_efpp_reload_after_submit'] === 'yes') {
            $ajax_handler->add_success_message(__('Zapisano. Trwa odświeżanie strony...', 'alex-efpp'));
        }
    }

    // Reload if enabled
    if (!empty($settings['alex_efpp_reload_after_submit']) && $settings['alex_efpp_reload_after_submit'] === 'yes') {
        $ajax_handler->add_success_message(__('Zapisano. Trwa odświeżanie strony...', 'alex-efpp'));
        // Ustawiamy aktualny adres strony jako redirect_url
        $ajax_handler->add_response_data( 'redirect_url', get_permalink($post_id) );
    }


    // === TAXONOMY TERMS (SINGLE SELECT from term_id) ===
    $taxonomy = $settings['alex_efpp_taxonomy'] ?? 'category';
    $cat_source = $settings['alex_efpp_post_category_field'] ?? '';
    $cat_field_id = '';

    // Obsługa [field id="..."]
    if (preg_match('/\[field id="([^"]+)"\]/', $cat_source, $match)) {
        $cat_field_id = $match[1];
    } else {
        $cat_field_id = $cat_source;
    }


    if (!empty($taxonomy) && taxonomy_exists($taxonomy)) {
        $term_id = null;

        if (!empty($cat_field_id)) {
            $term_id = $fields[$cat_field_id]['value'] ?? $manager->tag_text($cat_field_id);
        }

        if (!empty($term_id)) {
            wp_set_post_terms($post_id, [(int) $term_id], $taxonomy, false);
        } else {
            // Add to default category if field is empty
            $default_term = get_option('default_category');
            if ($taxonomy === 'category' && $default_term) {
                wp_set_post_terms($post_id, [(int) $default_term], 'category', false);
            }
        }
    }



    // Save custom fields as post meta
    foreach ($fields as $id => $field) {
        // Pomijamy systemowe pola (tytuł, treść, obraz, itd.)
        if (in_array($id, ['title', 'content', 'image_url', 'tags', 'category', 'post_id', 'price', 'postname'])) continue;

        $value = $field['value'];

        // Jeśli pole to checkbox lub select z wieloma wartościami – rozbij na tablicę
        if (
            isset($field['type']) &&
            in_array($field['type'], ['checkbox', 'select']) &&
            is_string($value) &&
            strpos($value, ',') !== false
        ) {
            $value = array_map('trim', explode(',', $value));
        }

        // Nie nadpisuj, jeśli pole jest puste, a w meta już coś istnieje
        if (
            ($value === '' || is_null($value)) &&
            metadata_exists('post', $post_id, $id)
        ) {
            continue;
        }

        update_post_meta($post_id, $id, $value);
    }




    // === FEATURED IMAGE ===
    $image_source = $settings['alex_efpp_featured_image_field'] ?? '';
    $image_field_id = '';

    // Obsługa dynamic tag / shortcode
    if (preg_match('/\[field id="([^"]+)"\]/', $image_source, $img_match)) {
        $image_field_id = $img_match[1];
    } else {
        $image_field_id = $image_source;
    }

    // 💡 NEW: próbujemy odczytać pole ręcznie z $_POST jeśli nie ma go w fields[]
    $image_url = '';
    if (isset($fields[$image_field_id]['value']) && !empty($fields[$image_field_id]['value'])) {
        $image_url = $fields[$image_field_id]['value'];
    } elseif (!empty($_POST[$image_field_id])) {
        $image_url = sanitize_text_field($_POST[$image_field_id]);
    }

    if (!empty($image_url)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Ściągamy obraz i przypisujemy
        $tmp_file = download_url($image_url);

        if (!is_wp_error($tmp_file)) {
            $file_array = [
                'name'     => basename($image_url),
                'tmp_name' => $tmp_file,
            ];

            $attachment_id = media_handle_sideload($file_array, $post_id);

            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($post_id, $attachment_id);
            } else {
                @unlink($file_array['tmp_name']); // Czyścimy w razie błędu
            }
        }
    }


        // === EFPP FEATURED IMAGE ===
    $image_field_id = 'efpp_featured_image'; // ID pola formularza – musi być identyczny jak w form config
    $image_url = '';

    if (!empty($fields[$image_field_id]['value'])) {
        $image_url = esc_url_raw($fields[$image_field_id]['value']);
    }

    if (!empty($image_url)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Pobieramy ID poprzedniego obrazka wyróżniającego
        $prev_thumbnail_id = get_post_thumbnail_id($post_id);

        // Pobieramy i zapisujemy nowy obrazek
        $tmp_file = download_url($image_url);
        if (!is_wp_error($tmp_file)) {
            $file_array = [
                'name'     => basename($image_url),
                'tmp_name' => $tmp_file,
            ];

            $attachment_id = media_handle_sideload($file_array, $post_id);

            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($post_id, $attachment_id);

                // Usuwamy poprzedni obrazek (jeśli istnieje i nie jest taki sam)
                if ($prev_thumbnail_id && $prev_thumbnail_id !== $attachment_id) {
                    wp_delete_attachment($prev_thumbnail_id, true);
                }

            } else {
                // Błąd – usuwamy plik tymczasowy
                @unlink($file_array['tmp_name']);
            }
        }
    }



            // Tags
        if (!empty($fields['tags']['value'])) {
            $tags = array_map('trim', explode(',', $fields['tags']['value']));
            wp_set_post_terms($post_id, $tags, 'post_tag');
        }

        // === WooCommerce: obsługa produktów ===
        if ($type === 'product') {
            $price_field_id = $settings['alex_efpp_price_field'] ?? '';
            $price = '';

            if (!empty($price_field_id)) {
                $price = $fields[$price_field_id]['value'] ?? $manager->tag_text($price_field_id);
            }

            $price = is_numeric($price) ? floatval($price) : 0;

            update_post_meta($post_id, '_regular_price', $price);
            update_post_meta($post_id, '_price', $price);

            $product_type = $settings['alex_efpp_product_type'] ?? 'simple';
            wp_set_object_terms($post_id, $product_type, 'product_type');

            update_post_meta($post_id, '_stock_status', 'instock');

            do_action('woocommerce_process_product_meta_' . $product_type, $post_id);
        }
    }

    public function on_export($element) {}


    private function media_sideload_image($file_url, $post_id) {
        if (!function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp = download_url($file_url);
        if (is_wp_error($tmp)) return false;

        $file_array = [
            'name'     => basename($file_url),
            'tmp_name' => $tmp,
        ];

        $id = media_handle_sideload($file_array, $post_id);
        if (is_wp_error($id)) return false;

        return $id;
    }
}