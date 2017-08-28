<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Checkbox_Field' ) ) {

	class WPPF_Checkbox_Field extends Pretty_Fields {

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 *
		 * @todo add support for std
		 */
		static function html( $meta, $field ) {

			$output = '<label><input type="checkbox" class="wppf-checkbox" name="'.$field['id'].'" id="'.$field['id'].'" value="1" '.checked( ! empty( $meta ), 1, false ).'>'.$field['checkbox_title'].'</label>';

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
				'checkbox_title' => ''
			) );

			return $field;

		}

		/**
		 * Check the value of the checkbox
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return int
		 */
		static function value( $new, $old, $post_id, $field ) {
			return empty( $new ) ? 0 : 1;
		}

	}

}
