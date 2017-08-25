<?php
/**
 * Misc functions
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Misc
 */



/**
 * Add post thumbnail theme support for customn post types
 *
 * @since 2.1.0
 */
function learndash_add_theme_support() {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails', array( 'sfwd-certificates', 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-assignment', 'sfwd-essays' ) );
	}
}

add_action( 'after_setup_theme', 'learndash_add_theme_support' );



/**
 * Get a Quiz Pro's quiz ID
 *
 * @todo   purpose of this function and how quiz pro id's relate to quizzes
 *
 * @since 2.1.0
 *
 * @param  int $quiz_id  quiz pro id
 * @return int           quiz id
 */

function learndash_get_quiz_id_by_pro_quiz_id( $quiz_id ) {
	global $wpdb;

	$sql_str = $wpdb->prepare( "SELECT post_id FROM ". $wpdb->postmeta . " WHERE meta_key=%s ", 'quiz_pro_id_' . intval( $quiz_id ) );
	//error_log('sql_str['. $sql_str .']');
	$quiz_post_id = $wpdb->get_var( $sql_str );
	//error_log('quiz_post_id['. $quiz_post_id .']');
	if ( $quiz_post_id != '' ) {
		//error_log('#1: quiz_id['. $quiz_id .'] found['. $quiz_post_id .']');
		return intval($quiz_post_id);
	}
	
	
	$sql_str = $wpdb->prepare( "SELECT post_id FROM ". $wpdb->postmeta . " WHERE meta_key=%s AND meta_value=%d", 'quiz_pro_id', intval( $quiz_id ) );
	//error_log('sql_str['. $sql_str .']');
	$quiz_post_id = $wpdb->get_var( $sql_str );
	//error_log('quiz_post_id['. $quiz_post_id .']');
	if ( $quiz_post_id != '' ) {
		update_post_meta( intval($quiz_post_id), 'quiz_pro_id_' . intval($quiz_id), intval($quiz_id) );
		//error_log('#2: quiz_id['. $quiz_id .'] found['. $quiz_post_id .']');
		return intval($quiz_post_id);
	} 

	// Because we seem to have a mix of int and string values when these are serialized the format to look for end up being somewhat kludge-y. 
	$quiz_id_str = sprintf('%s', intval($quiz_id));
	$quiz_id_len = strlen($quiz_id_str);

	$like_i = 'sfwd-quiz_quiz_pro";i:'. intval($quiz_id) .';';
	$like_s = '"sfwd-quiz_quiz_pro";s:'. $quiz_id_len .':"'. $quiz_id_str .'"';

	//$sql_str = $wpdb->prepare( "SELECT post_id FROM ". $wpdb->postmeta . " WHERE meta_key=%s AND meta_value LIKE '". $like_i ."' OR meta_value LIKE '". $like_s ."'", '_sfwd-quiz' );
	
	// Using REGEX because it is slightly faster then OR on text fields pattern search
	$sql_str = $wpdb->prepare( "SELECT post_id FROM ". $wpdb->postmeta . " WHERE meta_key=%s AND meta_value REGEXP '". $like_i ."|". $like_s ."'", '_sfwd-quiz' );
	//error_log('sql_str['. $sql_str .']');
	$quiz_post_id = $wpdb->get_var( $sql_str );
	//error_log('quiz_post_id['. $quiz_post_id .']');
	if ( $quiz_post_id != '' ) {
		update_post_meta( intval($quiz_post_id), 'quiz_pro_id_' . intval($quiz_id), intval($quiz_id) );
		update_post_meta( intval($quiz_post_id), 'quiz_pro_id', intval($quiz_id) );
		//error_log('#3: quiz_id['. $quiz_id .'] found['. $quiz_post_id .']');
		return $quiz_post_id;
	} 

	//error_log('#4: quiz_id['. $quiz_id .'] !found');	
	/*
	// DON'T DO THIS. We don't want to create the Quiz Post if there is an orphaned ProQuiz. 
	$sql_str = $wpdb->prepare( "SELECT * FROM ". $wpdb->prefix . "wp_pro_quiz_master WHERE id=%d ", intval( $quiz_id ) );
	//error_log('sql_str['. $sql_str .']');
	$quiz_pro = $wpdb->get_row( $sql_str );	
	//error_log('quiz_pro<pre>'. print_r($quiz_pro, true) .'</pre>');
	if ($quiz_pro) {
		$user_id = get_current_user_id();

		$quiz_post_id = wp_insert_post(
			array(
				'post_title' => $quiz_pro->name,
				'post_type' => 'sfwd-quiz',
				'post_status' => 'draft',
				'post_author' => $user_id,
			)
		);
		if ( ! empty( $quiz_post_id ) ) {
			learndash_update_setting( $quiz_post_id, 'quiz_pro', intval($quiz_id) );
			update_post_meta( intval($quiz_post_id), 'quiz_pro_id_' . intval($quiz_id), intval($quiz_id) );
			update_post_meta( intval($quiz_post_id), 'quiz_pro_id', intval($quiz_id) );
		}
		//error_log('#4: quiz_post_id['. $quiz_post_id .']');
		
		return intval( $quiz_post_id );
	}
	*/
}

