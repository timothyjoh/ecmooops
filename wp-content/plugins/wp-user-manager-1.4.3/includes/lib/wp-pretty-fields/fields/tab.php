<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Tab_Field' ) ) {

	class WPPF_Tab_Field extends Pretty_Fields {

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts() {
			
	        wp_enqueue_script('jquery-ui-tabs');

		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 */
		static function html( $meta, $field ) {

			return '<div id="wppf-tab-'.$field['id'].'" class="wppf-tab-field" data-name="'.$field['name'].'"></div>';

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
			return;
		}

	}

}
