<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Multiselect_Field' ) ) {

	class WPPF_Multiselect_Field extends Pretty_Fields {

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

			$output .= '<select class="wppf-multiselect" name="'.$field['id'].'[]" id="'.$field['id'].'" multiple="multiple">';
			foreach ( $field['options'] as $value => $label ) {
				$output .= '<option value="'.$value.'" '.selected( in_array( $value, (array) $meta ), true, false ).'>'.$label.'</option>';
			}
			$output .= '</select>';

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
				'options' => array(),
			) );

			return $field;

		}

	}

}
