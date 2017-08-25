<?php
/**
 * Handles assignment uploads and includes helper functions for assignments
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Assignments
 */



/**
 * Upload, delete, and mark assignments as complete
 *
 * @since 2.1.0
 */
function learndash_assignment_process_init() {
	
	if ( isset( $_POST['uploadfile'] ) && isset( $_POST['post'] ) ) {
		$post_id = $_POST['post'];
		$file = $_FILES['uploadfiles'];
		$name = $file['name'];

		if ( ! empty( $file['name'][0] ) ) {
			$file_desc = learndash_fileupload_process( $file, $post_id );
			$file_name = $file_desc['filename'];
			$file_link = $file_desc['filelink'];
			$params = array(
				'filelink' => $file_link, 
				'filename' => $file_name,
			);
		}
	}
	
	if ( ! empty( $_GET['learndash_delete_attachment'] ) ) {
		$post = get_post( $_GET['learndash_delete_attachment'] );

		if ( $post->post_type != 'sfwd-assignment' ) {
			return;
		}

		$current_user_id = get_current_user_id();

		if ( ( learndash_is_admin_user( $current_user_id ) ) || ( learndash_is_group_leader_of_user( $current_user_id, $post->post_author ) ) ) {
			wp_delete_post( $post->ID );
			return;
		}
	}
	
	if ( ! empty( $_POST['attachment_mark_complete'] ) && ! empty( $_POST['userid'] ) ) {
		$lesson_id = $_POST['attachment_mark_complete'];
		$current_user_id = get_current_user_id();
		$user_id = $_POST['userid'];

		if ( ( learndash_is_admin_user( $current_user_id ) ) || ( learndash_is_group_leader_of_user( $current_user_id, $user_id ) ) ) {
			learndash_approve_assignment( $user_id, $lesson_id );
		}
	}
}

add_action( 'parse_request', 'learndash_assignment_process_init', 1 );



/**
 * Get a users assignments
 * 
 * @since 2.1.0
 * 
 * @param  int 	 $post_id
 * @param  int 	 $user_id
 * @return array WP_Post Assigment objects
 */
function learndash_get_user_assignments( $post_id, $user_id ) {
	$opt = array(
		'post_type' => 'sfwd-assignment', 
		'posts_per_page' => - 1, 
		'author' => $user_id, 
		'meta_key' => 'lesson_id', 
		'meta_value' => $post_id,
	);

	return get_posts( $opt );
}



/**
 * Migrate assignments from post meta to Assignments custom post type
 * 
 * @since 2.1.0
 */
function learndash_assignment_migration() {
	if ( ! learndash_is_admin_user( ) ) {
		return;
	}
	
	global $wpdb;
	$old_assignment_ids = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'sfwd_lessons-assignment'" );
	
	if ( ! empty( $old_assignment_ids ) && ! empty( $old_assignment_ids[0] ) ) {

		foreach( $old_assignment_ids as $post_id ) {
			$assignment_meta_data = get_post_meta( $post_id, 'sfwd_lessons-assignment', true );

			if ( ! empty( $assignment_meta_data ) && ! empty( $assignment_meta_data['assignment'] ) ) {
				$assignment_data = $assignment_meta_data['assignment'];
				$post = get_post( $post_id );
				$assignment_posts_ids = array();

				if ( ! empty( $assignment_data ) ) {
					$error = false;

					foreach( $assignment_data as $k => $v ) {

						if ( empty( $v['file_name'] ) ) {
							continue;
						}
						
						$fname = $v['file_name'];
						$dest = $v['file_link'];
						$username = $v['user_name'];
						$dispname = $v['disp_name'];
						$file_path = $v['file_path'];

						if ( ! empty( $v['user_name'] ) ) {
							$user = get_user_by( 'login', $v['user_name'] );
						}

						$course_id = learndash_get_course_id( $post->ID );

						$assignment_meta = array(
							'file_name' => $fname, 
							'file_link' => $dest, 
							'user_name' => $username, 
							'disp_name' => $dispname, 
							'file_path' => $file_path, 
							'user_id' => @$user->ID, 
							'lesson_id' => $post->ID, 
							'course_id' => $course_id, 
							'lesson_title' => $post->post_title, 
							'lesson_type' => $post->post_type, 
							'migrated' => '1',
						);
					   
						$assignment = array(
							'post_title' => $fname, 
							'post_type' => 'sfwd-assignment', 
							'post_status' => 'publish', 
							'post_content' => "<a href='" . $dest . "' target='_blank'>" . $fname . '</a>', 
							'post_author' => @$user->ID,
						);

						$assignment_post_id = wp_insert_post( $assignment );

						if ( $assignment_post_id ) {
							$assignment_posts_ids[] = $assignment_post_id;

							foreach( $assignment_meta as $key => $value ) {
								update_post_meta( $assignment_post_id, $key, $value );
							}

							if ( learndash_is_assignment_approved( $assignment_post_id ) === true ) {
								learndash_approve_assignment_by_id( $assignment_post_id );
							}
						} else {
							$error = true;

							foreach( $assignment_posts_ids as $assignment_posts_id ) {
								wp_delete_post( $assignment_posts_id, true );
							}

							break;
						}

					}

					if ( ! $error ) {
						global $wpdb;
						$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = 'sfwd_lessons-assignment_migrated' WHERE meta_key = 'sfwd_lessons-assignment' AND post_id = '$post_id'" );
					}

				}
			}
		}
	}
}

