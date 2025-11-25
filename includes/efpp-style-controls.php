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
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option.efpp-checked' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_color_checked',
            [
                'label' => esc_html__('Checked Text Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option.efpp-checked' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_border_checked_color',
            [
                'label' => esc_html__('Checked Border Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option.efpp-checked' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_responsive_control(
            'efpp_radius_checked',
            [
                'label' => esc_html__('Border Radius (Checked)', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .efpp-options-wrapper label.elementor-field-option.efpp-checked' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->end_controls_tab();
        $element->end_controls_tabs();
        // === END: STYLE TABS ===

        // === LOGOUT LINK STYLES ===
        $element->add_control(
            'efpp_logout_heading',
            [
                'label' => esc_html__('Logout Link Styles', 'alex-efpp'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // User Info Styles
        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_logout_user_info_typography',
                'label' => esc_html__('User Info Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-logout-user-info',
            ]
        );

        $element->add_control(
            'efpp_logout_user_info_color',
            [
                'label' => esc_html__('User Info Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-user-info' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_responsive_control(
            'efpp_logout_user_info_spacing',
            [
                'label' => esc_html__('User Info Spacing', 'alex-efpp'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-user-info' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Style dla trybu listy
        $element->add_control(
            'efpp_logout_user_info_list_heading',
            [
                'label' => esc_html__('List Style (Flex)', 'alex-efpp'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_responsive_control(
            'efpp_logout_user_info_list_gap',
            [
                'label' => esc_html__('Gap Between Items', 'alex-efpp'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                    'em' => ['min' => 0, 'max' => 5],
                    'rem' => ['min' => 0, 'max' => 5],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-user-info-list' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .efpp-logout-user-info-list .efpp-user-info-item' => 'margin-bottom: 0;',
                ],
            ]
        );

        $element->add_responsive_control(
            'efpp_logout_user_info_list_direction',
            [
                'label' => esc_html__('Direction', 'alex-efpp'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'column' => esc_html__('Column (Vertical)', 'alex-efpp'),
                    'row' => esc_html__('Row (Horizontal)', 'alex-efpp'),
                ],
                'default' => 'column',
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-user-info-list' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_logout_user_info_label_color',
            [
                'label' => esc_html__('Label Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-user-info-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_logout_user_info_label_typography',
                'label' => esc_html__('Label Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-user-info-label',
            ]
        );

        $element->add_control(
            'efpp_logout_user_info_value_color',
            [
                'label' => esc_html__('Value Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-user-info-value' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_logout_user_info_value_typography',
                'label' => esc_html__('Value Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-user-info-value',
            ]
        );

        // Logout Button Styles
        $element->start_controls_tabs('efpp_logout_button_tabs');

        // Normal
        $element->start_controls_tab(
            'efpp_logout_button_normal',
            [
                'label' => esc_html__('Normal', 'alex-efpp'),
            ]
        );

        $element->add_control(
            'efpp_logout_button_color',
            [
                'label' => esc_html__('Text Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_logout_button_bg',
            [
                'label' => esc_html__('Background Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'efpp_logout_button_border',
                'selector' => '{{WRAPPER}} .efpp-logout-button',
            ]
        );

        $element->add_responsive_control(
            'efpp_logout_button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->add_responsive_control(
            'efpp_logout_button_padding',
            [
                'label' => esc_html__('Padding', 'alex-efpp'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'efpp_logout_button_typography',
                'label' => esc_html__('Typography', 'alex-efpp'),
                'selector' => '{{WRAPPER}} .efpp-logout-button',
            ]
        );

        $element->end_controls_tab();

        // Hover
        $element->start_controls_tab(
            'efpp_logout_button_hover',
            [
                'label' => esc_html__('Hover', 'alex-efpp'),
            ]
        );

        $element->add_control(
            'efpp_logout_button_hover_color',
            [
                'label' => esc_html__('Text Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_logout_button_hover_bg',
            [
                'label' => esc_html__('Background Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_logout_button_hover_border_color',
            [
                'label' => esc_html__('Border Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $element->add_control(
            'efpp_logout_button_hover_transition',
            [
                'label' => esc_html__('Transition Duration', 'alex-efpp'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efpp-logout-button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );

        $element->end_controls_tab();
        $element->end_controls_tabs();

        $element->end_controls_section();
    }

}, 10, 3);
