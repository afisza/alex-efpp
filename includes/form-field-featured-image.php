<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EFPP_Featured_Image_Field' ) ) {
    class EFPP_Featured_Image_Field extends Field_Base {

        public function get_type() {
            return 'efpp_featured_image';
        }

        public function get_name() {
            return 'EFPP Featured Image Upload + Preview';
        }

        public function render($item, $item_index, $form) {
            wp_enqueue_style(
                'efpp-featured-image-style',
                plugin_dir_url(__FILE__) . '../assets/efpp-featured-image.css',
                [],
                '1.0'
            );

            wp_enqueue_media();

            wp_enqueue_script(
                'efpp-featured-image-js',
                plugin_dir_url(__FILE__) . '../assets/efpp-featured-image.js',
                ['jquery', 'media-editor'],
                '1.0',
                true
            );

            wp_localize_script('efpp-featured-image-js', 'EFPPImageField', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('efpp_featured_image_upload'),
            ]);

            $field_name = !empty($item['custom_id']) ? $item['custom_id'] : 'featured_image';
            $field_id   = 'form-field-' . esc_attr($field_name);

            $field_value = '';

            if (!empty($item['field_value'])) {
                $field_value = esc_url($item['field_value']);
            } elseif (is_singular() && get_post_thumbnail_id()) {
                $field_value = wp_get_attachment_url(get_post_thumbnail_id());
            }

            $label = trim($item['title'] ?? '');
            ?>

            <div class="elementor-field-type-<?php echo esc_attr($this->get_type()); ?> elementor-column elementor-col-100 elementor-field-group-<?php echo esc_attr($field_name); ?>">
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="elementor-field-label"><?php echo esc_html($label); ?></label>
                <?php endif; ?>

                <div class="elementor-field efpp-featured-image-wrapper" data-field-name="<?php echo $field_name; ?>">
                    <div class="efpp-drop-zone<?php echo $field_value ? ' has-image' : ''; ?>">
                        <?php if ($field_value) : ?>
                            <img src="<?php echo $field_value; ?>" class="efpp-preview" />
                        <?php else : ?>
                            <img src="" class="efpp-preview" style="display: none;" />
                        <?php endif; ?>

                        <div class="efpp-instructions"><?php _e('Click or Drag to add an image', 'alex-efpp'); ?></div>
                        <button type="button" class="efpp-remove-image" style="<?php echo $field_value ? '' : 'display:none;'; ?>">×</button>
                    </div>

                    <input type="hidden" name="form_fields[<?php echo esc_attr($field_name); ?>]" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_url($field_value); ?>" />
                </div>
            </div>
            <?php

            if ( Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                ?>
                <script>
                    var fieldItemId = "<?php echo esc_js($item['_id']); ?>";
                    var field = jQuery('[data-field-name="<?php echo esc_js($field_name); ?>"]').parent().clone();

                    if (typeof window.efppFieldsCache === 'undefined') {
                        window.efppFieldsCache = [];
                    }

                    if (typeof window.efppFieldsCache["<?php echo esc_js($item['_id']); ?>"] === 'undefined') {
                        window.efppFieldsCache["<?php echo esc_js($item['_id']); ?>"] = {};
                    }

                    window.efppFieldsCache["<?php echo esc_js($item['_id']); ?>"].html = jQuery(field).html();
                </script>
                <?php
            }
        }

        public function get_default_settings() {
            return [
                'input_type' => 'hidden',
            ];
        }

        public function __construct() {
            parent::__construct();
            add_action('elementor/preview/init', [ $this, 'editor_preview_footer' ]);
        }

        public function editor_preview_footer(): void {
            add_action('wp_footer', [ $this, 'content_template_script' ]);
        }

        //CACHE
        public function content_template_script(): void {
            ?>
            <script>
                jQuery(document).ready(() => {
                    elementor.hooks.addFilter(
                        'elementor_pro/forms/content_template/field/efpp_featured_image',
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
}