add_action( 'admin_init', 'learndash_assignment_migration' );



/**
 * Get list of all assignments
 *
 * @todo  first argument not used
 * @since 2.1.0
 * 
 * @param  object 	 $post 	not used
 * @return array     $posts array of post objects
 */
function learndash_get_assignments_list( $post ) {
	$posts = get_posts( 'post_type=sfwd-assignment&posts_per_page=-1' );

	if ( ! empty( $posts ) ) {

		foreach( $posts as $key => $p ) {
			$meta = get_post_meta( $p->ID, '', true );

			foreach( $meta as $meta_key => $value ) {

				if ( is_string( $value ) || is_numeric( $value ) ) {
					$posts[ $key ]->{$meta_key} = $value;
				} else if ( is_string( $value[0] ) || is_numeric( $value[0] ) ) {
					$posts[ $key ]->{$meta_key} = $value[0];
				}
				
				if ( $meta_key == 'file_path' ) {
					$posts[ $key ]->{$meta_key} = rawurldecode( $posts[ $key ]->{$meta_key} );
				}

			}
		}
	}
	
	return $posts;
}



/**
 * Function to handle assignment uploads
 * Takes Post ID, filename as arguments(We don't want to store BLOB data there)
 *
 * @todo  How is this different from learndash_assignment_process_init() ?
 *
 * @since 2.1.0
 * 
 * @param  int $post_id 
 * @param  int $fname    filename
 */
function learndash_upload_assignment_init( $post_id, $fname ) {
	//Initialize an empty array
	global $wp;

	if ( ! function_exists( 'wp_get_current_user' ) ) {
		include ABSPATH . 'wp-includes/pluggable.php';
	}

	$new_assignmnt_meta = array();
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$dispname = $current_user->display_name;
	$userid = $current_user->ID;
	$url_link_arr = wp_upload_dir();
	$url_link = $url_link_arr['baseurl'];
	$dir_link = $url_link_arr['basedir'];
	$file_path = $dir_link . '/assignments/';
	$url_path = $url_link . '/assignments/' . $fname;

	if ( file_exists( $file_path . $fname ) ) {
		$dest = $url_path;
	} else {
		return;
	}
	
	update_post_meta( $post_id, 'sfwd_lessons-assignment', $new_assignmnt_meta );
	$post = get_post( $post_id );
	$course_id = learndash_get_course_id( $post->ID );

	$assignment_meta = array(
		'file_name' => $fname, 
		'file_link' => $dest, 
		'user_name' => $username, 
		'disp_name' => $dispname, 
		'file_path' => rawurlencode( $file_path . $fname ), 
		'user_id' => $current_user->ID, 
		'lesson_id' => $post->ID, 
		'course_id' => $course_id, 
		'lesson_title' => $post->post_title, 
		'lesson_type' => $post->post_type,
	);

	$points_enabled = learndash_get_setting( $post, 'lesson_assignment_points_enabled' );

	if ( $points_enabled == 'on' ) {
		$assignment_meta['points'] = 'Pending';
	}

	$assignment = array(
		'post_title' => $fname, 
		'post_type' => 'sfwd-assignment', 
		'post_status' => 'publish', 
		'post_content' => "<a href='" . $dest . "' target='_blank'>" . $fname . '</a>', 
		'post_author' => $current_user->ID,
	);

	$assignment_post_id = wp_insert_post( $assignment );

	if ( $assignment_post_id ) {
		foreach ( $assignment_meta as $key => $value ) {
			update_post_meta( $assignment_post_id, $key, $value );
		}

		/**
		 * Run action hook after assignment is uploaded
		 * 
		 * @since 2.2
		 *
		 * @param int 		$assignment_post_id 	Newly created assignment post ID which the
		 *                                 	 		assignment is uploaded to
		 * @param array 	$assignment_meta 		Assignment meta data
		 */
		do_action( 'learndash_assignment_uploaded', $assignment_post_id, $assignment_meta );
	}
	
	$auto_approve = learndash_get_setting( $post, 'auto_approve_assignment' );

	if ( ! empty( $auto_approve ) ) {
		learndash_approve_assignment( $current_user->ID, $post_id );

		// assign full points if auto approve & points are enabled
		if ( $points_enabled == 'on' ) {
			$points = learndash_get_setting( $post, 'lesson_assignment_points_amount' );
			update_post_meta( $assignment_post_id, 'points', intval( $points ) );
		}

		learndash_get_next_lesson_redirect( $post );
	}
}



