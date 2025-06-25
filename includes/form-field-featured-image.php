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
            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script(
                'efpp-featured-image-js',
                plugin_dir_url(__FILE__) . '../assets/efpp-featured-image.js',
                ['jquery', 'media-editor', 'jquery-ui-sortable'],
                '1.0',
                true
            );

            wp_localize_script('efpp-featured-image-js', 'EFPPImageField', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('efpp_featured_image_upload'),
            ]);

            $field_name = !empty($item['custom_id']) ? $item['custom_id'] : 'featured_image';
			//$gallery_meta_key = $item['gallery_meta_key'] ?? 'gallery';
			$gallery_meta_key = !empty($item['custom_id']) ? $item['custom_id'] : 'gallery';


            $field_id   = 'form-field-' . esc_attr($field_name);
            $label = trim($item['title'] ?? '');

            ?>

            <div class="elementor-field-type-<?php echo esc_attr($this->get_type()); ?> elementor-column elementor-col-100 elementor-field-group-<?php echo esc_attr($field_name); ?>">
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="elementor-field-label"><?php echo esc_html($label); ?></label>
                <?php endif; ?>

                <div class="elementor-field efpp-featured-image-wrapper" data-field-name="<?php echo esc_attr($field_name); ?>">
                    <div class="efpp-gallery-clickable">
						<div class="efpp-drop-zone">
							<div class="efpp-instructions"><?php _e('Kliknij lub przeciągnij, aby dodać obrazki', 'alex-efpp'); ?></div>
						</div>
						<ul class="efpp-image-list"></ul>
					</div>

                    <input type="hidden"
						name="form_fields[<?php echo esc_attr($field_name); ?>]"
						class="efpp-featured-input efpp-featured-<?php echo esc_attr($field_name); ?>"
						value="">

					<input type="hidden"
						name="form_fields[<?php echo esc_attr($gallery_meta_key); ?>]"
						class="efpp-gallery-input efpp-gallery-<?php echo esc_attr($gallery_meta_key); ?>"
						value="">


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
