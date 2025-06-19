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
        // Ensure the taxonomy name is safely pulled from the $item array
        $taxonomy = isset( $item['efpp_taxonomy'] ) ? sanitize_key( $item['efpp_taxonomy'] ) : '';

        // Check if the given taxonomy actually exists before proceeding
        if ( taxonomy_exists( $taxonomy ) ) {

            // Retrieve all terms from the given taxonomy, even if they are not used
            $terms = get_terms(
                array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false, // Set to true if you want to exclude unused terms
                )
            );

            // Begin rendering the <select> dropdown
            printf(
                '<select name="%1$s_term">',
                esc_attr( $taxonomy )
            );

            // Optional: Add a default empty option at the top
            printf(
                '<option value="">%s</option>',
                esc_html( 'Select a ' . ucfirst( $taxonomy ) )
            );

            // Only loop through the terms if there were no errors and terms were found
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    printf(
                        '<option value="%1$d">%2$s</option>',
                        esc_attr( $term->term_id ),
                        esc_html( $term->name )
                    );
                }
            }

            // Close the <select> element
            echo '</select>';

        } else {
            // Display an error message if the taxonomy is invalid
            echo '<p><em>' . esc_html__( 'Invalid taxonomy selected.', 'your-text-domain' ) . '</em></p>';
        }
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
                'render_type' => 'template',
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
            ],
        ];

        $control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );
		$widget->update_control( 'form_fields', $control_data );
    }

    	/**
	 * Field constructor.
	 *
	 * Used to add a script to the Elementor editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );
	}

	/**
	 * Elementor editor preview.
	 *
	 * Add a script to the footer of the editor preview screen.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function editor_preview_footer(): void {
		add_action( 'wp_footer', [ $this, 'content_template_script' ] );
	}

	/**
	 * Content template script.
	 *
	 * Add content template alternative, to display the field in Elementor editor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function content_template_script(): void {
		?>
		<script>
		jQuery( document ).ready( () => {

			elementor.hooks.addFilter(
				'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
				function ( inputField, item, i ) {
					// const fieldType    = 'tel';
					// const fieldId      = `form_field_${i}`;
					// const fieldClass   = `elementor-field-textual elementor-field ${item.css_classes}`;
					// const inputmode    = 'numeric';
					// const maxlength    = '19';
					// const pattern      = '[0-9\s]{19}';
					// const placeholder  = item['credit-card-placeholder'];
					// const autocomplete = 'cc-number';

					return `<select></select>`;
				}, 10, 3
			);

		});
		</script>
		<?php
	}

}
