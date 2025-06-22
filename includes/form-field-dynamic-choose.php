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
                            error_log( "field\n" . print_r( $field, true ) . "\n" );
                            if ($field['name'] === $field_name && $field['type'] === $input_type) {
                                $options = $field['choices'];
                                break;
                            }
                        }
                    }

                }
                break;

            case 'jetengine':

                break;
            
            default:
                # code...
                break;
        }

        // TODO: logika pobierania wartości z metadanych ACF/JetEngine


        // if ($source_type === 'jetengine' && function_exists('jet_engine')) {
            // $meta_boxes = jet_engine()->meta_boxes->meta_boxes ?? [];
            // foreach ($meta_boxes as $group) {
                // if (isset($group['fields'])) {
                    // foreach ($group['fields'] as $field) {
                        // if ($field['name'] === $field_name && isset($field['options'])) {
                            // $values = $field['options'];
                            // break 2;
                        // }
                    // }
                // }
            // }

            // // Dodatkowo: CPT field fallback
            // $post_types = jet_engine()->post_types->post_types ?? [];
            // foreach ($post_types as $type) {
                // if (isset($type['fields'])) {
                    // foreach ($type['fields'] as $field) {
                        // if ($field['name'] === $field_name && isset($field['options'])) {
                            // $values = $field['options'];
                            // break 2;
                        // }
                    // }
                // }
            // }
        // }

        echo '<label for="form-field-' . esc_attr($field_name) . '" class="elementor-field-label">' . esc_html($item['title'] ?? 'Dynamic Field') . '</label>';
        echo '<div class="elementor-field elementor-select-wrapper">';

        if ($input_type === 'select') {

        }

        switch ($input_type) {
            case 'select':
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
                break;
            
            case 'radio':

                break;
 
 
            case 'checkboxes':

                break;
        }

        echo '</div>';
    }

    public function update_controls($widget) {
        $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) return;
        error_log('EFPP: update_controls działa.');

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