/**
 * Ensure comments are open for assignments
 * 
 * @since 2.1.0
 * 
 * @param bool        	$open    Whether the current post is open for comments.
 * @param int|obj		$post_id The post ID or WP_Post object.
 * @return int|obj		$post_id The post ID or WP_Post object.
 */
function learndash_assignments_comments_open( $open, $post_id ) {
	$post = get_post( $post_id );

	if ( empty( $open ) && @$post->post_type == 'sfwd-assignment' ) {
		if ( is_numeric( $post_id ) ) {
			global $wpdb;
			$wpdb->query( "UPDATE $wpdb->posts SET comment_status = 'open' WHERE ID = '" . $post_id . "'" );
			$open = true;
		}
	}
	
	return $open;
}

add_filter( 'comments_open', 'learndash_assignments_comments_open', 10, 2 );



/**
 * Enable comments when adding new assignment
 * 
 * @since 2.1.0
 * 
 * @param  array $data post data
 * @return array $data post data
 */
function learndash_assignments_comments_on( $data ) {
	if ( $data['post_type'] == 'sfwd-assignment' ) {
		$data['comment_status'] = 'open';
	}

	return $data;
}

add_filter( 'wp_insert_post_data', 'learndash_assignments_comments_on' );



/**
 * Ensure clean filename on upload
 * 
 * @since 2.1.0
 * 
 * @param  string $string file name
 * @return string         clean file name
 */
