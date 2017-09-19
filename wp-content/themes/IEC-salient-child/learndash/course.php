<?php
/**
 * Displays a course
 *
 * Available Variables:
 * $course_id 		: (int) ID of the course
 * $course 		: (object) Post object of the course
 * $course_settings : (array) Settings specific to current course
 * 
 * $courses_options : Options/Settings as configured on Course Options page
 * $lessons_options : Options/Settings as configured on Lessons Options page
 * $quizzes_options : Options/Settings as configured on Quiz Options page
 * 
 * $user_id 		: Current User ID
 * $logged_in 		: User is logged in
 * $current_user 	: (object) Currently logged in user object
 * 
 * $course_status 	: Course Status
 * $has_access 	: User has access to course or is enrolled.
 * $materials 		: Course Materials
 * $has_course_content		: Course has course content
 * $lessons 		: Lessons Array
 * $quizzes 		: Quizzes Array
 * $lesson_progression_enabled 	: (true/false)
 * $has_topics		: (true/false) 
 * $lesson_topics	: (array) lessons topics 
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Course
 */
?>

<?php
/**
 * Display course status
 */
?>
<div class="myRight">
<?php if ( $logged_in ) : ?>
	<span id="learndash_course_status">
		<b><?php _e( 'Course Status:', 'learndash' ); ?></b> <?php echo $course_status; ?>
		<br />
	</span>
	<br />

	<?php  if ( ! empty( $course_certficate_link ) ) : ?>
		<div id="learndash_course_certificate">
			<a href='<?php echo esc_attr( $course_certficate_link ); ?>' class="btn-blue" target="_blank" ><?php _e( 'PRINT YOUR CERTIFICATE!', 'learndash' ); ?></a>
		</div>
		<br />
	<?php endif; ?>
<?php endif; ?>

<?php echo $content; ?>

<?php if ( ! $has_access ) : ?>
	<?php echo learndash_payment_buttons( $post ); ?>
<?php endif; ?>

</div>
<?php if ( isset( $materials ) ) : ?>
	<div id="learndash_course_materials">
		<h4><?php _e( 'Course Materials', 'learndash' ); ?></h4>
		<p><?php echo $materials; ?></p>
	</div>
<?php endif; ?>

