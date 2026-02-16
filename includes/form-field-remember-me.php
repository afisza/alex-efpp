<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

if (!defined('ABSPATH')) exit;

if (!class_exists('EFPP_Remember_Me_Field')) {

    class EFPP_Remember_Me_Field extends Field_Base {

        public function get_type() {
            return 'efpp_remember_me';
        }

        public function get_name() {
            return __('EFPP Remember Me', 'alex-efpp');
        }

        public function update_controls($widget) {
            $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
            if (is_wp_error($control_data)) {
                return;
            }
            $field_controls = [
                'efpp_remember_me_label' => [
                    'name' => 'efpp_remember_me_label',
                    'label' => __('Label', 'alex-efpp'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('Remember me', 'alex-efpp'),
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
            $show_remember = !empty($settings['efpp_login_remember']);
            if (!$show_remember) {
                return;
            }

            $field_name = 'efpp_remember_me';
            $field_id = 'form-field-' . esc_attr($field_name) . '-' . $item_index;
            $label_text = !empty($item['efpp_remember_me_label']) ? $item['efpp_remember_me_label'] : __('Remember me', 'alex-efpp');

            ?>
            <div class="elementor-field-type-checkbox elementor-field-group elementor-column elementor-field-group-efpp-remember-me elementor-col-100 elementor-field-group-<?php echo esc_attr($field_name); ?>">
                <div class="elementor-field elementor-field-checkbox">
                    <label for="<?php echo esc_attr($field_id); ?>" class="elementor-field-label" style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="<?php echo esc_attr($field_id); ?>" name="form_fields[<?php echo esc_attr($field_name); ?>]" value="1" class="elementor-field efpp-remember-me-checkbox" style="margin-right: 0.5em; width: auto; height: auto;">
                        <span><?php echo esc_html($label_text); ?></span>
                    </label>
                </div>
            </div>
            <?php
        }

        public function get_default_settings() {
            return [
                'custom_id' => 'efpp_remember_me',
                'input_type' => 'checkbox',
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
            $text = __('Remember me', 'alex-efpp');
            ?>
            <script>
                jQuery(document).ready(function() {
                    if (typeof elementor === 'undefined' || !elementor.hooks) return;
                    var labelText = <?php echo json_encode($text); ?>;
                    elementor.hooks.addFilter('elementor_pro/forms/content_template/field/efpp_remember_me', function(inputField, item, i) {
                        var itemLabel = (item && item.efpp_remember_me_label) ? item.efpp_remember_me_label : labelText;
                        return '<div class="elementor-field-type-checkbox elementor-field-group elementor-column elementor-field-group-efpp-remember-me elementor-col-100">' +
                            '<div class="elementor-field elementor-field-checkbox">' +
                            '<label class="elementor-field-label" style="display:flex;align-items:center;cursor:pointer;">' +
                            '<input type="checkbox" class="elementor-field efpp-remember-me-checkbox" style="margin-right:0.5em;width:auto;height:auto;" disabled>' +
                            '<span>' + (itemLabel || labelText) + '</span>' +
                            '</label></div></div>';
                    }, 10, 3);
                });
            </script>
            <?php
        }
    }
}
