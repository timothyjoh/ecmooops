<?php
/**
 * Course Progress Functions
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Course
 */



/**
 * Output HTML output to mark a course complete
 *
 * Must meet requirements of course
 * 
 * @since 2.1.0
 * 
 * @param  object $post WP_Post lesson, topic
 * @return string		HTML output to mark course complete
 */
function learndash_mark_complete( $post ) {

	$current_user = wp_get_current_user();
	$userid = $current_user->ID;
	
	if ( isset( $_POST['sfwd_mark_complete'] ) && isset( $_POST['post'] ) && $post->ID == $_POST['post'] ) {
		return '';
	}

	if ( $post->post_type == 'sfwd-lessons' ) {
		$progress = learndash_get_course_progress( null, $post->ID );

		if ( ! empty( $progress['this']->completed ) ) {
			if ( !apply_filters( 'learndash_previous_step_completed', false, $progress['this']->ID, $current_user->ID ) ) {
				return '';
			}
		}

		if ( ! empty( $progress['prev'] ) && empty( $progress['prev']->completed ) && learndash_lesson_progression_enabled() ) {
			if ( !apply_filters( 'learndash_previous_step_completed', false, $progress['prev']->ID, $current_user->ID ) ) {
				return '';
			}
		}

		if ( ! learndash_lesson_topics_completed( $post->ID ) ) {
			if ( !apply_filters( 'learndash_previous_step_completed', false, $post->ID, $current_user->ID ) ) {
				return '';
			}
		}
	}

	if ( $post->post_type == 'sfwd-topic' ) {
		$progress = learndash_get_course_progress( null, $post->ID );

		if ( ! empty( $progress['this']->completed ) ) {
			if ( !apply_filters( 'learndash_previous_step_completed', false, $progress['this']->ID, $current_user->ID ) ) {
				return '';
			}
		}

		if ( ! empty( $progress['prev'] ) && empty( $progress['prev']->completed ) && learndash_lesson_progression_enabled() ) {
			if ( !apply_filters( 'learndash_previous_step_completed', false, $progress['prev']->ID, $current_user->ID ) ) {
				return '';
			}
		}

		if ( learndash_lesson_progression_enabled() ) {
			$lesson_id = learndash_get_setting( $post, 'lesson' );
			$lesson = get_post( $lesson_id );

			if ( ! is_previous_complete( $lesson ) ) {
				if ( !apply_filters( 'learndash_previous_step_completed', false, $lesson->ID, $current_user->ID ) ) {
					return '';
				}
			}
		}
	}

	$timeval = learndash_forced_lesson_time();

	if ( ! empty( $timeval ) ) {
		$time_sections = explode( ' ', $timeval );
		$h = $m = $s = 0;

		foreach ( $time_sections as $k => $v ) {
			$value = trim( $v );

			if ( strpos( $value, 'h' ) ) {
				$h = intVal( $value );
			} else if ( strpos( $value, 'm' ) ) {
				$m = intVal( $value );
			} else if ( strpos( $value, 's' ) ) {
				$s = intVal( $value );
			}
		}

		$time = $h * 60 * 60 + $m * 60 + $s;

		if ( $time == 0 ) {
			$time = (int)$timeval;
		}
	}

	if ( lesson_hasassignments( $post ) ) {

		$ret = '
				<table id="leardash_upload_assignment">
					<tr> <u>' . __( 'Upload Assignment', 'learndash' ) . "</u></tr>
					<tr>
						<td>
							<form name='uploadfile' id='uploadfile_form' method='POST' enctype='multipart/form-data' action='' accept-charset='utf-8' >
								<input type='file' name='uploadfiles[]' id='uploadfiles' size='35' class='uploadfiles' />
								<input type='hidden' value='" . $post->ID . "' name='post'/>
								<input type='hidden' name='uploadfile' value='". wp_create_nonce( 'uploadfile_'. get_current_user_id() .'_'. $post->ID ) ."'  />
								<input class='button-primary' type='submit' id='uploadfile_btn' value='" . __( 'Upload', 'learndash' ) . "'  />
							</form>
						</td>
					</tr>
				</table>
				";
		return $ret;


	} else {
		$return = '';
		$button_disabled = '';
		
		if ( !empty( $time ) ) {
			$return = '<script>
							var learndash_forced_lesson_time = ' . $time . ' ;
							var learndash_timer_var = setInterval(function(){learndash_timer()},1000);
						</script>
						<style>
							input#learndash_mark_complete_button[disabled] {color: #aaa;}
						</style>';
			$button_disabled = " disabled='disabled' ";			

		} 
		
		$return .= "<form id='sfwd-mark-complete' method='post' action=''>
					<input type='hidden' value='" . $post->ID . "' name='post'/>
					<input type='hidden' value='". wp_create_nonce( 'sfwd_mark_complete_'. get_current_user_id() .'_'. $post->ID ) ."' name='sfwd_mark_complete'/>
					<input type='submit' value='" . LearnDash_Custom_Label::get_label( 'button_mark_complete' ) . "' id='learndash_mark_complete_button' ". $button_disabled ."/>
				</form>";
		if ( !empty( $time ) ) {
			$return .= "<span id='learndash_timer'></span>";
		}
				
	} 
	
	/**
	 * Filter HTML output to mark course complete
	 * 
	 * @since 2.1.0
	 * 
	 * @param  string  $return
	 */
	return apply_filters( 'learndash_mark_complete', $return, $post );
}



function learndash_ajax_mark_complete( $quiz_id = null, $lesson_id = null ) {

	if ( empty( $quiz_id ) || empty( $lesson_id ) ) {
		return;
	}

	global $post;
	
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

	$can_attempt_again = learndash_can_attempt_again( $user_id, $quiz_id );

	if ( $can_attempt_again ) {
		$link = learndash_next_lesson_quiz( false, $user_id, $lesson_id, null );
	} else {
		$link = learndash_next_lesson_quiz( false, $user_id, $lesson_id, array( $quiz_id ) );
	}
	  

}