function learndash_get_quiz_id_by_pro_quiz_id_v22( $quiz_id ) {

	$opt = array(
		'post_type' 		=> 	'sfwd-quiz',
		'post_status' 		=> 	array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
		'posts_per_page'	=> 	1,
		'orderby'			=>	'date',
		'order'				=>	'ASC',
		'meta_query' => array(
			array(
				'key'		=>	'quiz_pro_id',
				'value'		=>	intval($quiz_id),
				'compare'	=>	'=',
			),
		),
	);
	
	$quizzes = get_posts( $opt );
	if (!empty( $quizzes ) ) {
		return $quizzes[0]->ID;
		
	} else {
		// Because we seem to have a mix of int and string values when these are serialized the format to look for end up being somewhat kludge-y. 
		$quiz_id_str = sprintf('%s', $quiz_id);
		$quiz_id_len = strlen($quiz_id_str);
	
		$opt = array(
			'post_type' 		=> 	'sfwd-quiz',
			'post_status' 		=> 	array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			// Mot sure why there would ever be more than one matching post for a quiz. 
			'posts_per_page'	=> 	-1,
			'orderby'			=>	'date',
			'order'				=>	'ASC',
			'meta_query' => array(
				'relation'		=>	'OR',
				array(
					'key'		=>	'_sfwd-quiz',
					'value'		=>	'sfwd-quiz_quiz_pro";i:'. $quiz_id .';',
					'compare'	=>	'LIKE',
				),
				array(
					'key' 		=>	'_sfwd-quiz',
					'value'		=>	'"sfwd-quiz_quiz_pro";s:'. $quiz_id_len .':"'. $quiz_id_str .'"',
					'compare'	=> 	'LIKE',
				),
			),
		);
	
		$quizzes = get_posts( $opt );
		if (!empty($quizzes)) {
			foreach ( $quizzes as $quiz ) {
				if ( $quiz_id == learndash_get_setting( $quiz, 'quiz_pro', true ) ) {
					// Add the post_meta so next time we don't have to do the LIKE query and loop. 
					update_post_meta( $quiz->ID, 'quiz_pro_id', $quiz_id );
					
					return $quiz->ID;
				}
			}
		}
	}
}

function learndash_get_quiz_id_by_pro_quiz_id_ORG( $quiz_id ) {
	$opt = array(
		'post_type' => 'sfwd-quiz',
		'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
		'posts_per_page'	=> -1
	);
	$quizzes = get_posts( $opt );
	foreach ( $quizzes as $quiz ) {
		$pro_quiz_id  = learndash_get_setting( $quiz, 'quiz_pro', true );
		if ( $quiz_id == $pro_quiz_id ) {
			return $quiz->ID;
		}
	}
}


/**
 * Get LearnDash setting for a post
 * 
 * @since 2.1.0
 * 
 * @param  id|obj $post    
 * @param  string $setting 
 * @return string value for requested setting
 */
