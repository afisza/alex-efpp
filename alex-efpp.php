<?php
/*
Plugin Name: Alex EFPP
Description: Publikuje treści z formularza Elementor jako wpis lub CPT.
Version: 1.0.2.1
Author: Alex Scar
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Alex_EFPP {

    public function __construct() {
        // Rejestracja akcji formularza: publikacja posta
        add_action('elementor_pro/forms/actions/register', [$this, 'register_action']);

        // Rejestracja akcji formularza: rejestracja / aktualizacja użytkownika
        add_action('elementor_pro/forms/actions/register', [$this, 'register_user_action']);

        // Dodanie własnych pól formularza do Elementora
        add_action('elementor_pro/forms/fields/register', [$this, 'register_efpp_form_fields']);

        // Załaduj JS w edytorze
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_hint']);

        // Załaduj CSS na froncie
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);

        // AJAX
        require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
    }

    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'alex-efpp-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/efpp-frontend.css',
            [],
            '1.0'
        );
    }

    public function enqueue_editor_hint() {
        wp_enqueue_script(
            'alex-efpp-admin-hint',
            plugin_dir_url(__FILE__) . 'assets/admin-hint.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_enqueue_script(
            'alex-efpp-editor',
            plugin_dir_url(__FILE__) . 'assets/editor-taxonomy.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('alex-efpp-editor', 'AlexEFPP', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('alex_efpp_taxonomy_filter'),
        ]);

        wp_enqueue_script(
            'alex-efpp-dynamic-choose-editor',
            plugin_dir_url(__FILE__) . 'assets/editor-dynamic-choose.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('alex-efpp-dynamic-choose-editor', 'AlexEFPP', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('alex_efpp_dynamic_fields'),
        ]);
    }

    public function register_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-form-action-post.php';
        $actions->register(new \Alex_EFPP_Form_Action_Post());
    }

    public function register_user_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/form-action-register-user.php';
        $actions->register(new \EFPP_Form_Action_Register_User());
    }

    public function register_efpp_form_fields($fields_manager) {
        // Taxonomy Terms field
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-taxonomy.php';
        if (class_exists('\Taxonomy_Terms_Field')) {
            $fields_manager->register(new \Taxonomy_Terms_Field());
        }

        // Featured Image field
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-featured-image.php';
        if (class_exists('\EFPP_Featured_Image_Field')) {
            $fields_manager->register(new \EFPP_Featured_Image_Field());
        }

        // Dynamic Choose field
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-choose.php';
        if (class_exists('\Dynamic_Choose_Field')) {
            $fields_manager->register(new \Dynamic_Choose_Field());
        }
    }
}

new Alex_EFPP();