function learndash_clean_filename( $string ) {
	$string = htmlentities( $string, ENT_QUOTES, 'UTF-8' );
	$string = preg_replace( '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string );
	$string = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
	$string = preg_replace( array('~[^0-9a-z.]~i', '~[ -]+~'), ' ', $string );
	$string = str_replace( ' ', '_', $string );
	return trim( $string, ' -' );
}



/**
 * Upload files
 * 
 * @since 2.1.0
 * 
 * @param  array 	$uploadfiles
 * @param  int 		$post_id     assignment id
 * @return array    file description
 */
function learndash_fileupload_process( $uploadfiles, $post_id ) {


	if ( is_array( $uploadfiles ) ) {
		
		foreach( $uploadfiles['name'] as $key => $value ) {
			// look only for uploded files
			if ( $uploadfiles['error'][ $key ] == 0 ) {
				
				$filetmp = $uploadfiles['tmp_name'][ $key ];

				//clean filename
				$filename = learndash_clean_filename( $uploadfiles['name'][ $key ] );

				//extract extension
				if ( ! function_exists( 'wp_get_current_user' ) ) {
					include ABSPATH . 'wp-includes/pluggable.php';
				}

				// get file info
				// @fixme: wp checks the file extension....
				$filetype = wp_check_filetype( basename( $filename ), null );
				$filetitle = preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
				$filename = $filetitle . '.' . $filetype['ext'];
				$upload_dir = wp_upload_dir();
				$upload_dir_base = $upload_dir['basedir'];
				$upload_url_base = $upload_dir['baseurl'];
				$upload_dir_path = $upload_dir_base . '/assignments';
				$upload_url_path = $upload_url_base . '/assignments/';

				if ( ! file_exists( $upload_dir_path ) ) {
					mkdir( $upload_dir_path );
				}

				/**
				 * Check if the filename already exist in the directory and rename the
				 * file if necessary
				 */
				$i = 0;

				while ( file_exists( $upload_dir_path . '/' . $filename ) ) {
					$i++;
					$filename = $filetitle . '_' . $i . '.' . $filetype['ext'];
				}

				$filedest = $upload_dir_path . '/' . $filename;
				$destination = $upload_url_path . $filename;

				/**
				 * Check write permissions
				 */
				if ( ! is_writeable( $upload_dir_path ) ) {
					die( __( 'Unable to write to directory. Is this directory writable by the server?', 'learndash' ) );
					return;
				}

				/**
				 * Save temporary file to uploads dir
				 */
				if ( ! @move_uploaded_file( $filetmp, $filedest ) ) {
					echo( "Error, the file $filetmp could not moved to : $filedest " );
					continue;
				}

				/**
				 * Add upload meta to database
				 *
				 */
				learndash_upload_assignment_init( $post_id, $filename, $filedest );
				$file_desc = array();
				$file_desc['filename'] = $filename;
				$file_desc['filelink'] = $destination;
				return $file_desc;
			}
		}

	}
}



/**
 * Does lesson have assignments
 * 
 * @since 2.1.0
 * 
 * @param  object $post WP_Post assignment
 * @return bool
 */
function lesson_hasassignments( $post ) {
	$post_id = $post->ID;
	$assign_meta = get_post_meta( $post_id, '_' . $post->post_type, true );

	if ( ! empty( $assign_meta[ $post->post_type . '_lesson_assignment_upload'] ) ) {
		$val = $assign_meta[ $post->post_type . '_lesson_assignment_upload'];

		if ( $val == 'on' ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}



/**
 * Add 'Approve' next to certain selects on assignment edit screen in admin
 *
 * @todo  check if needed, jQuery selector seems incorrect
 * 
 * @since 2.1.0
 */
function learndash_assignment_bulk_actions() {
	global $post;
	if ( ! empty( $post->post_type ) && $post->post_type == 'sfwd-assignment' ) {
		$approve_text = __('Approve', 'learndash'); 
		
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('approve_assignment').text('<?php echo $approve_text ?>').appendTo("select[name='action']");
					jQuery('<option>').val('approve_assignment').text('<?php echo $approve_text ?>').appendTo("select[name='action2']");
				});
			</script>
		<?php
	}
}

add_action( 'admin_footer', 'learndash_assignment_bulk_actions' );



/**
 * Handle approval of assignments in bulk
 * 
 * @since 2.1.0
 */
function learndash_assignment_bulk_actions_approve() {
	
	if ( ( ( isset( $_REQUEST['post'] ) ) && ( ! empty( $_REQUEST['post'] ) ) && (is_array( $_REQUEST['post'] ) ) )
	  && ( ( isset( $_REQUEST['post_type'] ) ) && ( $_REQUEST['post_type'] == 'sfwd-assignment' ) ) ) {

		$action = '';
  		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
  			$action = esc_attr( $_REQUEST['action'] );

  		else if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
  			$action = esc_attr( $_REQUEST['action2'] );
	
		else if ( ( isset( $_REQUEST['ld_action'] ) ) && ( $_REQUEST['ld_action'] == 'approve_assignment') )  
  			$action = 'approve_assignment';
	
		//error_log('_REQUEST<pre>'. print_r($_REQUEST, true) .'</pre>');
		
		if ( $action == 'approve_assignment' ) {
			if ( ( isset( $_REQUEST['post'] ) ) && ( !empty( $_REQUEST['post'] ) ) ) {
				if ( !is_array( $_REQUEST['post'] ) ) 
					$assignments = array($_REQUEST['post']);
				else
					$assignments = $_REQUEST['post'];

				//error_log('assignments<pre>'. print_r($assignments, true) .'</pre>');
				//return;


				foreach( $assignments as $assignment_id ) {

					$assignment_post = get_post( $assignment_id );
					if ( ( !empty( $assignment_post ) ) && ( $assignment_post instanceof WP_Post) && ( $assignment_post->post_type == 'sfwd-assignment' ) ) {
						
						$user_id = $assignment_post->post_author;
						$lesson_id = get_post_meta( $assignment_post->ID, 'lesson_id', true );
							
						if ( learndash_assignment_is_points_enabled( $assignment_id ) === true ) {
					
							if ( ( isset( $_REQUEST['assignment_points'] ) ) && ( isset( $_REQUEST['assignment_points'][$assignment_id] ) ) ) {
								$assignment_points = abs( intval( $_REQUEST['assignment_points'][$assignment_id] ) ); 
						
								$assignment_settings_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
								if (!empty( $assignment_settings_id ) ) {
									$max_points = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_amount' );
								} 
						
								// Double check the assiged points is NOT larger than max points. 
								if ( $assignment_points > $max_points ) 
									$assignment_points = $max_points;
								
								//error_log('assignment_id['. $assignment_id .'] points['. $assignment_points .']');
								
								update_post_meta( $assignment_id, 'points', $assignment_points );
							}
						}
		
						learndash_approve_assignment( $user_id, $lesson_id, $assignment_id );
					}
				}
			}
			
			if ( ! empty( $_REQUEST['ret_url'] ) ) {
				header( 'Location: ' . rawurldecode( $_REQUEST['ret_url'] ) );
				exit;
			}
		}
	}
}

add_action( 'load-edit.php', 'learndash_assignment_bulk_actions_approve' );



/**
 * Approve assignment by id for user
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 * @return bool
 */
function learndash_approve_assignment_by_id( $assignment_id ) {
	$assignment_post = get_post( $assignment );
	$user_id = $assignment_post->post_author;
	$lesson_id = get_post_meta( $assignment_post->ID, 'lesson_id', true );
	return learndash_approve_assignment( $user_id, $lesson_id );
}



/**
 * Mark assignment true with user id and lesson id
 * 
 * @since 2.1.0
 * 
 * @param  int $user_id
 * @param  int $lesson_id
 * @return bool
 */
function learndash_approve_assignment( $user_id, $lesson_id, $assignment_post_id = 0 ) {

	/**
	 * Filter whether assignmnent should be approved or not
	 * 
	 * @since 2.1.0
	 * 
	 * @param  bool
	 */
	$learndash_approve_assignment = apply_filters( 'learndash_approve_assignment', true, $user_id, $lesson_id );

	if ( $learndash_approve_assignment ) {
		$learndash_process_mark_complete = learndash_process_mark_complete( $user_id, $lesson_id );
		//echo $learndash_process_mark_complete;
		
		if ( $learndash_process_mark_complete ) {
			 /*	
			  * @todo This query needs to be reworked to NOT query all posts with that meta_key. Better off using WP_Query. 
			  */	
			global $wpdb;
			$assignment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lesson_id' AND meta_value = %d", $lesson_id ) );
			
			
			foreach( $assignment_ids as $assignment_id ) {
				if ( ( intval($assignment_post_id) != 0 ) && ( intval( $assignment_post_id ) != intval( $assignment_id ) ) )
					continue;
				
				$assignment = get_post( $assignment_id );
				if ( $assignment->post_author == $user_id ) {
					learndash_assignment_mark_approved( $assignment_id );

					/**
					 * Run action hook after assignment is approved
					 * 
					 * @since 2.2
					 *
					 * @param int $assignment_id 	Newly created assignment post ID which 
					 *                              the assignment is uploaded to
					 */
					do_action( 'learndash_assignment_approved', $assignment_id );
				}
			}
		}

		return $learndash_process_mark_complete;
	}
}



/**
 * Update assignments post meta with approval status
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 */
function learndash_assignment_mark_approved( $assignment_id ) {
	update_post_meta( $assignment_id, 'approval_status', 1 );
}



/**
 * Get assignments approval status
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 * @return bool
 */
function learndash_is_assignment_approved_by_meta( $assignment_id ) {
	return get_post_meta( $assignment_id, 'approval_status', true );
}



/**
 * Adds inline actions to assignments on post listing hover in admin
 * 
 * @since 2.1.0
 * 
 * @param  array 	$actions 	post actions
 * @param  object 	$post   	WP_Post assignment
 * @return array 	$actions 	post actions
 */
function learndash_assignment_inline_actions( $actions, $post ) {
	if ( $post->post_type == 'sfwd-assignment' ) {
		$download_link = get_post_meta( $post->ID, 'file_link', true );
		$actions['download_assignment'] = "<a href='" . $download_link . "' target='_blank'>" . __( 'Download', 'learndash' ) . '</a>';
		$learndash_assignment_approval_link = learndash_assignment_approval_link( $post->ID );

		$points_enabled = learndash_assignment_is_points_enabled( $post->ID );
		
		if ( $learndash_assignment_approval_link && ! $points_enabled ) {
			$actions['approve_assignment'] = "<a href='" . $learndash_assignment_approval_link . "' >" . __( 'Approve', 'learndash' ) . '</a>';
		}
	}

	return $actions;
}

add_filter( 'post_row_actions', 'learndash_assignment_inline_actions', 10, 2 );



/**
 * Restrict assignment listings view to group leader only
 * 
 * @since 2.1.0
 * 
 * @param  object 	$query 	WP_Query
 * @return object 	$query 	WP_Query
 */
function learndash_restrict_assignment_listings( $query ) {
	global $pagenow, $typenow;
	$q_vars = & $query->query_vars;

	if ( !is_admin() ) return;
	if ( $pagenow != 'edit.php' ) return;
	if ( !$query->is_main_query() ) return;
	if ( empty( $typenow ) ) return;


	if ( $typenow == 'sfwd-assignment' )  {
		
		//error_log('in '. __FUNCTION__ );
		//error_log('_GET<pre>'. print_r($_GET, true) .'</pre>');

		if ( !learndash_is_admin_user( ) ) {
			$user_id = get_current_user_id();

			if ( learndash_is_group_leader_user( $user_id ) ) {
				$group_ids = learndash_get_administrators_group_ids( $user_id );
				$course_ids = array();
				$user_ids = array();

				if ( ! empty( $group_ids ) && is_array( $group_ids ) ) {
					foreach( $group_ids as $group_id ) {
						$group_course_ids = learndash_group_enrolled_courses( $group_id );
						if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) {
							$course_ids = array_merge( $course_ids, $group_course_ids );
						}

						$group_users = learndash_get_groups_user_ids( $group_id );
						if ( ! empty( $group_users ) && is_array( $group_users ) ) {
							foreach( $group_users as $group_user_id ) {
								$user_ids[ $group_user_id ] = $group_user_id;
							}
						}
					}
				}
			
				if ( ! empty( $course_ids ) && count( $course_ids ) ) {
					
					if (!isset( $q_vars['meta_query'] ) )
						$q_vars['meta_query'] = array();
					
					$q_vars['meta_query'][] = array(
						'key'     => 'course_id',
						'value'   => $course_ids,
						'compare' => 'IN',
					);
				} 

				if ( ! empty( $user_ids ) && count( $user_ids ) ) {
					$q_vars['author__in'] = $user_ids;
				} else {
					$q_vars['author__in'] = - 2;
				}
			}
		}
	}
}

