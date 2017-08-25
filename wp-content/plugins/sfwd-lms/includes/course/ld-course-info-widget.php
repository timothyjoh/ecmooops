<?php
/**
 * Course info and navigation widgets
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Widgets
 */


// This filter will parse the text of the widget for shortcodes.
add_filter( 'widget_text', 'do_shortcode' );

class LearnDash_Course_Info_Widget extends WP_Widget {

	/**
	 * Setup Course Info Widget
	 */
	function __construct() {
		$widget_ops = array( 
			'classname' => 'widget_ldcourseinfo', 
			'description' => sprintf( _x( 'LearnDash - %s attempt and score information of users. Visible only to users logged in.', 'placeholders: course', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) )
		);
		$control_ops = array(); //'width' => 400, 'height' => 350);
		parent::__construct( 'ldcourseinfo', sprintf( _x( '%s Information', 'Course Information', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), $widget_ops, $control_ops );
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

		 /**
		 * Filter widget title
		 * 
		 * @since 2.1.0
		 * 
		 * @param  string
		 */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );
		
		if ( empty( $user_id ) ) {
			$current_user = wp_get_current_user();
			if ( empty( $current_user->ID ) ) {
				return;
			}
			
			$user_id = $current_user->ID;
		}
		
		$courseinfo = learndash_course_info( $user_id );
		
		if ( empty( $courseinfo ) ) {
			return;
		}
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		
		echo $courseinfo;
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
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags( $instance['title'] );
		//$text = format_to_edit($instance['text']);
		
		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'learndash' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
		<?php
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("LearnDash_Course_Info_Widget");' ) );



class LearnDash_Course_Navigation_Widget extends WP_Widget {
	
	/**
	 * Setup Course Navigation Widget
	 */
	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_ldcoursenavigation', 
			'description' => sprintf( _x( 'LearnDash - %s Navigation. Shows lessons and topics on the current course.', 'LearnDash - Course Navigation. Shows lessons and topics on the current course.', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) )
		);
		$control_ops = array(); //'width' => 400, 'height' => 350);
		parent::__construct( 'widget_ldcoursenavigation', sprintf( _x( '%s Navigation', 'Course Navigation Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ), $widget_ops, $control_ops );
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
		
		global $post;
		
		if ( empty( $post->ID ) || ! is_single() ) {
			return;
		}
		
		$course_id = learndash_get_course_id( $post->ID );

		if ( empty( $course_id ) ) {
			return;
		}
		
		extract( $args );

		 /**
		 * Filter widget title
		 * 
		 * @since 2.1.0
		 * 
		 * @param  string
		 */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );
		
		echo $before_widget;
		
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		
		learndash_course_navigation( $course_id );
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
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags( $instance['title'] );
		//$text = format_to_edit($instance['text']);		
		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'learndash' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
		<?php
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("LearnDash_Course_Navigation_Widget");' ) );



/**
 * Outputs course navigation template for widget
 * 
 * @since 2.1.0
 * 
 * @param  int 		$course_id  course id
 * @return string 			 	course navigation output
 */
function learndash_course_navigation( $course_id ) {
	$course = get_post( $course_id );
	
	if ( empty( $course->ID ) || $course_id != $course->ID ) {
		return;
	}
	
	$course = get_post( $course_id );

	if ( empty( $course->ID ) || $course->post_type != 'sfwd-courses' ) {
		return;
	}
	
	$course_settings = learndash_get_setting( $course );
	$lessons = learndash_get_course_lessons_list( $course );
	
	include( SFWD_LMS::get_template( 
		'course_navigation_widget', 
		array(
			'course_id' => $course_id, 
			'course' => $course, 
			'lessons' => $lessons,
		), 
		null, 
		true 
	));
}



/**
 * Outputs course navigation admin template for widget
 * 
 * @since 2.1.0
 * 
 * @param  int 		$course_id  course id
 * @return string 			 	course navigation output
 */
function learndash_course_navigation_admin( $course_id ) {
	$course = get_post( $course_id );
	
	if ( empty( $course->ID ) || $course_id != $course->ID ) {
		return;
	}
	
	$course = get_post( $course_id );

	if ( empty( $course->ID ) || $course->post_type != 'sfwd-courses' ) {
		return;
	}
	
	$course_settings = learndash_get_setting( $course );
	$lessons = learndash_get_course_lessons_list( $course );
	
	include( SFWD_LMS::get_template( 
		'course_navigation_admin', 
		array( 
			'course_id' => $course_id, 
			'course' => $course, 
			'lessons' => $lessons, 
		), 
		null, 
		true 
	));
}



/**
 * Register course navigation meta box for admin
 * 
 * @since 2.1.0
 */
function learndash_course_navigation_admin_box() {
	$post_types = array('sfwd-courses', 'sfwd-lessons', 'sfwd-quiz', 'sfwd-topic');

	foreach( $post_types as $post_type ) {
		add_meta_box( 'learndash_course_navigation_admin_meta', __( 'Associated Content', 'learndash' ), 'learndash_course_navigation_admin_box_content', $post_type, 'side', 'high' );
	}
}

add_action( 'add_meta_boxes', 'learndash_course_navigation_admin_box' );



/**
 * Add content to course navigation meta box for admi
 * 
 * @since 2.1.0
 */
function learndash_course_navigation_admin_box_content() {
	$course_id = learndash_get_course_id( @$_GET['post'] );
	
	if ( empty( $course_id ) ) {
		return;
	}

	learndash_course_navigation_admin( $course_id );
}


/**
 * Get course info html output for user (helper function)
 * 
 * @since 2.1.0
 * 
 * @param  int 		$user_id 
 * @return string 	course info output
 */
function learndash_course_info( $user_id ) {
	return SFWD_LMS::get_course_info( $user_id );
}



/**
 * Shortcode get course info html output for user (helper function)
 * 
 * @since 2.1.0
 * 
 * @param  array 	$atts 	shortcode attributes
 * @return string 	course info output
 */
function learndash_course_info_shortcode( $atts ) {
	
	global $learndash_shortcode_used;
	
	if ( isset( $atts['user_id'] ) ) {
		$user_id = $atts['user_id'];
	} else {
		$current_user = wp_get_current_user();
		
		if ( empty( $current_user->ID ) ) {
			return;
		}
		
		$user_id = $current_user->ID;
	}

	$learndash_shortcode_used = true;
	
	return SFWD_LMS::get_course_info( $user_id );
}

add_shortcode( 'ld_course_info', 'learndash_course_info_shortcode' );



/**
 * Shortcoude output profile for user
 * 
 * @since 2.1.0
 * 
 * @param  array 	$atts 	shortcode attributes
 * @return string 	output profile for user
 */
function learndash_profile( $atts ) {
	
	global $learndash_shortcode_used;
	$defaults = array(
		'user_id'	=>	get_current_user_id(),
		'order' 	=> 'DESC', 
		'orderby' 	=> 'ID', 
	);
	$atts = wp_parse_args( $atts, $defaults );

	$atts = apply_filters('learndash_profile_shortcode_atts', $atts);

	if ( empty( $atts['user_id'] ) ) return;

	$current_user = get_user_by( 'id', $atts['user_id'] );
	$user_courses = ld_get_mycourses( $atts['user_id'], $atts );

	$usermeta = get_user_meta( $atts['user_id'], '_sfwd-quizzes', true );
	$quiz_attempts_meta = empty( $usermeta ) ? false : $usermeta;
	$quiz_attempts = array();

	if ( ! empty( $quiz_attempts_meta ) ) {

		foreach ( $quiz_attempts_meta as $quiz_attempt ) {
			$c = learndash_certificate_details( $quiz_attempt['quiz'], $atts['user_id'] );
			$quiz_attempt['post'] = get_post( $quiz_attempt['quiz'] );
			$quiz_attempt['percentage'] = ! empty( $quiz_attempt['percentage'] ) ? $quiz_attempt['percentage'] : ( ! empty( $quiz_attempt['count'] ) ? $quiz_attempt['score'] * 100 / $quiz_attempt['count'] : 0 );
			
			if ( $atts['user_id'] == get_current_user_id() && ! empty( $c['certificateLink'] ) && ( ( isset( $quiz_attempt['percentage'] ) && $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100 ) ) ) {
				$quiz_attempt['certificate'] = $c;
			}

			$quiz_attempts[learndash_get_course_id( $quiz_attempt['quiz'] )][] = $quiz_attempt;
		}
	}
	
	$learndash_shortcode_used = true;

	return SFWD_LMS::get_template( 'profile', array(
		'user_id' 			=> 	$atts['user_id'], 
		'quiz_attempts' 	=> 	$quiz_attempts, 
		'current_user' 		=> 	$current_user, 
		'user_courses' 		=> 	$user_courses,
		) 
	);
}

add_shortcode( 'ld_profile', 'learndash_profile' );