/**
 * Are topics completed for lesson
 * 
 * @since 2.1.0
 * 
 * @param  int  $lesson_id
 * @param  bool $mark_lesson_complete 	should we mark lesson complete
 * @return bool
 */
function learndash_lesson_topics_completed( $lesson_id, $mark_lesson_complete = false ) {
	$topics = learndash_get_topic_list( $lesson_id );

	if ( empty( $topics[0]->ID ) ) {
		return true;
	}

	$progress = learndash_get_course_progress( null, $topics[0]->ID );

	if ( empty( $progress['posts'] ) || ! is_array( $progress['posts'] ) ) {
		return false;
	}

	foreach ( $progress['posts'] as $topic ) {
		if ( empty( $topic->completed ) ) {
			return false;
		}
	}

	if ( $mark_lesson_complete ) {
		$user_id = get_current_user_id();
		learndash_process_mark_complete( null, $lesson_id );
	}

	//learndash_get_next_lesson_redirect();
	return true;
}



/**
 * Process request to mark a course complete
 * 
 * @since 2.1.0
 * 
 * @param  int $post
 */
function learndash_mark_complete_process( $post = null ) {
	// This is wrong. This function hooks into the 'wp' action. That action doesn't pass a post object or post_id. 
	// The $post object set hwere is not even used. We only need the _POST[post] (post_id) variable from the form
	if ( empty( $post ) ) {
		global $post;
	}

	if ( ( isset( $_POST['sfwd_mark_complete'] ) ) && (!empty( $_POST['sfwd_mark_complete'] ) ) && ( isset( $_POST['post'] ) ) && ( !empty( $_POST['post'] ) ) ) {
		if ( empty( $post ) || empty( $post->ID ) ) {
			$post = get_post();
			if ( empty( $post ) || empty( $post->ID ) ) {
				return;
			}
		}
		
		$post_id = intval( $_POST['post'] );

		if ( isset( $_POST['userid'] ) ) {
			$userid = $_POST['userid'];
		} else {
			if (!is_user_logged_in()) return;
			
			$userid = get_current_user_id();
		}


		/**
		 * Verify the form is valid
		 * @since 2.2.1.2
		 */
		if ( !wp_verify_nonce( $_POST['sfwd_mark_complete'], 'sfwd_mark_complete_'. $userid .'_'. $post_id ) ) {
			return;
		}

		$return = learndash_process_mark_complete( $userid, $post_id );

		if ( $return ) {
			$nextlessonredirect = learndash_get_next_lesson_redirect();
		} else {
			$nextlessonredirect = get_permalink();
		}

		if ( ! empty( $nextlessonredirect ) ) {

			/**
			 * Filter url to redirect to on next lesson
			 * 
			 * @param string $nextlessonredirect
			 */
			$nextlessonredirect = apply_filters( 'learndash_completion_redirect', $nextlessonredirect, $post_id );
			wp_redirect( $nextlessonredirect );
			exit;
		}
	}
}

add_action( 'wp', 'learndash_mark_complete_process' );



/**
 * Get a courses permalink
 * 
 * @since 2.1.0
 * 
 * @param  int 		$id 	course, topic, lesson, quiz, etc.
 * @return string 			course permalink
 */
function learndash_get_course_url( $id = null ) {

	if ( empty( $id ) ) {
		$id = learndash_get_course_id();
	}

	return get_permalink( $id );
}



/**
 * Redirect user to next lesson
 * 
 * @since 2.1.0
 * 
 * @param  object $post
 */
function learndash_get_next_lesson_redirect( $post = null ) {
	if ( empty( $post->ID ) ) {
		global $post;
	}

	$next = learndash_next_post_link( '', true, $post );

	if ( ! empty( $next ) ) {
		$link = $next;
	} else {
		if ( $post->post_type == 'sfwd-topic' ) {
			$lesson_id = learndash_get_setting( $post, 'lesson' );
			$link = get_permalink( $lesson_id );
		} else {
			$course_id = learndash_get_course_id( $post );
			$link = learndash_next_global_quiz( true, null, $course_id );
		}
	}

	if ( ! empty( $link ) ) {
		
		/**
		 * Filter where user should be redirected to for next lesson
		 *
		 * @since 2.1.0
		 * 
		 * @var $link 	redirect url
		 */
		$link = apply_filters( 'learndash_completion_redirect', $link, @$post->ID );
		wp_redirect( $link );
		exit;
	} else {
		return '';
	}
}



/**
 * Redirect user after quiz completion
 * 
 * @since 2.1.0
 */
