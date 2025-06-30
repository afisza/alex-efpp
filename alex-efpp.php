<?php
/*
Plugin Name: Alex EFPP - Elementor Form Publish Post
Description: Publishes content from the Elementor form as an post or CPT.
Version: 1.0.3.1
Author: Alex Scar
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Alex_EFPP {

    public function load_textdomain() {
        load_plugin_textdomain('alex-efpp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function __construct() {
		add_action('plugins_loaded', [$this, 'load_textdomain']);
		add_action('elementor_pro/forms/actions/register', [$this, 'register_action']);
		add_action('elementor_pro/forms/actions/register', [$this, 'register_user_action']);
		add_action('elementor_pro/forms/fields/register', [$this, 'register_efpp_form_fields']);
		add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
		add_action('elementor_pro/forms/render_form', [$this, 'render_efpp_messages_div'], 100, 2);

		// 🔽 TU JEST NASZ KOD:
		add_filter('elementor_pro/forms/show_message', function($show, $ajax_handler) {
			$settings = $ajax_handler->get_settings();
			if (in_array('alex_efpp', $settings['submit_actions'] ?? [])) {
				return false;
			}
			return $show;
		}, 10, 2);

		require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
		require_once plugin_dir_path(__FILE__) . 'includes/form-field-icons-extension.php';
        require_once plugin_dir_path(__FILE__) . 'includes/efpp-style-controls.php';

	}

	public function render_efpp_messages_div_global() {
		echo '<div class="efpp-messages"></div>';
	}


    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'alex-efpp-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/efpp-frontend.css',
            [],
            '1.0'
        );
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
