<?php

if (!class_exists('Learndash_Admin_User_Profile_Edit')) {
	class Learndash_Admin_User_Profile_Edit {
		
		function __construct() {
			// Hook into the on-load action for our post_type editor
			add_action( 'load-profile.php', 		array( $this, 'on_load_user_profile') );
			add_action( 'load-user-edit.php', 		array( $this, 'on_load_user_profile') );

			add_action( 'show_user_profile', 		array( $this, 'show_user_profile') );
			add_action( 'edit_user_profile', 		array( $this, 'show_user_profile') );

			add_action( 'personal_options_update',  array( $this, 'save_user_profile' ), 1 );
			add_action( 'edit_user_profile_update', array( $this, 'save_user_profile' ), 1 );

		}
		
		function on_load_user_profile() {

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
			
			// We need to load the wpProQuiz_admin.js in order to show the quiz statistics on the profile. 
			//$WpProQuiz_Controller_Admin = new WpProQuiz_Controller_Admin();
			//$WpProQuiz_Controller_Admin->enqueueScript();
		}
		
		function show_user_profile( $user ) {
			$this->show_user_courses( $user );
			$this->show_user_groups( $user );
			$this->show_leader_groups( $user );
			
			//LD_QuizPro::showModalWindow();
		}
				
		function save_user_profile( $user_id ) {
			if ( ! learndash_is_admin_user( ) ) {
				return;
			}
			
			if ( ( isset( $_POST['learndash_user_courses'] ) ) && ( isset( $_POST['learndash_user_courses'][$user_id] ) ) && ( !empty( $_POST['learndash_user_courses'][$user_id] ) ) ) {
				if ( ( isset( $_POST['learndash_user_courses-'. $user_id .'-nonce'] ) ) && ( !empty( $_POST['learndash_user_courses-'. $user_id .'-nonce'] ) ) ) {
					if (wp_verify_nonce( $_POST['learndash_user_courses-'. $user_id .'-nonce'], 'learndash_user_courses-'.$user_id )) {
						$user_courses = (array)json_decode( stripslashes( $_POST['learndash_user_courses'][$user_id] ) );
						learndash_user_set_enrolled_courses( $user_id, $user_courses );
					}
				}
			}

			if ( ( isset( $_POST['learndash_user_groups'] ) ) && ( isset( $_POST['learndash_user_groups'][$user_id] ) ) && ( !empty( $_POST['learndash_user_groups'][$user_id] ) ) ) {
				if ( ( isset( $_POST['learndash_user_groups-'. $user_id .'-nonce'] ) ) && ( !empty( $_POST['learndash_user_groups-'. $user_id .'-nonce'] ) ) ) {
					if (wp_verify_nonce( $_POST['learndash_user_groups-'. $user_id .'-nonce'], 'learndash_user_groups-'.$user_id )) {

						$user_groups = (array)json_decode( stripslashes( $_POST['learndash_user_groups'][$user_id] ) );
						learndash_set_users_group_ids( $user_id, $user_groups );
					}
				}
			}

			if ( ( isset( $_POST['learndash_leader_groups'] ) ) && ( isset( $_POST['learndash_leader_groups'][$user_id] ) ) && ( !empty( $_POST['learndash_leader_groups'][$user_id] ) ) ) {
				if ( ( isset( $_POST['learndash_leader_groups-'. $user_id .'-nonce'] ) ) && ( !empty( $_POST['learndash_leader_groups-'. $user_id .'-nonce'] ) ) ) {
					if (wp_verify_nonce( $_POST['learndash_leader_groups-'. $user_id .'-nonce'], 'learndash_leader_groups-'.$user_id )) {
						$user_groups = (array)json_decode( stripslashes( $_POST['learndash_leader_groups'][$user_id] ) );
						learndash_set_administrators_group_ids( $user_id, $user_groups );
					}
				}
			}
			
			learndash_save_user_course_complete( $user_id );
		}
		
		function show_user_courses( $user ) {
			// First check is the user viewing the screen is admin...
			if ( learndash_is_admin_user( ) ) {
				// Then is the user profile being viewed is not admin
				if ( learndash_is_admin_user( $user->ID ) ) {
					
					/**
					 * See example if 'learndash_override_course_auto_enroll' filter 
					 * https://bitbucket.org/snippets/learndash/kon6y
					 *
					 * @since 2.3
					 */
					if ( apply_filters('learndash_override_course_auto_enroll', true, $user->ID )) {
						?>
						<h3><?php echo sprintf( _x('User Enrolled %s', 'User Enrolled Courses', 'learndash'), LearnDash_Custom_Label::get_label( 'courses' ) )  ?></h3>
						<p><?php _e('Administrators are automatically enrolled in all Courses.', 'learndash') ?></p>
						<?php
						return;
					} 
				}
				
				$ld_binary_selector_user_courses = new Learndash_Binary_Selector_User_Courses(
					array(
						'user_id'				=>	$user->ID,
						'selected_ids'			=>	learndash_user_get_enrolled_courses( $user->ID, array(), true ),
						'search_posts_per_page' => 100
					)
				);
				$ld_binary_selector_user_courses->show();
			}
		}

		function show_user_groups( $user ) {
			if ( learndash_is_admin_user( ) ) {
				$ld_binary_selector_user_groups = new Learndash_Binary_Selector_User_Groups(
					array(
						'user_id'				=>	$user->ID,
						'selected_ids'			=>	learndash_get_users_group_ids( $user->ID, true ),
						'search_posts_per_page' => 100
					)
				);
				$ld_binary_selector_user_groups->show();
			}
		}

		function show_leader_groups( $user ) {
			if ( learndash_is_admin_user() ) {
				if ( learndash_is_group_leader_user( $user->ID ) ) {
					$ld_binary_selector_leader_groups = new Learndash_Binary_Selector_Leader_Groups(
						array(
							'user_id'				=>	$user->ID,
							'selected_ids'			=>	learndash_get_administrators_group_ids( $user->ID, true ),
							'search_posts_per_page' => 100
						)
					);
					$ld_binary_selector_leader_groups->show();
				}
			}
		}

		// End of functions
	}
}
