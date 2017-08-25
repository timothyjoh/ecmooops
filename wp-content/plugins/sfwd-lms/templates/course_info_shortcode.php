<?php
/**
 * Displays course information for a user
 *
 * Available:
 * $user_id
 * $courses_registered: course
 * $course_progress: Progress in courses
 * $quizzes
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Course
 */

/**
 * Course registered
 */

global $pagenow;
?>
<div id='ld_course_info'>

	<!-- Course info shortcode -->
	<?php if ( $courses_registered ) : ?>
		<div id='ld_course_info_mycourses_list'>
			<h4><?php echo sprintf( _x( 'You are registered for the following %s', 'You are registered for the following courses', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'courses' ) ); ?></h4>
			<?php foreach ( $courses_registered as $c ) : ?>
				<div class='ld-course-info-my-courses'><?php echo get_the_post_thumbnail( $c ); ?>
				<?php echo '<h2 class="ld-entry-title entry-title"><a href="' . get_permalink( $c ) . '"  rel="bookmark">'.get_the_title( $c ).'</a></h2>'; ?>
				</div>
			<?php endforeach; ?>
			<br/>
		</div>
	<?php endif; ?>

	<?php /* Course progress */ ?>
	<?php if ( !empty($courses_registered) ) : ?>
		<div id='course_progress_details'>
			<h4><?php printf( _x( '%s progress details:', 'Course progress details Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h4>
			<?php 
				if ( learndash_show_user_course_complete( $user_id ) ) {
					?>
					<input type="hidden" id="user-progress-<?php echo $user_id ?>" name="user_progress[<?php echo $user_id ?>]" value="<?php echo htmlspecialchars( json_encode( array('course' => array(), 'quiz' => array()), JSON_FORCE_OBJECT ) ); ?>" />
					<input type="hidden" name="user_progress-<?php echo $user_id ?>-nonce" value="<?php echo wp_create_nonce( 'user_progress-'. $user_id ) ?>" />
					<?php 
				}

				foreach ( $courses_registered as $course_id ) {
					
					$course = get_post( $course_id );
					if ( ( !( $course instanceof WP_Post ) ) || ( empty( $course->post_title ) )) 
						continue;
					
					?><span class="learndash-profile-course-title"><strong><a href="<?php echo get_permalink( $course->ID ); ?>"><?php echo get_the_title( $course->ID ) ?></a></strong>:</span><?php

					$course_status = learndash_course_status( $course_id, $user_id );
					?> <?php _e('Status:', 'learndash') ?> <span class="leardash-course-status leardash-course-status-<?php echo sanitize_title_with_dashes($course_status) ?>"><?php echo $course_status ?></span><?php

					if (isset($course_progress[$course_id])) {
						$coursep = $course_progress[$course_id];

						echo ' '. sprintf( __( 'Completed <strong>%d</strong> out of <strong>%d</strong> steps', 'learndash' ), $coursep['completed'], $coursep['total'] );
						
					} else {
						$coursep = array();
					}

					$since = ld_course_access_from( $course->ID,  $user_id );
					if ( !empty( $since ) ) {
						echo ' <span class="learndash-profile-course-access-label">'. sprintf( __('Since: %s', 'learndash' ), learndash_adjust_date_time_display( $since ) ) .'</span>';
					} else {
						$since = learndash_user_group_enrolled_to_course_from( $user_id, $course->ID );
						if ( !empty( $since ) ) {
							echo ' <span class="learndash-profile-course-access-label">'. sprintf( __('Since: %s (Group Access)', 'learndash'), learndash_adjust_date_time_display( $since ) ) .'</span>';
						}
					} 

					// Display the Course Access if expired or expiring
					$expire_access = learndash_get_setting( $course_id, 'expire_access' );
					if ( !empty( $expire_access ) ) {
						$expired = ld_course_access_expired( $course_id, $user_id );
						if ( $expired ) {
							?> <span class="leardash-course-expired"><?php echo __('(access expired)', 'learndash') ?></span> <?php
						} else {
							$expired_on = ld_course_access_expires_on($course_id, $user_id);
							if (!empty( $expired_on ) ) {
								?> <span class="leardash-course-expired"><?php echo sprintf( _x('(expires %s)', 'Course Expires on date', 'learndash'),
							 		learndash_adjust_date_time_display( $expired_on ) ) ?></span> <?php
							}
						}
					}

					if ( ( $user_id == get_current_user_id() ) || ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) {
						$certificateLink = learndash_get_course_certificate_link( $course_id, $user_id );
						if ( !empty( $certificateLink ) ) {
							?> - <a href="<?php echo $certificateLink ?>" target="_blank"><?php echo __( 'Certificate', 'learndash' ); ?></a><?php
						}
					}

					if ( current_user_can('edit_courses', intval($course->ID) ) ) {
						$edit_post_link = get_edit_post_link( intval($course->ID) );
						//error_log('edit_post_link['. $edit_post_link .']');
						?> <a class="learndash-profile-edit-course-link" href="<?php echo $edit_post_link; ?>"><?php echo _x('(edit)', 'profile edit course link label', 'learndash') ?></a><?php
					}

					if ( learndash_show_user_course_complete( $user_id ) ) {
						
						$lessons = learndash_get_course_lessons_list( $course_id, $user_id );
						$course_quiz_list = learndash_get_course_quiz_list( $course_id, $user_id ); 

						if ((!empty($lessons)) || (!empty($course_quiz_list))) {
							$user_course_progress 					= 	array();
							$user_course_progress['user_id'] 		= 	$user_id;
							$user_course_progress['course_id']  	= 	$course_id;							
							$user_course_progress['course_data']	=	$coursep;
								
							if ($course_status == __( 'Completed', 'learndash' )) {
								$course_checked 					= 	' checked="checked" ';
								$user_course_progress['checked'] 	= 	true;
							} else {
								$course_checked 					= 	'';
								$user_course_progress['checked'] 	= 	false;
							}
							
							?> <a href="#" id="learndash-profile-course-details-link-<?php echo $course_id ?>" class="learndash-profile-course-details-link"><?php echo _x('(details)', 'Course progress details link', 'learndash') ?></a>
							<div id="learndash-profile-course-details-container-<?php echo $course_id ?>" class="learndash-profile-course-details-container" style="display:none">
								<input id="learndash-mark-course-complete-<?php echo $course_id ?>" type="checkbox" <?php echo $course_checked; ?> class="learndash-mark-course-complete" data-name="<?php echo htmlspecialchars( json_encode( $user_course_progress, JSON_FORCE_OBJECT ) ) ?>" data-title-checked="<?php echo htmlspecialchars ( sprintf( _x('Are you sure you want to set all %s, %s and %s complete for this %s?', 'Are you sure you want to set all Lessons, Topics and Quizzes complete for this Course?', 'learndash'), LearnDash_Custom_Label::get_label( 'lessons' ), LearnDash_Custom_Label::get_label( 'topics' ), LearnDash_Custom_Label::get_label( 'quizzes' ), LearnDash_Custom_Label::get_label( 'course' )), ENT_QUOTES) ?>" /><label for="learndash-mark-course-complete-<?php echo $course_id ?>"><?php echo sprintf( _x('%s All Complete', 'Course All Complete', 'learndash'), LearnDash_Custom_Label::get_label( 'course' ) ) ?></label><br />
								<?php
									include( 
										SFWD_LMS::get_template( 
											'course_navigation_admin', 
											array( 
												'course_id' 		=> 	$course_id, 
												'course' 			=> 	$course, 
												'course_progress' 	=> 	$course_progress,
												'lessons' 			=> 	$lessons, 
												'course_quiz_list'	=>	$course_quiz_list,
												'user_id'			=>	$user_id
											), 
											null, 
											true 
										)
									);
								?>
							</div>
							<?php
						}
					}
					
					?><br/><?php
				} 
			?>
		</div>
		<br>
	<?php endif; ?>

	<?php /* Quizzes */ ?>
	<?php if ( $quizzes ) : ?>
		<?php
			global $learndash_assets_loaded;
			
			if ( !isset( $learndash_assets_loaded['scripts']['learndash_template_script_js'] ) ) {
				$filepath = locate_template( 
					array( 
						'learndash/learndash_template_script.min.js', 
						'learndash/learndash_template_script.js', 
						'learndash_template_script.min.js', 
						'learndash_template_script.js' 
					) 
				);

				if ( !empty( $filepath ) ) {
					wp_enqueue_script( 'learndash_template_script_js', str_replace( ABSPATH, '/', $filepath ), array( 'jquery' ), LEARNDASH_VERSION, true );
					$learndash_assets_loaded['scripts']['learndash_template_script_js'] = __FUNCTION__;
				} else if ( file_exists( LEARNDASH_LMS_PLUGIN_DIR .'/templates/learndash_template_script'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js' ) ) {
					wp_enqueue_script( 'learndash_template_script_js', LEARNDASH_LMS_PLUGIN_URL . 'templates/learndash_template_script'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.js', array( 'jquery' ), LEARNDASH_VERSION, true );
					$learndash_assets_loaded['scripts']['learndash_template_script_js'] = __FUNCTION__;
		
					$data = array();
					$data['ajaxurl'] = admin_url('admin-ajax.php');
					$data = array( 'json' => json_encode( $data ) );
					wp_localize_script( 'learndash_template_script_js', 'sfwd_data', $data );
				}
			}
			LD_QuizPro::showModalWindow();
		?>
		<h4><?php echo sprintf( _x( 'You have taken the following %s:', 'You have taken the following quizzes:', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'quizzes' ) ); ?></h4>

		<?php foreach ( $quizzes as $k => $v ) : ?>
			<?php $quiz = get_post( $v['quiz'] ); ?>
			<?php
			
			$certificateLink = '';
			$certificate_threshold = 0;

			if (!isset($v['has_graded']))
				$v['has_graded'] = false;

			if ( true === $v['has_graded'] && true === LD_QuizPro::quiz_attempt_has_ungraded_question( $v ) ) {
				$certificateLink = '';
				$certificate_threshold = 0;
				$passstatus = 'red';
			} else {
				$c = learndash_certificate_details( $v['quiz'], $user_id );
				if ((isset($c['certificateLink'])) && (!empty($c['certificateLink'])))
					$certificateLink = $c['certificateLink']; 
				
				if ((isset($c['certificate_threshold'])) && (!empty($c['certificate_threshold'])))
					$certificate_threshold = $c['certificate_threshold'];
				
				$passstatus = isset( $v['pass'] ) ? ( ( $v['pass'] == 1 ) ? 'green' : 'red' ) : '';
			}
			?>
			
			<?php //$passstatus = isset( $v['pass'] ) ? ( ( $v['pass'] == 1 ) ? 'green' : 'red' ) : ''; ?>
			<?php //$c = learndash_certificate_details( $v['quiz'], $user_id ); ?>
			<?php //$certificateLink = $c['certificateLink']; ?>
			<?php // $certificate_threshold = $c['certificate_threshold']; ?>
			<?php $quiz_title = ! empty( $quiz->post_title ) ? $quiz->post_title : @$v['quiz_title']; ?>

			<?php if ( ! empty( $quiz_title ) ) : ?>
				<p>
					<strong><a href="<?php echo get_permalink($quiz->ID) ?>"><?php echo $quiz_title ?></a></strong> 
					<?php echo isset( $v['percentage'] ) ? " - <span style='color:". $passstatus ."'>". $v['percentage'] ."%</span>" : '' ?>
					<?php if ( ( ( $user_id == get_current_user_id() ) || ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) && ( !empty( $certificateLink ) ) ) {
							if ( ( ( isset( $v['pass'] ) ) && ( $v['pass'] == 1 ) )
								&& ( ( isset( $v['percentage'] ) && $v['percentage'] >= $certificate_threshold * 100 ) ) 
								|| ( ( isset( $v['count'] ) ) && ( intval($v['count'] ) ) 
								&& ( isset( $v['score'] ) ) && ( intval( $v['score'] ) )
								&& ( ( intval( $v['score'] ) / intval( $v['count'] ) ) >= $certificate_threshold ) )
						 	) { 
							?> - <a href='<?php echo add_query_arg(array('time' => $v['time']), $certificateLink) ?>' target='_blank'><?php echo __( 'Certificate', 'learndash' ); ?></a><?php } ?>					
					<?php
					}
					
					if ( ( ( $user_id == get_current_user_id() ) || ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) && ( isset( $v['statistic_ref_id'] ) ) && ( !empty( $v['statistic_ref_id'] ) ) ) {

						/**
						 *	 @since 2.3
						 * See snippet on use of this filter https://bitbucket.org/snippets/learndash/5o78q
						 */
						if ( apply_filters( 'show_user_profile_quiz_statistics', 
							get_post_meta( $v['quiz'], '_viewProfileStatistics', true ), $user_id, $v, basename( __FILE__ ) ) ) {
								?><a class="user_statistic" data-statistic_nonce="<?php echo wp_create_nonce( 'statistic_nonce_'. $v['statistic_ref_id'] .'_'. get_current_user_id() . '_'. $user_id ); ?>" data-user_id="<?php echo $user_id ?>" data-quiz_id="<?php echo $v['pro_quizid'] ?>" data-ref_id="<?php echo intval( $v['statistic_ref_id'] ) ?>" href="#"><?php _e('Statistics', 'learndash'); ?></a><?php
						}
					}
					?>
					<?php					
					if (isset($v['m_edit_by'])) {
						$manual_edit_user = get_user_by('id', $v['m_edit_by']);
						if ($manual_edit_user instanceof WP_User) {
							$manual_edit_str = sprintf( __('Manual Edit by: %s on %s', 'learndash'), 
								$manual_edit_user->display_name, 
								date_i18n(apply_filters('learndash_date_time_formats', get_option('date_format') .' '. get_option('time_format')), $v['m_edit_time'] + get_option('gmt_offset') * 3600) );
							
							?> <abbr title="<?php echo $manual_edit_str ?>"><?php _e('(m)', 'learndash'); ?></abbr><?php
						}
					}
					?>
					<?php
					if ( current_user_can( 'wpProQuiz_edit_quiz' ) ) {
						?><a href="<?php echo get_edit_post_link( $quiz->ID ) ?>"><?php echo _x('(edit)', 'profile edit quiz link label', 'learndash') ?></a><?php
					}
					?>
					<br/>

					<?php
						if ( ( true === $v['has_graded'] ) && ( isset( $v['graded'] ) ) && (is_array( $v['graded'] ) ) && (!empty( $v['graded'] ) ) ) {
							foreach($v['graded'] as $quiz_question_id => $graded ) {
								
								if ( isset( $graded['post_id'] ) ) {

									$graded_post = get_post( $graded['post_id'] );
									if ($graded_post instanceof WP_Post) {
									
										if ($graded['status'] == 'graded') {
											$graded_color = ' color: green;';
										} else {
											$graded_color = ' color: red;';
										}
									
										$post_status_object_label = get_post_status_object( $graded['status'] )->label;

										//$post_type_object_label_name = get_post_type_object( $graded_post->post_type )->labels->name;
										
										echo /* $post_type_object_label_name .': '. */ get_the_title( $graded['post_id'] ) . ', '. __('Status', 'learndash') . ': <span style="'. $graded_color .'">' . $post_status_object_label .'</span>, '. __('Points', 'learndash') .': ' .  $graded['points_awarded'];
									
										if (current_user_can('edit_essays')) {
											echo ' <a target="_blank" href="'. get_edit_post_link( $graded['post_id'] ) .'">'. __( 'edit', 'learndash' ) .'</a>';
										}
										echo ' <a target="_blank" href="'. get_permalink( $graded['post_id'] ) .'">'. __( 'view', 'learndash' ) .'</a>';
									
										echo ' <a target="_blank" href="'. get_permalink( $graded['post_id'] ) .'#comments">'. __( 'comments', 'learndash' ) .' '. get_comments_number( $graded['post_id'] ) .'</a>';
										echo '<br />';
									}
								}
							}
						}
					?>

					
					<?php if ( isset( $v['rank'] ) && is_numeric( $v['rank'] ) ) : ?>
						<?php echo __( 'Rank: ', 'learndash' ); ?> <?php echo $v['rank']; ?>, 
					<?php endif; ?>

					<?php echo __( 'Score ', 'learndash' ); ?><?php echo $v['score']; ?> <?php echo __( ' out of ', 'learndash' ); ?> <?php 
					if ( ( isset( $v['question_show_count'] ) ) && ( !empty( $v['question_show_count'] ) ) ) echo $v['question_show_count'];
					else echo $v['count']; ?> <?php echo __( ' question(s)', 'learndash' ); ?>
					
					<?php if ( isset( $v['points'] ) && isset( $v['total_points'] ) ) : ?>
						<?php echo __( ' . Points: ', 'learndash' ); ?> <?php echo $v['points']; ?>/<?php echo $v['total_points']; ?>
					<?php endif; ?>

					<?php echo __( ' on ', 'learndash' ); ?> <?php echo date_i18n(apply_filters('learndash_date_time_formats', get_option('date_format') .' '. get_option('time_format')), $v['time'] + get_option('gmt_offset') * 3600) //date_i18n( DATE_RSS, $v['time'] ); ?>
					
					<?php
					/**
					 * 'course_info_shortcode_after_item' filter
					 *
					 * @todo filter doesn't make sense, change to action?
					 * 
					 * @since 2.1.0
					 */
					?>
					<?php echo apply_filters( 'course_info_shortcode_after_item', '', $quiz, $v, $user_id ); ?>
				</p>
			<?php endif; ?>	
		<?php endforeach; ?>

	<?php endif; ?>
	<!-- End Course info shortcode -->
</div>