function learndash_quiz_redirect() {
	global $post;

	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

	if ( ! empty( $_GET['quiz_redirect'] ) && ! empty( $_GET['quiz_id'] ) && ! empty( $_GET['quiz_type'] ) && ! empty( $_GET['course_id'] ) && $_GET['quiz_type'] == 'global' ) {
		
		$quiz_id = $_GET['quiz_id'];
		$can_attempt_again = learndash_can_attempt_again( $user_id, $quiz_id );

		if ( $can_attempt_again ) {
			$link = learndash_next_global_quiz();
		} else {
			$link = learndash_next_global_quiz( true, null, null, array($quiz_id) );
		}

		learndash_update_completion( $user_id );

		/**
		 * Filter where user should be redirected
		 *
		 * @since 2.1.0
		 * 
		 * @var $link 	redirect url
		 *
		 */
		$link = apply_filters( 'learndash_completion_redirect', $link, $quiz_id );
		wp_redirect( $link );
		exit;

	} else {
		
		if ( ! empty( $_GET['quiz_redirect'] ) && ! empty( $_GET['quiz_id'] ) && ! empty( $_GET['quiz_type'] ) && ! empty( $_GET['lesson_id'] ) && $_GET['quiz_type'] == 'lesson' ) {
			$quiz_id = $_GET['quiz_id'];
			$lesson_id = $_GET['lesson_id'];

			// We don't need to check if the quiz can be retaken because the learndash_next_lesson_quiz() function does that for us.
			//if ( $can_attempt_again ) {
			//	$link = learndash_next_lesson_quiz( true, $user_id, $lesson_id, null );
			//} else {
			//	$link = learndash_next_lesson_quiz( true, $user_id, $lesson_id, array($quiz_id) );
			//}

			$link = learndash_next_lesson_quiz( true, $user_id, $lesson_id, null );
			if ( empty( $link ) ) {
				$link = learndash_next_post_link( '', true );
			}

			if ( empty( $link ) ) {
				$post = get_post( $lesson_id );
				if ( $post->post_type == 'sfwd-topic' ) {
					$lesson = learndash_get_setting( $post, 'lesson' );
					$link = get_permalink( $lesson );
				} else {
					$link = learndash_next_global_quiz();
				}
			}
			
			// v2.3: Removed this and moved to an earlier step
			//learndash_update_completion( $user_id );

			if ( ! empty( $link ) ) {

				/**
				 * Filter where user should be redirected
				 *
				 * @since 2.1.0
				 * 
				 * @var $link 	redirect url
				 */
				$link = apply_filters( 'learndash_completion_redirect', $link, $quiz_id );
				wp_redirect( $link );
				exit;
			}
		}
	}
}

add_action( 'wp', 'learndash_quiz_redirect' );



/**
 * Can the user attempt the quiz again
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$quiz_id
 * @return bool
 */
function learndash_can_attempt_again( $user_id, $quiz_id ) {
	$quizmeta = get_post_meta( $quiz_id, '_sfwd-quiz', true );

	$repeats = $quizmeta['sfwd-quiz_repeats'];

	/**
	 * Number of repeats for quiz
	 * 
	 * @param int $repeats
	 */
	$repeats = apply_filters( 'learndash_allowed_repeats', $repeats, $user_id, $quiz_id );

	if ( $repeats == "" ) {
		return true;
	}

	$quiz_results = get_user_meta( $user_id, '_sfwd-quizzes', true );

	$count = 0;

	if ( ! empty( $quiz_results ) ) {
		foreach ( $quiz_results as $quiz ) {
			if ( $quiz['quiz'] == $quiz_id ) {
				$count++;
			}
		}
	}

	if ( $repeats > $count - 1 ) {
		return true;
	} else {
		return false;
	}
}



/**
 * Is previous topic, lesson complete
 * 
 * @since 2.1.0
 * 
 * @param  object  $post  WP_Post
 * @return bool
 */
function is_previous_complete( $post ) {
	$progress = learndash_get_course_progress( null, $post->ID );

	if ( empty( $progress ) ) {
		return 1;
	}

	if ( ! empty( $progress['prev'] ) && empty( $progress['prev']->completed ) ) {
		return 0;
	} else {
		return 1;
	}
}


/**
 * Returns the previous lesson/topic to be completed.
 * 
 * @since 2.2.1.1
 * 
 * @param  object  $post  WP_Post
 * @return object  WP_Post object 
 */
function learndash_get_previous( $post ) {
	$progress = learndash_get_course_progress( null, $post->ID );
	if ( ! empty( $progress['prev'] ) ) {
		return $progress['prev'];
	} 
}



/**
 * Update user meta with completion status for any resource
 * 
 * @since 2.1.0
 * 
 * @param  int  	$user_id
 * @param  int  	$postid 		course, lesson, topic
 * @param  boolean 	$onlycalculate	
 * @return bool                   	if user meta was updated
 */
