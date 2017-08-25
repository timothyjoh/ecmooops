<?php
if ( !class_exists( 'Learndash_Admin_Settings_Upgrades_User_Meta_Quizzes' ) ) {
	class Learndash_Admin_Settings_Upgrades_User_Meta_Quizzes extends Learndash_Admin_Settings_Data_Upgrades {
		
		public static $instance = null;
		
		function __construct() {
			self::$instance =& $this;

			$this->data_slug = 'user-meta-quizzes';
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
					<button class="learndash-data-upgrades-button button button-primary" data-nonce="<?php echo wp_create_nonce( 'learndash-data-upgrades-'. $this->data_slug .'-'. get_current_user_id() ); ?>" data-slug="<?php echo $this->data_slug ?>"><?php printf( _x( 'Upgrade User %s Data', 'Export User Quiz Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?></button></td>
				<td class="learndash-data-upgrades-status-container" style="width: 80%">
					<p><?php printf( _x('This upgrade will sync your existing user data for %s into a new database table for better reporting. (Required)', 'This upgrade will sync your existing user data for quiz into a new database table for better reporting. (Required)', 'learndash'), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ) ?></p>
						
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
						LEFT JOIN ". $wpdb->prefix ."usermeta as user_meta_quiz_progress ON users.ID=user_meta_quiz_progress.user_id 
							AND user_meta_quiz_progress.meta_key='_sfwd-quizzes'
						LEFT JOIN ". $wpdb->prefix ."usermeta as user_meta_quiz_upgraded ON users.ID=user_meta_quiz_upgraded.user_id 
							AND user_meta_quiz_upgraded.meta_key='". $this->meta_key ."'
						WHERE 1=1 
							AND user_meta_quiz_progress.meta_key IS NOT null
							AND (user_meta_quiz_upgraded.meta_value IS NULL OR user_meta_quiz_upgraded.meta_value != 'COMPLETE')";

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

							$this->convert_user_meta_quizzes_progress_to_activity( intval( $user_id ) );					

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
		
		function convert_user_meta_quizzes_progress_to_activity( $user_id = 0 ) {
			global $wpdb;

			if ( empty( $user_id ) ) return;
			
			$user_quiz_upgraded = get_user_meta($user_id, $this->meta_key, true);
			if ( $user_quiz_upgraded == 'COMPLETE' ) return;
			$user_quiz_upgraded = intval( $user_quiz_upgraded );
			
			$user_course_ids_used = array();

			$user_meta_quizzes_progress = get_user_meta( $user_id, '_sfwd-quizzes', true );
			if ( ( !empty( $user_meta_quizzes_progress ) ) && ( is_array( $user_meta_quizzes_progress ) ) ) {
				$user_meta_quizzes_data_new = array();

				foreach( $user_meta_quizzes_progress as $idx => $quiz_data ) {
					
					// Need a way to seek to a specific key starting point in an array
					if ( $user_quiz_upgraded > intval( $idx ) )
						continue;
					
					$user_quiz_upgraded = $idx;
					
					// We store the idx of the array item so we can pick up later.
					update_user_meta($user_id, $this->meta_key, $user_quiz_upgraded);
				
					if ( $this->out_of_timer() ) {
						return;
					}
					
					$quiz_post = get_post( intval( $quiz_data['quiz'] ) );
					if (!$quiz_post) continue;

					unset($quiz_data['started']);
					unset($quiz_data['completed']);

					if ( ( !isset( $quiz_data['completed'] ) ) || ( empty( $quiz_data['completed'] ) ) ) {
						if ( ( isset( $quiz_data['time'] ) ) && ( !empty( $quiz_data['time'] ) ) ) {
							$quiz_data['completed'] = $quiz_data['time'];
						}
					}

					if ( ( !isset( $quiz_data['started'] ) ) || ( empty( $quiz_data['started'] ) ) ) {
						if ( ( isset( $quiz_data['time'] ) ) && ( !empty( $quiz_data['time'] ) ) ) {
							if ( isset( $quiz_data['timespent'] ) ) {
								$quiz_data['started'] = abs(intval( $quiz_data['time'] - round( $quiz_data['timespent'], 0 ) ) );
							}
						}
					}

					$quiz_data_meta = $quiz_data;
					
					// Remove many fields that we either don't need or are duplicate of the main table columns
					unset($quiz_data_meta['quiz']);
					unset($quiz_data_meta['pro_quizid']);
					unset($quiz_data_meta['time']);
					unset($quiz_data_meta['completed']);
					unset($quiz_data_meta['started']);
					//unset($quiz_data_meta['graded']);
					
					if ($quiz_data_meta['rank'] == '-')
						unset($quiz_data_meta['rank']);

					learndash_update_user_activity(
						array(
							'post_id'				=>	$quiz_data['quiz'],
							'user_id'				=>	$user_id,
							'activity_type'			=>	'quiz',
							'activity_status'		=>	$quiz_data['pass'] ,
							'activity_started'		=>	$quiz_data['started'],
							'activity_completed'	=>	$quiz_data['completed'], 
							'activity_meta'			=>	$quiz_data_meta,
						)
					); 
										
					/*
					$course_id = learndash_get_course_id( $quiz_data['quiz'] );
					if ( !empty( $course_id ) ) {
						$course_post = get_post( $course_id );
						if ( ( !empty( $course_post ) ) && ( $course_post instanceof WP_Post ) ) {
							
							$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
							if ( isset( $course_progress[$course_post->ID] ) ) {
								$course_progress = $course_progress[$course_post->ID];
							} else {
								$course_progress = null;
							}

							learndash_course_user_update_activity(
								$course_post->ID, 
								'completed', 
								$quiz_data['completed'], 
								$user_id,
								true,
								array( 'course_progress' => $course_progress )
							);
						}
					}
					*/
				}
			}
			update_user_meta($user_id, $this->meta_key, 'COMPLETE');
		}
		
	}
}
