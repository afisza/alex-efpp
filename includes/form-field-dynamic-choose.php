<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

class Dynamic_Choose_Field extends Field_Base {

    public function get_type() {
        return 'dynamic_choose';
    }

    public function get_name() {
        return 'Dynamic Choose';
    }

    public function render($item, $item_index, $form) {
        $source_type = $item['source_type'] ?? 'acf';
        $field_name = $item['field_name'] ?? '';
        $input_type = $item['input_type'] ?? 'select';

        // TODO: logika pobierania wartości z metadanych ACF/JetEngine
        $values = [];

        if ($source_type === 'acf' && function_exists('get_field_object')) {
            $field_object = get_field_object($field_name);
            if ($field_object && !empty($field_object['choices'])) {
                $values = $field_object['choices'];
            }
        }

        if ($source_type === 'jetengine' && function_exists('jet_engine')) {
            $meta_boxes = jet_engine()->meta_boxes->meta_boxes ?? [];
            foreach ($meta_boxes as $group) {
                if (isset($group['fields'])) {
                    foreach ($group['fields'] as $field) {
                        if ($field['name'] === $field_name && isset($field['options'])) {
                            $values = $field['options'];
                            break 2;
                        }
                    }
                }
            }

            // Dodatkowo: CPT field fallback
            $post_types = jet_engine()->post_types->post_types ?? [];
            foreach ($post_types as $type) {
                if (isset($type['fields'])) {
                    foreach ($type['fields'] as $field) {
                        if ($field['name'] === $field_name && isset($field['options'])) {
                            $values = $field['options'];
                            break 2;
                        }
                    }
                }
            }
        }

        echo '<label for="form-field-' . esc_attr($field_name) . '" class="elementor-field-label">' . esc_html($item['title'] ?? 'Dynamic Field') . '</label>';
        echo '<div class="elementor-field elementor-select-wrapper">';

        if ($input_type === 'select') {
            echo '<select 
    name="form_fields[' . esc_attr($field_name) . ']" 
    id="form-field-' . esc_attr($field_name) . '" 
    class="elementor-field-textual elementor-select efpp-dynamic-select" 
    data-field-type="efpp-dynamic-choose">
';

            echo '<option value="">Wybierz</option>';
            foreach ($values as $val => $label) {
                echo '<option value="' . esc_attr($val) . '">' . esc_html($label) . '</option>';
            }
            echo '</select>';
        }

        echo '</div>';
    }

    public function update_controls($widget) {
        $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) return;
        error_log('EFPP: update_controls działa.');

        $field_controls = [
            'source_type' => [
                'name' => 'source_type',
                'label' => esc_html__('Source', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'acf' => 'ACF',
                    'jetengine' => 'JetEngine',
                ],
                'default' => 'acf',
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],

            'field_group' => [
                'name' => 'field_group',
                'label' => esc_html__('Field Group / MetaBox', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'label_block' => true,
                'multiple' => false,
                'tabs_wrapper' => 'form_fields_tabs',
                'inner_tab' => 'form_fields_content_tab',
                'tab' => 'content',
            ],

            'field_name' => [
                'name' => 'field_name',
                'label' => esc_html__('Field name', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'e.g. car_brand',
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'input_type' => [
                'name' => 'input_type',
                'label' => esc_html__('Input Type', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'select' => 'Select',
                    'radio' => 'Radio',
                    'checkbox' => 'Checkbox',
                ],
                'default' => 'select',
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

    public function get_value($item, $submitted_data) {
        $field_name = $item['field_name'] ?? '';
        return $submitted_data[$field_name] ?? '';
    }
}