function learndash_process_mark_complete( $user_id = null, $postid = null, $onlycalculate = false ) {
	if ( empty( $user_id ) ) {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
		} else {
			return false;
		}
	} else {
		$current_user = get_user_by( 'id', $user_id );
	}

	$post = get_post( $postid );
	if (!($post instanceof WP_Post)) return false;
	
	if ( ! $onlycalculate ) {

		/**
		 * Filter if this should be marked completed
		 * 
		 * @since 2.1.0
		 * 
		 * @param  bool
		 */
		$process_completion = apply_filters( 'learndash_process_mark_complete', true, $post, $current_user );

		if ( ! $process_completion ) {
			return false;
		}
	}

	if ( $post->post_type == 'sfwd-topic' ) {
		$lesson_id = learndash_get_setting( $post, 'lesson' );
		//$lesson_topics = learndash_get_topic_list( $lesson_id);

	}

	$lessons = learndash_get_lesson_list( $postid );
	$course_id = learndash_get_course_id( $postid );

	if ( empty( $course_id ) ) {
		return false;
	}

	if ( has_global_quizzes( $postid ) ) {
		$globalquiz = 1;
	} else {
		$globalquiz = 0;
	}

	if ( $globalquiz && is_all_global_quizzes_complete( $user_id, $postid ) ) {
		$globalquizcompleted = 1;
	} else {
		$globalquizcompleted = 0;
	}
	
	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	if ( empty( $course_progress[ $course_id ] ) ) {
		$course_progress[ $course_id ] = array( 'lessons' => array(), 'topics' => array() );
	}

	if ( empty( $course_progress[ $course_id ]['lessons'] ) ) {
		$course_progress[ $course_id ]['lessons'] = array();
	}

	if ( empty( $course_progress[ $course_id ]['topics'] ) ) {
		$course_progress[ $course_id ]['topics'] = array();
	}

	if ( $post->post_type == 'sfwd-topic' && empty( $course_progress[ $course_id ]['topics'][ $lesson_id ] ) ) {
		$course_progress[ $course_id ]['topics'][ $lesson_id ] = array();
	}

	$lesson_completed = false;
	$topic_completed = false;
	
	if ( ! $onlycalculate && $post->post_type == 'sfwd-lessons' && empty( $course_progress[ $course_id ]['lessons'][ $postid ] ) ) {
		$course_progress[ $course_id ]['lessons'][ $postid ] = 1;
		$lesson_completed = true;
	} 

	if ( ! $onlycalculate && $post->post_type == 'sfwd-topic' && empty( $course_progress[ $course_id ]['topics'][ $lesson_id ][ $postid ] ) ) {
		$course_progress[ $course_id ]['topics'][ $lesson_id ][ $postid ] = 1;
		$topic_completed = true;
	}

	$completed_old = isset( $course_progress[ $course_id ]['completed'] ) ? $course_progress[ $course_id ]['completed'] : 0;

	//$course_progress[ $course_id ]['completed'] = count( $course_progress[ $course_id ]['lessons'] ) + $globalquizcompleted;
	
	$completed = learndash_course_get_completed_steps( $user_id, $course_id, $course_progress[ $course_id ] );
	
	$course_progress[ $course_id ]['completed'] = $completed;
	// 2016-07-16 v2.3 Changed the logic on the count here. In the previous logic the count of lessons and 1 or 0 for global quiz.
	//$course_progress[ $course_id ]['total'] = count( $lessons ) + $globalquiz;
	
	// New logic includes lessons and topics. 
	$course_progress[ $course_id ]['total'] = learndash_get_course_steps_count( $course_id );
	
	/**
	 * Track the last post_id (Lesson, Topic, Quiz) seen by user.
	 * 
	 * @since 2.1.0
	 */
	$course_progress[ $course_id ]['last_id'] = $post->ID;

	// If course is completed
	if ( $course_progress[ $course_id ]['completed'] > $completed_old && $course_progress[ $course_id ]['total'] == $course_progress[ $course_id ]['completed'] ) {
		
		/**
		 * Run actions before course is completed
		 * 
		 * @since 2.1.0
		 */
		do_action( 'learndash_before_course_completed', array(
				'user' => $current_user, 
				'course' => get_post( $course_id ), 
				'progress' => $course_progress,
			) 
		);
	}
	
	update_user_meta( $user_id, '_sfwd-course_progress', $course_progress );

	if ( ! empty( $topic_completed ) ) {

		/**
		 * Run actions after topic is completed
		 * 
		 * @since 2.1.0
		 */
		do_action( 'learndash_topic_completed', array(
				'user' => $current_user, 
				'course' => get_post( $course_id ), 
				'lesson' => get_post( $lesson_id ), 
				'topic' => $post, 
				'progress' => $course_progress,
			)
		);
		
		
		learndash_update_user_activity(
			array(
				'user_id'				=>	$current_user->ID,
				'post_id'				=>	$post->ID,
				'activity_type'			=>	'topic',
				'activity_status'		=>	true,
				'activity_completed'	=>	time(),
				'activity_meta'			=>	array( 
												'steps_total'		=>	$course_progress[ $course_id ]['total'],
												'steps_completed'	=>	$course_progress[ $course_id ]['completed'],
											)
				
			)
		);
		
		learndash_update_user_activity(
			array(
				'user_id'			=>	$current_user->ID,
				'post_id'			=>	$course_id,
				'activity_type'		=>	'course',
				'activity_meta'		=>	array( 
											'steps_total'		=>	$course_progress[ $course_id ]['total'],
											'steps_completed'	=>	$course_progress[ $course_id ]['completed'],
											'steps_last_id'		=>	$post->ID
										)
			)
		);
	} 

	if ( ! empty( $lesson_completed ) ) {

		/**
		 * Run actions lesson is completed
		 * 
		 * @since 2.1.0
		 */
		do_action( 'learndash_lesson_completed', array(
				'user' => $current_user, 
				'course' => get_post( $course_id ), 
				'lesson' => $post, 
				'progress' => $course_progress,
			) 
		);
		
		learndash_update_user_activity(
			array(
				'user_id'				=>	$current_user->ID,
				'post_id'				=>	$post->ID,
				'activity_type'			=>	'lesson',
				'activity_status'		=>	true,
				'activity_completed'	=>	time(),
				'activity_meta'			=>	array( 
												'steps_total'		=>	$course_progress[ $course_id ]['total'],
												'steps_completed'	=>	$course_progress[ $course_id ]['completed'],
											)
				
			)
		);
		
		learndash_update_user_activity(
			array(
				'user_id'	=>	$current_user->ID,
				'post_id'	=>	$course_id,
				'activity_type'		=>	'course',
				//'activity_status'	=>	true,
				'activity_meta'		=>	array( 
											'steps_total'		=>	$course_progress[ $course_id ]['total'],
											'steps_completed'	=>	$course_progress[ $course_id ]['completed'],
											'last_id'			=>	$post->ID
										)
			)
		);
	}
	
	if ( $course_progress[ $course_id ]['completed'] > $completed_old && $course_progress[ $course_id ]['total'] == $course_progress[ $course_id ]['completed'] ) {
		/**
		 * Run actions after course is completed
		 * 
		 * @since 2.1.0
		 */
		do_action( 'learndash_course_completed', array(
				'user' => $current_user, 
				'course' => get_post( $course_id ), 
				'progress' => $course_progress,
			) 
		);
		
		learndash_update_user_activity(
			array(
				'user_id'				=>	$current_user->ID,
				'post_id'				=>	$course_id,
				'activity_type'			=>	'course',
				'activity_status'		=>	true,
				'activity_completed'	=>	time(),
				'activity_meta'			=>	array( 
												'steps_total'		=>	$course_progress[ $course_id ]['total'],
												'steps_completed'	=>	$course_progress[ $course_id ]['completed'],
												'last_id'	=>	$post->ID
											)
			)
		);
		
	}

	return true;

}



