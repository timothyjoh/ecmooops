<?php
/**
 * Displays the Prerequisites
 *
 * Available Variables:
 * $current_post : (WP_Post Object) Current Post object being display. Equal to global $post in most cases.
 * $prerequisite_post : (WP_Post Object) Post object needed to be taken prior to $current_post
 * $content_type : (string) Will contain the singlar lowercase common label 'course', 'lesson', 'topic', 'quiz'
 * $course_settings : (array) Settings specific to current course
 * 
 * @since 2.2.1.2
 * 
 * @package LearnDash\Course
 */
?>
<div id="learndash_complete_prerequisites"><?php echo sprintf( 
			_x( 
				'To take this %s, you need to complete the following %s first:%s', 
				'placeholders: (1) will be Course, Lesson or Quiz sigular. (2) Course sigular label, (3) link and title to prerequisites course.', 
				'learndash' 
			), 
			$content_type, 
			LearnDash_Custom_Label::label_to_lower( 'course' ), 
			'<br><a href="'. get_the_permalink( $prerequisite_post ) .'">'. get_the_title( $prerequisite_post ) .'</a>' ) ?></div>
