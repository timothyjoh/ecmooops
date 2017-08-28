<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPPF_Gallery_Field' ) ) {

	class WPPF_Gallery_Field extends Pretty_Fields {

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts() {
			
			// This function loads in the required media files for the media manager.
	        wp_enqueue_media();

		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 * @return string
		 */
		static function html( $meta, $field ) {

			global $post;

			$output = null;

			$all_images = maybe_unserialize( get_post_meta( $post->ID, $field['id'], true ) );
			$all_ids = '';

			$output = '<div class="wppf-gallery-container">';

				$output .= '<div class="wppf-thumbs-container">';
					$output .= '<ul id="wppf-gallery-thumbs-'.$field['id'].'">';

						if($all_images) :

							foreach ($all_images as $index => $image) {
								
								$output .= '<li>';
									$output .= '<input type="hidden" name="wppf-gallery-'.$field['id'].'['.$index.'][id]" value="'.$image['id'].'">';
									$output .= '<input type="hidden" name="wppf-gallery-'.$field['id'].'['.$index.'][url]" value="'.$image['url'].'">';
									$output .= '<img src="'.$image['url'].'">';
								$output .= '</li>';
							
								$all_ids .= $image['id'] . ',';

							}

						endif;
					
					$output .= '</ul>';
				$output .= '</div>';

				$all_ids = rtrim($all_ids,',');

				$output .= '<div class="wppf-actions">';
					$output .='<div class="action-left">';
						// media gallery manager trigger button
						$output .= '<a href="#" id="wppf-gallery-'.$field['id'].'" class="wppf-open-gallery button button-primary wppf-gallery" data-list="#wppf-gallery-thumbs-'.$field['id'].'" data-ids="'.$all_ids.'" data-title="'.$field['frame_title'].'" data-button="'.$field['frame_button'].'">'.$field['button_create'].'</a> ';
					$output .= '</div>';
					$output .='<div class="action-right">';
						// media gallery manager delete gallery button
						$output .= '<a href="#" id="wppf-gallery-delete-'.$field['id'].'" class="wppf-delete-gallery button button-secondary wppf-gallery" data-list="#wppf-gallery-thumbs-'.$field['id'].'" data-del="'.$field['delete_message'].'">'.$field['button_delete'].'</a>';
					$output .='</div>';
					$output .='<div class="clear"></div>';
				$output .='</div>';

			$output .= '</div>';
			
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
				'button_create'  => __('Add/Edit Gallery', 'wpum'),
				'button_delete'  => __('Delete Gallery', 'wpum'),
				'frame_title'    => __('Select or upload images to create a gallery', 'wpum'),
				'frame_button'   => __('Insert gallery', 'wpum'),
				'delete_message' => __('Are you sure you want to delete the gallery?', 'wpum')				
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

			$get_field_name = 'wppf-gallery-'.$field['id'];
			$wppf_images = ( isset( $_POST[$get_field_name] ) ? $_POST[$get_field_name] : '' );
			
			// Prepare new value
			$new = array();

			if(isset($wppf_images) && !empty($wppf_images) ) {
				
				foreach ($wppf_images as $image) {
					$new[] = array(
						'id'  => intval($image['id']),
						'url' => esc_url($image['url'])
					);
				}

			}

			return maybe_serialize($new);

		}

	}

}
