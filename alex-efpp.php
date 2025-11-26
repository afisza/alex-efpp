<?php
/*
Plugin Name: Alex EFPP - Elementor Form Publish Post/Register User
Description: Publishes content from the Elementor form as a post or CPT. Includes user registration, login, logout, and password reset actions.
Version: 1.0.3.8.3
Author: Alex Shram
Plugin URI: https://github.com/afisza/alex-efpp
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Alex_EFPP {

    /**
     * Instancja GitHub Updatera
     */
    private $github_updater;

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_action']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_user_action']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_login_action']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_logout_action']);
        add_action('elementor_pro/forms/actions/register', [$this, 'register_reset_password_action']);
        add_action('elementor_pro/forms/fields/register', [$this, 'register_efpp_form_fields']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('elementor_pro/forms/render_form', [$this, 'render_efpp_messages_div'], 100, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

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
        require_once plugin_dir_path(__FILE__) . 'includes/form-buttons-logout-control.php';
        
        // GitHub Updater
        require_once plugin_dir_path(__FILE__) . 'includes/class-github-updater.php';
        $this->init_github_updater();
    }
    
    /**
     * Inicjalizuje GitHub Updater
     */
    private function init_github_updater() {
        // Updater automatycznie pobierze URL z stałej ALEX_EFPP_GITHUB_REPO_URL
        // lub z filtra 'alex_efpp_github_repo_url'
        // Przykład użycia w functions.php:
        // define('ALEX_EFPP_GITHUB_REPO_URL', 'https://github.com/username/repo-name');
        // lub
        // add_filter('alex_efpp_github_repo_url', function() {
        //     return 'https://github.com/username/repo-name';
        // });
        $this->github_updater = new Alex_EFPP_GitHub_Updater(__FILE__);
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

    public function enqueue_admin_scripts($hook) {
        // Załaduj skrypt tylko na stronie z wtyczkami
        if ($hook === 'plugins.php') {
            wp_enqueue_script(
                'alex-efpp-check-update',
                plugin_dir_url(__FILE__) . 'assets/js/check-update.js',
                ['jquery'],
                '1.0',
                true
            );
            
            // Lokalizacja dla skryptu (dodaje zmienną ajaxurl)
            wp_localize_script('alex-efpp-check-update', 'alexEfppAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);
        }
    }

    public function register_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-form-action-post.php';
        $actions->register(new \Alex_EFPP_Form_Action_Post());
    }

    public function register_user_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/form-action-register-user.php';
        $actions->register(new \EFPP_Form_Action_Register_User());
    }

    public function register_login_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/form-action-login.php';
        $actions->register(new \EFPP_Form_Action_Login());
    }

    public function register_logout_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/form-action-logout.php';
        $actions->register(new \EFPP_Form_Action_Logout());
    }

    public function register_reset_password_action($actions) {
        require_once plugin_dir_path(__FILE__) . 'includes/form-action-reset-password.php';
        $actions->register(new \EFPP_Form_Action_Reset_Password());
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

        // Logout Link - zakomentowane na razie
        // require_once plugin_dir_path(__FILE__) . 'includes/form-field-logout-link.php';
        // if (class_exists('\EFPP_Logout_Link_Field')) {
        //     $fields_manager->register(new \EFPP_Logout_Link_Field());
        // }

        // Dynamic Choose
        require_once plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-choose.php';
        if (class_exists('\Dynamic_Choose_Field')) {
            $fields_manager->register(new \Dynamic_Choose_Field());
        }
    }
}

new Alex_EFPP();
