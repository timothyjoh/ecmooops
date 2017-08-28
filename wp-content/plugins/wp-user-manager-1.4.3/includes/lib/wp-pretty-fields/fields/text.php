<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Text_Field' ) ) {

	class WPPF_Text_Field extends Pretty_Fields {

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
				'<input type="text" class="wppf-text %s" name="%s" id="%s" value="%s" placeholder="%s" %s>',
				$field['size'],
				$field['id'],
				$field['id'],
				$meta,
				$field['placeholder'],
				$field['disabled']
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
				'size' => 'large',
				'disabled' => ''
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
			return sanitize_text_field( $new );
		}

	}

}
