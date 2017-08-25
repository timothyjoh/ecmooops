<?php
/**
 * Shortcodes for displaying Quiz and Course info
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Shortcodes
 */



/**
 * Shortcode that displays the requested quiz information
 * 
 * @since 2.1.0
 * 
 * @param  array $attr shortcode attributes
 * @return string      shortcode output
 */
function learndash_quizinfo( $attr ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;
	
	$shortcode_atts = shortcode_atts(
		array(
			'show'    => '', //[score], [count], [pass], [rank], [timestamp], [pro_quizid], [points], [total_points], [percentage], [timespent]
			'user_id' => '',
			'quiz'    => '',
			'time'    => '',
			'format'  => 'F j, Y, g:i a',
		), 
		$attr 
	);

	extract( $shortcode_atts );

	$time    = ( empty( $time ) && isset( $_REQUEST['time'] ) ) ? $_REQUEST['time'] : $time;
	$show    = ( empty( $show ) && isset( $_REQUEST['show'] ) ) ? $_REQUEST['show'] : $show;	
	$quiz    = ( empty( $quiz ) && isset( $_REQUEST['quiz'] ) ) ? $_REQUEST['quiz'] : $quiz;
	$user_id = ( empty( $user_id ) && isset( $_REQUEST['user_id'] ) ) ? $_REQUEST['user_id'] : $user_id;

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
		
		/**
		 * Added logic to allow admin and group_leader to view certificate from other users. 
		 * @since 2.3
		 */
		$post_type = '';
		if ( get_query_var( 'post_type' ) ) {
			$post_type = get_query_var( 'post_type' );
		}

		if ( $post_type == 'sfwd-certificates' ) {
			if ( ( ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) && ( ( isset( $_GET['user'] ) ) && (!empty( $_GET['user'] ) ) ) ) {
				$user_id = intval( $_GET['user'] );
			}
		}
	}

	if ( empty( $quiz) || empty( $user_id ) || empty( $show) ) {
		return '';
	}

	$quizinfo = get_user_meta( $user_id, '_sfwd-quizzes', true );

	$selected_quizinfo = '';
	$selected_quizinfo2 = '';
	
	foreach ( $quizinfo as $quiz_i ) {

		if ( isset( $quiz_i['time'] ) && $quiz_i['time'] == $time && $quiz_i['quiz'] == $quiz ) {
			$selected_quizinfo = $quiz_i;
			break;
		}

		if ( $quiz_i['quiz'] == $quiz ) {
			$selected_quizinfo2 = $quiz_i;
		}
	}

	$selected_quizinfo = empty( $selected_quizinfo ) ? $selected_quizinfo2 : $selected_quizinfo;

	switch ( $show ) {
		case 'timestamp':
			date_default_timezone_set( get_option( 'timezone_string' ) );
			$selected_quizinfo['timestamp'] = date_i18n( $format, $selected_quizinfo['time'] );
			break;

		case 'percentage':		
			if ( empty( $selected_quizinfo['percentage'] ) ) {
				$selected_quizinfo['percentage'] = empty( $selected_quizinfo['count'] ) ? 0 : $selected_quizinfo['score'] * 100 / $selected_quizinfo['count'];
			}

			break;

		case 'pass':
			$selected_quizinfo['pass'] = ! empty( $selected_quizinfo['pass'] ) ? __( 'Yes', 'learndash' ) : __( 'No', 'learndash' );
			break;

		case 'quiz_title':
			$quiz_post = get_post( $quiz );

			if ( ! empty( $quiz_post->post_title) ) {
				$selected_quizinfo['quiz_title'] = $quiz_post->post_title;
			}

			break;

		case 'course_title':
			$course_id = learndash_get_setting( $quiz, 'course' );
			$course    = get_post( $course_id );

			if ( ! empty( $course->post_title) ) {
				$selected_quizinfo['course_title'] = $course->post_title;
			}

			break;

		case 'timespent':
			$selected_quizinfo['timespent'] = isset( $selected_quizinfo['timespent'] ) ? learndash_seconds_to_time( $selected_quizinfo['timespent'] ) : '';
			break;

	}

	if ( isset( $selected_quizinfo[ $show ] ) ) {
		return $selected_quizinfo[ $show ];
	} else {
		return '';
	}
}

add_shortcode( 'quizinfo', 'learndash_quizinfo' );



/**
 * Shortcode that displays the requested course information
 * 
 * @since 2.1.0
 * 
 * @param  array $attr shortcode attributes
 * @return string      shortcode output
 */
