<?php
/*
Plugin Name: Alex EFPP
Description: Publikuje treści z formularza Elementor jako wpis lub CPT.
Version: 1.0.1
Author: Alex Scar
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Alex_EFPP {
    public function __construct() {
        add_action('elementor_pro/forms/actions/register', [$this, 'register_action']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_hint']);
    }

    public function register_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-form-action-post.php';
        $actions->register(new \Alex_EFPP_Form_Action_Post());
    }

    public function enqueue_editor_hint() {
        wp_enqueue_script(
            'alex-efpp-admin-hint',
            plugin_dir_url(__FILE__) . 'assets/admin-hint.js',
            ['jquery'],
            '1.0',
            true
        );
    }
}

new Alex_EFPP();