function learndash_get_setting( $post, $setting = null ) {

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	} else {
		if ( empty( $post ) || ! is_object( $post ) || empty( $post->ID ) ) {
			return null;
		}
	}
	
	if ($post instanceof WP_Post) {

		if ( $setting == 'lesson' ) {
			return learndash_get_lesson_id( $post->ID ); 
		}

		if ( $setting == 'course' ) {
			return get_post_meta( $post->ID, 'course_id', true ); 
		}

		$meta = get_post_meta( $post->ID, '_' . $post->post_type, true );

		if ( empty( $setting ) && ! empty( $meta )  ) {
			$settings = array();
			foreach ( $meta as $k => $v ) {
				$settings[ str_replace( $post->post_type.'_', '', $k ) ] = $v;
			}
			return $settings;
		} else {
			if ( isset( $meta[ $post->post_type.'_'.$setting ] ) ) {
				return $meta[ $post->post_type.'_'.$setting ]; 
			} else {
				return ''; 
			}
		}
	}
}



/**
 * Get options for a particular post type and setting
 * 
 * @since 2.1.0
 * 
 * @param  string $post_type
 * @param  string $setting
 * @return array|string 	options requested
 */
function learndash_get_option( $post_type, $setting = '' ) {
	$options = get_option( 'sfwd_cpt_options' );

	if ( empty( $setting) && ! empty( $options['modules'][ $post_type.'_options'] ) ) {
		foreach ( $options['modules'][ $post_type.'_options'] as $key => $val ) {
			$return[str_replace( $post_type.'_', '', $key )] = $val;
		}
		return $return;
	}

	if ( ! empty( $options['modules'][ $post_type.'_options'][ $post_type.'_'.$setting] ) ) {
		return $options['modules'][ $post_type.'_options'][ $post_type.'_'.$setting];
	} else {
		return '';
	}	
}



/**
 * Update LearnDash setting for a post
 *
 * @since 2.1.0
 * 
 * @param  id|obj $post    
 * @param  string $setting 
 * @param  string $value
 * @return bool   if update was successful         
 */
function learndash_update_setting( $post, $setting, $value ) {
	if ( empty( $setting) ) {
		return null;
	}

	// Were we sent a post ID?
	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	} 
	
	// Ensure we have a post object or type WP_Post!
	if ($post instanceof WP_Post) {  
	
		$meta = get_post_meta( $post->ID, '_'.$post->post_type, true );
		$meta[ $post->post_type.'_'.$setting] = $value;

		if ( $setting == 'course' ) {
			update_post_meta( $post->ID, 'course_id', $value );
		} else {
			if ( $setting == 'lesson' ) {
				update_post_meta( $post->ID, 'lesson_id', $value );
			}
		}
		return update_post_meta( $post->ID, '_'.$post->post_type, $meta );
	}
}



if ( ! function_exists( 'sfwd_lms_get_post_options' ) ) {

	/**
	 * Set up wp query args for the post type that are saved in options
	 * 
	 * @param  string $post_type
	 * @return array  wp query arguments
	 */
	function sfwd_lms_get_post_options( $post_type ) {
		global $sfwd_lms;
	
		// Set our default options

		$ret = array( 
			'order' 			=> 	'ASC', 
			'orderby' 			=> 	'date', 
			'posts_per_page' 	=> 	get_option('posts_per_page')
		);

		if ( ( !empty( $post_type ) ) && ( isset( $sfwd_lms->post_types[ $post_type ] ) ) ) {
			$cpt = $sfwd_lms->post_types[ $post_type ];
			if ( ( $cpt ) && ( $cpt instanceof SFWD_CPT_Instance ) ) {
				$prefix = $cpt->get_prefix();
				$options = $cpt->get_current_options();

				if ((!empty($prefix)) && (!empty($options))) {
					foreach ( $ret as $k => $v ) {
						if ( ! empty( $options["{$prefix}{$k}"] ) ) {
							$ret[ $k ] = $options["{$prefix}{$k}"];
						}
					}
				}
			}
		}

		return $ret;
	}
}



