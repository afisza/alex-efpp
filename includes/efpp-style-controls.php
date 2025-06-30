<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;


add_action('elementor/element/after_section_end', function ( $element, $section_id, $args ) {

        if ( 'form' === $element->get_name() && 'section_field_style' === $section_id ) {

            $element->start_controls_section(
                'custom_section',
                [
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'label' => esc_html__( 'Custom Section', 'textdomain' ),
                ]
            );

            $element->add_control(
                'custom_control',
                [
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'label' => esc_html__( 'Custom Control', 'textdomain' ),
                ]
            );

            
		    $element->end_controls_section();

    }
}, 10, 3);

