<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Number_Field' ) ) {

	class WPPF_Number_Field extends Pretty_Fields {

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
				'<input type="number" class="wppf-text number mini" name="%s" id="%s" value="%s" step="%s" min="%s" placeholder="%s">',
				$field['id'],
				$field['id'],
				$meta,
				$field['step'],
				$field['min'],
				$field['placeholder']
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
				'step' => 1,
				'min'  => 0,
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
			return intval( $new );
		}

	}

}
