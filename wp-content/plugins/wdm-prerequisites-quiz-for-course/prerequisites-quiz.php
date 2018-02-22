<?php

/*
  Plugin Name: LearnDash prerequisites quiz for course
  Plugin URI: http://www.wisdmlabs.com
  Description: Checks paerticular quiz has been completed, if yes, then marks course complete.
  Version: 0.1
  Author: WisdmLabs
 * By - uday
 */


include_once 'skip-lesson.php'; // to skip lesson and start directly topic/quiz.

add_filter( 'learndash_post_args', 'wdm_set_course_final_quiz', 10, 1 );

/**
 * adds custom meta field to the "edit course" page in admin section.Gets the list of all associated quizzes of a course and gives functionality to select only one quiz as a final quiz.
 * @param array $post_args post arguments of a filter
 * @return array $post_args post arguments of a filter
 */
function wdm_set_course_final_quiz( $post_args ) {

	if ( is_admin() && ! empty( $_GET ) && ( isset( $_GET[ 'post' ] ) ) ) { // while page loads
		$post_id = $_GET[ 'post' ];
	} elseif ( isset( $_POST[ 'post_ID' ] ) ) { // it is necessary because when form submitted, post id becomes blank
		$post_id = $_POST[ 'post_ID' ];
	} else {
		$post_id = '';
	}

	foreach ( $post_args as $key => $val ) {

		if ( $post_args[ $key ][ 'slug_name' ] == 'courses' ) { // if course page
			$wdm_course_quizzes	 = learndash_get_global_quiz_list( $post_id ); // gets all associated quizzes
			if( 'array' !== gettype( $wdm_course_quizzes ) ) {
				$wdm_course_quizzes = array();
			}
			$wdm_course_quizzes	 = array_filter( $wdm_course_quizzes );

			$quiz_array = array( __( '-- Select a Quiz --', 'learndash' ) ); // default value

			if ( ! empty( $wdm_course_quizzes ) ) {
				foreach ( $wdm_course_quizzes as $value ) {
					$quiz_array[ $value->ID ] = $value->post_title;
				}
			}

			// to show custom meta element on a course page
			$post_args[ $key ][ 'fields' ][ 'wdm_final_quiz' ] = array(
				'name'				 => 'Final Quiz',
				'type'				 => 'select',
				'initial_options'	 => $quiz_array,
				'help_text'			 => 'Select quiz to complete the course.'
			);
		} // if ( $post_args[ $key ][ 'slug_name' ] == 'courses' )
	} // foreach ( $post_args as $key => $val ) 
	return $post_args;
}

add_filter( 'learndash_post_args', 'wdm_lessons_prerequisites', 10, 1 );

/**
 * adds custom field on edit quiz page, in which all corresponding lessons are listed. Here admin can select lesson(s) to be completed before starting quiz. 
 * @param array $post_args post arguments of a filter
 * @return array $post_args post arguments of a filter
 */
function wdm_lessons_prerequisites( $post_args ) {
	if ( is_admin() ) {
		if ( ! empty( $_GET ) && ( isset( $_GET[ 'post' ] ) ) ) { // while page loads
			$post_id = $_GET[ 'post' ];
		} elseif ( isset( $_POST[ 'post_ID' ] ) ) { // it is necessary because when form submitted, post id becomes blank
			$post_id = $_POST[ 'post_ID' ];
		} else {
			$post_id = '';
		}

		if ( isset( $_POST[ 'sfwd-quiz_wdm_prerequisite_lessons' ] ) ) {
			$quiz_wdm_prerequisite_lessons = base64_encode( serialize( $_POST[ 'sfwd-quiz_wdm_prerequisite_lessons' ] ) ); // base64_encode because throwing errors for quotes.
			if ( ! empty( $post_id ) ) {
				update_post_meta( $post_id, '_sfwd_wdm_pre_lessons', $quiz_wdm_prerequisite_lessons );
			}
			unset( $_POST[ 'sfwd-quiz_wdm_prerequisite_lessons' ] ); // because it will save in quiz meta, we do not require to store it in LD quiz meta, it will store as post meta for current quiz.
		}

		foreach ( $post_args as $key => $val ) {

			if ( $post_args[ $key ][ 'slug_name' ] == 'quizzes' ) { // if quiz page
				$course_id = learndash_get_course_id( $post_id );

				$lessons = array();

				if ( ! empty( $course_id ) ) {
					$lessons = learndash_get_course_lessons_list( $course_id );
				}

				$lessons_array = array( __( '-- Select Lessons --', 'learndash' ) ); // default value

				if ( ! empty( $lessons ) ) {
					foreach ( $lessons as $value ) {
						$lessons_array[ $value[ 'post' ]->ID ] = $value[ 'post' ]->post_title;
					}
				}

				$wdm_meta = get_post_meta( $post_id, '_sfwd_wdm_pre_lessons', true );

				$default = array( '0' );
				if ( ! empty( $wdm_meta ) ) {

					$prerequisite_lessons = unserialize( base64_decode( $wdm_meta ) );

					if ( ! empty( $prerequisite_lessons ) ) {
						$default = $prerequisite_lessons;
					}
				}

				//echo '<pre>default='; print_r( $default ); echo '</pre>';
				// to show custom meta element on a course page
				$arr								 = array();
				$arr[ 'wdm_prerequisite_lessons' ]	 = array(
					'name'				 => 'Prerequisite Lessons',
					'type'				 => 'multiselect',
					'initial_options'	 => $lessons_array,
					//'default'		=>  array( '403','318' ),
					'default'			 => $default,
					'help_text'			 => 'Select prerequisite lessons.'
				);

				$new_arr = array_slice( $post_args[ $key ][ 'fields' ], 0, 7 ) + $arr + array_slice( $post_args[ $key ][ 'fields' ], 2 ); // to fir it in between start of the quiz meta to display on edit quiz page.
				//echo '<pre>'; print_r( $new_arr ); echo '</pre>';

				$post_args[ $key ][ 'fields' ] = $new_arr;
			}
		} // foreach ( $post_args as $key => $val )
	}
	return $post_args;
}

add_action( 'learndash_quiz_completed', 'wdm_quiz_completed', 10, 2 );

function wdm_quiz_completed( $quizdata, $user ) {
	//echo '<pre>quizdata='; print_r( $quizdata ); echo '</pre>';

	if ( ! empty( $quizdata ) ) {
		if ( isset( $quizdata[ 'pass' ] ) && $quizdata[ 'pass' ] == 1 ) {
			if ( isset( $quizdata[ 'course' ] ) ) {
				$wdm_course_id = $quizdata[ 'course' ]->ID;
			}
		}
	}
}


/*
 * This function sets "prerequisite" element checked by default when page loads. It is because, to reduce efforts of admin to scroll down and check it manually.
 */
function wdm_pre_quiz_checked() {
	$currentScreen = get_current_screen();
	if ( $currentScreen->post_type == 'sfwd-quiz' ) {
		?>
<script>
	jQuery( document ).ready( function() {
		jQuery( "input[name=prerequisite]" ).prop('checked',true);
	});
</script>
		<?php

	}
}
add_action( 'admin_footer', 'wdm_pre_quiz_checked' );