function learndash_courseinfo( $attr ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;
	
	$shortcode_atts = shortcode_atts(
			array(
				'show'      => '', //name, cumulative_score, completed_on
				'user_id'   => '',
				'course_id' => '',
				'format'    => 'F j, Y, g:i a',
			), 
			$attr
		);

	extract( $shortcode_atts );

	$course_id = ! empty( $course_id ) ? $course_id : @$_GET['course_id'];
	$user_id   = ! empty( $user_id ) ? $user_id : @$_GET['user_id'];

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
		
		/**
		 * Added logic to allow admin and group_leader to view certificate from other users. 
		 * @since 2.3
		 */
		$post_type = '';
		if ( get_query_var( 'post_type' ) ) {
			$post_type = get_query_var( 'post_type' );
		}

		if ( $post_type == 'sfwd-certificates' ) {
			if ( ( ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) && ( ( isset( $_GET['user'] ) ) && (!empty( $_GET['user'] ) ) ) ) {
				$user_id = intval( $_GET['user'] );
			}
		}
		
	}

	if ( empty( $course_id ) || empty( $user_id ) ) {
		return '';
	}

	$show = strtolower( $show );

	switch ( $show ) {
		case 'course_title':
			$course = get_post( $course_id );
			return $course->post_title;

		case 'cumulative_score':
		case 'cumulative_points':
		case 'cumulative_total_points':
		case 'cumulative_percentage':
		case 'cumulative_timespent':
		case 'cumulative_count':
			$field    = str_replace( 'cumulative_', '', $show );
			$quizdata = get_user_meta( $user_id, '_sfwd-quizzes', true );			
			global $wpdb;
			$quizzes = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->postmeta . " WHERE meta_key = 'course_id' AND meta_value = '%d'", $course_id ) );
			
			if ( empty( $quizzes) ) {
				return 0;
			}

			$scores = array();

			if ( ( !empty( $quizdata ) ) && ( is_array( $quizdata ) ) ) {
				foreach ( $quizdata as $data ) {
					if ( in_array( $data['quiz'], $quizzes ) ) {
						if ( empty( $scores[ $data['quiz'] ] ) || $scores[ $data['quiz'] ] < $data[ $field ] ) {
							$scores[ $data['quiz'] ] = $data[ $field ];
						}
					}
				}
			}

			if ( empty( $scores) || ! count( $scores ) ) {
				return 0;
			}

			$sum = 0;

			foreach ( $scores as $score ) {
				$sum += $score;
			}

			$return = number_format( $sum / count( $scores ), 2 );

			if ( $field == 'timespent' ) {
				return learndash_seconds_to_time( $return );
			} else {
				return $return;
			}

		case 'aggregate_percentage':
		case 'aggregate_score':
		case 'aggregate_points':
		case 'aggregate_total_points':
		case 'aggregate_timespent':
		case 'aggregate_count':
			$field    = substr_replace( $show, '', 0, 10 );
			$quizdata = get_user_meta( $user_id, '_sfwd-quizzes', true );
			global $wpdb;
			$quizzes = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->postmeta . " WHERE meta_key = 'course_id' AND meta_value = '%d'", $course_id ) );
			
			if ( empty( $quizzes) ) {
				return 0;
			}

			$scores = array();
			
			if ( ( !empty( $quizdata ) ) && ( is_array( $quizdata ) ) ) {
				foreach ( $quizdata as $data ) {
					if ( in_array( $data['quiz'], $quizzes ) ) {
						if ( empty( $scores[ $data['quiz'] ] ) || $scores[ $data['quiz'] ] < $data[ $field ] ) {
							$scores[ $data['quiz'] ] = $data[ $field ];
						}
					}
				}
			}

			if ( empty( $scores) || ! count( $scores ) ) {
				return 0;
			}

			$sum = 0;

			foreach ( $scores as $score ) {
				$sum += $score;
			}

			$return = number_format( $sum, 2 );

			if ( $field == 'timespent' ) {
				return learndash_seconds_to_time( $return );
			} else {
				return $return;
			}

		case 'completed_on':
			$completed_on = get_user_meta( $user_id, 'course_completed_' . $course_id, true );

			if ( empty( $completed_on) ) {
				return '-';
			}

			date_default_timezone_set( get_option( 'timezone_string' ) );
			return date_i18n( $format, $completed_on );

		default:
			return '';
	}
}

add_shortcode( 'courseinfo', 'learndash_courseinfo' );
?>
