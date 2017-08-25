<?php
/**
 * User functions
 *
 * @since 2.1.0
 *
 * @package LearnDash\Users
 */



/**
 *
 * Outputs HTML for courses which the user is enrolled into
 *
 * @since 2.1.0
 *
 * @param  object $user User object
 */
function learndash_show_enrolled_courses( $user ) {
	$courses = get_pages( 'post_type=sfwd-courses' );
	$enrolled = array();
	$notenrolled = array();
	?>
		<table class='form-table'>
			<tr>
				<th> <h3><?php printf( _x( 'Enrolled %s', 'Enrolled Courses Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?></h3></th>
				<td>
					<ol>
					<?php
						foreach ( $courses as $course ) {
							if ( sfwd_lms_has_access( $course->ID,  $user->ID ) ) {
								$since = ld_course_access_from( $course->ID,  $user->ID );
								$since = empty( $since ) ? '' : 'Since: '.date( 'm/d/Y H:i:s', $since );

								if ( empty( $since ) ) {
									$since = learndash_user_group_enrolled_to_course_from( $user->ID, $course->ID );
									$since = empty( $since ) ? '' : 'Since: '.date( 'm/d/Y H:i:s', $since ).' (Group Access)';
								}

								echo "<li><a href='".get_permalink( $course->ID )."'>".$course->post_title."</a> ".$since."</li>";
								$enrolled[] = $course;
							} else {
								$notenrolled[] = $course;
							}
						}
					?>
					</ol>
				</td>
			</tr>

			<?php if ( current_user_can( 'enroll_users' ) ) : ?>
					<tr>
						<th> <h3><?php printf( _x( 'Enroll a %s', 'Enroll a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h3></th>
						<td>
							<select name='enroll_course'>
								<option value=''><?php printf( _x('-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></option>
									<?php foreach ( $notenrolled as $course ) : ?>
										<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
									<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th> <h3><?php printf( _x( 'Unenroll a %s', 'Unenroll a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h3></th>
						<td>
								<select name='unenroll_course'>
									<option value=''><?php printf( _x( '-- Select a %s --', 'Select a Course Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></option>
									<?php foreach ( $enrolled as $course ) : ?>
										<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
									<?php endforeach; ?>
								</select>
						</td>
					</tr>
			<?php endif; ?>
		</table>
	<?php
}



/**
 *
 * Saves enrolled courses for a particular user given it's user id.  Returns false on inability to enroll users.
 *
 * @since 2.1.0
 *
 * @param  int $user_id User ID
 * @return false
 */
function learndash_save_enrolled_courses( $user_id ) {
	if ( ! current_user_can( 'enroll_users' ) ) {
		return FALSE;
	}

	$enroll_course = $_POST['enroll_course'];
	$unenroll_course = $_POST['unenroll_course'];

	if ( ! empty( $enroll_course ) ) {
		$meta = ld_update_course_access( $user_id, $enroll_course );
	}

	if ( ! empty( $unenroll_course ) ) {
		$meta = ld_update_course_access( $user_id, $unenroll_course, $remove = true );
	}
}

if ((defined('LEARNDASH_GROUPS_LEGACY_v220') && (LEARNDASH_GROUPS_LEGACY_v220 === true))) {

	add_action( 'show_user_profile', 'learndash_show_enrolled_courses' );
	add_action( 'edit_user_profile', 'learndash_show_enrolled_courses' );

	add_action( 'personal_options_update', 'learndash_save_enrolled_courses' );
	add_action( 'edit_user_profile_update', 'learndash_save_enrolled_courses' );
}


/**
 * Output link to delete course data for user
 *
 * @since 2.1.0
 * 
 * @param  object $user WP_User object
 */
function learndash_delete_user_data_link( $user ) {
	if ( ! learndash_is_admin_user( ) ) {
		return '';
	}

	?>
		<div id="learndash_delete_user_data">
			<h2><?php printf( _x( 'Permanently Delete %s Data', 'Permanently Delete Course Data Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h2>
			<p><input type="checkbox" id="learndash_delete_user_data" name="learndash_delete_user_data" value="<?php echo $user->ID; ?>"> <label for="learndash_delete_user_data"><?php _e( 'Check and click update profile to permanently delete user\'s LearnDash course data. <strong>This cannot be undone.</strong>', 'learndash' ); ?></label></p>
		</div>
	<?php
}

add_action( 'show_user_profile', 'learndash_delete_user_data_link', 1000, 1 );
add_action( 'edit_user_profile', 'learndash_delete_user_data_link', 1000, 1 );
add_action( 'nss_license_footer','learndash_delete_user_data_link' );



/**
 * Delete user data
 * 
 * @since 2.1.0
 * 
 * @param  int $user_id
 */
function learndash_delete_user_data( $user_id ) {
	if ( ! learndash_is_admin_user( ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	if ( ! empty( $user->ID ) && ! empty( $_POST['learndash_delete_user_data'] ) && $user->ID == $_POST['learndash_delete_user_data'] ) {

		global $wpdb;

		$ref_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT statistic_ref_id FROM '.$wpdb->prefix."wp_pro_quiz_statistic_ref WHERE  user_id = '%d' ", $user->ID ) );

		if ( ! empty( $ref_ids[0] ) ) {
			$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_statistic_ref', array( 'user_id' => $user->ID ) );
			$wpdb->query( 'DELETE FROM '.$wpdb->prefix.'wp_pro_quiz_statistic WHERE statistic_ref_id IN ('.implode( ',', $ref_ids ).')' );
		}

		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_sfwd-quizzes', 'user_id' => $user->ID ) );
		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_sfwd-course_progress', 'user_id' => $user->ID ) );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'completed_%' AND user_id = '".$user->ID."'" );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'course_%_access_from' AND user_id = '".$user->ID."'" );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'course_completed_%' AND user_id = '".$user->ID."'" );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'learndash_course_expired_%' AND user_id = '".$user->ID."'" );

		// Need to add logic check to ensure table exists.
		$activity_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT activity_id FROM '. $wpdb->prefix ."learndash_user_activity WHERE user_id = '%d' ", $user->ID ) );
		//error_log('activity_ids<pre>'. print_r($activity_ids, true) .'</pre>');
		if ( !empty( $activity_ids ) ) {
			//$wpdb->delete( $wpdb->prefix. 'learndash_user_activity', array( 'activity_id' => $activity_ids ) );
			$sql_str = "DELETE FROM ". $wpdb->prefix ."learndash_user_activity WHERE activity_id IN (". implode(',', $activity_ids).") ";
			//error_log('sql_str['. $sql_str .']');
			$wpdb->query( $sql_str );

			//$wpdb->delete( $wpdb->prefix. 'learndash_user_activity_meta', array( 'activity_id' => $activity_ids ) );
			$sql_str = 'DELETE FROM '.$wpdb->prefix ."learndash_user_activity_meta WHERE activity_id IN (". implode(',', $activity_ids).") ";
			//error_log('sql_str['. $sql_str .']');
			$wpdb->query( $sql_str );
		}

		$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_lock', array( 'user_id' => $user->ID ) );
		$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_toplist', array( 'user_id' => $user->ID ) );

		// Move user uploaded Assignements to Trash.
		$user_assignements_query_args = array(
			'post_type'		=>	'sfwd-assignment',
			'post_status'	=>	'publish',
			'nopaging'		=>	true,
			'author' 		=> 	$user->ID
		);
		
		$user_assignements_query = new WP_Query( $user_assignements_query_args );
		//error_log('user_assignements_query<pre>'. print_r($user_assignements_query, true) .'</pre>');
		if ( $user_assignements_query->have_posts() ) {
			
			foreach( $user_assignements_query->posts as $assignment_post ) {
				wp_trash_post( $assignment_post->ID );
			}
		}
		wp_reset_postdata();


		// Move user uploaded Essay to Trash.
		$user_essays_query_args = array(
			'post_type'		=>	'sfwd-essays',
			'post_status'	=>	'any',
			'nopaging'		=>	true,
			'author' 		=> 	$user->ID
		);
		
		$user_essays_query = new WP_Query( $user_essays_query_args );
		//error_log('user_essays_query<pre>'. print_r($user_essays_query, true) .'</pre>');
		if ( $user_essays_query->have_posts() ) {
			
			foreach( $user_essays_query->posts as $essay_post ) {
				wp_trash_post( $essay_post->ID );
			}
		}
		wp_reset_postdata();
	}	
}

add_action( 'personal_options_update', 'learndash_delete_user_data' );
add_action( 'edit_user_profile_update', 'learndash_delete_user_data' );
//add_action( 'delete_user', 'learndash_delete_user_data' );


/**
 * Get all Courses enrolled by User
 * 
 * @since 2.2.1
 * 
 * @param  int $user_id
 */
function learndash_user_get_enrolled_courses( $user_id = 0, $course_query_args = array(), $bypass_transient = false ) {

	$course_ids = array();

	if ( empty( $user_id ) ) 
		return $courses_ids;

	$bypass_transient = true;
	$transient_key = "learndash_user_courses_" . $user_id;

	if ( !$bypass_transient ) {
		$courses_ids_transient = get_transient( $transient_key );
		//error_log('from transient: ['. $transient_key .']');
	} else {
		$courses_ids_transient = false;
	}

	if ( $courses_ids_transient === false ) {
	
		if ( ( learndash_is_admin_user( $user_id ) ) && ( apply_filters('learndash_override_course_auto_enroll', true, $user_id ) ) ) {

			$defaults = array(
				'post_type'			=>	'sfwd-courses',
				'fields'			=>	'ids',
				'nopaging'			=>	true,
			);

			$course_query_args = wp_parse_args( $course_query_args, $defaults );
			//error_log('course_query_args<pre>'. print_r( $course_query_args, true) .'</pre>');
	
			$course_query = new WP_Query( $course_query_args );
			//error_log('course_query<pre>'. print_r($course_query->posts, true) .'</pre>');
			if ( ( isset( $course_query->posts ) ) && ( !empty( $course_query->posts ) ) ) {
				$course_ids = $course_query->posts;
			}

		} else {
			
			$course_ids_open = learndash_get_open_courses();
			//error_log("course_ids_open<pre>". print_r($course_ids_open, true) .'</pre>');
			if ( !empty( $course_ids_open ) )
				$course_ids = array_merge( $course_ids, $course_ids_open );

			$course_ids_paynow = learndash_get_paynow_courses();
			//error_log("course_ids_paynow<pre>". print_r($course_ids_paynow, true) .'</pre>');
			if ( !empty( $course_ids_paynow ) )
				$course_ids = array_merge( $course_ids, $course_ids_paynow );
		
			$course_ids_access = learndash_get_user_course_access_list( $user_id );
			//error_log("course_ids_access<pre>". print_r($course_ids_access, true) .'</pre>');
			if ( !empty( $course_ids_access ) )
				$course_ids = array_merge( $course_ids, $course_ids_access );
		
			$course_ids_groups = learndash_get_user_groups_courses_ids( $user_id );
			//error_log("course_ids_groups<pre>". print_r($course_ids_groups, true) .'</pre>');
			if ( !empty( $course_ids_groups ) )
				$course_ids = array_merge($course_ids, $course_ids_groups );
		
			//error_log('F: course_ids<pre>'. print_r( $course_ids, true) .'</pre>');

			if ( !empty( $course_ids ) ) {
				$course_ids = array_unique( $course_ids );
		
				if ( !empty( $course_query_args ) ) {
					$defaults = array(
						'post_type'			=>	'sfwd-courses',
						'fields'			=>	'ids',
						'nopaging'			=>	true,
					);
	
					$course_query_args = wp_parse_args( $course_query_args, $defaults );
					$course_query_args['post__in'] = $course_ids;
					//error_log('course_query_args<pre>'. print_r( $course_query_args, true) .'</pre>');
			
					$course_query = new WP_Query( $course_query_args );
					//error_log('course_query<pre>'. print_r($course_query->posts, true) .'</pre>');
					if ( ( isset( $course_query->posts ) ) && ( !empty( $course_query->posts ) ) ) {
						$course_ids = $course_query->posts;
					}
				}
			}
		}
		
		set_transient( $transient_key, $course_ids, MINUTE_IN_SECONDS );

	} else {
		$courses_ids = $courses_ids_transient;
	}

	//error_log('user_id['. $user_id .'] course_ids<pre>'. print_r($course_ids, true) .'</pre>');
	return $course_ids;
}

// The legacy code below was replaces in v2.3. The code does the same basic function but each step is 
// now handled by sub-functions instead of all the logic being contained in a single function
function learndash_user_get_enrolled_courses_old( $user_id = 0, $bypass_transient = false ) {
	global $wpdb;
	
	$enrolled_courses_ids = array();

	if ( empty( $user_id ) ) 
		return $enrolled_courses_ids;

	$transient_key = "learndash_user_courses_" . $user_id;

	if (!$bypass_transient) {
		$enrolled_courses_ids_transient = get_transient( $transient_key );
		//error_log('from transient: ['. $transient_key .']');
	} else {
		$enrolled_courses_ids_transient = false;
	}

	if ( $enrolled_courses_ids_transient === false ) {
	
		// Atypical meta_value set looks like:
		// a:10:{s:29:"sfwd-courses_course_materials";s:15:"Course Material";s:30:"sfwd-courses_course_price_type";s:4:"open";s:30:"sfwd-courses_custom_button_url";s:0:"";s:25:"sfwd-courses_course_price";s:0:"";s:31:"sfwd-courses_course_access_list";s:50:"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20";s:34:"sfwd-courses_course_lesson_orderby";s:0:"";s:32:"sfwd-courses_course_lesson_order";s:0:"";s:32:"sfwd-courses_course_prerequisite";s:1:"0";s:31:"sfwd-courses_expire_access_days";s:0:"";s:24:"sfwd-courses_certificate";s:1:"6";}
		
		// First we try some magic. We attempt to query those course that have:
		// price type == 'open' or 'paynow'
		$course_price_type = "'s:30:\"sfwd-courses_course_price_type\";s:4:\"open\"'|'s:30:\"sfwd-courses_course_price_type\";s:6:\"paynow\"'";
		
		// OR the access list is not empty
		$not_like = "'s:31:\"sfwd-courses_course_access_list\";s:0:\"\";'";
		
		// OR the user ID is found in the access list. Note this pattern is four options
		// 1. The user ID is the only entry. 
		// 2. The user ID is at the front of the list as in "sfwd-courses_course_access_list";*:"X,*";
		// 3. The user ID is in middle "sfwd-courses_course_access_list";*:"*,X,*";
		// 4. The user ID is at the end "sfwd-courses_course_access_list";*:"*,X";
		$is_like = "
			's:31:\"sfwd-courses_course_access_list\";i:". $user_id .";'|
			's:31:\"sfwd-courses_course_access_list\";s:(.*):\"". $user_id .",(.*)\";'|
			's:31:\"sfwd-courses_course_access_list\";s:(.*):\"(.*),". $user_id .",(.*)\";'|
			's:31:\"sfwd-courses_course_access_list\";s:(.*):\"(.*),". $user_id ."\";'";


		$sql_str = "SELECT post_id FROM ". $wpdb->prefix ."postmeta WHERE meta_key='_sfwd-courses' AND (meta_value REGEXP ". $course_price_type ."	OR (meta_value NOT REGEXP ". $not_like ." AND meta_value REGEXP ". $is_like ."))";
		//error_log('sql_str['. $sql_str .']');
		
		$user_course_ids = $wpdb->get_col( $sql_str );
		//error_log('user_course_ids<pre>'. print_r($user_course_ids, true) .'</pre>');
		
		// Next we grap all the groups the user is a member of
		$users_groups = learndash_get_users_group_ids( $user_id );
		//error_log('users_groups<pre>'. print_r($users_groups, true) .'</pre>');
		
		$potential_course_ids = array_merge($user_course_ids, $users_groups);
		//error_log('potential_course_ids<pre>'. print_r($potential_course_ids, true) .'</pre>');
		
		// Instead of just getting ALL course IDs we settup a loop to grab batches (2000 per page) of Courses. 
		// This means if the site has 30k Courses we are not attempting to load all these in memory at once. 
		
		if ( !empty( $potential_course_ids ) ) {
		
			$course_query_args = array(
				'post_type'			=>	'sfwd-courses',
				'paged'				=>	1,
				'posts_per_page'	=>	2000,
				'fields'			=>	'ids',
				'post__in'			=>	$potential_course_ids
			);
		
			while( true ) {
				//error_log('course_query_args<pre>'. print_r($course_query_args, true) .'</pre>');
				$course_query = new WP_Query( $course_query_args );
				//error_log('course_query<pre>'. print_r($course_query->posts, true) .'</pre>');
	
				if ( ( isset( $course_query->posts ) ) && ( !empty( $course_query->posts ) ) ) {

					foreach ( $course_query->posts as $course_id ) {
						if ( sfwd_lms_has_access( $course_id,  $user_id ) ) {
							$enrolled_courses_ids[] = $course_id;
						}
					}
				
					$course_query_args['paged'] = intval($course_query_args['paged']) + 1;
				} else {
					break;
				}
			}	
		}
		set_transient( $transient_key, $enrolled_courses_ids, MINUTE_IN_SECONDS );
		//error_log('enrolled_courses_ids count['. count($enrolled_courses_ids) .']');
	} else {
		$enrolled_courses_ids = $enrolled_courses_ids_transient;
	}
	//error_log('enrolled_courses_ids<pre>'. print_r($enrolled_courses_ids, true) .'</pre> gettype['. gettype($enrolled_courses_ids) .']');
	
	return $enrolled_courses_ids;
}

/**
 * Set Courses enrolled by User
 * 
 * @since 2.2.1
 * 
 * @param  int $user_id
 */
function learndash_user_set_enrolled_courses( $user_id = 0, $user_courses_new = array() ) {

	if (!empty( $user_id )) {

		$user_courses_old = learndash_user_get_enrolled_courses( $user_id, true );
		if ((empty($user_courses_old)) && (!is_array($user_courses_old))) {
			$user_courses_old = array();
		}
		$user_courses_intersect = array_intersect( $user_courses_new, $user_courses_old );

		$user_courses_add = array_diff( $user_courses_new, $user_courses_intersect );
		if ( !empty( $user_courses_add ) ) {
			foreach ( $user_courses_add as $course_id ) {
				ld_update_course_access( $user_id, $course_id);
			}
		}
		$user_courses_remove = array_diff( $user_courses_old, $user_courses_intersect );
		if ( !empty( $user_courses_remove ) ) {
			foreach ( $user_courses_remove as $course_id ) {
				ld_update_course_access( $user_id, $course_id, true);
			}
		}
		
		// Finally clear our cache for other services 
		$transient_key = "learndash_user_courses_" . $user_id;
		delete_transient( $transient_key );
	}
}


function learndash_show_user_course_complete( $user_id = 0 ) {
	
	$show_options = false;
	
	if ( ( !empty( $user_id ) ) && ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user() ) ) {
		global $pagenow;
		
		if ( ( ( $pagenow == 'profile.php' ) || ( $pagenow == 'user-edit.php' ) ) ) {
			if ( learndash_is_admin_user( ) )
				$show_options = true;
			else if ( learndash_is_group_leader_user() )
				$show_options = false;
			
		} else if ($pagenow == 'admin.php') {
			if ( ( isset( $_GET['page'] ) ) && ( $_GET['page'] == 'group_admin_page' ) ) {
				if ( learndash_is_admin_user( ) )
					$show_options = true;
				else if ( learndash_is_group_leader_user() )
					$show_options = false;
			}
		}
	}	
	
	// See example snippet of this filter https://bitbucket.org/snippets/learndash/bMA7r
	return apply_filters( 'learndash_show_user_course_complete_options', $show_options, $user_id );
}

/**
 * Save User Courses Complete date
 * 
 * @since 2.3
 * 
 * @param  int $user_id
 */
function learndash_save_user_course_complete( $user_id = 0) {
	
	// Hate this cross-logic. But here it is. 
	// If we are clearing out the user's LD data then we abort this function. Now use going through the update.
	if ( ( isset( $_POST['learndash_delete_user_data'] ) ) && ( !empty( $_POST['learndash_delete_user_data'] ) )) {
		return;
	}
	
	if ( ( !empty( $user_id ) ) && ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user( ) ) ) {
		if ( ( isset( $_POST['user_progress'] ) ) && ( isset( $_POST['user_progress'][$user_id] ) ) && ( !empty( $_POST['user_progress'][$user_id] ) ) ) {
			if ( ( isset( $_POST['user_progress-'. $user_id .'-nonce'] ) ) && ( !empty( $_POST['user_progress-'. $user_id .'-nonce'] ) ) ) {
				if (wp_verify_nonce( $_POST['user_progress-'. $user_id .'-nonce'], 'user_progress-'. $user_id )) {
					$user_progress = (array)json_decode( stripslashes( $_POST['user_progress'][$user_id] ) );
					$user_progress = json_decode(json_encode($user_progress), true);
					//error_log('user_progress<pre>'. print_r($user_progress, true) .'</pre>');
					//return;
					
					
					$processed_course_ids = array();

					if ( ( isset( $user_progress['course'] ) ) && ( !empty( $user_progress['course'] ) ) ) {
						
						$usermeta = get_user_meta( $user_id, '_sfwd-course_progress', true );
						$course_progress = empty( $usermeta ) ? array() : $usermeta;
						
						$_COURSE_CHANGED = false; // Simple flag to let us know we changed the quiz data so we can save it back to user meta.
						
						foreach($user_progress['course'] as $course_id => $course_data_new ) {
						
							$processed_course_ids[] = $course_id;
							
							if ( isset( $course_progress[$course_id] ) ) {
								$course_data_old = $course_progress[$course_id];
							} else {
								$course_data_old = array();
							}
							
							$course_data_new = learndash_course_item_to_activity_sync( $user_id, $course_id, $course_data_new, $course_data_old );
							
							$course_progress[$course_id] = $course_data_new;
							
							$_COURSE_CHANGED = true;	
						}
						
						if ( $_COURSE_CHANGED === true )
							update_user_meta( $user_id, '_sfwd-course_progress', $course_progress );
					}

					if ( ( isset( $user_progress['quiz'] ) ) && ( !empty( $user_progress['quiz'] ) ) ) {
						
						$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
						$quizz_progress = empty( $usermeta ) ? array() : $usermeta;
						$_QUIZ_CHANGED = false; // Simple flag to let us know we changed the quiz data so we can save it back to user meta.
						
						foreach( $user_progress['quiz'] as $quiz_id => $quiz_new_status ) {
							$quiz_meta = get_post_meta( $quiz_id, '_sfwd-quiz', true);
							
							if (!empty($quiz_meta)) {
								$quiz_old_status = !learndash_is_quiz_notcomplete( $user_id, array( $quiz_id => 1 ) );
							

								// For Quiz if the admin marks a qiz complete we don't attempt to update an existing attempt for the user quiz. 
								// Instead we add a new entry. LD doesn't care as it will take the complete one for calculations where needed. 
								if ($quiz_new_status == true) {
									if ($quiz_old_status != true) {
										
										// If the admin is marking the quiz complete AND the quiz is NOT already complete...
										// Then we add the minimal quiz data to the user profile
										$quizdata = array(
											'quiz' 					=> 	$quiz_id,
											'score' 				=> 	0,
											'count' 				=> 	0,
											'pass' 					=> 	true,
											'rank' 					=> 	'-',
											'time' 					=> 	time(),
											'pro_quizid' 			=> 	$quiz_meta['sfwd-quiz_quiz_pro'],
											'points' 				=> 	0,
											'total_points' 			=> 	0,
											'percentage' 			=> 	0,
											'timespent' 			=> 	0,
											'has_graded'   			=> 	false,
											'statistic_ref_id' 		=> 	0,
											'm_edit_by'				=>	get_current_user_id(),	// Manual Edit By ID
											'm_edit_time'			=>	time()			// Manual Edit timestamp
										);
										
										$quizz_progress[] = $quizdata;
										
										// Then we add the quiz entry to the activity database. 
										learndash_update_user_activity(
											array(
												'user_id'				=>	$user_id,
												'post_id'				=>	$quiz_id,
												'activity_type'			=>	'quiz',
												'activity_action'		=>	'insert',
												'activity_status'		=>	$quizdata['pass'],
												'activity_started'		=>	$quizdata['time'],
												'activity_completed' 	=>	$quizdata['time'],
												'activity_meta'			=>	$quizdata 
											)
										);

										$_QUIZ_CHANGED = true;

									}
								} else if ($quiz_new_status != true) {
									// If we are unsetting a quiz ( changing from complete to incomplete). We need to do some complicated things...
									if ($quiz_old_status == true) {

										if (!empty($quizz_progress)) {
											foreach($quizz_progress as $quiz_idx => $quiz_item) {
												if (($quiz_item['quiz'] == $quiz_id) && ($quiz_item['pass'] == true)) {
													$quizz_progress[$quiz_idx]['pass'] = false;
													
													// We need to update the activity database records for this quiz_id
													$activity_query_args = array(
														'post_ids'		=>	$quiz_id,
														'user_ids'		=>	$user_id,
														'activity_type'	=>	'quiz'
													);
													$quiz_activity = learndash_reports_get_activity($activity_query_args);
													if ( ( isset( $quiz_activity['results'] ) ) && ( !empty( $quiz_activity['results'] ) ) ) {
														foreach( $quiz_activity['results'] as $result ) {
															if ( ( isset( $result->activity_meta['pass'] ) ) && ( $result->activity_meta['pass'] == true ) ) {
															
																// If the activity meta 'pass' element is set to true we want to update it to false. 
																learndash_update_user_activity_meta( $result->activity_id, 'pass', false);
																
																//Also we need to update the 'activity_status' for this record
																learndash_update_user_activity(
																	array(
																		'activity_id'			=>	$result->activity_id,
																		'user_id'				=>	$user_id,
																		'post_id'				=>	$quiz_id,
																		'activity_type'			=>	'quiz',
																		'activity_action'		=>	'update',
																		'activity_status'		=>	false,
																		'activity_started'		=>	$result->activity_started,
																	)
																);
															}
														}
													}
													
													$_QUIZ_CHANGED = true;
												}
											}
										}
									}
								}
								
								$course_id = learndash_get_course_id( $quiz_id );
								if ( !empty( $course_id ) ) {
									$processed_course_ids[] = $course_id;
									
								}
							}
						}
						
						if ($_QUIZ_CHANGED == true)
							$ret = update_user_meta( $user_id, '_sfwd-quizzes', $quizz_progress );
					
					}
					
					if (!empty( $processed_course_ids ) ) {
						foreach( $processed_course_ids as $course_id ) {
							learndash_process_mark_complete( $user_id, $course_id);								
						}
					}
				}
			}
		}
	}
}


/**
 * We need to compare the new course item progress array to the existing one. Also update the new activity db table
 * 
 * @since 2.3
 * 
 * @param  int $user_id The user ID related to this course entry
 * @param  int $course_id The course ID related to this user course entry
 * @param  array $course_data_new The new course data item
 * @param  array $course_data_old The old course data item
 * @return none
 */
function learndash_course_item_to_activity_sync( $user_id = 0, $course_id = 0, $course_data_new = array(), $course_data_old = array() ) {
	if ( ( empty( $user_id ) ) || ( empty( $course_id ) ) || ( empty( $course_data_new ) ) ) {
		return;
	}
	
	// If we don't have the old course data we can get it. 
	if ( empty( $course_data_old ) ) {
		$user_course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
		if (isset( $user_course_progress[$course_id] ) )
			$course_data_old = $user_course_progress[$course_id];
		else 
			$course_data_old = array();
	}
	
	
	// First we loop over the new curse data lessons. We add any items not in the old array to the activity table
	if ( ( isset( $course_data_new['lessons'] ) ) && ( !empty( $course_data_new['lessons'] ) ) ) {
		foreach( $course_data_new['lessons'] as $lesson_id => $lesson_complete ) {
			if ( !isset( $course_data_old['lessons'][$lesson_id] ) ) {
				learndash_update_user_activity(
					array(
						'user_id'				=>	$user_id,
						'post_id'				=>	$lesson_id,
						'activity_type'			=>	'lesson',
						'activity_status'		=>	$lesson_complete,
						'activity_completed'	=>	time(),
					)
				);
			}
		}
	}

	// Next we loop over the lesson topics. We add any new items not in the old array to the activity table
	if ( ( isset( $course_data_new['topics'] ) ) && ( !empty( $course_data_new['topics'] ) ) ) {
		foreach( $course_data_new['topics'] as $lesson_id => $lesson_topics ) {
			if ( !empty( $lesson_topics ) ) {
				foreach( $lesson_topics as $topic_id => $topic_complete ) {
					if ( !isset( $course_data_old['topics'][$lesson_id][$topic_id] ) ) {
						learndash_update_user_activity(
							array(
								'user_id'				=>	$user_id,
								'post_id'				=>	$topic_id,
								'activity_type'			=>	'topic',
								'activity_status'		=>	$topic_complete,
								'activity_completed'	=>	time(),
							)
						);
					}
				}
			}
		}
	}


	// Then we loop over the old course lessons. Here if the lesson is NOT in the new course lessons we need to change the 'activity_status' to false. 
	if ( ( isset( $course_data_old['lessons'] ) ) && ( !empty( $course_data_old['lessons'] ) ) ) {
		foreach( $course_data_old['lessons'] as $lesson_id => $lesson_complete ) {
			if ( !isset( $course_data_new['lessons'][$lesson_id] ) ) {
				learndash_update_user_activity(
					array(
						'user_id'				=>	$user_id,
						'post_id'				=>	$lesson_id,
						'activity_type'			=>	'lesson',
						'activity_status'		=>	false,
					)
				);
			}
		}
	}

	// Then we loop over the old course topics. Here if the lesson is NOT in the new course topics we need to change the 'activity_status' to false. 
	if ( ( isset( $course_data_old['topics'] ) ) && ( !empty( $course_data_old['topics'] ) ) ) {
		foreach( $course_data_old['topics'] as $lesson_id => $lesson_topics ) {
			if ( !empty( $lesson_topics ) ) {
				foreach( $lesson_topics as $topic_id => $topic_complete ) {
					if ( !isset( $course_data_new['topics'][$lesson_id][$topic_id] ) ) {
						learndash_update_user_activity(
							array(
								'user_id'				=>	$user_id,
								'post_id'				=>	$topic_id,
								'activity_type'			=>	'topic',
								'activity_status'		=>	false,
							)
						);
					}
				}
			}
		}
	}

	// Finally we recalculate the course completed steps from the new course data. 
	$completed_steps = learndash_course_get_completed_steps( $user_id, $course_id, $course_data_new );
	if ( ( !isset( $course_data_new['completed'] ) ) || ( $completed_steps != $course_data_new['completed'] ) ) {
		learndash_update_user_activity(
			array(
				'user_id'			=>	$user_id,
				'post_id'			=>	$course_id,
				'activity_type'		=>	'course',
				'activity_meta'		=>	array( 
											'steps_completed' => $completed_steps,
										)
			)
		);
		
	}
	
	// Then return the new course data to the caller. 
	return $course_data_new;
}

/**
 * Get all Courses where the User ID in the course meta 'access_list' field.
 * 
 * @since 2.3
 * 
 * @param  int $user_id
 * @return array an array of course_ids.
 */
function learndash_get_user_course_access_list( $user_id = 0 ) {
	global $wpdb;
	
	// OR the access list is not empty
	$not_like = " postmeta.meta_value NOT REGEXP '\"sfwd-courses_course_access_list\";s:0:\"\";' ";
	
	// OR the user ID is found in the access list. Note this pattern is four options
	// 1. The user ID is the only entry. 
	//	1a. The single entry could be an int 
	//	1b. Ot the single entry could be an string
	// 2. The user ID is at the front of the list as in "sfwd-courses_course_access_list";*:"X,*";
	// 3. The user ID is in middle "sfwd-courses_course_access_list";*:"*,X,*";
	// 4. The user ID is at the end "sfwd-courses_course_access_list";*:"*,X";

	$is_like = " postmeta.meta_value REGEXP 's:31:\"sfwd-courses_course_access_list\";i:". $user_id .";' 
		OR postmeta.meta_value REGEXP 's:31:\"sfwd-courses_course_access_list\";s:(.*):\"". $user_id ."\";' 
		OR postmeta.meta_value REGEXP 's:31:\"sfwd-courses_course_access_list\";s:(.*):\"". $user_id .",(.*)\";' 
		OR postmeta.meta_value REGEXP  's:31:\"sfwd-courses_course_access_list\";s:(.*):\"(.*),". $user_id .",(.*)\";' 
		OR postmeta.meta_value REGEXP 's:31:\"sfwd-courses_course_access_list\";s:(.*):\"(.*),". $user_id ."\";'";

	$sql_str = "SELECT post_id FROM ". $wpdb->prefix ."postmeta as postmeta INNER JOIN ". $wpdb->prefix ."posts as posts ON posts.ID = postmeta.post_id WHERE posts.post_status='publish' AND postmeta.meta_key='_sfwd-courses' AND ( ". $not_like ." AND (". $is_like ."))";
	//error_log('sql_str['. $sql_str .']');
	
	$user_course_ids = $wpdb->get_col( $sql_str );
	//error_log('user_course_ids<pre>'. print_r($user_course_ids, true) .'</pre>');
	
	return $user_course_ids;
}

/**
 * Get all Courses within all the Groups the user has access.
 * 
 * @since 2.3
 * 
 * @param  int $user_id
 * @return array an array of course_ids.
 */
function learndash_get_user_groups_courses_ids( $user_id = 0 ) {
	global $wpdb;
	
	$user_group_course_ids = array();
	
	if ( empty( $user_id ) ) 
		return $user_group_course_ids;
	
	// Next we grap all the groups the user is a member of
	$users_group_ids = learndash_get_users_group_ids( $user_id );
	
	if ( !empty( $users_group_ids ) ) {
		//$user_group_course_ids = learndash_get_groups_courses_ids( $user_id, $users_group_ids );	
		foreach ( $users_group_ids as $group_id ) {
			$group_course_ids = learndash_group_enrolled_courses( $group_id );
			if ( !empty( $group_course_ids ) ) {
				$user_group_course_ids = array_merge( $user_group_course_ids, $group_course_ids );
			}
		}
	}
	
	return $user_group_course_ids;
}


/**
 * Record the last login time for the user. 
 * 
 * @since 2.3
 * 
 * @param  string $user_login login name
 * @param  object $user Object WP_User with user details
 * @return nothing
 */
function learndash_wp_login($user_login, $user) {
	
	if ( ( !empty( $user_login ) ) && ( $user instanceof WP_User ) ) {
		update_user_meta( $user->ID, 'learndash-last-login', time() );
	}
}
add_action('wp_login', 'learndash_wp_login', 99, 2);
