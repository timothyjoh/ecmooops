<?php

add_filter( 'learndash_post_args', 'wdm_skip_lesson_admin', 10, 1 );
/**
 * back-end part:
 * adds checkbox to skip lesson to sub-object topic/quiz.
 */
function wdm_skip_lesson_admin( $post_args ) {
	if( is_admin() ) {
		if ( ! empty( $_GET ) && ( isset( $_GET[ 'post' ] ) ) ) { // while page loads
			$post_id = $_GET[ 'post' ];
		} elseif ( isset( $_POST[ 'post_ID' ] ) ) { // it is necessary because when form submitted, post id becomes blank
			$post_id = $_POST[ 'post_ID' ];
		} else {
			$post_id = '';
		}

		foreach ( $post_args as $key => $val ) {

			if ( $post_args[ $key ][ 'slug_name' ] == 'lessons' ) { // if lesson page


				if ( isset( $_POST[ 'sfwd-lessons_wdm_skip_lesson' ] ) ) {
					$lesson_skip = $_POST[ 'sfwd-lessons_wdm_skip_lesson' ];
					if ( ! empty( $post_id ) ) {
						update_post_meta( $post_id, '_sfwd_wdm_skip_lesson', $lesson_skip );
					}
					unset( $_POST[ 'sfwd-lessons_wdm_skip_lesson' ] ); // because it will save in quiz meta, we do not require to store it in LD quiz meta, it will store as post meta for current quiz.
				} elseif ( isset( $_POST[ 'save' ] ) ) { // if posted but skip lesson is unchecked

					update_post_meta( $post_id, '_sfwd_wdm_skip_lesson', 'off' );
				}


				$wdm_meta = get_post_meta( $post_id, '_sfwd_wdm_skip_lesson', true );

				// to show custom meta element on a lesson page
				$post_args[ $key ][ 'fields' ][ 'wdm_skip_lesson' ] = array(
					'name'		 => 'Skip lesson',
					'type'		 => 'checkbox',
					'default'	 => ( $wdm_meta == 'on' ) ? '1' : '0',
					'help_text'	 => 'Select it if want to skip lesson to sub-object.'
				);
			} // if ( $post_args[ $key ][ 'slug_name' ] == 'lessons' )
			
		} // foreach ( $post_args as $key => $val ) 
	}
	return $post_args;
}

add_action( 'wp', 'wdm_ld_content' );

/**
 *  to redirect first topic or first quiz if lesson is set to skip.
 */
function wdm_ld_content() {
	// Never do this for admin pages, otherwise you will be redirected
	// from admin dashboard to the client-facing quiz or topic.
	// Lesson edits will be locked out.
	if( is_admin() ) return;

	$post_id = get_the_ID(); //1262

	if ( ! empty( $post_id ) ) {
		$wdm_lesson_skip_meta = get_post_meta( $post_id, '_sfwd_wdm_skip_lesson', true );

		if ( $wdm_lesson_skip_meta == 'on' ) {

			if ( get_post_type( $post_id ) == 'sfwd-lessons' ) { // if current post type is a lesson
				$topics	 = learndash_get_topic_list( $post_id );
				$topics	 = array_filter( $topics );
				
				$quizzes = learndash_get_lesson_quiz_list( $post_id );
				$quizzes = array_filter( $quizzes );
				
				if ( ! empty( $topics ) ) {
					$first_topic = $topics[ 0 ]->ID;
					wp_redirect( get_permalink( $first_topic ) );
					//exit;
				} else if ( ! empty( $quizzes ) ) { 
					if( isset( $quizzes[ 1 ][ 'permalink' ] ) ) { // first quiz of a lesson
						wp_redirect( $quizzes[ 1 ][ 'permalink' ] );
					}
				}
			}
		}
	}
}
