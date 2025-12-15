<?php
/*
Plugin Name: Alex EFPP - Elementor Form Publish Post/Register User
Description: Publishes content from the Elementor form as a post or CPT. Includes user registration, login, logout, and password reset actions.
Version: 1.0.3.8.6
Author: Alex Shram
Plugin URI: https://github.com/afisza/alex-efpp
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Alex_EFPP {

    /**
     * Instancja GitHub Updatera
     */
    private $github_updater;

    /**
     * Wersja pluginu
     */
    private $version;

    public function __construct() {
        // Pobierz wersję z nagłówka pluginu
        $plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], 'plugin');
        $this->version = $plugin_data['Version'] ?? '1.0.3.8.6';
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
        add_action('elementor/frontend/widget/before_render', [$this, 'add_remember_me_data_to_form'], 10, 1);
        add_action('elementor/frontend/widget/before_render', [$this, 'add_form_switch_data_to_form'], 10, 1);
        add_action('wp_footer', [$this, 'add_remember_me_script']);
        add_action('wp_footer', [$this, 'add_form_switch_script']);
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
        $plugin_path = plugin_dir_path(__FILE__);
        
        $required_files = [
            'includes/ajax.php',
            'includes/form-field-icons-extension.php',
            'includes/efpp-style-controls.php',
            'includes/form-buttons-logout-control.php',
            'includes/class-github-updater.php',
        ];
        
        foreach ($required_files as $file) {
            $file_path = $plugin_path . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                // Log error but don't break the plugin
                error_log(sprintf(
                    'Alex EFPP: Required file not found: %s (Full path: %s)',
                    $file,
                    $file_path
                ));
            }
        }
        
        // GitHub Updater - only initialize if class exists
        if (class_exists('Alex_EFPP_Github_Updater')) {
            $this->init_github_updater();
        }
        
        // Add plugin action links (Check for updates)
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_action_links']);
        
        // Handle manual update check
        add_action('admin_post_alex_efpp_check_updates', [$this, 'handle_manual_update_check']);
        
        // Show update check notice
        add_action('admin_notices', [$this, 'show_update_check_notice']);
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
                plugin_dir_url(__FILE__) . 'assets/css/efpp-frontend.css',
                [],
                $this->version
            );
        }

        // JS
        if ( ! wp_script_is('alex-efpp-frontend', 'enqueued') ) {
            wp_enqueue_script(
                'alex-efpp-frontend',
                plugin_dir_url(__FILE__) . 'assets/js/efpp-frontend.js',
                ['jquery'],
                $this->version,
                true
            );
        }

        // Osobny skrypt do wiadomości (jeśli nadal potrzebny)
        wp_enqueue_script(
            'alex-efpp-messages',
            plugin_dir_url(__FILE__) . 'assets/js/efpp-messages.js',
            ['jquery'],
            $this->version,
            true
        );
    }

    public function enqueue_editor_scripts() {
        wp_enqueue_script(
            'alex-efpp-editor',
            plugin_dir_url(__FILE__) . 'assets/editor.js',
            [],
            $this->version,
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
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'alex-efpp-editor-dynamic-choose',
            plugin_dir_url(__FILE__) . 'assets/editor-dynamic-choose.js',
            ['jquery'],
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'alex-efpp-admin-hint',
            plugin_dir_url(__FILE__) . 'assets/admin-hint.js',
            ['jquery'],
            $this->version,
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

    /**
     * Add data attribute to form widget for Remember Me checkbox
     */
    public function add_remember_me_data_to_form($widget) {
        // Only process form widgets
        if ($widget->get_name() !== 'form') {
            return;
        }
        
        $settings = $widget->get_settings_for_display();
        
        // Check if EFPP Login action is enabled
        $submit_actions = $settings['submit_actions'] ?? [];
        $has_efpp_login = in_array('efpp_login', $submit_actions);
        
        // Check if Remember me checkbox should be shown
        $show_remember_me = $has_efpp_login && !empty($settings['efpp_login_remember']);
        
        // Add data attribute to widget
        if ($show_remember_me) {
            $widget->add_render_attribute('_wrapper', 'data-efpp-show-remember-me', '1');
        }
    }

    /**
     * Add Remember Me script
     */
    public function add_remember_me_script() {
        // Only load if Elementor is active
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        wp_enqueue_script(
            'efpp-remember-me',
            plugin_dir_url(__FILE__) . 'assets/js/efpp-remember-me.js',
            ['jquery', 'elementor-frontend'],
            $this->version,
            true
        );
        
        // Pass translated text to JavaScript
        wp_localize_script('efpp-remember-me', 'efppRememberMe', [
            'text' => __('Zapamiętaj mnie', 'alex-efpp'),
        ]);
    }

    /**
     * Add data attributes for form switching
     */
    public function add_form_switch_data_to_form($widget) {
        // Only process form widgets
        if ($widget->get_name() !== 'form') {
            return;
        }
        
        $settings = $widget->get_settings_for_display();
        
        // Check for Login form with reset password link
        $submit_actions = $settings['submit_actions'] ?? [];
        if (in_array('efpp_login', $submit_actions)) {
            $show_reset_link = !empty($settings['efpp_login_show_reset_link']);
            if ($show_reset_link) {
                $login_form_id = $settings['efpp_login_form_id'] ?? '';
                $reset_form_id = $settings['efpp_reset_password_form_id'] ?? '';
                $link_text = $settings['efpp_reset_password_link_text'] ?? __('Zapomniałeś hasła?', 'alex-efpp');
                
                if (!empty($login_form_id) && !empty($reset_form_id)) {
                    $widget->add_render_attribute('_wrapper', 'data-efpp-login-form-id', esc_attr($login_form_id));
                    $widget->add_render_attribute('_wrapper', 'data-efpp-reset-form-id', esc_attr($reset_form_id));
                    $widget->add_render_attribute('_wrapper', 'data-efpp-reset-link-text', esc_attr($link_text));
                    $widget->add_render_attribute('_wrapper', 'data-efpp-show-reset-link', '1');
                }
            }
        }
        
        // Check for Reset Password form with login link
        if (in_array('efpp_reset_password', $submit_actions)) {
            // Check both 'yes' string and boolean true
            $show_login_link = !empty($settings['efpp_reset_show_login_link']) && 
                               ($settings['efpp_reset_show_login_link'] === 'yes' || $settings['efpp_reset_show_login_link'] === true);
            
            if ($show_login_link) {
                $login_form_id = $settings['efpp_reset_login_form_id'] ?? '';
                $reset_form_id = $settings['efpp_reset_password_form_id'] ?? '';
                $link_text = $settings['efpp_reset_login_link_text'] ?? __('Wróć do logowania', 'alex-efpp');
                
                // Always try to get reset form ID from form settings (this is the current form's ID)
                // Priority: efpp_reset_password_form_id > form_id > form_name
                if (empty($reset_form_id)) {
                    $reset_form_id = $settings['form_id'] ?? $settings['form_name'] ?? '';
                }
                
                // Add attributes if we have login_form_id (required)
                if (!empty($login_form_id)) {
                    $widget->add_render_attribute('_wrapper', 'data-efpp-login-form-id', esc_attr($login_form_id));
                    $widget->add_render_attribute('_wrapper', 'data-efpp-login-link-text', esc_attr($link_text));
                    $widget->add_render_attribute('_wrapper', 'data-efpp-show-login-link', '1');
                    
                    // Always add reset_form_id - use form_id/form_name if efpp_reset_password_form_id is empty
                    if (!empty($reset_form_id)) {
                        $widget->add_render_attribute('_wrapper', 'data-efpp-reset-form-id', esc_attr($reset_form_id));
                    }
                }
            }
        }
    }

    /**
     * Add Form Switch script
     */
    public function add_form_switch_script() {
        // Only load if Elementor is active
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        wp_enqueue_script(
            'efpp-form-switch',
            plugin_dir_url(__FILE__) . 'assets/js/efpp-form-switch.js',
            ['jquery', 'elementor-frontend'],
            $this->version,
            true
        );
    }

    public function enqueue_admin_scripts($hook) {
        // Załaduj skrypt tylko na stronie z wtyczkami
        if ($hook === 'plugins.php') {
            wp_enqueue_script(
                'alex-efpp-check-update',
                plugin_dir_url(__FILE__) . 'assets/js/check-update.js',
                ['jquery'],
                $this->version,
                true
            );
            
            // Lokalizacja dla skryptu (dodaje zmienną ajaxurl)
            wp_localize_script('alex-efpp-check-update', 'alexEfppAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);
        }
    }

    public function register_action($actions) {
        $file_path = plugin_dir_path(__FILE__) . 'includes/class-form-action-post.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\Alex_EFPP_Form_Action_Post')) {
                $actions->register(new \Alex_EFPP_Form_Action_Post());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }
    }

    public function register_user_action($actions) {
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-action-register-user.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\EFPP_Form_Action_Register_User')) {
                $actions->register(new \EFPP_Form_Action_Register_User());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }
    }

    public function register_login_action($actions) {
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-action-login.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\EFPP_Form_Action_Login')) {
                $actions->register(new \EFPP_Form_Action_Login());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }
    }

    public function register_logout_action($actions) {
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-action-logout.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\EFPP_Form_Action_Logout')) {
                $actions->register(new \EFPP_Form_Action_Logout());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }
    }

    public function register_reset_password_action($actions) {
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-action-reset-password.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\EFPP_Form_Action_Reset_Password')) {
                $actions->register(new \EFPP_Form_Action_Reset_Password());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }
    }

    public function register_efpp_form_fields($fields_manager) {
        // Dynamic Taxonomy
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-taxonomy.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\Taxonomy_Terms_Field')) {
                $fields_manager->register(new \Taxonomy_Terms_Field());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }

        // Featured Image
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-field-featured-image.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\EFPP_Featured_Image_Field')) {
                $fields_manager->register(new \EFPP_Featured_Image_Field());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }

        // Logout Link - zakomentowane na razie
        // $file_path = plugin_dir_path(__FILE__) . 'includes/form-field-logout-link.php';
        // if (file_exists($file_path)) {
        //     require_once $file_path;
        //     if (class_exists('\EFPP_Logout_Link_Field')) {
        //         $fields_manager->register(new \EFPP_Logout_Link_Field());
        //     }
        // }

        // Dynamic Choose
        $file_path = plugin_dir_path(__FILE__) . 'includes/form-field-dynamic-choose.php';
        if (file_exists($file_path)) {
            require_once $file_path;
            if (class_exists('\Dynamic_Choose_Field')) {
                $fields_manager->register(new \Dynamic_Choose_Field());
            }
        } else {
            error_log('Alex EFPP: File not found: ' . $file_path);
        }
    }
    
    /**
     * Add plugin action links
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function add_plugin_action_links($links) {
        $check_updates_link = sprintf(
            '<a href="%s">%s</a>',
            wp_nonce_url(
                admin_url('admin-post.php?action=alex_efpp_check_updates'),
                'alex_efpp_check_updates',
                'nonce'
            ),
            __('Check for updates', 'alex-efpp')
        );

        array_unshift($links, $check_updates_link);

        return $links;
    }

    /**
     * Handle manual update check
     */
    public function handle_manual_update_check() {
        // Verify nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'alex_efpp_check_updates')) {
            wp_die(__('Security check failed', 'alex-efpp'));
        }

        // Check user capabilities
        if (!current_user_can('update_plugins')) {
            wp_die(__('You do not have permission to update plugins.', 'alex-efpp'));
        }

        // Clear the update cache
        delete_transient('alex_efpp_github_version');
        
        // Clear WordPress update cache
        delete_site_transient('update_plugins');
        wp_clean_plugins_cache();

        // Redirect back to plugins page with success message
        $redirect_url = add_query_arg(
            [
                'alex_efpp_update_check' => 'success',
            ],
            admin_url('plugins.php')
        );

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Show update check notice
     */
    public function show_update_check_notice() {
        // Only show if we're on plugins.php
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        // Check if update check was successful
        if (isset($_GET['alex_efpp_update_check']) && 'success' === $_GET['alex_efpp_update_check']) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong><?php esc_html_e('Alex EFPP:', 'alex-efpp'); ?></strong>
                    <?php esc_html_e('Update check completed. If an update is available, you will see it above.', 'alex-efpp'); ?>
                </p>
            </div>
            <?php
        }
    }
}

new Alex_EFPP();