/**
 * Helper to update completion resource
 *
 * @todo  seems redundant, function already exists
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$postid
 * @return bool if user meta was updated
 */
function learndash_update_completion( $user_id = null, $postid = null ) {
	if ( empty( $postid ) ) {
		global $post;
		$postid = $post->ID;
	}

	learndash_process_mark_complete( $user_id, $postid, true );
}



/**
 * Is quiz complete
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$quiz_id
 * @return bool
 */
function learndash_is_quiz_complete( $user_id = null, $quiz_id ) {
	return ! learndash_is_quiz_notcomplete( $user_id, array($quiz_id => 1) );
}



/**
 * Is quiz not complete
 *
 * Checks against quizzes in user meta and passing percentage of the quiz itself
 * 
 * @since 2.1.0
 * 
 * @param  int 		$user_id
 * @param  array 	$quizes
 * @return bool
 */
function learndash_is_quiz_notcomplete( $user_id = null, $quizes = null ) {
	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	$quiz_results = get_user_meta( $user_id, '_sfwd-quizzes', true );

	if ( ! empty( $quiz_results ) && is_array( $quiz_results ) ) {
		foreach ( $quiz_results as $quiz ) {
			if ( ! empty( $quizes[ $quiz['quiz'] ] ) ) {
				if ( isset( $quiz['pass'] ) ) {
					$pass = ( $quiz['pass'] == 1 ) ? 1 : 0;
				} else {
					$quizmeta = get_post_meta( $quiz['quiz'], '_sfwd-quiz', true );
					$passingpercentage = intVal( $quizmeta['sfwd-quiz_passingpercentage'] );
					$pass = ( ! empty( $quiz['count'] ) && $quiz['score'] * 100 / $quiz['count'] >= $passingpercentage ) ? 1 : 0;
				}

				if ( $pass ) {
					unset( $quizes[ $quiz['quiz'] ] );
				}
			}
		}
	}

	if ( empty( $quizes ) ) {
		return 0;
	} else {
		return 1;
	}
}



/**
 * Return array of where the user currently is in course
 * 
 * @since 2.1.0
 * 
 * @param  int 		$user_id
 * @param  int 		$postid
 * @return array 	list of courses, topics, lessons
 *                  current, previous, next
 */
function learndash_get_course_progress( $user_id = null, $postid = null ) {
	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();

		if ( empty( $current_user->ID ) ) {
			return null;
		}

		$user_id = $current_user->ID;
	}

	$course_id = learndash_get_course_id( $postid );
	$lessons = learndash_get_lesson_list( $course_id );
	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
	$this_post = get_post( $postid );

	if ( empty( $course_progress ) ) {
		learndash_update_completion( $user_id, $postid );
		$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
	}

	if ( $this_post->post_type == 'sfwd-lessons' ) {
		$posts = learndash_get_lesson_list( $postid );

		if ( empty( $course_progress ) || empty( $course_progress[ $course_id ]['lessons'] ) ) {
			$completed_posts = array();
		} else {
			$completed_posts = $course_progress[ $course_id ]['lessons'];
		}
	} else if ( $this_post->post_type == 'sfwd-topic' ) {
		$lesson_id = learndash_get_setting( $this_post, 'lesson' );
		$posts = learndash_get_topic_list( $lesson_id );

		if ( empty( $course_progress ) || empty( $course_progress[ $course_id ]['topics'][ $lesson_id ] ) ) {
			$completed_posts = array();
		} else {
			$completed_posts = $course_progress[ $course_id ]['topics'][ $lesson_id ];
		}
	}

	$temp = $prev_p = $next_p = $this_p = '';

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $k => $post ) {

			if ( ! empty( $completed_posts[ $post->ID] ) ) {
				$posts[ $k ]->completed = 1;
			} else {
				$posts[ $k ]->completed = 0;
			}

			if ( $post->ID == $postid ) {
				$this_p = $post;
				$prev_p = $temp;
			}

			if ( ! empty( $temp->ID ) && $temp->ID == $postid ) {
				$next_p = $post;
			}

			$temp = $post;
		}

	}

	return array(
		'posts' => $posts, 
		'this' => $this_p, 
		'prev' => $prev_p, 
		'next' => $next_p,
	);	
}



/**
 * Is lesson complete
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$lesson_id
 * @return bool 
 */
function learndash_is_lesson_complete( $user_id = null, $lesson_id ) {
	return ! learndash_is_lesson_notcomplete( $user_id, array( $lesson_id => 1 ) );
}



/**
 * Is lesson not complete
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$lesson_id
 * @return bool 
 */
function learndash_is_lesson_notcomplete( $user_id = null, $lessons ) {
	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	if ( ! empty( $lessons ) ) {
		foreach ( $lessons as $lesson => $v ) {
			$course_id = learndash_get_course_id( $lesson );
			if ( ! empty( $course_progress[ $course_id ] ) && ! empty( $course_progress[ $course_id ]['lessons'] ) && ! empty( $course_progress[ $course_id ]['lessons'][ $lesson ] ) ) {
				unset( $lessons[ $lesson ] );
			}
		}
	}

	if ( empty( $lessons ) ) {
		return 0;
	} else {
		return 1;
	}
}

/**
 * Is topic complete
 * 
 * @since 2.3.0.2
 * 
 * @param  int 	$user_id
 * @param  int 	$topic_id
 * @return bool 
 */
function learndash_is_topic_complete( $user_id = null, $topic_id ) {
	return ! learndash_is_topic_notcomplete( $user_id, array( $topic_id => 1 ) );
}



/**
 * Is topic not complete
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$lesson_id
 * @return bool 
 */
