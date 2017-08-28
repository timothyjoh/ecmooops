<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Radio_Field' ) ) {

	class WPPF_Radio_Field extends Pretty_Fields {

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

			$output = null;
			
			// Check for std parameter
			if(!$meta && array_key_exists('std', $field)){
				$meta = $field['std'];
			}

			foreach ( $field['options'] as $value => $label ) {
				$output .= '<label><input type="radio" class="wppf-radio" name="'.$field['name'].'" value="'.$value.'" '.checked( $value, $meta, false ).'>'.$label.'</label><br/>';
			}

			$output .= '<br/>';

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
				'options' => array()
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
			return sanitize_key( $new );
		}

	}

}
