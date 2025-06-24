<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;
use Elementor\Core\DynamicTags\Manager as TagsManager;
use Elementor\Core\DynamicTags\Manager as DynamicTagsManager;

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
        $role_options['guest'] = 'Guest (not logged in)';

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

        // $widget->add_control(
        //     'alex_efpp_redirect_after_submit_create',
        //     [
        //         'label' => __('Redirect to new post after submit', 'alex-efpp'),
        //         'type' => \Elementor\Controls_Manager::SWITCHER,
        //         'label_on' => __('Yes', 'alex-efpp'),
        //         'label_off' => __('No', 'alex-efpp'),
        //         'return_value' => 'yes',
        //         'default' => 'no',
        //         'condition' => [
        //             'submit_actions' => $this->get_name(),
        //             'alex_efpp_post_mode' => 'create',
        //         ],
        //     ]
        // );

        $widget->add_control(
            'alex_efpp_post_type',
            [
                'label' => 'Post Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $post_types,
                'default' => 'post',
            ]
        );

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
            'alex_efpp_redirect_type',
            [
                'label' => __('Redirect After Submit', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'   => __('None', 'alex-efpp'),
                    'post'   => __('Redirect to new post', 'alex-efpp'),
                    'custom' => __('Redirect to custom URL', 'alex-efpp'),
                ],
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'alex_efpp_custom_redirect_url',
            [
                'label' => __('Custom Redirect URL', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/thanks',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_redirect_type' => 'custom',
                ],
                'ai' => [ 'active' => false ],
            ]
        );

        $widget->add_control(
            'alex_efpp_post_id_field',
            [
                'label' => __('Post ID Field', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'post_id',
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'alex_efpp_post_mode' => 'update',
                ],
            ]
        );

        $widget->add_control(
            'efpp_post_field_map',
            [
                'label' => __('Post Field Mapping', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'field_type',
                        'label' => 'Target Field',
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'options' => [
                            'title' => 'Post Title',
                            'content' => 'Post Content',
                            'featured_image' => 'Featured Image (URL)',
                            'price' => 'WooCommerce Price',
                            'post_date' => 'Post Date (Y-m-d)',
                            'post_time' => 'Post Time (H:i)',
                            'custom_field' => 'Custom Field (meta)',
                            'taxonomy' => 'Taxonomy (term_id)',
                            
                        ],
                        'default' => 'custom_field',
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'form_field_id',
                        'label' => 'Form Field ID',
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g. title, price, custom_field_1',
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'meta_key',
                        'label' => 'Meta Key (for custom field)',
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g. _custom_price',
                        'condition' => [ 'field_type' => 'custom_field' ],
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'taxonomy_slug',
                        'label' => 'Taxonomy Slug',
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g. category',
                        'condition' => [ 'field_type' => 'taxonomy' ],
                        'ai' => [ 'active' => false ],
                    ],
                ],
                'title_field' => '{{ field_type }}',
                'condition' => [ 'submit_actions' => $this->get_name() ],
                'ai' => [ 'active' => false ],
            ]
        );



        $widget->end_controls_section();
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



    // public function run($record, $ajax_handler) {
    //     $manager = \Elementor\Plugin::$instance->dynamic_tags;
    //     $settings = $record->get('form_settings');
    //     $post_mode = $settings['alex_efpp_post_mode'] ?? 'create';
    //     $fields = $record->get('fields');
    //     //$allowed_role = $settings['alex_efpp_allowed_role'] ?? 'subscriber';
    //     $allowed_roles = $settings['alex_efpp_allowed_role'] ?? ['subscriber'];
    //     if (!is_array($allowed_roles)) {
    //         $allowed_roles = [$allowed_roles];
    //     }

    //     $valid_roles = array_keys(get_editable_roles());
    //     $valid_roles[] = 'guest';

    //     foreach ($allowed_roles as $role) {
    //         if (!in_array($role, $valid_roles)) {
    //             $ajax_handler->add_error_message('Błędna konfiguracja ról – formularz został zatrzymany.');
    //             return;
    //         }
    //     }

    //     if (!is_user_logged_in()) {
    //         if (!in_array('guest', $allowed_roles)) {
    //             $ajax_handler->add_error_message('Tylko zalogowani użytkownicy mogą publikować treści.');
    //             return;
    //         }
    //     } else {
    //         $user = wp_get_current_user();
    //         $user_roles = $user->roles ?? [];

    //         $access_granted = false;
    //         foreach ($user_roles as $role) {
    //             if (in_array($role, $allowed_roles)) {
    //                 $access_granted = true;
    //                 break;
    //             }
    //         }

    //         if (!$access_granted) {
    //             $ajax_handler->add_error_message('Nie masz odpowiednich uprawnień do publikacji.');
    //             return;
    //         }
    //     }

    //     // Create post...


    // // === TITLE ===
    // $title_source = $settings['alex_efpp_post_title_field'] ?? '';
    // $title_field_id = '';

    // if (preg_match('/\[field id="([^"]+)"\]/', $title_source, $match)) {
    //     $title_field_id = $match[1]; // legacy compatibility
    // } else {
    //     $title_field_id = $title_source;
    // }

    // $title = 'Untitled';
    // if (!empty($title_field_id)) {
    //     if (!empty($fields[$title_field_id]['value'])) {
    //         $title = $fields[$title_field_id]['value'];
    //     } else {
    //         // fallback to dynamic tag value (e.g. user info, post title, etc.)
    //         $tag_text = $manager->tag_text($title_field_id);
    //         if (!empty($tag_text)) {
    //             $title = $tag_text;
    //         }
    //     }
    // }


    // $content = isset($fields['content']) ? $fields['content']['value'] : '';
    // $type    = $settings['alex_efpp_post_type'] ?? 'post';
    // $status  = $settings['alex_efpp_post_status'] ?? 'draft';

    // // === POST DATE from custom date & time fields ===
    //     $post_date = current_time('mysql'); // fallback: teraz

    //     $date_field_id = $settings['alex_efpp_post_date_field'] ?? '';
    //     $time_field_id = $settings['alex_efpp_post_time_field'] ?? '';

    //     $date = !empty($date_field_id) ? ($fields[$date_field_id]['value'] ?? '') : '';
    //     $time = !empty($time_field_id) ? ($fields[$time_field_id]['value'] ?? '') : '';

    //     if ($date && $time) {
    //         $datetime = strtotime($date . ' ' . $time);
    //         if ($datetime) {
    //             $post_date = date('Y-m-d H:i:s', $datetime);
    //         }
    //     }

    //     // === CREATE or UPDATE ===
    // $post_data = [
    //     'post_type'    => $type,
    //     'post_status'  => $status,
    //     'post_title'   => sanitize_text_field($title),
    //     'post_content' => wp_kses_post($content),
    //     'post_date'    => $post_date,
    //     'post_date_gmt'=> get_gmt_from_date($post_date),

    // ];

    // // if (!empty($post_date)) {
    // //     $post_date['post_date'] = $post_date;
    // //     $post_date['post_date_gmt'] = get_gmt_from_date($post_date);
    // // }

    // if ($post_mode === 'update') {
    //     $post_id_field = $settings['alex_efpp_post_id_field'] ?? 'post_id';
    //     $update_post_id = $fields[$post_id_field]['value'] ?? null;

    //     if (!empty($update_post_id) && get_post_status($update_post_id)) {
    //         $post_data['ID'] = (int) $update_post_id;
    //         $post_id = wp_update_post($post_data);
    //     } else {
    //         $ajax_handler->add_error_message(__('Post ID is missing or invalid.', 'alex-efpp'));
    //         return;
    //     }
    // } else {
    //     $post_id = wp_insert_post($post_data);

    //     if (is_wp_error($post_id)) {
    //         $ajax_handler->add_error_message(__('Error saving post.', 'alex-efpp'));
    //         return;
    //     }

    //     // Redirect if enabled
    //     if (!empty($settings['alex_efpp_redirect_after_submit_create']) && $settings['alex_efpp_redirect_after_submit_create'] === 'yes') {
    //         $ajax_handler->add_success_message(__('Zapisano. Trwa przekierowanie do nowego wpisu...', 'alex-efpp'));
    //         //$ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
    //         if ($post_id && get_post_status($post_id)) {
    //             $ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
    //         }
    //     } elseif (!empty($settings['alex_efpp_reload_after_submit']) && $settings['alex_efpp_reload_after_submit'] === 'yes') {
    //         $ajax_handler->add_success_message(__('Zapisano. Trwa odświeżanie strony...', 'alex-efpp'));
    //     }
    // }


    // // globalne sprawdzenie błędu
    // if (is_wp_error($post_id)) {
    //     $ajax_handler->add_error_message(__('Error saving post.', 'alex-efpp'));
    //     return;
    // }

    // // redirect lub reload
    // if ($post_mode === 'update') {
    //     if (!empty($settings['alex_efpp_redirect_after_submit']) && $settings['alex_efpp_redirect_after_submit'] === 'yes') {
    //         $ajax_handler->add_success_message(__('Zapisano. Trwa przekierowanie...', 'alex-efpp'));
    //         //$ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
    //         if ($post_id && get_post_status($post_id)) {
    //             $ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
    //         }
    //     } elseif (!empty($settings['alex_efpp_reload_after_submit']) && $settings['alex_efpp_reload_after_submit'] === 'yes') {
    //         $ajax_handler->add_success_message(__('Zapisano. Trwa odświeżanie strony...', 'alex-efpp'));
    //     }
    // }

    // // Reload if enabled
    // if (!empty($settings['alex_efpp_reload_after_submit']) && $settings['alex_efpp_reload_after_submit'] === 'yes') {
    //     $ajax_handler->add_success_message(__('Zapisano. Trwa odświeżanie strony...', 'alex-efpp'));
    //     // Ustawiamy aktualny adres strony jako redirect_url
    //     $ajax_handler->add_response_data( 'redirect_url', get_permalink($post_id) );
    // }


    // // === TAXONOMY TERMS (SINGLE SELECT from term_id) ===
    // $taxonomy = $settings['alex_efpp_taxonomy'] ?? 'category';
    // $cat_source = $settings['alex_efpp_post_category_field'] ?? '';
    // $cat_field_id = '';

    // // Obsługa [field id="..."]
    // if (preg_match('/\[field id="([^"]+)"\]/', $cat_source, $match)) {
    //     $cat_field_id = $match[1];
    // } else {
    //     $cat_field_id = $cat_source;
    // }


    // if (!empty($taxonomy) && taxonomy_exists($taxonomy)) {
    //     $term_id = null;

    //     if (!empty($cat_field_id)) {
    //         $term_id = $fields[$cat_field_id]['value'] ?? $manager->tag_text($cat_field_id);
    //     }

    //     if (!empty($term_id)) {
    //         wp_set_post_terms($post_id, [(int) $term_id], $taxonomy, false);
    //     } else {
    //         // Add to default category if field is empty
    //         $default_term = get_option('default_category');
    //         if ($taxonomy === 'category' && $default_term) {
    //             wp_set_post_terms($post_id, [(int) $default_term], 'category', false);
    //         }
    //     }
    // }



    // // Save custom fields as post meta
    // foreach ($fields as $id => $field) {
    //     // Pomijamy systemowe pola (tytuł, treść, obraz, itd.)
    //     if (in_array($id, ['title', 'content', 'image_url', 'tags', 'category', 'post_id', 'price', 'postname'])) continue;

    //     if (!is_array($field)) {
    //         continue; // pomijamy uszkodzone/puste pole
    //     }

    //     $value = $field['value'] ?? '';

    //     // Jeśli pole to checkbox lub select z wieloma wartościami – rozbij na tablicę
    //     if (
    //         isset($field['type']) &&
    //         in_array($field['type'], ['checkbox', 'select']) &&
    //         is_string($value) &&
    //         strpos($value, ',') !== false
    //     ) {
    //         $value = array_map('trim', explode(',', $value));
    //     }

    //     // Nie nadpisuj, jeśli pole jest puste, a w meta już coś istnieje
    //     if (
    //         ($value === '' || is_null($value)) &&
    //         metadata_exists('post', $post_id, $id)
    //     ) {
    //         continue;
    //     }

    //     update_post_meta($post_id, $id, $value);
    // }




    // // === FEATURED IMAGE ===
    // $image_source = $settings['alex_efpp_featured_image_field'] ?? '';
    // $image_field_id = '';

    // // Obsługa dynamic tag / shortcode
    // if (preg_match('/\[field id="([^"]+)"\]/', $image_source, $img_match)) {
    //     $image_field_id = $img_match[1];
    // } else {
    //     $image_field_id = $image_source;
    // }

    // // 💡 NEW: próbujemy odczytać pole ręcznie z $_POST jeśli nie ma go w fields[]
    // $image_url = '';
    // if (isset($fields[$image_field_id]['value']) && !empty($fields[$image_field_id]['value'])) {
    //     $image_url = $fields[$image_field_id]['value'];
    // } elseif (!empty($_POST[$image_field_id])) {
    //     $image_url = sanitize_text_field($_POST[$image_field_id]);
    // }

    // if (!empty($image_url)) {
    //     require_once ABSPATH . 'wp-admin/includes/file.php';
    //     require_once ABSPATH . 'wp-admin/includes/media.php';
    //     require_once ABSPATH . 'wp-admin/includes/image.php';

    //     // Ściągamy obraz i przypisujemy
    //     $tmp_file = download_url($image_url);

    //     if (!is_wp_error($tmp_file)) {
    //         $file_array = [
    //             'name'     => basename($image_url),
    //             'tmp_name' => $tmp_file,
    //         ];

    //         $attachment_id = media_handle_sideload($file_array, $post_id);

    //         if (!is_wp_error($attachment_id)) {
    //             set_post_thumbnail($post_id, $attachment_id);
    //         } else {
    //             @unlink($file_array['tmp_name']); // Czyścimy w razie błędu
    //         }
    //     }
    // }


    //     // === EFPP FEATURED IMAGE ===
    // // $image_field_id = 'efpp_featured_image'; // ID pola formularza – musi być identyczny jak w form config
    // // $image_url = '';

    // // if (!empty($fields[$image_field_id]['value'])) {
    // //     $image_url = esc_url_raw($fields[$image_field_id]['value']);
    // // }
    // // if (!empty($fields[$image_field_id]) && is_array($fields[$image_field_id]) && !empty($fields[$image_field_id]['value'])) {
    // //     $image_url = esc_url_raw($fields[$image_field_id]['value']);
    // // }

    // $image_field_id = 'efpp_featured_image';
    // $image_value = $fields[$image_field_id] ?? '';
    // $image_url = '';

    // // Obsługa array vs string
    // if (is_array($image_value) && !empty($image_value['value'])) {
    //     $image_url = esc_url_raw($image_value['value']);
    // } elseif (is_string($image_value)) {
    //     $image_url = esc_url_raw($image_value);
    // } elseif (!empty($_POST[$image_field_id])) {
    //     $image_url = esc_url_raw($_POST[$image_field_id]);
    // }


    // if (!empty($image_url)) {
    //     require_once ABSPATH . 'wp-admin/includes/file.php';
    //     require_once ABSPATH . 'wp-admin/includes/media.php';
    //     require_once ABSPATH . 'wp-admin/includes/image.php';

    //     // Pobieramy ID poprzedniego obrazka wyróżniającego
    //     $prev_thumbnail_id = get_post_thumbnail_id($post_id);

    //     // Pobieramy i zapisujemy nowy obrazek
    //     $tmp_file = download_url($image_url);
    //     if (!is_wp_error($tmp_file)) {
    //         $file_array = [
    //             'name'     => basename($image_url),
    //             'tmp_name' => $tmp_file,
    //         ];

    //         $attachment_id = media_handle_sideload($file_array, $post_id);

    //         if (!is_wp_error($attachment_id)) {
    //             set_post_thumbnail($post_id, $attachment_id);

    //             // Usuwamy poprzedni obrazek (jeśli istnieje i nie jest taki sam)
    //             if ($prev_thumbnail_id && $prev_thumbnail_id !== $attachment_id) {
    //                 wp_delete_attachment($prev_thumbnail_id, true);
    //             }

    //         } else {
    //             // Błąd – usuwamy plik tymczasowy
    //             @unlink($file_array['tmp_name']);
    //         }
    //     }
    // }



    //         // Tags
    //     if (!empty($fields['tags']['value'])) {
    //         $tags = array_map('trim', explode(',', $fields['tags']['value']));
    //         wp_set_post_terms($post_id, $tags, 'post_tag');
    //     }

    //     // === WooCommerce: obsługa produktów ===
    //     if ($type === 'product') {
    //         $price_field_id = $settings['alex_efpp_price_field'] ?? '';
    //         $price = '';

    //         if (!empty($price_field_id)) {
    //             $price = $fields[$price_field_id]['value'] ?? $manager->tag_text($price_field_id);
    //         }

    //         $price = is_numeric($price) ? floatval($price) : 0;

    //         update_post_meta($post_id, '_regular_price', $price);
    //         update_post_meta($post_id, '_price', $price);

    //         $product_type = $settings['alex_efpp_product_type'] ?? 'simple';
    //         wp_set_object_terms($post_id, $product_type, 'product_type');

    //         update_post_meta($post_id, '_stock_status', 'instock');

    //         do_action('woocommerce_process_product_meta_' . $product_type, $post_id);
    //     }
    // }


    public function run($record, $ajax_handler) {
        $manager = \Elementor\Plugin::$instance->dynamic_tags;
        $settings = $record->get('form_settings');
        $fields   = $record->get('fields');
        error_log( print_r( $fields, true ) );

        $field_map = $settings['efpp_post_field_map'] ?? [];

        // --- Role check ---
        $allowed_roles = $settings['alex_efpp_allowed_role'] ?? ['subscriber'];
        if (!is_array($allowed_roles)) $allowed_roles = [$allowed_roles];

        $valid_roles = array_merge(array_keys(get_editable_roles()), ['guest']);

        foreach ($allowed_roles as $role) {
            if (!in_array($role, $valid_roles)) {
                $ajax_handler->add_error_message('Invalid role configuration.');
                return;
            }
        }

        if (!is_user_logged_in()) {
            if (!in_array('guest', $allowed_roles)) {
                $ajax_handler->add_error_message('You must be logged in to submit.');
                return;
            }
        } else {
            $user = wp_get_current_user();
            if (!array_intersect($user->roles, $allowed_roles)) {
                $ajax_handler->add_error_message('You do not have permission to submit.');
                return;
            }
        }

        // --- Prepare initial post data ---
        $post_data = [
            'post_type'    => $settings['alex_efpp_post_type'] ?? 'post',
            'post_status'  => $settings['alex_efpp_post_status'] ?? 'draft',
            'post_title'   => 'Untitled',
            'post_content' => '',
            'post_date'    => current_time('mysql'),
        ];

        $post_meta = [];
        $post_terms = [];
        $post_date_raw = null;
        $post_time_raw = null;
        $featured_image_url = null;
        $price_value = null;

        foreach ($field_map as $map) {
            $type = $map['field_type'] ?? '';
            $form_field = $map['form_field_id'] ?? '';
            if (!$form_field || !$type) continue;

            $value = $fields[$form_field]['value'] ?? $manager->tag_text($form_field);

            switch ($type) {
                case 'title':
                    $post_data['post_title'] = sanitize_text_field($value);
                    break;
                case 'content':
                    $post_data['post_content'] = wp_kses_post($value);
                    break;
                case 'featured_image':
                    $featured_image_url = esc_url_raw($value);
                    error_log('[EFPP] featured_image_url: ' . $featured_image_url);
                    break;
                case 'post_date':
                    $post_date_raw = $value;
                    break;
                case 'post_time':
                    $post_time_raw = $value;
                    break;
                case 'price':
                    $price_value = is_numeric($value) ? floatval($value) : 0;
                    break;
                case 'custom_field':
                    $meta_key = $map['meta_key'] ?? '';
                    if ($meta_key) {
                        $post_meta[$meta_key] = $value;
                    }
                    break;
                case 'taxonomy':
                    $taxonomy = $map['taxonomy_slug'] ?? '';
                    if ($taxonomy && taxonomy_exists($taxonomy) && !empty($value)) {
                        $term = get_term_by('slug', $value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $post_terms[$taxonomy] = [ (int) $term->term_id ];
                        }
                    }
                    break;
            }
        }

        // --- Handle post_date & post_time merge ---
        if ($post_date_raw && $post_time_raw) {
            $timestamp = strtotime("$post_date_raw $post_time_raw");
            if ($timestamp) {
                $post_data['post_date'] = date('Y-m-d H:i:s', $timestamp);
                $post_data['post_date_gmt'] = get_gmt_from_date($post_data['post_date']);
            }
        }

        // --- Create or Update ---
        $post_mode = $settings['alex_efpp_post_mode'] ?? 'create';
        if ($post_mode === 'update') {
            $id_field = $settings['alex_efpp_post_id_field'] ?? 'post_id';
            $update_id = $fields[$id_field]['value'] ?? 0;
            if (!$update_id || !get_post_status($update_id)) {
                $ajax_handler->add_error_message(__('Post ID is missing or invalid.', 'alex-efpp'));
                return;
            }
            $post_data['ID'] = (int)$update_id;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if (is_wp_error($post_id)) {
            $ajax_handler->add_error_message(__('Error saving post.', 'alex-efpp'));
            return;
        }

        // --- Set featured image ---
        if (!empty($featured_image_url)) {
            error_log('[EFPP] Próbuję przypisać obrazek z: ' . $featured_image_url);
            $attachment_id = null;

            if (is_numeric($featured_image_url)) {
                // Jeśli formularz zwraca ID załącznika
                $attachment_id = (int)$featured_image_url;
                if (!wp_get_attachment_url($attachment_id)) {
                    $attachment_id = null;
                    error_log('[EFPP] Podany ID nie jest poprawnym załącznikiem.');
                } else {
                    error_log('[EFPP] Użyto istniejącego załącznika ID: ' . $attachment_id);
                }
            } elseif (filter_var($featured_image_url, FILTER_VALIDATE_URL)) {
                // Jeśli formularz zwraca URL – próbujemy zaciągnąć obraz
                $attachment_id = $this->media_sideload_image($featured_image_url, $post_id);
                error_log('[EFPP] media_sideload_image zwrócił ID: ' . print_r($attachment_id, true));
            }

            if ($attachment_id) {
                set_post_thumbnail($post_id, $attachment_id);
                error_log('[EFPP] Ustawiono obrazek wyróżniający ID: ' . $attachment_id);
            } else {
                error_log('[EFPP] Nie udało się przypisać obrazka.');
            }
        }

        // --- Save custom fields ---
        foreach ($post_meta as $key => $val) {
            update_post_meta($post_id, $key, $val);
        }

        // --- Set taxonomies ---
        foreach ($post_terms as $taxonomy => $term_ids) {
            wp_set_post_terms($post_id, $term_ids, $taxonomy, false);
        }

        // --- WooCommerce support ---
        if ($post_data['post_type'] === 'product') {
            update_post_meta($post_id, '_regular_price', $price_value);
            update_post_meta($post_id, '_price', $price_value);
            $product_type = $settings['alex_efpp_product_type'] ?? 'simple';
            wp_set_object_terms($post_id, $product_type, 'product_type');
            update_post_meta($post_id, '_stock_status', 'instock');
            do_action('woocommerce_process_product_meta_' . $product_type, $post_id);
        }

        // --- Redirect if enabled ---
        $redirect_type = $settings['alex_efpp_redirect_type'] ?? 'none';

        switch ($redirect_type) {
            case 'post':
                if (get_post_status($post_id)) {
                    $ajax_handler->add_success_message(__('Saved. Redirecting to post...', 'alex-efpp'));
                    $ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
                }
                break;

            case 'custom':
                $custom_url = $settings['alex_efpp_custom_redirect_url'] ?? '';
                if (!empty($custom_url) && filter_var($custom_url, FILTER_VALIDATE_URL)) {
                    $ajax_handler->add_success_message(__('Saved. Redirecting...', 'alex-efpp'));
                    $ajax_handler->add_response_data('redirect_url', esc_url_raw($custom_url));
                }
                break;

            case 'none':
            default:
                // Optional: message without redirect
                $ajax_handler->add_success_message(__('Saved successfully.', 'alex-efpp'));
                break;
        }
    }

}