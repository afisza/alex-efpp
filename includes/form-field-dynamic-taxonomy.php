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
        // $taxonomy = $item['taxonomy_terms_type'] ?? 'category';
        // $display_type = $item['taxonomy_terms_display'] ?? 'select';
        // $multiple = in_array($display_type, ['multiselect', 'checkbox']);

        // $terms = get_terms([
        //     'taxonomy' => $taxonomy,
        //     'hide_empty' => false,
        // ]);

        // if (is_wp_error($terms) || empty($terms)) {
        //     echo '<div class="elementor-alert elementor-alert-warning">No terms found for taxonomy: ' . esc_html($taxonomy) . '</div>';
        //     return;
        // }

        // $field_name = $form->get_attribute_name($item, $item_index);

        // $label = $item['field_label'] ?? '';
        // $required = !empty($item['required']) && $item['required'] === 'yes';
        // $default = $item['field_default'] ?? '';
        // $default_values = array_map('trim', explode(',', $default));

        // echo '<div class="elementor-field-subgroup elementor-dynamic-taxonomy">';

        // switch ($display_type) {
        //     case 'select':
        //     case 'multiselect':
        //         echo '<select name="' . esc_attr($field_name) . ($multiple ? '[]' : '') . '" ' . ($multiple ? 'multiple' : '') . '>';
        //         foreach ($terms as $term) {
        //             $selected = in_array($term->slug, $default_values) ? 'selected' : '';
        //             echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        //         }
        //         echo '</select>';
        //         break;

        //     case 'radio':
        //         foreach ($terms as $term) {
        //             $checked = in_array($term->slug, $default_values) ? 'checked' : '';
        //             echo '<label><input type="radio" name="' . esc_attr($field_name) . '" value="' . esc_attr($term->slug) . '" ' . $checked . '> ' . esc_html($term->name) . '</label><br>';
        //         }
        //         break;

        //     case 'checkbox':
        //         foreach ($terms as $term) {
        //             $checked = in_array($term->slug, $default_values) ? 'checked' : '';
        //             echo '<label><input type="checkbox" name="' . esc_attr($field_name) . '[]" value="' . esc_attr($term->slug) . '" ' . $checked . '> ' . esc_html($term->name) . '</label><br>';
        //         }
        //         break;
        // }

        // echo '</div>';
    }

    public function update_controls( $widget ) {
		$control_data = Elementor\Plugin::$instance->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

		if ( is_wp_error( $control_data ) ) {
			return;
		}

        $args = array(
            'public'   => true,
            // '_builtin' => true
        ); 

        $output = 'objects'; // or names
        $operator = 'and'; // 'and' or 'or'
        $taxonomies = get_taxonomies( $args, $output, $operator ); 

        

        $taxonomy_options = array();

        foreach( $taxonomies as $taxonomy ) {
            $taxonomy_options[ $taxonomy->name ] = $taxonomy->labels->name;
        }

        error_log( "taxonomy_options\n" . print_r( $taxonomy_options, true ) . "\n" );

        $field_controls = [
            'efpp_taxonomy' => [
                'name' => 'efpp_taxonomy',
                'label' => esc_html__( 'Taxonomy', 'alex-efpp' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                // 'default' => esc_html__( 'Select Terms', 'alex-efpp' ),
                'options' => $taxonomy_options,
                'condition' => [
					'field_type' => $this->get_type(),
				],
            ],
        ];

        $control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );
		$widget->update_control( 'form_fields', $control_data );
    }

}
