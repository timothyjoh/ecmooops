<?php
if ( !class_exists( 'Learndash_Admin_Data_Upgrades_User_Meta_Courses' ) ) {
	class Learndash_Admin_Data_Upgrades_User_Meta_Courses extends Learndash_Admin_Settings_Data_Upgrades {
		
		public static $instance = null;

		function __construct() {
			self::$instance =& $this;
			
			$this->data_slug = 'user-meta-courses';
			$this->meta_key = 'ld-upgraded-'. $this->data_slug;

			add_filter( 'learndash_admin_settings_upgrades_register_actions', array( $this, 'register_upgrade_action' ) );
		}
		
		public static function getInstance() {
		    if ( ! isset( self::$_instance ) ) {
		        self::$_instance = new self();
		    }
		    return self::$_instance;
		}
		
		function register_upgrade_action( $upgrade_actions = array() ) {
			// Add ourselved to the upgrade actions
			$upgrade_actions[$this->data_slug] = array(
				'class'		=>	get_class( $this ),
				'instance'	=>	$this,
				'slug'		=>	$this->data_slug
			);
			
			return $upgrade_actions;
		}
		
		function show_upgrade_action() {
			?>
			<tr id="learndash-data-upgrades-container-<?php echo $this->data_slug ?>" class="learndash-data-upgrades-container">
				<td class="learndash-data-upgrades-button-container" style="width:20%">
					<button class="learndash-data-upgrades-button button button-primary" data-nonce="<?php echo wp_create_nonce( 'learndash-data-upgrades-'. $this->data_slug .'-'. get_current_user_id() ); ?>" data-slug="<?php echo $this->data_slug ?>"><?php printf( _x( 'Upgrade User %s Data', 'Export User Course Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></button></td>
				<td class="learndash-data-upgrades-status-container" style="width: 80%">
					<p><?php printf( _x('This upgrade will sync your existing user data for %s into a new database table for better reporting. (Required)', 'This upgrade will sync your existing user data for course into a new database table for better reporting. (Required)', 'learndash'), LearnDash_Custom_Label::label_to_lower( 'course' ) ) ?></p>
					<p class="description"><?php echo $this->get_last_run_info(); ?></p>	
						
					<div style="display:none;" class="meter learndash-data-upgrades-status">
						<div class="progress-meter">
							<span class="progress-meter-image"></span>
						</div>
						<div class="progress-label"></div>
					</div>
				</td>
			</tr>
			<?php
		}
				
		/**
		 * Class method for the AJAX update logic
		 * This function will determine what users need to be converted. Then the course and quiz functions
		 * will be called to convert each individual user data set.
		 *
		 * @since 2.3
		 * 
		 * @param  array 	$data 		Post data from AJAX call
		 * @return array 	$data 		Post data from AJAX call
		 */
		function process_upgrade_action( $data = array() ) {
			global $wpdb;
			
			$this->init_process_times();

			if ( ( isset( $data['nonce'] ) ) && ( !empty( $data['nonce'] ) ) ) {
				if ( wp_verify_nonce( $data['nonce'], 'learndash-data-upgrades-'. $this->data_slug .'-'. get_current_user_id() ) ) {
			
					if ( ( isset( $data['init'] ) ) && ( $data['init'] == true ) ) {
						$this->clear_previous_run_meta();
					}
			
					$sql_str = "SELECT ID FROM ". $wpdb->prefix ."users as users
						LEFT JOIN ". $wpdb->prefix ."usermeta as user_meta_course_progress ON users.ID=user_meta_course_progress.user_id 
							AND user_meta_course_progress.meta_key='_sfwd-course_progress'
						LEFT JOIN ". $wpdb->prefix ."usermeta as user_meta_course_upgraded ON users.ID=user_meta_course_upgraded.user_id 
							AND user_meta_course_upgraded.meta_key='". $this->meta_key ."'
						WHERE 1=1
							AND user_meta_course_progress.meta_key IS NOT null
							AND (user_meta_course_upgraded.meta_value IS NULL OR user_meta_course_upgraded.meta_value != 'COMPLETE')";

					$process_users = $wpdb->get_col( $sql_str );

					if ( ( isset( $data['init'] ) ) && ( $data['init'] == 1 ) ) {
						$users_count = count_users();
						$data['total_count'] 	= 	intval($users_count['total_users']);
					}
			
					$data['result_count'] 		= 	$data['total_count'] - count( $process_users );
					$data['progress_percent'] 	= 	($data['result_count'] / $data['total_count']) * 100;
					$data['progress_label']		= 	sprintf( __('%d of %s Users', 'learndash'), $data['result_count'], $data['total_count']);

					if ( !isset( $data['init'] ) ) {
						foreach( $process_users as $user_id ) {
							
							$this->convert_user_meta_courses_progress_to_activity( intval( $user_id ) );
							
							if ( $this->out_of_timer() ) {
								break;
							}
						}
					} else {
						// Remove it
						unset( $data['init'] );
					}
				}
			} 
			
			// If we are at 100% then we update the internal data settings so other parts of LD know the upgrade has been run
			if ( ( isset( $data['progress_percent'] ) ) && ( $data['progress_percent'] == 100 ) ) {
				$this->set_last_run_info( $data );
				$data['last_run_info'] = $this->get_last_run_info();
			}
			
			return $data;
		}
		
		function convert_user_meta_courses_progress_to_activity( $user_id = 0 ) {
			global $wpdb;

			if ( empty( $user_id ) ) return;
			
			$user_course_upgraded = get_user_meta($user_id, $this->meta_key, true);
			if ( $user_course_upgraded == 'COMPLETE' ) return;
			$user_course_upgraded = intval( $user_course_upgraded );
			
			$user_course_ids_used = array();

			$user_meta_courses_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
			if ( ( !empty( $user_meta_courses_progress ) ) && ( is_array( $user_meta_courses_progress ) ) ) {
				// We sort the course progress array because we may need to save our place and need to know
				// where we loft off
				ksort($user_meta_courses_progress);
				
				foreach( $user_meta_courses_progress as $course_id => $course_data ) {

					// Need a way to seek to a specific key starting point in an array
					if ( $user_course_upgraded > $course_id ) 
						continue;

					$user_course_upgraded = $course_id;
					update_user_meta($user_id, $this->meta_key, $user_course_upgraded);
				
					if ( $this->out_of_timer() ) {
						return;
					}

					$user_course_ids_used[$course_id] = $course_id;
					
					$user_course_completed = get_user_meta($user_id, 'course_completed_'. $course_id, true);
					
					$user_course_access_from = get_user_meta($user_id, 'course_'. $course_id .'_access_from', true);
					
					if ( empty( $user_course_access_from ) ) {
						// IF the course access from date is empty. BUT there is a course 
						// complete date we set the start to the complete date
						if ( !empty( $user_course_completed ) ) {
							$user_course_access_from = $user_course_completed;
						} else {
							// If we don't have the start time. And there is no complete time. 
							// Then we take the post_time from the course.
							$course_post = get_post( $course_id );
							if ( ( !empty( $course_post ) ) && ( $course_post instanceof WP_Post ) ) {
								if ( ( property_exists( $course_post, 'post_date_gmt' ) ) && ( !empty( $course_post->post_date_gmt ) )) {
									$user_course_access_from = strtotime( $course_post->post_date_gmt );
								}
							}
						}
					}
					
					if ( !empty( $user_course_access_from ) ) {
						learndash_update_user_activity(
							array(
								'post_id'			=>	$course_id,
								'user_id'			=>	$user_id,
								'activity_type'		=>	'access',
								'activity_started'	=>	$user_course_access_from
							)
						);
					}
					
					//$user_course_access_expired_on = ld_course_access_expires_on( $course_id, $user_id );
					//if (!empty($user_course_access_expired_on))
					//	learndash_course_user_update_activity($course_id, 'access_expired', $user_course_access_expired_on, $user_id); 

					// First add the main Course entry. 
					if (!empty( $user_course_access_from ) ) {
						
						$course_args = array(
							'post_id'			=>	$course_id,
							'activity_type'		=>	'course',
							'user_id'			=>	$user_id,
							'activity_started'	=>	$user_course_access_from, 
							'activity_meta'		=>	array( 
														'steps_total'		=>	intval( $course_data['total'] ),
														'steps_completed'	=>	intval( $course_data['completed'] ),
													)
						);
						
						
						// Finally if there is a Course Complete date we add it. 
						if ( !empty( $user_course_completed ) ) {
							$course_args['activity_completed'] = $user_course_completed;							
						}
						
						if ( ( intval($course_data['total'] ) ) == (intval($course_data['completed'] ) ) ) {
							$course_args['activity_status']	= true;
						}
						learndash_update_user_activity( $course_args ); 
					}

					// Then loop over Lessons
					if ( ( isset( $course_data['lessons'] ) ) && ( !empty( $course_data['lessons'] ) ) ) {
						foreach( $course_data['lessons'] as $lesson_id => $lesson_complete ) {
							$lesson_args = array(
								'post_id'			=>	$lesson_id,
								'user_id'			=>	$user_id,
								'activity_type'		=>	'lesson',
								'activity_meta'		=>	array( 
															'steps_total'		=>	intval( $course_data['total'] ),
															'steps_completed'	=>	intval( $course_data['completed'] ),
														)
							);

							if (!empty( $user_course_access_from ) ) {
								$lesson_args['activity_started']	= $user_course_access_from;
							}
							
							if ( ( $lesson_complete == true) && ( !empty( $user_course_completed ) ) ) {
								$lesson_args['activity_completed'] 	= $user_course_completed;
								$lesson_args['activity_status'] 	= true;
							}
							learndash_update_user_activity( $lesson_args );
						}
					}

					// Then loop over Topics
					if ( ( isset( $course_data['topics'] ) ) && ( !empty( $course_data['topics'] ) ) ) {
						foreach( $course_data['topics'] as $lesson_id => $lessons_topics ) {
							if ( !empty( $lessons_topics ) ) {
								foreach( $lessons_topics as $topic_id => $topic_complete ) {

									$topic_args = array(
										'post_id'			=>	$topic_id,
										'user_id'			=>	$user_id,
										'activity_type'		=>	'topic',
										'activity_meta'		=>	array( 
																	'steps_total'		=>	intval( $course_data['total'] ),
																	'steps_completed'	=>	intval( $course_data['completed'] ),
																)
									);


									if (!empty( $user_course_access_from ) ) {
										$topic_args['activity_started'] = $user_course_access_from;
									}
									
									if ( ( $topic_complete == true) && ( !empty( $user_course_completed ) ) ) {
										$topic_args['activity_completed'] 	= $user_course_completed;
										$topic_args['activity_status'] 		= true;
									}
									
									learndash_update_user_activity( $topic_args );
								}
							}
						}
					}
				}
			}
					
			// Finally we go through the user's meta again to grab the random course access items. These would be there 
			// If the user was granted access but didn't actually start a lesson/quiz etc. 
			$user_courses_access_sql = $wpdb->prepare( "SELECT user_id, meta_key, meta_value as course_access_from FROM ". $wpdb->prefix ."usermeta WHERE user_id=%d AND meta_key LIKE %s", $user_id, 'course_%_access_from');
			$user_courses_access = $wpdb->get_results( $user_courses_access_sql );
			
			if ( !empty( $user_courses_access ) ) {
				foreach( $user_courses_access as $user_course_access ) {
			
					if ( ( property_exists ( $user_course_access, 'meta_key' ) ) && ( !empty( $user_course_access->meta_key ) ) ) {
						$user_course_access->course_id = str_replace('course_', '', $user_course_access->meta_key);
						$user_course_access->course_id = str_replace('_access_from', '', $user_course_access->course_id);

						if (!isset($user_course_ids_used[$user_course_access->course_id])) {
							//if ( ( property_exists ( $user_course_access, 'course_access_from' ) ) && ( !empty( $user_course_access->course_access_from ) ) ) {
							//	$user_course_access->course_access_from_ymd = date( 'Y-m-d H:i:s', $user_course_access->course_access_from );
							//} else {
							//	$user_course_access->course_access_from_ymd = '0000-00-00 00:00:00';
							//}

							learndash_update_user_activity(
								array(
									'post_id'			=>	$user_course_access->course_id,
									'user_id'			=>	$user_id,
									'activity_type'		=>	'access',
									'activity_started'	=>	$user_course_access->course_access_from, 
								)
							); 

						}
					}
				}
			}
			
			update_user_meta($user_id, $this->meta_key, 'COMPLETE');
		}
		
	}
}
