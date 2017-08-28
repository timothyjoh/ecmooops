<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Hidden_Field' ) ) {

	class WPPF_Hidden_Field extends Pretty_Fields {

		static function display_wrapper() {
			return false;
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 */
		static function html( $meta, $field ) {

			return sprintf(
				'<input type="hidden" class="wppf-hidden" name="%s" id="%s" value="%s">',
				$field['id'],
				$field['id'],
				$meta
			);

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
