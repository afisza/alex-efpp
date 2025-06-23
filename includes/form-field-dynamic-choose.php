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
        $source_type = $item['efpp_dc_source_type'] ?? 'acf';
        $input_type = $item['efpp_dc_input_type'] ?? 'select';


        switch ($source_type) {
            case 'acf':
                $acf_field_group_post_id = $item['efpp_dc_acf_field_group_post_id'];
                $field_name = $item['efpp_dc_acf_field_name'];

                if (function_exists('acf_get_fields')) {
                    $field_group_key = get_post_field('post_name', $acf_field_group_post_id);

                    $fields = acf_get_fields($field_group_key );

                    $options = [];

                    if ($fields) {
                        foreach ($fields as $field) {
                            if ($field['name'] === $field_name && $field['type'] === $input_type) {
                                $options = $field['choices'];
                                break;
                            }
                        }
                    }

                }
                break;

            case 'jetengine':
                $field_name = explode( '|', $item['efpp_dc_jet_engine_field'], 1 )[0];
                if (function_exists('jet_engine')) {
                    $jet_engine_field = $item['efpp_dc_jet_engine_field'];
                    $options = $this->get_jet_engine_meta_field_options( $jet_engine_field );
                }
                break;
            
            default:
                # code...
                break;
        }


        $label = trim($item['title'] ?? '');

        if ( ! empty($label) ) {
            echo '<label for="form-field-' . esc_attr($field_name) . '" class="elementor-field-label">' . esc_html($label) . '</label>';
        }
        
        switch ($input_type) {
            case 'select':
                echo '<div class="elementor-field elementor-select-wrapper">';
                    echo '<select 
                        name="form_fields[' . esc_attr($field_name) . ']" 
                        id="form-field-' . esc_attr($field_name) . '" 
                        class="elementor-field-textual elementor-select efpp-dynamic-select" 
                        data-field-type="efpp-dynamic-choose">
                    ';

                    echo '<option value="">Wybierz</option>';
                        foreach ($options as $value => $label) {
                            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                        }
                    echo '</select>';
                echo '</div>';
                break;
            
            case 'radio':
                echo '<div class="elementor-field-subgroup elementor-field-type-radio">';
                foreach ($options as $value => $label) {
                    echo '<label class="elementor-field-option">';
                    echo '<input 
                        type="radio" 
                        name="form_fields[' . esc_attr($field_name) . ']" 
                        value="' . esc_attr($value) . '" 
                        class="elementor-field elementor-radio" 
                    >';
                    echo '<span>' . esc_html($label) . '</span>';
                    echo '</label>';
                }
                echo '</div>';
                break;

            case 'checkboxes':
                echo '<div class="elementor-field-subgroup elementor-field-type-checkbox">';
                foreach ($options as $value => $label) {
                    echo '<label class="elementor-field-option">';
                    echo '<input 
                        type="checkbox" 
                        name="form_fields[' . esc_attr($field_name) . '][]" 
                        value="' . esc_attr($value) . '" 
                        class="elementor-field elementor-checkbox" 
                    >';
                    echo '<span>' . esc_html($label) . '</span>';
                    echo '</label>';
                }
                echo '</div>';
                break;
        }

    }

    public function update_controls($widget) {
        $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) return;
        error_log('EFPP: update_controls działa.');


        ob_start();
            ?>
            <#
                jQuery(document).off('change.efpp', '.efpp-remote-render select') // namespaced for safety
                    .on('change.efpp', '.efpp-remote-render select', function() {
                        elementor.getPanelView().currentPageView.model.renderRemoteServer();
                    });
            #>
            <?php
        $script = ob_get_clean();

        $field_controls = [
            'efpp_dc_source_type' => [
                'name' => 'efpp_dc_source_type',
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
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'efpp_dc_acf_field_group_post_id' => [
                'name' => 'efpp_dc_acf_field_group_post_id',
				'label'     => esc_html__( 'ACF Fields Group', 'alex-efpp' ),
				'label_block' => true,
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'options'   => ( function() {
					$options = array();

					// Define the WP_Query arguments
					$args = array(
						'post_type'      => 'acf-field-group', // Replace with your post type
						'posts_per_page' => -1,              // Get all posts
						'post_status'    => 'publish',       // Only published posts
					);

					// Initialize WP_Query
					$query = new WP_Query( $args );

					// Check if there are posts available
					if ( $query->have_posts() ) {
						// $options[''] = esc_html__( 'Select Query', 'admin' );

						// Loop through the posts and set them in the options array
						while ( $query->have_posts() ) {
							$query->the_post();
							$options[ get_the_ID() ] = get_the_title(); // Set post ID as the key and post title as the value
						}
					} else {
						// If no posts found, set a default message
						$options[''] = esc_html__( 'No Field Groups Found', 'alex-efpp' );
					}

					// Restore original Post Data
					wp_reset_postdata();

					return $options;
				} )(),
                'classes' => 'efpp-remote-render',
                'tabs_wrapper' => 'form_fields_tabs',
                'inner_tab' => 'form_fields_content_tab',
                'tab' => 'content',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'efpp_dc_source_type' => 'acf',
                ],
            ],
            'efpp_dc_acf_field_name' => [
                'name' => 'efpp_dc_acf_field_name',
                'label' => esc_html__( 'Field name', 'alex-efpp' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'e.g. car_brand',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'efpp_dc_source_type' => 'acf',
                ],
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'efpp_dc_jet_engine_field' => [
                'name' => 'efpp_dc_jet_engine_field',
                'label' => esc_html__('Meta Field', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'groups' => $this->get_jet_engine_meta_fields_with_options_for_select(),
                'default' => '',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'efpp_dc_source_type' => 'jetengine',
                ],
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'efpp_dc_input_type' => [
                'name' => 'efpp_dc_input_type',
                'label' => esc_html__('Field Type', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'select' => 'Select',
                    'radio' => 'Radio',
                    'checkboxes' => 'Checkbox',
                ],
                'default' => 'select',
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'efpp_dc_reload_widget_script' => [
                'label' => 'Test',
                'name' => 'efpp_dc_reload_widget_script',
                'type' => \Elementor\Controls_Manager::RAW_HTML,

                // 'raw' => '<# window.efppControlRemoteRender("form") #>',
                'raw' => $script,
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

    private function get_jet_engine_meta_fields_with_options_for_select() {
        $options = array();
        
        if (function_exists('jet_engine') && jet_engine()->meta_boxes) {
            $meta_boxes = jet_engine()->meta_boxes->get_registered_fields();
            $post_types = get_post_types( array(), 'objects' );

            foreach ( $meta_boxes as $meta_box_name => $meta_box_fields ) {
                foreach( $meta_box_fields as $field ) {
                    if ( in_array( $field['type'], ['select', 'radio', 'checkbox'] ) ) {
                        $options[ $meta_box_name ]['label'] = $post_types[ $meta_box_name ]->labels->name;

                        $option_value = $meta_box_name . '|' . $field['name'];
                        $option_label = $field['title'];

                        $options[ $meta_box_name ]['options'][ $option_value ] = $option_label;
                    }
                }
            }


        }

        return $options;
    }

    private function get_jet_engine_meta_field_options( $jet_engine_field ) {
        $jet_engine_field_args = explode( '|', $jet_engine_field, 2 );
        $meta_fields_group_name = $jet_engine_field_args[0];
        $meta_field_name = $jet_engine_field_args[1];
        $options = array();

        if (function_exists('jet_engine') && jet_engine()->meta_boxes) {

            $meta_boxes = jet_engine()->meta_boxes->get_registered_fields();
            $meta_fields_group = $meta_boxes[ $meta_fields_group_name ];

            foreach( $meta_fields_group as $meta_field ) {
                if ( $meta_field['name'] === $meta_field_name ) {
                    $options = $meta_field['options'];
                    break;
                }
            }
            
            $options = array_column( $options, 'value', 'key' );
        }

        return $options;
    }

}
