<?php
/**
 * @package wp-pretty-fields
 * @author Alessandro Tesoro
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Pretty_Fields') ) {

	/**
	 * Pretty fields class.
	 */
	class Pretty_Fields {


		/**
		 * Enables markup for all fields
		 *
		 * @return void
		 * @since  1.0.0
		 */
		static function display_wrapper() {
			return true;
		}

		/**
		 * Enables markup for fields who don't require markup.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		static function display_empty_wrapper() {
			return false;
		}

		/**
		 * Add actions
		 *
		 * @return void
		 * @since  1.0.0
		 */
		static function add_actions() {

		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 * @since  1.0.0
		 */
		static function admin_enqueue_scripts() {

		}

		/**
		 * Show field HTML
		 *
		 * @param array $field
		 * @param bool  $saved
		 * @since  1.0.0
		 * @return string
		 */
		static function show( $field, $saved ) {

			global $post;
			
			$field_class = Pretty_Metabox::get_class_name( $field );
			$meta        = call_user_func( array( $field_class, 'meta' ), $post->ID, $saved, $field );

			// Call separated methods for displaying each type of field
			$field_html = call_user_func( array( $field_class, 'html' ), $meta, $field );

			echo $field_html;

		}

		/**
		 * Show field HTML For taxonomies
		 *
		 * @param array $field
		 * @param string  $meta
		 * @since  1.1.0
		 * @return string
		 */
		static function show_taxonomy( $field, $meta ) {

			$field_class = Pretty_Metabox::get_class_name( $field );

			// Call separated methods for displaying each type of field
			$field_html = call_user_func( array( $field_class, 'html' ), $meta, $field );

			echo $field_html;

		}

		/**
		 * Show field HTML For Users
		 *
		 * @param array $field
		 * @param string  $meta
		 * @since  1.1.0
		 * @return string
		 */
		static function show_user( $field, $meta ) {

			$field_class = Pretty_Metabox::get_class_name( $field );

			// Call separated methods for displaying each type of field
			$field_html = call_user_func( array( $field_class, 'html' ), $meta, $field );

			echo $field_html;

		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function html( $meta, $field ) {
			return '';
		}

		/**
		 * Get meta value
		 *
		 * @param int   $post_id
		 * @param bool  $saved
		 * @param array $field
		 * @since  1.0.0
		 *
		 * @return mixed
		 */
		static function meta( $post_id, $saved, $field ) {

			$meta = get_post_meta( $post_id, $field['id'], true );

			// Use $field['std'] only when the meta box hasn't been saved (i.e. the first time we run)
			$meta = ( ! $saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;
			
			// Escape attributes for non-wysiwyg fields
			if ( $field['type'] !== 'editor'  )
				$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );

			return $meta;

		}

		/**
		 * Set value of meta before saving into database
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 * @since  1.0.0
		 *
		 * @return int
		 */
		static function value( $new, $old, $post_id, $field ) {
			return $new;
		}

		/**
		 * Save meta value
		 *
		 * This function checks which $method has been picked
		 * and then decides whether is a post, a taxonomy term
		 * or a user is being updated.
		 *
		 * @param $new
		 * @param $old
		 * @param $object_id
		 * @param $field
		 * @param $method
		 * @since  1.0.0
		 *
		 * @return  void
		 */
		static function save( $new, $old, $object_id, $field, $method = 'post' ) {

			$name = $field['id'];

			if( $method == 'taxonomy' ) {

				Pretty_Taxonomy_Metabox::delete_tax_meta( $object_id, $name );
			    if ( $new === '' || $new === array() ) 
			      return;
			    
			    Pretty_Taxonomy_Metabox::update_tax_meta( $object_id, $name, $new );

			} elseif ( $method == 'user' ) {

				if ( '' === $new || array() === $new ) {
					delete_user_meta( $object_id, $name );
					return;
				}

				update_user_meta( $object_id, $name, $new );

			} else {

				if ( '' === $new || array() === $new ) {
					delete_post_meta( $object_id, $name );
					return;
				}

				update_post_meta( $object_id, $name, $new );

			}
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return array
		 */
		static function normalize_field( $field ){
			return $field;
		}

		/**
		 * Show custom markup before the markup of the field.
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function before_field( $field, $has_tabs ) {

			$field_class = Pretty_Metabox::get_class_name( $field );
			$output = null;

			if(call_user_func( array( $field_class, 'display_wrapper' ) )) {

				$output = '<tr id="'.$field['id'].'" class="'.$field['classes'].' wppf-type-'.$field['type'].'">';

					if(!$has_tabs) {
						$output .= '<td class="label">';
							$output .= '<label>'.$field['name'].'</label>';
							$output .= '<p class="description">'.$field['sub'].'</p>';
						$output .= '</td>';
					}

					if($has_tabs) {
						$output .= '<td class="field has-tab field-type-'.$field['type'].'">';
							$output .= '<label>'.$field['name'].'</label>';
							$output .= '<p class="description">'.$field['sub'].'</p>';
					} else {
						$output .= '<td class="field field-type-'.$field['type'].'">';
					}

			}

			if(call_user_func( array( $field_class, 'display_empty_wrapper' ) )) {
				$output = '</tbody></table>';
				$output .= '<div class="wppf-external-wrapper">';
			}

			return $output;
		}

		/**
		 * Show custom markup before the markup of the field.
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function before_field_simple( $field ) {

			$field_class = Pretty_Metabox::get_class_name( $field );
			$output = null;

			$output = '<div id="'.$field['id'].'" class="wppf-field-wrapper-simple field-'.$field['type'].' '.$field['classes'].'">';
				$output .= '<label>'.$field['name'].'</label>';
				$output .= '<p class="description">'.$field['sub'].'</p>';
				$output .= '<div class="field field-type-'.$field['type'].'">';

			return $output;
		}

		/**
		 * Show custom markup after the markup of the field.
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function after_field( $field, $has_tabs ) {

			$field_class = Pretty_Metabox::get_class_name( $field );
			$output = null;
			
			if(call_user_func( array( $field_class, 'display_wrapper' ) )) {
				$output = '<p class="description">'.$field['desc'].'</p>';
				$output .= '</td></tr>';
			}

			if(call_user_func( array( $field_class, 'display_empty_wrapper' ) )) {
				$output = '</div>';
				$output .= '<table class="options-table-responsive wppf-options-table" style="display: table;">';
					$output .= '<tbody>';
			}

			return $output;
		}

		/**
		 * Show custom markup after the markup of the field.
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function after_field_simple( $field ) {

			$field_class = Pretty_Metabox::get_class_name( $field );
			
			$output .= '</div></div>';
			
			return $output;
		}

		/**
		 * Show custom markup before the markup of the field.
		 * Function reserved for taxonomy pages only.
		 * 
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function before_field_taxonomy( $field, $form_type ) {
			
			$output = null;
			
			if($form_type == 'edit') {
				$output .= '<tr class="form-field">';
					$output .= '<th scope="row"><label for="'.$field['id'].'">'.$field['name'].'</label></th>';
					$output .= '<td>';
						$output .= '<div class="field field-type-'.$field['type'].'">';
			} else {
				$output .= '<div id="'.$field['id'].'" class="form-field wppf-field-wrapper-taxonomy field-'.$field['type'].' '.$field['classes'].'">';
					$output .= '<label for="'.$field['id'].'">'.$field['name'].'</label>';
					$output .= '<div class="field field-type-'.$field['type'].'">';
			}

			return $output;

		}

		/**
		 * Show custom markup after the markup of the field.
		 * Function reserved for taxonomy pages only.
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function after_field_taxonomy( $field, $form_type ) {
			
			$output = '<p class="description">'.$field['desc'].'</p>';
			$output .= '</div></div>';
			
			if($form_type == 'edit') {
				$output .= '</td></tr>';
			}

			return $output;

		}

		/**
		 * Show custom markup before the markup of the field.
		 * Function reserved for user pages only.
		 * 
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function before_field_user( $field ) {
			
			$output = null;
			
			$output .= '<tr class="form-field">';
				$output .= '<th scope="row"><label for="'.$field['id'].'">'.$field['name'].'</label></th>';
				$output .= '<td>';
					$output .= '<div class="field field-type-'.$field['type'].'">';

			return $output;

		}

		/**
		 * Show custom markup after the markup of the field.
		 * Function reserved for user pages only.
		 *
		 * @param array $field
		 * @since  1.0.0
		 * @return string
		 */
		static function after_field_user( $field ) {
			
			$output = '<p class="description">'.$field['desc'].'</p>';
			$output .= '</div></div>';
			
			$output .= '</td></tr>';

			return $output;

		}

	}

}