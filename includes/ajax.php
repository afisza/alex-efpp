<?php

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