/**
 * Output LearnDash Payment buttons
 * 
 * @since 2.1.0
 *
 * @uses learndash_get_function()
 * @uses sfwd_lms_has_access()
 * 
 * @param  id|obj 	$course course id or WP_Post course object
 * @return string   output of payment buttons
 */
function learndash_payment_buttons( $course ) {

	if ( is_numeric( $course ) ) {
		$course_id = $course;
		$course = get_post( $course_id );
	} else if ( ! empty( $course->ID ) ) {
		$course_id = $course->ID;
	} else {
		return '';
	}

	$user_id = get_current_user_id();

	if ( $course->post_type != 'sfwd-courses' ) {
		return '';
	}

	$meta = get_post_meta( $course_id, '_sfwd-courses', true );
	$course_price_type = @$meta['sfwd-courses_course_price_type'];
	$course_price = @$meta['sfwd-courses_course_price'];
	$course_no_of_cycles = @$meta['sfwd-courses_course_no_of_cycles'];
	$course_price = @$meta['sfwd-courses_course_price'];
	$custom_button_url = @$meta['sfwd-courses_custom_button_url'];

	$courses_options = learndash_get_option( 'sfwd-courses' );

	if ( ! empty( $courses_options ) ) {
		extract( $courses_options );
	}

	$paypal_sandbox = empty( $paypal_sandbox ) ? 0 : 1;

	if ( sfwd_lms_has_access( $course->ID, $user_id ) ) {
		return '';
	}

	$button_text = LearnDash_Custom_Label::get_label( 'button_take_this_course' );

	if ( ! empty( $course_price_type ) && $course_price_type == 'closed' ) {

		if ( empty( $custom_button_url) ) {
			$custom_button = '';
		} else {
			if ( ! strpos( $custom_button_url, '://' ) ) {
				$custom_button_url = 'http://'.$custom_button_url;
			}

			$custom_button = '<a class="btn-join" href="'.$custom_button_url.'" id="btn-join">'. $button_text .'</a>';
		}

		$payment_params = array(
			'custom_button_url' => $custom_button_url,
			'post' => $course
		);

		/**
		 * Filter a closed course payment button
		 * 
		 * @since 2.1.0
		 * 
		 * @param  string  $custom_button       
		 */
		return 	apply_filters( 'learndash_payment_closed_button', $custom_button, $payment_params );

	} else if ( ! empty( $course_price ) ) {
		include_once( 'vendor/paypal/enhanced-paypal-shortcodes.php' );

		$paypal_button = '';

		if ( ! empty( $paypal_email ) ) {

			if ( empty( $course_price_type ) || $course_price_type == 'paynow' ) {
				$paypal_button = wptexturize( do_shortcode( "<div class='learndash_checkout_button learndash_paypal_button'>[paypal type='paynow' amount='{$course_price}' sandbox='{$paypal_sandbox}' email='{$paypal_email}' itemno='{$course->ID}' name='{$course->post_title}' noshipping='1' nonote='1' qty='1' currencycode='{$paypal_currency}' rm='2' notifyurl='{$paypal_notifyurl}' returnurl='{$paypal_returnurl}' imagewidth='100px' pagestyle='paypal' lc='{$paypal_country}' cbt='" . __( 'Complete Your Purchase', 'learndash' ) . "' custom='".$user_id."']</div>" ) );
			} else if ( $course_price_type == 'subscribe' ) {
				$course_price_billing_p3 = get_post_meta( $course_id, 'course_price_billing_p3',  true );
				$course_price_billing_t3 = get_post_meta( $course_id, 'course_price_billing_t3',  true );
				$srt = intval( $course_no_of_cycles );
				$paypal_button = wptexturize( do_shortcode( "<div class='learndash_checkout_button learndash_paypal_button'>[paypal type='subscribe' a3='{$course_price}' p3='{$course_price_billing_p3}' t3='{$course_price_billing_t3}' sandbox='{$paypal_sandbox}' email='{$paypal_email}' itemno='{$course->ID}' name='{$course->post_title}' noshipping='1' nonote='1' qty='1' currencycode='{$paypal_currency}' rm='2' notifyurl='{$paypal_notifyurl}' returnurl='{$paypal_returnurl}' imagewidth='100px' pagestyle='paypal' lc='{$paypal_country}' cbt='" . __( 'Complete Your Purchase', 'learndash' ) . "' custom='".$user_id."' srt='{$srt}']</div>" ) );
			}
		}

		$payment_params = array(
			'price' => $course_price,
			'post' => $course,
		);

		/**
		 * Filter PayPal payment button
		 * 
		 * @since 2.1.0
		 * 
		 * @param  string  $paypal_button
		 */
		$payment_buttons = apply_filters( 'learndash_payment_button', $paypal_button, $payment_params );
		
		if ( ! empty( $payment_buttons ) ) {
		
			if ( ( !empty( $paypal_button ) ) && ( $payment_buttons != $paypal_button ) ) {

				$button = 	'';
				$button .= 	'<div id="learndash_checkout_buttons_course_'. $course->ID .'" class="learndash_checkout_buttons">';
				$button .= 		'<input id="btn-join-'. $course->ID .'" class="btn-join btn-join-'. $course->ID .' button learndash_checkout_button" data-jq-dropdown="#jq-dropdown-'. $course->ID .'" type="button" value="'. $button_text .'" />';
				$button .= 	'</div>';
			
				global $dropdown_button;
				$dropdown_button .= 	'<div id="jq-dropdown-'. $course->ID .'" class="jq-dropdown jq-dropdown-tip checkout-dropdown-button">';
				$dropdown_button .= 		'<ul class="jq-dropdown-menu">';
				$dropdown_button .= 		'<li>';
				$dropdown_button .= 			str_replace($button_text, __('Use Paypal', 'learndash'), $payment_buttons);
				$dropdown_button .= 		'</li>';
				$dropdown_button .= 		'</ul>';
				$dropdown_button .= 	'</div>';
			
				return $button;
				
			} else {
				return	'<div id="learndash_checkout_buttons_course_'. $course->ID .'" class="learndash_checkout_buttons">'. $payment_buttons .'</div>';
			}
		}
	} else {
		$join_button = '<div class="learndash_join_button"><form method="post">
							<input type="hidden" value="'. $course->ID .'" name="course_id" />
							<input type="hidden" name="course_join" value="'. wp_create_nonce( 'course_join_'. get_current_user_id() .'_'. $course->ID ) .'" />
							<input type="submit" value="'.$button_text.'" class="btn-join" id="btn-join" />
						</form></div>';

		$payment_params = array( 
			'price' => '0', 
			'post' => $course, 
			'course_price_type' => $course_price_type 
		);

		/**
		 * Filter Join payment button
		 * 
		 * @since 2.1.0
		 * 
		 * @param  string  $join_button
		 */
		$payment_buttons = apply_filters( 'learndash_payment_button', $join_button, $payment_params );
		return $payment_buttons;
	}

}