<?php if ( $has_course_content ) : ?>
	<div id="learndash_course_content">
		<h4 id="learndash_course_content_title"> <?php _e( 'Course Content', 'learndash' ); ?></h4>

		<?php
        /**
         * Display lesson list
         */
        ?>
		<?php if ( ! empty( $lessons ) ) : ?>

			<?php if ( $has_topics ) : ?>
				<div class="expand_collapse">
					<a href="#" onClick='jQuery("#learndash_post_<?php echo $course_id; ?> .learndash_topic_dots").slideDown(); return false;'><?php _e( 'Expand All', 'learndash' ); ?></a> | <a href="#" onClick='jQuery("#learndash_post_<?php echo esc_attr( $course_id ); ?> .learndash_topic_dots").slideUp(); return false;'><?php _e( 'Collapse All', 'learndash' ); ?></a>
				</div>
			<?php endif; ?>

			<div id="learndash_lessons">

				<div id="lesson_heading">
					<span><?php _e( 'Lessons', 'learndash' ); ?></span>
					<span class="right"><?php _e( 'Status', 'learndash' ); ?></span>
				</div>

				<div id="lessons_list">

					<?php foreach ( $lessons as $lesson ) : ?>
						<div class='post-<?php echo esc_attr( $lesson['post']->ID ); ?> <?php echo esc_attr( $lesson['sample'] ); ?>'>

							<div class="list-count">
								<?php echo $lesson['sno']; ?>
							</div>

							<h4>
								<?php 
                if (!function_exists('lesson_xapi')) {
                  function lesson_xapi( $lesson_id ) {
                    global $wpdb;
                    $xapi_id = $wpdb->get_var( "
                                  SELECT meta_value
                                  FROM wp_postmeta
                                  WHERE post_id = $lesson_id
                                    AND meta_key = 'show_xapi_content'
                                ;" );
                    return $xapi_id;
                  }
                }
                if (!function_exists('propel_render_lesson_link')) {
                  function propel_render_lesson_link( $lsn ) {
                    $link_classes = esc_attr( $lsn['status'] );
                    $xapi_id = lesson_xapi( $lsn['post']->ID );
                    $url = esc_attr( $lsn['permalink'] );
                    $linktarget = "_self";

                    if ($xapi_id != 0) {
                      $url = do_shortcode("[grassblade id=".$xapi_id." target='url']");
                      $link_classes = $link_classes . " grassblade_launch_link";
                      $linktarget = "_blank";
                    }

                    // If the above didnt work, embed the old button to go to the course page
                    return "<a class='" . $link_classes. "' href='". $url ."' target='". $linktarget ."'>";
                  }
                }

                echo propel_render_lesson_link( $lesson ); 
                ?>
                <?php echo $lesson['post']->post_title; ?>
                </a>


								<?php
                                /**
                                 * Not available message for drip feeding lessons
                                 */
                                ?>
								<?php if ( ! empty( $lesson['lesson_access_from'] ) ) : ?>
									<small class="notavailable_message">
										<?php echo sprintf( __( 'Available on: %s ', 'learndash' ), date_i18n( 'd-M-Y', $lesson['lesson_access_from'] ) ); ?>
									</small>
								<?php endif; ?>


								<?php
                                /**
                                 * Lesson Topics
                                 */
                                ?>
								<?php $topics = @$lesson_topics[ $lesson['post']->ID ]; ?>

								<?php if ( ! empty( $topics ) ) : ?>
									<div id='learndash_topic_dots-<?php echo esc_attr( $lesson['post']->ID ); ?>' class="learndash_topic_dots type-list">
										<ul>
											<?php $odd_class = ''; ?>
											<?php foreach ( $topics as $key => $topic ) : ?>
												<?php $odd_class = empty( $odd_class ) ? 'nth-of-type-odd' : ''; ?>
												<?php $completed_class = empty( $topic->completed ) ? 'topic-notcompleted':'topic-completed'; ?>												
												<li class='<?php echo esc_attr( $odd_class ); ?>'>
													<span class="topic_item">
														<a class='<?php echo esc_attr( $completed_class ); ?>' href='<?php echo esc_attr( get_permalink( $topic->ID ) ); ?>' title='<?php echo esc_attr( $topic->post_title ); ?>'>
															<span><?php echo $topic->post_title; ?></span>
														</a>
													</span>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>

							</h4>
						</div>
					<?php endforeach; ?>

				</div>
			</div>
		<?php endif; ?>


		<?php
        /**
         * Display quiz list
         */
        ?>
		<?php if ( ! empty( $quizzes ) ) : ?>
			<div id="learndash_quizzes">
				<div id="quiz_heading">
					<?php
						$title = "Quizzes";
						if ( sizeof($quizzes) == 1 ) $title="Quiz"
					?>
					<span><?php _e( $title, 'learndash' ); ?></span><span class="right"><?php _e( 'Status', 'learndash' ); ?></span>
				</div>
				<div id="quiz_list">

					<?php foreach( $quizzes as $quiz ) : ?>
						<div id='post-<?php echo esc_attr( $quiz['post']->ID ); ?>' class='<?php echo esc_attr( $quiz['sample'] ); ?>'>
							<div class="list-count"><?php echo $quiz['sno']; ?></div>
							<h4>
                                <a class='<?php echo esc_attr( $quiz['status'] ); ?>' href='<?php echo esc_attr( $quiz['permalink'] ); ?>'><?php echo $quiz['post']->post_title; ?></a>
                            </h4>
						</div>						
					<?php endforeach; ?>

				</div>
			</div>
		<?php endif; ?>

	</div>
<?php endif; ?>