//add_filter( 'parse_query', 'learndash_restrict_assignment_listings' );



/**
 * Check if assignment is completed
 * 
 * @since 2.1.0
 * 
 * @param  int 		$assignment_id 		
 * @return bool
 */
function learndash_is_assignment_approved( $assignment_id ) {
	$assignment = get_post( $assignment_id );

	if ( empty( $assignment->ID ) ) {
		return '';
	}
	
	$lesson_id = learndash_get_lesson_id( $assignment->ID );
	
	if ( empty( $lesson_id ) ) {
		return '';
	}
	
	$lesson_completed = learndash_is_lesson_notcomplete( $assignment->post_author, array( $lesson_id => 1 ) );

	if ( empty( $lesson_completed ) ) {
		return true;
	} else {
		return false;
	}
}



/**
 * Get assignment approval url
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 * @return string assignment approval url
 */
function learndash_assignment_approval_link( $assignment_id ) {
	if ( ! learndash_is_assignment_approved_by_meta( $assignment_id ) ) {
		$approve_url = admin_url( 'edit.php?post_type=sfwd-assignment&ld_action=approve_assignment&post[]=' . $assignment_id . '&ret_url=' . rawurlencode( @$_SERVER['REQUEST_URI'] ) );
		return $approve_url;
	} else {
		return '';
	}
}



