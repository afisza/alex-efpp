<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;

add_action('elementor/element/after_section_end', function ( $element, $section_id, $args ) {

    if ( 'form' === $element->get_name() && 'section_field_style' === $section_id ) {

        $element->start_controls_section(
            'custom_section',
            [
                'tab' => Controls_Manager::TAB_STYLE,
                'label' => esc_html__( 'EFPP Style Settings', 'alex-efpp' ),
            ]
        );

        // Padding
        $element->add_responsive_control(
            'efpp_inline_option_padding',
            [
                'label' => esc_html__('Label Padding', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} label.elementor-field-option' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Column Gap
        $element->add_responsive_control(
            'efpp_inline_column_gap',
            [
                'label' => esc_html__('Horizontal Gap', 'alex-efpp'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-subgroup.efpp-options-inline' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Row Gap
        $element->add_responsive_control(
            'efpp_inline_row_gap',
            [
                'label' => esc_html__('Vertical Gap', 'alex-efpp'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-subgroup.efpp-options-inline' => 'row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Typography
        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_inline_typography',
                'label' => esc_html__('Label Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-options-wrapper label',
                //'selector' => '{{WRAPPER}} .elementor-field-subgroup.efpp-options-inline .efpp-option-label',
                'classes' => 'elementor-re-render efpp-remote-render',
            ]
        );

        $element->add_responsive_control(
            'efpp_icon_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'alex-efpp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'default' => [
                    'size' => 0.5,
                    'unit' => 'em',
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-option-icon, {{WRAPPER}} .efpp-option-icon-checked' => 'margin-inline-end: {{SIZE}}{{UNIT}};',
                ],
                'classes' => 'elementor-re-render',
            ]
        );
        $element->add_responsive_control(
            'efpp_icon_size',
            [
                'label' => esc_html__('Icon Size', 'alex-efpp'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'default' => [
                    'size' => 1.2,
                    'unit' => 'em',
                ],
                'range' => [
                    'px' => ['min' => 8, 'max' => 100],
                    'em' => ['min' => 0.5, 'max' => 5],
                    'rem' => ['min' => 0.5, 'max' => 5],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-option-icon, {{WRAPPER}} .efpp-option-icon-checked' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .efpp-option-icon svg, {{WRAPPER}} .efpp-option-icon-checked svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'classes' => 'elementor-re-render',
            ]
        );


        $element->add_control(
            'efpp_hide_inputs',
            [
                'label' => esc_html__('Hide Radio/Checkbox Inputs', 'alex-efpp'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'alex-efpp'),
                'label_off' => esc_html__('No', 'alex-efpp'),
                'return_value' => 'yes',
                'default' => '',
                'prefix_class' => 'efpp-hide-inputs-',
                'classes' => 'elementor-re-render',
            ]
        );


                // === START: STYLE TABS ===
        $element->start_controls_tabs('efpp_style_tabs');

        // --- NORMAL ---
        $element->start_controls_tab(
            'efpp_style_tab_normal',
            [
                'label' => esc_html__('Normal', 'alex-efpp'),
            ]
        );

        $element->add_control(
            'efpp_icon_normal',
            [
                'label' => esc_html__('Normal Icon', 'alex-efpp'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'far fa-circle',
                    'library' => 'fa-regular',
                ],
                'recommended' => [
                    'fa-regular' => ['circle', 'square'],
                    'fa-solid' => ['circle', 'square', 'circle-dot'],
                ],
                'skin' => 'inline',
            ]
        );

        $element->add_control(
            'efpp_color_normal',
            [
                'label' => esc_html__('Text Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option' => 'color: {{VALUE}};',
                ],
                'classes' => 'elementor-re-render',
            ]
        );

        $element->add_control(
            'efpp_bg_normal',
            [
                'label' => esc_html__('Background Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option' => 'background-color: {{VALUE}};',
                ],
                'classes' => 'elementor-re-render',
            ]
        );

        $element->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'efpp_border_normal',
                'selector' => '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option',
            ]
        );

        $element->add_responsive_control(
            'efpp_radius_normal',
            [
                'label' => esc_html__('Border Radius', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->end_controls_tab();

        // --- HOVER ---
        $element->start_controls_tab(
            'efpp_style_tab_hover',
            [
                'label' => esc_html__('Hover', 'alex-efpp'),
            ]
        );

        $element->add_control(
            'efpp_bg_hover',
            [
                'label' => esc_html__('Background Hover', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_color_hover',
            [
                'label' => esc_html__('Text Hover', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_border_hover_color',
            [
                'label' => esc_html__('Border Hover Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_responsive_control(
            'efpp_radius_hover',
            [
                'label' => esc_html__('Border Radius (Hover)', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option:hover' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->end_controls_tab();

        // --- CHECKED ---
        $element->start_controls_tab(
            'efpp_style_tab_checked',
            [
                'label' => esc_html__('Checked', 'alex-efpp'),
            ]
        );

        $element->add_control(
            'efpp_icon_checked',
            [
                'label' => esc_html__('Checked Icon', 'alex-efpp'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'far fa-dot-circle',
                    'library' => 'fa-regular',
                ],
                'recommended' => [
                    'fa-regular' => ['dot-circle', 'check-square'],
                    'fa-solid' => ['dot-circle', 'check-square', 'check-circle'],
                ],
                'skin' => 'inline',
            ]
        );

        $element->add_control(
            'efpp_bg_checked',
            [
                'label' => esc_html__('Checked Background', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-subgroup label.elementor-field-option input:checked ~ *' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_color_checked',
            [
                'label' => esc_html__('Checked Text Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper input:checked + label.elementor-field-option' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_border_checked_color',
            [
                'label' => esc_html__('Checked Border Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper input:checked + label.elementor-field-option' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_responsive_control(
            'efpp_radius_checked',
            [
                'label' => esc_html__('Border Radius (Checked)', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper input:checked + label.elementor-field-option' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->end_controls_tab();
        $element->end_controls_tabs();
        // === END: STYLE TABS ===

        $element->end_controls_section();
    }

}, 10, 3);
