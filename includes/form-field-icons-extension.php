<?php
if (!defined('ABSPATH')) exit;

// Dodajemy własne kontrolki do typu pola formularza
add_filter('elementor_pro/forms/field_types', function ($types) {
    foreach ($types as &$type) {
        if (!isset($type['fields']) || !is_array($type['fields'])) continue;

        $type['fields']['efpp_tab'] = [
            'label' => __('EFPP', 'alex-efpp'),
            'type' => \Elementor\Controls_Manager::TAB,
        ];

        $type['fields']['icon'] = [
            'label' => __('Icon', 'alex-efpp'),
            'type' => \Elementor\Controls_Manager::ICONS,
            'skin' => 'inline',
            'fa4compatibility' => 'icon',
        ];

        $type['fields']['icon_position'] = [
            'label' => __('Icon Position', 'alex-efpp'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'left' => __('Left', 'alex-efpp'),
                'right' => __('Right', 'alex-efpp'),
                'top' => __('Top', 'alex-efpp'),
            ],
            'default' => 'left',
        ];

        $type['fields']['efpp_custom_class'] = [
            'label' => __('Custom CSS Class', 'alex-efpp'),
            'type' => \Elementor\Controls_Manager::TEXT,
        ];
    }

    return $types;
});

// Renderowanie ikony
function alex_efpp_render_label_icon($item_html, $item, $form = null, $index = null) {
    if (empty($item['icon'])) return $item_html;

    ob_start();
    \Elementor\Icons_Manager::render_icon($item['icon'], ['aria-hidden' => 'true']);
    $icon_html = ob_get_clean();

    $position = $item['icon_position'] ?? 'left';
    $class = !empty($item['efpp_custom_class']) ? esc_attr($item['efpp_custom_class']) : '';

    $wrap_start = '<div class="efpp-label-icon ' . $class . '">';
    $wrap_end = '</div>';

    if ($position === 'top') {
        $item_html = str_replace('<label', $wrap_start . $icon_html . '<br><label', $item_html);
    } elseif ($position === 'right') {
        $item_html = preg_replace('/(<label[^>]*>)(.*?)(<\/label>)/', $wrap_start . '$1$2 ' . $icon_html . '$3' . $wrap_end, $item_html);
    } else {
        $item_html = preg_replace('/(<label[^>]*>)(.*?)(<\/label>)/', $wrap_start . '$1' . $icon_html . ' $2$3' . $wrap_end, $item_html);
    }

    return $item_html;
}

// Typy pól obsługiwane
$field_types = [
    'text', 'email', 'textarea', 'select', 'tel', 'url', 'date', 'number',
    'checkbox', 'radio', 'upload', 'password',
    'taxonomy_terms', 'dynamic_choose', 'efpp_featured_image'
];

foreach ($field_types as $type) {
    add_filter("elementor_pro/forms/render/item/{$type}", 'alex_efpp_render_label_icon', 10, 4);
}
