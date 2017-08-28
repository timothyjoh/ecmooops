<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Title_Field' ) ) {

	class WPPF_Title_Field extends Pretty_Fields {

		/**
		 * Disables the output of the regular fields wrapper
		 */
		static function display_wrapper() {
			return false;
		}

		/**
		 * Enables the output of markup for fields that do not require a wrapper.
		 */
		static function display_empty_wrapper() {
			return true;
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 */
		static function html( $meta, $field ) {

			$output = '<h3 class="wppf-title" id="'.$field['id'].'">'.$field['name'].'</h3>';
			$output .= '<p class="wppf-title description">'.$field['desc'].'</h3>';

			return $output;

		}

	}

}
