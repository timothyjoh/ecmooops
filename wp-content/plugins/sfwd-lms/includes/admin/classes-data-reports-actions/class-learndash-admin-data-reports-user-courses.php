<?php
if ( !class_exists( 'Learndash_Admin_Data_Reports_Courses' ) ) {
	class Learndash_Admin_Data_Reports_Courses extends Learndash_Admin_Settings_Data_Reports {
		
		public static $instance = null;
		private $data_slug = 'user-courses';

		private $data_headers = array();
		private $report_filename = '';

		private $transient_key = '';
		private $transient_data = array();

		private $csv_parse;
		
		function __construct() {
			self::$instance =& $this;
			
			add_filter( 'learndash_admin_report_register_actions', array( $this, 'register_report_action' ) );
		}
		
		public static function getInstance() {
		    if ( ! isset( self::$_instance ) ) {
		        self::$_instance = new self();
		    }
		    return self::$_instance;
		}
		
		function register_report_action( $report_actions = array() ) {
			// Add ourselved to the upgrade actions
			$report_actions[$this->data_slug] = array(
				'class'		=>	get_class( $this ),
				'instance'	=>	$this,
				'slug'		=>	$this->data_slug
			);
			
			$this->set_report_headers();
			
			return $report_actions;
		}
		
		function show_report_action() {
			?>
			<tr id="learndash-data-reports-container-<?php echo $this->data_slug ?>" class="learndash-data-reports-container">
				<td class="learndash-data-reports-button-container" style="width:20%">
					<button class="learndash-data-reports-button button button-primary" data-nonce="<?php echo wp_create_nonce( 'learndash-data-reports-'. $this->data_slug .'-'. get_current_user_id() ); ?>" data-slug="<?php echo $this->data_slug ?>"><?php printf( _x( 'Export User %s Data', 'Export User Course Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></button></td>
				<td class="learndash-data-reports-status-container" style="width: 80%">
					
					<div style="display:none;" class="meter learndash-data-reports-status">
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
		function process_report_action( $data = array() ) {
			global $wpdb;
			
			$this->init_process_times();
			
			if ( !isset( $data['total_count'] ) )
				$data['total_count'] = 0;

			if ( !isset( $data['result_count'] ) )
				$data['result_count'] = 0;

			if ( !isset( $data['progress_percent'] ) )
				$data['progress_percent'] = 0;
			
			if ( !isset( $data['progress_label'] ) )
				$data['progress_label']	= '';
			
			$_DOING_INIT = false;
						
			require_once( LEARNDASH_LMS_PLUGIN_DIR . 'includes/vendor/parsecsv.lib.php' );
			$this->csv_parse = new lmsParseCSV();
			
			if ( ( isset( $data['nonce'] ) ) && ( !empty( $data['nonce'] ) ) ) {
				if ( wp_verify_nonce( $data['nonce'], 'learndash-data-reports-'. $this->data_slug .'-'. get_current_user_id() ) ) {
					$this->transient_key = $this->data_slug .'_'. $data['nonce'];
						
					// On the 'init' (the first call via AJAX we load up the transient with the user_ids)
					if ( ( isset( $data['init'] ) ) && ( $data['init'] == 1 ) ) {
						$_DOING_INIT = true;
						
						unset( $data['init'] );

						$this->transient_data = array();
						
						if ( ( isset( $data['group_id'] ) ) && ( !empty( $data['group_id'] ) ) ) {
							$this->transient_data['users_ids'] = learndash_get_groups_user_ids( intval( $data['group_id'] ) );
							$this->transient_data['posts_ids'] = learndash_group_enrolled_courses( intval( $data['group_id'] ) );
						} else {
							$this->transient_data['users_ids'] = learndash_get_report_user_ids();
							$this->transient_data['posts_ids'] = '';
						}
						$this->transient_data['total_users'] = count( $this->transient_data['users_ids'] );
						
						$this->set_report_filenames( $data );
						$this->report_filename = ABSPATH . $this->transient_data['report_filename'];
												
						$data['report_download_link'] = $this->transient_data['report_url'];
						$data['total_count'] = $this->transient_data['total_users'];
					
						// Clear out any previous file
						$reports_fp = fopen( $this->report_filename, 'w' );
						fclose($reports_fp);
											
						set_transient( $this->transient_key, $this->transient_data, MINUTE_IN_SECONDS );
						
						$this->send_report_headers_to_csv();
						
					} else {
						$this->transient_data = get_transient( $this->transient_key );
						
						$this->report_filename = ABSPATH . $this->transient_data['report_filename'];
					}
								
					if ( !empty( $this->transient_data['users_ids'] ) ) {
											
						// If we are doing the initial 'init' then we return so we can show the progress meter.			
						if ( $_DOING_INIT != true) {
						
							$course_query_args = array(
								'orderby'		=>	'title',
								'order'			=>	'ASC',
								'nopaging'		=>	true
							);

							$activity_query_args = array(
								'post_types' 		=> 	'sfwd-courses',
								'activity_types'	=>	'course',
								'activity_status'	=>	array('NOT_STARTED' , 'IN_PROGRESS', 'COMPLETED'),
								'orderby_order'		=>	'users.ID, posts.post_title',
								'date_format'		=>	'F j, Y H:i:s',
								'nopaging'			=>	true
							);
							
							$course_progress_data = array();
							
							foreach( $this->transient_data['users_ids'] as $user_id_idx => $user_id ) {
						
								unset( $this->transient_data['users_ids'][$user_id_idx] );
								set_transient( $this->transient_key, $this->transient_data, MINUTE_IN_SECONDS );
							
								$report_user = get_user_by('id', $user_id);
								if ( $report_user !== false ) {
								
									if ( ( !isset( $this->transient_data['posts_ids'] ) ) || ( empty( $this->transient_data['posts_ids'] ) ) ) {
										$post_ids = learndash_user_get_enrolled_courses( intval( $user_id ), $course_query_args, true );
									} else {
										$post_ids = $this->transient_data['posts_ids'];
									}

									if ( !empty( $post_ids ) ) {

										$activity_query_args['user_ids'] = array( $user_id );
										$activity_query_args['post_ids'] = $post_ids;
									
										if ( !empty( $activity_query_args['post_ids'] ) ) {
											$activity_query_args['activity_status'] = array('NOT_STARTED', 'IN_PROGRESS', 'COMPLETED');
										} else {
											$activity_query_args['activity_status'] = array('IN_PROGRESS', 'COMPLETED');
										}
									
										$user_courses_reports = learndash_reports_get_activity( $activity_query_args );
										if ( !empty( $user_courses_reports['results'] ) ) {
											foreach( $user_courses_reports['results'] as $result ) {											
												$row = array();
											
												foreach( $this->data_headers as $header_key => $header_data ) {
												
													if ( ( isset( $header_data['display'] ) ) && ( !empty( $header_data['display'] ) ) ) {
														$row[$header_key] = call_user_func_array( $header_data['display'], array(
																'header_value'	=>	$header_data['default'],
																'header_key'	=>	$header_key, 
																'item' 			=> 	$result, 
																'report_user' 	=> 	$report_user,
															) 
														);
													} else if ( ( isset( $header_data['default'] ) ) && ( !empty( $header_data['default'] ) ) ) {
														$row[$header_key] = $header_data['default'];
													} else {
														$row[$header_key] = '';
													}
												}

												if ( !empty($row ) ) {
													$course_progress_data[] = $row;
												}
											}
										} else {
											$row = array();
										
											foreach( $this->data_headers as $header_key => $header_data ) {
											
												if ( ( isset( $header_data['display'] ) ) && ( !empty( $header_data['display'] ) ) ) {
													$row[$header_key] = call_user_func_array( $header_data['display'], array(
															'header_value'	=>	$header_data['default'],
															'header_key'	=>	$header_key,
															'item' 			=> 	new stdClass(), 
															'report_user' 	=> 	$report_user,
														) 
													);
												} else if ( ( isset( $header_data['default'] ) ) && ( !empty( $header_data['default'] ) ) ) {
													$row[$header_key] = $header_data['default'];
												} else {
													$row[$header_key] = '';
												}
											}

											if ( !empty($row ) ) {
												$course_progress_data[] = $row;
											}
										
										}
									}
								}
														
								if ( $this->out_of_timer() ) {
									break;
								}
							}

							if ( !empty( $course_progress_data ) ) {
								$this->csv_parse->save( $this->report_filename, $course_progress_data, true );
							}
						} 
						
						$data['result_count'] 		= 	$data['total_count'] - count( $this->transient_data['users_ids'] );
						$data['progress_percent'] 	= 	( $data['result_count'] / $data['total_count'] ) * 100;
						$data['progress_label']		= 	sprintf( __('%d of %s Users', 'learndash'), $data['result_count'], $data['total_count']);
			
					}
				} 
			}
			
			return $data;
		}

		function set_report_headers() {
			$this->data_headers									=	array();
			$this->data_headers['user_id']  					= 	array( 
																		'label'		=>	'user_id',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
			$this->data_headers['user_name'] 					= 	array( 
																		'label'		=>	'name',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);

			$this->data_headers['user_email'] 					=	array( 
																		'label'		=>	'email',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
																	
			$this->data_headers['course_id'] 					= 	array( 
																		'label'		=>	'course_id',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
			$this->data_headers['course_title'] 				= 	array( 
																		'label'		=>	'course_title',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);

			$this->data_headers['course_steps_completed'] 		= 	array( 
																		'label'		=>	'steps_completed',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
			$this->data_headers['course_steps_total'] 			= 	array( 
																		'label'		=>	'steps_total',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
			$this->data_headers['course_completed'] 			= 	array( 
																		'label'		=>	'course_completed',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
			$this->data_headers['course_completed_on']			=	array( 
																		'label'		=>	'course_completed_on',
																		'default'	=>	'',
																		'display'	=>	array( $this, 'report_column' )
																	);
		
			$this->data_headers = apply_filters('learndash_data_reports_headers', $this->data_headers, $this->data_slug );
		}

		function send_report_headers_to_csv() {
			if ( !empty( $this->data_headers ) ) {
				$this->csv_parse->save( $this->report_filename, array( wp_list_pluck( $this->data_headers, 'label' ) ), false );
			}
		}

		function set_report_filenames( $data ) {
			$wp_upload_dir = wp_upload_dir();
			$wp_upload_dir['basedir'] = str_replace('\\', '/', $wp_upload_dir['basedir']);
		
			//$ld_file_part = '/learndash/learndash_reports_'. $this->data_slug .'_' . str_replace( 'ld_data_reports_', '', $this->transient_key ) .'.csv';
			$ld_file_part = '/learndash/learndash_reports_'.  str_replace( array('ld_data_reports_', '-'), array('', '_'), $this->transient_key ) .'.csv';
			
			$ld_wp_upload_filename = $wp_upload_dir['basedir'] . $ld_file_part;
			if ( wp_mkdir_p( dirname( $ld_wp_upload_filename ) ) === false ) {
				$data['error_message'] = __("ERROR: Cannot create working folder. Check that the parent folder is writable", 'learndash') ." ". $ld_wp_upload_dir;
				return $data;
			}
			file_put_contents( trailingslashit( dirname( $ld_wp_upload_filename ) ) .'index.php', '// nothing to see here');
		
			// Because we on;y want to store the relative path 
			$ld_wp_upload_filename = str_replace( ABSPATH, '', $ld_wp_upload_filename );
		
			$this->transient_data['report_filename'] = $ld_wp_upload_filename;

			//$this->transient_data['report_url'] = $wp_upload_dir['baseurl'] . $ld_file_part;
			$this->transient_data['report_url'] = add_query_arg(
				array(
					'data-slug' 		=> 	$this->data_slug,
					'data-nonce'		=>	$data['nonce'],
					'report-download' 	=> 	1
				),
				admin_url() //get_option('home')
			);
		}
		
		
		function report_column( $column_value = '', $column_key, $report_item, $report_user ) {
			
			switch( $column_key ) {
				case 'user_id':
					if ( $report_user instanceof WP_User ) {
						$column_value = $report_user->ID;
					}
					break;

				case 'user_name': 
					if ( $report_user instanceof WP_User ) {
						$column_value =  $report_user->display_name;
					}
					break;

				case 'user_email':	
					if ( $report_user instanceof WP_User ) {
						$column_value = $report_user->user_email;
					}
					break;

				case 'course_id':
					if ( property_exists( $report_item, 'post_id' ) ) {
						$column_value = $report_item->post_id;
					}
					break;
				
				case 'course_title':
					if ( property_exists( $report_item, 'post_title' ) ) {
						$column_value = $report_item->post_title;
					}
					break;
				
				case 'course_steps_total':
					$column_value = '0';
					if ( ( property_exists( $report_item, 'activity_meta' ) ) && ( !empty( $report_item->activity_meta ) ) ) {
						if ( ( isset( $report_item->activity_meta['steps_total'] ) ) && (!empty( $report_item->activity_meta['steps_total'] ) ) ) {
							$column_value = $report_item->activity_meta['steps_total'];
						}
					} else {
						$column_value = learndash_get_course_steps_count( $report_item->post_id );
					}
					break;

				case 'course_steps_completed':	
					$column_value = '0';
					if ( ( property_exists( $report_item, 'activity_meta' ) ) && ( !empty( $report_item->activity_meta ) ) ) {
						if ( ( isset( $report_item->activity_meta['steps_completed'] ) ) && (!empty( $report_item->activity_meta['steps_completed'] ) ) ) {
							$column_value = $report_item->activity_meta['steps_completed'];
						}
					}
					break;

				case 'course_completed':
					$column_value = _x('NO', 'Course Complete Report label: NO', 'learndash');
					if ( property_exists( $report_item, 'activity_status' ) ) {
						if ( $report_item->activity_status == true ) {
							$column_value = _x('YES', 'Course Complete Report label: YES', 'learndash');
						} 	
					}
					break;
			
				case 'course_completed_on':	
					if ( ( property_exists( $report_item, 'activity_completed' ) ) && ( !empty( $report_item->activity_completed ) ) ) {
						return learndash_adjust_date_time_display( $report_item->activity_completed, 'Y-m-d' );
					}
					break;
				
				default:	
					break;
			}
			
			return $column_value;
		}
		
		
		// End of functions
	}
}


