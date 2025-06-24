<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

class Taxonomy_Terms_Field extends Field_Base {

    public function get_type() {
        return 'taxonomy_terms';
    }

    public function get_name() {
        return 'Taxonomy Terms';
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
                'classes' => 'efpp-remote-render',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'efpp_taxonomy_input_type' => [
            'name' => 'efpp_taxonomy_input_type',
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
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

    public function render($item, $item_index, $form) {
        $taxonomy = isset($item['efpp_taxonomy']) ? sanitize_key($item['efpp_taxonomy']) : '';
        $field_name = !empty($item['custom_id']) ? $item['custom_id'] : $taxonomy;
        $field_id = 'form-field-' . esc_attr($field_name);
        $label = trim($item['title'] ?? '');
        $required = !empty($item['required']) ? 'required' : '';
        $type = $item['efpp_taxonomy_input_type'] ?? 'select';

        // Label
        if (!empty($label)) {
            printf('<label for="%s" class="elementor-field-label">%s</label>', esc_attr($field_id), esc_html($label));
        }

        // Placeholder
        $taxonomy_label = taxonomy_exists($taxonomy)
            ? get_taxonomy($taxonomy)->labels->name
            : ucfirst(str_replace('_', ' ', $taxonomy));

        $label_template = __('Select a %s', 'alex-efpp');
        $locale = get_locale();
        if (str_starts_with($locale, 'pl')) $label_template = 'Wybierz %s';
        elseif (str_starts_with($locale, 'uk')) $label_template = 'Оберіть %s';
        elseif (str_starts_with($locale, 'ru')) $label_template = 'Выберите %s';

        $terms = taxonomy_exists($taxonomy)
            ? get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false])
            : [];

        if (is_wp_error($terms) || empty($terms)) return;

        $wrapper_attr = sprintf('data-fields-repeater-item-id="%s"', esc_attr($item['_id']));

        switch ($type) {
            case 'radio':
            case 'checkboxes':
                echo '<div class="elementor-field-subgroup" ' . $wrapper_attr . '>';
                $index = 0;
                foreach ($terms as $term) {
                    echo '<span class="elementor-field-option">';
                    $option_id = 'form-field-' . $field_name . '-' . $index;

                    printf(
                        '<input id="%s" type="%s" name="form_fields[%s]%s" value="%s" %s class="elementor-field elementor-%s" />',
                        esc_attr($option_id),
                        $type === 'checkboxes' ? 'checkbox' : 'radio',
                        esc_attr($field_name),
                        $type === 'checkboxes' ? '[]' : '',
                        esc_attr($term->slug),
                        $required,
                        $type
                    );

                    printf(
                        '<label for="%s">%s</label>',
                        esc_attr($option_id),
                        esc_html($term->name)
                    );
                    echo '</span>';
                    $index++;
                }
                echo '</div>';
                break;

            case 'select':
            default:
                echo '<div class="elementor-field elementor-select-wrapper" ' . $wrapper_attr . '>';
                echo '<div class="select-caret-down-wrapper">';
                echo '<svg aria-hidden="true" class="e-font-icon-svg e-eicon-caret-down" viewBox="0 0 571.4 571.4" xmlns="http://www.w3.org/2000/svg"><path d="M571 393Q571 407 561 418L311 668Q300 679 286 679T261 668L11 418Q0 407 0 393T11 368 36 357H536Q550 357 561 368T571 393Z"></path></svg>';
                echo '</div>';

                printf(
                    '<select name="form_fields[%s]" id="%s" class="elementor-field-textual elementor-select efpp-dynamic-select" %s>',
                    esc_attr($field_name),
                    esc_attr($field_id),
                    $required
                );
                printf('<option value="">%s</option>', esc_html(sprintf($label_template, $taxonomy_label)));

                foreach ($terms as $term) {
                    printf('<option value="%s">%s</option>', esc_attr($term->slug), esc_html($term->name));
                }

                echo '</select></div>';
                break;
        }

        // === Elementor Editor Preview Support ===
        if (Elementor\Plugin::$instance->editor->is_edit_mode()) {
            ?>
            <script>
                var fieldItemId = "<?php echo esc_js($item['_id']); ?>";
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
            jQuery(document).ready(() => {
                elementor.hooks.addFilter(
                    'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                    function (inputField, item, i) {
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
}
