<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

class Taxonomy_Terms_Field extends Field_Base {

    public function get_type() {
        return 'taxonomy_terms';
    }

    public function get_name() {
        return 'Taxonomy Terms';
    }

    public function render($item, $item_index, $form) {
        $taxonomy = $item['taxonomy_terms_type'] ?? 'category';
        $display_type = $item['taxonomy_terms_display'] ?? 'select';
        $multiple = in_array($display_type, ['multiselect', 'checkbox']);

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            echo '<div class="elementor-alert elementor-alert-warning">No terms found for taxonomy: ' . esc_html($taxonomy) . '</div>';
            return;
        }

        $field_name = $form->get_attribute_name($item, $item_index);

        $label = $item['field_label'] ?? '';
        $required = !empty($item['required']) && $item['required'] === 'yes';
        $default = $item['field_default'] ?? '';
        $default_values = array_map('trim', explode(',', $default));

        echo '<div class="elementor-field-subgroup elementor-dynamic-taxonomy">';

        switch ($display_type) {
            case 'select':
            case 'multiselect':
                echo '<select name="' . esc_attr($field_name) . ($multiple ? '[]' : '') . '" ' . ($multiple ? 'multiple' : '') . '>';
                foreach ($terms as $term) {
                    $selected = in_array($term->slug, $default_values) ? 'selected' : '';
                    echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                }
                echo '</select>';
                break;

            case 'radio':
                foreach ($terms as $term) {
                    $checked = in_array($term->slug, $default_values) ? 'checked' : '';
                    echo '<label><input type="radio" name="' . esc_attr($field_name) . '" value="' . esc_attr($term->slug) . '" ' . $checked . '> ' . esc_html($term->name) . '</label><br>';
                }
                break;

            case 'checkbox':
                foreach ($terms as $term) {
                    $checked = in_array($term->slug, $default_values) ? 'checked' : '';
                    echo '<label><input type="checkbox" name="' . esc_attr($field_name) . '[]" value="' . esc_attr($term->slug) . '" ' . $checked . '> ' . esc_html($term->name) . '</label><br>';
                }
                break;
        }

        echo '</div>';
    }

    public function get_controls() {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $taxonomy_options = [];

        foreach ($taxonomies as $key => $obj) {
            $taxonomy_options[$key] = $obj->label;
        }

        return [
            'field_label' => [
                'label' => __('Label', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Select Terms', 'alex-efpp'),
            ],
            'required' => [
                'label' => __('Required', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'alex-efpp'),
                'label_off' => __('No', 'alex-efpp'),
                'return_value' => 'yes',
            ],
            'taxonomy_terms_type' => [
                'label' => __('Taxonomy', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $taxonomy_options,
                'default' => 'category',
            ],
            'taxonomy_terms_display' => [
                'label' => __('Display As', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'select' => 'Select',
                    'multiselect' => 'Multi-select',
                    'radio' => 'Radio buttons',
                    'checkbox' => 'Checkboxes',
                ],
                'default' => 'select',
            ],
            'field_default' => [
                'label' => __('Default Value (comma separated slugs)', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ],
        ];
    }

}
