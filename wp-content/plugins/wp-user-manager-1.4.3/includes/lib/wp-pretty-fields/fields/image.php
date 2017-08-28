<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Image_Field' ) ) {

	class WPPF_Image_Field extends Pretty_Fields {

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts() {
			
			// This function loads in the required media files for the media manager.
	        wp_enqueue_media();

		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 */
		static function html( $meta, $field ) {

			// media manager trigger button
			$output = '<a href="#" id="wppf-image-'.$field['id'].'" class="wppf-open-media button button-primary wppf-image" data-inputid="#wppf-'.$field['id'].'" data-frame="'.$field['frame_title'].'" data-button="'.$field['frame_button'].'">'.$field['button_label'].'</a> ';

			// Image url container
			$output .= '<input type="text" class="wppf-image wppf-image-url" name="wppf-'.$field['id'].'" id="wppf-'.$field['id'].'" size="70" value="'.$meta.'"/>';

			return $output;

		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 * @return array
		 */
		static function normalize_field( $field ) {

			$field = wp_parse_args( $field, array(
				'button_label' => __('Upload', 'wpum'),
				'frame_title' => __('Select or upload an image', 'wpum'),
				'frame_button' => __('Insert image', 'wpum')
			) );

			return $field;

		}

		/**
		 * Sanitize url
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string
		 */
		static function value( $new, $old, $post_id, $field ){

			$prefix = 'wppf-';
			$the_field_id = $prefix.$field['id'];

			return esc_url( $_POST[$the_field_id] );
		}

	}

}
