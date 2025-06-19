<?php

use ElementorPro\Modules\Forms\Classes\Action_Base;
use Elementor\Core\DynamicTags\Manager as TagsManager;
use Elementor\Core\DynamicTags\Manager as DynamicTagsManager;

$manager = \Elementor\Plugin::$instance->dynamic_tags;

if (!defined('ABSPATH')) exit;

class Alex_EFPP_Form_Action_Post extends Action_Base {

    public function get_name() {
        return 'alex_efpp';
    }

    public function get_label() {
        return 'Alex EFPP – Create Post';
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
            'alex_efpp_post_type',
            [
                'label' => 'Post Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $post_types,
                'default' => 'post',
                
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
                    'pending' => 'Pending Review',
                ],
                'default' => 'draft',
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

        $widget->add_control(
            'alex_efpp_post_category_field',
            [
                'label' => 'Field ID for Categories',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => '[field id="category"]',
                'description' => 'Enter the shortcode of the field that should define the post categories.',
                'dynamic' => [ 'active' => true ],
                'ai' => [ 'active' => false ],
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
        $settings = $record->get('form_settings');
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

        // Dalej kontynuuj tworzenie posta...

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


    $content = $fields['content']['value'] ?? '';
    $type    = $settings['alex_efpp_post_type'] ?? 'post';
    $status  = $settings['alex_efpp_post_status'] ?? 'draft';

    $post_id = wp_insert_post([
        'post_type'    => $type,
        'post_status'  => $status,
        'post_title'   => sanitize_text_field($title),
        'post_content' => wp_kses_post($content),
    ]);

    if (is_wp_error($post_id)) {
        $ajax_handler->add_error_message(__('Error creating post.', 'alex-efpp'));
        return;
    }

    // === CATEGORIES ===
    $cat_source = $settings['alex_efpp_post_category_field'] ?? '';
    $cat_field_id = '';

    if (preg_match('/\[field id="([^"]+)"\]/', $cat_source, $cat_match)) {
        $cat_field_id = $cat_match[1]; // legacy
    } else {
        $cat_field_id = $cat_source;
    }

    if (!empty($cat_field_id)) {
        $cat_value = $fields[$cat_field_id]['value'] ?? $manager->tag_text($cat_field_id);
        if (!empty($cat_value)) {
            $cat_names = array_map('trim', explode(',', $cat_value));
            $cat_ids = [];

            foreach ($cat_names as $cat_name) {
                if (empty($cat_name)) continue;

                $term = get_term_by('name', $cat_name, 'category');

                if (!$term) {
                    $new_term = wp_insert_term($cat_name, 'category');
                    if (!is_wp_error($new_term)) {
                        $cat_ids[] = $new_term['term_id'];
                    }
                } else {
                    $cat_ids[] = $term->term_id;
                }
            }

            if (!empty($cat_ids)) {
                wp_set_post_terms($post_id, $cat_ids, 'category');
            }

        }
    }


        // Save custom fields as post meta
        foreach ($fields as $id => $field) {
            if (in_array($id, [$title_field_id, 'content', 'image', 'category', 'tags'])) continue;

            $value = $field['value'];
            if (strpos($value, ',') !== false) {
                $value = array_map('trim', explode(',', $value));
            }

            update_post_meta($post_id, $id, $value);
        }


        // === FEATURED IMAGE ===
        $image_source = $settings['alex_efpp_featured_image_field'] ?? '';
        $image_field_id = '';

        if (preg_match('/\[field id="([^"]+)"\]/', $image_source, $img_match)) {
            $image_field_id = $img_match[1];
        } else {
            $image_field_id = $image_source;
        }

        if (!empty($image_field_id)) {
            $image_url = $fields[$image_field_id]['value'] ?? $manager->tag_text($image_field_id);

            if (!empty($image_url)) {
                $attachment_id = $this->media_sideload_image($image_url, $post_id);
                if ($attachment_id) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }
        }


        // Tags
        if (!empty($fields['tags']['value'])) {
            $tags = array_map('trim', explode(',', $fields['tags']['value']));
            wp_set_post_terms($post_id, $tags, 'post_tag');
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
