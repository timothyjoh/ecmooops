<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Textarea_Field' ) ) {

	class WPPF_Textarea_Field extends Pretty_Fields {

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 */
		static function html( $meta, $field ) {

			// Check for std parameter
			if(!$meta && array_key_exists('std', $field)){
				$meta = $field['std'];
			}
			
			return sprintf(
				'<textarea class="wppf-textarea large-text" name="%s" id="%s" cols="%s" rows="%s" placeholder="%s">%s</textarea>',
				$field['id'],
				$field['id'],
				$field['cols'],
				$field['rows'],
				$field['placeholder'],
				$meta
			);

		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 * @return array
		 */
		static function normalize_field( $field ) {

			$field = wp_parse_args( $field, array(
				'cols' => 1,
				'rows' => 5,
			) );

			return $field;

		}

		/**
		 * Sanitize
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string
		 */
		static function value( $new, $old, $post_id, $field ){
			return esc_textarea( $new );
		}

	}

}
