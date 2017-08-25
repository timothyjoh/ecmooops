<?php
/**
 * This file contains the code that displays the course navigation admin.
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Course
 */

?>
<?php
global $pagenow;

if (( isset( $course_id ) ) && ( !empty( $course_id ) )) {

	// Normally this will be called on a Course/Lesson/Topic/Quiz admin page or front-end where the post var is available.
	if ( isset( $_GET['post'] ) ) {
		$post_id = intval( $_GET['post'] );
		$post = get_post( $post_id );

		if ( $post->post_type == 'sfwd-topic' || $post->post_type == 'sfwd-quiz' ) {
			$lesson_id = learndash_get_setting( $post, 'lesson' );
		} else {
			$lesson_id = $post->ID;
		}
		
	} else {
		$post_id = 0;
		$lesson_id = 0;
	}

	if ( ( !isset( $user_id ) ) || ( empty( $user_id ) ) )
		$user_id = get_current_user_id();

	if ( !isset( $course_quiz_list ) )
		$course_quiz_list = learndash_get_course_quiz_list( $course_id, $user_id ); 

	if ( !isset( $course_progress ) )
		$course_progress = array();
		
	?>
	<div id="course_navigation-<?php echo $course_id ?>" class="course_navigation">

		<div class="learndash_navigation_lesson_topics_list">

			<?php if ( ( isset( $lessons ) ) && ( ! empty( $lessons ) ) ) { ?>
				<?php foreach( $lessons as $course_lesson_id => $course_lesson ) { ?>
					<?php 			
					$lesson_meta = get_post_meta($course_lesson['post']->ID, '_sfwd-lessons', true);
					
					$current_topic_ids = '';
					$lesson_topics_list =  learndash_topic_dots( $course_lesson['post']->ID, false, 'array' );
					$lesson_quizzes_list = learndash_get_lesson_quiz_list( $course_lesson['post']->ID, $user_id ); 

					$is_current_lesson = ( $lesson_id == $course_lesson['post']->ID );
					$lesson_list_class = ( $is_current_lesson ) ? 'active' : 'inactive';
					$lesson_lesson_completed = 'lesson_incomplete';
					$list_arrow_class = ( $is_current_lesson && ! empty( $lesson_topics_list ) ) ? 'expand' : 'collapse';
					?>

					<?php if ( ! empty( $lesson_topics_list ) ) : ?>
						<?php $list_arrow_class .= ' flippable'; ?>
					<?php endif; ?>
				
					<div class='<?php echo $lesson_list_class ?>' id='lesson_list-<?php echo $course_lesson['post']->ID; ?>'>
						<div class='list_arrow <?php echo $list_arrow_class; ?> <?php echo $lesson_lesson_completed; ?>' onClick='return flip_expand_collapse("#lesson_list", <?php echo $course_lesson['post']->ID; ?>);' ></div>
						<div class="list_lessons">
							<div class="lesson" >
								<?php 
									//if ( ( ( $pagenow == 'profile.php' ) || ( $pagenow == 'user-edit.php' ) ) && ( learndash_is_admin_user( ) ) ) { 
									//if ( ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user() ) ) {
									if ( learndash_show_user_course_complete( $user_id ) ) {
									
										$user_lesson_progress 				= 	array();
										$user_lesson_progress['user_id'] 	= 	$user_id;
										$user_lesson_progress['course_id']  = 	$course_id;
										$user_lesson_progress['lesson_id'] 	= 	$course_lesson['post']->ID;
									
										if ($course_lesson['status'] == 'completed') {
											$user_lesson_progress['checked'] = true;
										} else {
											$user_lesson_progress['checked'] = false;
										}
										?><input id="learndash-mark-lesson-complete-<?php echo $course_lesson['post']->ID ?>" type="checkbox" <?php checked($course_lesson['status'], 'completed') ?> class="learndash-mark-lesson-complete" data-title-checked="<?php echo htmlspecialchars ( sprintf( _x('Are you sure you want to set this %s and all related %s complete?', 'Are you sure you want to set this Lesson and all related Topics complete?', 'learndash'), LearnDash_Custom_Label::get_label( 'lesson' ), LearnDash_Custom_Label::get_label( 'topics' ) ), ENT_QUOTES) ?>" data-name="<?php echo htmlspecialchars( json_encode( $user_lesson_progress, JSON_FORCE_OBJECT ) ) ?>" /> <?php 
									} 
								?>
								<a href='<?php echo get_edit_post_link( $course_lesson['post']->ID ); ?>'><?php echo $course_lesson['post']->post_title; ?></a> 
							</div> 

							<?php 
								if ( ( ! empty( $lesson_topics_list ) ) || ( ! empty( $lesson_quizzes_list ) ) ) { 
									?>
									<div id='learndash_topic_dots-<?php echo $course_lesson['post']->ID; ?>' class="flip learndash_topic_widget_list"  style='<?php echo ( strpos( $list_arrow_class, 'collapse' ) !== false ) ? 'display:none' : '' ?>'>
										<ul class="learndash-topic-list">
										<?php

											if ( ! empty( $lesson_topics_list ) ) {
												$odd_class = '';

												foreach ( $lesson_topics_list as $key => $topic ) {
													$odd_class = empty( $odd_class ) ? 'nth-of-type-odd' : '';
													$completed_class = 'topic-notcompleted';
												
													$topic_quiz_list = learndash_get_lesson_quiz_list( $topic->ID, $user_id ); 												
													if ( !empty( $topic_quiz_list ) ) {
														$checked_message = ' data-title-checked="'. htmlspecialchars( sprintf( _x('Are you sure you want to set this %s and all related %s?', 'Are you sure you want to set this Topic and all related Quizzes?', 'learndash'), LearnDash_Custom_Label::get_label( 'topic' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ), ENT_QUOTES) .'" ';
													} else {
														$checked_message = '';
													}
													?>
													<li class="topic-item">
														<span class="topic_item">
															<?php 
																//if ( ( ( $pagenow == 'profile.php' ) || ( $pagenow == 'user-edit.php' ) ) && ( learndash_is_admin_user( ) ) ) { 
																//if ( ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user() ) ) {
																if ( learndash_show_user_course_complete( $user_id ) ) {
																	
																												
																	$user_topic_progress 				= 	array();
																	$user_topic_progress['user_id'] 	= 	$user_id;
																	$user_topic_progress['course_id'] 	= 	$course_id;
																	$user_topic_progress['lesson_id'] 	= 	$course_lesson['post']->ID;
																	$user_topic_progress['topic_id'] 	= 	$topic->ID;

 																	if ((isset($course_progress[$course_id]['topics'][$course_lesson['post']->ID][$topic->ID])) 
																	&& ((isset($course_progress[$course_id]['topics'][$course_lesson['post']->ID][$topic->ID])) == true)) {
																		$topic_checked = ' checked="checked" ';
																		$user_topic_progress['checked'] = true;
																	} else {
																		$topic_checked = '';
																		$user_topic_progress['checked'] = false;
																	}
														
																	?><input type="checkbox" <?php echo $topic_checked ?> id="learndash-mark-topic-complete-<?php echo $topic->ID ?>" class="learndash-mark-topic-complete" <?php echo $checked_message; ?> data-name="<?php echo htmlspecialchars( json_encode( $user_topic_progress, JSON_FORCE_OBJECT ) ) ?>" /><?php 
																} 
															?>
															<a class='<?php echo $completed_class; ?>' href='<?php echo get_edit_post_link( $topic->ID ); ?>' title='<?php echo $topic->post_title; ?>'><span><?php echo $topic->post_title; ?></span></a>														
														</span>
											
														<?php 
															$topic_quiz_list = learndash_get_lesson_quiz_list( $topic->ID, $user_id ); 
															if ( !empty( $topic_quiz_list ) ) {
																?>
																<ul id="learndash-quiz-list-<?php echo $topic->ID ?>" class="learndash-quiz-list">
																	<?php foreach ( $topic_quiz_list as $quiz ) { ?>
																		<li class="quiz-item">
																			<?php 
																				//if ( ( ( $pagenow == 'profile.php' ) || ( $pagenow == 'user-edit.php' ) ) && ( learndash_is_admin_user( ) ) ) { 
																				//if ( ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user() ) ) {
																				if ( learndash_show_user_course_complete( $user_id ) ) {
																					
																												
																					$user_quiz_progress 				= 	array();
																					$user_quiz_progress['user_id'] 		= 	$user_id;
																					$user_quiz_progress['course_id'] 	= 	$course_id;
																					$user_quiz_progress['lesson_id'] 	= 	$course_lesson['post']->ID;
																					$user_quiz_progress['topic_id'] 	= 	$topic->ID;
																					$user_quiz_progress['quiz_id'] 		= 	$quiz['post']->ID;
														
																					if ( $quiz['status'] == 'completed') {
																						$quiz_checked 					= 	' checked="checked" ';
																						$user_quiz_progress['checked'] 	= 	true;
																					} else {
																						$quiz_checked 					= 	'';
																						$user_quiz_progress['checked'] 	= 	false;
																					}
														
																					?><input type="checkbox" <?php echo $quiz_checked ?>class="learndash-mark-topic-quiz-complete learndash-mark-quiz-complete" data-name="<?php echo htmlspecialchars( json_encode( $user_quiz_progress, JSON_FORCE_OBJECT ) ) ?>" /><?php 
																				} 
																			?>
																
																			<a href='<?php echo get_edit_post_link( $quiz['post']->ID ); ?>' title='<?php echo $quiz['post']->post_title; ?>'><span><?php echo $quiz['post']->post_title; ?></span></a>
																	
																		</li>
																	<?php } ?>
																</ul>
																<?php
															}
														?>
													</li>
													<?php

												}
											}
										
											if ( !empty( $lesson_quizzes_list ) ) {
												foreach ( $lesson_quizzes_list as $quiz ) {
													?><li class="quiz-item"><?php
														//if ( ( ( $pagenow == 'profile.php' ) || ( $pagenow == 'user-edit.php' ) ) && ( learndash_is_admin_user( ) ) ) { 
														//if ( ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user() ) ) {
														if ( learndash_show_user_course_complete( $user_id ) ) {
																					
															$user_quiz_progress 				= 	array();
															$user_quiz_progress['user_id'] 		= 	$user_id;
															$user_quiz_progress['course_id'] 	= 	$course_id;
															$user_quiz_progress['lesson_id'] 	= 	$course_lesson['post']->ID;
															$user_quiz_progress['quiz_id'] 		= 	$quiz['post']->ID;
							
															if ( $quiz['status'] == 'completed') {
																$quiz_checked 					= 	' checked="checked" ';
																$user_quiz_progress['checked'] 	= 	true;
															} else {
																$quiz_checked 					= 	'';
																$user_quiz_progress['checked'] 	= 	false;
															}
							
															?><input type="checkbox" <?php echo $quiz_checked ?>class="learndash-mark-lesson-quiz-complete learndash-mark-quiz-complete" data-name="<?php echo htmlspecialchars( json_encode( $user_quiz_progress, JSON_FORCE_OBJECT ) ) ?>" /><?php 
														} 
														?><a href='<?php echo get_edit_post_link( $quiz['post']->ID ); ?>' title='<?php echo $quiz['post']->post_title; ?>'><span><?php echo $quiz['post']->post_title; ?></span></a>
													</li>
													<?php 
												} 
											}
										?>
										</ul>
									</div>
									<?php
								}
							?>
						</div>
					</div> 
				<?php } ?>

			<?php } ?>


			<?php 
				if ( ! empty( $course_quiz_list ) ) {
					foreach ( $course_quiz_list as $quiz ) {
						?>
						<div id='quiz_list-<?php echo $quiz['post']->ID; ?>'>
							<div class='list_arrow'></div>
							<div class="list_lessons">
								<div class="lesson" >
									<?php 
										//if ( ( ( $pagenow == 'profile.php' ) || ( $pagenow == 'user-edit.php' ) ) && ( learndash_is_admin_user( ) ) ) { 
										//if ( ( learndash_is_admin_user( ) ) || ( learndash_is_group_leader_user() ) ) {
										if ( learndash_show_user_course_complete( $user_id ) ) {
																
											$user_quiz_progress 				= 	array();
											$user_quiz_progress['user_id'] 		= 	$user_id;
											$user_quiz_progress['course_id'] 	= 	$course_id;
											$user_quiz_progress['quiz_id'] 		= 	$quiz['post']->ID;
		
											if ( $quiz['status'] == 'completed') {
												$quiz_checked 					= 	' checked="checked" ';
												$user_quiz_progress['checked'] 	= 	true;
											} else {
												$quiz_checked 					= 	'';
												$user_quiz_progress['checked'] 	= 	false;
											}
											?><input type="checkbox" <?php echo $quiz_checked ?> class="learndash-mark-quiz-complete learndash-mark-course-quiz-complete" data-name="<?php echo htmlspecialchars( json_encode( $user_quiz_progress, JSON_FORCE_OBJECT ) ) ?>" /><?php 
										} 
									?>
									<a href='<?php echo get_edit_post_link( $quiz['post']->ID ); ?>' title='<?php echo $quiz['post']->post_title; ?>'><?php echo $quiz['post']->post_title; ?></a>
					
								</div>
							</div>
						</div>
						<?php
					}
				}
			?>

		</div> <!-- Closing <div class='learndash_navigation_lesson_topics_list'> -->

		<?php if ( $post_id != $course->ID ) : ?> 
			<div class="widget_course_return">
				<?php _e( 'Return to', 'learndash' ); ?> <a href='<?php echo get_edit_post_link( $course_id ); ?>'>
					<?php echo $course->post_title; ?>
				</a>
			</div>
		<?php endif; ?>

	</div> <!-- Closing <div id='course_navigation'> -->
	<?php 
}