/**
 * Register assignment metabox
 * 
 * @since 2.1.0
 */
function learndash_assignment_metabox() {
	add_meta_box( 'learndash_assignment_metabox', __( 'Assignment', 'learndash' ), 'learndash_assignment_metabox_content', 'sfwd-assignment', 'advanced', 'high' );
}

add_action( 'add_meta_boxes', 'learndash_assignment_metabox' );



/**
 * Add Approval Link to assignment metabox
 * 
 * @since 2.1.0
 */
function learndash_assignment_metabox_content() {
	global $post;

	// point handling
	$assignment_settings_id = intval( get_post_meta( $post->ID, 'lesson_id', true ) );
	$points_enabled = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_enabled' );

	if ( $points_enabled == 'on' && is_numeric( $assignment_settings_id ) ) {
		$max_points = intval( learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_amount' ) );
		$current_points = intval( get_post_meta( $post->ID, 'points', true ) );
		$update_text = learndash_is_assignment_approved_by_meta( $post->ID ) ? __( 'Update', 'learndash' ) : __( 'Update & Approve', 'learndash' );

		echo "<p>";
		echo "<label for='assignment-points'>" . sprintf( __( 'Awarded Points (Out of %d):', 'learndash' ), $max_points ) . "</label><br />";
		echo "<input name='assignment-points' type='number' min=0 max='{$max_points}' value='{$current_points}'>";
		echo "<p><input name='save' type='submit' class='button button-primary button-large' id='publish' value='{$update_text}'></p>";
		echo "</p>";
	} else {

		// this approval link is actually used for approving assignment in bulk. needs to be optimized to approve only
		// one given assignment
		$learndash_assignment_approval_link = learndash_assignment_approval_link( $post->ID );

		if ( $learndash_assignment_approval_link ) {
			echo "<a href='" . $learndash_assignment_approval_link . "' class='button button-primary'>" . __( 'Approve', 'learndash' ) . '</a>';
		}
	}

	// link handling
	$file_link = get_post_meta( $post->ID, 'file_link', true );

	echo "<a href='" . $file_link . "' target='_blank' class='button'>" . __( 'Download', 'learndash' ) . '</a>';
}



/**
 * Update assignment points and approval status
 *
 * @since 2.1.0
 *
 * @param $assignment_id
 */
function learndash_assignment_save_metabox_content( $assignment_id ) {
	if ( ! isset( $_POST['assignment-points'] ) ) {
		return;
	}

	// update points
	$points = intval( $_POST['assignment-points'] );
	update_post_meta( $assignment_id, 'points', $points );

	// approve assignment
	$assignment_post = get_post( $assignment_id );
	$lesson_id = get_post_meta( $assignment_id, 'lesson_id', true );
	learndash_approve_assignment( $assignment_post->post_author, $lesson_id );
}

add_action( 'save_post', 'learndash_assignment_save_metabox_content' );



/**
 * Only allow admins, group leaders, and assignment owners to see assignment
 * 
 * @since 2.1.0
 */
function learndash_assignment_permissions() {
	global $post;

	if ( ! empty( $post->post_type ) && $post->post_type == 'sfwd-assignment' && is_singular() ) {
		$user_id = get_current_user_id();

		if ( learndash_is_admin_user( $user_id ) ) {
			return;
		}
		
		if ( $post->post_author == $user_id ) {
			return;
		} else if ( learndash_is_group_leader_of_user( $user_id, $post->post_author ) ) {
			return;
		} else {
			wp_redirect( get_bloginfo( 'url' ) );
			exit;
		}
	}
}

add_action( 'wp', 'learndash_assignment_permissions' ); //, 0, 3 );



/**
 * Register Assignments custom post type
 * 
 * @since 2.1.0
 */
function learndash_register_assignment_upload_type() {
	$labels = array(
		'name' => __( 'Assignments', 'learndash' ), 
		'singular_name' => __( 'Assignment', 'learndash' ), 
		'edit_item' => __( 'Edit Assignment', 'learndash' ), 
		'view_item' => __( 'View Assignment', 'learndash' ), 
		'search_items' => __( 'Search Assignments', 'learndash' ), 
		'not_found' => __( 'No assignment found', 'learndash' ), 
		'not_found_in_trash' => __( 'No assignment found in Trash', 'learndash' ), 
		'parent_item_colon' => __( 'Parent:', 'learndash' ), 
		'menu_name' => __( 'Assignments', 'learndash' ),
	);

	$args = array(
		'labels' => $labels, 
		'hierarchical' => false, 
		'supports' => array( 'title', 'comments', 'author' ), 
		'public' => true, 
		'show_ui' => true, 
		'show_in_menu' => true, 
		'show_in_nav_menus' => true, 
		'publicly_queryable' => true, 
		'exclude_from_search' => true, 
		'has_archive' => false, 
		'query_var' => true,
		'rewrite' => array( 'slug' => 'assignment' ), 
		'capability_type' => 'assignment', 
		'capabilities' => array( 
			'read_post' => 'read_assignment', 
			'publish_posts' => 'publish_assignments', 
			'edit_posts' => 'edit_assignments', 
			'edit_others_posts' => 'edit_others_assignments', 
			'delete_posts' => 'delete_assignments', 
			'delete_others_posts' => 'delete_others_assignments', 
			'read_private_posts' => 'read_private_assignments', 
			'edit_post' => 'edit_assignment', 
			'delete_post' => 'delete_assignment', 
			'edit_published_posts' => 'edit_published_assignments', 
			'delete_published_posts' => 'delete_published_assignments',
		), 
		'map_meta_cap' => true,
	);

	register_post_type( 'sfwd-assignment', $args );	
}

add_action( 'init', 'learndash_register_assignment_upload_type' );



/**
 * Setup capabilities for Assignments custom post type
 * 
 * @since 2.1.0
 */
function learndash_add_assignment_caps() {
	$admin_role = get_role( 'administrator' );
	if ( ( $admin_role ) && ( $admin_role instanceof WP_Role ) ) {
		$cap = $admin_role->has_cap( 'delete_others_assignments' );

		if ( empty( $cap ) ) {
			$admin_role->add_cap( 'edit_assignment' );
			$admin_role->add_cap( 'edit_assignments' );
			$admin_role->add_cap( 'edit_others_assignments' );
			$admin_role->add_cap( 'publish_assignments' );
			$admin_role->add_cap( 'read_assignment' );
			$admin_role->add_cap( 'read_private_assignments' );
			$admin_role->add_cap( 'delete_assignment' );
			$admin_role->add_cap( 'edit_published_assignments' );
			$admin_role->add_cap( 'delete_others_assignments' );
			$admin_role->add_cap( 'delete_published_assignments' );
		}
	}	
	
	$group_leader_role = get_role( 'group_leader' );
	if ( ( $group_leader_role ) && ( $group_leader_role instanceof WP_Role ) ) {
		$group_leader_role->add_cap( 'read_assignment' );
		$group_leader_role->add_cap( 'edit_assignments' );
		$group_leader_role->add_cap( 'edit_others_assignments' );
		$group_leader_role->add_cap( 'edit_published_assignments' );
		$group_leader_role->add_cap( 'delete_others_assignments' );
		$group_leader_role->add_cap( 'delete_published_assignments' );
	}
}

add_action( 'admin_init', 'learndash_add_assignment_caps' );



/**
 * Delete assignment file when assignment post is deleted
 * 
 * @since 2.1.0
 * 
 * @param  int $post_id 
 */
function learndash_before_delete_assignment( $post_id ) {
	$post = get_post( $post_id );

	if ( $post->post_type != 'sfwd-assignment' ) {
		return;
	}
	
	$file_path = get_post_meta( $post_id, 'file_path', true );
	$file_path = rawurldecode( $file_path );
	
	if ( file_exists( $file_path ) ) {
		unlink( $file_path );
	}
}

add_action( 'before_delete_post', 'learndash_before_delete_assignment' );



/**
 * Echo the number of points awarded on the front end
 *
 * Displayed on single lessons under the submitted assignment
 *
 * @param $assignment_id
 *
 * @return string
 */
function learndash_assignment_points_awarded( $assignment_id ) {
	$points_enabled = learndash_assignment_is_points_enabled( $assignment_id );

	if ( $points_enabled ) {
		$current = get_post_meta( $assignment_id, 'points', true );

		if ( is_numeric( $current ) ) {
			$assignment_settings_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
			$max_points = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_amount' );
			$percentage = ( intval( $current ) / intval( $max_points ) ) * 100;
			$percentage = round( $percentage, 2 );
			$current = apply_filters(
				'learndash_points_awarded_output_format',
				sprintf(
					'(%d/%d) %d&#37; ',
					$current,
					$max_points,
					$percentage
				),
				$current,
				$max_points,
				$percentage
			);
		}

		return apply_filters( 'learndash_points_awarded_output', sprintf( 'Points Awarded: %s', $current ), $current );
	}
}


function learndash_assignment_is_points_enabled( $assignment ) {
	if ( is_a( $assignment, 'WP_Post' ) ) {
		$assignment_id = $assignment->ID;
	} else {
		$assignment_id = intval( $assignment );
	}

	$assignment_settings_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
	$points_enabled = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_enabled' );

	if ( $points_enabled == 'on' ) {
		return true;
	}

	return false;
}

