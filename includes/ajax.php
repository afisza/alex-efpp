<?php

// === TAXONOMIES ===
add_action('wp_ajax_alex_efpp_get_taxonomies', 'alex_efpp_get_taxonomies');
add_action('wp_ajax_nopriv_alex_efpp_get_taxonomies', 'alex_efpp_get_taxonomies');

function alex_efpp_get_taxonomies() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized');
    }

    $post_type = sanitize_text_field($_POST['post_type'] ?? 'post');

    if (empty($post_type) || !post_type_exists($post_type)) {
        wp_send_json_error('Invalid post type');
    }

    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $response = ['none' => '-- Select taxonomy --'];

    foreach ($taxonomies as $slug => $taxonomy) {
        $response[$slug] = $taxonomy->labels->name;
    }

    wp_send_json_success($response);
}


// === FEATURED IMAGE UPLOAD ===
add_action('wp_ajax_efpp_upload_image', 'efpp_handle_image_upload');
add_action('wp_ajax_nopriv_efpp_upload_image', 'efpp_handle_image_upload');

function efpp_handle_image_upload() {
    check_ajax_referer('efpp_featured_image_upload');

    if (!function_exists('media_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    if (empty($_FILES['file'])) {
        wp_send_json_error(['message' => 'No file provided']);
    }

    $file = $_FILES['file'];
    $overrides = ['test_form' => false];
    $uploaded = wp_handle_upload($file, $overrides);

    if (isset($uploaded['error'])) {
        wp_send_json_error(['message' => $uploaded['error']]);
    }

    $attachment = [
        'post_mime_type' => $uploaded['type'],
        'post_title'     => sanitize_file_name($uploaded['file']),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $uploaded['file']);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    wp_send_json_success([
        'id'  => $attach_id,
        'url' => wp_get_attachment_url($attach_id),
    ]);
}


// === DYNAMIC FIELD LOADER ===
add_action('wp_ajax_alex_efpp_get_dynamic_fields', 'alex_efpp_get_dynamic_fields');

function alex_efpp_get_dynamic_fields() {
    check_ajax_referer('alex_efpp_dynamic_fields', '_ajax_nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized');
    }

    $source = sanitize_text_field($_POST['source_type'] ?? '');
    $group_key = sanitize_text_field($_POST['group_key'] ?? '');

    if (empty($source) || empty($group_key)) {
        wp_send_json_error('Missing parameters');
    }

    $fields = [];

    // ... ACF + JetEngine logika ...

    if (empty($fields)) {
        wp_send_json_error('No fields found');
    }

    wp_send_json_success($fields); // ← TO TU MA BYĆ końcem tej funkcji
}


// === LIST FIELD GROUPS (nowa funkcja!) ===
add_action('wp_ajax_alex_efpp_list_field_groups', 'alex_efpp_list_field_groups');

function alex_efpp_list_field_groups() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized');
    }

    $options = [];
    $source = sanitize_text_field($_POST['source_type'] ?? 'acf');

    // ACF
    if ($source === 'acf' && function_exists('acf_get_field_groups')) {
        foreach (acf_get_field_groups() as $group) {
            if (!empty($group['key']) && !empty($group['title'])) {
                $options[] = [
                    'id' => $group['key'],
                    'text' => '📘 ACF: ' . $group['title'],
                ];
            }
        }
    }

    // JetEngine
    if ($source === 'jetengine' && function_exists('jet_engine')) {
        $meta_boxes = jet_engine()->meta_boxes->meta_boxes ?? [];
        foreach ($meta_boxes as $id => $box) {
            $label = !empty($box['title']) ? $box['title'] : $id;
            $options[] = [
                'id' => $id,
                'text' => '🧩 JetMetaBox: ' . $label,
            ];
        }

        $post_types = jet_engine()->post_types->post_types ?? [];
        foreach ($post_types as $slug => $pt) {
            if (!empty($pt['fields'])) {
                $options[] = [
                    'id' => "jet_cpt__{$slug}",
                    'text' => '🚗 JetCPT: ' . ($pt['name'] ?? $slug),
                ];
            }
        }
    }

    error_log('EFPP: list_field_groups zakończona. Zebrano: ' . count($options));
    wp_send_json_success($options);
}