function learndash_is_topic_notcomplete( $user_id = null, $topics ) {
	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	if ( ! empty( $topics ) ) {
		foreach ( $topics as $topic_id => $v ) {
			$course_id = learndash_get_course_id( $topic_id );
			$lesson_id = learndash_get_lesson_id( $topic_id );
			
			if ( ( isset( $course_progress[ $course_id ] ) ) 
				&& ( ! empty( $course_progress[ $course_id ] ) )
			    && ( isset( $course_progress[ $course_id ]['topics'] ) ) 
			    && ( ! empty( $course_progress[ $course_id ]['topics'] ) ) 
			    && ( isset( $course_progress[ $course_id ]['topics'][ $lesson_id ][ $topic_id ] ) ) 
			    && ( ! empty( $course_progress[ $course_id ]['topics'][ $lesson_id ][ $topic_id ] ) ) ) {
				unset( $topics[ $topic_id ] );
			}
		}
	}

	if ( empty( $topics ) ) {
		return 0;
	} else {
		return 1;
	}
}



/**
 * Output current status of course
 * 
 * @since 2.1.0
 * 
 * @param  int 		$id
 * @param  int 		$user_id
 * @return string 	output of current course status
 */
function learndash_course_status( $id, $user_id = null ) {
	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	$has_completed_topic = false;

	if ( ! empty( $course_progress[ $id ] ) && ! empty( $course_progress[ $id ]['topics'] ) && is_array( $course_progress[ $id ]['topics'] ) ) {
		foreach ( $course_progress[ $id ]['topics'] as $lesson_topics ) {
			if ( ! empty( $lesson_topics ) && is_array( $lesson_topics ) ) {
				foreach ( $lesson_topics as $topic ) {
					if ( ! empty( $topic ) ) {
						$has_completed_topic = true;
						break;
					}
				}
			}

			if ( $has_completed_topic ) {
				break;
			}
		}
	}

	$quiz_notstarted = true;
	$quizzes = learndash_get_global_quiz_list( $id );

	if ( ! empty( $quizzes ) ) {
		foreach ( $quizzes as $quiz ) {
			if ( ! learndash_is_quiz_notcomplete( $user_id, array( $quiz->ID => 1 ) ) ) {
				$quiz_notstarted = false;
			}
		}
	}

	if ( ( empty( $course_progress[ $id ] ) || empty( $course_progress[ $id ]['lessons'] ) && ! $has_completed_topic ) && $quiz_notstarted ) {
		return __( 'Not Started', 'learndash' );
	} else if ( empty( $course_progress[ $id ] ) || @$course_progress[ $id ]['completed'] < @$course_progress[ $id ]['total'] ) {
		return __( 'In Progress', 'learndash' );
	} else {
		return __( 'Completed', 'learndash' );
	}
}


/**
 * Get the course status idex from the course status label
 *
 * In various places with LD the course status is expressed as a string as in 'Not Started', 'In Progress' or 'Complete'. 
 * the problem with using this string is it will be translated depending on the locale(). This means comparative logic can 
 * possible fails. 
 *
 * The purpose of this function is to help use an internal key to keep track of the course status value
 * 
 * @since 2.3
 * 
 * @uses $learndash_course_statuses
 * @param  string 	$course_status_label Current translatable text for Course Status
 * @return string 	The index/key of the course status string if found in the $learndash_course_statuses array
 */
function learndash_course_status_idx( $course_status_label = '' ) {
	global $learndash_course_statuses;
	
	return array_search( $course_status_label, $learndash_course_statuses );
}


/**
 * Output HTML template of users course progress
 * 
 * @since 2.1.0
 * 
 * @param  array $atts shortcode attributes
 * @return string      shortcode output
 */
function learndash_course_progress( $atts ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;
	
	extract( shortcode_atts( array( 'course_id' => 0, 'user_id' => 0, 'array' => false, ), $atts ) );

	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	if ( empty( $course_id ) ) {
		$course_id = learndash_get_course_id();
	}

	if ( empty( $course_id ) ) {
		return '';
	}

	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	$percentage = 0;
	$message = '';

	if ( ! empty( $course_progress ) && ! empty( $course_progress[ $course_id ] ) && ! empty( $course_progress[ $course_id ]['total'] ) ) {
		$completed = intVal( $course_progress[ $course_id ]['completed'] );
		$total = intVal( $course_progress[ $course_id ]['total'] );

		if ( $completed == $total - 1 ) {
			learndash_update_completion( $user_id );
			$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
			$completed = intVal( $course_progress[ $course_id ]['completed'] );
			$total = intVal( $course_progress[ $course_id ]['total'] );
		}

		$percentage = intVal( $completed * 100 / $total );
		$percentage = ( $percentage > 100 ) ? 100 : $percentage;
		$message = sprintf( __('%d out of %d steps completed', 'learndash' ), $completed, $total );
	}

	if ( $array ) {
		return array(
			'percentage' => @$percentage, 
			'completed' => @$completed, 
			'total' => @$total
		);
	}

	return SFWD_LMS::get_template( 'course_progress_widget', array(
		'message' => @$message, 
		'percentage' => @$percentage, 
		'completed' => @$completed, 
		'total' => @$total,
		) 
	);
}

add_shortcode( 'learndash_course_progress', 'learndash_course_progress' );



/**
 * Is quiz accessible to user
 * 
 * @since 2.1.0
 * 
 * @param  int  	$user_id
 * @param  object 	$post    WP_Post quiz
 * @return bool
 */
