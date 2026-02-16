<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

if (!defined('ABSPATH')) exit;

if (!class_exists('EFPP_Reset_Password_Link_Field')) {

    class EFPP_Reset_Password_Link_Field extends Field_Base {

        public function get_type() {
            return 'efpp_reset_password_link';
        }

        public function get_name() {
            return __('EFPP Forgot Password Link', 'alex-efpp');
        }

        public function update_controls($widget) {
            $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
            if (is_wp_error($control_data)) {
                return;
            }
            $field_controls = [
                'efpp_reset_link_text' => [
                    'name' => 'efpp_reset_link_text',
                    'label' => __('Link Text', 'alex-efpp'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('Lost password?', 'alex-efpp'),
                    'condition' => [
                        'field_type' => $this->get_type(),
                    ],
                    'tab' => 'content',
                    'inner_tab' => 'form_fields_content_tab',
                    'tabs_wrapper' => 'form_fields_tabs',
                ],
            ];
            $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
            $widget->update_control('form_fields', $control_data);
        }

        public function render($item, $item_index, $form) {
            $settings = method_exists($form, 'get_settings_for_display') ? $form->get_settings_for_display() : $form->get_settings();
            $submit_actions = $settings['submit_actions'] ?? [];
            if (!in_array('efpp_login', $submit_actions)) {
                return;
            }
            if (empty($settings['efpp_login_show_reset_link'])) {
                return;
            }
            $login_form_id = $settings['efpp_login_form_id'] ?? '';
            $reset_form_id = $settings['efpp_login_reset_password_form_id'] ?? '';
            if (empty($login_form_id) || empty($reset_form_id)) {
                return;
            }

            // Form-level "Reset Password Link Text" overrides field-level when set
            $link_text = isset($settings['efpp_reset_password_link_text']) && (string) $settings['efpp_reset_password_link_text'] !== ''
                ? $settings['efpp_reset_password_link_text']
                : (!empty($item['efpp_reset_link_text']) ? $item['efpp_reset_link_text'] : __('Lost password?', 'alex-efpp'));

            ?>
            <div class="elementor-field-type-<?php echo esc_attr($this->get_type()); ?> elementor-column elementor-col-100 elementor-field-group-<?php echo esc_attr($this->get_type()); ?> efpp-reset-link-wrapper">
                <a href="#" class="efpp-switch-to-reset-link" data-login-form-id="<?php echo esc_attr($login_form_id); ?>" data-reset-form-id="<?php echo esc_attr($reset_form_id); ?>"><?php echo esc_html($link_text); ?></a>
            </div>
            <?php
        }

        public function get_default_settings() {
            return [
                'custom_id' => 'efpp_reset_password_link',
                'input_type' => 'hidden',
            ];
        }

        public function __construct() {
            parent::__construct();
            add_action('elementor/preview/init', [$this, 'editor_preview_footer']);
        }

        public function editor_preview_footer(): void {
            add_action('wp_footer', [$this, 'content_template_script']);
        }

        public function content_template_script(): void {
            $text = __('Lost password?', 'alex-efpp');
            ?>
            <script>
                jQuery(document).ready(function() {
                    if (typeof elementor === 'undefined' || !elementor.hooks) return;
                    var defaultText = <?php echo json_encode($text); ?>;
                    elementor.hooks.addFilter('elementor_pro/forms/content_template/field/efpp_reset_password_link', function(inputField, item, i) {
                        var linkText = (item && item.efpp_reset_link_text) ? item.efpp_reset_link_text : defaultText;
                        return '<div class="elementor-field-type-efpp_reset_password_link elementor-column elementor-col-100 efpp-reset-link-wrapper">' +
                            '<a href="#" class="efpp-switch-to-reset-link">' + (linkText || defaultText) + '</a>' +
                            '</div>';
                    }, 10, 3);
                });
            </script>
            <?php
        }
    }
}
