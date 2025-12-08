<?php

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EFPP_Featured_Image_Field' ) ) {
    class EFPP_Featured_Image_Field extends Field_Base {

        public function get_type() {
            return 'efpp_featured_image';
        }

        public function get_name() {
            return 'EFPP Image Uploader';
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
				'defaults' => [
					'limit' => 6,
					'maxSize' => 50,
					'allowedTypes' => ['jpg', 'jpeg', 'png', 'webp',]
				]
			]);

            $field_name = !empty($item['custom_id']) ? $item['custom_id'] : 'featured_image';
			$gallery_meta_key = !empty($item['custom_id']) ? $item['custom_id'] : 'gallery';
            $field_id   = 'form-field-' . esc_attr($field_name);
            $label = trim($item['title'] ?? '');

			// Ustawienia domyślne
			$limit       = isset($item['max_images_count']) ? (int) $item['max_images_count'] : 12;
			$max_size    = isset($item['max_file_size']) ? (float) $item['max_file_size'] : 5;
			$allowed_types = !empty($item['allowed_file_types']) ? $item['allowed_file_types'] : 'jpg,jpeg,png,webp';


			$field_map = $form->get_settings('efpp_post_field_map') ?? [];

			foreach ($field_map as $map) {
				if (($map['field_type'] ?? '') === 'custom_field' && ($map['form_field_id'] ?? '') === $field_name) {
					if (!empty($map['gallery_limit'])) {
						$limit = (int) $map['gallery_limit'];
					}
					if (!empty($map['gallery_max_size'])) {
						$max_size = (float) $map['gallery_max_size'];
					}
					if (!empty($map['gallery_allowed_types'])) {
						$allowed_types = sanitize_text_field($map['gallery_allowed_types']);
					}
				}
			}


            ?>

            <div class="elementor-field-type-<?php echo esc_attr($this->get_type()); ?> elementor-column elementor-col-100 elementor-field-group-<?php echo esc_attr($field_name); ?>">
				<?php if (!empty($label)) : ?>
					<label for="<?php echo esc_attr($field_id); ?>" class="elementor-field-label"><?php echo esc_html($label); ?></label>
				<?php endif; ?>

				<div
					class="elementor-field efpp-featured-image-wrapper"
					data-field-name="<?php echo esc_attr($field_name); ?>"
					data-limit="<?php echo esc_attr($limit); ?>"
					data-max-size="<?php echo esc_attr($max_size); ?>"
					data-types="<?php echo esc_attr($allowed_types); ?>"
				>
					<!-- Strefa drag&drop -->
					<div class="efpp-gallery-clickable efpp-drop-zone">
						<div class="efpp-instructions"><?php _e('Kliknij lub przeciągnij, aby dodać obrazki', 'alex-efpp'); ?></div>
					</div>

					<!-- Miniaturki -->
					<ul class="efpp-image-list"></ul>

					<!-- Błędy -->
					<div class="efpp-error" style="color: red; font-size: 13px; margin-top: 8px; display: none;"></div>

					<!-- Hidden inputs -->
					<input type="hidden" name="form_fields[<?php echo esc_attr($field_name); ?>]" class="efpp-featured-input" value="">
					<input type="hidden" name="form_fields[gallery]" class="efpp-gallery-input" value="">
				</div>

			</div>

			<?php
			if (Elementor\Plugin::$instance->editor->is_edit_mode()) :
				?>
				<script>
					var fieldItemId = "<?php echo esc_js($item['_id']); ?>";
					var field = jQuery('[data-field-name="<?php echo esc_js($field_name); ?>"]').parent().clone();

					if (typeof window.efppFieldsCache === 'undefined') window.efppFieldsCache = [];
					if (typeof window.efppFieldsCache["<?php echo esc_js($item['_id']); ?>"] === 'undefined') {
						window.efppFieldsCache["<?php echo esc_js($item['_id']); ?>"] = {};
					}

					window.efppFieldsCache["<?php echo esc_js($item['_id']); ?>"].html = jQuery(field).html();
				</script>
                <?php
            endif;
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
