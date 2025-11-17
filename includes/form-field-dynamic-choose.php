<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

class Dynamic_Choose_Field extends Field_Base {

    public function get_type() {
        return 'dynamic_choose';
    }

    public function get_name() {
        return 'Dynamic Choose';
    }

    public function update_controls($widget) {
        $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if ( is_wp_error( $control_data ) ) return;

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
            'efpp_dc_acf_field' => [
                'name' => 'efpp_dc_acf_field',
                'label' => esc_html__('Meta Field', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'groups' => $this->get_acf_meta_fields_for_select(),
                'default' => '',
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
                'groups' => $this->get_jet_engine_meta_fields_for_select(),
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
            'efpp_dc_layout' => [
                'name' => 'efpp_dc_layout',
                'label' => esc_html__('Layout', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => 'Default',
                    'inline' => 'Inline',
                    'grid' => 'Grid',
                ],
                'default' => 'default',
                'condition' => [
                    'field_type' => $this->get_type(),
                    'efpp_dc_input_type' => ['radio', 'checkboxes'],
                ],
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'efpp_dc_grid_columns' => [
                'name' => 'efpp_dc_grid_columns',
				'label' => esc_html__( 'Columns', 'alex-efpp' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 4,
                'condition' => [
                    'field_type' => $this->get_type(),
                    'efpp_dc_input_type' => ['radio', 'checkboxes'],
                    'efpp_dc_layout' => ['grid'],
                ],
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
			],
        ];

        $control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );
        $widget->update_control('form_fields', $control_data);
    }


    public function render($item, $item_index, $form) {
        $source_type = $item['efpp_dc_source_type'] ?? 'acf';
        $input_type = $item['efpp_dc_input_type'] ?? 'select';
        $is_inline = !empty($item['efpp_dc_inline_display']) && $item['efpp_dc_inline_display'] === 'yes';
        $form_settings = $form->get_settings_for_display();

        switch ($source_type) {
            case 'acf':
                $field_name = explode( ':', $item['efpp_dc_acf_field'], 2 )[1];
                $acf_field = $item['efpp_dc_acf_field'];
                $options = $this->get_acf_meta_field_options( $acf_field );
                break;

            case 'jetengine':
                $field_name = explode( '|', $item['efpp_dc_jet_engine_field'], 2 )[1];
                //$item['field_name'] = $field_name;
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
                echo '<div class="elementor-field elementor-select-wrapper" data-fields-repeater-item-id="' . $item['_id'] . '">';
                echo '<div class="select-caret-down-wrapper"><svg aria-hidden="true" class="e-font-icon-svg e-eicon-caret-down" viewBox="0 0 571.4 571.4" xmlns="http://www.w3.org/2000/svg"><path d="M571 393Q571 407 561 418L311 668Q300 679 286 679T261 668L11 418Q0 407 0 393T11 368 36 357H536Q550 357 561 368T571 393Z"></path></svg></div>';
                    echo '<select 
                        name="form_fields[' . esc_attr($item['custom_id']) . ']" 
                        id="form-field-' . esc_attr($field_name) . '" 
                        class="elementor-field-textual elementor-select efpp-dynamic-select" 
                        data-field-type="efpp-dynamic-choose">
                    ';

                    echo '<option value="">Wybierz</option>';
                        foreach ( $options as $value => $label ) {
                            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                        }
                    echo '</select>';
                echo '</div>';
                break;
            
            case 'radio':

                $field_group_classes = array(
                    'elementor-field-subgroup',
                );

                if ( ! empty( $item['efpp_dc_layout'] ) ) {
                    switch ( $item['efpp_dc_layout'] ) {
                        case 'inline':
                            $field_group_classes[] = 'elementor-subgroup-inline';
                            $field_group_classes[] = 'efpp-options-wrapper';
                            $field_group_classes[] = 'efpp-options-inline';

                            break;
                        
                        case 'grid':
                            $field_group_classes[] = 'efpp-options-wrapper';
                            $field_group_classes[] = 'efpp-options-grid';
                            if ( ! empty( $item['efpp_dc_grid_columns'] ) ) {
                                $field_group_classes[] = 'efpp-options-grid-columns-' . $item['efpp_dc_grid_columns'];
                            }
                            break;
                    }
                }

                echo '<div class="' . implode( ' ', $field_group_classes ) . '" data-fields-repeater-item-id="' . esc_attr($item['_id']) . '">';

                $index = 0;
                foreach ( $options as $value => $label ) {
                    $field_id = 'form-field-field_' . $item['_id'] . '-' . $index;

                    echo '<label class="elementor-field-option" for="' . esc_attr($field_id) . '">';

                        echo '<input
                            id="' . esc_attr($field_id) . '"
                            type="radio"
                            name="form_fields[' . esc_attr($item['custom_id']) . ']"
                            value="' . esc_attr($value) . '"
                        >';

                        if ( ! empty( $form_settings['efpp_icon_normal'] ) ) {
                            echo '<span class="efpp-option-icon">';
                            Icons_Manager::render_icon( $form_settings['efpp_icon_normal'], [ 'aria-hidden' => 'true' ] );
                            echo '</span>';
                        }

                        if ( ! empty( $form_settings['efpp_icon_checked'] ) ) {
                            echo '<span class="efpp-option-icon-checked">';
                            Icons_Manager::render_icon( $form_settings['efpp_icon_checked'], [ 'aria-hidden' => 'true' ] );
                            echo '</span>';
                        }

                        //echo '<span class="efpp-option-label">' . esc_html($label) . '</span>';
                        echo esc_html($label);

                    echo '</label>';

                    $index++;
                }

                echo '</div>';
                break;

            case 'checkboxes':

                $field_group_classes = array(
                    'elementor-field-subgroup',
                );

                if ( ! empty( $item['efpp_dc_layout'] ) ) {
                    switch ( $item['efpp_dc_layout'] ) {
                        case 'inline':
                            $field_group_classes[] = 'elementor-subgroup-inline';
                            $field_group_classes[] = 'efpp-options-wrapper';
                            $field_group_classes[] = 'efpp-options-inline';
                            break;
                        
                        case 'grid':
                            $field_group_classes[] = 'efpp-options-wrapper';
                            $field_group_classes[] = 'efpp-options-grid';
                            if ( ! empty( $item['efpp_dc_grid_columns'] ) ) {
                                $field_group_classes[] = 'efpp-options-grid-columns-' . $item['efpp_dc_grid_columns'];
                            }
                            break;
                    }
                }

                echo '<div class="' . implode( ' ', $field_group_classes ) . '" data-fields-repeater-item-id="' . esc_attr($item['_id']) . '">';

                $index = 0;
                foreach ( $options as $value => $label ) {
                    $field_id = 'form-field-field_' . $item['_id'] . '-' . $index;

                    echo '<label class="elementor-field-option" for="' . esc_attr($field_id) . '">';

                        echo '<input
                            id="' . esc_attr($field_id) . '"
                            type="checkbox"
                            name="form_fields[' . esc_attr($item['custom_id']) . '][]"
                            value="' . esc_attr($value) . '"
                            class="elementor-field elementor-checkbox"
                        >';

                        if ( ! empty( $form_settings['efpp_icon_normal'] ) ) {
                            echo '<span class="efpp-option-icon">';
                            Icons_Manager::render_icon( $form_settings['efpp_icon_normal'], [ 'aria-hidden' => 'true' ] );
                            echo '</span>';
                        }

                        if ( ! empty( $form_settings['efpp_icon_checked'] ) ) {
                            echo '<span class="efpp-option-icon-checked">';
                            Icons_Manager::render_icon( $form_settings['efpp_icon_checked'], [ 'aria-hidden' => 'true' ] );
                            echo '</span>';
                        }

                        //echo '<span class="efpp-option-label">' . esc_html($label) . '</span>';
                        echo esc_html($label);

                    echo '</label>';

                    $index++;
                }

                echo '</div>';
                break;
        }

        if ( Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            ?>
            <script>
                var fieldItemId = "<?php echo $item['_id']; ?>";
                var field = jQuery('div[data-fields-repeater-item-id="' + fieldItemId + '"');

                if (typeof window.efppFieldsCache === 'undefined') {
                    window.efppFieldsCache = [];
                }

                if (typeof window.efppFieldsCache[fieldItemId] === 'undefined') {
                    window.efppFieldsCache[fieldItemId] = {};
                }

                var fieldParentClone = jQuery(field).parent().clone();
                jQuery(fieldParentClone).find('label.elementor-field-label').remove();
                jQuery(fieldParentClone).find('script').remove();

                var fieldParentCloneHtml = jQuery(fieldParentClone).html();

                window.efppFieldsCache[fieldItemId].html = fieldParentCloneHtml;
            </script>
            <?php
        }

    }


    public function get_value($item, $submitted_data) {
        $field_name = $item['field_name'] ?? '';
        return $submitted_data[$field_name] ?? '';
    }

    public function __construct() {
        parent::__construct();
        add_action('elementor/preview/init', [ $this, 'editor_preview_footer' ]);
    }

    public function editor_preview_footer(): void {
        add_action('wp_footer', [ $this, 'content_template_script' ]);
    }

    public function content_template_script(): void {
        ?>
        <script>
            jQuery( document ).ready( () => {

                elementor.hooks.addFilter(
                    'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                    function ( inputField, item, i ) {
                        if (typeof window.efppFieldsCache === 'undefined') {
                            window.efppFieldsCache = [];
                        }

                        if (typeof window.efppFieldsCache[item._id] === 'undefined') {
                            window.efppFieldsCache[item._id] = {};
                        }

                        var fieldHtml = window.efppFieldsCache[item._id].html;

                        return fieldHtml;
                    }, 10, 3
                );

            });
        </script>
        <?php
    }

    private function get_acf_meta_fields_for_select() {
        $types = array(
            'select',
			'checkbox',
			'radio',
        );

		// ACF >= 5.0.0
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();
		} else {
			$acf_groups = apply_filters( 'acf/get_field_groups', [] );
		}

		$groups = [];

		$options_page_groups_ids = [];

		if ( function_exists( 'acf_options_page' ) ) {
			$pages = acf_options_page()->get_pages();
			foreach ( $pages as $slug => $page ) {
				$options_page_groups = acf_get_field_groups( [
					'options_page' => $slug,
				] );

				foreach ( $options_page_groups as $options_page_group ) {
					$options_page_groups_ids[] = $options_page_group['ID'];
				}
			}
		}

		foreach ( $acf_groups as $acf_group ) {
			// ACF >= 5.0.0
			if ( function_exists( 'acf_get_fields' ) ) {
				if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
					$fields = acf_get_fields( $acf_group['ID'] );
				} else {
					$fields = acf_get_fields( $acf_group );
				}
			} else {
				$fields = apply_filters( 'acf/field_group/get_fields', [], $acf_group['id'] );
			}

			$options = [];

			if ( ! is_array( $fields ) ) {
				continue;
			}

			$has_option_page_location = in_array( $acf_group['ID'], $options_page_groups_ids, true );
			$is_only_options_page = $has_option_page_location && 1 === count( $acf_group['location'] );

			foreach ( $fields as $field ) {
				if ( ! in_array( $field['type'], $types, true ) ) {
					continue;
				}

				// Use group ID for unique keys
				if ( $has_option_page_location ) {
					$key = 'options:' . $field['name'];
					$options[ $key ] = esc_html__( 'Options', 'elementor-pro' ) . ':' . $field['label'];
					if ( $is_only_options_page ) {
						continue;
					}
				}

				$key = $acf_group['ID'] . ':' . $field['name'];
				$options[ $key ] = $field['label'];
			}

			if ( empty( $options ) ) {
				continue;
			}

			if ( 1 === count( $options ) ) {
				$options = [ -1 => ' -- ' ] + $options;
			}

			$groups[] = [
				'label' => $acf_group['title'],
				'options' => $options,
			];
		} // End foreach().

		return $groups;
	}

    private function get_acf_meta_field_options( $acf_field ) {
        $acf_field_args = explode( ':', $acf_field, 2 );
        $meta_fields_group_name = $acf_field_args[0];
        $meta_field_name = $acf_field_args[1];
        $group_fields = acf_get_fields( $meta_fields_group_name );
        $options = array();

        if ( $group_fields ) {
            foreach ( $group_fields as $field ) {
                if ( $field['name'] === $meta_field_name ) {
                    $options = $field['choices'];
                    break;
                }
            }
        }

        return $options;
	}

    private function get_jet_engine_meta_fields_for_select() {
        $types = array(
            'select',
            'radio',
            'checkbox'
        );
        $options = array();
        
        if (function_exists('jet_engine') && jet_engine()->meta_boxes) {
            $meta_boxes = jet_engine()->meta_boxes->get_registered_fields();
            $post_types = get_post_types( array(), 'objects' );

            foreach ( $meta_boxes as $meta_box_name => $meta_box_fields ) {
                foreach( $meta_box_fields as $field ) {
                    if ( in_array( $field['type'], $types ) ) {
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