function is_quiz_accessable( $user_id = null, $post = null ) {
	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();

		if ( empty( $current_user->ID ) ) {
			return 1;
		}

		$user_id = $current_user->ID;
	}

	if ( ( !empty( $post ) ) && ( $post instanceof WP_Post ) ) {
		$quiz_lesson = learndash_get_setting( $post, 'lesson' );

		if ( ! empty( $quiz_lesson ) ) {
			$quiz_lesson_post = get_post( $quiz_lesson );
			if (( $quiz_lesson_post instanceof WP_Post) && ( $quiz_lesson_post->post_type == 'sfwd-topic' )) {
				return 1;
			} else if ( learndash_lesson_topics_completed( $quiz_lesson ) ) {
				return 1;
			} else {
				return 0;
			}
		} else {
			$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
			$course_id = learndash_get_course_id( $post->ID );

			if ( ! empty( $course_progress ) && ! empty( $course_progress[ $course_id ] ) && ! empty( $course_progress[ $course_id ]['total'] ) ) {
				$completed = intVal( $course_progress[ $course_id ]['completed'] );
				$total = intVal( $course_progress[ $course_id ]['total'] );

				if ( $completed >= $total - 1 ) {
					return 1;
				}
			}

			$lessons = learndash_get_lesson_list( $course_id );

			if ( empty( $lessons ) ) {
				return 1;
			}
		}
	}
	return 0;
}



/**
 * Check if all quizzes for course are complete for user
 * 
 * @since 2.1.0
 * 
 * @param  int  $user_id
 * @param  int  $id
 * @return bool
 */
function is_all_global_quizzes_complete( $user_id = null, $id = null ) {
	$quizzes = learndash_get_global_quiz_list( $id );
	$return = true;

	if ( ! empty( $quizzes ) ) {
		foreach ( $quizzes as $quiz ) {
			if ( learndash_is_quiz_notcomplete( $user_id, array( $quiz->ID => 1 ) ) ) {
				$return = false;
			}
		}
	}

	return $return;
}



/**
 * Get next quiz for course
 * 
 * @since 2.1.0
 * 
 * @param  bool 		$url     	return a url
 * @param  int  		$user_id 
 * @param  int  		$id
 * @param  array   		$exclude 	excluded quiz id's
 * @return int|string           	id of quiz or url of quiz
 */
function learndash_next_global_quiz( $url = true, $user_id = null, $id = null, $exclude = array() ) {
	if ( empty( $id ) ) {
		$id = learndash_get_course_id();
	}

	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	$quizzes = learndash_get_global_quiz_list( $id );
	$return = get_permalink( $id );

	if ( ! empty( $quizzes ) ) {
		foreach ( $quizzes as $quiz ) {
			if ( ! in_array( $quiz->ID, $exclude ) && learndash_is_quiz_notcomplete( $user_id, array( $quiz->ID => 1 ) ) && learndash_can_attempt_again( $user_id, $quiz->ID ) ) {
				if ( $url ) {
					return get_permalink( $quiz->ID );
				} else {
					return $quiz->ID;
				}
			}
		}
	}

	/**
	 * Filter return value of next global quiz
	 *
	 * @todo  filter name does not seem correct
	 *        in context of function
	 *
	 * @since 2.1.0
	 * 
	 * @var id|string 	$return
	 */
	$return = apply_filters( 'learndash_course_completion_url', $return, $id );
	return $return;
}



/**
 * Get next quiz for current lesson for current user
 * 
 * @since 2.1.0
 *
 * @param  bool 		$url     	return a url
 * @param  int  		$user_id 
 * @param  int  		$lesson_id 	
 * @param  array   		$exclude 	excluded quiz id's
 * @return int|string           	id of quiz or url of quiz
 */
function learndash_next_lesson_quiz( $url = true, $user_id = null, $lesson_id = null, $exclude = array() ) {
	global $post;

	$return = false;

	if ( empty( $lesson_id ) ) {
		$lesson_id = $post->ID;
	}

	if ( empty( $exclude ) ) {
		$exclude = array();
	}

	if ( empty( $user_id ) ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	}

	// Assumption here is the learndash_get_lesson_quiz_list returns the quizzes in the order they should be taken.
	$quizzes = learndash_get_lesson_quiz_list( $lesson_id, $user_id );
	if ((!empty($quizzes)) && (is_array($quizzes))) {
		foreach ( $quizzes as $quiz ) {
			// The logic here is we need to check all the quizzes in this lesson. If all the quizzes are complete
			// (including the current one) then we set the parent (lesson) to complete.
			if ( $quiz['status'] == 'completed' ) continue;

			// If not complete AND the user CAN take the quiz again...
			if ( learndash_can_attempt_again( $user_id, $quiz['post']->ID ) ) {
				$return = ( $url ) ? get_permalink( $quiz['post']->ID ) : $quiz['post']->ID;
				break;
			}

			$return = ( $url ) ? get_permalink( $quiz['post']->ID ) : $quiz['post']->ID;
			//$return = ( $url ) ? get_permalink( $lesson_id ) : $lesson_id;
			break;		
		}
	}
	
	if ( empty( $return ) ) {
		learndash_process_mark_complete( $user_id, $lesson_id );
	} else {
		return $return;
	}
}



/**
 * Does resource have quizzes?
 * 
 * @since 2.1.0
 * 
 * @param  int  	$id 	
 * @return bool
 */
function has_global_quizzes( $id = null ) {
	$quizzes = learndash_get_global_quiz_list( $id );
	return ! empty( $quizzes );
}



/**
 * Course Progress Widget
 */
class LearnDash_Course_Progress_Widget extends WP_Widget {

