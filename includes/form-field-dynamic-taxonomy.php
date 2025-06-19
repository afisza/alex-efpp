<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

class Taxonomy_Terms_Field extends Field_Base {

    public function get_type() {
        return 'taxonomy_terms';
    }

    public function get_name() {
        return 'Taxonomy Terms';
    }

    public function render( $item, $item_index, $form ) {
        $taxonomy = isset( $item['efpp_taxonomy'] ) ? sanitize_key( $item['efpp_taxonomy'] ) : '';
        $field_name = isset($item['custom_id']) && !empty($item['custom_id']) ? $item['custom_id'] : $taxonomy;

        if ( taxonomy_exists( $taxonomy ) ) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            echo '<label for="' . esc_attr($field_name) . '">' . esc_html($item['title'] ?? ucfirst($taxonomy)) . '</label>';

            echo '<div class="elementor-field elementor-select-wrapper">';

            printf(
                '<select name="form_fields[%s]" id="form-field-%s" class="elementor-field-textual elementor-select" required>',
                esc_attr($field_name),
                esc_attr($field_name)
            );


            printf('<option value="">%s</option>', esc_html('Select a ' . ucfirst($taxonomy)));

            if ( ! is_wp_error($terms) && ! empty($terms) ) {
                foreach ( $terms as $term ) {
                    printf('<option value="%1$d">%2$s</option>',
                        esc_attr( $term->term_id ),
                        esc_html( $term->name )
                    );
                }
            }

            echo '</select>';
            echo '</div>';

        } else {
            echo '<p><em>' . esc_html__('Invalid taxonomy selected.', 'your-text-domain') . '</em></p>';
        }
    }

    public function update_controls( $widget ) {
        $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

        if ( is_wp_error( $control_data ) ) {
            return;
        }

        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $taxonomy_options = [];

        foreach ( $taxonomies as $taxonomy ) {
            $taxonomy_options[$taxonomy->name] = $taxonomy->labels->name;
        }

        $field_controls = [
            'efpp_taxonomy' => [
                'name' => 'efpp_taxonomy',
                'label' => esc_html__('Taxonomy', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $taxonomy_options,
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'render_type' => 'template',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

    public function get_value( $item, $submitted_data ) {
        $field_name = isset($item['custom_id']) && !empty($item['custom_id']) ? $item['custom_id'] : $item['efpp_taxonomy'] ?? '';

        if (isset($_POST[$field_name])) {
            return sanitize_text_field($_POST[$field_name]);
        }

        return '';
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
        jQuery(document).ready(() => {
            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                function (inputField, item, i) {
                    const fieldId    = `form_field_${i}`;
                    const fieldName  = typeof item.custom_id === 'string' ? item.custom_id.trim() : '';
                    const fieldLabel = item.title || fieldName || 'Taxonomy';

                    if (!fieldName) {
                        return `<div class="elementor-field elementor-select-wrapper">
                            <label for="${fieldId}">${fieldLabel}</label>
                            <select id="${fieldId}" name="taxonomy" class="elementor-field-textual elementor-select" disabled>
                                <option>Wybierz taksonomię</option>
                            </select>
                        </div>`;
                    }

                    const mockTerms = [
                        { id: 1, name: 'Term 1' },
                        { id: 2, name: 'Term 2' },
                        { id: 3, name: 'Term 3' }
                    ];

                    let options = `<option value="">Select a ${fieldName}</option>`;
                    mockTerms.forEach(term => {
                        options += `<option value="${term.id}">${term.name}</option>`;
                    });

                    return `<div class="elementor-field elementor-select-wrapper">
                        <label for="${fieldId}">${fieldLabel}</label>
                        <select id="${fieldId}" name="${fieldName}" class="elementor-field-textual elementor-select">
                            ${options}
                        </select>
                    </div>`;
                },
                10,
                3
            );
        });
        </script>
        <?php
    }
}
