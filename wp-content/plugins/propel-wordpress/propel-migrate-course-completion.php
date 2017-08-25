<?php

/**
 * Migrate course completion data to enrollments table
 * curl -H "Content-Type: application/json" -X POST -d '{"course":"xyz"}' http://host/wp-json/scitent/v1/migrate
 *     JSON payload is {course: course_id}
 */
function migrate( $data ) {
	$course_id  = $data->get_param( 'course' );
	error_log("Migrate course completion data from course $course_id");

	// Get the final quiz for this course
	$course_meta = get_post_meta($course_id, '_sfwd-courses', true);
	$final_quiz =  $course_meta['sfwd-courses_wdm_final_quiz'];	

	// Get all enrollments for this course
	global $wpdb;
	$table = $wpdb->prefix . Propel_DB::enrollments_table;
	$enrollments = $wpdb->get_results("
		SELECT user_id, completion_date, passed FROM $table
		WHERE post_id = $course_id"
	);

	// Completion must be handled differntly for courses without a final quiz!
	if ( $final_quiz == 0 ) {
		return migrate_course_complete($course_id, $enrollments);
	} else {
		return migrate_final_quiz_complete($course_id, $final_quiz, $enrollments);
	}
}

/**
 * Migrate completion data for courses that have no final quiz
 */
function migrate_course_complete( $course_id, $enrollments ) {
	global $wpdb;
	$table = $wpdb->prefix . Propel_DB::enrollments_table;
	$completion_count  = 0;
	$skipped = 0;
	foreach ( $enrollments as $enroll ) {
		// Only care to migrate if the complete date and pass/fail are NULL
		if ( is_null($enroll->date_completed) && is_null($enroll->passed) ){

			// Get the course progress for this user and see if it contains 
			// completed and total AND that these two numbers are equal. If so, 
			// the course has been completed
			$all_progress = get_user_meta($enroll->user_id, '_sfwd-course_progress', true); 
			if ( empty($all_progress) ) continue;

			$course_progress = $all_progress[$course_id];
			if ( !is_array($course_progress)) continue;

			if ( $course_progress['total'] != $course_progress['completed']) continue;

			// Course has been completed. Now we need to grab all of the quiz
			// results for this course and pick the one with the latest date.
			// This is the date the course was completed
			$quiz_results = get_user_meta($enroll->user_id, '_sfwd-quizzes', true); 
			$quizzes = get_quiz_ids($course_id);
			
			$latest = -1;
			foreach ( $quiz_results as $result ) {
				// toss this reqult if it is from another course
				if ( !in_array($result['quiz'], $quizzes) ) continue;

				$time = $result['time'] + 0; // convert to long
				if ( $time > $latest) {
					$latest = $time;
				}
			}

			$wpdb->update(
				$table,
				array( 'completion_date' => date('Y-m-d h:i:s', $latest), 'passed' => true ),
				array( 'user_id'=>$enroll->user_id, 'post_id'=>$course_id)
			);
			$completion_count += 1;
		} else {
			$skipped += 1;
		}
	}

	$out = array( "course"=>$course_id, "enrollments"=> count($enrollments), 
				  "completions"=>$completion_count, "skipped"=>$skipped );
	return new WP_REST_Response( $out, 200 );
}

/**
 * Get an array of POST IDs of quizzes that are part of the spacified course.
 * This makes it much easier to identify which quiz responses are for quiz
 * questions that are a part of a specific course.
 */
function get_quiz_ids($course_id) {
	$quizzes = get_posts(
		array('post_type' => 'sfwd-quiz', 'posts_per_page' => -1, 
		'meta_key' => 'course_id', 'meta_value'	=> $course_id));
	$ids = array();
	foreach ( $quizzes as $quiz ) {
		array_push($ids, $quiz->ID);
	}
	return $ids;
}

/**
 * Migrate completion of a course that has a final quiz
 */
function migrate_final_quiz_complete($course_id, $final_quiz, $enrollments) {
	global $wpdb;
	$table = $wpdb->prefix . Propel_DB::enrollments_table;

	// Find each one that has null complete and pass fields; those are candidates
	// for migrating data out of the user_meta table
	$completion_count = 0;
	$passed_cnt = 0;
	$skipped = 0;
	foreach ( $enrollments as $enroll ) {
		if ( is_null($enroll->date_completed) && is_null($enroll->passed) ){
			
			// Null completion and pass/fail status; get quiz results for this
			// user and see if they have completed the final quiz
			$passed = false;
			$took_final = false;
			$quiz_results = get_user_meta($enroll->user_id, '_sfwd-quizzes', true); 
			foreach($quiz_results as $quiz) { 

				if ( $quiz['quiz'] == $final_quiz ) {

					// This is a final quiz result. Only care until we get a pass
					$took_final = true;
					if ($passed == false ) {

						// track complete time and pass/fail status
						$time = date('Y-m-d h:i:s', $quiz['time']);
						if ($quiz['pass']== 1) {
							error_log("User $enroll->user_id PASSED final at $time");
							$passed = true;
							$passed_cnt += 1;
						} else {
							error_log("User $enroll->user_id FAILED final at $time");	
						}
					}
				}
			}

			// All quiz results for this user processed. If the final was attempted
			// we need to migrate the completeion date and pass/fail status
			if ( $took_final ) {
				$completion_count += 1;
				error_log("ADD enrollment complete $time result $passed for user $enroll->user_id");
				$wpdb->update(
					$table,
					array( 'completion_date' => $time, 'passed' => $passed ),
					array( 'user_id'=>$enroll->user_id, 'post_id'=>$course_id)
				);
			}
		} else {
			$skipped += 1;
		}
	} 
	$out = array( "course"=>$course_id, "enrollments"=> count($enrollments), 
				  "completions"=>$completion_count, "passed"=>$passed_cnt, "skipped"=>$skipped );
	return new WP_REST_Response( $out, 200 );
}

/**
 * Return a JSON array of course_id, title of available courses
 */
function get_courses() {
	error_log("get courses");
	$courses = get_posts(
		array('post_type' => 'sfwd-courses', 'posts_per_page' => -1));
	$out = array();
	foreach($courses as $course) {
		array_push($out, array("id"=>$course->ID, 'title'=>$course->post_title) );
	} 
	return new WP_REST_Response( $out, 200 );
}

//
// Register the endpoints with WP API
// 
add_action( 'rest_api_init', function () {
    register_rest_route( 'scitent/v1', '/migrate-completion', array(
        'methods' => 'POST',
        'callback' => 'migrate',
    ) );
    register_rest_route( 'scitent/v1', '/courses', array(
        'methods' => 'GET',
        'callback' => 'get_courses',
    ) );
} );
