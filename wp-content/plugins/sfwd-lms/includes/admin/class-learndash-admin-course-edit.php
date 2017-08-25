<?php

if (!class_exists('Learndash_Admin_Course_Edit')) {
	class Learndash_Admin_Course_Edit {
		
		private $courses_post_type = 'sfwd-courses';
	    
		function __construct() {
			// Hook into the on-load action for our post_type editor
			add_action( 'load-post.php', 			array( $this, 'on_load_groups') );
			add_action( 'load-post-new.php', 		array( $this, 'on_load_groups') );
		}
		
		function on_load_groups() {
			global $typenow;	// Contains the same as $_GET['post_type]
			
			if ( (empty( $typenow ) ) || ( $typenow != $this->courses_post_type ) )  return;

			wp_enqueue_script( 
				'learndash-admin-binary-selector-script', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-binary-selector'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', 
				array( 'jquery' ),
				LEARNDASH_VERSION,
				true
			);

			wp_enqueue_style( 
				'learndash-admin-binary-selector-style', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-binary-selector'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', 
				array( ),
				LEARNDASH_VERSION
			);
		
			// Add Metabox and hook for saving post metabox
			add_action( 'add_meta_boxes', 			array( $this, 'add_course_metaboxes' ) );
			add_action( 'save_post', 				array( $this, 'save_course_metaboxes') );

		}
		
		/**
		 * Register Groups meta box for admin
		 *
		 * Managed enrolled groups, users and group leaders
		 * 
		 * @since 2.1.2
		 */
		function add_course_metaboxes() {
			
			add_meta_box(
				'learndash_grouse_groups',
				sprintf( _x( 'LearnDash %s Group', 'LearnDash Course Group', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
				array( $this, 'course_groups_page_box' ),
				$this->courses_post_type
			);
		}


		/**
		 * Prints content for Groups meta box for admin
		 *
		 * @since 2.1.2
		 * 
		 * @param  object $post WP_Post
		 * @return string 		meta box HTML output
		 */
		function course_groups_page_box( $post ) {
			global $wpdb;

			//echo "post<pre>"; print_r($post); echo "</pre>";
			//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');
			//error_log('post<pre>'. print_r($post, true) .'</pre>');

			$course_id = $post->ID;

			// Use nonce for verification
			wp_nonce_field( 'learndash_course_groups_nonce_'. $course_id, 'learndash_course_groups_nonce' );
			
			?>
			
			<div id="learndash_course_groups_page_box" class="learndash_course_groups_page_box">
			<?php
				$ld_binary_selector_course_groups = new Learndash_Binary_Selector_Course_Groups(
					array(
						'course_id'		=>	$course_id,
						'selected_ids'	=>	learndash_get_course_groups( $course_id, true ),
						'search_posts_per_page' => 100
					)
				);
				$ld_binary_selector_course_groups->show();
			?>
			</div>
			<?php 
		}

		function save_course_metaboxes( $post_id ) {
			// verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if ( ! isset( $_POST['learndash_course_groups_nonce'] ) || ! wp_verify_nonce( $_POST['learndash_course_groups_nonce'], 'learndash_course_groups_nonce_'. $post_id ) ) {
				return;
			}

			// Check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			if ( $this->courses_post_type != $_POST['post_type'] ) {
				return;
			}
			
			//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>');
			
			if ( ( isset( $_POST['learndash_course_groups'] ) ) && ( isset( $_POST['learndash_course_groups'][$post_id] ) ) && ( !empty( $_POST['learndash_course_groups'][$post_id] ) ) ) {
				$course_groups = (array)json_decode( stripslashes( $_POST['learndash_course_groups'][$post_id] ) );
				//error_log('course_groups<pre>'. print_r($course_groups, true) .'</pre>');
				learndash_set_course_groups( $post_id, $course_groups );
			}
		}
		// End of functions
	}
}
