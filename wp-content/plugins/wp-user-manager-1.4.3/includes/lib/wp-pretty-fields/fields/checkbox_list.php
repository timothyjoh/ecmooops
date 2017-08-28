<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Checkbox_List_Field' ) ) {

	class WPPF_Checkbox_List_Field extends Pretty_Fields {

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

			$meta = (array) $meta;
			
			foreach ($field['options'] as $value => $label) {
				$output .= '<label><input type="checkbox" name="'.$field['id'].'[]" value="'.$value.'" '.checked( in_array( $value, $meta ), 1, false ).' />'.$label.'</label><br />';
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

	}

}
