<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EFPP_Logout_Link_Field' ) ) {
    class EFPP_Logout_Link_Field extends Field_Base {

        public function get_type() {
            return 'efpp_logout_link';
        }

        public function get_name() {
            return 'Logout Link';
        }

        public function update_controls( $widget ) {
            $control_data = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

            if ( is_wp_error( $control_data ) ) {
                return;
            }

            $field_controls = [
                'efpp_logout_link_text' => [
                    'name' => 'efpp_logout_link_text',
                    'label' => esc_html__('Logout Link Text', 'alex-efpp'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'Wyloguj',
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

        public function render($item, $item_index, $form) {
            // Sprawdź czy użytkownik jest zalogowany
            if (!is_user_logged_in()) {
                return; // Nie pokazuj linku, jeśli użytkownik nie jest zalogowany
            }

            $field_name = !empty($item['custom_id']) ? $item['custom_id'] : 'logout';
            $field_id = 'form-field-' . esc_attr($field_name);
            $label = trim($item['title'] ?? '');
            $link_text = !empty($item['efpp_logout_link_text']) ? $item['efpp_logout_link_text'] : 'Wyloguj';

            // Pobierz ustawienia formularza, aby sprawdzić jakie informacje pokazać
            $form_settings = method_exists($form, 'get_settings_for_display') 
                ? $form->get_settings_for_display() 
                : $form->get_settings();
            
            $submit_actions = $form_settings['submit_actions'] ?? [];
            
            // Informacje użytkownika pokazujemy tylko jeśli akcja logout jest wybrana
            $show_user_info = in_array('efpp_logout', $submit_actions);
            $show_email = $show_user_info && !empty($form_settings['efpp_logout_show_email']);
            $show_login = $show_user_info && !empty($form_settings['efpp_logout_show_login']);
            $show_name = $show_user_info && !empty($form_settings['efpp_logout_show_name']);
            $show_role = $show_user_info && !empty($form_settings['efpp_logout_show_role']);
            
            // Pobierz styl wyświetlania (inline lub list)
            $display_style = $form_settings['efpp_logout_user_info_display'] ?? 'inline';

            $user = wp_get_current_user();

            ?>
            <div class="elementor-field-type-<?php echo esc_attr($this->get_type()); ?> elementor-column elementor-col-100 elementor-field-group-<?php echo esc_attr($field_name); ?>">
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="elementor-field-label"><?php echo esc_html($label); ?></label>
                <?php endif; ?>

                <?php if ($show_user_info && ($show_email || $show_login || $show_name || $show_role)) : ?>
                    <div class="efpp-logout-user-info efpp-logout-user-info-<?php echo esc_attr($display_style); ?>">
                        <?php 
                        $info_items = [];
                        
                        if ($show_email) {
                            $info_items[] = [
                                'class' => 'efpp-user-email',
                                'label' => 'E-mail',
                                'value' => esc_html($user->user_email)
                            ];
                        }
                        
                        if ($show_login && $user->user_login) {
                            $info_items[] = [
                                'class' => 'efpp-user-login',
                                'label' => 'Login',
                                'value' => esc_html($user->user_login)
                            ];
                        }
                        
                        if ($show_name) {
                            $first_name = get_user_meta($user->ID, 'first_name', true);
                            $last_name = get_user_meta($user->ID, 'last_name', true);
                            $full_name = trim($first_name . ' ' . $last_name);
                            if (!empty($full_name)) {
                                $info_items[] = [
                                    'class' => 'efpp-user-name',
                                    'label' => 'Imię i Nazwisko',
                                    'value' => esc_html($full_name)
                                ];
                            }
                        }
                        
                        if ($show_role && !empty($user->roles)) {
                            $role_names = [];
                            foreach ($user->roles as $role) {
                                $role_obj = get_role($role);
                                if ($role_obj) {
                                    $role_names[] = translate_user_role($role_obj->name);
                                }
                            }
                            if (!empty($role_names)) {
                                $info_items[] = [
                                    'class' => 'efpp-user-role',
                                    'label' => 'Rola',
                                    'value' => esc_html(implode(', ', $role_names))
                                ];
                            }
                        }
                        
                        if (!empty($info_items)) {
                            if ($display_style === 'list') {
                                // Wyświetl jako lista (flex)
                                // Kierunek będzie kontrolowany przez CSS w Elementorze
                                echo '<div class="efpp-logout-user-info-list">';
                                foreach ($info_items as $item) {
                                    echo '<div class="efpp-user-info-item ' . esc_attr($item['class']) . '">';
                                    echo '<span class="efpp-user-info-label">' . esc_html($item['label']) . ':</span> ';
                                    echo '<span class="efpp-user-info-value">' . $item['value'] . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                // Wyświetl inline (domyślnie)
                                $info_parts = [];
                                foreach ($info_items as $item) {
                                    $info_parts[] = '<span class="' . esc_attr($item['class']) . '">' . $item['value'] . '</span>';
                                }
                                echo implode(' | ', $info_parts);
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="elementor-field efpp-logout-link-wrapper">
                    <button type="submit" class="elementor-button efpp-logout-button" id="<?php echo esc_attr($field_id); ?>">
                        <?php echo esc_html($link_text); ?>
                    </button>
                </div>
            </div>
            <?php
        }

        public function get_default_settings() {
            return [
                'input_type' => 'button',
            ];
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
                            var linkText = item.efpp_logout_link_text || 'Wyloguj';
                            return '<button type="submit" class="elementor-button efpp-logout-button">' + linkText + '</button>';
                        }, 10, 3
                    );
                });
            </script>
            <?php
        }
    }
}

