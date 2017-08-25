<?php

/* Plugin Name: Lesson Completion on quiz completion
 * Plugin URI: http://wisdmlabs.com/
 * Description: 
 * Version: 1.0
 * Author: Wisdmlabs
 * */

add_action( "learndash_quiz_completed", function($data) {

//Called when quiz is completed.
	//echo '<pre>';print_R($data);echo '</pre>';exit;
	$quiz_id = $data[ 'quiz' ]->ID;

	$course_id	 = learndash_get_course_id( $quiz_id );
	$lesson_id	 = learndash_get_lesson_id( $quiz_id );
	$user_id	 = get_current_user_id();

	//echo 'lesson '.$lesson_id;exit;
	$lessons = array();
	if ( $lesson_id != '' && $lesson_id != 0 ) {
		$lessons[ $lesson_id ]	 = get_post( $lesson_id );
		$lesson_notcomplete		 = learndash_is_lesson_notcomplete( $user_id, $lessons );
		if ( $lesson_notcomplete ) {
			//echo "asdasdasd";exit;

			$quizzes = get_posts( array( 'post_type' => 'sfwd-quiz', 'posts_per_page' => -1, 'meta_key' => 'lesson_id', 'meta_value' => $lesson_id, 'meta_compare' => '=', 'orderby' => $orderby, 'order' => $order ) );
			//echo '<pre>';print_R($quizzes);echo '</pre>';exit;
			if ( ! empty( $quizzes ) ) {
				$quiz_array = array();
				foreach ( $quizzes as $key => $value ) {
					$quiz_array[ $value->ID ] = $value->ID;
				}
				$flag = learndash_is_quiz_notcomplete( $user_id, $quiz_array );

				if ( ! $flag ) {
					$return = learndash_process_mark_complete( $user_id, $lesson_id, false );
				}
			}
		}
	} else {
		$return = learndash_process_mark_complete( $user_id, $quiz_id, false );
		//echo 'fdsdfas '.$return;exit;
	}
}, 5, 1 );



add_action('wp_head','wdm_back_on_quiz_page');
function wdm_back_on_quiz_page(){
	global $post_type;
	if($post_type == 'sfwd-quiz'){
		wp_enqueue_script('wdm_back_js',plugins_url('wdm_back.js',__FILE__),array('jquery'));
	}
}


add_action( 'switch_to_user', 'switch_to_user_redirect' );
function switch_to_user_redirect()
{
	wp_redirect( home_url(  ) );
	exit();
}

add_action( 'switch_back_user', 'switch_back_user_redirect' );
function switch_back_user_redirect()
{
	wp_redirect( home_url( 'wp-admin' ) );
	exit();
}