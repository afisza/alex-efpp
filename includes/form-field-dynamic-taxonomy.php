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
        $taxonomy = isset($item['efpp_taxonomy']) ? sanitize_key($item['efpp_taxonomy']) : '';
        $field_name = isset($item['custom_id']) && !empty($item['custom_id']) ? $item['custom_id'] : $taxonomy;

        $custom_label = trim($item['title'] ?? '');

        // if (!empty($custom_label)) {
        //     echo sprintf(
        //         '<label for="form-field-%1$s" class="elementor-field-label">%2$s</label>',
        //         esc_attr($field_name),
        //         esc_html($custom_label)
        //     );
        // }

        echo '<div class="elementor-field elementor-select-wrapper">';

        printf(
            '<select name="form_fields[%s]" id="form-field-%s" class="elementor-field-textual elementor-select" required>',
            esc_attr($field_name),
            esc_attr($field_name)
        );

        // Gettext + fallback tłumaczenia
        $label_template = __( 'Select a %s', 'alex-efpp' );
        if ( $label_template === 'Select a %s' ) {
            $locale = get_locale();
            if ( str_starts_with( $locale, 'pl' ) ) {
                $label_template = 'Wybierz %s';
            } elseif ( str_starts_with( $locale, 'uk' ) ) {
                $label_template = 'Оберіть %s';
            } elseif ( str_starts_with( $locale, 'ru' ) ) {
                $label_template = 'Выберите %s';
            }
        }

        $taxonomy_term_label = taxonomy_exists($taxonomy)
            ? get_taxonomy($taxonomy)->labels->name
            : ucfirst(str_replace('_', ' ', $taxonomy));

        printf(
            '<option value="">%s</option>',
            esc_html( sprintf($label_template, $taxonomy_term_label) )
        );

        if (taxonomy_exists($taxonomy)) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    printf(
                        '<option value="%d">%s</option>',
                        esc_attr($term->term_id),
                        esc_html($term->name)
                    );
                }
            }
        }

        echo '</select>';
        echo '</div>';
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
                'frontend_available' => true,
                'render_type' => 'template',
                'label' => esc_html__('Taxonomy', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $taxonomy_options,
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

    public function get_value( $item, $submitted_data ) {
        $field_name = isset($item['custom_id']) && !empty($item['custom_id']) ? $item['custom_id'] : $item['efpp_taxonomy'] ?? '';
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
                        const fieldType    = 'tel';
                        const fieldId      = `form_field_${i}`;
                        const fieldClass   = `elementor-field-textual elementor-field ${item.css_classes}`;
                        const inputmode    = 'numeric';
                        const maxlength    = '19';
                        const pattern      = '[0-9\s]{19}';
                        const placeholder  = item['credit-card-placeholder'];
                        const autocomplete = 'cc-number';

                        return `<input type="${fieldType}" id="${fieldId}" class="${fieldClass}" inputmode="${inputmode}" maxlength="${maxlength}" pattern="${pattern}" placeholder="${placeholder}" autocomplete="${autocomplete}">`;
                    }, 10, 3
                );

            });
        </script>
        <?php
    }
}
