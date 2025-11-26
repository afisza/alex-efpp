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
        return __('EFPP – Create/Update Post', 'alex-efpp');
    }

    public function register_settings_section($widget) {
        $post_types = get_post_types(['public' => true], 'names');
        if (empty($post_types)) {
            $post_types = ['post' => 'Post'];
        }

        $widget->start_controls_section(
            'section_alex_efpp',
            [
                'label' =>  __('EFPP – Publish Settings'),
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
        $role_options['guest'] = __('Guest (not logged in)', 'alex-efpp');

        $widget->add_control(
            'alex_efpp_allowed_role',
            [
                'label' => __('Allowed Role', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $role_options,
                'default' => ['administrator', 'editor'],
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
            'alex_efpp_post_type',
            [
                'label' => __('Post Type', 'alex-efpp'),
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
                    'simple'   => __('Simple', 'alex-efpp'),
                    'grouped'  => __('Grouped', 'alex-efpp'),
                    'external' => __('External/Affiliate', 'alex-efpp'),
                    'variable' => __('Variable', 'alex-efpp'),
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
                'label' => __('Post Status', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'draft'   => __('Draft', 'alex-efpp'),
                    'publish' => __('Published', 'alex-efpp'),
                    'future'  => __('Scheduled', 'alex-efpp'),
                    'pending' => __('Pending Review', 'alex-efpp'),
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

        $field_names = array(
            'title'          => __('Post Title', 'alex-efpp'),
            'content'        => __('Post Content', 'alex-efpp'),
            'featured_image' => __('Featured Image (URL)', 'alex-efpp'),
            'price'          => __('WooCommerce Price', 'alex-efpp'),
            'post_date'      => __('Post Date (Y-m-d)', 'alex-efpp'),
            'post_time'      => __('Post Time (H:i)', 'alex-efpp'),
            'custom_field'   => __('Custom Field', 'alex-efpp'),
            'gallery_field'  => __('Gallery Field', 'alex-efpp'),
            'taxonomy'       => __('Taxonomy (term_id)', 'alex-efpp'),
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
                        'options' => $field_names,
                        'default' => 'custom_field',
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'form_field_id',
                        'label' => __('Form Field ID'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'title, price, custom_field_1',
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'meta_key',
                        'label' => __('Meta Key (for custom field)'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => '_custom_price',
                        'condition' => [
                            'field_type' => 'custom_field',
                        ],
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'meta_key_gallery',
                        'label' => __('Meta Key', 'alex-efpp'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => '_gallery_images',
                        'condition' => [
                            'field_type' => 'gallery_field',
                        ],
                        'ai' => [ 'active' => false ],
                    ],
                    [
                        'name' => 'taxonomy_slug',
                        'label' => __('Taxonomy Slug'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g. category',
                        'condition' => [ 'field_type' => 'taxonomy' ],
                        'ai' => [ 'active' => false ],
                    ],

                    [
                        'name' => 'gallery_limit',
                        'label' => __('Max images count'),
                        'type' => \Elementor\Controls_Manager::NUMBER,
                        'default' => 12,
                        'condition' => [
                            'field_type' => 'gallery_field',
                            'meta_key!' => '',
                        ],
                    ],
                    [
                        'name' => 'gallery_max_size',
                        'label' => __('Max file size (MB)'),
                        'type' => \Elementor\Controls_Manager::NUMBER,
                        'default' => 5,
                        'condition' => [
                            'field_type' => 'gallery_field',
                            'meta_key!' => '',
                        ],
                    ],
                    [
                        'name' => 'gallery_allowed_types',
                        'label' => __('Allowed file types (comma separated)'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'jpg,png,webp',
                        'condition' => [
                            'field_type' => 'gallery_field',
                            'meta_key!' => '',
                        ],
                    ],

                ],
                //'title_field' => '{{ field_type }}',
                'title_field' => '{{ field_type }}: {{ form_field_id }}',
                'condition' => [ 'submit_actions' => $this->get_name() ],
                'max_items' => 50,
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

    public function run($record, $ajax_handler) {
        $manager = \Elementor\Plugin::$instance->dynamic_tags;

        $settings = $record->get('form_settings');
        $fields   = $record->get('fields');

        $field_map = $settings['efpp_post_field_map'] ?? [];

        // --- Role check ---
        $allowed_roles = $settings['alex_efpp_allowed_role'] ?? ['administrator'];
        if (!is_array($allowed_roles)) $allowed_roles = [$allowed_roles];

        $valid_roles = array_merge(array_keys(get_editable_roles()), ['guest']);

        foreach ($allowed_roles as $role) {
            if (!in_array($role, $valid_roles)) {
                $ajax_handler->add_error_message(__('Invalid role configuration.', 'alex-efpp'));
                return;
            }
        }

        if (!is_user_logged_in()) {
            if (!in_array('guest', $allowed_roles)) {
                $ajax_handler->add_error_message(__('You must be logged in to submit.', 'alex-efpp'));
                return;
            }
        } else {
            $user = wp_get_current_user();
            if (!array_intersect($user->roles, $allowed_roles)) {
                $role_list = implode(', ', $allowed_roles);
                $ajax_handler->add_error_message(sprintf(
                    __('You do not have permission to submit. Allowed roles: %s', 'alex-efpp'),
                    $role_list
                ));
                return;
            }
        }

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
        $gallery_urls = [];
        $price_value = null;
        $gallery_meta_key = '';
        $gallery_urls = '';

        foreach ($field_map as $map) {
            $type = $map['field_type'] ?? '';
            $form_field = $map['form_field_id'] ?? '';

            $gallery_urls = null;
            $gallery_meta_key = null;

            if (!$form_field || !$type) continue;

            $field_data = $fields[$form_field] ?? [];
            $value = $field_data['raw_value'] ?: $field_data['value'];

            switch ($type) {
                case 'title':
                    $post_data['post_title'] = sanitize_text_field($value);
                    break;
                case 'content':
                    $post_data['post_content'] = wp_kses_post($value);
                    break;
                case 'featured_image':
                    $featured_image_url = esc_url_raw($value);
                    break;
                case 'gallery_field':
                    $gallery_meta_key = $map['meta_key_gallery'] ?? $map['meta_key'] ?? 'gallery';
                    $gallery_urls = $fields[$form_field]['raw_value'] ?? ($fields[$form_field]['value'] ?? '');
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
                        $values = is_array($value) ? $value : array_map('trim', explode(',', $value));
                        $term_ids = [];
                        foreach ($values as $slug) {
                            $term = get_term_by('slug', $slug, $taxonomy);
                            if ($term && !is_wp_error($term)) {
                                $term_ids[] = (int) $term->term_id;
                            }
                        }
                        if (!empty($term_ids)) {
                            $post_terms[$taxonomy] = $term_ids;
                        }
                    }
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
            }
        }

        if ($post_date_raw && $post_time_raw) {
            $timestamp = strtotime("$post_date_raw $post_time_raw");
            if ($timestamp) {
                $post_data['post_date'] = date('Y-m-d H:i:s', $timestamp);
                $post_data['post_date_gmt'] = get_gmt_from_date($post_data['post_date']);
                if ($timestamp > current_time('timestamp')) {
                    $post_data['post_status'] = 'future';
                }
            }
        }

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

        if (!empty($featured_image_url)) {
            $attachment_id = $this->media_sideload_image($featured_image_url, $post_id);
            if ($attachment_id) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        // --- Obsługa galerii (media_sideload_image + featured image) ---
        if ($gallery_urls && $gallery_meta_key) {
            $urls = is_array($gallery_urls) ? $gallery_urls : array_map('trim', explode(',', $gallery_urls));
            $attachment_ids = [];

            foreach ($urls as $index => $url) {
                if (!$url) continue;

                $id = attachment_url_to_postid($url);
                if (!$id && filter_var($url, FILTER_VALIDATE_URL)) {
                    $id = $this->media_sideload_image($url, $post_id);
                }

                if ($id && !is_wp_error($id)) {
                    $attachment_ids[] = (int) $id;

                    // Ustaw pierwszy jako featured image
                    if ($index === 0 && !has_post_thumbnail($post_id)) {
                        set_post_thumbnail($post_id, $id);
                    }
                }
            }

            if (!empty($attachment_ids)) {
                update_post_meta($post_id, $gallery_meta_key, $attachment_ids);
            }
        }



        foreach ($post_meta as $key => $val) {
            update_post_meta($post_id, $key, $val);
        }

        foreach ($post_terms as $taxonomy => $term_ids) {
            wp_set_post_terms($post_id, $term_ids, $taxonomy, false);
        }

        if ($post_data['post_type'] === 'product') {
            update_post_meta($post_id, '_regular_price', $price_value);
            update_post_meta($post_id, '_price', $price_value);
            $product_type = $settings['alex_efpp_product_type'] ?? 'simple';
            wp_set_object_terms($post_id, $product_type, 'product_type');
            update_post_meta($post_id, '_stock_status', 'instock');
            do_action('woocommerce_process_product_meta_' . $product_type, $post_id);
        }

        $redirect_type = $settings['alex_efpp_redirect_type'] ?? 'none';

        $success_message = '';

        if ($post_mode === 'update') {
            $success_message = __('Updated entry successfully.', 'alex-efpp');
        } else {
            $success_message = __('POST added successfully.', 'alex-efpp');
        }

        switch ($redirect_type) {
            case 'post':
                if (get_post_status($post_id)) {
                    $ajax_handler->add_response_data('form', true);
                    $ajax_handler->add_response_data('message', $success_message);
                    $ajax_handler->add_response_data('success_message', $success_message);
                    $ajax_handler->add_response_data('redirect_url', get_permalink($post_id));
                }
                break;

            case 'custom':
                $custom_url = $settings['alex_efpp_custom_redirect_url'] ?? '';
                if (!empty($custom_url) && filter_var($custom_url, FILTER_VALIDATE_URL)) {
                    $ajax_handler->add_response_data('form', true);
                    $ajax_handler->add_response_data('message', $success_message);
                    $ajax_handler->add_response_data('success_message', $success_message);
                    $ajax_handler->add_response_data('redirect_url', esc_url_raw($custom_url));
                }
                break;

            case 'none':
            default:
                $ajax_handler->add_response_data('form', true);
                $ajax_handler->add_response_data('message', $success_message);
                $ajax_handler->add_response_data('success_message', $success_message);
                break;
        }

    }

}