// Yes, global var here. This var is set within the payment button processing. The var will contain HTML for a fancy dropdown
$dropdown_button = '';
add_action("wp_footer", 'ld_footer_payment_buttons');
function ld_footer_payment_buttons() {
	global $dropdown_button;
	
	if (!empty($dropdown_button)) {
		echo $dropdown_button;
	}
}

add_action('get_footer', 'learndash_get_footer');
function learndash_get_footer() {
	if (is_admin()) return;

	global $dropdown_button;
	if (empty($dropdown_button)) {
		wp_dequeue_script('jquery-dropdown-js');
	}
}



/**
 * Payment buttons shortcode
 *
 * @since 2.1.0
 * 
 * @param  array $attr short code attributes
 * @return string      output of payment buttons
 */
function learndash_payment_buttons_shortcode( $attr ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;

	$shortcode_atts = shortcode_atts( array( 'course_id' => 0 ), $attr );

	extract( $shortcode_atts );

	if ( empty( $course_id ) ) {
		return '';
	} else {
		return learndash_payment_buttons( $course_id );
	}
}

add_shortcode( 'learndash_payment_buttons', 'learndash_payment_buttons_shortcode' );



/**
 * Check if lesson, topic, or quiz is a sample
 *
 * @since 2.1.0
 * 
 * @param  id|obj $post id of post or WP_Post object
 * @return bool
 */
