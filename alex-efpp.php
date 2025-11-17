<?php
/*
Plugin Name: Alex EFPP - Elementor Form Publish Post
Description: Publishes content from the Elementor form as a post or CPT.
Version: 1.0.3.2
Author: Alex Scar
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Alex_EFPP {

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_action']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_user_action']);
        add_action('elementor_pro/forms/fields/register', [$this, 'register_efpp_form_fields']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('elementor_pro/forms/render_form', [$this, 'render_efpp_messages_div'], 100, 2);

        // Ukryj domyślny komunikat Elementora przy akcji EFPP
        add_filter('elementor_pro/forms/show_message', function($show, $ajax_handler) {
            $settings = $ajax_handler->get_settings();
            if (in_array('alex_efpp', $settings['submit_actions'] ?? [])) {
                return false;
            }
            return $show;
        }, 10, 2);

        // Wewnętrzne pliki
        require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-icons-extension.php';
        require_once plugin_dir_path(__FILE__) . 'includes/efpp-style-controls.php';
    }

    public function load_textdomain() {
        load_plugin_textdomain('alex-efpp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_frontend_assets() {
        // CSS (zabezpieczenie przed wielokrotnym ładowaniem)
        if ( ! wp_style_is('alex-efpp-frontend', 'enqueued') ) {
            wp_enqueue_style(
                'alex-efpp-frontend',
                plugin_dir_url(__FILE__) . 'assets/CSS/efpp-frontend.css',
                [],
                '1.0'
            );
        }

        // JS
        if ( ! wp_script_is('alex-efpp-frontend', 'enqueued') ) {
            wp_enqueue_script(
                'alex-efpp-frontend',
                plugin_dir_url(__FILE__) . 'assets/js/efpp-frontend.js',
                ['jquery'],
                time(), // cache-busting
                true
            );
        }

        // Osobny skrypt do wiadomości (jeśli nadal potrzebny)
        wp_enqueue_script(
            'alex-efpp-messages',
            plugin_dir_url(__FILE__) . 'assets/js/efpp-messages.js',
            ['jquery'],
            '1.0',
            true
        );
    }

    public function enqueue_editor_scripts() {
        wp_enqueue_script(
            'alex-efpp-editor',
            plugin_dir_url(__FILE__) . 'assets/editor.js',
            [],
            '1.0',
            true
        );
        
        // Lokalizacja dla skryptów edytora
        wp_localize_script('alex-efpp-editor', 'AlexEFPP', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alex_efpp_ajax'),
        ]);
        
        // Dodatkowe skrypty edytora
        wp_enqueue_script(
            'alex-efpp-editor-taxonomy',
            plugin_dir_url(__FILE__) . 'assets/editor-taxonomy.js',
            ['jquery'],
            '1.0',
            true
        );
        
        wp_enqueue_script(
            'alex-efpp-editor-dynamic-choose',
            plugin_dir_url(__FILE__) . 'assets/editor-dynamic-choose.js',
            ['jquery'],
            '1.0',
            true
        );
        
        wp_enqueue_script(
            'alex-efpp-admin-hint',
            plugin_dir_url(__FILE__) . 'assets/admin-hint.js',
            ['jquery'],
            '1.0',
            true
        );
        
        // Lokalizacja dla wszystkich skryptów edytora
        wp_localize_script('alex-efpp-editor-taxonomy', 'AlexEFPP', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alex_efpp_ajax'),
        ]);
        
        wp_localize_script('alex-efpp-editor-dynamic-choose', 'AlexEFPP', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alex_efpp_ajax'),
        ]);
    }

    public function render_efpp_messages_div() {
        echo '<div class="efpp-messages"></div>';
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
        // Dynamic Taxonomy
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-taxonomy.php';
        if (class_exists('\Taxonomy_Terms_Field')) {
            $fields_manager->register(new \Taxonomy_Terms_Field());
        }

        // Featured Image
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-featured-image.php';
        if (class_exists('\EFPP_Featured_Image_Field')) {
            $fields_manager->register(new \EFPP_Featured_Image_Field());
        }

        // Dynamic Choose
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-choose.php';
        if (class_exists('\Dynamic_Choose_Field')) {
            $fields_manager->register(new \Dynamic_Choose_Field());
        }
    }
}

new Alex_EFPP();