	/**
	 * Set up course project widget
	 */
	function __construct() {
		$widget_ops = array( 
			'classname' => 'widget_ldcourseprogress',
			'description' => sprintf( _x( 'LearnDash %s progress bar', 'placeholders: course', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) )
		);
		$control_ops = array(); //'width' => 400, 'height' => 350);
		parent::__construct( 'ldcourseprogress', sprintf( _x( '%s Progress Bar', 'Course Progress Bar Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), $widget_ops, $control_ops );
	}



	/**
	 * Displays widget
	 * 
	 * @since 2.1.0
	 * 
	 * @param  array $args     widget arguments
	 * @param  array $instance widget instance
	 * @return string          widget output
	 */
	function widget( $args, $instance ) {
		global $learndash_shortcode_used;
		
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );

		if ( ! is_singular() ) {
			return;
		}

		$progressbar = learndash_course_progress( $args );

		if ( empty( $progressbar ) ) {
			return;
		}

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		echo $progressbar;
		echo $after_widget;
		
		$learndash_shortcode_used = true;
	}



	/**
	 * Handles widget updates in admin
	 * 
	 * @since 2.1.0
	 * 
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array $instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}



	/**
	 * Display widget form in admin
	 * 
	 * @since 2.1.0
	 * 
	 * @param  array $instance widget instance
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array)$instance, array('title' => '') );
		$title = strip_tags( $instance['title'] );
		//$text = format_to_edit( $instance['text'] );
		?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'learndash' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		<?php
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("LearnDash_Course_Progress_Widget");' ) );



/**
 * Output HTML of course progress for user
 *
 * @todo consider for deprecation, not in use
 * 
 * @since 2.1.0
 * 
 * @param  $atts
 */
function learndash_course_progress_widget( $atts ) {
	echo learndash_course_progress( $atts );
}



/**
 * Is progression enabled for lesson
 * 
 * @since 2.1.0
 * 
 * @return bool
 */
function learndash_lesson_progression_enabled() {
	$id = learndash_get_course_id();
	$meta = get_post_meta( $id, '_sfwd-courses' );
	return empty( $meta[0]['sfwd-courses_course_disable_lesson_progression'] );
}




/**
 * Get lesson time for lesson if it exists
 * 
 * @since 2.1.0
 */
function learndash_forced_lesson_time() {
	global $post;
	
	if ( empty( $post->ID ) ) {
		return 0;
	}

	$meta = get_post_meta( $post->ID, '_' . $post->post_type );

	if ( ! empty( $meta[0][ $post->post_type . '_forced_lesson_time'] ) ) {
		return $meta[0][ $post->post_type . '_forced_lesson_time'];
	} else {
		return 0;
	}
}



/**
 * Is course completed for user
 * 
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$course_id
 * @return bool
 */
function learndash_course_completed( $user_id, $course_id ) {
	if ( learndash_course_status( $course_id, $user_id ) == __( 'Completed', 'learndash' ) ) {
		return true;
	} else {
		return false;
	}
}



/**
 * Add course completion date to user meta
 *
 * @since 2.1.0
 * 
 * @param  array $data
 */
function learndash_course_completed_store_time( $data ) {
	$user_id = $data['user']->ID;
	$course_id = $data['course']->ID;
	$meta_key = 'course_completed_'.$course_id;
	$meta_value = time();

	$course_completed = get_user_meta( $user_id, $meta_key );

	if ( empty( $course_completed ) ) {
		update_user_meta( $user_id, $meta_key, $meta_value );
	}
}

add_action( 'learndash_before_course_completed', 'learndash_course_completed_store_time', 10, 1 );



/**
 * Delete course progress for user
 *
 * @since 2.1.0
 * 
 * @param  int $course_id
 * @param  int $user_id
 */
function learndash_delete_course_progress( $course_id, $user_id ) {
	global $wpdb;
	$usermeta = get_user_meta( $user_id, '_sfwd-course_progress', true );

	if ( isset( $usermeta[ $course_id] ) ) {
		unset( $usermeta[ $course_id] );
		update_user_meta( $user_id, '_sfwd-course_progress', $usermeta );
	}

	$quizzes = get_posts( 
		array(
			'post_type' => 'sfwd-quiz',
			'meta_key'	=> 'course_id',
			'meta_value' => $course_id
		)
	);

	foreach ( $quizzes as $quiz ) {
		learndash_delete_quiz_progress( $user_id, $quiz->ID );
	}
}



/**
 * Delete quiz progress for user
 *
 * @since 2.1.0
 * 
 * @param  int $user_id
 * @param  int $quiz_id
 */
function learndash_delete_quiz_progress( $user_id, $quiz_id ) {
	global $wpdb;

	//Clear User Meta
	$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );

	if ( ! empty( $usermeta) && is_array( $usermeta ) ) {
		foreach ( $usermeta as $key => $quizmeta ) {
			if ( $quizmeta['quiz'] != $quiz_id ) {
				$usermeta_new[] = $quizmeta;
			}
		}
		update_user_meta( $user_id, '_sfwd-quizzes', $usermeta_new );
	}

	//Lesson/Topic
	/*
	$lesson_id = learndash_get_setting( $quiz_id, "lesson");
	if(!empty( $lesson_id )) {
	$lesson_post = get_post( $lesson_id );
	if( $lesson_post->post_type == "sfwd-lessons")
		learndash_specific_mark_lesson_incomplete( $user_id, $lesson_id );
	else if( $lesson_post->post_type == "sfwd-topic")
		learndash_specific_mark_topic_incomplete( $user_id, $lesson_id );
	}*/

	//Pro Quiz Data
	$pro_quiz_id = learndash_get_setting( $quiz_id, 'quiz_pro' );

	$ref_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT statistic_ref_id FROM '.$wpdb->prefix."wp_pro_quiz_statistic_ref WHERE  user_id = '%d' AND quiz_id = '%d' ", $user_id, $pro_quiz_id ) );

	if ( ! empty( $ref_ids[0] ) ) {
		$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_statistic_ref', array( 'user_id' => $user_id, 'quiz_id' => $pro_quiz_id ) );
		$wpdb->query( 'DELETE FROM '.$wpdb->prefix.'wp_pro_quiz_statistic WHERE statistic_ref_id IN ('.implode( ',', $ref_ids ).')' );
	}

	//$wpdb->query("DELETE FROM ".$wpdb->usermeta." WHERE meta_key LIKE 'completed_%' AND user_id = '".$user->ID."'");
	$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_toplist',  array( 'user_id' => $user_id, 'quiz_id' => $pro_quiz_id ) );
}
