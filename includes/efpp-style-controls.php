<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

add_action('elementor/init', function () {
    add_action('elementor/element/form/after_section_end', function ($element) {
        error_log('EFPP after_section_end');

        $element->start_controls_section(
            'alex_efpp_checkbox_style',
            [
                'label' => __('EFPP Checkbox Style', 'alex-efpp'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $element->add_control(
            'alex_efpp_checkbox_color',
            [
                'label' => __('Text Color', 'alex-efpp'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-type-dynamic_choose input[type="checkbox"] + label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'alex_efpp_checkbox_typography',
                'selector' => '{{WRAPPER}} .elementor-field-type-dynamic_choose input[type="checkbox"] + label',
            ]
        );

        $element->end_controls_section();
    }, 10, 1);
});
