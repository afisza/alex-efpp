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

            wp_enqueue_media(); // Konieczne do otwierania Media Library

            wp_enqueue_script(
                'efpp-featured-image-js',
                plugin_dir_url(__FILE__) . '../assets/efpp-featured-image.js',
                ['jquery', 'media-editor'],
                '1.0',
                true
            );

            // Przekazujemy dane do JS
            wp_localize_script('efpp-featured-image-js', 'EFPPImageField', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('efpp_featured_image_upload'),
            ]);

            $field_name  = esc_attr($item['field_name'] ?? 'efpp_featured_image');
            $field_value = '';

            if ( isset( $item['field_value'] ) && ! empty( $item['field_value'] ) ) {
                $field_value = esc_url( $item['field_value'] );
            } elseif ( is_singular() && get_post_thumbnail_id() ) {
                $field_value = wp_get_attachment_url( get_post_thumbnail_id() );
            }


            ?>

            <div class="efpp-featured-image-wrapper" data-field-name="<?php echo $field_name; ?>">
                <div class="efpp-drop-zone<?php echo $field_value ? ' has-image' : ''; ?>">
                    <?php if ( $field_value ) : ?>
                        <img src="<?php echo $field_value; ?>" class="efpp-preview" />
                    <?php else : ?>
                        <img src="" class="efpp-preview" style="display: none;" />
                    <?php endif; ?>

                    <div class="efpp-instructions">Kliknij lub przeciągnij, aby dodać obrazek</div>
                    <button type="button" class="efpp-remove-image" style="<?php echo $field_value ? '' : 'display:none;'; ?>">×</button>
                </div>

                <input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_url( $field_value ); ?>" />
            </div>

            <?php
        }

        public function get_default_settings() {
            return [
                'input_type' => 'hidden',
            ];
        }
    }
}