function learndash_is_sample( $post ) {
	if ( empty( $post) ) {
		return false;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	if ( empty( $post->ID ) ) {
		return false;
	}

	if ( $post->post_type == 'sfwd-lessons' ) {
		if ( learndash_get_setting( $post->ID, 'sample_lesson' ) ) {
			return true;
		}
	}

	if ( $post->post_type == 'sfwd-topic' ) {
		$lesson_id = learndash_get_setting( $post->ID, 'lesson' );
		if ( learndash_get_setting( $lesson_id, 'sample_lesson' ) ) {
			return true;
		}
	}

	if ( $post->post_type == 'sfwd-quiz' ) {
		$lesson_id = learndash_get_setting( $post->ID, 'lesson' );
		return learndash_is_sample( $lesson_id );
	}

	return false;
}



/**
 * Helper function for php output buffering
 * 
 * @todo not sure what this is preventing with a while looping
 *       counting to 10 and checking current buffer level
 *
 * @since 2.1.0
 * 
 * @param  integer $level
 * @return string
 */
function learndash_ob_get_clean( $level = 0 ) {
	$content = '';
	$i = 1;

	while ( $i <= 10 && ob_get_level() > $level ) {
		$i++;
		$content = ob_get_clean();
	}

	return $content;
}



/**
 * Redirect to home if user lands on archive pages for lesson or quiz post types
 * 
 * @since 2.1.0
 * 
 * @param  object $wp WP object
 */
function ld_remove_lessons_and_quizzes_page( $wp ) {

	if ( is_archive() && ! is_admin() )  {
		$post_type = get_post_type();

		if ( $post_type == 'sfwd-lessons' || $post_type == 'sfwd-quiz' ) {
			wp_redirect( home_url() );
			exit;
		}
	}

}

add_action( 'wp', 'ld_remove_lessons_and_quizzes_page' );



/**
 * Removes comments
 * Filter callback for 'comments_array' (wp core hook)
 *
 * @since 2.1.0
 * 
 * @param  array $comments
 * @param  array $array
 * @return array empty array
 */
function learndash_remove_comments( $comments, $array ) {
	return array();
}


/**
 * Include auto updater file and instantiate nss_plugin_updater_sfwd_lms class
 *
 * @since 2.1.0
 */
function nss_plugin_updater_activate_sfwd_lms() {
	
	//if(!class_exists('nss_plugin_updater'))
	require_once ( dirname( __FILE__ ).'/ld-autoupdate.php' );

	$nss_plugin_updater_plugin_remote_path = 'http://support.learndash.com/';
	$nss_plugin_updater_plugin_slug = basename( dirname( dirname( __FILE__ ) ) ) . '/sfwd_lms.php';

	new nss_plugin_updater_sfwd_lms( $nss_plugin_updater_plugin_remote_path, $nss_plugin_updater_plugin_slug );
}

// Load the auto-update class
add_action( 'init', 'nss_plugin_updater_activate_sfwd_lms' );



if ( ! function_exists( 'ld_debug' ) ) {

	/**
	 * Log debug messages to file
	 * 
	 * @param  int|str|arr|obj|bool 	$msg 	data to log
	 */
	function ld_debug( $msg ) {
		$original_log_errors = ini_get( 'log_errors' );
		$original_error_log = ini_get( 'error_log' );
		ini_set( 'log_errors', true );
		ini_set( 'error_log', dirname( dirname( __FILE__ ) ).DIRECTORY_SEPARATOR.'debug.log' );

		global $processing_id;

		if ( empty( $processing_id ) ) {
			$processing_id	= time();
		}

		if ( isset( $_GET['debug'] ) ) {
			error_log( "[ $processing_id] ".print_r( $msg, true ) ); //Comment This line to stop logging debug messages.
		}

		ini_set( 'log_errors', $original_log_errors );
		ini_set( 'error_log', $original_error_log );
	}

}



/**
 * Convert seconds to time
 *
 * @since 2.1.0
 * 
 * @param  int 		$inputSeconds
 * @return string   time output
 */
function learndash_seconds_to_time( $inputSeconds ) {
	$secondsInAMinute = 60;
	$secondsInAnHour  = 60 * $secondsInAMinute;
	$secondsInADay    = 24 * $secondsInAnHour;

	$return = '';
	// extract days
	$days = floor( $inputSeconds / $secondsInADay );
	$return .= empty( $days ) ? '' : $days.'day';

	// extract hours
	$hourSeconds = $inputSeconds % $secondsInADay;
	$hours = floor( $hourSeconds / $secondsInAnHour );
	$return .= ( empty( $hours ) && empty( $days ) )? '':' '.$hours.'hr';

	// extract minutes
	$minuteSeconds = $hourSeconds % $secondsInAnHour;
	$minutes = floor( $minuteSeconds / $secondsInAMinute );
	$return .= ( empty( $hours ) && empty( $days ) && empty( $minutes ) ) ? '' : ' '.$minutes.'min';

	// extract the remaining seconds
	$remainingSeconds = $minuteSeconds % $secondsInAMinute;
	$seconds = ceil( $remainingSeconds );
	$return .= ' '.$seconds.'sec';

	return trim( $return );
}

/**
 * Convert a timestamp to locally timezone adjusted output display
 *
 * @since 2.2.0
 *
 * @param int		$timestamp timestamp to display
 * @param string	$display_format optional display format
 * @return string	offset adjusted displayed date/time
 */
function learndash_adjust_date_time_display($timestamp = 0, $display_format = '' ) {
	$date_time_display = '';

	if ($timestamp != 0) {
		if ( empty( $display_format ) ) {
			$display_format = apply_filters('learndash_date_time_formats', get_option('date_format') .' '. get_option('time_format'));
		}
		
		$date_time_display = get_date_from_gmt( date('Y-m-d H:i:s', $timestamp), $display_format );
	}
	return $date_time_display;	
} 


/**
 * Check if server is on Microsoft IIS
 *
 * @since 2.1.0
 * 
 * @return bool
 */
function learndash_on_iis() {
	$sSoftware = strtolower( $_SERVER['SERVER_SOFTWARE'] );
	if ( strpos( $sSoftware, 'microsoft-iis' ) !== false ) {
		return true;
	} else {
		return false;
	}
}



/**
 * Sql "Default NULL check" in version 5(strict mode)
 * Function to disable null checks
 * Refer to bug http://core.trac.wordpress.org/ticket/2115
 *
 * @since 2.1.0
 */
function mysql_5_hack() {
	if ( learndash_on_iis() ) {
		global $wpdb;
		$sqlVersion = $wpdb->get_var( 'select @@version' );

		if ( $sqlVersion{0} == 5 ) { 
			$wpdb->query( 'set sql_mode="";' ); //set "Strict" mode off
		}		
	}
}

add_action( 'init', 'mysql_5_hack' );



/**
 * Helper function to print_r() in preformatted text 
 * 
 * @since 2.1.0
 * 
 * @param  string $msg
 */
function ldp( $msg ) {
	echo '<pre>';
	print_r( $msg );
	echo '</pre>';
}

/**
 * Utility function to traverse multidimensional array and apply user function 
 * 
 * @since 2.1.2
 * 
 * @param function $func callable user defined or system function. This 
 *			should be 'esc_attr', or some similar function. 
 * @param array $arr This is the array to traverse and cleanup. 
 *
 * @return array $arr cleaned array
 */
function array_map_r( $func, $arr) {
    foreach( $arr as $key => $value ) {
		if (is_array( $value ) ) {
			$arr[ $key ] = array_map_r( $func, $value );
		} else if (is_array($func)) {
			$arr[ $key ] = call_user_func_array($func, $value);
		} else {
			$arr[ $key ] = $func( $value );
		}
    }

    return $arr;
}
